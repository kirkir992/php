<?php
// index.php - Main page to list all servers
include 'config.php';

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
        .action-buttons a { margin-right: 10px; text-decoration: none; }
        .power-on { color: green; }
        .power-off { color: red; }
        .btn { padding: 5px 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .btn-edit { background-color: #008CBA; }
    </style>
</head>
<body>
    <h1>Server Management System</h1>
    
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search servers..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
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
                         "' class='btn'>Toggle Power</a>";
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
</body>
</html>