<!-- Home Page (HTML) -->
<?php
// THIS IS THE LINE YOU NEED TO ADD AT THE VERY TOP
require_once '../pages/session_check.php'; // Make sure this path is correct!
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="NotesVault - Organize your academic notes and PYQs semester-wise"
    />
    <title>NotesVault - Your Organized Learning Companion</title>

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
     <link rel="stylesheet" href="../styling/chatbot.css" />
    <link rel="stylesheet" href="../styling/home.css" />
    <link rel="stylesheet" href="../styling/base.css" />
    <link rel="stylesheet" href="../styling/variables.css" />
  </head>

  <body>
    <!-- Header -->
    <?php include '../components/header.php'; ?>

    <!-- Main Content -->
    <main id="main-content">
      <section class="hero">
        <div class="container">
          <div class="hero-content">
            <p class="welcome-text">Welcome Back</p>
            <h1>Your Organized <span class="text-accent">Learning Companion...</span></h1>
            <p class="subtext">
              Keep your study notes and PYQs organized, easy to find, and always
              just a click away with NotesVault!
            </p>
            <div class="cta-buttons">
              <a href="notes.php" class="btn btn-secondary"><i class="fas fa-search"></i>Browse Notes</a>
              <a href="upload.php" class="btn btn-primary"><i class="fas fa-upload"></i>Upload Notes</a>
            </div>
          </div>
          <div class="search-card">
            <div class="search-header">
              <p>Notes Organized by <span id="typewriter">Subject</span></p>
            </div>
            <form
              action="notes.php"
              method="get"
              class="search-form"
            >
              <div class="search-input">
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                >
                  <circle cx="11" cy="11" r="8"></circle>
                  <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input
                  type="text"
                  name="search"
                  placeholder="Search"
                  aria-label="Search notes"
                />
              </div>
              <input type="submit" hidden />
            </form>
          </div>
        </div>
      </section>

      <!-- Features Section -->
      <section class="features" data-aos="fade-up">
        <div class="container">
          <h2 class="section-title">Key Features</h2>
          <p class="section-subtitle">
            Powerful tools to organize your academic journey!
          </p>

          <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card" data-feature="upload">
              <div class="feature-icon" >
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
              <h3>Easy Upload</h3>
              <p>
                Add your notes quickly and effortlessly with drag & drop
                functionality
              </p>
              <div class="feature-hover-effect"></div>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card" data-feature="organize">
              <div class="feature-icon">
                <i class="fas fa-folder-tree"></i>
              </div>
              <h3>Sort The Chaos</h3>
              <p>Neatly arrange all your notes with smart categorization</p>
              <div class="feature-hover-effect"></div>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card" data-feature="search">
              <div class="feature-icon">
                <i class="fas fa-tags"></i>
              </div>
              <h3>Search By Tags</h3>
              <p>Find notes instantly using subject tags and keywords</p>
              <div class="feature-hover-effect"></div>
            </div>

            <!-- Feature 4 -->
            <div class="feature-card" data-feature="sync">
              <div class="feature-icon">
                <i class="fas fa-sync-alt"></i>
              </div>
              <h3>Cross-Device Sync</h3>
              <p>Access your notes from any device, anywhere</p>
              <div class="feature-hover-effect"></div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>
    <?php include '../components/chatbot.php'; ?>

    <!-- JavaScript -->
    <script src="../scripts/header.js" defer></script>
    <script src="../scripts/script.js" defer></script>
    <script src="../scripts/chatbot.js" defer></script>
    <script>
      // Example JavaScript code to demonstrate functionality
      document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.querySelector('.search-form');
        const searchInput = document.querySelector('input[name="search"]');

        searchForm.addEventListener('submit', function (e) {
          e.preventDefault();
          const query = searchInput.value.trim().toLowerCase();
          if (query) {
            // Check for special keywords
            if (query === 'dashboard') {
              window.location.href = 'dashboard.php';
            } else if (query === 'groups') {
              window.location.href = 'groups.php';
            } else if (query === 'notes') {
              window.location.href = 'notes.php';
            } else {
            }}
          })});

      // Fetch example data from the API and log it
      fetch('http://localhost/notesvault/backend/api/')
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => console.error('Error fetching data:', error));
    </script>
  </body>
</html>
