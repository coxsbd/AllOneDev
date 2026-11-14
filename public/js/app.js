const modes = {
  chat: { title: 'AI Chat', icon: '✳', description: 'Ask anything, explore ideas, and get clear answers.', placeholder: 'Message AllOne AI…', ideas: ['Help me plan a productive week', 'Explain a complex topic simply', 'Give me ideas for a side project'] },
  write: { title: 'Writing Assistant', icon: '✎', description: 'Draft, rewrite, and polish your words.', placeholder: 'Describe what you’d like to write…', ideas: ['Write a friendly project update', 'Improve this paragraph', 'Draft a compelling introduction'] },
  summarize: { title: 'Summarizer', icon: '≡', description: 'Turn long text into the important points.', placeholder: 'Paste text to summarize, or ask a question…', ideas: ['Summarize meeting notes', 'Extract key takeaways', 'Make this text easier to scan'] }
};
let mode = 'chat';
let conversationId = null;
let busy = false;
const $ = (selector) => document.querySelector(selector);
const messages = $('#messages');

function renderIdeas() {
  $('#suggestions').innerHTML = modes[mode].ideas.map((idea) => `<button class="suggestion">${idea}<span>→</span></button>`).join('');
  document.querySelectorAll('.suggestion').forEach((button) => button.addEventListener('click', () => send(button.textContent.replace('→', '').trim())));
}
function setMode(next) {
  mode = next;
  conversationId = null;
  messages.replaceChildren();
  $('#welcome').hidden = false;
  $('#breadcrumb').textContent = modes[mode].title;
  $('#heading').textContent = modes[mode].title;
  $('#heading-icon').textContent = modes[mode].icon;
  $('#subtitle').textContent = modes[mode].description;
  $('#prompt').placeholder = modes[mode].placeholder;
  document.querySelectorAll('.tool').forEach((button) => button.classList.toggle('active', button.dataset.mode === mode));
  renderIdeas();
}
function appendMessage(role, text) {
  const row = document.createElement('article');
  row.className = `message ${role}`;
  const avatar = document.createElement('span'); avatar.className = 'avatar'; avatar.textContent = role === 'assistant' ? '✳' : 'Y';
  const body = document.createElement('div'); body.className = 'message-body';
  const name = document.createElement('b'); name.textContent = role === 'assistant' ? 'AllOne AI' : 'You';
  const content = document.createElement('p'); content.textContent = text;
  body.append(name, content); row.append(avatar, body); messages.append(row); row.scrollIntoView({ behavior: 'smooth', block: 'end' });
}
async function send(text = $('#prompt').value.trim()) {
  if (!text || busy) return;
  busy = true; $('#send').disabled = true; $('#error').hidden = true; $('#welcome').hidden = true;
  $('#prompt').value = ''; appendMessage('user', text);
  const loading = document.createElement('p'); loading.className = 'loading'; loading.textContent = 'AllOne AI is thinking…'; messages.append(loading);
  try {
    const response = await fetch('/chat', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ message: text, mode, conversation_id: conversationId }) });
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || data.error || 'Request failed. Please try again.');
    conversationId = data.conversation_id; loading.remove(); appendMessage('assistant', data.answer);
  } catch (error) {
    loading.remove(); $('#error').textContent = error.message || 'Could not reach the AI service.'; $('#error').hidden = false;
  } finally { busy = false; $('#send').disabled = false; }
}

document.querySelectorAll('.tool').forEach((button) => button.addEventListener('click', () => setMode(button.dataset.mode)));
$('#composer').addEventListener('submit', (event) => { event.preventDefault(); send(); });
$('#prompt').addEventListener('keydown', (event) => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); send(); } });
setMode('chat');
