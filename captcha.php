<?php

/*******w******** 
    
    Name: Gregory Rennie
    Date: November 25, 2024
    Description: Project - Magic: The Gathering CMS Captcha PHP
                 Referenced from https://www.the-art-of-web.com/php/captcha/

****************/

    session_start();

    // Generate colours and lines
    $image = imagecreatetruecolor(150, 50);
    $background = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $background);
    
    $textcolour = imagecolorallocate($image, 0, 0, 0);
    for ($i=0; $i<6; $i++) {
        $linecolour = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
        imagesetthickness($image, 1);
        imageline($image, 0, rand(0, 50), 150, rand(0, 50), $linecolour);
    }
    
    // Generate text
    $characters = '23456789abcdefghjkmnpqrstvwxyzABCDEFGHJKLMNPQRSTVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < 6; $i++) {
        $num = $characters[rand(0, $characters_length - 1)];
        $random_string .= $num;
        imagechar($image, rand(3, 5), 10 + ($i * 20), rand(2, 25), $num, $textcolour);
    }

    $_SESSION['captcha'] = $random_string;
    
    header('Content-type: image/png');
    imagepng($image);
    imagedestroy($image);

?>