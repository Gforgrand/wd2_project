<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 6, 2024
    Description: Project - Magic: The Gathering CMS Edit PHP

****************/

    session_start();
    
    require('search_logic.php');
    require('image_upload.php');
    require('get_or_create.php');

    if (!isset($_SESSION['userlevel']) || $_SESSION['userlevel'] < 20) {
        header("Location: index.php?accessdenied");
        exit;
    }

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

                $query = "SELECT filename FROM images WHERE cardid = :cardid LIMIT 1";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->execute();
                $image = $statement->fetch();

                if ($image && !empty($image['filename'])) {
                    $image_path = file_upload_path($_POST['filename']);

                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                    
                    $query = "DELETE FROM images WHERE cardid = :cardid";
                    $statement = $db->prepare($query); 
                    $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);  
                    $statement->execute(); 

                    $_SESSION['upload_message'] = '';
                    unset($_SESSION['upload_message']);
                }

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

            $query_images = "SELECT COUNT(*) FROM images WHERE cardid = :cardid";
            $statement_images = $db->prepare($query_images);
            $statement_images->bindValue(':cardid', $cardid, PDO::PARAM_INT);
            $statement_images->execute();
            $count_images = $statement_images->fetchColumn();

            if (isset($_SESSION['imageid']) && $count_images == 0) {
                $query = "UPDATE images SET cardid = :cardid WHERE imageid = :imageid";
                $statement = $db->prepare($query);
                $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);
                $statement->bindValue(':imageid', $_SESSION['imageid'], PDO::PARAM_INT);
                $statement->execute();
                $_SESSION['imageid'] = '';
                unset($_SESSION['imageid']);
                $_SESSION['image_filename'] = '';
                unset($_SESSION['image_filename']);
            } else {
                $_SESSION['upload_message'] = "This card already has an image!";
            }

            if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                if (isset($_POST['filename']) && !empty($_POST['filename'])) {
                    $image_path = file_upload_path($_POST['filename']);

                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                    
                    $query = "DELETE FROM images WHERE cardid = :cardid";
                    $statement = $db->prepare($query); 
                    $statement->bindValue(':cardid', $cardid, PDO::PARAM_INT);  
                    $statement->execute(); 
                    $_SESSION['upload_message'] = "Image deleted!";
                }
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

        $query = "SELECT c.*, t.cardtypeid, m.manaid, s.cardsetid, i.filename
                 FROM cards c
                 JOIN cardtypes t ON c.cardtypeid = t.cardtypeid
                 LEFT JOIN cardcosts cc ON c.cardid = cc.cardid
                 LEFT JOIN manacolours m ON cc.manaid = m.manaid
                 LEFT JOIN cardsetcards cs ON c.cardid = cs.cardid
                 LEFT JOIN cardsets s ON cs.cardsetid = s.cardsetid
                 LEFT JOIN images i ON c.cardid = i.cardid
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
    <?php include 'search.php' ?>
    <div id="header">
        <h1><a href="index.php">Magic: The Gathering CMS - Edit Card</a></h1>
        <ul id="menu">
            <li><a href="index.php">Home</a></li>
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
                        <?php if(isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
                            <option value="new">Add New Cardtype</option>
                        <?php endif ?>
                    </select>
                </p>
                <?php if(isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
                    <p>
                        <label for="newcardtype">New Cardtype</label>
                        <input type="text" id="newcardtype" name="newcardtype">
                    </p>
                <?php endif ?>
                <p>
                    <label for="colourname">Card Cost</label>
                    <select name="colourname" id="colourname">
                        <?php while($row = $statement_manacolours->fetch()): ?>
                            <option value="<?= $row['manaid'] ?>" <?= $row['manaid'] == $post['manaid'] ? 'selected' : '' ?>>
                                <?= $row['colourname'] ?>
                            </option>
                        <?php endwhile ?>
                        <?php if(isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
                            <option value="new">Add New Card Cost</option>
                        <?php endif ?>
                    </select>
                </p>
                <?php if(isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
                    <p>
                        <label for="newcolourname">New Card Cost</label>
                        <input type="text" id="newcolourname" name="newcolourname">
                    </p>
                <?php endif ?>
                <p>
                    <label for="cardsetname">Set</label>
                    <select name="cardsetname" id="cardsetname">
                        <?php while($row = $statement_cardsets->fetch()): ?>
                            <option value="<?= $row['cardsetid'] ?>" <?= $row['cardsetid'] == $post['cardsetid'] ? 'selected' : '' ?>>
                                <?= $row['cardsetname'] ?>
                            </option>
                        <?php endwhile ?>
                        <?php if(isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
                            <option value="new">Add New Set</option>
                        <?php endif ?>
                    </select>
                </p>
                <?php if(isset($_SESSION['userlevel']) && $_SESSION['userlevel'] >= 30): ?>
                    <p>
                        <label for="newcardset">New Set</label>
                        <input type="text" id="newcardset" name="newcardset">
                    </p>
                <?php endif ?>
                <p>
                    <label for="power">Power</label>
                    <textarea id="power" name="power"><?= $post['power'] ?></textarea>
                </p>
                <p>
                    <label for="toughness">Toughness</label>
                    <textarea id="toughness" name="toughness"><?= $post['toughness'] ?></textarea>
                </p>
                <?php if (!empty($post['filename'])): ?>
                    <input type="hidden" name="filename" value="<?= $post['filename'] ?>">
                    <img src="uploads/<?= $post['filename'] ?>" alt="<?= $post['cardname'] ?>">
                    <label for="delete_image">
                        <input type="checkbox" name="delete_image" value="1"> Delete this image
                    </label>
                <?php endif ?>
                <input type="submit" name="update" value="Update">
                <button type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this card?')">Delete</button>
                <?php if (
                    $_POST &&
                    isset($_POST['cardname']) &&
                    isset($_POST['cardtypename']) &&
                    isset($_POST['cardsetname']) &&
                    empty(trim($_POST['cardname'])) &&
                    empty(trim($_POST['cardtypename'])) &&
                    empty(trim($_POST['cardsetname'])) &&
                    !isset($_POST['image_insert']) &&
                    !isset($_GET['keyword'])
                ): ?>
                    <p class="warning">The Card Name, Cardtype, and Set must each contain at least 1 non-whitespace character.</p>
                <?php endif ?>
            </fieldset>
        </form>
        <?php if (empty($post['filename'])): ?>
            <form id="image_insert" method="post" enctype="multipart/form-data">
                <input type="hidden" name="image_insert">
                <label for="image">Image Filename:</label>
                <input type="file" name="image" id="image">
                <input type="submit" name="submit" value="Upload Image">
            </form>
        <?php endif ?>
    <?php else: ?>
        <p>No Card Selected.</p>
    <?php endif ?>
    <?php if (isset($_SESSION['upload_message']) && $_SESSION['upload_message'] != "Image deleted!"): ?>
        <script> alert("<?= $_SESSION['upload_message'] ?>"); </script>
        <?php $_SESSION['upload_message'] = '' ?>
        <?php unset($_SESSION['upload_message']) ?>
    <?php endif ?>
</body>
</html>