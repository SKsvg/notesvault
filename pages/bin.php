<?php
// THIS IS THE LINE YOU NEED TO ADD AT THE VERY TOP
require_once '../pages/session_check.php'; // Make sure this path is correct!
?>

<?php
// Start session and check user login if needed

// Database connection details
$servername = "localhost:3307";
$username = "root";
$password = "insathMYSQL#123";
$dbname = "test3";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch trashed notes from database
$sql = "SELECT id, title, branch, semester, tags, file_path, uploader, upload_date FROM notes WHERE is_trashed = 1 ORDER BY upload_date DESC";
$result = $conn->query($sql);

$trashNotes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $trashNotes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NotesVault - Trash Bin</title>

    <link rel="icon" href="../assets/index/images/favicon.png" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />

    <link rel="stylesheet" href="../styling/variables.css" />
    <link rel="stylesheet" href="../styling/base.css" />
    <link rel="stylesheet" href="../styling/bin.css" />
    <link rel="stylesheet" href="../styling/footer.css" />
  </head>

  <body>
    <?php include '../components/header.php'; ?>

    <main style="padding-top: 4rem;">
      <div class="trash-bin-title-box">
        <h1 class="page-title">Trash Bin</h1>
        <p id="note" style= "font-size: 1.2rem; color: white;">Manage your deleted notes here. You can restore or permanently delete them.</p></div>
      <div id="binNotesContainer" class="notes-grid"></div>

    <script>
      // Pass PHP trash notes data to JS
      const trashNotes = <?php echo json_encode($trashNotes); ?>;

      // Fetch trashed notes from localStorage
      const localTrashNotes = JSON.parse(localStorage.getItem('trashNotes') || '[]');

      // Combine database and localStorage trash notes
      const combinedTrashNotes = [...trashNotes, ...localTrashNotes];

      const container = document.getElementById('binNotesContainer');

      if (!combinedTrashNotes.length) {
        container.innerHTML = "<p>No notes in trash.</p>";
      } else {
        container.innerHTML = ''; // Clear existing content

        combinedTrashNotes.forEach(note => {
          const noteCard = document.createElement('div');
          noteCard.className = 'note-card';
          noteCard.setAttribute('data-note-id', note.id || note._id);
          noteCard.setAttribute('data-note-source', note.id ? 'db' : 'local');
          noteCard.innerHTML = `
            <div class="note-card-body">
              <h3>${note.title}</h3>
              <p>${note.tags || ''}</p>
              <p><strong>Branch:</strong> ${note.branch || ''}</p>
              <p><strong>Semester:</strong> ${note.semester || ''}</p>
              <p><strong>Uploaded By:</strong> ${note.uploader || ''}</p>
              <div class="actions">
                <button class="restore-button" data-action="restore"><i class="fas fa-undo"></i> Restore</button>
                <button class="delete-button" data-action="delete"><i class="fas fa-trash"></i> Delete Permanently</button>
              </div>
            </div>
          `;
          container.appendChild(noteCard);
        });

        // Event delegation for buttons
        container.addEventListener('click', (event) => {
          const button = event.target.closest('button');
          if (!button) return;

          const noteCard = button.closest('.note-card');
          if (!noteCard) return;

          const noteId = noteCard.getAttribute('data-note-id');
          const noteSource = noteCard.getAttribute('data-note-source');
          const action = button.getAttribute('data-action');

          if (action === 'restore') {
            if (noteSource === 'db') {
              restoreNoteFromTrashDB(noteId);
            } else {
              restoreNoteFromTrashLocal(noteId);
            }
          } else if (action === 'delete') {
            if (noteSource === 'db') {
              permanentlyDeleteNoteDB(noteId);
            } else {
              permanentlyDeleteNoteLocal(noteId);
            }
          }
        });
      }

      // Restore note from trash to main notes (database)
      function restoreNoteFromTrashDB(noteId) {
        if (confirm('Are you sure you want to restore this note?')) {
          window.location.href = `restore_note.php?id=${noteId}`;
        }
      }

      // Permanently delete a note from trash (database)
      function permanentlyDeleteNoteDB(noteId) {
        if (confirm('Are you sure you want to permanently delete this note? This action cannot be undone.')) {
          window.location.href = `permanent_delete_note.php?id=${noteId}`;
        }
      }

      // Restore note from trash to main notes (localStorage)
      function restoreNoteFromTrashLocal(noteId) {
        if (confirm('Are you sure you want to restore this note?')) {
          const trash = JSON.parse(localStorage.getItem('trashNotes') || '[]');
          const notes = JSON.parse(localStorage.getItem('notes') || '[]');
          const noteToRestore = trash.find(n => n._id === noteId);
          if (noteToRestore) {
            const updatedTrash = trash.filter(n => n._id !== noteId);
            notes.push(noteToRestore);
            localStorage.setItem('trashNotes', JSON.stringify(updatedTrash));
            localStorage.setItem('notes', JSON.stringify(notes));
            location.reload();
          }
        }
      }

      // Permanently delete a note from trash (localStorage)
      function permanentlyDeleteNoteLocal(noteId) {
        if (confirm('Are you sure you want to permanently delete this note? This action cannot be undone.')) {
          const trash = JSON.parse(localStorage.getItem('trashNotes') || '[]');
          const updatedTrash = trash.filter(n => n._id !== noteId);
          localStorage.setItem('trashNotes', JSON.stringify(updatedTrash));
          location.reload();
        }
      }
     
    </script>
    <script src="../scripts/header.js" defer></script>
    <script src="../scripts/script.js" defer></script>
    <script src="../scripts/bin.js" defer></script>
    
  <?php include '../components/footer.php'; ?>
  </body>
</html>
