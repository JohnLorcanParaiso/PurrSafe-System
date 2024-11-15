<?php
 //include ('db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>User Panel</title>
</head>
<body>
    <div class="side-menu">
        <div class="brand-name">
            <img src="logo.png" class="logo">
        </div>
        <ul>
            <li>
                <form method="POST" action="dashboard.php">
                    <button type="submit" name="action" value="dashboard">Dashboard</button>
                </form>
            </li>
            <li>
                <form method="POST" action="dashboard.php">
                    <button type="submit" name="action" value="add_new_cat">Add New Cat Profile</button>
                </form>
            </li>
            <li>
                <form method="POST" action="dashboard.php">
                    <button type="submit" name="action" value="report_cat">Report Lost and Found Cat</button>
                </form>
            </li>
            <li>
                <form method="POST" action="dashboard.php">
                    <button type="submit" name="action" value="view_profile">View/Edit Own Profile</button>
                </form>
            </li>
            <li>
                <form method="POST" action="dashboard.php">
                    <button type="submit" name="action" value="help">Help</button>
                </form>
            </li>
            <li>
                <form method="POST" action="dashboard.php">
                    <button type="submit" name="action" value="settings">Settings</button>
                </form>
            </li>
        </ul>
    </div>
    
    <div class="container">
        <div class="header">
            <div class="nav">
                <div class="search">
                    <input type="text" placeholder="Search..">
                    <button type="submit"><img src="search.png" alt=""></button>
                </div>
                <div class="user">
                    <a href="#" class="btn">Add New</a>
                    <img src="notifications.png" alt="">
                    <div class="img-case">
                        <img src="user.png" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
        </div>
    </div>

    <?php
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'dashboard':
                echo "<p>Dashboard selected</p>";
                break;
            case 'add_new_cat':
                echo "<p>Add New Cat Profile selected</p>";
                break;
            case 'report_cat':
                echo "<p>Report Lost and Found Cat selected</p>";
                break;
            case 'view_profile':
                echo "<p>View/Edit Own Profile selected</p>";
                break;
            case 'help':
                echo "<p>Help selected</p>";
                break;
            case 'settings':
                echo "<p>Settings selected</p>";
                break;
        }
    }
    ?>
</body>
</html>
