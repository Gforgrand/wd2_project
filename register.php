<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS User Registration PHP

****************/

require('connect.php');

if ($POST && ) {
    $query = "INSERT INTO users (username, confirmpassword) VALUES (:username, :confirmpassword)";
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
</head>
<body>
    <a href="index.php">Home</a>
    <form method="post">
        <fieldset>
            <label for="username">Username</label>
            <input type="text" id="username" name="username">
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <label for="confirmpassword">Confirm Password</label>
            <input type="password" id="confirmpassword" name="confirmpassword">
        </fieldset>
    </form>
</body>
</html>