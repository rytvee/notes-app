let currentCategory = "all";
let currentSearch = "";
let currentPage = 1;
const notesPerPage = 12;
let allNotes = [];
let noteToDelete = null;
let editingNoteId = null;

// ==========================
// Safe text (avoid null/undefined issues)
// ==========================
function safeText(value) {
  return value ? String(value) : "";
}

// ==========================
// Fetch and Render Notes
// ==========================
document.addEventListener("DOMContentLoaded", async () => {
  const container = document.getElementById("notes-container");
  container.innerHTML = "<p>Loading notes...</p>";

  try {
    const res = await fetch("api/get-notes.php");
    const notes = await res.json();

    if (!notes.length) {
      container.innerHTML = "<p>No notes found.</p>";
      return;
    }

    allNotes = notes;
    renderNotes();

  } catch (err) {
    container.innerHTML = `<p>Error loading notes: ${err.message}</p>`;
  }
});

// ==========================
// Get Filtered Notes (Category + Search)
// ==========================
function getFilteredNotes() {
  let filtered = currentCategory === "all"
    ? allNotes
    : allNotes.filter(n =>
        safeText(n.category_name).toLowerCase() === String(currentCategory).toLowerCase()
      );

  if (currentSearch) {
    filtered = filtered.filter(n =>
      safeText(n.title).toLowerCase().includes(currentSearch)
    );
  }

  return filtered;
}

function searchNotes() {
  const input = document.getElementById("searchInput");
  currentSearch = input.value.trim().toLowerCase();
  const clearBtn = document.getElementById("clearBtn");

  clearBtn.style.display = currentSearch ? "inline" : "none";

  currentPage = 1; // reset to first page
  renderNotes(); // from notes.js
}

function clearSearch() {
  const input = document.getElementById("searchInput");
  input.value = "";
  currentSearch = "";
  document.getElementById("clearBtn").style.display = "none";
  currentPage = 1;
  renderNotes(); // from notes.js
}


// ==========================
// Render Notes
// ==========================
function renderNotes() {
  const container = document.getElementById("notes-container");
  if (!container) return;
  container.innerHTML = "";

  const filteredNotes = getFilteredNotes();

  if (!filteredNotes.length) {
    container.innerHTML = "<p>No notes found.</p>";
    renderPageNumbers(0);
    return;
  }

  const totalPages = Math.ceil(filteredNotes.length / notesPerPage) || 1;
  if (currentPage > totalPages) currentPage = totalPages;

  const start = (currentPage - 1) * notesPerPage;
  const end = start + notesPerPage;
  const notesToShow = filteredNotes.slice(start, end);

  notesToShow.forEach(note => {
    const card = document.createElement("div");
    card.className = "note-card";
    card.dataset.link = `pages/view.php?id=${note.note_id}`;
    card.dataset.category = note.category_name || "Uncategorized";

    const displayTitle = safeText(note.title).length > 20 
      ? safeText(note.title).substring(0, 20) + "…" 
      : safeText(note.title);

    const displayContent = safeText(note.content).length > 50 
      ? safeText(note.content).substring(0, 50) + "…" 
      : safeText(note.content);

    card.innerHTML = `
      <div class="note-header">
        <div class="note-title"><a href="pages/view.php?id=${note.note_id}">${displayTitle}</a></div>
        <div class="note-menu">
          <button class="menu-btn" aria-expanded="false">⋮</button>
          <div class="menu-dropdown">
            <a href="#" class="edit-note" data-id="${note.note_id}">Edit</a>
            <a href="#" class="delete-note" data-id="${note.note_id}">Delete</a>
            <a href="#" class="cancel-menu">Cancel</a>
          </div>
        </div>
      </div>
      <div class="note-meta">${safeText(note.category_name) || "Uncategorized"}</div>
      <div class="note-content">${displayContent}</div>
    `;

    container.appendChild(card);
  });

  makeCardsClickable();
  renderPageNumbers(filteredNotes.length);
}

// ==========================
// Make Cards Clickable
// ==========================
function makeCardsClickable() {

  document.querySelectorAll(".note-card").forEach(card => {
    const titleLink = card.querySelector(".note-title a");

    card.onclick = null;
    if (titleLink) titleLink.onclick = null;

      if (titleLink) {
        titleLink.onclick = e => {
          e.preventDefault();
          e.stopPropagation();
        };
      }

      card.onclick = e => {
        if (!e.target.closest(".note-menu") && card.dataset.link) {
          window.location.href = card.dataset.link;
        }
      };
  });
}

