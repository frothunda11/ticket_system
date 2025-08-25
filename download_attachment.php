<?php
require_once 'config.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare("SELECT file_name, file_data, mime_type FROM ticket_attachments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($file_name, $file_data, $mime_type);

    if ($stmt->fetch()) {
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: inline; filename="' . basename($file_name) . '"');
        echo $file_data;
        exit;
    }
    $stmt->close();
}
http_response_code(404);
echo "File not found.";
?>