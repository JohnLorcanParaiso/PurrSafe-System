<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'dashboard':
            header("Location: dashboard.php");
            exit();
            
        case 'add_new_cat':
            header("Location: add_new_cat.php");
            exit();
            
        case 'report_cat':
            header("Location: report_cat.php");
            exit();
            
        case 'view_profile':
            header("Location: view_profile.php");
            exit();
            
        case 'help':
            header("Location: help.php");
            exit();
            
        case 'settings':
            header("Location: settings.php");
            exit();
            
        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
            
        default:
            header("Location: dashboard.php");
            exit();
    }
}

$cat_profile_count = 50;  
$report_count = 100;      
$profile_count = 30;      
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
        }
        body{
            min-height: 100vh;
        }
        a{
            text-decoration: none;
        }
        .logo {
            width: 100px;       
            height: auto;       
            display: block;   
            margin: 20px;     
            padding: 14px;      
            border-radius: 8px; 
        }

        li{
            list-style: none;
        }
        h1,
        h2{
            color: #444;
        }
        h3{
            color: #999;
        }
        .btn{
            background: #E8F0F7;
            color: black;
            padding: 5px 10px;
            text-align: center;
        }
        .btn:hover{
            color: black;
            background: white;
            padding: 3px 8px;
            border: 2px solid #E8F0F7;
        }
        .title{
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 15px 10px;
            border-bottom: 2px solid #999;
        }
        table{
            padding: 10px;
        }
        th,td{
            text-align: left;
            padding: 8px;
        }
        .side-menu{
            position: fixed;
            background: #E8F0F7;
            width: 19vw;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .side-menu .brand-name{
            height: 10vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .side-menu li{
            font-size: 20px;
            padding: 10px 40px;
            color: black;
            margin: 5%;
            display: flex;
            align-items: center;
        }
        .side-menu li:hover{
            background: white;
            color: lightblue;
        }
        .container{
            position: absolute;
            right: 0;
            width: 80vw;
            height: 100vh;
        }

        .container .header{
            position: fixed;
            top: 0;
            right: 0;
            width: 80vw;
            height: 10vh;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            z-index: 1;
        }
        .container .header .nav{
            width: 90%;
            display: flex;
            align-items: center;
        }
        .container .header .nav .search{
            flex: 3;
            display: flex;
            justify-content: center;
        }
        .container .header .nav .search input[type=text]{
            border: none;
            background: #f1f1f1;
            padding: 10px;
            width: 50%;
            border-radius: 4px 0 0 4px;
        }
        .container .header .nav .search button{
            width: 40px;
            height: 40px;
            border: none;
            background: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 0 4px 4px 0;
            transition: background-color 0.3s ease;
        }
        .container .header .nav .search button:hover{
            background: #e1e1e1;
        }
        .container .header .nav .search button img{
            width: 20px;
            height: 20px;
        }
        .container .header .nav .user{
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
        }
        .container .header .nav .user button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container .header .nav .user .icon-btn {
            width: 40px;
            height: 40px;
            padding: 8px;
            border-radius: 50%;
            overflow: hidden;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .container .header .nav .user .icon-btn:hover {
            transform: scale(1.1);
            background-color: #f1f1f1;
        }

        .container .header .nav .user .img-case button {
            width: 40px;
            height: 40px;
            padding: 8px;
            border-radius: 50%;
            overflow: hidden;
        }

        .container .header .nav .user button img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .container .header .nav .user .btn {
            background: #E8F0F7;
            color: black;
            padding: 8px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .container .header .nav .user .btn:hover {
            background: #D1E4F5;
        }

        .container .header .nav .user .profile-btn {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 50%;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .container .header .nav .user .profile-btn:hover {
            transform: scale(1.1);
        }

        .container .header .nav .user .profile-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .container .header .nav .user .img-case{
            position: relative;
            width: 50px;
            height: 50px;
        }
        .container .header .nav .user .img-case img{
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .container .content{
            position: relative;
            margin-top: 10vh;
            min-height: 90vh;
        }
        .container .content .cards{
            padding: 20px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .container .content .cards .card{
            width: 300px;
            height: 180px;
            background: white;
            margin: 20px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) ;
            transition: transform 0.3s ease;
        }
        .container .content .cards .card:hover{
            transform: translateY(-5px);
        }
        .container .content .cards .card .box{
            text-align: center;
            padding: 20px;
            width: 100%;
        }
        .container .content .cards .card .box h1{
            font-size: 60px;
            margin-bottom: 15px;
            color: #444;
            font-weight: 600;
        }
        .container .content .cards .card .box h3{
            font-size: 18px;
            color: #999;
            line-height: 1.4;
            padding: 0 10px;
        }
        .container .content .content-2{
            min-height: 60vh;
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .container .content .content-2 .report-lost{
            min-height: 50vh;
            flex: 5;
            background: white;
            margin: 0 25px 25px 25px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) ;
            display: flex;
            flex-direction: column;
        }
        .container .content .content-2 .new-cats{
            flex: 2;
            background: white;
            min-height: 50vh;
            margin: 0 25px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) ;
            display: flex;
            flex-direction: column;
        }
        .container .content .content-2 .new-cats td:nth-child(1) img{ 
            height: 30px;
            width: 30px;
        }
        @media screen and (max-witdh: 1050px) {
            .side-menu li{
                font-size: 18px;
            }
        }
        @media screen and (max-witdh: 940px) {
            .side-menu li span{
                display: none;
            }
            .side-menu{
                align-items: center;
            }
        }
        @media screen and (max-witdh: 536px){
            .container .content .cards{
                justify-content: center;
            }
        }
        .side-menu .menu-btn {
            background: none;
            border: none;
            color: black;
            font-size: 20px;
            cursor: pointer;
            width: 100%;
            text-align: left;
            padding: 0;
        }
        .side-menu li {
            font-size: 20px;
            padding: 10px 40px;
            color: black;
            margin: 5%;
            display: flex;
            align-items: center;
        }
        .side-menu li:hover {
            background: white;
            color: lightblue;
        }
        .side-menu li:hover .menu-btn {
            color: lightblue;
        }
        .side-menu form {
            width: 100%;
        }
    </style>
    <title>User Panel</title>
</head>
<body>
    <div class="side-menu">
        <div class="brand-name">
            <img src="logo.png" class="logo">
        </div>
        <ul>
            <li>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="dashboard" class="menu-btn">
                        <span>Dashboard</span>
                    </button>
                </form>
            </li>
            <li>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="add_new_cat" class="menu-btn">
                        <span>Add New Cat Profile</span>
                    </button>
                </form>
            </li>
            <li>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="report_cat" class="menu-btn">
                        <span>Report Lost and Found Cat</span>
                    </button>
                </form>
            </li>
            <li>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="view_profile" class="menu-btn">
                        <span>View/Edit Own Profile</span>
                    </button>
                </form>
            </li>
            <li>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="help" class="menu-btn">
                        <span>Help</span>
                    </button>
                </form>
            </li>
            <li>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="settings" class="menu-btn">
                        <span>Settings</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
    <div class="container">
        <div class="header">
            <div class="nav">
                <form method="POST" class="search">
                    <input type="text" name="search" placeholder="Search..">
                    <button type="submit" name="action" value="search">
                        <img src="search.png" alt="search" style="cursor: pointer;">
                    </button>
                </form>
                <div class="user">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="action" value="add_new" class="btn">Add New</button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="action" value="notifications" class="icon-btn">
                            <img src="notifications.png" alt="notifications" style="cursor: pointer;">
                        </button>
                    </form>
                    <div class="img-case">
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="action" value="profile" class="icon-btn">
                                <img src="user.png" alt="user profile" style="cursor: pointer;">
                            </button>
                        </form>
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