<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 6, 2024
    Description: Project - Magic: The Gathering CMS Show PHP

****************/

    session_start();
    require('connect.php');

    $cardid  = filter_input(INPUT_GET,'cardid', FILTER_SANITIZE_NUMBER_INT);

    $comments_query = "SELECT * FROM comments WHERE cardid = :cardid ORDER BY commentid DESC";
    $comments_statement = $db->prepare($comments_query);
    $comments_statement->bindValue('cardid', $cardid, PDO::PARAM_INT);
    $comments_statement->execute();

    $query = "SELECT c.*, t.cardtypename, m.colourname, s.cardsetname, i.filename
              FROM cards c
              JOIN cardtypes t ON c.cardtypeid = t.cardtypeid
              LEFT JOIN cardcosts cc ON c.cardid = cc.cardid
              LEFT JOIN manacolours m ON cc.manaid = m.manaid
              LEFT JOIN cardsetcards cs ON c.cardid = cs.cardid
              LEFT JOIN cardsets s ON cs.cardsetid = s.cardsetid
              LEFT JOIN images i ON c.cardid = i.cardid
              WHERE c.cardid = :cardid LIMIT 1";
    $statement = $db->prepare($query);
    $statement->bindValue('cardid', $cardid, PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetch();

    $cardDetails = [
        'Card Cost' => $row['colourname'],
        'Cardtype' => $row['cardtypename'],
        'Power' => $row['power'],
        'Toughness' => $row['toughness'],
        'Set' => $row['cardsetname']
    ];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Card</title>
</head>
<body>
    <?php include 'search.php'; ?>
    <div id="header">
        <h1><a href="index.php">Magic: The Gathering Content Management System</a></h1>
    </div>
    <ul id="menu">
        <li><a href="index.php">Home</a></li>
    </ul>
    <h2><?= $row['cardname'] ?></h2>
    <?php if (isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 20): ?>
        <p><small><a href="edit.php?cardid=<?= $row['cardid'] ?>">edit</a></small></p>
    <?php endif ?>
    <?php foreach ($cardDetails as $label => $value): ?>
        <?php if (!is_null($value) && !empty($value)): ?>
            <p><?= $label ?>: <?= $value ?></p>
        <?php endif ?>
    <?php endforeach ?>
    <?php if (!empty($row['filename'])): ?>
        <img src="uploads/<?= $row['filename'] ?>" alt="<?= $row['cardname'] ?>">
    <?php endif ?>
    <?php if (isset($_SESSION['username'])): ?>
        <form action=comment_insert.php method="post">
            <input type="hidden" name="cardid" value="<?= $row['cardid'] ?>">
            <fieldset>
                <p>
                    <label for="content">Comment</label>
                    <textarea id="content" name="content"><?= isset($_SESSION['content']) ? $_SESSION['content'] : '' ?></textarea>
                </p>
                <input type="submit" name="comment" value="Comment">
            </fieldset>
        </form>
    <?php endif ?>
    <h4>Comments</h4>
    <?php while($comment = $comments_statement->fetch()): ?>
        <?php if ($comment['commentaction'] < 3): ?>
            <p><small>On <?= date("F d, Y, h:i a", strtotime($comment['timestamp'])); ?>, <?= $comment['username'] ?> said:</small></p>
            <?php if ($comment['commentaction'] > 1): ?>
                <p>-- <?= str_ireplace(array('a','e','i','o','u'), '*', $comment['content']) ?></p>
            <?php else: ?>
                <p>-- <?= $comment['content'] ?></p>
            <?php endif ?>
        <?php else: ?>
            <p><small>The comment made on <?= date("F d, Y, h:i a", strtotime($comment['timestamp'])); ?> has been hidden. Please contact the moderator if you wish to appeal this action.</small></p>    
        <?php endif ?>
        <?php if (isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
            <form action="comment_moderate.php" method="post">
                <input type="hidden" name="cardid" value="<?= $row['cardid'] ?>">
                <input type="hidden" name="commentid" value="<?= $comment['commentid'] ?>">
                <label for="commentaction"><input type="submit" name="moderate" value="Moderate"></label>
                <select name="commentaction" id="commentaction">
                    <option value="1" <?= 1 == $comment['commentaction'] ? 'selected' : '' ?>>No Action</option>
                    <option value="2" <?= 2 == $comment['commentaction'] ? 'selected' : '' ?>>Disenvowel</option>
                    <option value="3" <?= 3 == $comment['commentaction'] ? 'selected' : '' ?>>Hide</option>
                </select>
                <button type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
            </form>
        <?php endif ?>
    <?php endwhile ?>
</body>
</html>