// ==========================
// Event Delegation (Dropdown + Delete + Edit + Cancel)
// ==========================
document.addEventListener("DOMContentLoaded", () => {
  makeCardsClickable();

  const container = document.getElementById("notes-container");

  container.addEventListener("click", e => {
    const target = e.target;

    if (target.classList.contains("menu-btn")) {
      e.stopPropagation();
      const dropdown = target.nextElementSibling;

      container.querySelectorAll(".menu-dropdown.show").forEach(menu => {
        if (menu !== dropdown) menu.classList.remove("show");
      });

      dropdown.classList.toggle("show");
      return;
    }

    if (target.classList.contains("edit-note")) {
      e.preventDefault();
      editingNoteId = target.dataset.id;
      const currentTitle = target.closest(".note-card")
                                .querySelector(".note-title a").textContent;
      titleInput.value = currentTitle;
      titleModal.style.display = "flex";
      target.closest(".menu-dropdown").classList.remove("show");
      return;
    }

    if (target.classList.contains("delete-note")) {
      e.preventDefault();
      noteToDelete = target.dataset.id;
      document.getElementById("deleteModal").style.display = "flex";
      target.closest(".menu-dropdown").classList.remove("show");
      return;
    }

    if (target.classList.contains("cancel-menu")) {
      e.preventDefault();
      target.closest(".menu-dropdown").classList.remove("show");
      return;
    }

    container.querySelectorAll(".menu-dropdown.show").forEach(menu => {
      menu.classList.remove("show");
    });
  });
});

window.makeCardsClickable = makeCardsClickable;

// ==========================
// Delete Modal Buttons
// ==========================
document.getElementById("cancel-delete").addEventListener("click", () => {
  document.getElementById("deleteModal").style.display = "none";
  noteToDelete = null;
});

document.getElementById("confirm-delete").addEventListener("click", async () => {
  if (!noteToDelete) return;

  try {
    const res = await fetch("api/delete-note.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ id: noteToDelete })
    });
    const result = await res.json();

    if (result.status === "success") {
      allNotes = allNotes.filter(n => Number(n.note_id) !== Number(noteToDelete));
      renderNotes();
    } else {
      document.getElementById("errorMsg").textContent = result.message || "Failed to delete note.";
      document.getElementById("errorModal").style.display = "flex";
    }
  } catch (err) {
    document.getElementById("errorMsg").textContent = "Request failed: " + err.message;
    document.getElementById("errorModal").style.display = "flex";
  }

  document.getElementById("deleteModal").style.display = "none";
  noteToDelete = null;
});

window.addEventListener("click", e => {
  if (e.target === deleteModal) deleteModal.style.display = "none";
});

// ==========================
// Edit Title Modal Buttons
// ==========================
const titleModal = document.getElementById("editTitleModal");
const closeTitleModalBtn = document.getElementById("cancel-title-btn");
const saveTitleBtn = document.getElementById("save-title-btn");
const titleInput = document.getElementById("edit-title-input");
const editMsg = document.getElementById("edit-title-msg");

closeTitleModalBtn.addEventListener("click", () => {
  titleModal.style.display = "none";
});

window.addEventListener("click", e => {
  if (e.target === titleModal) {
    titleModal.style.display = "none";
  }
});

saveTitleBtn.addEventListener("click", async () => {
  const newTitle = titleInput.value.trim();
  editMsg.textContent = "";
  editMsg.style.color = "";

  if (!newTitle) {
    editMsg.textContent = "Title cannot be empty!";
    editMsg.style.color = "red";
    return;
  }

  try {
    const response = await fetch("api/update-title.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ id: editingNoteId, title: newTitle })
    });

    const result = await response.text();

    if (result.toLowerCase().includes("success")) {
      const card = document.querySelector(`.note-card .note-title a[href="pages/view.php?id=${editingNoteId}"]`);
      if (card) card.textContent = newTitle;
      titleModal.style.display = "none";
    } else {
      editMsg.textContent = result;
      editMsg.style.color = "red";
    }
  } catch (err) {
    editMsg.textContent = "Request failed";
    editMsg.style.color = "red";
  }
});

// ==========================
// Pagination
// ==========================
function renderPageNumbers(totalNotes = getFilteredNotes().length) {
  const totalPages = Math.ceil(totalNotes / notesPerPage);
  const pageNumbers = document.getElementById("pageNumbers");
  pageNumbers.innerHTML = "";

  const span = document.createElement("span");
  span.innerText = `${currentPage} / ${totalPages || 1}`;
  span.classList.add("active-page");
  pageNumbers.appendChild(span);

  document.querySelector(".prev").disabled = currentPage === 1;
  document.querySelector(".next").disabled =
    currentPage === totalPages || totalPages === 0;
}

function prevPage() {
  if (currentPage > 1) {
    currentPage--;
    renderNotes();
  }
}

function nextPage() {
  const totalPages = Math.ceil(getFilteredNotes().length / notesPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    renderNotes();
  }
}

window.prevPage = prevPage;
window.nextPage = nextPage;
