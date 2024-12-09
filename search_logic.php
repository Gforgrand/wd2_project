<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: December 8, 2024
    Description: Project - Magic: The Gathering CMS Search Logic PHP

****************/

    require('connect.php');

    $query_cardtypes = "SELECT * FROM cardtypes";
    $statement_cardtypes = $db->prepare($query_cardtypes);
    $statement_cardtypes->execute();

    $query_manacolours = "SELECT * FROM manacolours";
    $statement_manacolours = $db->prepare($query_manacolours);
    $statement_manacolours->execute();

    $query_cardsets = "SELECT * FROM cardsets";
    $statement_cardsets = $db->prepare($query_cardsets);
    $statement_cardsets->execute();

    $search_query = "SELECT c.*, t.cardtypename, m.colourname, s.cardsetname
              FROM cards c
              JOIN cardtypes t ON c.cardtypeid = t.cardtypeid
              LEFT JOIN cardcosts cc ON c.cardid = cc.cardid
              LEFT JOIN manacolours m ON cc.manaid = m.manaid
              LEFT JOIN cardsetcards cs ON c.cardid = cs.cardid
              LEFT JOIN cardsets s ON cs.cardsetid = s.cardsetid
              WHERE 1=1";

    $bindings = [];

    if ($_GET) {
        if (isset($_GET['search'])) {
            $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $search_query .= " AND c.cardname LIKE :search";
        }    
        
        if (isset($_GET['cardtypename']) && $_GET['cardtypename'] != 0) {
            $cardtypename = filter_input(INPUT_GET, 'cardtypename', FILTER_SANITIZE_NUMBER_INT);
            $search_query .= " AND t.cardtypeid = :cardtypename";
            $bindings[':cardtypename'] = $cardtypename;
        }

        if (isset($_GET['colourname'] ) && $_GET['colourname'] != 0) {
            $colourname = filter_input(INPUT_GET, 'colourname', FILTER_SANITIZE_NUMBER_INT);
            $search_query .= " AND m.manaid = :colourname";
            $bindings[':colourname'] = $colourname;
        }

        if (isset($_GET['cardsetname']) && $_GET['cardsetname'] != 0) {
            $cardsetname = filter_input(INPUT_GET, 'cardsetname', FILTER_SANITIZE_NUMBER_INT);
            $search_query .= " AND s.cardsetid = :cardsetname";
            $bindings[':cardsetname'] = $cardsetname;
        }
    }

    $search_query_statement = $db->prepare($search_query);
    foreach ($bindings as $key => $value) {
        $search_query_statement->bindValue($key, $value, PDO::PARAM_INT);
    }
    if (isset($_GET['search'])) {
        $search_query_statement->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $search_query_statement->execute();

    if (isset($_GET['clear'])) {
        $_GET['search'] = '';
        unset($_GET['search']);
        header("Location: index.php?cardtypename=0&colourname=0&cardsetname=0");
        exit;
    }

?>