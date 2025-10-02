document.addEventListener('DOMContentLoaded', () => {
  const chatbotToggleBtn = document.getElementById('chatbot-toggle-btn');
  const chatWidget = document.getElementById('chat-widget');
  const chatCloseBtn = document.getElementById('chat-close-btn');
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const messagesContainer = document.getElementById('chat-messages');

  // --- Event Listeners ---
  chatbotToggleBtn.addEventListener('click', toggleChatWidget);
  chatCloseBtn.addEventListener('click', closeChatWidget);
  chatForm.addEventListener('submit', handleFormSubmit);

  // --- Functions ---
  function toggleChatWidget() {
    // We remove the 'hidden' class to allow CSS animations to work
    if (chatWidget.classList.contains('hidden')) {
      chatWidget.classList.remove('hidden');
      // A small delay to allow the element to be rendered before adding the 'active' class
      setTimeout(() => {
        chatWidget.classList.add('active');
        chatbotToggleBtn.style.display = 'none'; // Hide button when chat is open
      }, 10);
    } else {
      closeChatWidget();
    }
  }

  function closeChatWidget() {
    chatWidget.classList.remove('active');
    chatbotToggleBtn.style.display = 'flex'; // Show button when chat is closed
    // Re-add 'hidden' after the animation completes
    setTimeout(() => {
      chatWidget.classList.add('hidden');
    }, 300); // Must match the transition duration in CSS
  }

  function handleFormSubmit(e) {
    e.preventDefault();
    const userMessage = chatInput.value.trim();

    if (userMessage) {
      appendMessage(userMessage, 'user');
      chatInput.value = '';

      // Simulate bot thinking and get a response
      setTimeout(() => {
        const botResponse = getBotResponse(userMessage);
        appendMessage(botResponse, 'bot');
      }, 1000);
    }
  }

  function appendMessage(message, sender) {
    const messageWrapper = document.createElement('div');
    messageWrapper.classList.add('chat-message', `${sender}-message`);

    const messageParagraph = document.createElement('p');
    // Using innerHTML to allow for links in the bot's response
    messageParagraph.innerHTML = message;
    messageWrapper.appendChild(messageParagraph);
    
    messagesContainer.appendChild(messageWrapper);

    // Scroll to the latest message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // --- Chatbot "Brain" ---
  function getBotResponse(userInput) {
    const input = userInput.toLowerCase();

    // Basic keyword matching
    if (input.includes('hello') || input.includes('hi')) {
      return 'Hello there! How can I assist you with your notes today?';
    }

    if (input.includes('upload') || input.includes('add notes')) {
      return 'You can upload your notes by clicking the "Upload Notes" button on the homepage, or by visiting the <a href="upload.php">Upload Page</a> directly.';
    }

    if (input.includes('find') || input.includes('search') || input.includes('browse')) {
      return 'To find notes, you can use the search bar on the homepage or head over to the <a href="notes.php">Browse Notes</a> page for advanced filtering.';
    }

    if (input.includes('pyq') || input.includes('previous year')) {
      return 'PYQs (Previous Year Questions) are organized alongside the notes. You can find them by searching for a specific subject.';
    }
    
    if (input.includes('thank you') || input.includes('thanks')) {
        return 'You\'re welcome! Let me know if there is anything else I can help with.';
    }

    if (input.includes('help')) {
        return 'I can help you find information on how to upload, search for, or organize your notes and PYQs. What would you like to do?';
    }

    // Default fallback response
    return "I'm sorry, I'm not sure how to answer that. Try asking about uploading, searching, or browsing notes.";
  }
});