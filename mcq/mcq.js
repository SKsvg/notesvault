document.addEventListener('DOMContentLoaded', function() {
  let mcqFront = document.querySelector('.mcq-front');
  let mcqContainer = document.querySelector('.mcq-container');
  let quizCreateForm = document.getElementById('quizCreateForm');
  let mcqForm = document.getElementById('mcqForm');
  let mcqList = document.getElementById('mcqList');

  // Quiz Logic
  let quizzes = JSON.parse(localStorage.getItem('notesvault-quizzes')) || [];
  // expose debug handle so you can inspect state in console
  window.__mcq = window.__mcq || {};
  window.__mcq.quizzes = quizzes;
  // Migrate old mcqs if exist
  if (quizzes.length === 0 && localStorage.getItem('notesvault-mcqs')) {
    const oldMcqs = JSON.parse(localStorage.getItem('notesvault-mcqs'));
    quizzes.push({id: Date.now(), name: 'Default Quiz', description: 'Migrated MCQs', mcqs: oldMcqs});
    localStorage.removeItem('notesvault-mcqs');
  }
  // Add default quiz if none exists
  if (quizzes.length === 0) {
    const defaultMcqs = [
      {
        question: "What is the capital of France?",
        options: { A: "Paris", B: "London", C: "Berlin", D: "Madrid" },
        correct: "A"
      },
      {
        question: "What is 2 + 2?",
        options: { A: "3", B: "4", C: "5", D: "6" },
        correct: "B"
      }
    ];
    quizzes.push({
      id: Date.now(),
      name: "Sample Quiz",
      description: "Default quiz with sample questions",
      mcqs: defaultMcqs
    });
    saveQuizzes();
  }
  function saveQuizzes() {
    localStorage.setItem('notesvault-quizzes', JSON.stringify(quizzes));
    window.__mcq.quizzes = quizzes;
  }
  let currentQuizId = null;
  let isSortedNewest = false;

  // Render quiz cards on front page
  function renderQuizCards(sortNewestFirst = false) {
    const quizCards = document.getElementById('quizCards');
    if (!quizCards) return;
    let displayQuizzes = sortNewestFirst ? [...quizzes].sort((a,b) => b.id - a.id) : quizzes;
    if (displayQuizzes.length === 0) {
      quizCards.innerHTML = '<p>No quizzes yet. Create your first quiz!</p>';
      return;
    }
    quizCards.innerHTML = displayQuizzes.map(quiz => `
      <div class="quiz-card">
        <div class="quiz-title">${quiz.name}</div>
        <div class="quiz-meta">
          <span class="quiz-date">${quiz.description}</span>
          <span class="quiz-count"><i class="fas fa-question-circle"></i> ${quiz.mcqs.length} Questions</span>
          ${quiz.score !== undefined ? `<span class="quiz-score">Score: ${quiz.score} / ${quiz.mcqs.length}</span>` : ''}
        </div>
        <div class="quiz-buttons">
          <button class="create-quiz-btn add-mcq-btn" data-quiz-id="${quiz.id}">Add MCQ</button>
          <button class="create-quiz-btn take-quiz-btn" data-quiz-id="${quiz.id}">Take Quiz</button>
          <button class="delete-quiz-btn" data-quiz-id="${quiz.id}">Delete Quiz</button>
        </div>
      </div>
    `).join('');
  }

  // Event delegation for card buttons
  document.addEventListener('click', function(e) {
    if (e.target.matches('.add-mcq-btn')) {
      currentQuizId = parseInt(e.target.dataset.quizId);
      window.__mcq.currentQuizId = currentQuizId;
      mcqFront.style.display = 'none';
      mcqContainer.style.display = '';
      quizCreateForm.style.display = 'none';
      mcqForm.style.display = '';
      mcqList.style.display = '';
      renderMCQsForQuiz(currentQuizId);
    } else if (e.target.matches('.take-quiz-btn')) {
      const quizId = parseInt(e.target.dataset.quizId);
      const quiz = quizzes.find(q => q.id == quizId);
      if (!quiz || quiz.mcqs.length === 0) return;
      currentQuizId = quizId;
      window.__mcq.currentQuizId = currentQuizId;
      mcqFront.style.display = 'none';
      mcqContainer.style.display = '';
      quizCreateForm.style.display = 'none';
      mcqForm.style.display = 'none';
      mcqList.style.display = '';
      let score = 0;
      let quizHtml = `<h2>${quiz.name}</h2>`;
      quiz.mcqs.forEach((q, i) => {
        if (q.type === 'short') {
          quizHtml += `<div class="quiz-item">
            <strong>Q${i+1} (Short Answer):</strong> ${q.question}<br>
            <input type="text" name="quiz${i}" placeholder="Your answer">
          </div>`;
        } else {
          quizHtml += `<div class="quiz-item">
            <strong>Q${i+1} (MCQ):</strong> ${q.question}<br>
            <label><input type="radio" name="quiz${i}" value="A"> A: ${q.options.A}</label>
            <label><input type="radio" name="quiz${i}" value="B"> B: ${q.options.B}</label>
            <label><input type="radio" name="quiz${i}" value="C"> C: ${q.options.C}</label>
            <label><input type="radio" name="quiz${i}" value="D"> D: ${q.options.D}</label>
          </div>`;
        }
      });
      quizHtml += '<div class="quiz-submit-row"><button id="submitQuizBtn" class="quiz-btn">Submit Quiz</button><button id="resetQuizBtn" class="quiz-btn">Reset</button></div>';
      mcqList.innerHTML = quizHtml;
      const submitBtn = document.getElementById('submitQuizBtn');
      const resetBtn = document.getElementById('resetQuizBtn');
      if (submitBtn) {
        submitBtn.addEventListener('click', function() {
          score = 0;
          quiz.mcqs.forEach((q, i) => {
            if (q.type === 'short') {
              const input = document.querySelector(`input[name='quiz${i}']`);
              if (input && input.value.trim().toLowerCase() === q.options.A.toLowerCase()) score++;
            } else {
              const selected = document.querySelector(`input[name='quiz${i}']:checked`);
              if (selected && selected.value === q.correct) score++;
            }
          });
          quiz.score = score;
          saveQuizzes();
          mcqList.innerHTML += `<p>Your Score: ${score} / ${quiz.mcqs.length}</p>`;
        });
      }
      if (resetBtn) {
        resetBtn.addEventListener('click', function() {
          document.querySelectorAll('input[type="radio"], input[type="text"]').forEach(input => input.value = '');
          document.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
        });
      }
    } else if (e.target.matches('.delete-quiz-btn')) {
      const quizId = parseInt(e.target.dataset.quizId);
      if (confirm('Are you sure you want to delete this quiz?')) {
        quizzes = quizzes.filter(q => q.id != quizId);
        saveQuizzes();
        renderQuizCards();
      }
    }
  });

  const createQuizBtn = document.getElementById('createQuizBtn');
  if (createQuizBtn) {
    createQuizBtn.onclick = function() {
      mcqFront.style.display = 'none';
      mcqContainer.style.display = '';
      quizCreateForm.style.display = '';
      mcqForm.style.display = 'none';
      mcqList.style.display = 'none';
    };
  }

  // Initial render
  renderQuizCards();

  const sortBtn = document.querySelector('.mcq-sort button');
  if (sortBtn) {
    // Initial text adjustment if needed, but HTML starts with 'Newest to Oldest'
    sortBtn.addEventListener('click', function() {
      isSortedNewest = !isSortedNewest;
      sortBtn.querySelector('span').textContent = isSortedNewest ? 'Newest to Oldest' : 'Oldest to Newest';
      renderQuizCards(isSortedNewest);
    });
  }

  function renderMCQsForQuiz(quizId) {
    const quiz = quizzes.find(q => q.id == quizId);
    if (!quiz) return;
    const list = document.getElementById('mcqList');
    if (!list) return;
    if (quiz.mcqs.length === 0) {
      list.innerHTML = '<p>No questions yet. Add your first!</p>';
      return;
    }
    list.innerHTML = quiz.mcqs.map((q, i) => {
      if (q.type === 'short') {
        return `
          <div class="mcq-item">
            <strong>Q${i+1} (Short Answer):</strong> ${q.question}<br>
            <span>Answer: ${q.options.A}</span>
          </div>
        `;
      } else {
        return `
          <div class="mcq-item">
            <strong>Q${i+1} (MCQ):</strong> ${q.question}<br>
            <span>A: ${q.options.A}</span> | <span>B: ${q.options.B}</span> | <span>C: ${q.options.C}</span> | <span>D: ${q.options.D}</span>
            <span class="correct">Correct: ${q.correct}</span>
          </div>
        `;
      }
    }).join('');
  }

  quizCreateForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('quizName').value.trim();
    const description = document.getElementById('quizDescription').value;
    if (!name) return;
    const newId = Date.now();
    quizzes.push({id: newId, name, description, mcqs: []});
    saveQuizzes();
    window.__mcq.currentQuizId = newId;
    this.reset();
    // open add-question view for newly created quiz
    currentQuizId = newId;
    mcqFront.style.display = 'none';
    mcqContainer.style.display = '';
    quizCreateForm.style.display = 'none';
    mcqForm.style.display = '';
    if (mcqList) mcqList.style.display = '';
    renderMCQsForQuiz(currentQuizId);
    renderQuizCards(isSortedNewest);
  });

  // Toggle sections based on question type
  document.querySelectorAll('input[name="questionType"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const textboxSection = document.getElementById('textboxSection');
      const mcqInputSection = document.getElementById('mcqInputSection');
      const shortIds = ['questionInputShort', 'answer'];
      const mcqIds = ['questionInputMcq', 'optionA', 'optionB', 'optionC', 'optionD', 'correctOption'];
      if (this.value === 'short') {
        textboxSection.style.display = '';
        mcqInputSection.style.display = 'none';
        // enable short inputs
        shortIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) { el.disabled = false; el.required = true; }
        });
        // disable mcq inputs so hidden required won't block form validation
        mcqIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) { el.disabled = true; el.required = false; }
        });
      } else {
        textboxSection.style.display = 'none';
        mcqInputSection.style.display = '';
        // disable short inputs
        shortIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) { el.disabled = true; el.required = false; }
        });
        // enable mcq inputs
        mcqIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) { el.disabled = false; el.required = true; }
        });
      }
    });
  });

  // Initialize input disabled states according to default radio
  (function initQuestionType() {
    const selected = document.querySelector('input[name="questionType"]:checked');
    if (selected) selected.dispatchEvent(new Event('change'));
  })();

  mcqForm.addEventListener('submit', function(e) {
    e.preventDefault();
    // If no quiz is selected (user didn't click Add MCQ), default to the latest quiz
    if (!currentQuizId) {
      if (quizzes && quizzes.length > 0) {
        currentQuizId = quizzes[quizzes.length - 1].id;
        console.log('[MCQ] No quiz selected — defaulting to most recent quiz id', currentQuizId);
        try { alert('No quiz selected — adding to the most recent quiz.'); } catch (e) {}
      } else {
        console.warn('[MCQ] No quizzes exist to add to');
        return;
      }
    }
    const type = document.querySelector('input[name="questionType"]:checked').value;
    let question, options, correct;
    if (type === 'short') {
      question = document.getElementById('questionInputShort').value.trim();
      const answer = document.getElementById('answer').value.trim();
      if (!question || !answer) return;
      options = { A: answer, B: '', C: '', D: '' };
      correct = 'A';
    } else {
      question = document.getElementById('questionInputMcq').value.trim();
      options = {
        A: document.getElementById('optionA').value.trim(),
        B: document.getElementById('optionB').value.trim(),
        C: document.getElementById('optionC').value.trim(),
        D: document.getElementById('optionD').value.trim()
      };
      correct = document.getElementById('correctOption').value;
      if (!question || !options.A || !options.B || !options.C || !options.D || !correct) return;
    }
    const quiz = quizzes.find(q => q.id == currentQuizId);
    if (quiz) {
      quiz.mcqs.push({ type, question, options, correct });
      saveQuizzes();
      window.__mcq.quizzes = quizzes;
      console.log('[MCQ] added question to quiz', currentQuizId, quiz.mcqs[quiz.mcqs.length-1]);
    }
    this.reset();
    // Reset to short answer
    document.querySelector('input[name="questionType"][value="short"]').checked = true;
    document.getElementById('textboxSection').style.display = '';
    document.getElementById('mcqInputSection').style.display = 'none';
    renderMCQsForQuiz(currentQuizId);
    renderQuizCards(isSortedNewest);
  });

  // Back button functionality
  const backBtn = document.getElementById('backBtn');
  if (backBtn) {
    backBtn.addEventListener('click', function() {
      mcqContainer.style.display = 'none';
      mcqFront.style.display = '';
      quizCreateForm.style.display = 'none';
      mcqForm.style.display = 'none';
      mcqList.style.display = 'none';
      currentQuizId = null;
    });
  }

  // Weekly quiz button (if exists)
  const weeklyQuizBtn = document.getElementById('weeklyQuizBtn');
  if (weeklyQuizBtn) {
    weeklyQuizBtn.addEventListener('click', function() {
      const allMcqs = quizzes.flatMap(q => q.mcqs);
      if (allMcqs.length === 0) return;
      const weeklyQuizSection = document.getElementById('weeklyQuizSection');
      let score = 0;
      let quizHtml = '<h2>All Quizzes Combined</h2>';
      allMcqs.forEach((q, i) => {
        quizHtml += `<div class="quiz-item">
          <strong>Q${i+1}:</strong> ${q.question}<br>
          <label><input type="radio" name="quiz${i}" value="A"> A: ${q.options.A}</label>
          <label><input type="radio" name="quiz${i}" value="B"> B: ${q.options.B}</label>
          <label><input type="radio" name="quiz${i}" value="C"> C: ${q.options.C}</label>
          <label><input type="radio" name="quiz${i}" value="D"> D: ${q.options.D}</label>
        </div>`;
      });
      quizHtml += '<button id="submitQuiz">Submit Quiz</button>';
      weeklyQuizSection.innerHTML = quizHtml;
      document.getElementById('submitQuiz').onclick = function() {
        score = 0;
        allMcqs.forEach((q, i) => {
          const selected = document.querySelector(`input[name='quiz${i}']:checked`);
          if (selected && selected.value === q.correct) score++;
        });
        weeklyQuizSection.innerHTML += `<p>Your Score: ${score} / ${allMcqs.length}</p>`;
      };
    });
  }
});
