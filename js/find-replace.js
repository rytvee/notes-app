document.addEventListener('DOMContentLoaded', () => {
  const editor = document.getElementById('note-content');
  const findBox = document.getElementById("find-box");
  const replaceBox = document.getElementById("replace-box");
  const mobileFindBox = document.getElementById("mobile-find");
  const mobileReplaceBox = document.getElementById("mobile-replace");

  let matches = [];
  let currentIndex = -1;
  let activeMode = null;

  // --- Helpers ---
  function escapeRegExp(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function getActiveFindInput() {
    const inputs = Array.from(document.querySelectorAll('.find-input'));
    for (const i of inputs) if (i.value.trim() !== '') return i;
    for (const i of inputs) if (i.offsetParent !== null) return i;
    return inputs[0] || null;
  }

  function getActiveReplaceInput() {
    const inputs = Array.from(document.querySelectorAll('.replace-input'));
    for (const i of inputs) if (i.value.trim() !== '') return i;
    for (const i of inputs) if (i.offsetParent !== null) return i;
    return inputs[0] || null;
  }

  // --- Cursor save/restore ---
  function getCursorOffset() {
    const selection = window.getSelection();
    if (!selection || !selection.rangeCount) return 0;
    const range = selection.getRangeAt(0);
    const preRange = range.cloneRange();
    preRange.selectNodeContents(editor);
    preRange.setEnd(range.startContainer, range.startOffset);
    return preRange.toString().length;
  }

  function restoreCursorOffset(offset) {
    let charCount = 0;
    const walker = document.createTreeWalker(editor, NodeFilter.SHOW_TEXT, null);
    let node;
    while ((node = walker.nextNode())) {
      const nextCount = charCount + node.length;
      if (offset <= nextCount) {
        const range = document.createRange();
        const sel = window.getSelection();
        range.setStart(node, offset - charCount);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
        return;
      }
      charCount = nextCount;
    }
  }

  // --- Highlight handling ---
  function resetHighlights() {
    editor.querySelectorAll('span.highlight, span.current-highlight').forEach(span => {
      span.replaceWith(...span.childNodes);
    });
    matches = [];
    currentIndex = -1;
  }

  function findText() {
    const input = getActiveFindInput();
    if (!input) return;
    const needle = input.value.trim();
    if (!needle) return;

    const cursorOffset = getCursorOffset();
    resetHighlights();

    const html = editor.innerHTML;
    const regex = new RegExp(escapeRegExp(needle), 'gi');
    const newHTML = html.replace(regex, match => `<span class="highlight">${match}</span>`);
    editor.innerHTML = newHTML;

    matches = Array.from(editor.querySelectorAll('.highlight'));
    currentIndex = -1;

    restoreCursorOffset(cursorOffset);

    if (matches.length) {
      // ensure the first match is selected
      goToMatch(0);
    }
  }

  function goToMatch(index) {
    if (!matches.length) return;
    if (currentIndex >= 0) matches[currentIndex].classList.remove('current-highlight');
    currentIndex = (index + matches.length) % matches.length;
    const el = matches[currentIndex];
    el.classList.add('current-highlight');
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function nextMatch() { if (matches.length) goToMatch(currentIndex + 1); }
  function prevMatch() { if (matches.length) goToMatch(currentIndex - 1); }

  // --- Replace functions ---
  function replaceCurrent() {
    const replaceInput = getActiveReplaceInput();
    if (!replaceInput || matches.length === 0) return;

    if (currentIndex < 0) currentIndex = 0; // ensure something is active
    const current = matches[currentIndex];
    if (!current) return;

    // Build replacement fragment (handle multi-line)
    const fragment = document.createDocumentFragment();
    const lines = replaceInput.value.split('\n');
    lines.forEach((line, i) => {
      fragment.appendChild(document.createTextNode(line));
      if (i < lines.length - 1) fragment.appendChild(document.createElement('br'));
    });

    // Replace the current match
    current.replaceWith(fragment);

    // Update matches
    matches.splice(currentIndex, 1);
    if (matches.length === 0) {
      currentIndex = -1;
    } else {
      if (currentIndex >= matches.length) currentIndex = 0;
      goToMatch(currentIndex);
    }
  }

  function replaceAll() {
    const replaceInput = getActiveReplaceInput();
    if (!replaceInput || matches.length === 0) return;

    const replacement = replaceInput.value;

    matches.forEach(span => {
      const fragment = document.createDocumentFragment();
      const lines = replacement.split('\n');
      lines.forEach((line, i) => {
        fragment.appendChild(document.createTextNode(line));
        if (i < lines.length - 1) fragment.appendChild(document.createElement('br'));
      });
      span.replaceWith(fragment);
    });

    matches = [];
    currentIndex = -1;
  }

  // --- Buttons ---
  document.querySelector(".find-action")?.addEventListener("click", e => { e.preventDefault(); replaceCurrent(); });
  document.querySelector(".replace-action")?.addEventListener("click", e => { e.preventDefault(); replaceAll(); });

  // --- Paste plain text ---
  editor.addEventListener('paste', e => {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text');
    document.execCommand("insertText", false, text);
  });

  // --- Enter key ---
  editor.addEventListener("keydown", e => {
    if (e.key === "Enter") {
      e.preventDefault();
      const sel = window.getSelection();
      if (!sel.rangeCount) return;
      const range = sel.getRangeAt(0);
      const br1 = document.createElement("br");
      const br2 = document.createElement("br");
      range.insertNode(br1);
      range.insertNode(br2);
      range.setStartAfter(br2);
      range.collapse(true);
      sel.removeAllRanges();
      sel.addRange(range);
    }
  });

  // --- Live highlighting ---
  editor.addEventListener('input', () => {
    const activeFind = getActiveFindInput();
    if (activeFind && activeFind.value.trim() !== '') {
      if (editor._deb) clearTimeout(editor._deb);
      editor._deb = setTimeout(findText, 150);
    }
  });

  document.querySelectorAll('.find-input').forEach(inp => {
    inp.addEventListener('input', () => {
      if (inp._deb) clearTimeout(inp._deb);
      inp._deb = setTimeout(findText, 200);
    });
  });

  document.querySelectorAll('.replace-input').forEach(inp => {
    inp.addEventListener('keydown', ev => {
      if (ev.key === 'Enter') {
        ev.preventDefault();
        replaceCurrent();
      }
    });
  });

  // --- Navigation buttons ---
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    e.preventDefault();
    const action = btn.dataset.action;
    if (action === 'prev') prevMatch();
    if (action === 'next') nextMatch();
    if (action === 'find') replaceCurrent();
    if (action === 'replace') replaceAll();
  });

  // --- Dropdown mode toggle ---
  const menuToggle = document.querySelector(".menu-toggle");
  const dropdown = document.querySelector(".dropdown");
  if (menuToggle && dropdown) {
    menuToggle.addEventListener("click", () => {
      dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
    });
    document.addEventListener("click", e => {
      if (!menuToggle.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = "none";
    });
  }

  function closeDropdown() { if (dropdown) dropdown.style.display = "none"; }

  window.toggleMode = function(mode, el) {
    document.querySelectorAll(".dropdown li, .menu li").forEach(li => li.classList.remove("active"));
    findBox.style.display = "none";
    replaceBox.style.display = "none";
    mobileFindBox.style.display = "none";
    mobileReplaceBox.style.display = "none";

    const findBtns = document.querySelectorAll(".find-btn");

    if (activeMode === mode) {
      activeMode = null;
      resetHighlights();
      closeDropdown();
      findBtns.forEach(btn => btn.classList.remove("disabled"));
      return;
    }

    const isMobile = window.innerWidth < 768;
    if (mode === "find") {
      if (isMobile) { mobileFindBox.style.display = "flex"; mobileReplaceBox.style.display = "none"; }
      else { findBox.style.display = "flex"; replaceBox.style.display = "none"; }
      findBtns.forEach(btn => btn.classList.remove("disabled"));
    }

    if (mode === "replace") {
      if (isMobile) { mobileFindBox.style.display = "flex"; mobileReplaceBox.style.display = "flex"; }
      else { findBox.style.display = "flex"; replaceBox.style.display = "flex"; }
      findBtns.forEach(btn => btn.classList.add("disabled"));
    }

    activeMode = mode;
    el.classList.add("active");
    closeDropdown();

    const activeFind = getActiveFindInput();
    if (activeFind && activeFind.value.trim() !== '') findText();
  };
});
