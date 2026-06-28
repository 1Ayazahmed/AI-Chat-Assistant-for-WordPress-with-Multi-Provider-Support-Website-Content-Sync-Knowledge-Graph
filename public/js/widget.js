(function () {
  'use strict';

  if (typeof ceacConfig === 'undefined') return;

  const C = ceacConfig;
  const root = document.getElementById('ceac-widget-root');
  if (!root) return;

  const $ = (sel) => root.querySelector(sel);
  const launcher = $('#ceac-launcher');
  const panel = $('#ceac-panel');
  const consent = $('#ceac-consent');
  const chat = $('#ceac-chat');
  const messages = $('#ceac-messages');
  const input = $('#ceac-input');
  const sendBtn = $('#ceac-send');
  const typing = $('#ceac-typing');
  const fallbackOpts = $('#ceac-fallback-options');

  let isOpen = false;
  let conversationId = null;
  let hasConsent = !C.cookieConsent || localStorage.getItem('ceac_consent') === '1';

  function init() {
    root.style.setProperty('--ceac-offset-x', C.offsetX + 'px');
    root.style.setProperty('--ceac-offset-y', C.offsetY + 'px');

    $('.ceac-avatar').src = C.avatarUrl;
    $('.ceac-bot-name').textContent = C.botName;
    $('.ceac-powered').textContent = C.strings.powered_by;
    input.placeholder = C.strings.placeholder;

    if (C.statusIndicator) {
      $('.ceac-status-text').textContent = C.strings.online;
    } else {
      $('.ceac-status').style.display = 'none';
    }

    if (C.cookieConsent) {
      $('.ceac-consent-title').textContent = C.strings.consent_title;
      $('.ceac-consent-text').textContent = C.strings.consent_text;
      $('#ceac-consent-accept').textContent = C.strings.consent_accept;
      $('#ceac-consent-decline').textContent = C.strings.consent_decline;
    }

    bindEvents();
    setupTriggers();
  }

  function bindEvents() {
    launcher.addEventListener('click', togglePanel);
    $('#ceac-minimize').addEventListener('click', closePanel);
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
    input.addEventListener('input', autoResize);

    if (C.cookieConsent) {
      $('#ceac-consent-accept').addEventListener('click', () => {
        localStorage.setItem('ceac_consent', '1');
        hasConsent = true;
        showChat();
      });
      $('#ceac-consent-decline').addEventListener('click', closePanel);
    }
  }

  function setupTriggers() {
    if (C.clickToOpenOnly) return;

    if (C.autoOpenDelay > 0) {
      setTimeout(() => { if (!isOpen) openPanel(); }, C.autoOpenDelay * 1000);
    }

    if (C.scrollDepth > 0) {
      let triggered = false;
      window.addEventListener('scroll', () => {
        if (triggered) return;
        const depth = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
        if (depth >= C.scrollDepth) {
          triggered = true;
          openPanel();
        }
      });
    }

    if (C.exitIntent) {
      document.addEventListener('mouseout', (e) => {
        if (e.clientY < 10 && !isOpen) openPanel();
      });
    }
  }

  function togglePanel() {
    isOpen ? closePanel() : openPanel();
  }

  function openPanel() {
    isOpen = true;
    panel.style.display = 'flex';
    panel.className = 'ceac-panel ceac-animate-' + C.animation;
    launcher.querySelector('.ceac-launcher-icon').style.display = 'none';
    launcher.querySelector('.ceac-launcher-close').style.display = 'block';

    if (hasConsent) {
      showChat();
    } else {
      consent.style.display = 'flex';
      chat.style.display = 'none';
    }
  }

  function closePanel() {
    isOpen = false;
    panel.style.display = 'none';
    launcher.querySelector('.ceac-launcher-icon').style.display = 'block';
    launcher.querySelector('.ceac-launcher-close').style.display = 'none';
  }

  async function showChat() {
    consent.style.display = 'none';
    chat.style.display = 'flex';

    if (messages.children.length === 0) {
      await loadGreeting();
    }
    input.focus();
  }

  async function loadGreeting() {
    try {
      const res = await fetch(C.apiUrl + '/greeting?page_url=' + encodeURIComponent(C.pageUrl) + '&language=' + C.language, {
        headers: { 'X-WP-Nonce': C.nonce }
      });
      const data = await res.json();

      if (!data.online && C.businessHours) {
        showOfflineForm();
        return;
      }

      addMessage(data.greeting, 'bot');
    } catch (e) {
      addMessage(C.strings.online === 'Online' ? 'Welcome! How can I help you?' : 'مرحباً! كيف يمكنني مساعدتك؟', 'bot');
    }
  }

  function showOfflineForm() {
    const form = $('#ceac-offline-form');
    form.style.display = 'block';
    $('.ceac-offline-msg').textContent = C.offlineMessage;
    $('#ceac-offline-email').placeholder = C.strings.email_label;
    $('#ceac-offline-submit').textContent = C.strings.offline_submit;
    $('.ceac-status-dot').classList.add('offline');
    $('.ceac-status-text').textContent = C.strings.offline;
    $('.ceac-input-area').style.display = 'none';

    $('#ceac-offline-submit').addEventListener('click', async () => {
      const email = $('#ceac-offline-email').value;
      const msg = $('#ceac-offline-message').value;
      if (!email) return;

      await fetch(C.apiUrl + '/escalate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce },
        body: JSON.stringify({
          conversation_id: conversationId || 0,
          method: 'leave_message',
          user_email: email,
          message: msg
        })
      });
      addMessage('Thank you! We will get back to you soon.', 'bot');
      form.style.display = 'none';
    });
  }

  async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    autoResize();
    addMessage(text, 'user');
    sendBtn.classList.add('ceac-pulse');
    setTimeout(() => sendBtn.classList.remove('ceac-pulse'), 400);

    if (C.hapticsEnabled && navigator.vibrate) navigator.vibrate(10);
    if (C.soundEnabled) playSound();

    showTyping(true);
    fallbackOpts.style.display = 'none';

    try {
      const res = await fetch(C.apiUrl + '/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce },
        body: JSON.stringify({
          session_id: C.sessionId,
          message: text,
          page_url: C.pageUrl,
          language: C.language,
          conversation_id: conversationId
        })
      });

      const data = await res.json();
      showTyping(false);

      if (data.success) {
        conversationId = data.conversation_id;
        const delay = data.typing_delay || 500;
        setTimeout(() => {
          addMessage(data.message, 'bot');
          if (data.is_fallback && data.options) {
            showFallbackOptions(data.options);
          }
        }, Math.min(delay, 2000));
      } else {
        addMessage(data.error || 'Sorry, something went wrong. Please try again.', 'bot');
      }
    } catch (e) {
      showTyping(false);
      addMessage('Connection error. Please check your internet and try again.', 'bot');
    }
  }

  function addMessage(text, role) {
    const div = document.createElement('div');
    div.className = 'ceac-message ceac-message-' + role;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function showTyping(show) {
    typing.style.display = show ? 'flex' : 'none';
    if (show) messages.scrollTop = messages.scrollHeight;
  }

  function showFallbackOptions(options) {
    fallbackOpts.innerHTML = '';
    fallbackOpts.style.display = 'flex';
    options.forEach(opt => {
      const btn = document.createElement('button');
      btn.className = 'ceac-fallback-btn';
      btn.textContent = opt.label;
      btn.addEventListener('click', () => handleFallbackAction(opt.action));
      fallbackOpts.appendChild(btn);
    });
  }

  async function handleFallbackAction(action) {
    fallbackOpts.style.display = 'none';
    switch (action) {
      case 'rephrase':
        input.focus();
        addMessage('Please rephrase your question and I\'ll do my best to help.', 'bot');
        break;
      case 'show_topics':
        addMessage('Here are topics I can help with:\n• Money Transfers\n• Currency Exchange\n• AML/Compliance\n• Contact & Support\n• Exchange Rates', 'bot');
        break;
      case 'escalate':
      case 'leave_message':
        if (conversationId) {
          await fetch(C.apiUrl + '/escalate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce },
            body: JSON.stringify({ conversation_id: conversationId, method: action })
          });
          addMessage('Your request has been forwarded to our support team. We\'ll be in touch shortly.', 'bot');
        }
        break;
    }
  }

  function autoResize() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 100) + 'px';
  }

  function playSound() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = 800;
      gain.gain.value = 0.05;
      osc.start();
      osc.stop(ctx.currentTime + 0.1);
    } catch (e) { /* silent */ }
  }

  init();
})();
