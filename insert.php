<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 3, 2024
    Description: Project - Magic: The Gathering CMS Insert PHP

****************/

    require('connect.php');
    require('authenticate.php');
    require('get_or_create.php');

    $query_cardtypes = "SELECT * FROM cardtypes";
    $statement_cardtypes = $db->prepare($query_cardtypes);
    $statement_cardtypes->execute();

    $query_manacolours = "SELECT * FROM manacolours";
    $statement_manacolours = $db->prepare($query_manacolours);
    $statement_manacolours->execute();

    $query_cardsets = "SELECT * FROM cardsets";
    $statement_cardsets = $db->prepare($query_cardsets);
    $statement_cardsets->execute();

    if(
        $_POST &&
        !empty(trim($_POST['cardname'])) &&
        !empty(trim($_POST['cardtypename'])) &&
        !empty(trim($_POST['cardsetname']))
        ) {

        $cardname = filter_input(INPUT_POST, 'cardname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $power = filter_input(INPUT_POST, 'power', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $toughness = filter_input(INPUT_POST, 'toughness', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        $cardtypeid = get_or_create($db, 'cardtypes', 'cardtypeid', 'cardtypename', 'newcardtype');
        $manaid = get_or_create($db, 'manacolours', 'manaid', 'colourname', 'newcolourname');
        $cardsetid = get_or_create($db, 'cardsets', 'cardsetid', 'cardsetname', 'newcardset');
        
        try {
            $db->beginTransaction();

            $query = "INSERT INTO cards (cardname, cardtypeid, power, toughness) VALUES (:cardname, :cardtypeid, :power, :toughness)";
            $statement = $db->prepare($query);
            $statement->bindValue(':cardname', $cardname, PDO::PARAM_STR);
            $statement->bindValue(':cardtypeid', $cardtypeid, PDO::PARAM_INT);
            $statement->bindValue(':power', $power, PDO::PARAM_STR);
            $statement->bindValue(':toughness', $toughness, PDO::PARAM_STR);
            $statement->execute();
            
            $cardid = $db->lastInsertId();
            
            $query = "INSERT INTO cardsetcards (cardid, cardsetid) VALUES (:cardid, :cardsetid)";
            $statement = $db->prepare($query);
            $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
            $statement->bindValue(':cardsetid', $cardsetid, PDO::PARAM_INT);
            $statement->execute();

            if ($manaid) {
                $query = "INSERT INTO cardcosts (cardid, manaid) VALUES (:cardid, :manaid)";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->bindValue(':manaid', $manaid, PDO::PARAM_INT);
                $statement->execute();
            }

            $db->commit();

            header("Location: index.php?success");
            exit;
        
        } catch (Exception $exception) {
            $db->rollBack();
            echo "Transaction failed: " . $exception->getMessage();
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
                <label for="cardtypename">Card Type</label>
                <select name="cardtypename" id="cardtypename">
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
            <p>
                <label for="colourname">Card Cost</label>
                <select name="colourname" id="colourname">
                    <?php while($row = $statement_manacolours->fetch()): ?>
                        <option value="<?= $row['manaid'] ?>"><?= $row['colourname'] ?></option>
                    <?php endwhile ?>
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
                    <?php while($row = $statement_cardsets->fetch()): ?>
                        <option value="<?= $row['cardsetid'] ?>"><?= $row['cardsetname'] ?></option>
                    <?php endwhile ?>
                    <option value="new">Add New Set</option>
                </select>
            </p>
            <p>
                <label for="newcardset">New Set</label>
                <input type="text" id="newcardset" name="newcardset">
            </p>
            <p>
                <label for="power">Power</label>
                <textarea id="power" name="power"></textarea>
            </p>
            <p>
                <label for="toughness">Toughness</label>
                <textarea id="toughness" name="toughness"></textarea>
            </p>
            <input type="submit">
            <?php if (
                $_POST &&
                empty(trim($_POST['cardname'])) &&
                empty(trim($_POST['cardtypename'])) &&
                empty(trim($_POST['cardsetname']))
            ): ?>
                <p class="warning">The Card Name, Cardtype, and Set must each contain at least 1 non-whitespace character.</p>
            <?php endif ?>
        </fieldset>
    </form>
</body>
</html>