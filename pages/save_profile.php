<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$conn = new mysqli('localhost:3307', 'root', 'insathMYSQL#123', 'test3');
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$institution = $_POST['institution'] ?? '';
$branch = $_POST['branch'] ?? '';
$year = $_POST['year'] ?? '';
$studentID = $_POST['studentID'] ?? '';
$profilePicPath = null;

if (!empty($_FILES['profilePic']['name'])) {
    $uploadDir = "../uploads/profile_pics/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filename = time() . "_" . basename($_FILES['profilePic']['name']);
    $target = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $target)) {
        $profilePicPath = $target;
    }
}

// Update name in users table
$stmt1 = $conn->prepare("UPDATE users SET name=? WHERE id=?");
$stmt1->bind_param("si", $name, $user_id);
$stmt1->execute();

// Insert or update into edit_users
if ($profilePicPath) {
    $stmt2 = $conn->prepare("
        INSERT INTO edit_users (user_id, phone, institution, branch, year, student_id, profile_pic)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        phone=VALUES(phone), institution=VALUES(institution),
        branch=VALUES(branch), year=VALUES(year),
        student_id=VALUES(student_id), profile_pic=VALUES(profile_pic)
    ");
    $stmt2->bind_param("issssss", $user_id, $phone, $institution, $branch, $year, $studentID, $profilePicPath);
} else {
    $stmt2 = $conn->prepare("
        INSERT INTO edit_users (user_id, phone, institution, branch, year, student_id)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        phone=VALUES(phone), institution=VALUES(institution),
        branch=VALUES(branch), year=VALUES(year),
        student_id=VALUES(student_id)
    ");
    $stmt2->bind_param("isssss", $user_id, $phone, $institution, $branch, $year, $studentID);
}

if ($stmt2->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully",
        "data" => [
            "name" => $name,
            "phone" => $phone,
            "institution" => $institution,
            "branch" => $branch,
            "year" => $year,
            "studentID" => $studentID,
            "profilePic" => $profilePicPath
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}
?>
