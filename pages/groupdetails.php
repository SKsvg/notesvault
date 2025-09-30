<?php
// ------------------- Backend Storage -------------------
$groupName = isset($_GET['group']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['group']) : 'default';
$baseDir = __DIR__ . "/data";
if (!is_dir($baseDir)) mkdir($baseDir);

$chatFile    = "$baseDir/{$groupName}_chat.json";
$notesFile   = "$baseDir/{$groupName}_notes.json";
$gamesFile   = "$baseDir/{$groupName}_games.json";
$meetingFile = "$baseDir/{$groupName}_meeting.json";
$groupsFile  = "../data/groups.json";

// initialize empty JSON if not exists
foreach([$chatFile, $notesFile, $gamesFile, $meetingFile] as $f){
    if(!file_exists($f)) file_put_contents($f, json_encode([]));
}
if(!file_exists($groupsFile)) file_put_contents($groupsFile, json_encode([]));

// ------------------- API Handling -------------------
if(isset($_GET['api'])){
    header("Content-Type: application/json");
    $api = $_GET['api'];
    $user = $_POST['user'] ?? "Anonymous";

    // ------------------- Chat -------------------
    if($api === 'chat_send' && !empty($_POST['msg'])){
        $msgs = json_decode(file_get_contents($chatFile), true);
        $msgs[] = ["name"=>$user,"text"=>$_POST['msg'],"time"=>date("H:i:s")];
        file_put_contents($chatFile, json_encode($msgs));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if($api === 'chat_get'){
        echo file_get_contents($chatFile);
        exit;
    }
    if($api === 'chat_delete' && isset($_POST['index'])){
        $index = (int)$_POST['index'];
        $msgs = json_decode(file_get_contents($chatFile), true);
        if(isset($msgs[$index])){
            array_splice($msgs,$index,1);
            file_put_contents($chatFile,json_encode($msgs));
            echo json_encode(["status"=>"ok"]);
        }else{
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    // ------------------- Notes -------------------
    if($api === 'note_add' && !empty($_POST['title'])){
        $notes = json_decode(file_get_contents($notesFile), true);
        $uploadDir = __DIR__ . "/uploads/{$groupName}";
        if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

        $note = ["title"=>$_POST['title'], "user"=>$user, "time"=>date("Y-m-d H:i:s")];

        if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK){
            $fileName = basename($_FILES['file']['name']);
            $filePath = $uploadDir.'/'.$fileName;
            if(move_uploaded_file($_FILES['file']['tmp_name'],$filePath)){
                $note['type']='file';
                $note['file_path']="uploads/{$groupName}/{$fileName}";
                $note['file_name']=$fileName;
            }
        }else{
            $note['content'] = $_POST['content'] ?? '';
        }

        $notes[] = $note;
        file_put_contents($notesFile,json_encode($notes));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if($api === 'note_get'){
        echo file_get_contents($notesFile);
        exit;
    }
    if($api === 'note_delete' && isset($_POST['index'])){
        $index = (int)$_POST['index'];
        $notes = json_decode(file_get_contents($notesFile), true);
        if(isset($notes[$index])){
            array_splice($notes,$index,1);
            file_put_contents($notesFile,json_encode($notes));
            echo json_encode(["status"=>"ok"]);
        }else{
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    // ------------------- Meetings -------------------
    if($api === 'meeting_toggle'){
        $meeting = json_decode(file_get_contents($meetingFile), true);
        if(!isset($meeting['joined'])) $meeting['joined'] = [];
        if(in_array($user,$meeting['joined'])){
            $meeting['joined'] = array_diff($meeting['joined'], [$user]);
        }else{
            $meeting['joined'][]=$user;
        }
        file_put_contents($meetingFile,json_encode($meeting));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    // Meeting Schedule: ADDED support for 'link'
    if($api === 'meeting_schedule' && !empty($_POST['title']) && !empty($_POST['datetime'])){
        $link = $_POST['link'] ?? ''; // Added: Capture the link
        if(empty($link)) {
             echo json_encode(["status"=>"error", "message"=>"Meeting link is required"]);
             exit;
        }

        $meeting = json_decode(file_get_contents($meetingFile), true);
        if(!isset($meeting['scheduled'])) $meeting['scheduled'] = [];
        // Stored the new 'link' parameter
        $meeting['scheduled'][] = ["title"=>$_POST['title'], "datetime"=>$_POST['datetime'], "user"=>$user, "time"=>date("Y-m-d H:i:s"), "link"=>$link];
        file_put_contents($meetingFile,json_encode($meeting));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if($api === 'meeting_get'){
        echo file_get_contents($meetingFile);
        exit;
    }
    if($api === 'meeting_delete_scheduled' && isset($_POST['index'])){
        $index = (int)$_POST['index'];
        $meeting = json_decode(file_get_contents($meetingFile), true);
        if(!isset($meeting['scheduled'])) $meeting['scheduled']=[];
        if(isset($meeting['scheduled'][$index])){
            array_splice($meeting['scheduled'],$index,1);
            file_put_contents($meetingFile,json_encode($meeting));
            echo json_encode(["status"=>"ok"]);
        }else{
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    // ------------------- Games -------------------
    if($api === 'quiz_add' && !empty($_POST['question']) && !empty($_POST['answer'])){
        $games = json_decode(file_get_contents($gamesFile), true);
        if(!isset($games['quizzes'])) $games['quizzes'] = [];
        $review = $_POST['review'] ?? '';
        $games['quizzes'][]=["question"=>$_POST['question'], "answer"=>$_POST['answer'], "review"=>$review, "user"=>$user];
        file_put_contents($gamesFile,json_encode($games));
        echo json_encode(["status"=>"ok"]);
        exit;
    }
    if($api === 'quiz_get'){
        echo file_get_contents($gamesFile);
        exit;
    }
    if($api === 'quiz_delete' && isset($_POST['index'])){
        $index=(int)$_POST['index'];
        $games = json_decode(file_get_contents($gamesFile),true);
        if(isset($games['quizzes'][$index])){
            array_splice($games['quizzes'],$index,1);
            file_put_contents($gamesFile,json_encode($games));
            echo json_encode(["status"=>"ok"]);
        }else{
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    // ------------------- Members -------------------
    if($api === 'members_get'){
        $groups = json_decode(file_get_contents($groupsFile), true);
        $group = $groups[$groupName] ?? [];
        $members = $group['members'] ?? [];
        echo json_encode($members);
        exit;
    }

    if($api === 'members_add' && !empty($_POST['email'])){
        $groups = json_decode(file_get_contents($groupsFile), true);
        if(!isset($groups[$groupName])) $groups[$groupName] = ['members'=>[]];
        $email = $_POST['email'];
        if(!in_array($email,$groups[$groupName]['members'])){
            $groups[$groupName]['members'][] = $email;
        }
        file_put_contents($groupsFile,json_encode($groups));
        echo json_encode(["success"=>true,"message"=>"Member added successfully"]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Group: <?php echo htmlspecialchars($groupName); ?></title>
<link rel="stylesheet" href="../styling/groupdetails.css">
<link rel="stylesheet" href="../styling/base.css">
<link rel="stylesheet" href="../styling/variables.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
</script>
</head>
<body>
<h2>Group: <?php echo htmlspecialchars($groupName); ?></h2>

<!-- Tabs -->
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
<textarea id="noteContent" placeholder="Note content" rows="4"></textarea>
<input type="file" id="noteFile">
<button id="noteShare">Share Note</button>
</div>

<!-- Meeting -->
<div class="tab-content" id="meeting">
<h3>Meeting</h3>
<div id="meetingList"></div>
<input type="text" id="meetingTitle" placeholder="Meeting title">
<input type="datetime-local" id="meetingDateTime">
<input type="url" id="meetingLink" placeholder="Permanent Meeting Link (e.g., Google Meet URL)"> <button id="meetingScheduleBtn">Schedule Meeting</button>
</div>

<!-- Games -->
<div class="tab-content" id="games">
<h3>Games</h3>
<div id="gamesList"></div>
<input type="text" id="quizQuestion" placeholder="Question">
<input type="text" id="quizAnswer" placeholder="Answer">
<textarea id="quizReview" placeholder="Review (optional)" rows="3"></textarea>
<button id="quizAddBtn">Add Quiz</button>
<button id="quizStartBtn">Start Quiz</button>
<div id="quizContainer" style="display:none">
<div id="quizQuestionDisplay"></div>
<input type="text" id="quizUserAnswer" placeholder="Your answer">
<button id="quizSubmitBtn">Submit Answer</button>
<div id="quizFeedback"></div>
</div>
</div>

<!-- Members -->
<div class="tab-content" id="members">
<h3>Members</h3>
<div id="membersList"></div>
<input type="email" id="memberEmailInput" placeholder="Enter email">
<button id="addMemberBtn">Add Member</button>
<div id="memberMsg"></div>
</div>

<script>
let userName = localStorage.getItem("userEmail") || "Anonymous";
let groupName = "<?php echo $groupName; ?>";

// Tabs
$(".tab-btn").click(function(){
$(".tab-content").removeClass("active");
$("#"+$(this).data("tab")).addClass("active");
});

// ------------------- Chat -------------------
function loadChat(){
$.get("?group=<?php echo $groupName; ?>&api=chat_get", function(data){
let html="";
data.forEach((m,i)=>{
let cls = m.name === userName ? "you" : "other";
html += `<div class="chat-message ${cls}"><b>${m.name}</b>: ${m.text}<small>${m.time}</small> <button class="delete-chat" data-index="${i}">Delete</button></div>`;
});
$("#chatWindow").html(html);
$("#chatWindow").scrollTop($("#chatWindow")[0].scrollHeight);
},"json");
}
$("#chatSend").click(function(){
let msg=$("#chatInput").val().trim();
if(!msg) return;
$.post("?group=<?php echo $groupName; ?>&api=chat_send",{msg:msg,user:userName},()=>{ $("#chatInput").val(""); loadChat(); });
});
$(document).on('click','.delete-chat',function(){
let index=$(this).data('index');
if(confirm('Delete this message?')){
$.post("?group=<?php echo $groupName; ?>&api=chat_delete",{index:index}, function(r){ if(r.status==="ok") loadChat(); else alert("Error"); },"json");
}
});
setInterval(loadChat,2000);

// ------------------- Notes -------------------
function loadNotes(){
$.get("?group=<?php echo $groupName; ?>&api=note_get", function(data){
let html="";
data.forEach((n,i)=>{
let content="";
if(n.type==='file'){
let ext=n.file_name.split('.').pop().toLowerCase();
if(['jpg','jpeg','png'].includes(ext)) content=`<img src="${n.file_path}" style="max-width:100%">`;
else content=`<a href="${n.file_path}" target="_blank">Download ${n.file_name}</a>`;
}else content=n.content.replace(/\n/g,'<br>');
html+=`<div class="note-item"><b>${n.title}</b><div>${content}</div><small>${n.user} at ${n.time}</small> <button class="delete-note" data-index="${i}">Delete</button></div>`;
});
$("#notesList").html(html);
},"json");
}
$("#noteShare").click(function(){
let title=$("#noteTitle").val().trim();
let content=$("#noteContent").val().trim();
let fileInput=$("#noteFile")[0];
if(!title) return alert("Enter title");
if(!content && !fileInput.files[0]) return alert("Enter content or file");
let formData=new FormData();
formData.append('title',title);
formData.append('content',content);
formData.append('user',userName);
if(fileInput.files[0]) formData.append('file',fileInput.files[0]);
$.ajax({
url:"?group=<?php echo $groupName; ?>&api=note_add",
type:"POST",
data:formData,
processData:false,
contentType:false,
success:function(res){ if(res.status==="ok"){ $("#noteTitle,#noteContent,#noteFile").val(""); loadNotes(); } else alert("Error"); },
dataType:"json"
});
});
$(document).on('click','.delete-note',function(){
let index=$(this).data('index');
if(confirm('Delete this note?')) $.post("?group=<?php echo $groupName; ?>&api=note_delete",{index:index},function(r){ if(r.status==="ok") loadNotes(); else alert("Error"); },"json");
});
loadNotes();

// ------------------- Meetings -------------------
function loadMeeting(){
$.get("?group=<?php echo $groupName; ?>&api=meeting_get",function(data){
// Removed 'Joined Users' section as it relates to the old, separate meeting logic
let html="<h4>Scheduled Meetings:</h4>";
if(data.scheduled && data.scheduled.length>0) {
    data.scheduled.forEach((m,i)=> {
        // Use the stored link for a single meeting everyone joins
        const joinButton = m.link 
            ? `<button onclick="window.open('${m.link}', '_blank')">Join Meeting</button>` // Correctly uses the scheduled link
            : '<span style="color:red; margin-left:10px;">No link provided</span>';
            
        html+=`<div>
            <b>${m.title}</b> at ${m.datetime} by ${m.user} 
            ${joinButton}
            <button class="delete-meeting" data-index="${i}">Delete</button>
        </div>`;
    });
} else {
    html+="<p>No meetings scheduled</p>";
}
$("#meetingList").html(html);
},"json");
}

$("#meetingScheduleBtn").click(function(){
let title=$("#meetingTitle").val().trim();
let dt=$("#meetingDateTime").val();
let link=$("#meetingLink").val().trim(); // ADDED: Get link value

if(!title || !dt || !link) return alert("Fill title, date/time, AND the permanent meeting link."); // IMPROVED CHECK

$.post("?group=<?php echo $groupName; ?>&api=meeting_schedule",{
    title:title,
    datetime:dt,
    link:link, // ADDED: Send link to the backend
    user:userName
},function(r){
    if(r.status==="ok"){
        $("#meetingTitle,#meetingDateTime,#meetingLink").val(""); // Clear all inputs
        loadMeeting();
    } else alert(r.message || "Error scheduling meeting");
},"json").fail(function(){
        alert("Network error scheduling meeting.");
    });
});

// Delete meeting logic
$(document).on("click", ".delete-meeting", function(){
    if(!confirm("Are you sure you want to delete this scheduled meeting?")) return;
    const index = $(this).data("index");
    $.post("?group="+groupName+"&api=meeting_delete_scheduled",{index:index},function(r){
        if(r.status==="ok") loadMeeting(); 
        else alert(r.message || "Error deleting meeting"); 
    },"json");
});

loadMeeting();

// ------------------- Games -------------------
let quizzes=[];
function loadGames(){
$.get("?group=<?php echo $groupName; ?>&api=quiz_get",function(data){
quizzes=data.quizzes||[];
let html="<h4>Quiz:</h4>";
quizzes.forEach((q,i)=> html+=`<div>${i+1}. ${q.question} by ${q.user} <button class="delete-quiz" data-index="${i}">Delete</button></div>`);
$("#gamesList").html(html);
},"json");
}
$("#quizAddBtn").click(function(){
let q=$("#quizQuestion").val().trim();
let a=$("#quizAnswer").val().trim();
let r=$("#quizReview").val().trim();
if(!q || !a) return alert("Fill question and answer");
$.post("?group=<?php echo $groupName; ?>&api=quiz_add",{question:q,answer:a,review:r,user:userName},function(r){ if(r.status==="ok"){ $("#quizQuestion,#quizAnswer,#quizReview").val(""); loadGames(); } else alert("Error"); },"json");
});
$(document).on('click','.delete-quiz',function(){
let index=$(this).data('index');
if(confirm('Delete question?')) $.post("?group=<?php echo $groupName; ?>&api=quiz_delete",{index:index},function(r){ if(r.status==="ok") loadGames(); else alert("Error"); },"json");
});

// ------------------- Quiz Logic with Review Button & Styled Back -------------------
let currentQuizIndex = 0;
let score = 0;

$("#quizStartBtn").click(function() {
    if (quizzes.length === 0) return alert("No quiz questions available!");
    currentQuizIndex = 0;
    score = 0;

    $("#gamesList, #quizStartBtn, #quizAddBtn, #quizQuestion, #quizAnswer, #quizReview").hide();
    $("#quizContainer").show();
    showQuizQuestion();
});

function showQuizQuestion() {
    if (currentQuizIndex >= quizzes.length) {
        $("#quizQuestionDisplay").html(`<b>Quiz Finished!</b><br>Your score: ${score} / ${quizzes.length}`);
        $("#quizUserAnswer, #quizSubmitBtn").hide();

        // Show Back button
        if(!$("#backToGamesBtn").length) {
            $("#quizContainer").append('<button id="backToGamesBtn" class="quiz-btn">Back to Games</button>');
            $("#backToGamesBtn").click(endQuiz);
        }
        return;
    }

    const q = quizzes[currentQuizIndex];
    $("#quizQuestionDisplay").html(`<b>Q${currentQuizIndex+1}:</b> ${q.question}`);
    $("#quizUserAnswer").val("").show();
    $("#quizSubmitBtn").show();
    $("#quizFeedback").html("");
    $("#quizReviewBtn")?.remove(); // remove old review button if any
}

$("#quizSubmitBtn").click(function() {
    const userAnswer = $("#quizUserAnswer").val().trim();
    if(!userAnswer) return alert("Enter your answer!");

    const q = quizzes[currentQuizIndex];
    const correctAnswer = q.answer.trim();
    score += userAnswer.toLowerCase() === correctAnswer.toLowerCase() ? 1 : 0;

    // Show feedback
    let feedback = userAnswer.toLowerCase() === correctAnswer.toLowerCase() 
        ? `<span style="color:green">Correct!</span>` 
        : `<span style="color:red">Wrong! Correct answer: ${correctAnswer}</span>`;

    $("#quizFeedback").html(feedback);

    // If review exists, show a "View Review" button
    if(q.review){
        if(!$("#quizReviewBtn").length){
            $("#quizFeedback").append('<br><button id="quizReviewBtn" class="quiz-btn">View Review</button>');
            $("#quizReviewBtn").click(function(){
                $(this).hide();
                $("#quizFeedback").append(`<div class="quiz-review-box">Note that: ${q.review}</div>`);
            });
        }
    }

    currentQuizIndex++;
    setTimeout(showQuizQuestion, 2500);
});

function endQuiz() {
    $("#quizContainer").hide();
    $("#gamesList, #quizStartBtn, #quizAddBtn, #quizQuestion, #quizAnswer, #quizReview").show();
    $("#backToGamesBtn").remove();
    $("#quizReviewBtn")?.remove();
    loadGames();
}

loadGames();

// ------------------- Members -------------------
async function loadMembers(){
try{
// FIX: Use external loadMembers.php (DB-backed)
const res = await fetch("loadMembers.php?groupName=<?php echo urlencode($groupName); ?>");
const data = await res.json();
let html="<h4>Members:</h4>";
// loadMembers.php returns {email, status}
if(data.length>0) data.forEach(m=> html+=`<div>${m.email} (${m.status})</div>`); else html+="<p>No members yet</p>";
document.getElementById("membersList").innerHTML=html;
}catch(e){ console.error("Error loading members:", e);}
}

document.getElementById("addMemberBtn").addEventListener("click", async ()=>{
const email=document.getElementById("memberEmailInput").value.trim();
if(!email) return;

try{
// FIX: Use external addMember.php (DB-backed), sending JSON body
const res = await fetch("addMember.php",{
method:"POST",
headers:{"Content-Type":"application/json"},
body:JSON.stringify({groupName: "<?php echo $groupName; ?>", email: email})
});

const data = await res.json();
document.getElementById("memberMsg").innerText=data.message||(data.success?"Member added successfully.":"Error.");
document.getElementById("memberMsg").style.color=data.success?"green":"red";

if(data.success) {
    document.getElementById("memberEmailInput").value="";
    loadMembers();
}

}catch(e){ 
    console.error("Error adding member:", e); 
    document.getElementById("memberMsg").innerText="Network Error adding member."; 
    document.getElementById("memberMsg").style.color="red";
}
});
loadMembers();

// ------------------- Init -------------------
$("#chat").addClass("active");
</script>
</body>
</html>
