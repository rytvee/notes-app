document.addEventListener("DOMContentLoaded", () => {
  const saveBtn = document.getElementById("saveBtn");

  // Modals
  const successModal = document.getElementById("successModal");
  const okSuccessBtn = document.getElementById("okUpdateBtn");
  const successMessage = document.getElementById("successMessage");

  const errorModal = document.getElementById("errorModal");
  const okErrorBtn = document.getElementById("okErrorBtn");
  const errorMsg = document.getElementById("errorMessage");

  // --- Helpers ---
  const showModal = (modal) => modal.style.display = "flex";
  const hideModal = (modal) => modal.style.display = "none";

  // --- Close buttons ---
  okSuccessBtn.addEventListener("click", () => {
    hideModal(successModal);
    window.location.href = "../index.php"; // redirect home after success
  });

  okErrorBtn.addEventListener("click", () => hideModal(errorModal));

  // Close Error Modal if user clicks outside
  window.addEventListener("click", (e) => {
    if (e.target === errorModal) hideModal(errorModal);
  });

  // --- Save button ---
  saveBtn.addEventListener("click", () => doSave());

  // --- Save logic ---
  async function doSave() {
    const titleEl = document.getElementById("note-title");
    const categoryEl = document.getElementById("category-input");
    const contentEl = document.getElementById("note-content");

    const title = titleEl.value.trim();
    const category_id = categoryEl.value;
    const content = contentEl.innerHTML.trim(); // assuming contenteditable

    // Validation
    if (!title || !category_id || !content) {
      errorMsg.textContent = "Please fill in all fields.";
      showModal(errorModal);
      return;
    }

    if (title.length > 20) {
      errorMsg.textContent = "Title cannot exceed 20 characters.";
      showModal(errorModal);
      return;
    }

    try {
      const res = await fetch("../api/save-note.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ title, category_id, content })
      });

      const result = await res.json();

      if (result.status === "success") {
        showModal(successModal);
        successMessage.textContent = "Note updated successfully!";
        titleEl.value = "";
        categoryEl.value = "";
        contentEl.innerHTML = ""; // fix here
        const sel = document.querySelector(".selected-text");
        if (sel) sel.textContent = "Category";
      } else {
        errorMsg.textContent = result.message || "Error saving note.";
        showModal(errorModal);
      }
    } catch (err) {
      errorMsg.textContent = "Request failed: " + err.message;
      showModal(errorModal);
    }
  }
});
