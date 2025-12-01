<?php
session_start();
include __DIR__ . "/../db.php";


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $deleteId = intval($_GET['id']);

    if ($deleteId > 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Category deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete category.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid category ID.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['category_name'])) {
    $name = strtolower(trim($_POST['category_name']));

    // Check if category exists
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM categories WHERE LOWER(category_name) = LOWER(?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['cnt'] == 0) {
        // Insert new category
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();

        $_SESSION['success'] = "Category \"$name\" added successfully.";
    } else {
        $_SESSION['error'] = "Category \"$name\" already exists.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Categories</title>
  <link rel="icon" href="../images/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    /* --- CSS Reset --- */
    *, *::before, *::after {
      box-sizing: border-box;
    }
    html, body {
      height: 100%;
      margin: 0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      line-height: 1.5;
      display: flex;
      flex-direction: column;
      background: #fff;
    }
    .cat-page {
      position: relative;
        justify-content: center;
    }
  </style>
</head>
<body>
  <main class="page">
    <section class="card">
        <a href="index.php" class="close-btn close"><i class="fa-solid fa-xmark"></i></a>

      <h1>Manage Category</h1>

      <!-- Flash messages -->
      <?php if (isset($_SESSION['error'])): ?>
        <p class="message" style="color:red; font-weight:bold;">
          <?= htmlspecialchars($_SESSION['error']) ?>
        </p>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <p class = "message" style="color:green; font-weight:bold;">
          <?= htmlspecialchars($_SESSION['success']) ?>
        </p>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <!-- Add Category Form -->
      <form class="input-row" method="POST" action="">
        <label for="note" class="sr-only">New category</label>
        <input id="note" name="category_name" type="text" placeholder="Enter new category..." aria-label="Your text" required />
        <button class="btn" type="submit">Submit</button>
      </form>

      <!-- Empty lists (filled dynamically by fetch_categories.js) -->
      <div class="div-list">
        <ul class="v-div-list list1"></ul>
        <ul class="v-div-list list2"></ul>
      </div>

      <!-- Category Delete Confirmation Modal -->
      <div id="deleteModal" class="deleteCategory">
        <div class="modal-content">
          <h2>Delete Category</h2>
          <p id="deleteMessage"></p>
          <div class="modal-actions">
            <form id="deleteForm" method="GET" action="">
              <input type="hidden" name="id" id="deleteId">
              <button type="submit" id="confirm-delete">Delete</button>
            </form>
            <button type="button" id="cancel-delete" onclick="closeModal()">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination cat-page">
        <button type="button" class="prev" id="prevBtn" disabled><i class="fa-solid fa-chevron-left"></i> <span>Prev</span></button>
        <span id="pageNumbers"></span>
        <button type="button" class="next" id="nextBtn" disabled><span>Next</span> <i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </section>
  </main>

  <script src="../js/close-btn.js"></script>
  <script src="../js/fetch-categories.js"></script>

  <script>
    window.onload = function() {
        const msg = document.querySelector('.message');
        if (msg) {
            setTimeout(() => {
                msg.style.display = 'none';
            }, 4000);
        }
    };
  </script>
</body>
</html>
