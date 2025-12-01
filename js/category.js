document.querySelectorAll('.category-select').forEach(select => {
  const selected = select.querySelector('.selected');
  const selectedText = select.querySelector('.selected-text');
  const options = select.querySelector('.options');
  const hiddenInput = select.querySelector('#category-input');

  // Toggle dropdown
  selected.addEventListener('click', () => {
    const isActive = select.classList.toggle('active');
    selected.setAttribute('aria-expanded', isActive);
  });

  // Select option (event delegation)
  options.addEventListener('click', (e) => {
    const option = e.target.closest('.option');
    if (option && !option.classList.contains('gear')) {
      const categoryValue = option.dataset.value;

      // Update UI + hidden input
      selectedText.textContent = option.textContent;
      if (hiddenInput) hiddenInput.value = categoryValue;

      select.classList.remove('active');
      selected.setAttribute('aria-expanded', false);

      // Only run filtering if renderNotes exists (index.php)
      if (typeof renderNotes === "function") {
        currentCategory = categoryValue;
        currentPage = 1; // reset pagination
        renderNotes();
      }
    }
  });

  // Close dropdown if clicking outside
  document.addEventListener('click', (e) => {
    if (!select.contains(e.target)) {
      select.classList.remove('active');
      selected.setAttribute('aria-expanded', false);
    }
  });
});
