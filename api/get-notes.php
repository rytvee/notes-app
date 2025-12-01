<?php
header("Content-Type: application/json");
include __DIR__ . "/../db.php";

$sql = "SELECT n.note_id, n.title, n.content, n.updated_at, c.category_name
        FROM notes n
        LEFT JOIN categories c ON n.category_id = c.category_id
        ORDER BY n.updated_at DESC"; // newest OR updated first

$result = $conn->query($sql);
$notes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
}

echo json_encode($notes);
$conn->close();
exit;
?>