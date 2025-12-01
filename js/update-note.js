// Get modals and message elements 
const updateSuccessModal = document.getElementById("successModal");
const updateErrorModal = document.getElementById("errorModal");
const successMessage = document.getElementById("successMessage");
const errorMessage = document.getElementById("errorMessage");

// Close modals
const closeModals = () => {
  updateSuccessModal.style.display = "none";
  updateErrorModal.style.display = "none";
};

// Close buttons
document.querySelectorAll(".close-modal, #okErrorBtn").forEach(btn => {
  btn.addEventListener("click", closeModals);
});

// OK button on success → redirect home
document.getElementById("okUpdateBtn").addEventListener("click", () => {
  updateSuccessModal.style.display = "none";
  window.location.href = "../index.php"; // redirect home
});

// Update note button
document.getElementById("updateBtn").addEventListener("click", async () => {
  const noteId = document.getElementById("updateBtn").dataset.id;
  const title = document.getElementById("note-title").value.trim();
  const category_id = document.getElementById("category-input").value;
  const content = document.getElementById("note-content").innerHTML.trim();

  if (!title || !category_id || !content) {
    errorMessage.textContent = "Please fill in all fields.";
    updateErrorModal.style.display = "flex";
    return;
  }

  try {
    const response = await fetch("../api/update-note.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ note_id: noteId, title, category_id, content })
    });

    const result = await response.json();
    if (result.status === "success") {
      successMessage.textContent = "Note updated successfully!";
      updateSuccessModal.style.display = "flex";
    } else {
      errorMessage.textContent = result.message || "Something went wrong.";
      updateErrorModal.style.display = "flex";
    }
  } catch (err) {
    errorMessage.textContent = "Request failed: " + err.message;
    updateErrorModal.style.display = "flex";
  }
});

// Close modal when clicking outside
window.addEventListener("click", e => {
  if (e.target === updateErrorModal) updateErrorModal.style.display = "none";
});
