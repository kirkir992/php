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

// Handle clone action
if (isset($_GET['clone_id'])) {
    $clone_id = intval($_GET['clone_id']);
    if ($clone_id > 0) {
        $conn = getDBConnection();
        
        // Get the server to clone
        $stmt = $conn->prepare("SELECT * FROM servers WHERE id = ?");
        $stmt->bind_param("i", $clone_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $server_to_clone = $result->fetch_assoc();
        $stmt->close();
        
        if ($server_to_clone) {
            // Generate cloned server name
            $original_name = $server_to_clone['server_name'];
            $clone_suffix = '_CLONE';
            $new_server_name = $original_name . $clone_suffix;
            
            // Check if clone name already exists, if so, add number
            $counter = 1;
            $base_name = $new_server_name;
            while (true) {
                $check_stmt = $conn->prepare("SELECT id FROM servers WHERE server_name = ?");
                $check_stmt->bind_param("s", $new_server_name);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows === 0) {
                    $check_stmt->close();
                    break;
                }
                
                $new_server_name = $base_name . '_' . $counter;
                $counter++;
                $check_stmt->close();
            }
            
            // Insert cloned server
            $insert_stmt = $conn->prepare("INSERT INTO servers (server_name, operating_system, group_owner, contact, power_status, additional_info, last_modified_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
            $insert_stmt->bind_param("ssssss", 
                $new_server_name,
                $server_to_clone['operating_system'],
                $server_to_clone['group_owner'],
                $server_to_clone['contact'],
                $server_to_clone['power_status'],
                $server_to_clone['additional_info']
            );
            
            if ($insert_stmt->execute()) {
                $message = "Server cloned successfully! New server: " . $new_server_name;
            } else {
                $error = "Error cloning server: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        } else {
            $error = "Server not found for cloning.";
        }
        $conn->close();
    }
}

// Handle search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Sorting parameters
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'server_name';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Validate sort column to prevent SQL injection
$allowed_columns = ['server_name', 'operating_system', 'group_owner', 'contact', 'power_status', 'last_modified_date'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'server_name';
}

// Validate sort order
$sort_order = strtolower($sort_order) === 'desc' ? 'DESC' : 'ASC';

// Build WHERE clause for search
$whereClause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $whereClause = " WHERE server_name LIKE ? OR operating_system LIKE ? OR group_owner LIKE ? OR contact LIKE ?";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term, $search_term];
    $types = 'ssss';
}

// Get all servers from database with sorting
$conn = getDBConnection();
$sql = "SELECT * FROM servers" . $whereClause . " ORDER BY $sort_column $sort_order";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Function to generate sort URL
function getSortUrl($column, $current_sort, $current_order) {
    $order = 'asc';
    if ($current_sort === $column) {
        $order = $current_order === 'ASC' ? 'desc' : 'asc';
    }
    $query_string = $_GET;
    $query_string['sort'] = $column;
    $query_string['order'] = $order;
    return '?' . http_build_query($query_string);
}

