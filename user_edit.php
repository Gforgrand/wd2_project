<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS Edit User PHP

****************/

session_start();

require('connect.php');

if (!isset($_SESSION['userlevel']) || $_SESSION['userlevel'] < 30) {
    header("Location: index.php?accessdenied");
    exit;
}

$u_error = $ps_error = $level_error = "";
$error_flag = false;

if ($_POST &&
    isset($_POST['userlevel']) &&
    !empty(trim($_POST['userlevel'])) &&
    isset($_POST['userid'])) {

    $userid = filter_input(INPUT_POST,'userid', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $userlevel = filter_input(INPUT_POST, 'userlevel', FILTER_SANITIZE_NUMBER_INT);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_input(INPUT_POST, 'confirmpassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($password !== $confirmpassword) {
        $ps_error = "Passwords do not match. Please ensure passwords match to continue.";
        $error_flag = true;
    }

    $query = "SELECT COUNT(*) FROM users WHERE username = :username AND userid != :userid";
    $statement = $db->prepare($query);
    $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
    $statement->bindValue(':username', $username, PDO::PARAM_STR);
    $statement->execute();

    if ($statement->fetchColumn() > 0) {
        $u_error = "This username is not available. Please select a different username.";
        $error_flag = true;
    }

    if (empty($_POST['userlevel']) || $_POST['userlevel'] > 30 || $_POST['userlevel'] < 1) {
        $level_error = "Please select a User Level.";
        $error_flag = true;
    }

    // DELETIONS
    if(isset($_POST['delete'])) {
        try {
            
            $query = "DELETE FROM users WHERE userid = :userid";
            $statement = $db->prepare($query);
            $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
            $statement->execute();

            header("Location: users.php");
            exit;

        } catch (Exception $exception) {
            echo "Deletion failed: " . $exception->getMessage();
        }
    }

    // UPDATES
    if (!$error_flag) {
        try {
            if (!empty($password) || !empty($confirmpassword)) {

                $password = password_hash($password, PASSWORD_DEFAULT);
                
                $query = "UPDATE users SET password = :password WHERE userid = :userid";
                $statement = $db->prepare($query);
                $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
                $statement->bindValue(':password', $password, PDO::PARAM_STR);
                $statement->execute();
            }
                    
            $query = "UPDATE users SET username = :username, userlevel = :userlevel WHERE userid = :userid";
            $statement = $db->prepare($query);
            $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
            $statement->bindValue(':username', $username, PDO::PARAM_STR);
            $statement->bindValue(':userlevel', $userlevel, PDO::PARAM_INT);
            $statement->execute();

            header("Location: users.php");
            exit;
        
        } catch (Exception $exception) {
            echo "Update failed: " . $exception->getMessage();
        }
    }
    
} else if(isset($_GET['userid'])) {
    $userid = filter_input(INPUT_GET,'userid', FILTER_SANITIZE_NUMBER_INT);

    $query = "SELECT * FROM users WHERE userid = :userid LIMIT 1";
    $statement = $db->prepare($query);
    $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
    $statement->execute();
    $post = $statement->fetch();

} else {
    $userid = false;
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
    <a href="users.php">Users</a>
    <?php if ($userid && isset($post) && $post): ?>
        <form method="post">
            <fieldset>
                <input type="hidden" name="userid" value="<?= $post['userid'] ?>">
                <p>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= $post['username'] ?>"required>
                    <?= $u_error ?>
                </p>
                <p>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Use existing password">
                </p>
                <p>
                    <label for="confirmpassword">Confirm Password</label>
                    <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Use existing password">
                </p>
                <p>
                    <label for="userlevel">User Level</label>
                    <select name="userlevel" id="userlevel">
                        <option value="10" <?= 10 == $post['userlevel'] ? 'selected' : '' ?>>Commenter (10)</option>
                        <option value="20" <?= 20 == $post['userlevel'] ? 'selected' : '' ?>>Contributor (20)</option>
                        <option value="30" <?= 30 == $post['userlevel'] ? 'selected' : '' ?>>Administrator (30)</option>
                    </select>
                </p>
                <p>
                    <input type="submit" value="Update">
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                </p>
            </fieldset>
        </form>
    <?php endif ?>
</body>
</html>