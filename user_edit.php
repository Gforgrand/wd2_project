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

$error_flag = false;
if ($_POST &&
    isset($_POST['userlevel']) &&
    !empty(trim($_POST['userlevel'])) &&
    isset($_POST['userid'])) {

    $userid = filter_input(INPUT_POST,'userid', FILTER_SANITIZE_NUMBER_INT);
    $userlevel = filter_input(INPUT_POST, 'userlevel', FILTER_SANITIZE_NUMBER_INT);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_input(INPUT_POST, 'confirmpassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($password !== $confirmpassword) {
        $error_flag = true;
    }

    if (empty($_POST['userlevel']) || $_POST['userlevel'] > 30 || $_POST['userlevel'] < 1) {
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
        if (!empty($password) || !empty($confirmpassword)) {
            try {

                $password = password_hash($password, PASSWORD_DEFAULT);
                
                $query = "UPDATE users SET password = :password, userlevel = :userlevel WHERE userid = :userid";
                $statement = $db->prepare($query);
                $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
                $statement->bindValue(':password', $password, PDO::PARAM_STR);
                $statement->bindValue(':userlevel', $userlevel, PDO::PARAM_INT);
                $statement->execute();
    
                header("Location: users.php");
                exit;
            
            } catch (Exception $exception) {
                echo "Update failed: " . $exception->getMessage();
            }
        } else {
            try {
                
                $query = "UPDATE users SET userlevel = :userlevel WHERE userid = :userid";
                $statement = $db->prepare($query);
                $statement->bindValue(':userid', $userid, PDO::PARAM_INT);
                $statement->bindValue(':userlevel', $userlevel, PDO::PARAM_INT);
                $statement->execute();
    
                header("Location: users.php");
                exit;
            
            } catch (Exception $exception) {
                echo "Update failed: " . $exception->getMessage();
            }
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
    header("Location: users.php");
    exit;
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
                <p>Username: <?= $post['username'] ?></p>
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
                    <input type="text" id="userlevel" name="userlevel" value="<?= $post['userlevel'] ?>" required>
                </p>
                <input type="submit" value="Update">
                <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
            </fieldset>
        </form>
    <?php else: ?>
        <script>
            alert("Invalid modification. Passwords in both fields must match, and the user level must be between 1 and 30.")
        </script>
    <?php endif ?>
</body>
</html>