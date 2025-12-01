<?php
include __DIR__ . "/../db.php";

$id = $_POST['id'] ?? null;
$title = $_POST['title'] ?? null;

if (!$id || !$title) {
    echo "Missing data";
    exit;
}

// --- Check if title already exists (ignore current note ID) ---
$check_stmt = $conn->prepare("SELECT note_id FROM notes WHERE title = ? AND note_id != ? LIMIT 1");
$check_stmt->bind_param("si", $title, $id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo "A note with this title already exists";
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// --- Update the note ---
$stmt = $conn->prepare("UPDATE notes SET title = ? WHERE note_id = ?");
$stmt->bind_param("si", $title, $id);


if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Note not found or does not belong to user"
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}


$stmt->close();
$conn->close();
?>