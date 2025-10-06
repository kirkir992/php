<?php
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_db_user';
$password = 'your_db_password';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle update
// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $poweron = $_POST['poweron'];
    $info = $_POST['info'];

    $sql = "UPDATE servers SET poweron = ?, info = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssi", $poweron, $info, $id);

    if ($stmt->execute()) {
        echo "Record updated successfully. <a href='index.php'>Back to list</a>";
    } else {
        echo "Update failed: " . $stmt->error;
    }
    $stmt->close();
}

} else if (isset($_GET['id'])) {
    // Fetch existing data
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM servers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
}
?>

<?php if (isset($record)): ?>
    <h2>Update Server Record</h2>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?= $record['id'] ?>">

        <p><strong>Host:</strong> <?= $record['host'] ?></p>

        <label>Power Status:</label>
        <select name="poweron">
            <option value="PoweredOn" <?= $record['poweron'] == 'PoweredOn' ? 'selected' : '' ?>>PoweredOn</option>
            <option value="PoweredOff" <?= $record['poweron'] == 'PoweredOff' ? 'selected' : '' ?>>PoweredOff</option>
        </select><br><br>

        <label>Info:</label><br>
        <textarea name="info" rows="4" cols="40"><?= htmlspecialchars($record['info']) ?></textarea><br><br>

        <button type="submit">Update</button>
        <a href="index.php">Cancel</a>
    </form>
<?php else: ?>
    <p>No record found.</p>
<?php endif; ?>

<?php $conn->close(); ?>
