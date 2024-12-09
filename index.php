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

    $messages = [
        ['condition' => $success, 'message' => "The Magic gathers!"],
        ['condition' => $deleted, 'message' => "The Magic dissipates..."],
        ['condition' => $updated, 'message' => "The Magic is in flux!"],
        ['condition' => $loggedin, 'message' => "Login successful!"],
        ['condition' => $loggedout, 'message' => "Logout successful!"],
        ['condition' => $registered, 'message' => "Thank you for registering!"]
    ];

    $query_cardtypes = "SELECT * FROM cardtypes";
    $statement_cardtypes = $db->prepare($query_cardtypes);
    $statement_cardtypes->execute();

    $query_manacolours = "SELECT * FROM manacolours";
    $statement_manacolours = $db->prepare($query_manacolours);
    $statement_manacolours->execute();

    $query_cardsets = "SELECT * FROM cardsets";
    $statement_cardsets = $db->prepare($query_cardsets);
    $statement_cardsets->execute();

    $query = "SELECT c.*, t.cardtypename, m.colourname, s.cardsetname
              FROM cards c
              JOIN cardtypes t ON c.cardtypeid = t.cardtypeid
              LEFT JOIN cardcosts cc ON c.cardid = cc.cardid
              LEFT JOIN manacolours m ON cc.manaid = m.manaid
              LEFT JOIN cardsetcards cs ON c.cardid = cs.cardid
              LEFT JOIN cardsets s ON cs.cardsetid = s.cardsetid
              WHERE 1=1";

    $bindings = [];

    if ($_GET) {
        if (isset($_GET['search'])) {
            $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $query .= " AND c.cardname LIKE :search";
        }    
        
        if (isset($_GET['cardtypename']) && $_GET['cardtypename'] != 0) {
            $cardtypename = filter_input(INPUT_GET, 'cardtypename', FILTER_SANITIZE_NUMBER_INT);
            $query .= " AND t.cardtypeid = :cardtypename";
            $bindings[':cardtypename'] = $cardtypename;
        }

        if (isset($_GET['colourname'] ) && $_GET['colourname'] != 0) {
            $colourname = filter_input(INPUT_GET, 'colourname', FILTER_SANITIZE_NUMBER_INT);
            $query .= " AND m.manaid = :colourname";
            $bindings[':colourname'] = $colourname;
        }

        if (isset($_GET['cardsetname']) && $_GET['cardsetname'] != 0) {
            $cardsetname = filter_input(INPUT_GET, 'cardsetname', FILTER_SANITIZE_NUMBER_INT);
            $query .= " AND s.cardsetid = :cardsetname";
            $bindings[':cardsetname'] = $cardsetname;
        }
    }

    $statement = $db->prepare($query);
    foreach ($bindings as $key => $value) {
        $statement->bindValue($key, $value, PDO::PARAM_INT);
    }
    if (isset($_GET['search'])) {
        $statement->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $statement->execute();

    if (isset($_GET['clear'])) {
        $_GET['search'] = '';
        unset($_GET['search']);
        header("Location: index.php?cardtypename=0&colourname=0&cardsetname=0");
        exit;
    }

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
    <?php include 'search.php'; ?>
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
            <li><a href="categories.php">Categories</a></li>
        <?php endif ?>
    </ul>
    <form action="">
        <ul id=categories>
            <li>
                <label for="cardtypename">Card Type</label>
                <select name="cardtypename" id="cardtypename">
                    <option value="0">Select a category</option>
                    <?php while($row = $statement_cardtypes->fetch()): ?>
                        <option value="<?= $row['cardtypeid'] ?>" <?= isset($_GET['cardtypename']) && $row['cardtypeid'] == $_GET['cardtypename'] ? 'selected' : '' ?>><?= $row['cardtypename'] ?></option>
                    <?php endwhile ?>
                </select>
            </li>
            <li>
                <label for="colourname">Card Cost</label>
                <select name="colourname" id="colourname">
                    <option value="0">Select a category</option>
                    <?php while($row = $statement_manacolours->fetch()): ?>
                        <option value="<?= $row['manaid'] ?>" <?= isset($_GET['colourname']) && $row['manaid'] == $_GET['colourname'] ? 'selected' : '' ?>><?= $row['colourname'] ?></option>
                    <?php endwhile ?>
                </select>
            </li>
            <li>
                <label for="cardsetname">Set</label>
                <select name="cardsetname" id="cardsetname">
                    <option value="0">Select a category</option>
                    <?php while($row = $statement_cardsets->fetch()): ?>
                        <option value="<?= $row['cardsetid'] ?>" <?= isset($_GET['cardsetname']) && $row['cardsetid'] == $_GET['cardsetname'] ? 'selected' : '' ?>><?= $row['cardsetname'] ?></option>
                    <?php endwhile ?>
                </select>
            </li>
        </ul>
        <input type="submit" id="filter" name="filter" value="Filter">
        <input type="submit" id="clear" name="clear" value="Clear">
    </form>
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
</body>
</html>