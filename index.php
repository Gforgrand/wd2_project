<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 3, 2024
    Description: Project - Magic: The Gathering CMS Index PHP

****************/

    session_start();

    require('connect.php');

    $success = filter_input(INPUT_GET,'success', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $deleted = filter_input(INPUT_GET,'deleted', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $updated = filter_input(INPUT_GET,'updated', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $loggedin = filter_input(INPUT_GET,'loggedin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $loggedout = filter_input(INPUT_GET,'loggedout', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $accessdenied = filter_input(INPUT_GET,'accessdenied', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $registered = filter_input(INPUT_GET,'registered', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $usermgmt = filter_input(INPUT_GET,'managed', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


    $query =
        "SELECT c.cardid, c.cardname, cs.cardsetname
        FROM cards c
        JOIN cardsetcards csc ON c.cardid = csc.cardid
        JOIN cardsets cs ON csc.cardsetid = cs.cardsetid";
    $statement = $db->prepare($query);
    $statement->execute();

    $messages = [
        ['condition' => $success, 'message' => "The Magic gathers!"],
        ['condition' => $deleted, 'message' => "The Magic dissipates..."],
        ['condition' => $updated, 'message' => "The Magic is in flux!"],
        ['condition' => $loggedin, 'message' => "Login successful!"],
        ['condition' => $loggedout, 'message' => "Logout successful!"],
        ['condition' => $registered, 'message' => "Thank you for registering!"]
    ];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Welcome to the Magic: The Gathering Content Management System!</title>
</head>
<body>
    <div id="header">
        <?php foreach ($messages as $message): ?>
            <?php if (null !==$message['condition']): ?>
                <p><?= $message['message'] ?></p>
            <?php endif ?>
        <?php endforeach ?>
        <h1><a href="index.php">Magic: The Gathering Content Management System</a></h1>
    </div>
    <ul id="menu">
        <li><a href="index.php" class="active">Home</a></li>
        <?php if (!isset($_SESSION['username'])): ?>
            <li><a href="login.php">Login</a> / <a href="register.php">Register</a></li>
        <?php else: ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif ?>
        <?php if (isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 20): ?>
            <li><a href="insert.php">Add Card</a></li>
        <?php endif ?>
        <?php if (isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
            <li><a href="users.php">User Management</a></li>
        <?php endif ?>
    </ul>
    <?php while($row = $statement->fetch()): ?>
        <h2><a href="show.php?cardid=<?= $row['cardid'] ?>"><?= $row['cardname'] ?></a></h2>
        <p>Set: <?= $row['cardsetname']?></p>
        <?php if (isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 20): ?>
            <p><small><a href="edit.php?cardid=<?= $row['cardid'] ?>">edit</a></small></p>
        <?php endif ?>
    <?php endwhile ?>
    <?php if (null !== $accessdenied): ?>
        <script>
            alert("You do not have permission to access this. Please log in with the appropriate credentials.");
        </script>
    <?php endif ?>
    <?php if (null !== $usermgmt): ?>
        <script>
            alert("User modified.");
        </script>
    <?php endif ?>
</body>
</html>