// Function to generate sort indicator
function getSortIndicator($column, $current_sort, $current_order) {
    if ($current_sort === $column) {
        return $current_order === 'ASC' ? ' ↑' : ' ↓';
    }
    return '';
}
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
        th { background-color: #f2f2f2; cursor: pointer; position: relative; }
        th:hover { background-color: #e0e0e0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .search-box { margin-bottom: 20px; }
        .action-buttons a { margin-right: 8px; text-decoration: none; padding: 4px 8px; border-radius: 3px; font-size: 12px; display: inline-block; margin-bottom: 2px; }
        .power-on { color: green; }
        .power-off { color: red; }
        .btn { padding: 5px 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-edit { background-color: #008CBA; }
        .btn-delete { background-color: #f44336; }
        .btn-toggle { background-color: #ff9800; }
        .btn-clone { background-color: #9C27B0; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .sort-indicator { margin-left: 5px; font-weight: bold; }
        .current-sort { background-color: #d4edff; }
        .action-column { width: 280px; }
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
            border-radius: 5px;
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
        .dialog-buttons { margin-top: 20px; text-align: right; }
        .dialog-buttons button { margin-left: 10px; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-cancel { background-color: #6c757d; color: white; }
        .btn-confirm { background-color: #28a745; color: white; }
        .btn-confirm-delete { background-color: #dc3545; color: white; }
        .clone-options { margin-bottom: 15px; }
        .clone-options label { display: block; margin-bottom: 8px; }
        .clone-options input[type="text"] { width: 100%; padding: 5px; margin-top: 2px; }
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
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars(strtolower($sort_order)); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="?sort=<?php echo htmlspecialchars($sort_column); ?>&order=<?php echo htmlspecialchars(strtolower($sort_order)); ?>" style="margin-left: 10px;">Clear Search</a>
            <?php endif; ?>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th onclick="window.location.href='<?php echo getSortUrl('server_name', $sort_column, $sort_order); ?>'">
                    Server Name<?php echo getSortIndicator('server_name', $sort_column, $sort_order); ?>
                </th>
                <th onclick="window.location.href='<?php echo getSortUrl('operating_system', $sort_column, $sort_order); ?>'">
                    Operating System<?php echo getSortIndicator('operating_system', $sort_column, $sort_order); ?>
                </th>
                <th onclick="window.location.href='<?php echo getSortUrl('group_owner', $sort_column, $sort_order); ?>'">
                    Group Owner<?php echo getSortIndicator('group_owner', $sort_column, $sort_order); ?>
                </th>
                <th onclick="window.location.href='<?php echo getSortUrl('contact', $sort_column, $sort_order); ?>'">
                    Contact<?php echo getSortIndicator('contact', $sort_column, $sort_order); ?>
                </th>
                <th onclick="window.location.href='<?php echo getSortUrl('power_status', $sort_column, $sort_order); ?>'">
                    Power Status<?php echo getSortIndicator('power_status', $sort_column, $sort_order); ?>
                </th>
                <th onclick="window.location.href='<?php echo getSortUrl('last_modified_date', $sort_column, $sort_order); ?>'">
                    Last Modified<?php echo getSortIndicator('last_modified_date', $sort_column, $sort_order); ?>
                </th>
                <th class="action-column">Actions</th>
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
                    echo "<a href='#' onclick='confirmClone(" . $row['id'] . ", \"" . htmlspecialchars(addslashes($row['server_name'])) . "\")' class='btn btn-clone'>Clone</a>";
                    echo "<a href='#' onclick='confirmDelete(" . $row['id'] . ", \"" . htmlspecialchars(addslashes($row['server_name'])) . "\")' class='btn btn-delete'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No servers found</td></tr>";
            }
            
            // Close statement if it was used
            if (!empty($params)) {
                $stmt->close();
            }
            $conn->close();
            ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <a href="add.php" class="btn">Add New Server</a>
    </div>

    <!-- Delete Confirmation Dialog -->
    <div id="overlay" class="overlay"></div>
    <div id="confirmationDialog" class="confirmation-dialog">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete server: <strong id="serverNameToDelete"></strong>?</p>
        <p>This action cannot be undone.</p>
        <div class="dialog-buttons">
            <button onclick="cancelDelete()" class="btn-cancel">Cancel</button>
            <button onclick="proceedDelete()" class="btn-confirm-delete">Delete</button>
        </div>
    </div>

    <!-- Clone Confirmation Dialog -->
    <div id="cloneDialog" class="confirmation-dialog">
        <h3>Clone Server</h3>
        <p>Create a copy of server: <strong id="serverNameToClone"></strong></p>
        <div class="clone-options">
            <label>
                New Server Name:
                <input type="text" id="newServerName" placeholder="Enter new server name">
            </label>
            <label>
                <input type="checkbox" id="keepPowerStatus" checked>
                Keep same power status
            </label>
        </div>
        <div class="dialog-buttons">
            <button onclick="cancelClone()" class="btn-cancel">Cancel</button>
            <button onclick="proceedClone()" class="btn-confirm">Clone Server</button>
        </div>
    </div>

    <script>
        let serverIdToDelete = null;
        let serverIdToClone = null;
        let originalServerName = '';
        
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
        
        function confirmClone(id, serverName) {
            serverIdToClone = id;
            originalServerName = serverName;
            document.getElementById('serverNameToClone').textContent = serverName;
            document.getElementById('newServerName').value = serverName + '_CLONE';
            document.getElementById('cloneDialog').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function cancelClone() {
            serverIdToClone = null;
            originalServerName = '';
            document.getElementById('cloneDialog').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        function proceedClone() {
            if (serverIdToClone) {
                const newServerName = document.getElementById('newServerName').value.trim();
                if (newServerName && newServerName !== originalServerName) {
                    window.location.href = 'index.php?clone_id=' + serverIdToClone + '&new_name=' + encodeURIComponent(newServerName);
                } else {
                    window.location.href = 'index.php?clone_id=' + serverIdToClone;
                }
            }
        }
        
        // Close dialogs when clicking overlay
        document.getElementById('overlay').addEventListener('click', function() {
            cancelDelete();
            cancelClone();
        });
        
        // Add hover effects to table headers
        document.addEventListener('DOMContentLoaded', function() {
            const headers = document.querySelectorAll('th');
            headers.forEach(header => {
                if (!header.classList.contains('action-column')) {
                    header.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#e0e0e0';
                    });
                    header.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '#f2f2f2';
                    });
                }
            });
        });

        // Handle Enter key in clone dialog
        document.getElementById('newServerName').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                proceedClone();
            }
        });
    </script>
</body>
</html>