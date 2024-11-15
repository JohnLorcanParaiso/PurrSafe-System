<?php

$db_server = "localhost";
    $db_user = "admin";
    $db_pass = "password";
    $db_name = "user_login";
    $connection = " ";

    try{
        $connection = mysqli_connect($db_server, $db_user, $db_pass, $db_name, 3306);
    }
    catch (mysqli_sql_exception){
        echo ' ';
    }

    // if($connection){
    //     echo "Connection Successful";
    // }
    // else{
    //     echo "Failed to connect";
    // }

?>