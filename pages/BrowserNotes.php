<?php
// THIS IS THE LINE YOU NEED TO ADD AT THE VERY TOP
require_once '../pages/session_check.php'; // Make sure this path is correct!
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Browse Notes - NotesVault</title>

    <link rel="icon" href="favicon.png" type="image/x-icon" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../styles.css" />
    <link rel="stylesheet" href="../styling/overview.css" />
  </head>

  <style>
    /* Color Palette */
    :root {
      --primary: #163d3b;
      --primary-light: #dff8f8;
      --secondary: #4a6fa5;
      --accent: #ff7e5f;
      --light-bg: #f4f7f2;
      --text-dark: #2d3748;
      --text-medium: #4a5568;
      --text-light: #718096;
      --white: #ffffff;
      --black: #000000;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      line-height: 1.6;
      color: var(--text-medium);
      background-color: #f9fafb;
      margin: 0;
      padding: 0;
    }

    /* Header */
    header {
      background-color: #ffffff;
      padding: 15px 50px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-weight: bold;
      font-size: 1.5em;
      color: #333;
    }

    nav ul {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 30px;
    }

    nav ul li a {
      text-decoration: none;
      color: #555;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    nav ul li a:hover {
      color: #007bff;
    }

    .sign-up-btn {
      background-color: #4caf50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1em;
      transition: background-color 0.3s ease;
    }

    .sign-up-btn:hover {
      background-color: #45a049;
    }

    /* Main Content */
    .browse-notes-container {
      background: url("../assets/index/images/back.jpg") center/cover no-repeat;
      max-width: 1400px;
      margin: 2rem auto;
      padding: 0 2rem;
    }

    .browse-notes-container h1 {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--primary);
      margin: 1.5rem 0;
      position: relative;
      display: inline-block;
    }

    /* Notes Grid */
    .notes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    /* Notes Card Design */
    .note-card {
      background: var(--white);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
      border: 1px solid #e5eaf0;
      padding: 1.75rem;
      text-align: left;
      position: relative;
    }

    .note-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
      border-color: var(--primary-light);
    }

    .note-card h3 {
      margin: 0 0 1rem 0;
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--black);
      line-height: 1.4;
    }

    .note-card p {
      margin: 0.75rem 0;
      font-size: 1.05rem;
      color: var(--text-medium);
      line-height: 1.6;
    }

    .note-card p strong {
      color: var(--text-dark);
      font-weight: 700;
    }

    /* Action Buttons (View Button) */
    .actions {
      margin-top: auto;
      display: flex;
      justify-content: space-between;
      gap: 1rem;
    }

    .view-button {
      flex: 1;
      min-width: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.6rem 0;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      border: 1px solid transparent;
      height: 40px;
      box-sizing: border-box;
    }

    .view-button {
      background-color: var(--primary-light);
      color: var(--primary);
      border: 1px solid var(--primary-light);
    }

    .view-button:hover {
      background-color: var(--primary);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    .delete-button, .trash-button {
      border-radius: 1.2vh;
      width: 15vh;
      background-color: #0f2a28;
      color: #abc1e2;
      border: none;
      cursor: pointer;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.4rem 0;
      transition: background-color 0.3s ease;
    }

    .delete-button:hover, .trash-button:hover {
      background-color: #07422a;
    }

    /* Responsive Design (Media Queries) */
    @media (max-width: 768px) {
      .note-card-footer {
        flex-direction: column;
      }

      .action-btn:first-child {
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
      }

      .modal-content {
        padding: 1.75rem;
        width: 95%;
      }

      .modal-meta {
        grid-template-columns: 1fr;
      }

      .modal-footer {
        flex-direction: column-reverse;
        gap: 0.75rem;
      }

      .download-btn {
        width: 100%;
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .note-meta {
        grid-template-columns: 1fr;
      }

      .modal-content {
        padding: 1.5rem 1.25rem;
      }

      .modal-header h2 {
        font-size: 1.5rem;
      }
    }
  </style>

  <body>
    <header>
      <div id="header">
        <div class="hamburger-wrapper">
          <!-- Hamburger icon -->
          <button class="hamburger-menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
          </button>

          <!-- Sidebar menu (hidden by default, visible on hover) -->
          <div class="hover-sidebar">
            <ul>
              <li><a href="/pages/overview.html">Overview</a></li>
              <li><a href="/pages/features.html">Features</a></li>
              <li><a href="#">Resources</a></li>
              <li><a href="/pages/bin.php">Trash Bin</a></li>
            </ul>
          </div>
        </div>
        <div id="header-title-box">
          <p id="header-title">NotesVault</p>
        </div>

        <!-- Navigation Menu -->
        <div id="header-navigation" class="nav-menu">
          <a href="overview.html">Overview</a>
          <a href="/">Student Account</a>
          <a href="about.html">About</a>
          <a href="/">Features</a>
        </div>

        <div id="header-signup-box">
          <p>Sign Up</p>
        </div>
      </div>
    </header>

    <main class="browse-notes-container">
       <div class="notes-header">
      <h1>Browse All Notes</h1>
      </div>

      <div class="notes-grid" id="notesGrid">
        <div class="loading-message" id="loadingMessage">Loading notes...</div>
        <div class="no-notes-message" id="noNotesMessage" style="display: none">
          No notes found.
        </div>
      </div>
    </main>

    <div id="noteDetailModal" class="modal">
      <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2 id="modalNoteTitle"></h2>
        <p><strong>Branch:</strong> <span id="modalNoteBranch"></span></p>
        <p><strong>Semester:</strong> <span id="modalNoteSemester"></span></p>
        <p>
          <strong>Description:</strong> <span id="modalNoteDescription"></span>
        </p>
        <p>
          <strong>Uploaded By:</strong> <span id="modalNoteUploader"></span>
        </p>
        <p>
          <strong>Upload Date:</strong> <span id="modalNoteUploadDate"></span>
        </p>
        <a id="modalDownloadButton" class="download-button" href="#" download>
          <i class="fas fa-download"></i> Download Note
        </a>
      </div>
    </div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const notesGrid = document.getElementById('notesGrid');
    const loadingMessage = document.getElementById('loadingMessage');
    const noNotesMessage = document.getElementById('noNotesMessage');
    const noteDetailModal = document.getElementById('noteDetailModal');
    const closeModalButton = noteDetailModal.querySelector('.close-button');
    const modalNoteTitle = document.getElementById('modalNoteTitle');
    const modalNoteBranch = document.getElementById('modalNoteBranch');
    const modalNoteSemester = document.getElementById('modalNoteSemester');
    const modalNoteDescription = document.getElementById('modalNoteDescription');
    const modalNoteUploader = document.getElementById('modalNoteUploader');
    const modalNoteUploadDate = document.getElementById('modalNoteUploadDate');
    const modalDownloadButton = document.getElementById('modalDownloadButton');

    const dummyNotes = [
      {
        _id: 'note1',
        title: 'Data Structures & Algorithms Basics',
        branch: 'Computer Science',
        semester: '3rd',
        description: 'Comprehensive notes on data structures and algorithms.',
        uploader: 'Alice Smith',
        uploadDate: '2023-03-15',
        filePath: 'http://example.com/notes/dsa_basics.pdf'
      },
      {
        _id: 'note2',
        title: 'Digital Electronics',
        branch: 'Electronics Engineering',
        semester: '4th',
        description: 'Covers logic gates and digital circuits.',
        uploader: 'Bob Johnson',
        uploadDate: '2023-04-20',
        filePath: 'http://example.com/notes/digital_electronics.pdf'
      },
      {
        _id: 'note3',
        title: 'Thermodynamics for Mechanical Engineers',
        branch: 'Mechanical Engineering',
        semester: '5th',
        description: 'Concepts of thermodynamics, laws, cycles, and their applications in various systems.',
        uploader: 'Charlie Brown',
        uploadDate: '2023-05-10',
        filePath: 'http://example.com/notes/thermodynamics.pdf',
      },
      {
        _id: 'note4',
        title: 'Object-Oriented Programming with Java',
        branch: 'Information Technology',
        semester: '3rd',
        description: 'Introduction to OOP principles using Java.',
        uploader: 'Alice Smith',
        uploadDate: '2023-06-01',
        filePath: 'http://example.com/notes/oop_java.pdf',
      },
      {
        _id: 'note5',
        title: 'Linear Algebra and Its Applications',
        branch: 'Mathematics',
        semester: '3rd',
        description: 'Explores vector spaces, linear transformations, eigenvalues.',
        uploader: 'Ritika Mehta',
        uploadDate: '2023-09-12',
        filePath: 'http://example.com/notes/linear-algebra.pdf',
      },
    ];

    function initializeNotes() {
      const existingNotes = JSON.parse(localStorage.getItem('notes') || '[]');
      if (existingNotes.length === 0) {
        localStorage.setItem('notes', JSON.stringify(dummyNotes));
        localStorage.setItem('trashNotes', JSON.stringify([]));
      }
    }

    async function fetchNotes() {
      loadingMessage.style.display = 'block';
      noNotesMessage.style.display = 'none';
      notesGrid.innerHTML = '';

      try {
        await new Promise((res) => setTimeout(res, 500));
        const notes = JSON.parse(localStorage.getItem('notes') || '[]');

        if (notes.length === 0) {
          noNotesMessage.style.display = 'block';
        } else {
          notes.forEach((note) => {
            const noteCard = createNoteCard(note);
            notesGrid.appendChild(noteCard);
          });
        }
      } catch (error) {
        console.error('Error fetching notes:', error);
        notesGrid.innerHTML = `<p class="error-message">Failed to load notes.</p>`;
      } finally {
        loadingMessage.style.display = 'none';
      }
    }

    function createNoteCard(note) {
      const noteCard = document.createElement('div');
      noteCard.classList.add('note-card');
      noteCard.setAttribute('data-note-id', note._id);

      noteCard.innerHTML = `
        <div class="note-card-body">
          <h3>${note.title}</h3>
          <p><strong>Branch:</strong> ${note.branch}</p>
          <p><strong>Semester:</strong> ${note.semester}</p>
          <p><strong>Uploaded By:</strong> ${note.uploader}</p>
          <div class="actions">
            <button class="view-button"><i class="fas fa-eye"></i> View</button>
            <button class="trash-button"><i class="fas fa-trash-alt"></i> Move to Trash</button>
            <button class="delete-button"><i class="fas fa-trash"></i> Delete Permanently</button>
          </div>
        </div>
      `;

      const viewButton = noteCard.querySelector('.view-button');
      viewButton.addEventListener('click', () => openNoteDetailModal(note));

      const trashButton = noteCard.querySelector('.trash-button');
      trashButton.addEventListener('click', () => {
        moveNoteToTrash(note._id);
        fetchNotes();
      });

      const deleteButton = noteCard.querySelector('.delete-button');
      deleteButton.addEventListener('click', () => {
        deleteNotePermanently(note._id);
        fetchNotes();
      });

      return noteCard;
    }

    function moveNoteToTrash(noteId) {
      const notes = JSON.parse(localStorage.getItem('notes') || '[]');
      const trash = JSON.parse(localStorage.getItem('trashNotes') || '[]');
      const noteToTrash = notes.find((n) => n._id === noteId);

      if (noteToTrash) {
        const updatedNotes = notes.filter((n) => n._id !== noteId);

        // Prevent duplicates in trash
        const alreadyTrashed = trash.some((n) => n._id === noteId);
        if (!alreadyTrashed) {
          trash.push(noteToTrash);
        }

        localStorage.setItem('notes', JSON.stringify(updatedNotes));
        localStorage.setItem('trashNotes', JSON.stringify(trash));
      }
    }

    function deleteNotePermanently(noteId) {
      const notes = JSON.parse(localStorage.getItem('notes') || '[]');
      const trash = JSON.parse(localStorage.getItem('trashNotes') || '[]');

      const updatedNotes = notes.filter((n) => n._id !== noteId);
      const updatedTrash = trash.filter((n) => n._id !== noteId);

      localStorage.setItem('notes', JSON.stringify(updatedNotes));
      localStorage.setItem('trashNotes', JSON.stringify(updatedTrash));
    }

    function openNoteDetailModal(note) {
      modalNoteTitle.textContent = note.title;
      modalNoteBranch.textContent = note.branch;
      modalNoteSemester.textContent = note.semester;
      modalNoteDescription.textContent = note.description;
      modalNoteUploader.textContent = note.uploader;
      modalNoteUploadDate.textContent = new Date(note.uploadDate).toLocaleDateString();
      modalDownloadButton.href = note.filePath;
      modalDownloadButton.setAttribute('download', `${note.title.replace(/\s/g, '_')}.pdf`);
      noteDetailModal.style.display = 'flex';
    }

    function closeNoteDetailModal() {
      noteDetailModal.style.display = 'none';
    }

    closeModalButton.addEventListener('click', closeNoteDetailModal);
    window.addEventListener('click', (event) => {
      if (event.target === noteDetailModal) {
        closeNoteDetailModal();
      }
    });

    initializeNotes();
    fetchNotes();
  });
</script>

  </body>
</html>
