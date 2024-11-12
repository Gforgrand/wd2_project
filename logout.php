<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 12, 2024
    Description: Project - Magic: The Gathering CMS User Logout PHP

****************/

session_start();

try {

    $_SESSION = [];
    
    header("Location: index.php?loggedout");
    exit;

} catch (Exception $e){
    echo "Failed to logout: " . $e->getMessage(); 
}

?>