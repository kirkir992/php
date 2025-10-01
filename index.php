<?php
// index.php - Main Home Page
require_once 'config.php';

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
    exit;
}

// Check session timeout
if (time() - $_SESSION['login_time'] > LOGIN_TIMEOUT) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Update login time
$_SESSION['login_time'] = time();

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .user-details {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .detail-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .feature-card h3 {
            color: #007bff;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-section">
            <h1>Welcome to <?php echo APP_NAME; ?></h1>
            <p>You have successfully authenticated with Active Directory.</p>
        </section>

        <section class="user-details">
            <h2>Your Information</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Username:</span>
                    <br><?php echo htmlspecialchars($user['username']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Full Name:</span>
                    <br><?php echo htmlspecialchars($user['fullname']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <br><?php echo htmlspecialchars($user['email']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Department:</span>
                    <br><?php echo htmlspecialchars($user['department']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Title:</span>
                    <br><?php echo htmlspecialchars($user['title']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <br><?php echo htmlspecialchars($user['phone']); ?>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="feature-card">
                <h3>Company Resources</h3>
                <p>Access internal documents, policies, and company resources.</p>
            </div>
            <div class="feature-card">
                <h3>Team Collaboration</h3>
                <p>Connect with your team members and collaborate on projects.</p>
            </div>
            <div class="feature-card">
                <h3>HR Portal</h3>
                <p>Manage your personal information and HR-related tasks.</p>
            </div>
        </section>
    </main>
</body>
</html>
