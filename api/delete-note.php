<?php
header("Content-Type: application/json");
include __DIR__ . "/../db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id'] ?? 0);

    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid note ID"
        ]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM notes WHERE note_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Note deleted successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Note not found"
            ]);
        }
    } else {
        error_log("DB DELETE ERROR: " . $stmt->error);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to delete note. Try again."
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
