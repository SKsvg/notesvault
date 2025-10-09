<?php
session_start();

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_POST['user_email'])) {
    header("Location: login.html");
    exit();
}

// 2. Database Connection
$conn = new mysqli('localhost:3307', 'root', 'insathMYSQ#123', 'test3');
if ($conn->connect_error) {
    error_log("DB Connection failed in save_profile.php: " . $conn->connect_error);
    header("Location: dashboard.php?status=db_error");
    exit();
}

// 3. Collect and Sanitize Input
$email_to_update = $_POST['user_email'];
$name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$institution = filter_var($_POST['institution'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$branch = filter_var($_POST['branch'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$year = filter_var($_POST['year'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$student_id = filter_var($_POST['student_id'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// 4. Profile Picture Upload Handling
$db_profile_pic_path = null; // This path will be stored in the database
$file_uploaded = false;

// *** FIX: Changed from $_FILES['profilePic'] to $_FILES['profile_pic'] to match the form input name ***
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_pic'];
    
    // Basic validation
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5 MB
    
    if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
        header("Location: dashboard.php?status=file_invalid");
        exit();
    }
    
    // *** FIX: Define a target directory relative to the PROJECT ROOT ***
    // This script (save_profile.php) is in a subfolder, so we use `../` to go up one level.
    // Your project structure should be:
    // /project_root
    //   -> /pages (contains dashboard.php, save_profile.php)
    //   -> /uploads
    //       -> /profiles  <- IMAGES WILL BE SAVED HERE
    $upload_dir = "../pages/uploads/profiles/";
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_name = uniqid('profile_', true) . '.' . $file_extension;
    $target_file = $upload_dir . $unique_name;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // *** FIX: Store a clean, root-relative path in the database ***
        // We do NOT store the "../" part.
        $db_profile_pic_path = "../pages/uploads/profiles/" . $unique_name;
        $file_uploaded = true;
    } else {
        error_log("Failed to move uploaded file for user: " . $email_to_update);
        header("Location: dashboard.php?status=file_error");
        exit();
    }
}

// 5. Prepare and Execute SQL UPDATE Statement
$sql = "UPDATE users SET name = ?, phone = ?, institution = ?, branch = ?, year = ?, student_id = ?";
$params = [$name, $phone, $institution, $branch, $year, $student_id];
$types = "ssssss";

if ($file_uploaded) {
    $sql .= ", profile_pic_path = ?";
    $params[] = $db_profile_pic_path; // Use the clean path for the database
    $types .= "s";
}

$sql .= " WHERE email = ?";
$params[] = $email_to_update;
$types .= "s";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("SQL Prepare failed: " . $conn->error);
    header("Location: dashboard.php?status=profile_error");
    exit();
}

// Bind parameters and execute
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if (isset($_SESSION['user_name'])) {
        $_SESSION['user_name'] = $name; 
    }
    header("Location: dashboard.php?status=profile_saved");
} else {
    error_log("Profile update failed for user " . $email_to_update . ": " . $stmt->error);
    header("Location: dashboard.php?status=profile_error");
}

$stmt->close();
$conn->close();
exit();
?>