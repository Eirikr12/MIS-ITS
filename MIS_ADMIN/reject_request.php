<?php
require_once "src/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['reject_reason'])) {
    $request_id = intval($_POST['request_id']);
    $reject_reason = $link->real_escape_string($_POST['reject_reason']);

    // Update the request status to "Rejected" and save the reason
    $stmt = $link->prepare("UPDATE request_info SET request_status = 'Rejected', reject_reason = ? WHERE request_id = ?");
    $stmt->bind_param("si", $reject_reason, $request_id);

    if ($stmt->execute()) {
        header("Location: request_status.php?success=1");
    } else {
        header("Location: request_status.php?error=1");
    }

    $stmt->close();
    $link->close();
    exit();
} else {
    header("Location: request_status.php");
    exit();
}