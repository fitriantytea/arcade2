<?php

if(!defined('CRON')){
	die('p');
}

$data = get_pref('cron-job');

define("LIMIT", 3);
$game_count = 0;
$log_txt = "";

if(!is_null($data)){
	$data = json_decode($data, true);
	if(isset($data['auto-post'])){
		$task_date = $data['auto-post']['date'];
		$cur_date = date("Y-m-d H:i:s");
		if($cur_date >= $task_date){
			$datetime1 = date_create($cur_date);
			$datetime2 = date_create($task_date);
			$interval = date_diff($datetime1, $datetime2);
			$diff = $interval->format('%d');

			if($diff < 4){
				$new_task_date = date('Y-m-d H:i:s', strtotime('+8 hours', strtotime(date('Y-m-d H:i:s'))));
				$data['auto-post']['date'] = $new_task_date;
				update_option('cron-job', json_encode($data));
				auto_add_games($data);
			} else { //More than 4 days inactive
				echo 'remove';
				unset($data['auto-post']);
				update_option('cron-job', json_encode($data));
			}
		} else {
			if(!defined('CRON')){
				echo 'on the way';
			}
		}
	} else {
		//Inactive
	}
}

function auto_add_games($data){
	if(!ADMIN_DEMO){
		add_to_log();
		$data['auto-post']['last-status'] = 'null';
		$url = 'https://api.cloudarcade.net/fetch-auto.php?action=fetch&code=44d59212-9515-485a-9f02-bf0d0df35ca0';
		$url .= '&data='.json_encode($data['auto-post']['list']);
		$url .= '&ref='.DOMAIN.'&v='.VERSION;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$curl = curl_exec($ch);
		curl_close($ch);
		$game_data = json_decode($curl, true);
		if(isset($game_data['error'])){
			add_to_log('Failed auto add games: '.$curl);
		} else if($game_data){
			foreach ($game_data as $a => $b) {
				foreach ($b as $item) {
					$item['tags'] = '';
					x_add_game2($item);
				}
			}
		} else {
			add_to_log('Failed auto add games. Null');
		}
		write_log();
	}
}

