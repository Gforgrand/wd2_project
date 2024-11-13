<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS User Management PHP

****************/

session_start();
    
require('connect.php');

if (!isset($_SESSION['userlevel']) || $_SESSION['userlevel'] < 30) {
    header("Location: index.php?accessdenied");
    exit;
}

$query = "SELECT * FROM users";
$statement = $db->prepare($query);
$statement->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
</head>
<body>
<h1><a href="index.php">Magic: The Gathering Content Management System</a></h1>
<h2>Users</h2>
<p><a href="user_create.php">CREATE USER</a></p>
    <table>
        <tr>
            <th></th>
            <th>Username</th>
            <th>User Level</th>
            <th>Password (Salted and Hashed)</th>
        </tr>
        <?php while($row = $statement->fetch()): ?>
            <tr>
                <td><a href="user_edit.php?userid=<?= $row['userid'] ?>">UPDATE</a></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['userlevel']?></td>
                <td><?= $row['password']?></td>
            </tr>
        <?php endwhile ?>
    </table>
</body>
</html>