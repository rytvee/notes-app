<?php 
header("Content-Type: application/json");
include __DIR__ . "/../db.php";

// --- Sanitizer helpers ---
function sanitizeTitle($title) {
    return htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');
}

function sanitizeContent($content) {
    $content = trim($content);

    // Allow only safe tags
    $allowed = '<div><br><p><b><i><u><strong><em><span><a>';
    $content = strip_tags($content, $allowed);

    // Clean <a> tags (block javascript:)
    if (strpos($content, '<a') !== false) {
        $content = preg_replace_callback(
            '/<a[^>]*href=["\']?([^"\'> ]+)["\']?[^>]*>/i',
            function ($matches) {
                $url = $matches[1];
                if (stripos($url, 'javascript:') === 0) {
                    return '<a>'; 
                }
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
    $note_id   = intval($_POST['note_id'] ?? 0);
    $title     = sanitizeTitle($_POST['title'] ?? '');
    $category  = intval($_POST['category_id'] ?? 0);
    $content   = sanitizeContent($_POST['content'] ?? '');

    if ($note_id && $title && $category && $content) {
        $stmt = $conn->prepare("UPDATE notes SET title = ?, category_id = ?, content = ? WHERE note_id = ?");
        $stmt->bind_param("sisi", $title, $category, $content, $note_id);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success", 
                "message" => "Note updated successfully",
                "note" => [
                    "note_id" => $note_id,
                    "title" => $title,
                    "category_id" => $category,
                    "content" => $content
                ]
            ]);
        } else {
            error_log("DB Error: " . $stmt->error);
            echo json_encode([
                "status" => "error", 
                "message" => "Failed to update note."
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
    }

    $conn->close();
    exit;
}

// Invalid method
echo json_encode(["status" => "error", "message" => "Invalid request method"]);
exit;
