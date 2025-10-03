<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function get_random_string($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$str = get_random_string();
$_SESSION['captcha'] = $str;

header("Content-type: image/png");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$img_handle = ImageCreate(80, 35) or die("X");
$back_color = ImageColorAllocate($img_handle, 102, 102, 153);
$txt_color = ImageColorAllocate($img_handle, 255, 255, 255);
ImageString($img_handle, 30, 15, 10, $str, $txt_color);
Imagepng($img_handle);

?>
