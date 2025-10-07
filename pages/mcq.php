<!-- MCQ Page (HTML) -->
<?php
  // Database connection configuration
  $host = 'localhost';
  $dbname = 'notesvault'; // Change if your database name is different
  $username = 'root'; // Default XAMPP MySQL user
  $password = ''; // Default XAMPP MySQL password (empty)

  try {
      $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
      die("Connection failed: " . $e->getMessage());
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $quizId = $_POST['quizId'];
      $type = $_POST['questionType'];
      if ($type === 'short') {
          $question = $_POST['questionInputShort'];
          $optionA = $_POST['answer'];
          $optionB = '';
          $optionC = '';
          $optionD = '';
          $correct = 'A';
      } else {
          $question = $_POST['questionInputMcq'];
          $optionA = $_POST['optionA'];
          $optionB = $_POST['optionB'];
          $optionC = $_POST['optionC'];
          $optionD = $_POST['optionD'];
          $correct = $_POST['correctOption'];
      }

      $stmt = $pdo->prepare("INSERT INTO mcqs
          (quiz_id, type, question, option_a, option_b, option_c, option_d, correct_option)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$quizId, $type, $question, $optionA, $optionB, $optionC, $optionD, $correct]);

      echo json_encode(["success" => true]);
  }

  if (isset($_GET['quizId'])) {
    $quizId = $_GET['quizId'];
    $stmt = $pdo->prepare("SELECT * FROM mcqs WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $mcqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($mcqs);
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NotesVault - MCQ</title>

    <!-- Favicon -->
    <link
      rel="icon"
      href="../assets/index/images/favicon.png"
      type="image/x-icon"
    />

    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />

    <!-- CSS -->
    <link rel="stylesheet" href="../mcq/mcq.css" />
    <link rel="stylesheet" href="../styling/base.css" />
    <link rel="stylesheet" href="../styling/variables.css" />
  </head>

  <body>
    <!-- Header -->
    <?php include '../components/header.php'; ?>
  <!-- JavaScript -->

    
      <!--section-->
      <section class="quiz-hero">
        <div class="container">
          <h1>Welcome to our mcq generating section</h1>
          <ul class="subtitle">
            <li>Create your own mcq question</li>
            <li>Test as you like</li>
            <li>See your performance</li>
          </ul>
        </div>
      </section>
    <!--section-->
    <div class="mcq-front">
      <div class="button-row">
        <button id="createQuizBtn" class="create-quiz-btn">
          <span>Create Quiz</span>
        </button>
        <div class="mcq-sort">
          <button>
            <span>Newest to Oldest</span>
          </button>
        </div>
      </div>
      <div id="quizCards" class="quiz-cards">
          <!-- Quiz cards will be rendered here by JS -->
      </div>
      <div id="weeklyQuizSection"></div>
    </div>
      <div class="mcq-container" style="display:none;">
        <button id="backBtn" class="back-btn">< Back</button>
        <h1>Create & Practice MCQs</h1>
        <form id="quizCreateForm" class="mcq-form">
          <input type="text" id="quizName" placeholder="Quiz Name" required />
          <textarea id="quizDescription" placeholder="Description"></textarea>
          <button type="submit">Create Quiz</button>
        </form>
        <form id="mcqForm" class="mcq-form">
          <div class="question-type">
            <label><input type="radio" name="questionType" value="short" checked> Short Answer</label>
            <label><input type="radio" name="questionType" value="mcq"> Multiple Choice</label>
          </div>
          <section class="textbox" id="textboxSection">
            <input type="text" id="questionInputShort" placeholder="Enter your question..." required />
            <input type="text" id="answer" placeholder="Enter answer" required />
          </section>
          <section class="quiz" id="mcqInputSection" style="display:none;">
            <input type="text" id="questionInputMcq" placeholder="Enter your question..." required />
            <input type="text" id="optionA" placeholder="Option A" required />
            <input type="text" id="optionB" placeholder="Option B" required />
            <input type="text" id="optionC" placeholder="Option C" required />
            <input type="text" id="optionD" placeholder="Option D" required />
            <select id="correctOption" required>
              <option value="">Select Correct Option</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
            </select>
          </section>

          <button class="addmcq" type="submit">Add Question</button>
        </form>
        <div id="mcqList"></div>
      </div>
    </main>
    <?php include '../components/footer.php'; ?>
    <?php $mcq_js_version = file_exists(__DIR__ . '/../mcq/mcq.js') ? filemtime(__DIR__ . '/../mcq/mcq.js') : time(); ?>
    <script src="../mcq/mcq.js?v=<?php echo $mcq_js_version; ?>"></script>
    <script src="../scripts/header.js" defer></script>
    <script src="../scripts/script.js" defer></script>
  </body>
</html>