function x_add_game2($data){
	$_POST = $data;
	// Copied from request.php add_game()
	$ref = '';
	if(isset($_POST['ref'])) $ref = $_POST['ref'];
	$_POST['description'] = html_purify($_POST['description']);
	$_POST['instructions'] = html_purify($_POST['instructions']);
	if($_POST['source'] == 'self' || $_POST['source'] == 'remote'){
		if(!isset($_POST['published'])){
			$_POST['published'] = false;
		}
	}
	if(!isset($_POST['is_mobile'])){
		$_POST['is_mobile'] = false;
	}
	$redirect = 0;
	if(isset($_POST['redirect'])){
		$redirect = $_POST['redirect'];
	}
	if(isset($_POST['slug'])){
		$slug = esc_slug($_POST['slug']);
	} else {
		$slug = esc_slug(strtolower(str_replace(' ', '-', $_POST["title"])));
	}
	$slug = preg_replace('/-{2,}/', '-', $slug);
	$slug = trim($slug, '-');
	$_POST['slug'] = $slug;
	if(is_array($_POST['category'])){
		// Array category is not allowed
		// Convert to string
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
		$_POST['category'] = $cats;
	}
	if($_POST['category'] == '' || $_POST['category'] == ' '){
		$_POST['category'] = 'Other';
	}
	// Begin category filter
	if(file_exists(ABSPATH."content/plugins/category-filter")){
		// Plugin exist
		$cats = '';
		$categories = commas_to_array($_POST['category']);
		$i = 0;
		$total = count($categories);
		foreach ($categories as $key) {
			$cats = $cats.category_name_filtering($key);
			if($i < $total-1){
				$cats = $cats.',';
			}
			$i++;
		}
		$_POST['category'] = $cats;
	}
	$game = new Game;
	$check=$game->getBySlug($slug);
	$status='failed';
	if(is_null($check)){
		if($ref != 'upload'){
			// Come from fetch games
			if(IMPORT_THUMB){
				// Check if webp is activated
				$use_webp = get_setting_value('webp_thumbnail');
				import_thumbnail($_POST['thumb_2'], $slug, 2);
				$name = basename($_POST['thumb_2']);
				$extension = pathinfo($_POST['thumb_2'], PATHINFO_EXTENSION);
				$_POST['thumb_2'] = '/thumbs/'.$slug.'_2.'.$extension;
				if($use_webp){
					$file_extension = pathinfo($_POST['thumb_2'], PATHINFO_EXTENSION);
					$_POST['thumb_2'] = str_replace('.'.$file_extension, '.webp', $_POST['thumb_2']);
				}
				//
				import_thumbnail($_POST['thumb_1'], $slug, 1);
				$name = basename($_POST['thumb_1']);
				$extension = pathinfo($_POST['thumb_1'], PATHINFO_EXTENSION);
				$_POST['thumb_1'] = '/thumbs/'.$slug.'_1.'.$extension;
				if($use_webp){
					$file_extension = pathinfo($_POST['thumb_1'], PATHINFO_EXTENSION);
					$_POST['thumb_1'] = str_replace('.'.$file_extension, '.webp', $_POST['thumb_1']);
				}
				if( SMALL_THUMB ){
					$output = pathinfo($_POST['thumb_2']);
					$_POST['thumb_small'] = '/thumbs/'.$slug.'_small.'.$output['extension'];
					if($use_webp){
						$file_extension = pathinfo($_POST['thumb_2'], PATHINFO_EXTENSION);
						$_POST['thumb_small'] = str_replace('.'.$file_extension, '.webp', $_POST['thumb_small']);
						generate_small_thumbnail($_POST['thumb_2'], $slug);
					} else {
						generate_small_thumbnail($_POST['thumb_2'], $slug);
					}
				}
			}
		}
		$game->storeFormValues( $_POST );
		$game->insert();
		$status='added';
		//
		$cats = commas_to_array($_POST['category']);
		if(is_array($cats)){ //Add new category if not exist
			$length = count($cats);
			for($i = 0; $i < $length; $i++){
				$_POST['name'] = $cats[$i];
				$category = new Category;
				$exist = $category->isCategoryExist($_POST['name']);
				if($exist){
				  //
				} else {
					unset($_POST['slug']);
					$_POST['description'] = '';
					$category->storeFormValues( $_POST );
					$category->insert();
				}
				$category->addToCategory($game->id, $category->id);
			}
		}
	}
	else{
		$status='exist';
	}
	$keys =['title', 'slug', 'description', 'instructions', 'width', 'height', 'category', 'thumb_1', 'thumb_2', 'url', 'tags'];
	if($status != 'added'){
		if($_POST['source'] == 'self' || $_POST['source'] == 'remote'){
			// Store current fields
			foreach ($keys as $item) {
				$_SESSION[$item] = (isset($_POST[$item])) ? $_POST[$item] : null;
			}
		}
	} else {
		// Successfully added
		// Clear last fields
		if(isset($_SESSION['title'])){
			foreach ($keys as $item) {
				if(isset($_SESSION[$item])){
					unset($_SESSION[$item]);
				}
			}
		}
		add_to_log('Game added - '.$_POST['source'].' - '.$slug);
	}
	if($status == 'exist'){
		add_to_log('Game alredy exist - '.$_POST['source'].' - '.$slug);
		$status='exist';
	}
}

