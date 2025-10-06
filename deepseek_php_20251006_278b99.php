<?php
// edit.php - Edit server information
include 'config.php';

// Get server ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch server data
$conn = getDBConnection();
$server = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM servers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $server = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $server_name = $_POST['server_name'];
    $operating_system = $_POST['operating_system'];
    $group_owner = $_POST['group_owner'];
    $contact = $_POST['contact'];
    $power_status = $_POST['power_status'];
    $additional_info = $_POST['additional_info'];
    
    if ($id > 0) {
        // Update existing server
        $stmt = $conn->prepare("UPDATE servers SET server_name=?, operating_system=?, group_owner=?, contact=?, power_status=?, additional_info=?, last_modified_date=CURDATE() WHERE id=?");
        $stmt->bind_param("ssssssi", $server_name, $operating_system, $group_owner, $contact, $power_status, $additional_info, $id);
    } else {
        // Insert new server
        $stmt = $conn->prepare("INSERT INTO servers (server_name, operating_system, group_owner, contact, power_status, additional_info, last_modified_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("ssssss", $server_name, $operating_system, $group_owner, $contact, $power_status, $additional_info);
    }
    
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Error saving server: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id > 0 ? 'Edit' : 'Add'; ?> Server</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .btn-cancel { background-color: #f44336; }
    </style>
</head>
<body>
    <h1><?php echo $id > 0 ? 'Edit Server' : 'Add New Server'; ?></h1>
    
    <?php if (isset($error)): ?>
        <div style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="server_name">Server Name:</label>
            <input type="text" id="server_name" name="server_name" value="<?php echo isset($server['server_name']) ? htmlspecialchars($server['server_name']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="operating_system">Operating System:</label>
            <input type="text" id="operating_system" name="operating_system" value="<?php echo isset($server['operating_system']) ? htmlspecialchars($server['operating_system']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="group_owner">Group Owner:</label>
            <input type="text" id="group_owner" name="group_owner" value="<?php echo isset($server['group_owner']) ? htmlspecialchars($server['group_owner']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="contact">Contact:</label>
            <input type="text" id="contact" name="contact" value="<?php echo isset($server['contact']) ? htmlspecialchars($server['contact']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="power_status">Power Status:</label>
            <select id="power_status" name="power_status" required>
                <option value="PoweredOn" <?php echo (isset($server['power_status']) && $server['power_status'] == 'PoweredOn') ? 'selected' : ''; ?>>Powered On</option>
                <option value="PoweredOff" <?php echo (isset($server['power_status']) && $server['power_status'] == 'PoweredOff') ? 'selected' : ''; ?>>Powered Off</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="additional_info">Additional Info:</label>
            <textarea id="additional_info" name="additional_info" rows="4"><?php echo isset($server['additional_info']) ? htmlspecialchars($server['additional_info']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Save</button>
            <a href="index.php" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</body>
</html>