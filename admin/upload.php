<?php
session_start();
require_once('../config.php');
require_once('../init.php');

$action = isset( $_POST['action'] ) ? $_POST['action'] : "";
$username = isset( $_SESSION['username'] ) ? $_SESSION['username'] : "";

if ( $action != "login" && $action != "logout" && !$username ) {
	exit('logout');
}

if( !USER_ADMIN ){
	exit('p');
}

if( ADMIN_DEMO ){
	header('Location: dashboard.php?viewpage=addgame');
	exit();
}

if( !has_admin_access() ){
	exit('x');
}

if (!file_exists('tmp')) {
	mkdir('tmp', 0755, true);
}
if (!file_exists('../games')) {
	mkdir('../games', 0755, true);
}
$target_dir = "tmp/";
$target_file = $target_dir . strtolower(str_replace(' ', '-', basename($_FILES["gamefile"]["name"])));
$folder_name = 0;
if(isset($_POST['slug'])){
	$_POST['slug'] = esc_slug($_POST['slug']);
	$folder_name = $_POST['slug'];
} else {
	$folder_name = esc_slug($_POST['title']);
}

$uploadOk = 1;
$error = array();

if (isset($_SERVER['CONTENT_LENGTH'])) {
	if($_SERVER['CONTENT_LENGTH'] > convert_to_bytes(ini_get('upload_max_filesize'))){
		$uploadOk = 0;
		$error['err'] = 'You file size is too large, your php.ini upload_max_filesize is '.ini_get('upload_max_filesize');
	}
}

function convert_to_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
if($fileType != 'zip'){
	$uploadOk = 0;
}

$have_icon_512 = false; //Used for Construct 3 game
$generate_thumbnail = false;

if ($uploadOk == 0) {
  //echo "error1";
} else {
  if (move_uploaded_file($_FILES["gamefile"]["tmp_name"], $target_file)) {
  	$check = array();
	$check['index'] = 'false';
	$check['thumb_1'] = false;
	$check['thumb_2'] = false;

	//uploaded
	$za = new ZipArchive();
	$za->open($target_file);

	for( $i = 0; $i < $za->numFiles; $i++ ){
		$stat = $za->statIndex( $i );
		$name = $stat['name'];
		if($name == 'index.html'){
			$check['index'] = $name;
		}
		if($name == 'thumb_1.png' || $name == 'thumb_1.jpg' || $name == 'thumb_1.jpeg' || $name == 'thumb_1.PNG' || $name == 'thumb_1.JPG'){
			if(!$check['thumb_1']){
				$check['thumb_1'] = $name;
			}
		}
		if($name == 'thumb_2.png' || $name == 'thumb_2.jpg' || $name == 'thumb_2.jpeg' || $name == 'thumb_2.PNG' || $name == 'thumb_2.JPG'){
			if(!$check['thumb_2']){
				$check['thumb_2'] = $name;
			}
		}
		if($name == 'icons/icon-512.png'){
			$have_icon_512 = true;
		}
	}

	if(!$check['thumb_1'] && !$check['thumb_2'] && $have_icon_512){
		$check['thumb_1'] = 'thumb_1.png';
		$check['thumb_2'] = 'thumb_2.png';
		$generate_thumbnail = true;
	}
	$za->close();
  } else {
	echo "error2";
  }
}

// Check if user chose to upload separate thumbnails
$thumb_method = $_POST['thumb_method'] ?? 'zip';

