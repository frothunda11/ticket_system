<?php
require_once 'config.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare("SELECT file_name, file_data FROM ticket_attachments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($file_name, $file_data);

    if ($stmt->fetch()) {
        // Determine MIME type from file extension
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];
        $content_type = $mime_types[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $content_type);
        header('Content-Disposition: inline; filename="' . basename($file_name) . '"');
        // For binary data, echo directly
        echo $file_data;
        exit;
    }
    $stmt->close();
}
http_response_code(404);
echo "File not found.";
?>