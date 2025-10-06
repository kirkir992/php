Fatal error: Call to a member function bind_param() on a non-object in update_server.php on line 24
<?php
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_db_user';
$password = 'your_db_password';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM servers ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Server List</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        a.button {
            padding: 4px 8px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h2>All Server Records</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Host</th>
                <th>OS</th>
                <th>Env</th>
                <th>Group Owner</th>
                <th>Contact</th>
                <th>Org</th>
                <th>Power</th>
                <th>Info</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['data'] ?></td>
                        <td><?= $row['host'] ?></td>
                        <td><?= $row['os'] ?></td>
                        <td><?= $row['env'] ?></td>
                        <td><?= $row['grp_own'] ?></td>
                        <td><?= $row['contact'] ?></td>
                        <td><?= $row['org'] ?></td>
                        <td><?= $row['poweron'] ?></td>
                        <td><?= $row['info'] ?></td>
                        <td><a class="button" href="update_server.php?id=<?= $row['id'] ?>">Edit</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="11">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php $conn->close(); ?>
