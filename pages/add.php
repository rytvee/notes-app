<?php
include __DIR__ . "/../db.php";

// Fetch categories from database
$sql = "SELECT category_id, category_name FROM categories";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Notes</title>
  <link rel="icon" href="../images/favicon.ico">
  <link rel="stylesheet" href="../css/styles.css">
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
</head>
<body class="container-notes">

  <div class="container notes">

    <!-- Close Button -->
    <div class="close-btn" id="closePage">
      <i class="fa-solid fa-xmark"></i>
    </div>

    <!-- Top Strip -->
    <div class="top-strip">
      <input 
        type="text" 
        id="note-title" 
        placeholder="Note Title" 
        maxlength="20"
      />

      <div class="category-select">
        <div class="selected">
          <span class="selected-text">Category</span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="options">
          <div class="option gear" style="display: none;">
            <a href="#"><i class="fa-solid fa-gear"></i></a>
          </div>

          <?php
          if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
              echo '<div class="option" data-value="' . $row["category_id"] . '">' 
                   . $row["category_name"] . 
                   '</div>';
            }
          }
          $conn->close();
          ?>
        </div>

        <input type="hidden" id="category-input" name="category" value="">
      </div>

      <button id="saveBtn">Save</button>
      
    </div>

    <!-- Find & Replace -->
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

      <!-- Desktop Find -->
      <div class="find-box" id="find-box">
        <input type="text" class="find-input" placeholder="Find">
        <span>
          <a href="#" class="find-btn" data-action="prev">
            <i class="fa-solid fa-chevron-left"></i>
          </a>
          <a href="#" class="find-btn" data-action="next">
            <i class="fa-solid fa-chevron-right"></i>
          </a>
        </span>
      </div>

      <!-- Desktop Replace -->
      <div class="replace-box" id="replace-box">
        <input type="text" class="replace-input" placeholder="Replace with">
        <span>
          <a href="#" class="find-action" data-action="find">
            <i class="fa-solid fa-rotate-right"></i>
          </a>
          <a href="#" class="replace-action" data-action="replace">
            <i class="fa-solid fa-arrows-rotate"></i>
          </a>
        </span>
      </div>
    </div>
    
    <!-- Editable Note Area -->
    <div 
      id="note-content" 
      class="editor" 
      contenteditable="true" 
      placeholder="Write your note here..."
    ></div>

    <!-- Mobile Find & Replace -->
    <div class="find-replace mobile">

      <!-- Find -->
      <div class="find-box" id="mobile-find">
        <input type="text" class="find-input" placeholder="Find">
        <span>
          <a href="#" class="find-btn" data-action="prev">
            <i class="fa-solid fa-chevron-left"></i>
          </a>
          <a href="#" class="find-btn" data-action="next">
            <i class="fa-solid fa-chevron-right"></i>
          </a>
        </span>
      </div>
      <!-- Replace -->
      <div class="replace-box" id="mobile-replace">
        <input type="text" class="replace-input" placeholder="Replace with">
        <span>
          <a href="#" class="find-action" data-action="find">
            <i class="fa-solid fa-rotate-right"></i>
          </a>
          <a href="#" class="replace-action" data-action="replace">
            <i class="fa-solid fa-arrows-rotate"></i>
          </a>
        </span>
      </div>
      
    </div>

  </div>
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
        <p>You have unsaved changes. Do you want to leave?</p>
        <div class="modal-actions">
          <button id="continueLeave">Continue</button>
          <button id="cancelLeave">Cancel</button>
        </div>
      </div>
    </div>

  <!-- Scripts -->
  <script src="../js/close-btn.js"></script>
  <script src="../js/save-note.js"></script>
  <script src="../js/category.js"></script>
  <script src="../js/find-replace.js"></script>
</body>
</html>
