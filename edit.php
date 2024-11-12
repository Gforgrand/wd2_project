<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 6, 2024
    Description: Project - Magic: The Gathering CMS Edit PHP

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

    if ($_POST &&
        isset($_POST['cardname']) &&
        !empty(trim($_POST['cardname'])) &&
        isset($_POST['cardtypename']) &&
        !empty(trim($_POST['cardtypename'])) &&
        isset($_POST['cardsetname']) &&
        !empty(trim($_POST['cardsetname'])) &&
        isset($_POST['cardid'])) {

        $cardid = filter_input(INPUT_POST,'cardid', FILTER_SANITIZE_NUMBER_INT);
        $cardname = filter_input(INPUT_POST, 'cardname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $power = filter_input(INPUT_POST, 'power', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $toughness = filter_input(INPUT_POST, 'toughness', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        $cardtypeid = get_or_create($db, 'cardtypes', 'cardtypeid', 'cardtypename', 'newcardtype');
        $manaid = get_or_create($db, 'manacolours', 'manaid', 'colourname', 'newcolourname');
        $cardsetid = get_or_create($db, 'cardsets', 'cardsetid', 'cardsetname', 'newcardset');
        
        // DELETIONS
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

        // UPDATES
        try {
            $db->beginTransaction();

            $query = "UPDATE cards SET cardname = :cardname, cardtypeid = :cardtypeid, power = :power, toughness = :toughness WHERE cardid = :cardid";
            $statement = $db->prepare($query);
            $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
            $statement->bindValue(':cardname', $cardname, PDO::PARAM_STR);
            $statement->bindValue(':cardtypeid', $cardtypeid, PDO::PARAM_INT);
            $statement->bindValue(':power', $power, PDO::PARAM_STR);
            $statement->bindValue(':toughness', $toughness, PDO::PARAM_STR);
            $statement->execute();
            
            $query = "UPDATE cardsetcards SET cardsetid = :cardsetid WHERE cardid = :cardid";
            $statement = $db->prepare($query);
            $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
            $statement->bindValue(':cardsetid', $cardsetid, PDO::PARAM_INT);
            $statement->execute();

            if ($manaid) {
                $query = "UPDATE cardcosts SET manaid = :manaid WHERE cardid = :cardid";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->bindValue(':manaid', $manaid, PDO::PARAM_INT);
                $statement->execute();
            }

            $db->commit();

            header("Location: index.php?updated");
            exit;
        
        } catch (Exception $exception) {
            $db->rollBack();
            echo "Transaction failed: " . $exception->getMessage();
        }
    } else if(isset($_GET['cardid'])) {
        $cardid = filter_input(INPUT_GET,'cardid', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT c.*, t.cardtypeid, m.manaid, s.cardsetid
                 FROM cards c
                 JOIN cardtypes t ON c.cardtypeid = t.cardtypeid
                 LEFT JOIN cardcosts cc ON c.cardid = cc.cardid
                 LEFT JOIN manacolours m ON cc.manaid = m.manaid
                 LEFT JOIN cardsetcards cs ON c.cardid = cs.cardid
                 LEFT JOIN cardsets s ON cs.cardsetid = s.cardsetid
                 WHERE c.cardid = :cardid LIMIT 1";
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
        <ul id="menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="edit.php" class="active">Edit Card</a></li>
        </ul> 
    </div>
    <?php if ($cardid && isset($post) && $post): ?>
        <form id="editForm" method="post">
            <input type="hidden" name="cardid" value="<?= $post['cardid'] ?>">
            <fieldset>
                <p>
                    <label for="cardname">Card Name</label>
                    <input id="cardname" name="cardname" value="<?= $post['cardname'] ?>">
                </p>
                <p>
                    <label for="cardtypename">Card Type</label>
                    <select name="cardtypename" id="cardtypename">
                        <?php while($row = $statement_cardtypes->fetch()): ?>
                            <option value="<?= $row['cardtypeid'] ?>" <?= $row['cardtypeid'] == $post['cardtypeid'] ? 'selected' : '' ?>>
                                <?= $row['cardtypename'] ?>
                            </option>
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
                            <option value="<?= $row['manaid'] ?>" <?= $row['manaid'] == $post['manaid'] ? 'selected' : '' ?>>
                                <?= $row['colourname'] ?>
                            </option>
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
                            <option value="<?= $row['cardsetid'] ?>" <?= $row['cardsetid'] == $post['cardsetid'] ? 'selected' : '' ?>>
                                <?= $row['cardsetname'] ?>
                            </option>
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
                    <textarea id="power" name="power"><?= $post['power'] ?></textarea>
                </p>
                <p>
                    <label for="toughness">Toughness</label>
                    <textarea id="toughness" name="toughness"><?= $post['toughness'] ?></textarea>
                </p>
                <input type="submit" name="update" value="Update">
                <button type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this card?')">Delete</button>
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
    <?php else: ?>
        <p>No Card Selected.</p>
    <?php endif ?>
</body>
</html>