<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 25, 2024
    Description: Project - Magic: The Gathering CMS Moderate Comment PHP

****************/

    session_start();

    require('connect.php');

    if (!isset($_SESSION['userlevel']) || $_SESSION['userlevel'] < 30) {
        header("Location: index.php?accessdenied");
        exit;
    }

    if($_POST) {

        $cardid = filter_input(INPUT_POST, 'cardid', FILTER_SANITIZE_NUMBER_INT);
        $commentaction = filter_input(INPUT_POST, 'commentaction', FILTER_SANITIZE_NUMBER_INT);
        $commentid = filter_input(INPUT_POST, 'commentid', FILTER_SANITIZE_NUMBER_INT);

        if(isset($_POST['delete'])) {
            $query = "DELETE FROM comments WHERE commentid = :commentid LIMIT 1";
            $statement = $db->prepare($query);
            $statement->bindValue(':commentid', $commentid, PDO::PARAM_INT);
            $statement->execute();

            header("Location: show.php?cardid=" . $cardid);
            exit;
        }
        
        if(isset($_POST['moderate'])) {
            $query = "UPDATE comments SET commentaction = :commentaction WHERE commentid = :commentid";
            $statement = $db->prepare($query);
            $statement->bindValue(':commentaction', $commentaction, PDO::PARAM_INT);
            $statement->bindValue(':commentid', $commentid, PDO::PARAM_INT);
            $statement->execute();
    
            header("Location: show.php?cardid=" . $cardid);
            exit;
        }
    }
?>