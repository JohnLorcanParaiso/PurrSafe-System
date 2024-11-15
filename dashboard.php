<?php
session_start();
include('db.php');
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
            <li><span>Dashboard</span></li>
            <li><span>Add New Cat Profile</span></li>
            <li><span>Report Lost and Found Cat</span></li>
            <li><span>View/Edit Own Profile</span></li>
            <li><span>Help</span></li>
            <li><span>Settings</span></li>
        </ul>
    </div>
    <div class="container">
        <div class="header">
            <div class="nav">
                <div class="search">
                    <input type="text" placeholder="Search..">
                    <button type="submit"><img src="search.png" alt="search"></button>
                </div>
                <div class="user">
                    <button type="submit" class="btn">Add New</button>
                    <button type="submit"><img src="notifications.png" alt="notifications"></button>
                    <div class="img-case">
                        <button type="submit"><img src="user.png" alt="user profile"></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="cards">
                <div class="card">
                    <div class="box">
                        <h1>50</h1>
                        <h3>Add New Cat Profile</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="box">
                        <h1>100</h1>
                        <h3>Report Lost and Found Cat</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="box">
                        <h1>30</h1>
                        <h3>View/Edit Own Profile</h3>
                    </div>
                </div>
            </div>

            <div class="content-2">
                <div class="report-lost">
                    <div class="title">
                        <h2>Report Lost and Found Cat</h2>
                        <button type="submit" class="btn">View All</button>
                    </div>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Breed</th>
                            <th>Gender</th>
                            <th>Color</th>
                            <th>Option</th>
                        </tr>
                        <tr>
                            <td>Dexter</td>
                            <td>Persian</td>
                            <td>Male</td>
                            <td>White</td>
                            <td><button type="submit" class="btn">View</button></td>
                        </tr>
                        <tr>
                            <td>Dexter</td>
                            <td>Persian</td>
                            <td>Male</td>
                            <td>White</td>
                            <td><button type="submit" class="btn">View</button></td>
                        </tr>
                        <tr>
                            <td>Dexter</td>
                            <td>Persian</td>
                            <td>Male</td>
                            <td>White</td>
                            <td><button type="submit" class="btn">View</button></td>
                        </tr>
                        <tr>
                            <td>Dexter</td>
                            <td>Persian</td>
                            <td>Male</td>
                            <td>White</td>
                            <td><button type="submit" class="btn">View</button></td>
                        </tr>
                        <tr>
                            <td>Dexter</td>
                            <td>Persian</td>
                            <td>Male</td>
                            <td>White</td>
                            <td><button type="submit" class="btn">View</button></td>
                        </tr>
                    </table>
                </div>
                <div class="new-cats">
                    <div class="title">
                        <h2>New Cats</h2>
                        <button type="submit" class="btn">View All</button>
                    </div>
                    <table>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Option</th>
                        </tr>
                        <tr>
                            <td><button type="submit"><img src="user.png" alt="user"></button></td>
                            <td>Dexter</td>
                            <td><button type="submit"><img src="info.png" alt="info"></button></td>
                        </tr>
                        <tr>
                            <td><button type="submit"><img src="user.png" alt="user"></button></td>
                            <td>Dexter</td>
                            <td><button type="submit"><img src="info.png" alt="info"></button></td>
                        </tr>
                        <tr>
                            <td><button type="submit"><img src="user.png" alt="user"></button></td>
                            <td>Dexter</td>
                            <td><button type="submit"><img src="info.png" alt="info"></button></td>
                        </tr>
                        <tr>
                            <td><button type="submit"><img src="user.png" alt="user"></button></td>
                            <td>Dexter</td>
                            <td><button type="submit"><img src="info.png" alt="info"></button></td>
                        </tr>
                        <tr>
                            <td><button type="submit"><img src="user.png" alt="user"></button></td>
                            <td>Dexter</td>
                            <td><button type="submit"><img src="info.png" alt="info"></button></td>
                        </tr>
                    </table>
                </div>
        </div>
    </div>
</body>
</html>
    <?php
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        switch ($action) {
            case 'dashboard':
                header("Location: dashboard.php");
                break;
            
            case 'add_new_cat':
                header("Location: add_cat.php");
                break;
            
            case 'report_cat':
                header("Location: report_cat.php");
                break;
            
            case 'view_profile':
                header("Location: view_profile.php");
                break;
            
            case 'help':
                header("Location: help.php");
                break;
            
            case 'settings':
                header("Location: settings.php");
                break;
            
            case 'search':
                $search_query = isset($_POST['search']) ? $_POST['search'] : '';
                header("Location: search.php?q=" . urlencode($search_query));
                break;
            
            case 'add_new':
                header("Location: add_cat.php");
                break;
            
            case 'notifications':
                header("Location: notifications.php");
                break;
            
            case 'profile':
                header("Location: profile.php");
                break;
            
            case 'view_cat':
                $cat_id = isset($_POST['cat_id']) ? $_POST['cat_id'] : '';
                header("Location: view_cat.php?id=" . urlencode($cat_id));
                break;
            
            default:
                header("Location: dashboard.php");
                break;
        }
        exit();
    }
    ?>
</body>
</html>
