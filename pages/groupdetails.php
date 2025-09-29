<?php
// ------------- Simple Backend Storage (JSON Files) -----------------
$groupName = isset($_GET['group']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['group']) : 'default';
$baseDir = __DIR__ . "/data";
if (!is_dir($baseDir)) mkdir($baseDir);

$chatFile   = "$baseDir/{$groupName}_chat.json";
$notesFile  = "$baseDir/{$groupName}_notes.json";
$gamesFile  = "$baseDir/{$groupName}_games.json";
$meetingFile= "$baseDir/{$groupName}_meeting.json";
$groupsFile = "../data/groups.json";

// initialize empty JSON if not exists
foreach ([$chatFile, $notesFile, $gamesFile, $meetingFile] as $f) {
    if (!file_exists($f)) file_put_contents($f, json_encode([]));
}
if (!file_exists($groupsFile)) file_put_contents($groupsFile, json_encode([]));

// API Handling
if (isset($_GET['api'])) {
    header("Content-Type: application/json");
    $api = $_GET['api'];
    $user = isset($_POST['user']) ? $_POST['user'] : "Anonymous";

    if ($api === 'chat_send' && !empty($_POST['msg'])) {
        $msgs = json_decode(file_get_contents($chatFile), true);
        $msgs[] = ["name"=>$user,"text"=>$_POST['msg'],"time"=>date("H:i:s")];
        file_put_contents($chatFile, json_encode($msgs));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if ($api === 'chat_get') {
        echo file_get_contents($chatFile);
        exit;
    }
    if ($api === 'note_add' && !empty($_POST['title'])) {
        $notes = json_decode(file_get_contents($notesFile), true);
        $uploadDir = __DIR__ . "/uploads/{$groupName}";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $note = ["title"=>$_POST['title'], "user"=>$user, "time"=>date("Y-m-d H:i:s")];
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['file']['name']);
            $filePath = $uploadDir . '/' . $fileName;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $note['type'] = 'file';
                $note['file_path'] = "uploads/{$groupName}/{$fileName}";
                $note['file_name'] = $fileName;
            }
        } else {
            $note['content'] = $_POST['content'] ?? '';
        }
        $notes[] = $note;
        file_put_contents($notesFile, json_encode($notes));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if ($api === 'note_get') {
        echo file_get_contents($notesFile);
        exit;
    }
    if ($api === 'meeting_toggle') {
        $meeting = json_decode(file_get_contents($meetingFile), true);
        if (!isset($meeting['joined'])) $meeting['joined'] = [];
        if (in_array($user, $meeting['joined'])) {
            $meeting['joined'] = array_diff($meeting['joined'], [$user]);
        } else {
            $meeting['joined'][] = $user;
        }
        file_put_contents($meetingFile, json_encode($meeting));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if ($api === 'meeting_schedule' && !empty($_POST['title']) && !empty($_POST['datetime'])) {
        $meeting = json_decode(file_get_contents($meetingFile), true);
        if (!isset($meeting['scheduled'])) $meeting['scheduled'] = [];
        $meeting['scheduled'][] = ["title"=>$_POST['title'], "datetime"=>$_POST['datetime'], "user"=>$user, "time"=>date("Y-m-d H:i:s")];
        file_put_contents($meetingFile, json_encode($meeting));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if ($api === 'meeting_get') {
        echo file_get_contents($meetingFile);
        exit;
    }
    if ($api === 'quiz_add' && !empty($_POST['question']) && !empty($_POST['answer'])) {
        $games = json_decode(file_get_contents($gamesFile), true);
        if (!isset($games['quizzes'])) $games['quizzes'] = [];
        $review = $_POST['review'] ?? '';
        $games['quizzes'][] = ["question"=>$_POST['question'], "answer"=>$_POST['answer'], "review"=>$review, "user"=>$user];
        file_put_contents($gamesFile, json_encode($games));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if ($api === 'quiz_delete' && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $games = json_decode(file_get_contents($gamesFile), true);
        if (isset($games['quizzes']) && isset($games['quizzes'][$index])) {
            array_splice($games['quizzes'], $index, 1);
            file_put_contents($gamesFile, json_encode($games));
            echo json_encode(["status"=>"ok"]);
        } else {
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }
    if ($api === 'quiz_get') {
        echo file_get_contents($gamesFile);
        exit;
    }
    if ($api === 'members_get') {
        $groups = json_decode(file_get_contents($groupsFile), true);
        $group = $groups[$groupName] ?? [];
        $members = $group['members'] ?? [];
        echo json_encode($members);
        exit;
    }
    if ($api === 'members_add' && !empty($_POST['email'])) {
        $groups = json_decode(file_get_contents($groupsFile), true);
        if (!isset($groups[$groupName])) {
            $groups[$groupName] = ['members' => []];
        }
        $email = $_POST['email'];
        if (!in_array($email, $groups[$groupName]['members'])) {
            $groups[$groupName]['members'][] = $email;
            // Send email notification
            $subject = "Added to Group";
            $message = "You are added to a group $groupName. Now you are a member of this group.";
            $headers = "From: noreply@yourdomain.com\r\n";
            mail($email, $subject, $message, $headers);
        }
        file_put_contents($groupsFile, json_encode($groups));
        echo json_encode(["status" => "ok"]);
        exit;
    }

    if ($api === 'chat_delete' && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $msgs = json_decode(file_get_contents($chatFile), true);
        if (isset($msgs[$index])) {
            array_splice($msgs, $index, 1);
            file_put_contents($chatFile, json_encode($msgs));
            echo json_encode(["status"=>"ok"]);
        } else {
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    if ($api === 'note_delete' && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $notes = json_decode(file_get_contents($notesFile), true);
        if (isset($notes[$index])) {
            array_splice($notes, $index, 1);
            file_put_contents($notesFile, json_encode($notes));
            echo json_encode(["status"=>"ok"]);
        } else {
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    if ($api === 'meeting_delete_scheduled' && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $meeting = json_decode(file_get_contents($meetingFile), true);
        if (!isset($meeting['scheduled'])) $meeting['scheduled'] = [];
        if (isset($meeting['scheduled'][$index])) {
            array_splice($meeting['scheduled'], $index, 1);
            file_put_contents($meetingFile, json_encode($meeting));
            echo json_encode(["status"=>"ok"]);
        } else {
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Group: <?php echo htmlspecialchars($groupName); ?></title>
<link rel="stylesheet" href="../styling/groupdetails.css" />
<link rel="stylesheet" href="../styling/base.css" />
<link rel="stylesheet" href="../styling/variables.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // Apply dark mode based on localStorage
  const theme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', theme);
</script>
</head>
<body>
<div style="margin-bottom: 20px;">
  <h2>Group: <?php echo htmlspecialchars($groupName); ?></h2>
</div>
<div>
    <button class="tab-btn" data-tab="chat">Chat</button>
    <button class="tab-btn" data-tab="notes">Notes</button>
    <button class="tab-btn" data-tab="meeting">Meeting</button>
    <button class="tab-btn" data-tab="games">Games</button>
    <button class="tab-btn" data-tab="members">Members</button>
</div>

<!-- Chat -->
<div class="tab-content" id="chat">
    <h3>Group Chat</h3>
    <div class="chat-window" id="chatWindow"></div>
    <input type="text" id="chatInput"><button id="chatSend">Send</button>
</div>

<!-- Notes -->
<div class="tab-content" id="notes">
    <h3>Notes</h3>
    <div id="notesList"></div>
    <input type="text" id="noteTitle" placeholder="Note title">
    <textarea id="noteContent" placeholder="Note content (optional if file uploaded)" rows="4"></textarea>
    <input type="file" id="noteFile" accept=".pdf,.jpg,.jpeg,.png">
    <button id="noteShare">Share Note</button>
</div>

<!-- Meeting -->
<div class="tab-content" id="meeting">
    <h3>Meeting</h3>
    <div id="meetingList"></div>
    <h4>Schedule a Meeting</h4>
    <input type="text" id="meetingTitle" placeholder="Meeting title">
    <input type="datetime-local" id="meetingDateTime">
    <button id="meetingScheduleBtn">Schedule Meeting</button>
</div>

<!-- Games -->
<div class="tab-content" id="games">
    <h3>Games</h3>
    <div id="gamesList"></div>
    <h4>Create a Quiz Question</h4>
    <input type="text" id="quizQuestion" placeholder="Question">
    <input type="text" id="quizAnswer" placeholder="Answer">
    <textarea id="quizReview" placeholder="Review/Explanation (optional)" rows="3"></textarea>
    <button id="quizAddBtn">Add Quiz Question</button>
    <h4>Play Quiz</h4>
    <button id="quizStartBtn">Start Quiz</button>
    <div class="quiz-container" id="quizContainer" style="display:none;">
        <div class="quiz-question" id="quizQuestionDisplay"></div>
        <input type="text" id="quizUserAnswer" placeholder="Your answer">
        <button id="quizSubmitBtn">Submit Answer</button>
        <div class="quiz-feedback" id="quizFeedback"></div>
    </div>
</div>

<!-- Members -->
<div class="tab-content" id="members">
    <h3>Group Members</h3>
    <div id="membersList"></div>
    <h4>Add Member</h4>
    <input type="email" id="memberEmailInput" placeholder="Enter email to add">
    <button id="addMemberBtn">Add Member</button>
    <div id="memberMsg"></div>
</div>

<script>
let userName = localStorage.getItem("userEmail") || "Anonymous";

$(".tab-btn").click(function(){
    $(".tab-content").removeClass("active");
    $("#" + $(this).data("tab")).addClass("active");
});

// Chat
function loadChat(){
    $.get("?group=<?php echo $groupName; ?>&api=chat_get",function(data){
        let html="";
        data.forEach((m,i)=> {
            let cls = m.name === userName ? "you" : "other";
            html+=`<div class="chat-message ${cls}"><b>${m.name}</b>: ${m.text}<small>${m.time}</small> <button class="delete-chat" data-index="${i}">Delete</button></div>`;
        });
        $("#chatWindow").html(html);
        $("#chatWindow").scrollTop($("#chatWindow")[0].scrollHeight);
    },"json");
}
$("#chatSend").click(function(){
    let msg=$("#chatInput").val().trim();
    if(!msg) return;
    $.post("?group=<?php echo $groupName; ?>&api=chat_send",{msg:msg, user:userName},()=>{ $("#chatInput").val(""); loadChat(); });
});
setInterval(loadChat,2000);

$(document).on('click', '.delete-chat', function() {
    let index = $(this).data('index');
    if (confirm('Are you sure you want to delete this message?')) {
        $.post("?group=<?php echo $groupName; ?>&api=chat_delete", {index: index}, function(response) {
            if (response.status === "ok") {
                loadChat();
            } else {
                alert("Error deleting message.");
            }
        }, "json");
    }
});

// Notes
function loadNotes(){
    $.get("?group=<?php echo $groupName; ?>&api=note_get",function(data){
        let html="";
        data.forEach((n,i)=> {
            let contentHtml = "";
            if (n.type === 'file') {
                let ext = n.file_name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    contentHtml = `<img src="${n.file_path}" alt="${n.file_name}" style="max-width:100%; height:auto;">`;
                } else {
                    contentHtml = `<a href="${n.file_path}" target="_blank">Download ${n.file_name}</a>`;
                }
            } else {
                contentHtml = n.content.replace(/\n/g, '<br>');
            }
            html += `<div class="note-item"><div class="note-title">${n.title}</div><div class="note-content">${contentHtml}</div><small>by ${n.user} at ${n.time}</small> <button class="delete-note" data-index="${i}">Delete</button></div>`;
        });
        $("#notesList").html(html);
    },"json");
}
$("#noteShare").click(function(){
    let title=$("#noteTitle").val().trim();
    let content=$("#noteContent").val().trim();
    let fileInput = $("#noteFile")[0];
    if(!title) return alert("Please enter a title.");
    if(!content && !fileInput.files[0]) return alert("Please enter content or select a file.");
    let formData = new FormData();
    formData.append('title', title);
    formData.append('content', content);
    formData.append('user', userName);
    if (fileInput.files[0]) {
        formData.append('file', fileInput.files[0]);
    }
    $.ajax({
        url: "?group=<?php echo $groupName; ?>&api=note_add",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response){
            if(response.status === "ok") {
                $("#noteTitle").val(""); $("#noteContent").val(""); $("#noteFile").val(""); loadNotes();
            } else {
                alert("Error adding note.");
            }
        },
        dataType: "json"
    });
});

$(document).on('click', '.delete-note', function() {
    let index = $(this).data('index');
    if (confirm('Are you sure you want to delete this note?')) {
        $.post("?group=<?php echo $groupName; ?>&api=note_delete", {index: index}, function(response) {
            if (response.status === "ok") {
                loadNotes();
            } else {
                alert("Error deleting note.");
            }
        }, "json");
    }
});

// Meeting
function loadMeeting(){
    $.get("?group=<?php echo $groupName; ?>&api=meeting_get",function(data){
        let html="<h4>Joined Users:</h4>";
        if(data.joined && data.joined.length > 0){
            data.joined.forEach(u=> html+=`<div class="meeting-item">${u} is in meeting</div>`);
        } else {
            html+="<p>No one joined yet.</p>";
        }
        html+="<h4>Scheduled Meetings:</h4>";
        if(data.scheduled && data.scheduled.length > 0){
            data.scheduled.forEach((m,i)=> html+=`<div class="meeting-item"><strong>${m.title || 'Untitled Meeting'}</strong> at ${m.datetime} scheduled by ${m.user} <button onclick="window.open('https://meet.google.com/new?authuser=0&pli=1', '_blank')">Join Google Meet</button> <button class="delete-meeting" data-index="${i}">Delete</button></div>`);
        } else {
            html+="<p>No meetings scheduled.</p>";
        }
        $("#meetingList").html(html);
    },"json");
}
$("#meetingToggle").click(function(){
    $.post("?group=<?php echo $groupName; ?>&api=meeting_toggle",{user:userName},()=> loadMeeting());
});
$("#meetingScheduleBtn").click(function(){
    let title = $("#meetingTitle").val().trim();
    let dt = $("#meetingDateTime").val();
    if(!title || !dt) return alert("Please fill meeting title and datetime.");
    $.post("?group=<?php echo $groupName; ?>&api=meeting_schedule",{title: title, datetime:dt, user:userName}, function(response){
        if(response.status === "ok") {
            $("#meetingTitle").val(""); $("#meetingDateTime").val(""); loadMeeting();
        } else {
            alert("Error scheduling meeting.");
        }
    },"json");
});
setInterval(loadMeeting,3000);

$(document).on('click', '.delete-meeting', function() {
    let index = $(this).data('index');
    if (confirm('Are you sure you want to delete this scheduled meeting?')) {
        $.post("?group=<?php echo $groupName; ?>&api=meeting_delete_scheduled", {index: index}, function(response) {
            if (response.status === "ok") {
                loadMeeting();
            } else {
                alert("Error deleting meeting.");
            }
        }, "json");
    }
});

// Games
let quizzes = [];
function loadGames(){
    $.get("?group=<?php echo $groupName; ?>&api=quiz_get",function(data){
        quizzes = data.quizzes || [];
        let html="<h4>Quiz Questions:</h4>";
        quizzes.forEach((q,i)=> html+=`<div class="game-item"><span>Q${i+1}: ${q.question} (by ${q.user})</span> <button class="delete-quiz" data-index="${i}">Delete</button></div>`);
        $("#gamesList").html(html);
    },"json");
}

$(document).on('click', '.delete-quiz', function() {
    let index = $(this).data('index');
    if (confirm('Are you sure you want to delete this question?')) {
        $.post("?group=<?php echo $groupName; ?>&api=quiz_delete", {index: index}, function(response) {
            if (response.status === "ok") {
                loadGames();
            } else {
                alert("Error deleting question.");
            }
        }, "json");
    }
});
$("#quizAddBtn").click(function(){
    let q=$("#quizQuestion").val().trim();
    let a=$("#quizAnswer").val().trim();
    let r=$("#quizReview").val().trim();
    if(!q || !a) return alert("Please fill question and answer.");
    $.post("?group=<?php echo $groupName; ?>&api=quiz_add",{question:q, answer:a, review:r, user:userName}, function(response){
        if(response.status === "ok") {
            $("#quizQuestion").val(""); $("#quizAnswer").val(""); $("#quizReview").val(""); loadGames();
        } else {
            alert("Error adding quiz question.");
        }
    },"json");
});
let currentQuizIndex = -1;
$("#quizStartBtn").click(function(){
    if(quizzes.length === 0) return alert("No quizzes available.");
    currentQuizIndex = 0;
    showQuiz();
    $("#quizContainer").show();
});
function showQuiz(){
    if(currentQuizIndex < quizzes.length){
        $("#quizQuestionDisplay").text(quizzes[currentQuizIndex].question);
        $("#quizUserAnswer").val("");
        $("#quizFeedback").empty(); // Clear previous content
        $("#viewReviewBtn").remove(); // Remove any previous review button
    } else {
        $("#quizContainer").hide();
        alert("Quiz finished!");
    }
}
$("#quizSubmitBtn").click(function(){
    let ans = $("#quizUserAnswer").val().trim().toLowerCase();
    let correct = quizzes[currentQuizIndex].answer.toLowerCase();
    let feedbackHtml = "";
    if(ans === correct){
        feedbackHtml = "Correct!";
        $("#quizFeedback").css("color","green");
    } else {
        feedbackHtml = "Wrong! Correct: " + quizzes[currentQuizIndex].answer;
        $("#quizFeedback").css("color","red");
    }
    $("#quizFeedback").html(feedbackHtml);

    // Check if review exists and add button
    if (quizzes[currentQuizIndex].review && quizzes[currentQuizIndex].review.trim() !== "") {
        let reviewBtn = $('<button id="viewReviewBtn" class="view-review-btn">View Review</button>');
        $("#quizFeedback").append(reviewBtn);
        reviewBtn.click(function() {
            let reviewDisplay = $('<div class="review-display">' + quizzes[currentQuizIndex].review + '</div>');
            $("#quizFeedback").append(reviewDisplay);
            $(this).hide(); // Hide button after click
            // Advance after showing review
            setTimeout(()=>{ currentQuizIndex++; showQuiz(); }, 3000);
        });
    } else {
        // No review, advance normally
        setTimeout(()=>{ currentQuizIndex++; showQuiz(); }, 2000);
    }
});

// Members
function loadMembers(){
    $.get("?group=<?php echo $groupName; ?>&api=members_get",function(data){
        let html="<h4>Current Members:</h4>";
        if(data.length > 0){
            data.forEach(m=> html+=`<div class="member-item">Member: ${m}</div>`);
        } else {
            html+="<p>No members yet.</p>";
        }
        $("#membersList").html(html);
    },"json");
}
$("#addMemberBtn").click(function(){
    let email=$("#memberEmailInput").val().trim();
    if(!email) return;
    $.post("?group=<?php echo $groupName; ?>&api=members_add",{email:email},function(response){
        if(response.status === "ok"){
            $("#memberMsg").text("Member added successfully!").css("color","green");
            $("#memberEmailInput").val("");
            loadMembers();
        } else {
            $("#memberMsg").text("Error adding member.").css("color","red");
        }
    },"json");
});



// init
loadChat(); loadNotes(); loadMeeting(); loadGames(); loadMembers();

// Automatically open chat tab
$("#chat").addClass("active");
</script>
</body>
</html>
