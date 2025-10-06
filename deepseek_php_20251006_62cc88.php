<?php
// delete.php - Handle server deletion with confirmation
include 'config.php';

// Get server ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Fetch server name for confirmation message
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT server_name FROM servers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $server = $result->fetch_assoc();
    $stmt->close();
    
    // Check if form was submitted (confirmation)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
            $stmt = $conn->prepare("DELETE FROM servers WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                header("Location: index.php?message=Server+deleted+successfully");
                exit();
            } else {
                $error = "Error deleting server: " . $stmt->error;
            }
            $stmt->close();
        } else {
            header("Location: index.php");
            exit();
        }
    }
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Server</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .confirmation-box { border: 1px solid #ccc; padding: 20px; margin: 20px 0; }
        .btn { padding: 10px 15px; margin-right: 10px; text-decoration: none; display: inline-block; }
        .btn-danger { background-color: #f44336; color: white; border: none; cursor: pointer; }
        .btn-secondary { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <h1>Delete Server</h1>
    
    <?php if (isset($error)): ?>
        <div style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="confirmation-box">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete the server: <strong><?php echo htmlspecialchars($server['server_name']); ?></strong>?</p>
        <p>This action cannot be undone.</p>
        
        <form method="POST" action="">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn btn-danger">Yes, Delete Server</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>