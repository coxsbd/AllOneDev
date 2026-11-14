<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AllOne AI — your creative workspace</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
  <div class="layout">
    <aside class="sidebar">
      <a class="brand" href="{{ route('chat.index') }}"><span class="brand-mark">✳</span> allone<span class="brand-ai">.ai</span></a>
      <p class="side-label">WORKSPACE</p>
      <nav class="tools" aria-label="AI tools">
        <button class="tool active" data-mode="chat"><span>✳</span> AI Chat</button>
        <button class="tool" data-mode="write"><span>✎</span> Writing Assistant</button>
        <button class="tool" data-mode="summarize"><span>≡</span> Summarizer</button>
      </nav>
      <div class="sidebar-bottom"><div class="privacy"><b>◈ &nbsp;Private by design</b><p>Your provider API key stays in the Python service environment.</p></div><small>ALLONE AI · PERSONAL WORKSPACE</small></div>
    </aside>
    <main class="main">
      <header class="topbar"><span>Workspace <i>/</i> <b id="breadcrumb">AI Chat</b></span><span class="status"><i></i> Ready to help</span></header>
      <section class="workspace">
        <div class="heading"><div class="heading-icon" id="heading-icon">✳</div><div><p class="eyebrow">YOUR CREATIVE WORKSPACE</p><h1 id="heading">AI Chat</h1><p class="subtitle" id="subtitle">Ask anything, explore ideas, and get clear answers.</p></div></div>
        <div id="welcome" class="welcome"><h2>What can I help you with?</h2><p>Choose a suggestion or write your own prompt.</p><div class="suggestions" id="suggestions"></div></div>
        <div id="messages" class="messages" aria-live="polite"></div>
        <div id="error" class="error" role="alert" hidden></div>
        <form id="composer" class="composer"><textarea id="prompt" maxlength="12000" rows="2" placeholder="Message AllOne AI…" aria-label="Your message"></textarea><div class="composer-bottom"><span><kbd>Enter</kbd> to send · <kbd>Shift + Enter</kbd> for a new line</span><button id="send" aria-label="Send message">↑</button></div></form>
        <p class="disclaimer">AI can make mistakes. Check important information.</p>
      </section>
    </main>
  </div>
  <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
