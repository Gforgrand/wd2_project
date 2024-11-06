<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 6, 2024
    Description: Contains get_or_create PHP function

****************/

function get_or_create($db, $table, $fieldid, $fieldname, $value) {

    $id = filter_input(INPUT_POST, $fieldname, FILTER_VALIDATE_INT);

    if ($_POST[$fieldname] == 'new' && !empty(trim($_POST[$value]))) {
        $new = filter_input(INPUT_POST, $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $query = "SELECT $fieldid FROM $table WHERE $fieldname = :$fieldname";
        $statement = $db->prepare($query);
        $statement->bindValue(":$fieldname", $new, PDO::PARAM_STR);
        $statement->execute();

        $exists = false;
        $id = null;

        while ($row = $statement->fetch()) {
            if ($row[$fieldid]) {
                $exists = true;
                $id = $row[$fieldid];
                break;
            }
        }

        if (!$exists) {
            $query = "INSERT INTO $table ($fieldname) VALUES (:$fieldname)";
            $statement = $db->prepare($query);
            $statement->bindValue(":$fieldname", $new, PDO::PARAM_STR);
            $statement->execute();
            $id = $db->lastInsertId();
        }
    }

    return $id;
}

?>