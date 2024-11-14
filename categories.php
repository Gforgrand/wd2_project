<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 13, 2024
    Description: Project - Magic: The Gathering CMS Categories PHP

****************/

session_start();

require('connect.php');

if (!isset($_SESSION['userlevel']) || $_SESSION['userlevel'] < 30) {
    header("Location: index.php?accessdenied");
    exit;
}

$query_cardtypes = "SELECT * FROM cardtypes";
$statement_cardtypes = $db->prepare($query_cardtypes);
$statement_cardtypes->execute();

$query_manacolours = "SELECT * FROM manacolours";
$statement_manacolours = $db->prepare($query_manacolours);
$statement_manacolours->execute();

$query_cardsets = "SELECT * FROM cardsets";
$statement_cardsets = $db->prepare($query_cardsets);
$statement_cardsets->execute();

function sanitize_and_update($table, $fieldname, $newvalue, $idname, $db) {

    $new = filter_input(INPUT_POST, $newvalue, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $array = []; 
    foreach ($_POST as $key => $value) {
        if (strpos($key, $idname) == 0 && $key != $newvalue) {
            $id = str_replace($idname, '', $key);
            $array[$id] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
    }

    if(!empty(trim($_POST[$newvalue]))) {
        $query = "INSERT INTO $table ($fieldname) VALUES (:$fieldname)";
        $statement = $db->prepare($query);
        $statement->bindValue(":$fieldname", $new, PDO::PARAM_STR);
        $statement->execute();
    }

    foreach ($array as $id => $name) {
        $query = "UPDATE $table SET $fieldname = :$fieldname WHERE {$idname}id = :{$idname}id";
        $statement = $db->prepare($query);
        $statement->bindValue(":$fieldname", $name, PDO::PARAM_STR);
        $statement->bindValue(":{$idname}id", $id, PDO::PARAM_INT);
        $statement->execute();
    }
}

if ($_POST) {

    sanitize_and_update('cardtypes', 'cardtypename', 'newcardtype', 'cardtype', $db);
    sanitize_and_update('manacolours', 'colourname', 'newcolourname', 'mana', $db);
    sanitize_and_update('cardsets', 'cardsetname', 'newcardset', 'cardset', $db);

    header("Location: categories.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
</head>
<body>
    <h1><a href="index.php">Magic: The Gathering CMS - New Card</a></h1>
    <h2>Categories</h2>
    <form method="post">
        <p><input type="submit" value="Update"></p>
        <table>
            <tr>
                <th>Cardtypes</th>
            </tr>
            <?php while($row = $statement_cardtypes->fetch()): ?>
                <tr>
                    <td><input type="text" id="cardtype<?= $row['cardtypeid'] ?>" name="cardtype<?= $row['cardtypeid'] ?>" value="<?= $row['cardtypename'] ?>"></td>
                </tr>
            <?php endwhile ?>
            <tr>
                <td><input type="text" id="newcardtype" name="newcardtype" placeholder="Add New Cardtype"></td>
            </tr>
        </table>
        <table>
            <tr>
                <th>Mana Colours</th>
            </tr>
            <?php while($row = $statement_manacolours->fetch()): ?>
                <tr>
                    <td><input type="text" id="mana<?= $row['manaid'] ?>" name="mana<?= $row['manaid'] ?>" value="<?= $row['colourname'] ?>"></td>
                </tr>
            <?php endwhile ?>
            <tr>
                <td><input type="text" id="newcolourname" name="newcolourname" placeholder="Add New Mana Colour"></td>
            </tr>
        </table>
        <table>
            <tr>
                <th>Sets</th>
            </tr>
            <?php while($row = $statement_cardsets->fetch()): ?>
                <tr>
                    <td><input type="text" id="cardset<?= $row['cardsetid'] ?>" name="cardset<?= $row['cardsetid'] ?>" value="<?= $row['cardsetname'] ?>"></td>
                </tr>
            <?php endwhile ?>
            <tr>
                <td><input type="text" id="newcardset" name="newcardset" placeholder="Add New Set"></td>
            </tr>
        </table>
    </form>
</body>
</html>