<?php
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_db_user';
$password = 'your_db_password';

// Connect to DB
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $poweron = $_POST['poweron'];
    $info = $_POST['info'];

    $stmt = $conn->prepare("UPDATE servers SET poweron = ?, info = ? WHERE id = ?");
    $stmt->bind_param("ssi", $poweron, $info, $id);
    
    if ($stmt->execute()) {
        echo "Record updated successfully!";
    } else {
        echo "Update failed: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!-- HTML FORM -->
<h2>Update Server Info</h2>
<form method="POST" action="">
    <label>Server ID:</label>
    <input type="number" name="id" required><br><br>

    <label>Power Status:</label>
    <select name="poweron">
        <option value="PoweredOn">PoweredOn</option>
        <option value="PoweredOff">PoweredOff</option>
    </select><br><br>

    <label>Info:</label>
    <textarea name="info" rows="4" cols="30"></textarea><br><br>

    <button type="submit">Update</button>
</form>
