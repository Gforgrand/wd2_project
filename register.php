<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS User Registration PHP

****************/

session_start();

require('connect.php');

$u_error = $ps_error = $captcha_error = "";
$error_flag = false;

if ($_POST) {
    $username = filter_input(INPUT_POST, 'username', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_input(INPUT_POST, 'confirmpassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $captcha = filter_input(INPUT_POST, 'captcha', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $userlevel = 10;

    if (empty(trim($_POST['password'])) || $password !== $confirmpassword) {
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

    if (!$error_flag && !empty(trim($captcha)) && $captcha == $_SESSION['captcha']) {
        try{

            $password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, password, userlevel) VALUES (:username, :password, :userlevel)";
            $statement = $db->prepare($query);
            $statement->bindValue(':username', $username, PDO::PARAM_STR);
            $statement->bindValue(':password', $password, PDO::PARAM_STR);
            $statement->bindValue(':userlevel', $userlevel, PDO::PARAM_INT);
            $statement->execute();

            $_SESSION['captcha'] = '';
            unset($_SESSION['captcha']);
    
            header("Location: index.php?registered");
            exit;

        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    } 
    
    if (empty(trim($captcha)) || $captcha != $_SESSION['captcha']) {
        $captcha_error = "Please try the CAPTCHA again.";
    }
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
    <?php include 'search.php'; ?>
    <a href="index.php">Home</a>
    <form method="post">
        <fieldset>
            <p>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= isset($_POST['username']) ? $_POST['username'] : ''?>">
                <?= $u_error ?>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="<?= isset($_POST['password']) ? $_POST['password'] : ''?>">
            </p>
            <p>
                <label for="confirmpassword">Confirm Password</label>
                <input type="password" id="confirmpassword" name="confirmpassword" value="<?= isset($_POST['confirmpassword']) ? $_POST['confirmpassword'] : ''?>">
                <?= $ps_error ?>
            </p>
            <p>
                <img src="captcha.php" alt="CAPTCHA">
                <input type="text" name="captcha" placeholder="Enter CAPTCHA">
                <?= $captcha_error ?>
            </p>
            <input type="submit" value="Register">
        </fieldset>
    </form>
</body>
</html>