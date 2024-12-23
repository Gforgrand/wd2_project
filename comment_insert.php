<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 25, 2024
    Description: Project - Magic: The Gathering CMS Insert Comment PHP

****************/

    session_start();

    require('connect.php');

    if (!isset($_SESSION['username'])) {
        header("Location: index.php?accessdenied");
        exit;
    }

    if($_POST && !empty(trim($_POST['content']))) {

        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $cardid = filter_input(INPUT_POST, 'cardid', FILTER_SANITIZE_NUMBER_INT);

        $query = "INSERT INTO comments (cardid, username, content) VALUES (:cardid, :username, :content)";
        $statement = $db->prepare($query);
        $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
        $statement->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
        $statement->bindValue(':content', $content, PDO::PARAM_STR);
        $statement->execute();

        header("Location: show.php?cardid=" . $cardid);
        exit;
    }
?>