if ($thumb_method == 'upload') {
	// Validate and handle custom thumbnail uploads
	
	// Validate thumb_upload_1
	if (isset($_FILES['thumb_upload_1']) && $_FILES['thumb_upload_1']['error'] == UPLOAD_ERR_OK) {
		$thumb_upload_1_size = $_FILES['thumb_upload_1']['size']; // File size in bytes
		$thumb_upload_1_type = mime_content_type($_FILES['thumb_upload_1']['tmp_name']); // File type

		// Check file size (1MB = 1048576 bytes)
		if ($thumb_upload_1_size > 1048576) {
			$uploadOk = 0;
			$error['thumb_1_size'] = 'thumb_1 exceeds the 1MB file size limit.';
		} elseif (in_array($thumb_upload_1_type, ['image/jpeg', 'image/png'])) {
			// Move the uploaded file to the appropriate folder
			$thumb_1_target = '../games/' . $folder_name . '/thumb_1.' . pathinfo($_FILES['thumb_upload_1']['name'], PATHINFO_EXTENSION);
			move_uploaded_file($_FILES['thumb_upload_1']['tmp_name'], $thumb_1_target);
			$_POST['thumb_1'] = '/games/'.$folder_name.'/'.basename($thumb_1_target);
		} else {
			$uploadOk = 0;
			$error['thumb_1_invalid'] = 'Invalid thumb_1 file type. Must be JPG or PNG.';
		}
	} else {
		$uploadOk = 0;
		$error['thumb_1_missing'] = 'thumb_1 upload failed.';
	}

	// Validate thumb_upload_2
	if (isset($_FILES['thumb_upload_2']) && $_FILES['thumb_upload_2']['error'] == UPLOAD_ERR_OK) {
		$thumb_upload_2_size = $_FILES['thumb_upload_2']['size']; // File size in bytes
		$thumb_upload_2_type = mime_content_type($_FILES['thumb_upload_2']['tmp_name']); // File type

		// Check file size (1MB = 1048576 bytes)
		if ($thumb_upload_2_size > 1048576) {
			$uploadOk = 0;
			$error['thumb_2_size'] = 'thumb_2 exceeds the 1MB file size limit.';
		} elseif (in_array($thumb_upload_2_type, ['image/jpeg', 'image/png'])) {
			// Move the uploaded file to the appropriate folder
			$thumb_2_target = '../games/' . $folder_name . '/thumb_2.' . pathinfo($_FILES['thumb_upload_2']['name'], PATHINFO_EXTENSION);
			move_uploaded_file($_FILES['thumb_upload_2']['tmp_name'], $thumb_2_target);
			$_POST['thumb_2'] = '/games/'.$folder_name.'/'.basename($thumb_2_target);
		} else {
			$uploadOk = 0;
			$error['thumb_2_invalid'] = 'Invalid thumb_2 file type. Must be JPG or PNG.';
		}
	} else {
		$uploadOk = 0;
		$error['thumb_2_missing'] = 'thumb_2 upload failed.';
	}
}

if ($thumb_method == 'zip') {
	// Use thumbnails from zip if user didn't choose separate upload
	if ($uploadOk == 1) {
		if (!$check['index']) {
			$error['err1'] = 'No index.html on root detected!';
			$uploadOk = 0;
		}
		if (!$check['thumb_1']) {
			$error['err2'] = 'No thumb_1.jpg/png on root detected!';
			$uploadOk = 0;
		}
		if (!$check['thumb_2']) {
			$error['err3'] = 'No thumb_2.jpg/png on root detected!';
			$uploadOk = 0;
		}
	}
}

if ($uploadOk == 0) {
	$error['err0'] = 'Upload failed!';
	unlink($target_file);
	// Store current fields
	$keys = ['title', 'slug', 'description', 'instructions', 'width', 'height', 'category', 'thumb_1', 'thumb_2', 'url', 'tags'];
	foreach ($keys as $item) {
		$_SESSION[$item] = (isset($_POST[$item])) ? $_POST[$item] : null;
	}
	header('Location: dashboard.php?viewpage=addgame&status=error&error-data='.json_encode($error));
} else {
	$zip = new ZipArchive;
	$res = $zip->open($target_file);
	if ($res === TRUE) {
		$zip->extractTo('../games/'.$folder_name.'/');
		$zip->close();
	} else {
		echo 'doh!';
	}
	unlink($target_file);
	if($generate_thumbnail){
		require_once('../includes/commons.php');
		// Begin generate thumbnail
		try {
			$target_img = '../games/'.$folder_name.'/icons/icon-512.png';
			if(file_exists($target_img)){
				imgCopy($target_img, '../games/'.$folder_name.'/thumb_1.png', 512, 384);
				imgCopy($target_img, '../games/'.$folder_name.'/thumb_2.png', 512, 512);
			}
		} catch(Exception $e) {
			var_dump($e);
		}
	}
	$cats = '';
	$i = 0;
	$total = count($_POST['category']);
	foreach ($_POST['category'] as $key) {
		$cats = $cats.$key;
		if($i < $total-1){
			$cats = $cats.',';
		}
		$i++;
	}
	$_POST['ref'] = 'upload';
	$_POST['action'] = 'addGame';
	$_POST['category'] = $cats;
	$_POST['url'] = '/games/'.$folder_name.'/';
	
	// Only set thumb_1 and thumb_2 from the zip if no separate upload was used
	if ($thumb_method == 'zip') {
		$_POST['thumb_1'] = '/games/'.$folder_name.'/'.$check['thumb_1'];
		$_POST['thumb_2'] = '/games/'.$folder_name.'/'.$check['thumb_2'];
	}

	if( SMALL_THUMB ){
		$output = pathinfo($_POST['thumb_2']);
		$_POST['thumb_small'] = '/games/'.$folder_name.'/'.$folder_name.'_small.'.$output['extension'];
		imgResize('..'.$_POST['thumb_2'], 160, 160, $folder_name);
	}
	//
	$_POST['redirect'] = 'dashboard.php?viewpage=addgame&status=uploaded';
	require 'request.php';
}
?>