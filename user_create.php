<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS Create User PHP

****************/

session_start();

require('search_logic.php');

if (!isset($_SESSION['userlevel']) || $_SESSION['userlevel'] < 30) {
    header("Location: index.php?accessdenied");
    exit;
}

$u_error = $ps_error = $level_error = "";
$error_flag = false;

if ($_POST && !empty(trim($_POST['username'])) && !empty(trim($_POST['password']))) {
    $username = filter_input(INPUT_POST, 'username', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_input(INPUT_POST, 'confirmpassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $userlevel = filter_input(INPUT_POST, 'userlevel', FILTER_SANITIZE_NUMBER_INT);

    if ($password !== $confirmpassword) {
        $ps_error = "Passwords do not match. Please ensure passwords match to continue.";
        $error_flag = true;
    }

    $query = "SELECT COUNT(*) FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->bindValue(':username', $username, PDO::PARAM_STR);
    $statement->execute();

    if ($statement->fetchColumn() > 0) {
        $u_error = "This username is not available. Please select a different username.";
        $error_flag = true;
    }

    if (!$error_flag) {
        try{

            $password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, password, userlevel) VALUES (:username, :password, :userlevel)";
            $statement = $db->prepare($query);
            $statement->bindValue(':username', $username, PDO::PARAM_STR);
            $statement->bindValue(':password', $password, PDO::PARAM_STR);
            $statement->bindValue(':userlevel', $userlevel, PDO::PARAM_INT);
            $statement->execute();
    
            header("Location: users.php");
            exit;

        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
</head>
<body>
    <?php include 'search.php' ?>
    <a href="users.php">Users</a>
    <form method="post">
        <fieldset>
            <p>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                <?= $u_error ?>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </p>
            <p>
                <label for="confirmpassword">Confirm Password</label>
                <input type="password" id="confirmpassword" name="confirmpassword" required>
                <?= $ps_error ?>
            </p>
            <p>
                <label for="userlevel">User Level</label>
                <select name="userlevel" id="userlevel">
                    <option value="10" selected>Commenter (10)</option>
                    <option value="20">Contributor (20)</option>
                    <option value="30">Administrator (30)</option>
                </select>
            </p>
            <input type="submit" value="Create">
        </fieldset>
    </form>
</body>
</html>