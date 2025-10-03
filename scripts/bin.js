document.addEventListener('DOMContentLoaded', () => {
  console.log("Loaded trash notes from DB:", trashNotes);
  const container = document.getElementById('binNotesContainer');

  if (!container) {
    console.error('Trash container not found');
    return;
  }

  // Fetch trashed notes from localStorage
  const localTrashNotes = JSON.parse(localStorage.getItem('trashNotes') || '[]');
  console.log("Loaded trash notes from localStorage:", localTrashNotes);

  // Combine database trash notes and localStorage trash notes
  const combinedTrashNotes = [...trashNotes, ...localTrashNotes];

  if (!combinedTrashNotes.length) {
    container.innerHTML = "<p>No notes in trash.</p>";
    return;
  }

  container.innerHTML = ''; // Clear any existing content

  combinedTrashNotes.forEach(note => {
    const noteCard = document.createElement('div');
    noteCard.className = 'note-card';
    // Add a data attribute to distinguish source: db or local
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
});

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
