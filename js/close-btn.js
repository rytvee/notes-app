document.addEventListener("DOMContentLoaded", () => {
  const closeBtn = document.getElementById("closePage");
  const unsavedModal = document.getElementById("unsavedModal");
  const continueBtn = document.getElementById("continueLeave");
  const cancelBtn = document.getElementById("cancelLeave");

  const titleEl = document.getElementById("note-title");
  const categoryEl = document.getElementById("category-input");
  const contentEl = document.getElementById("note-content");

  // Helper to check if any field is filled
  const hasUnsavedChanges = () => {
    return (
      titleEl.value.trim() ||
      categoryEl.value ||
      contentEl.innerText.trim()
    );
  };

  closeBtn.addEventListener("click", (e) => {
    e.preventDefault(); // prevent default link action

    if (hasUnsavedChanges()) {
      // Show modal
      unsavedModal.style.display = "flex";
    } else {
      // No changes → go home
      window.location.href = "../index.php";
    }
  });

  // Continue → go home
  continueBtn.addEventListener("click", () => {
    window.location.href = "../index.php";
  });

  // Cancel → close modal
  cancelBtn.addEventListener("click", () => {
    unsavedModal.style.display = "none";
  });

  // Click outside modal to close
  window.addEventListener("click", (e) => {
    if (e.target === unsavedModal) unsavedModal.style.display = "none";
  });
});
