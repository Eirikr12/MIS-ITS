<?php
require_once "src/config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $id = intval($_POST['request_id']);
    $action = $_POST['action'] === 'Approve' ? 'approved' : 'declined';

    $stmt = $link->prepare("UPDATE edit_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $id);
    $stmt->execute();
    $stmt->close();

    // Optionally apply the change to employee_info if approved
    if ($action === 'approved') {
        $fetch = $link->prepare("SELECT employee_id, field_to_edit, new_value FROM edit_requests WHERE id = ?");
        $fetch->bind_param("i", $id);
        $fetch->execute();
        $fetch->bind_result($emp_id, $field, $value);
        $fetch->fetch();
        $fetch->close();

        // Map field names to actual column names
        $map = [
            'firstname' => 'employee_fname',
            'middlename' => 'employee_mname',
            'lastname' => 'employee_lname',
            'contact' => 'employee_contact',
            'department' => 'department_id',
            'position' => 'position_id'
        ];

        if (isset($map[$field])) {
            $column = $map[$field];
            $update = $link->prepare("UPDATE employee_info SET $column = ? WHERE employee_id = ?");
            $update->bind_param("ss", $value, $emp_id);
            $update->execute();
            $update->close();
        }
    }

    header("Location: dashboard.php");
    exit();
}
?>