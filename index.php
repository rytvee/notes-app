<?php
include "db.php";

// Fetch categories from database
$sql = "SELECT category_id, category_name FROM categories"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes App</title>
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="images/favicon.ico">
</head>
<body>
  <div class="fab-wrapper">
    <a href="pages/add.php" class="fab-btn"><span>+</span></a>
  </div>
  
  <div class="container">
    <h1>My Notes</h1>
    
    <div class="note-form">
      
      <div class="search-wrapper">
        <input type="text" id="searchInput" placeholder="Search Title" oninput="searchNotes()" />
        <span id="clearBtn" onclick="clearSearch()">×</span>
      </div>
      
      <div class="category-select">
        <div class="selected">
          <span class="selected-text">All Categories</span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="options">
          <div class="option gear">
            <a href="pages/categories.php"><i class="fa-solid fa-gear"></i></a>
          </div>
          <?php
            if ($result->num_rows > 0) {
                echo '<div class="option" data-value="all">All Categories</div>';
                while($row = $result->fetch_assoc()) {
                    echo '<div class="option" data-value="' . $row["category_name"] . '">' . $row["category_name"] . '</div>';
                }
            } else {
                echo "<div class='option'>No categories found</div>";
            }
            $conn->close();
          ?>
          </div>
        </div>
        
        <a href="pages/add.php" class="add-btn">Add Note</a>
      
      </div>
      
      <div class="notes-grid" id="notes-container"></div>
    </div>
  </div>
  
  <!-- Pagination -->
  <div class="pagination">
    <button class="prev" onclick="prevPage()" disabled><i class="fa-solid fa-chevron-left"></i> <span>Prev</span></button>
    <span id="pageNumbers"></span>
    <button class="next" onclick="nextPage()" disabled><span>Next</span> <i class="fa-solid fa-chevron-right"></i></button>
  </div>
   
  <!-- Edit Title Modal -->
  <div id="editTitleModal" class="title-modal">
    <div class="title-modal-content">
      <h2>Edit Note Title</h2>
      <p id="edit-title-msg"></p>
      <input type="text" id="edit-title-input" placeholder="Enter new title">
      <div class="edit-modal-actions">
        <button id="save-title-btn">Save</button>
        <button id="cancel-title-btn">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
      <h2>Delete Note</h2>
      <p>Are you sure you want to delete this note?</p>
      <div class="delete-modal-actions">
        <button id="cancel-delete">Cancel</button>
        <button id="confirm-delete">Delete</button>
      </div>
    </div>
  </div>

  <script src="js/notes.js"></script>
  <script src="js/category.js"></script>
    
</body>
</html>