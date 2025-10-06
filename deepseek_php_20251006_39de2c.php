<?php
// Enhanced index.php with better sorting visuals
include 'config.php';

// [Previous PHP code remains the same until the style section]
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
        th { background-color: #f2f2f2; cursor: pointer; position: relative; padding-right: 25px; }
        th:hover { background-color: #e0e0e0; }
        th.sortable { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>');
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
        }
        th.sort-asc { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/></svg>');
            background-color: #d4edff;
        }
        th.sort-desc { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 9 5-5 5 5"/></svg>');
            background-color: #d4edff;
        }
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
        .action-column { width: 200px; }
        .current-sort { background-color: #d4edff; }
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
        .sort-info {
            margin: 10px 0;
            color: #666;
            font-style: italic;
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
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars(strtolower($sort_order)); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="?sort=<?php echo htmlspecialchars($sort_column); ?>&order=<?php echo htmlspecialchars(strtolower($sort_order)); ?>" style="margin-left: 10px;">Clear Search</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="sort-info">
        Sorted by: <strong><?php echo ucfirst(str_replace('_', ' ', $sort_column)); ?></strong> 
        (<?php echo $sort_order === 'ASC' ? 'Ascending' : 'Descending'; ?>)
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="sortable <?php echo $sort_column === 'server_name' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                    onclick="window.location.href='<?php echo getSortUrl('server_name', $sort_column, $sort_order); ?>'">
                    Server Name
                </th>
                <th class="sortable <?php echo $sort_column === 'operating_system' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                    onclick="window.location.href='<?php echo getSortUrl('operating_system', $sort_column, $sort_order); ?>'">
                    Operating System
                </th>
                <th class="sortable <?php echo $sort_column === 'group_owner' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                    onclick="window.location.href='<?php echo getSortUrl('group_owner', $sort_column, $sort_order); ?>'">
                    Group Owner
                </th>
                <th class="sortable <?php echo $sort_column === 'contact' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                    onclick="window.location.href='<?php echo getSortUrl('contact', $sort_column, $sort_order); ?>'">
                    Contact
                </th>
                <th class="sortable <?php echo $sort_column === 'power_status' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                    onclick="window.location.href='<?php echo getSortUrl('power_status', $sort_column, $sort_order); ?>'">
                    Power Status
                </th>
                <th class="sortable <?php echo $sort_column === 'last_modified_date' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                    onclick="window.location.href='<?php echo getSortUrl('last_modified_date', $sort_column, $sort_order); ?>'">
                    Last Modified
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
                    echo "<a href='#' onclick='confirmDelete(" . $row['id'] . ", \"" . htmlspecialchars(addslashes($row['server_name'])) . "\")' class='btn btn-delete'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No servers found</td></tr>";
            }
            
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

    <!-- Confirmation Dialog and JavaScript remain the same -->
    <!-- ... -->
</body>
</html>