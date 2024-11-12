<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS User Login PHP

****************/

session_start();

require('connect.php');

$login_error = "";

if ($_POST && !empty(trim($_POST['username'])) && !empty(trim($_POST['password']))) {
    $username = filter_input(INPUT_POST, 'username', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = "SELECT * FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->bindValue(':username', $username, PDO::PARAM_STR);
    $statement->execute();

    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['userid'] = $user['userid'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['userlevel'] = $user['userlevel'];
        
        header("Location: index.php?loggedin");
        exit;

    } else {
        $login_error = "Invalid username or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <a href="index.php">Home</a>
    <form method="post">
        <fieldset>
            <p>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </p>
            <input type="submit" value="Login"><?= $login_error ?>
        </fieldset>
    </form>
</body>
</html>