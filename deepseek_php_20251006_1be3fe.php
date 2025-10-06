<?php
// index.php - Main page to list all servers
include 'config.php';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id > 0) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM servers WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "Server deleted successfully!";
        } else {
            $error = "Error deleting server: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    }
}

// Handle search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = '';
if (!empty($search)) {
    $whereClause = " WHERE server_name LIKE '%$search%' OR operating_system LIKE '%$search%'";
}

// Get all servers from database
$conn = getDBConnection();
$sql = "SELECT * FROM servers" . $whereClause . " ORDER BY server_name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .search-box { margin-bottom: 20px; }
        .action-buttons a { margin-right: 10px; text-decoration: none; padding: 3px 8px; border-radius: 3px; }
        .power-on { color: green; }
        .power-off { color: red; }
        .btn { padding: 5px 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-edit { background-color: #008CBA; }
        .btn-delete { background-color: #f44336; }
        .btn-toggle { background-color: #ff9800; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <h1>Server Management System</h1>
    
    <?php if (isset($message)): ?>
        <div class="message success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search servers..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="index.php" style="margin-left: 10px;">Clear Search</a>
            <?php endif; ?>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Server Name</th>
                <th>Operating System</th>
                <th>Group Owner</th>
                <th>Contact</th>
                <th>Power Status</th>
                <th>Last Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['server_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['operating_system']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['group_owner']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                    echo "<td class='" . ($row['power_status'] == 'PoweredOn' ? 'power-on' : 'power-off') . "'>" . 
                         htmlspecialchars($row['power_status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['last_modified_date']) . "</td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='edit.php?id=" . $row['id'] . "' class='btn btn-edit'>Edit</a>";
                    echo "<a href='toggle_power.php?id=" . $row['id'] . "&status=" . 
                         ($row['power_status'] == 'PoweredOn' ? 'PoweredOff' : 'PoweredOn') . 
                         "' class='btn btn-toggle'>Toggle Power</a>";
                    echo "<a href='#' onclick='confirmDelete(" . $row['id'] . ", \"" . htmlspecialchars($row['server_name']) . "\")' class='btn btn-delete'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No servers found</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <a href="add.php" class="btn">Add New Server</a>
    </div>

    <!-- Confirmation Dialog -->
    <div id="overlay" class="overlay"></div>
    <div id="confirmationDialog" class="confirmation-dialog">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete server: <strong id="serverNameToDelete"></strong>?</p>
        <p>This action cannot be undone.</p>
        <div style="margin-top: 20px; text-align: right;">
            <button onclick="cancelDelete()" style="margin-right: 10px;">Cancel</button>
            <button onclick="proceedDelete()" style="background-color: #f44336; color: white;">Delete</button>
        </div>
    </div>

    <script>
        let serverIdToDelete = null;
        
        function confirmDelete(id, serverName) {
            serverIdToDelete = id;
            document.getElementById('serverNameToDelete').textContent = serverName;
            document.getElementById('confirmationDialog').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function cancelDelete() {
            serverIdToDelete = null;
            document.getElementById('confirmationDialog').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        function proceedDelete() {
            if (serverIdToDelete) {
                window.location.href = 'index.php?delete_id=' + serverIdToDelete;
            }
        }
        
        // Close dialog when clicking overlay
        document.getElementById('overlay').addEventListener('click', cancelDelete);
    </script>
</body>
</html>