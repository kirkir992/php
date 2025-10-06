<?php
// toggle_power.php - Toggle server power status
include 'config.php';

// Get server ID and new status from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$new_status = isset($_GET['status']) ? $_GET['status'] : '';

if ($id > 0 && in_array($new_status, ['PoweredOn', 'PoweredOff'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE servers SET power_status=?, last_modified_date=CURDATE() WHERE id=?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header("Location: index.php");
exit();
?>