<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: December 1, 2024
    Description: Project - Magic: The Gathering CMS Upload Image PHP

****************/

    function file_upload_path($original_filename, $upload_subfolder_name = 'uploads') {
        $current_folder = dirname(__FILE__);
        $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
        return join(DIRECTORY_SEPARATOR, $path_segments);
    }

    function file_is_an_image($temporary_path, $new_path) {
        $allowed_mime_types      = ['image/gif', 'image/jpeg', 'image/png'];
        $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

        $actual_file_extension   = pathinfo($new_path, PATHINFO_EXTENSION);
        $image_info = getimagesize($temporary_path);
        $actual_mime_type = $image_info ? $image_info['mime'] : false;

        $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
        $mime_type_is_valid      = in_array($actual_mime_type, $allowed_mime_types);

        return $file_extension_is_valid && $mime_type_is_valid;
    }

    function resize_image($file) {
        $image_info = getimagesize($file);
        $width = $image_info[0];
        $height = $image_info[1];
        $mime = $image_info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($file);
                break;
            case 'image/gif': 
                $source_image = imagecreatefromgif($file);
                break;
            default:
            return false;
        }

        $new_image = imagecreatetruecolor(336, 468);
        imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, 336, 468, $width, $height);
        switch ($mime) { 
            case 'image/jpeg': 
                imagejpeg($new_image, $file);
                break; 
            case 'image/png': 
                imagepng($new_image, $file); 
                break; 
            case 'image/gif': 
                imagegif($new_image, $file); 
                break; 
        }

        imagedestroy($source_image); 
        imagedestroy($new_image);
        return true;
    }

    $image_upload_detected = isset($_FILES['image']) && ($_FILES['image']['error'] === 0);

    if ($image_upload_detected) {
        $image_filename       = $_FILES['image']['name'];
        $temporary_image_path = $_FILES['image']['tmp_name'];
        $new_image_path       = file_upload_path($image_filename);
        $upload_successful = false;

        if (file_is_an_image($temporary_image_path, $new_image_path) && !isset($_SESSION['imageid'])) {
            resize_image($temporary_image_path);
            move_uploaded_file($temporary_image_path, $new_image_path);
            $upload_successful = true;
            $_SESSION['upload_message'] = "Image uploaded!";
            $_SESSION['image_filename'] = $image_filename;
            
            $query = "INSERT INTO images (filename) VALUES (:filename)";
            $statement = $db->prepare($query);
            $statement->bindValue(':filename', $image_filename, PDO::PARAM_STR);
            $statement->execute();
            $_SESSION['imageid'] = $db->lastInsertId();
        } else {
            $_SESSION['upload_message'] = "Upload not completed. Invalid file type or this card already has an image.";
        }
    }

?>