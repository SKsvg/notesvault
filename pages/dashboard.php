<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'notesvault');
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB connection failed"]));
}

$email = $_SESSION['user_email'];

// Fetch current user data for display
$stmt = $conn->prepare("SELECT u.id, u.name, u.email, e.phone, e.institution, e.branch, e.year, e.student_id, e.profile_pic
                        FROM users u
                        LEFT JOIN edit_users e ON u.id = e.user_id
                        WHERE u.email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NotesVault - Student Dashboard</title>
    <link rel="icon" href="../assets/index/images/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../styling/variables.css" />
    <link rel="stylesheet" href="../styling/base.css" />
    <link rel="stylesheet" href="../styling/dashboard.css" />
</head>
<body>
<?php include '../components/header.php'; ?>

<main class="dashboard-container">
    <div class="dashboard-grid">
        <!-- Profile Card -->
        <section class="profile-card" style="height: 630px;">
            <div class="profile-header">
                <div class="avatar">
                    <?php if(!empty($user['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Avatar">
                    <?php else: ?>
                        <i class="fas fa-user-graduate"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['name']); ?> <div id="studentnumber"><?php echo htmlspecialchars($user['student_id']); ?></div></h2>
                    <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="institution"><?php echo htmlspecialchars($user['institution'] ?: 'University of Jaffna'); ?></p>
                </div>
            </div>
            <div class="profile-details">
                <div class="detail-item"><i class="fas fa-graduation-cap"></i><div><span>Department</span><p><?php echo htmlspecialchars($user['branch'] ?: 'Computer Science'); ?></p></div></div>
                <div class="detail-item"><i class="fas fa-calendar-alt"></i><div><span>Year</span><p><?php echo htmlspecialchars($user['year'] ?: '2nd Year'); ?></p></div></div>
                <div class="detail-item"><i class="fas fa-id-card"></i><div><span>Student ID</span><p><?php echo htmlspecialchars($user['student_id']); ?></p></div></div>
            </div>
            <button class="edit-profile-btn"><i class="fas fa-edit"></i> Edit Profile</button>
        </section>

        <!-- Edit Modal -->
        <div class="modal" id="editModal" aria-hidden="true">
            <div class="profile-modal" role="dialog" aria-modal="true" aria-labelledby="editProfileTitle">
                <div class="modal-header">
                    <h3 id="editProfileTitle">Edit Profile</h3>
                    <button id="closeModal" class="close" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editForm" class="profile-form" enctype="multipart/form-data" novalidate>
                        <div class="avatar-upload">
                            <label class="avatar-label" for="profilePic">
                                <img src="<?php echo htmlspecialchars($user['profile_pic'] ?: '../assets/index/images/default-avatar.png'); ?>" alt="Avatar preview" id="avatarPreview" class="avatar-img" />
                                <span class="change-avatar-text"><i class="fas fa-camera"></i> Change profile</span>
                            </label>
                            <input type="file" id="profilePic" name="profilePic" accept="image/*" />
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="institution">Institution</label>
                                <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($user['institution']); ?>" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <input type="text" id="branch" name="branch" value="<?php echo htmlspecialchars($user['branch']); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="year">Year</label>
                                <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($user['year']); ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="studentID">Student ID</label>
                            <input type="text" id="studentID" name="studentID" value="<?php echo htmlspecialchars($user['student_id']); ?>" />
                        </div>
                        <div class="form-actions">
                            <button type="button" id="cancelBtn" class="cancel-btn">Cancel</button>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <section class="stats-card" style="height: 630px;">
            <h3><i class="fas fa-chart-line"></i> Your Stats</h3>
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value">3</div><div class="stat-label">Notes</div></div>
                <div class="stat-item"><div class="stat-value">0</div><div class="stat-label">PYQs</div></div>
                <div class="stat-item"><div class="stat-value">3</div><div class="stat-label">Subjects</div></div>
                <div class="stat-item"><div class="stat-value">0%</div><div class="stat-label">Completion</div></div>
            </div>
            
        </section>
         
                    <div class="calendar-card">
            <div class="calendar-header">
                <button class="calendar-nav-btn" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                <h3 id="currentMonthYear">
                <i class="fas fa-calendar-alt"></i> </h3>
                <button class="calendar-nav-btn" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                <button id="addEventBtn" class="btn-primary" style="margin-left: 10px;">
                    <i class="fas fa-plus"></i> Add Task
            </div>
            <div class="calendar-grid-labels calendar-grid">
                <div class="day-label">Sun</div>
                <div class="day-label">Mon</div>
                <div class="day-label">Tue</div>
                <div class="day-label">Wed</div>
                <div class="day-label">Thu</div>
                <div class="day-label">Fri</div>
                <div class="day-label">Sat</div>
            </div>
            <div class="calendar-grid" id="calendarDates">
                </div>
            </div>  
        <section class="pyqs-section" >
            <div class="section-header">
                <h2><i class="fas fa-book"></i> Your Notes</h2>
                <div class="section-actions">
                    <a href="upload.php" class="btn btn-primary" id="upload-note-btn" style="color:white;" onmouseover="this.style.color='white'" onmouseout="this.style.color='white'"><i class="fas fa-cloud-upload-alt"></i> Upload Notes</a>
                </div>
            </div>
            <div class="notes-grid">
                <div class="add-note-card" style="width: 160px; display: flex;">
                    <a href="upload.php"><button class="add-note-btn"><i class="fas fa-plus-circle"></i><span>Add New Notes</span></button></a>
                </div>
            </div>

        </section>
        <!-- <section class="pyqs-section">
            <div class="section-header">
                <h2><i class="fas fa-file-alt"></i> Previous Year Questions</h2>
                <div class="section-actions">
                    <button class="btn-outline"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
            <div class="pyqs-table-container">
                <table class="pyqs-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Exam</th>
                            <th>Year</th>
                            <th>File Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Computer Networks</td>
                            <td>Midterm</td>
                            <td>2022</td>
                            <td><span class="file-badge pdf">PDF</span></td>
                            <td>
                                <button class="table-action-btn"><i class="fas fa-download"></i></button>
                                <button class="table-action-btn"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Data Structures</td>
                            <td>Final</td>
                            <td>2021</td>
                            <td><span class="file-badge doc">DOC</span></td>
                            <td>
                                <button class="table-action-btn"><i class="fas fa-download"></i></button>
                                <button class="table-action-btn"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Algorithms</td>
                            <td>Quiz</td>
                            <td>2023</td>
                            <td><span class="file-badge pdf">PDF</span></td>
                            <td>
                                <button class="table-action-btn"><i class="fas fa-download"></i></button>
                                <button class="table-action-btn"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section> -->
    </div>
</main>
<?php include '../components/footer.php'; ?>
<script src="../scripts/header.js" defer></script>
<script src="../scripts/script.js" defer></script>
<script src="../scripts/dashboard.js"></script>


<div id="addEventModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Task/Event</h3>
            <button id="closeEventModal" class="close-modal" aria-label="Close modal">&times;</button>
        </div>
        <form id="eventForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="eventTitle">Task Title / Description</label>
                    <input type="text" id="eventTitle" required placeholder="e.g., Submit Database Assignment">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="eventDate">Date</label>
                        <input type="date" id="eventDate" required>
                    </div>
                    <div class="form-group">
                        <label for="eventType">Event Type</label>
                        <select id="eventType" required>
                            <option value="Assignment">Assignment</option>
                            <option value="Exam">Exam</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Project">Project Deadline</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-actions" style="border-top: 1px solid var(--border-color); padding: 12px 20px 12px 20px;">
                <button type="button" class="cancel-btn" onclick="document.getElementById('addEventModal').classList.remove('active');">Cancel</button>
                <button type="submit" class="save-btn">Save Task</button>
            </div>
        </form>
    </div>
</div>

<div id="eventDetailModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content event-details-content" style="max-width: 350px;">
        <div class="modal-header">
            <h3 id="eventDetailTitle">Task Details</h3>
            <button id="closeDetailModal" class="close-modal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <p><strong>Date:</strong> <span id="detailDate"></span></p>
            <div id="eventListContainer">
                </div>
            
            <button id="addNewTaskForDayBtn" class="btn-secondary">
                <i class="fas fa-plus"></i> Add New Task for this Day
            </button>
        </div>
    </div>
</div>

</body>
</html>
