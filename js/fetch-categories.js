let currentPage = 1;

async function renderPage(page) {
  const res = await fetch(`../api/fetch-categories.php?page=${page}`);
  const data = await res.json();

  const list1 = document.querySelector(".v-div-list.list1");
  const list2 = document.querySelector(".v-div-list.list2");
  list1.innerHTML = "";
  list2.innerHTML = "";

  if (data.categories.length === 0) {
    list1.innerHTML = "<li>No categories found.</li>";
    document.getElementById("pageNumbers").textContent = "";
    document.getElementById("prevBtn").disabled = true;
    document.getElementById("nextBtn").disabled = true;
    return;
  }

// Helper for delete button
  const deleteBtn = (cat) =>
    `<button class="delete-btn" data-id="${cat.category_id}" data-name="${cat.category_name}">&times;</button>`;

  // First 5
  data.categories.slice(0, 5).forEach(cat => {
    list1.innerHTML += `
      <li>
        ${cat.category_name}
        ${deleteBtn(cat)}
      </li>
    `;
  });

  // Next 5
  data.categories.slice(5, 10).forEach(cat => {
    list2.innerHTML += `
      <li>
        ${cat.category_name}
        ${deleteBtn(cat)}
      </li>
    `;
  });

  // Update page numbers
  document.getElementById("pageNumbers").textContent =
    `Page ${page} of ${data.totalPages}`;

  // Disable/enable pagination
  document.getElementById("prevBtn").disabled = (page === 1);
  document.getElementById("nextBtn").disabled = (page === data.totalPages);

  // Re-attach delete events
  document.querySelectorAll(".delete-btn").forEach(btn => {
    if (!btn.disabled) {
      btn.addEventListener("click", () => {
        document.getElementById("deleteId").value = btn.dataset.id;
        document.getElementById("deleteMessage").textContent =
          `Are you sure you want to delete "${btn.dataset.name}"?`;
        document.getElementById("deleteModal").style.display = "flex";
      });
    }
  });
}

// Prev / Next
document.getElementById("prevBtn").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderPage(currentPage);
  }
});

document.getElementById("nextBtn").addEventListener("click", () => {
  currentPage++;
  renderPage(currentPage);
});

// Category Delete
const modal = document.getElementById("deleteModal");
const deleteForm = document.getElementById("deleteForm");
const deleteId = document.getElementById("deleteId");
const deleteMessage = document.getElementById("deleteMessage");

document.querySelectorAll(".delete-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const id = this.dataset.id;
    const name = this.dataset.name;
    deleteId.value = id;
    deleteMessage.textContent = `Are you sure you want to delete "${name}"?`;
    modal.style.display = "flex";
  });
});

function closeModal() {
  modal.style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

// Initial load
renderPage(currentPage);

