<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 6, 2024
    Description: Project - Magic: The Gathering CMS Edit PHP

****************/

    require('connect.php');
    require('authenticate.php');
    require('get_or_create.php');

    if ($_POST &&
        isset($_POST['cardname']) &&
        !empty(trim($_POST['cardname'])) &&
        isset($_POST['cardtypename']) &&
        !empty(trim($_POST['cardtypename'])) &&
        isset($_POST['cardsetname']) &&
        !empty(trim($_POST['cardsetname'])) &&
        isset($_POST['cardid'])) {
            
        $cardid = filter_input(INPUT_POST,'cardid', FILTER_SANITIZE_NUMBER_INT);
        $cardname = filter_input(INPUT_POST,'cardname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $power = filter_input(INPUT_POST,'power', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $toughness = filter_input(INPUT_POST,'toughness', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if(isset($_POST['delete'])) {
            try {
                $db->beginTransaction();
                
                $query = "DELETE FROM cardsetcards WHERE cardid = :cardid";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->execute();

                $query = "DELETE FROM cardcosts WHERE cardid = :cardid";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->execute();

                $query = "DELETE FROM cards WHERE cardid = :cardid LIMIT 1";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->execute();
                
                $db->commit();

                header("Location: index.php?deleted");
                exit;

            } catch (Exception $exception) {
                $db->rollBack();
                echo "Transaction failed: " . $exception->getMessage();
            }
        }

        $query = "UPDATE cards SET cardname = :cardname, cardtypeid = :cardtypeid, power = :power, toughness = :toughness WHERE cardid = :cardid LIMIT 1";
        $statement = $db->prepare($query);
        $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
        $statement->bindValue(':cardname', $cardname, PDO::PARAM_STR);
        $statement->bindValue(':cardtypeid', $cardtypeid, PDO::PARAM_INT);
        $statement->bindValue(':power', $power, PDO::PARAM_STR);
        $statement->bindValue(':toughness', $toughness, PDO::PARAM_STR);
        $statement->execute();

        header("Location: index.php?updated");
        exit;
    } else if(isset($_GET['cardid'])) {
        $cardid = filter_input(INPUT_GET,'cardid', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT * FROM blogs WHERE cardid = :cardid LIMIT 1";
        $statement = $db->prepare($query);
        $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
        $statement->execute();
        $post = $statement->fetch();
    } else {
        $cardid = false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Change Card</title>
</head>

<body>
        <div id="header">
            <h1><a href="index.php">Blog - Edit Post</a></h1>
        </div>
        <form id="editForm" method="post">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <p>
                <label for="title">Title</label>
                <input id="title" name="title" value="<?= $post['title'] ?>">
            </p>
            <p>
                <label for="content">Content</label>
                <textarea id="content" name="content"><?= $post['content'] ?></textarea>
            </p>
            <button type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
            <?php if (
                $_POST &&
                empty(trim($_POST['cardname'])) &&
                empty(trim($_POST['cardtypename'])) &&
                empty(trim($_POST['cardsetname']))
            ): ?>
                <p class="warning">The Card Name, Cardtype, and Set must each contain at least 1 non-whitespace character.</p>
            <?php endif ?>
        </form>
</body>
</html>