<div id="chatbot-container">
  <button id="chatbot-toggle-btn" aria-label="Open Chatbot">
    <i class="fas fa-comment-dots"></i>
  </button>

  <div id="chat-widget" class="hidden">
    <div class="chat-header">
      <h3>NotesVault Assistant</h3>
      <button id="chat-close-btn" aria-label="Close Chatbot">&times;</button>
    </div>
    <div id="chat-messages">
      <div class="chat-message bot-message">
        <p>Hello! 👋 How can I help you navigate NotesVault today? You can ask me about uploading, finding notes, or features.</p>
      </div>
    </div>
    <form id="chat-form">
      <input
        type="text"
        id="chat-input"
        placeholder="Ask a question..."
        autocomplete="off"
        required
      />
      <button type="submit" aria-label="Send Message">
        <i class="fas fa-paper-plane"></i>
      </button>
    </form>
  </div>
</div>