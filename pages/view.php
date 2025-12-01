<?php
session_start();

// Prevent direct access (only allow if user came from your app)
if (
    !isset($_SERVER['HTTP_REFERER']) || 
    strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false
) {
    header("Location: index.php");
    exit;
}

include __DIR__ . "/../db.php";

// Get note ID from URL
$note_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch note with category name
$note_sql = "SELECT n.*, c.category_name 
             FROM notes n
             LEFT JOIN categories c ON n.category_id = c.category_id
             WHERE n.note_id = $note_id LIMIT 1";
$note_result = $conn->query($note_sql);
$note = $note_result->fetch_assoc();

// Fetch categories
$cat_sql = "SELECT category_id, category_name FROM categories"; 
$cat_result = $conn->query($cat_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Note</title>
  <link rel="icon" href="../images/favicon.ico">
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="container-notes">

  <div class="container notes">
    
      <!-- Close Button -->
      <div class="close-btn" id="closePage">
        <i class="fa-solid fa-xmark"></i>
      </div>

      <div class="top-strip">
        <input type="text" id="note-title" value="<?= htmlspecialchars($note['title']) ?>" placeholder="Note Title" disabled style="background: #eee;">

        <div class="category-select">
          <div class="selected">
            <span class="selected-text">
                <?= htmlspecialchars($note['category_name'] ?? "Uncategorized") ?>
            </span>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
          <div class="options">
            <div class="option gear" style="display: none;">
              <a href="#"><i class="fa-solid fa-gear"></i></a>
            </div>
            <?php while($row = $cat_result->fetch_assoc()): ?>
            <div class="option" 
                data-value="<?= $row['category_id'] ?>"
                <?php if ($row['category_id'] == $note['category_id']) echo 'style="font-weight:bold;background:#f0f0f0;"'; ?>>
              <?= htmlspecialchars($row['category_name']) ?>
            </div>
            <?php endwhile; ?>
          </div>
          <input type="hidden" id="category-input" name="category" value="<?= $note['category_id'] ?>">
        </div>
        <button id="updateBtn" data-id="<?= $note_id ?>">Update</button>
      </div>
      
      <div class="find-replace">
      <nav>
        <ul class="menu">
          <li onclick="toggleMode('find', this);">Find</li>
          <li onclick="toggleMode('replace', this);">Replace</li>
        </ul>

        <!-- Mobile toggle -->
        <div class="menu-toggle">⋯</div>
        <ul class="dropdown">
          <li onclick="toggleMode('find', this);">Find</li>
          <li onclick="toggleMode('replace', this);">Replace</li>
        </ul>
      </nav>

      <!-- Find input + arrows -->
      <div class="find-box" id="find-box">
        <input type="text" placeholder="Find">
        <span>
          <a href="#" class="find-btn"><i class="fa-solid fa-chevron-left"></i></a>
          <a href="#" class="find-btn"><i class="fa-solid fa-chevron-right"></i></a>
        </span>
      </div>

      <!-- Replace input + actions -->
      <div class="replace-box" id="replace-box">
        <input type="text" placeholder="Replace with">
        <span>
          <a href="#"><i class="fa-solid fa-rotate-right"></i></a>
          <a href="#"><i class="fa-solid fa-arrows-rotate"></i></a>
        </span>
      </div>
    </div>

    <div 
    id="note-content" 
    class="editor" 
    contenteditable="true" 
    placeholder="Write your note here...">
      <?= $note['content'] ?>
    </div>
 
    <div class="find-replace mobile">
      <div class="find-box" id="mobile-find">
        <input type="text" placeholder="Find">
        <span>
          <a href="#" class="find-btn"><i class="fa-solid fa-chevron-left"></i></a>
          <a href="#" class="find-btn"><i class="fa-solid fa-chevron-right"></i></a>
        </span>
      </div>

      <!-- Replace input + actions -->
      <div class="replace-box" id="mobile-replace">
        <input type="text" placeholder="Replace with">
        <span>
          <a href="#"><i class="fa-solid fa-rotate-right"></i></a>
          <a href="#"><i class="fa-solid fa-arrows-rotate"></i></a>
        </span>
      </div>
    </div>
  </div>  <!-- /container notes -->

  <!-- Success Modal -->
  <div id="successModal" class="save-modal">
    <div class="modal-content">
      <h2>Success!</h2>
      <p id="successMessage"></p>
      <div class="modal-actions">
        <button type="button" id="okUpdateBtn">Ok</button>
      </div>
    </div>
  </div>

  <!-- Error Modal -->
  <div id="errorModal" class="save-modal">
    <div class="modal-content">
      <h2>Error!</h2>
      <p id="errorMessage"></p>
      <div class="modal-actions">
        <button type="button" id="okErrorBtn">Ok</button>
      </div>
    </div>
  </div>

  <!-- Unsaved Changes Modal -->
  <div id="unsavedModal" class="close-modal">
    <div class="modal-content">
      <h2>Close?</h2>
      <p>You did not save. Do you want to leave?</p>
      <div class="modal-actions">
        <button id="continueLeave">Yes</button>
        <button id="cancelLeave">No</button>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="../js/close-btn.js"></script>
  <script src="../js/category.js"></script>
  <script src="../js/find-replace-menu.js"></script>
  <script src="../js/update-note.js"></script>

</body>
</html>
