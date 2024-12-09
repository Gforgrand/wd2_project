<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: December 8, 2024
    Description: Project - Magic: The Gathering CMS Search PHP

****************/

?>

<form id="search" action="index.php" method="GET">
    <input type="text" name="search" id="search" placeholder="Search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
    <input type="submit" value="Search">
</form>