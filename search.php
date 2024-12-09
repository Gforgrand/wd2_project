<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: December 8, 2024
    Description: Project - Magic: The Gathering CMS Search PHP

****************/

?>

<form id="search" action="index.php" method="GET">
    <input type="text" name="search" id="search" placeholder="Search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
    <ul id=categories>
        <li>
            <label for="cardtypename">Card Type</label>
            <select name="cardtypename" id="cardtypename">
                <option value="0">Select a category</option>
                <?php while($category = $statement_cardtypes->fetch()): ?>
                    <option value="<?= $category['cardtypeid'] ?>" <?= isset($_GET['cardtypename']) && $category['cardtypeid'] == $_GET['cardtypename'] ? 'selected' : '' ?>><?= $category['cardtypename'] ?></option>
                <?php endwhile ?>
            </select>
        </li>
        <li>
            <label for="colourname">Card Cost</label>
            <select name="colourname" id="colourname">
                <option value="0">Select a category</option>
                <?php while($category = $statement_manacolours->fetch()): ?>
                    <option value="<?= $category['manaid'] ?>" <?= isset($_GET['colourname']) && $category['manaid'] == $_GET['colourname'] ? 'selected' : '' ?>><?= $category['colourname'] ?></option>
                <?php endwhile ?>
            </select>
        </li>
        <li>
            <label for="cardsetname">Set</label>
            <select name="cardsetname" id="cardsetname">
                <option value="0">Select a category</option>
                <?php while($category = $statement_cardsets->fetch()): ?>
                    <option value="<?= $category['cardsetid'] ?>" <?= isset($_GET['cardsetname']) && $category['cardsetid'] == $_GET['cardsetname'] ? 'selected' : '' ?>><?= $category['cardsetname'] ?></option>
                <?php endwhile ?>
            </select>
        </li>
    </ul>
    <input type="submit" id="clear" name="clear" value="Clear">
    <input type="submit" value="Search">
</form>