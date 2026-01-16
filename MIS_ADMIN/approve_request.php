<?php
require_once "src/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['tech_id'])) {
    $request_id = intval($_POST['request_id']);
    $tech_id = intval($_POST['tech_id']);

    // Update the request status to "Approved"
    $stmt = $link->prepare("UPDATE request_info SET request_status = 'Approved' WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);

    if ($stmt->execute()) {
        // Insert data into the tech_request table
        $insert_stmt = $link->prepare("INSERT INTO tech_request (request_id, tech_id, tech_status) VALUES (?, ?, 'Pending')");
        $insert_stmt->bind_param("ii", $request_id, $tech_id);

        if ($insert_stmt->execute()) {
            header("Location: work_loads.php?success=1");
        } else {
            header("Location: work_loads.php?error=1");
        }

        $insert_stmt->close();
    } else {
        header("Location: work_loads.php?error=1");
    }

    $stmt->close();
    $link->close();
    exit();
} else {
    header("Location: work_loads.php");
    exit();
}