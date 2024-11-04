<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 3, 2024
    Description: Project - Magic: The Gathering CMS Insert PHP

****************/

    require('connect.php');
    require('authenticate.php');

    $query_cardtypes = "SELECT * FROM cardtypes";
    $statement_cardtypes = $db->prepare($query_cardtypes);
    $statement_cardtypes->execute();

    /*
    $query_manacolours = "SELECT * FROM manacolours";
    $statement_manacolours = $db->prepare($query_manacolours);
    $statement_manacolours->execute();
    */

    /*
    $query_cardsets = "SELECT * FROM cardsets";
    $statement_cardsets = $db->prepare($query_cardsets);
    $statement_cardsets->execute();
    */

    if(
        $_POST
        && !empty(trim($_POST['cardname']))
        && !empty(trim($_POST['cardtypename']))
        //&& !empty(trim($_POST['cardsetname']))
        ) {

        $cardname = filter_input(INPUT_POST, 'cardname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $cardtypeid = filter_input(INPUT_POST, 'cardtype', FILTER_VALIDATE_INT);
        //$colourid = filter_input(INPUT_POST, 'colourname', FILTER_VALIDATE_INT);
        //$cardsetid = filter_input(INPUT_POST, 'cardsetname', FILTER_VALIDATE_INT);
        $power = filter_input(INPUT_POST, 'power', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $toughness = filter_input(INPUT_POST, 'toughness', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        //cardtype
        if ($_POST['cardtype'] == 'new' && !empty(trim($_POST['newcardtype']))) {
            $new = filter_input(INPUT_POST, 'newcardtype', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $query = "SELECT cardtypeid FROM cardtypes WHERE cardtypename = :cardtypename";
            $statement = $db->prepare($query);
            $statement->bindValue(':cardtypename', $new);
            $statement->execute();

            $exists = false;
            while ($row = $statement->fetch()) {
                if ($row['cardtypeid']) {
                    $exists = true;
                    $cardtypeid = $row['cardtypeid'];
                    break;
                }
            }

            if (!$cardtype_exists) {
                $query = "INSERT INTO cardtypes (cardtypename) VALUES (:cardtypename)";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardtypename', $new);
                $statement->execute();
                $cardtypeid = $db->lastInsertId();
            }
        }

        /*
        //set
        if ($_POST['cardset'] == 'new' && !empty(trim($_POST['newcardset']))) {
            $new = filter_input(INPUT_POST, 'newcardset', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $query = "SELECT cardsetid FROM cardsets WHERE cardsetname = :cardsetname";
            $statement = $db->prepare($query);
            $statement->bindValue(':cardsetname', $new);
            $statement->execute();

            $exists = false;
            while ($row = $statement->fetch()) {
                if ($row['cardsetid']) {
                    $exists = true;
                    $cardsetid = $row['cardsetid'];
                    break;
                }
            }

            if (!$exists) {
                $query = "INSERT INTO cardsets (cardsetname) VALUES (:cardsetname)";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardsetname', $new);
                $statement->execute();
                $cardtypeid = $db->lastInsertId();
            }
        }
        */

        $query = "INSERT INTO cards (cardname, cardtypeid, power, toughness) VALUES (:cardname, :cardtypeid, :power, :toughness)";
        $statement = $db->prepare($query);
        $statement->bindValue(':cardname', $cardname);
        $statement->bindValue(':cardtypeid', $cardtypeid);
        $statement->bindValue(':power', $power);
        $statement->bindValue(':toughness', $toughness);

        if ($statement->execute()) {
            header("Location: index.php?success");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Add Card</title>
</head>
<body>
    <h1><a href="index.php">Magic: The Gathering CMS - New Card</a></h1>
    <ul id="menu">
        <li><a href="index.php">Home</a></li>
        <li><a href="insert.php" class="active">Add Card</a></li>
    </ul> 
    <form action="insert.php" method="post">
        <fieldset>
            <p>
                <label for="cardname">Card Name</label>
                <input id="cardname" name="cardname">
            </p>
            <p>
                <label for="cardtype">Card Type</label>
                <select name="cardtype" id="cardtype">
                    <?php while($row = $statement_cardtypes->fetch()): ?>
                        <option value="<?= $row['cardtypeid'] ?>"><?= $row['cardtypename'] ?></option>
                    <?php endwhile ?>
                    <option value="new">Add New Cardtype</option>
                </select>
            </p>
            <p>
                <label for="newcardtype">New Cardtype</label>
                <input type="text" id="newcardtype" name="newcardtype">
            </p>
            <!--
            <p>
                <label for="colourname">Card Cost</label>
                <select name="colourname" id="colourname">
                    <?php //while($row = $statement_manacolours->fetch()): ?>
                        <option value="<?= //$row['manaid'] ?>"><?= //$row['colourname'] ?></option>
                    <?php //endwhile ?>
                    <option value="new">Add New Card Cost</option>
                </select>
            </p>
            <p>
                <label for="newcolourname">New Card Cost</label>
                <input type="text" id="newcolourname" name="newcolourname">
            </p>
            <p>
                <label for="cardsetname">Set</label>
                <select name="cardsetname" id="cardsetname">
                    <?php //while($row = $statement_cardsets->fetch()): ?>
                        <option value="<?= //$row['cardsetid'] ?>"><?= //$row['cardsetname'] ?></option>
                    <?php //endwhile ?>
                    <option value="new">Add New Set</option>
                </select>
            </p>
            <p>
                <label for="newcardsetname">New Set</label>
                <input type="text" id="newcardsetname" name="newcardsetname">
            </p>
            -->
            <p>
                <label for="power">Power</label>
                <textarea id="power" name="power"></textarea>
            </p>
            <p>
                <label for="toughness">Toughness</label>
                <textarea id="toughness" name="toughness"></textarea>
            </p>
            <input type="submit">
            <?php if ($_POST && empty(trim($_POST['cardname']))): ?>
                <p class="warning">The Card Name, Cardtype, and Set must each contain at least 1 non-whitespace character.</p>
            <?php endif ?>
        </fieldset>
    </form>
</body>
</html>