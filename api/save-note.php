<?php
header("Content-Type: application/json");
include __DIR__ . "/../db.php"; // DB connection

// --- Sanitizer helpers ---
function sanitizeTitle($title) {
    return htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8'); // plain text only
}

function sanitizeContent($content) {
    $content = trim($content);

    // Allow only safe tags
    $allowed = '<div><br><p><b><i><u><strong><em><span><a>';
    $content = strip_tags($content, $allowed);

    // If <a> exists, strip dangerous attributes
    if (strpos($content, '<a') !== false) {
        $content = preg_replace_callback(
            '/<a[^>]*href=["\']?([^"\'> ]+)["\']?[^>]*>/i',
            function ($matches) {
                $url = $matches[1];
                // Disallow javascript: links
                if (stripos($url, 'javascript:') === 0) {
                    return '<a>'; 
                }
                // Escape attributes
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                return '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">';
            },
            $content
        );
    }

    return $content;
}

// --- Handle request ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = sanitizeTitle($_POST['title'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $content     = sanitizeContent($_POST['content'] ?? '');

    // Validate input
    if (!$title || !$category_id || !$content) {
        echo json_encode([
            "status" => "error",
            "message" => "All fields are required"
        ]);
        exit;
    }

    // --- Check if title already exists ---
    $check_stmt = $conn->prepare("SELECT note_id FROM notes WHERE title = ? LIMIT 1");
    $check_stmt->bind_param("s", $title);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "A note with this title already exists"
        ]);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    $check_stmt->close();

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO notes (title, category_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $title, $category_id, $content);

    if ($stmt->execute()) {
        $newNoteId = $conn->insert_id;

        echo json_encode([
            "status" => "success",
            "message" => "Note saved successfully",
            "note_id" => $newNoteId,
            "note" => [
                "note_id" => $newNoteId,
                "title" => $title,
                "category_id" => $category_id,
                "content" => $content
            ]
        ]);
    } else {
        error_log("DB Error: " . $stmt->error);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to save note. Please try again."
        ]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Invalid request method
echo json_encode([
    "status" => "error",
    "message" => "Invalid request method"
]);
exit;