function category_name_filtering($category_name){
	// Specific function for "Category Filter" plugin
	if(true){
		$json = get_pref("category-filter");
		if($json){
			$data = json_decode($json, true);
			foreach ($data as $key => $value) {
				if($key == $category_name){
					return $value;
				}
			}
		}
	}
	return $category_name;
}
function generate_small_thumbnail($path, $slug){
	// copied from admin-functions.php
	$parent_dir = dirname(__FILE__) . '/../'; // CloudArcade root / installation folder
	if(!file_exists($parent_dir.$path)){
		echo 'error 910: img file not found!';
		return;
	}
	// $use_webp = get_setting_value('webp_thumbnail');
	$path_info = pathinfo($path);
	$root_folder = explode ("/", $path);
	$output = "thumbs/" . $slug . "_small." . $path_info['extension'];
	if($path_info['extension'] == 'webp'){
		// WEBP thumbnail
		$file_extension = pathinfo($path, PATHINFO_EXTENSION);
		$output = str_replace('.'.$file_extension, '.webp', $output);
		$_img = getimagesize($parent_dir.$path);
		$width  = $_img['0'];
		$height = $_img['1'];
		$img = imagecreatefromwebp($parent_dir.$path);
		$new_img = imagecreatetruecolor(160, 160);
		imagecopyresized($new_img, $img, 0, 0, 0, 0, 160, 160, $width, $height);
		//output
		imagewebp($new_img, $parent_dir.$output, -1); // No compression
	} else {
		// PNG, JPG, GIF
		$x = getimagesize($parent_dir.$path);
		$width  = $x['0'];
		$height = $x['1'];
		switch ($x['mime']) {
		  case "image/gif":
			 $img = imagecreatefromgif($parent_dir.$path);
			 break;
		  case "image/jpg":
		  case "image/jpeg":
			 $img = imagecreatefromjpeg($parent_dir.$path);
			 break;
		  case "image/png":
			 $img = imagecreatefrompng($parent_dir.$path);
			 break;
		}
		$img_base = imagecreatetruecolor(160, 160);
		if($x['mime'] == "image/png"){
			imageAlphaBlending($img_base, false);
			imageSaveAlpha($img_base, true);
		}
		imagecopyresampled($img_base, $img, 0, 0, 0, 0, 160, 160, $width, $height);
		$path_info = pathinfo($parent_dir.$path);
		switch ($path_info['extension']) {
		  case "gif":
			 imagegif($img_base, $parent_dir.$output); // No compression
			 break;
		case "jpg":
		case "jpeg":
			 imagejpeg($img_base, $parent_dir.$output, 100); // No compression
			 break;
		  case "png":
			 imagepng($img_base, $parent_dir.$output, 6); // Balance compression
			 break;
		}
		imagedestroy($img);
		imagedestroy($img_base);
	}
}
function import_thumbnail($url, $game_slug, $index = null){
	// copied from admin-functions.php
	$parent_dir = dirname(__FILE__) . '/../'; // CloudArcade root / installation folder
	if($url) {
		if (!file_exists($parent_dir.'thumbs')) {
			mkdir($parent_dir.'thumbs', 0777, true);
		}
		$extension = pathinfo($url, PATHINFO_EXTENSION);
		$identifier = '';
		if(!is_null($index)){
			$identifier = '_'.$index;
		}
		$new = $parent_dir.'thumbs/'.$game_slug.$identifier.'.'.$extension;
		if( get_setting_value('webp_thumbnail') ){
			// Using WEBP format
			$file_extension = pathinfo($url, PATHINFO_EXTENSION);
			$new = str_replace('.'.$file_extension, '.webp', $new);
			// Create a cURL resource
			$ch = curl_init();
			// Set cURL options for retrieving the remote image file
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
			// Retrieve the remote image and save it to a local file
			$remoteImage = curl_exec($ch);
			if($remoteImage !== false){
				$localFile = fopen($new, 'w');
				if($localFile){
					fwrite($localFile, $remoteImage);
					fclose($localFile);
				} else {
					echo 'Could not create local file';
				}
			} else {
				echo 'Could not download remote image';
			}
			// Close the cURL resource
			curl_close($ch);
			image_to_webp($new, 100, $new);
		} else {
			// Using JPG/PNG/GIF format
			save_remote_thumbnail($url, $new);
		}
	}
}
function save_remote_thumbnail($source, $destination, $quality = 100) {
	// copied from admin-functions.php
	$ch = curl_init();
	// Set cURL options for retrieving the remote image file
	curl_setopt($ch, CURLOPT_URL, $source);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
	// Retrieve the remote image and create an image resource from it
	$remoteImage = curl_exec($ch);
	if($remoteImage !== false){
		$image = imagecreatefromstring($remoteImage);
		if($image !== false){
			$info = getimagesizefromstring($remoteImage);
			if ($info['mime'] == 'image/png'){
				imageAlphaBlending($image, true);
				imageSaveAlpha($image, true);
				imagepng($image, $destination, 6);
			} else if($info['mime'] == 'image/jpg' || $info['mime'] == 'image/jpeg') {
				imagejpeg($image, $destination, 100); // No compression
			} else if($info['mime'] == 'image/gif') {
				imagegif($image, $destination);
			}
			imagedestroy($image);
		} else {
			echo 'Could not create image resource';
		}
	} else {
		echo 'Could not download remote image';
	}
	// Close the cURL resource
	curl_close($ch);
}
function add_to_log($msg = ""){
	global $log_txt;
	if($msg == ""){
		$log_txt .= "---- Executed - ".date('Y-m-d H:i:s');
	} else {
		$log_txt .= $msg;
	}
	$log_txt .= PHP_EOL;
}
function write_log(){
	global $log_txt;
	if($log_txt != ""){
		$path = ABSPATH . PLUGIN_PATH . '/auto-publish';
		if(file_exists($path . '/log.txt')){
			$filesizeKB = filesize($path . '/log.txt') / 1024;
			if($filesizeKB >= 50){
				file_put_contents($path . '/log_prev.txt', file_get_contents($path . '/log.txt'));
				unlink($path . '/log.txt');
			}
		}
		if(file_exists($path)){
			$full_log = "";
			if(file_exists($path . '/log.txt')){
				$full_log = file_get_contents($path . '/log.txt');
			}
			$full_log = $log_txt.$full_log;
			file_put_contents($path . '/log.txt', $full_log);
		}
	}
}

?>