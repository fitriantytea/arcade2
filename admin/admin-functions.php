<?php

// Functions for Admin Panel

if(!USER_ADMIN){
	die('Forbidden');
}

define( "SKIP_QUERY_CACHE", true );

$admin_hooks = [];

// Function to register admin hook
function add_admin_hook($hook_name, $callback, $priority = 10) {
    global $admin_hooks;
    
    if (!isset($admin_hooks[$hook_name])) {
        $admin_hooks[$hook_name] = [];
    }
    
    if (!isset($admin_hooks[$hook_name][$priority])) {
        $admin_hooks[$hook_name][$priority] = [];
    }
    
    $admin_hooks[$hook_name][$priority][] = $callback;
}

// Function to execute admin hooks
function do_admin_hook($hook_name, ...$args) {
    global $admin_hooks;
    
    if (!isset($admin_hooks[$hook_name])) {
        return;
    }
    
    ksort($admin_hooks[$hook_name]); // Sort by priority
    
    foreach ($admin_hooks[$hook_name] as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            call_user_func_array($callback, $args);
        }
    }
}

function get_setting_group($category){
	// $conn = open_connection();
	// $sql = "SELECT * FROM settings WHERE category = :category";
	// $st = $conn->prepare($sql);
	// $st->bindValue('category', $category, PDO::PARAM_STR);
	// $st->execute();
	// $rows = $st->fetchAll(PDO::FETCH_ASSOC);
	// return $rows;
	$group = [];
	foreach (SETTINGS as $item) {
		if($item['category'] == $category){
			$group[] = $item;
		}
	}
	return $group;
}

function update_setting($name, $value){
	// Migrated, replacing update_settings()
	$this_setting = get_setting($name);
	// Validating data type
	if($this_setting['type'] == 'bool'){
		if($value == 1 || $value == 0){
			//
		} else {
			die('Type not valid');
		}
	} else if($this_setting['type'] == 'number'){
		if(!is_numeric($value)){
			die('Type not valid');
		}
	}
	$conn = open_connection();
	$sql = "UPDATE settings SET value = :value WHERE name = :name LIMIT 1";
	$st = $conn->prepare($sql);
	$st->bindValue(":name", $name, PDO::PARAM_STR);
	$st->bindValue(":value", $value, PDO::PARAM_STR);
	$st->execute();
}

function to_numeric_version($str_version){
	// Used to convert "1.5.0" to int 150
	return (int)str_replace('.', '', $str_version);
}

function curl_request($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		// If an error occured during the request, print the error
		echo 'Error:' . curl_error($ch);
		return false;
	}
	curl_close($ch);
	return $response;
}

function generate_small_thumbnail($path, $slug){
	// $path == $game->thumb_2
	// This function only works if thumb 2 is already stored locally
	$parent_dir = dirname(__FILE__) . '/../'; // CloudArcade root / installation folder
	if(!file_exists($parent_dir.$path)){
		echo 'error 910: img file not found!';
		return;
	}
	// $use_webp = get_setting_value('webp_thumbnail');
	$path_info = pathinfo(strtok($path, '?'));
	$root_folder = explode("/", $path);
	$output = "thumbs/" . $slug . "_small." . $path_info['extension'];
	if($path_info['extension'] == 'webp'){
		// WEBP thumbnail
		$file_extension = pathinfo(strtok($path, '?'), PATHINFO_EXTENSION);
		$output = str_replace('.'.$file_extension, '.webp', $output);
		$_img = getimagesize($parent_dir.$path);
		$width  = $_img[0];
		$height = $_img[1];
		$img = imagecreatefromwebp($parent_dir.$path);
		$new_img = imagecreatetruecolor(160, 160);
		imagecopyresampled($new_img, $img, 0, 0, 0, 0, 160, 160, $width, $height);
		// Output
		imagewebp($new_img, $parent_dir.$output, 100); // Best quality
		imagedestroy($img);
		imagedestroy($new_img);
	} else {
		// PNG, JPG, GIF
		$x = getimagesize($parent_dir.$path);
		$width  = $x[0];
		$height = $x[1];
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
			imagealphablending($img_base, false);
			imagesavealpha($img_base, true);
		}
		imagecopyresampled($img_base, $img, 0, 0, 0, 0, 160, 160, $width, $height);
		$path_info = pathinfo($parent_dir.$path);
		switch ($path_info['extension']) {
		  case "gif":
			 imagegif($img_base, $parent_dir.$output); // No compression
			 break;
		case "jpg":
		case "jpeg":
			 imagejpeg($img_base, $parent_dir.$output, 100); // Best quality
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
	// import_thumb() replacement from request.php
	// Used to import thumb_1 and thumb_2 from remote source
	$parent_dir = dirname(__FILE__) . '/../'; // CloudArcade root / installation folder
	if($url) {
		if (!file_exists($parent_dir.'thumbs')) {
			mkdir($parent_dir.'thumbs', 0777, true);
		}
		$extension = pathinfo(strtok($url, '?'), PATHINFO_EXTENSION);
		$identifier = '';
		if(!is_null($index)){
			$identifier = '_'.$index;
		}
		$new = $parent_dir.'thumbs/'.$game_slug.$identifier.'.'.$extension;
		if( get_setting_value('webp_thumbnail') ){
			// Using WEBP format
			$file_extension = pathinfo(strtok($url, '?'), PATHINFO_EXTENSION);
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
	// compressImage() replacement from request.php
	// Create a cURL resource
	$ch = curl_init();
	// Set cURL options for retrieving the remote image file
	curl_setopt($ch, CURLOPT_URL, $source);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
	// Retrieve the remote image
	$remoteImage = curl_exec($ch);
	// Close the cURL resource
	curl_close($ch);
	if ($remoteImage !== false) {
		$image = imagecreatefromstring($remoteImage);
		if ($image !== false) {
			$info = getimagesizefromstring($remoteImage);
			switch ($info['mime']) {
				case 'image/png':
					imagealphablending($image, false);
					imagesavealpha($image, true);
					imagepng($image, $destination, 6); // Compression level from 0 (no compression) to 9
					break;
				case 'image/jpeg':
				case 'image/jpg':
					imagejpeg($image, $destination, $quality); // Quality level from 0 (worst) to 100 (best)
					break;
				case 'image/gif':
					imagegif($image, $destination);
					break;
				default:
					echo 'Unsupported image format: ' . $info['mime'];
					imagedestroy($image);
					return false;
			}
			imagedestroy($image);
		} else {
			echo 'Could not create image resource';
			return false;
		}
	} else {
		echo 'Could not download remote image';
		return false;
	}
	return true;
}

function update_content_translation($content_type, $content_id, $language, $field_data) {
	// Sample usage =
	// Single : update_content_translation('game', 1, 'en', ['title' => 'New Title']);
	// Multiple : update_content_translation('game', 1, 'en', ['title' => 'New Title', 'description' => 'New Description']);
	if (ADMIN_DEMO || !USER_ADMIN) {
		die('ERR 918');
	}
	$conn = open_connection();
	try {
		$conn->beginTransaction();
		foreach ($field_data as $field => $translation) {
			$checkSql = "SELECT COUNT(*) FROM translations WHERE content_type = :content_type AND content_id = :content_id AND language = :language AND field = :field";
			$checkStmt = $conn->prepare($checkSql);
			$checkStmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
			$checkStmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
			$checkStmt->bindParam(':language', $language, PDO::PARAM_STR);
			$checkStmt->bindParam(':field', $field, PDO::PARAM_STR);
			$checkStmt->execute();
			if ($checkStmt->fetchColumn() > 0) {
				$sql = "UPDATE translations SET translation = :translation WHERE content_type = :content_type AND content_id = :content_id AND language = :language AND field = :field";
			} else {
				$sql = "INSERT INTO translations (content_type, content_id, language, field, translation) VALUES (:content_type, :content_id, :language, :field, :translation)";
			}
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
			$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
			$stmt->bindParam(':language', $language, PDO::PARAM_STR);
			$stmt->bindParam(':field', $field, PDO::PARAM_STR);
			$stmt->bindParam(':translation', $translation, PDO::PARAM_STR);
			$stmt->execute();
		}
		$conn->commit();
		return true;
	} catch (Exception $e) {
		$conn->rollback();
		return false;
	}
}

function delete_content_translation($content_type, $content_id, $language = null, $field = null) {
	if (ADMIN_DEMO || !USER_ADMIN) {
		die('ERR 237');
	}
	$conn = open_connection();
	$sql = "DELETE FROM translations WHERE content_type = :content_type AND content_id = :content_id";
	if ($language !== null) {
		$sql .= " AND language = :language";
	}
	if ($field !== null) {
		$sql .= " AND field = :field";
	}
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
	$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
	if ($language !== null) {
		$stmt->bindParam(':language', $language, PDO::PARAM_STR);
	}
	if ($field !== null) {
		$stmt->bindParam(':field', $field, PDO::PARAM_STR);
	}
	return $stmt->execute();
}

function get_extra_fields($content_type) {
	$conn = open_connection();
	$sql = "SELECT * FROM extra_fields WHERE content_type = :content_type";
	$st = $conn->prepare($sql);
	$st->bindValue(':content_type', $content_type, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	return $rows;
}

function get_extra_field_by_id($id) {
	$conn = open_connection();
	$sql = "SELECT * FROM extra_fields WHERE id = :id LIMIT 1";
	$st = $conn->prepare($sql);
	$st->bindValue(':id', $id, PDO::PARAM_INT);
	$st->execute();
	$row = $st->fetch(PDO::FETCH_ASSOC);
	return $row;
}

function get_extra_field_by_key($field_key, $content_type = null) {
	$allowed_types = ['game', 'category', 'page', 'post'];
	$including_type = false;
	if(!is_null($content_type)){
		if(in_array($content_type, $allowed_types)){
			$including_type = true;
		}
	}
	$conn = open_connection();
	$sql = "SELECT * FROM extra_fields WHERE field_key = :field_key";
	if ($including_type) {
		$sql .= " AND content_type = :content_type";
	}
	$sql .= " LIMIT 1";
	$st = $conn->prepare($sql);
	$st->bindValue(':field_key', $field_key, PDO::PARAM_STR);
	if ($including_type) {
		$st->bindValue(':content_type', $content_type, PDO::PARAM_STR);
	}
	$st->execute();
	$row = $st->fetch(PDO::FETCH_ASSOC);
	return $row;
}

function backup_cms($root_path, $backup_type = 'part'){
	// Backup directory and file name
	if (extension_loaded('zip') && is_login() && USER_ADMIN && !ADMIN_DEMO) {
		$backup_dir = $root_path.'/admin/backups';
		if (!file_exists($backup_dir)) {
			mkdir($backup_dir, 0755, true);
		}
		if (!file_exists($backup_dir.'/index.php')) {
			file_put_contents($backup_dir.'/index.php', '');
		}
		$backup_file = $_SESSION['username'].'-cloudarcade-backup-'.$backup_type.'-'.VERSION.'-'.time().'-'.generate_random_strings().'.zip';
		$allowed_folders = [];
		$allowed_extensions = [];
		if($backup_type == 'part'){
			$allowed_folders = ['admin', 'classes', 'db', 'includes', 'js', 'locales']; // 'images'
			$allowed_extensions = ['php', 'js', 'html', 'xml', 'json', 'css', 'htaccess', 'ico', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
		}
		$options = [
			'allowed_folders'	=> $allowed_folders, // root
			'ignore_folders'	=> ['cloudarcade', 'private', 'cache', 'temp', 'backups'], // also applied on sub-folder
			'ignore_extensions'	=> ['zip', 'rar', '7z'],
			'whitelisted_files'	=> [],
			'allowed_extensions'	=> $allowed_extensions,
			'ignore_files'		=> []
		];
		if($backup_type == 'part'){
			$options['whitelisted_files'] = ['content/themes/theme-functions.php'];
			$options['ignore_files'] = ['connect.php'];
		}
		zip_files_recursive( $root_path, ABSPATH . 'admin/backups/'.$backup_file, $options );
	}
}

function zip_files_recursive($source, $destination, $options = []) {
	$allowedFolders = isset($options['allowed_folders']) ? $options['allowed_folders'] : [];
	$ignoreFolders = isset($options['ignore_folders']) ? $options['ignore_folders'] : [];
	$ignoreExtensions = isset($options['ignore_extensions']) ? $options['ignore_extensions'] : [];
	$whitelistedFiles = isset($options['whitelisted_files']) ? $options['whitelisted_files'] : [];
	$ignoreFiles = isset($options['ignore_files']) ? $options['ignore_files'] : [];
	$allowedExtensions = isset($options['allowed_extensions']) ? $options['allowed_extensions'] : [];
	if (!extension_loaded('zip') || !is_login()) {
		return false;
	}
	if (file_exists($source)) {
		$zip = new ZipArchive();
		if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
			$maxSize = 20 * 1024 * 1024; // 20 MB
			if (is_dir($source)) {
				$iterators = [];
			    if (!empty($allowedFolders)) {
			        foreach ($allowedFolders as $allowedFolder) {
			            $folderPath = $source . $allowedFolder . '/';
			            if (file_exists($folderPath)) {
			                $iterators[] = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
			            }
			        }
			        $root_files = scandir($source);
			        $_root_files = [];
			        foreach ($root_files as $file) {
					    if ($file == '.' || $file == '..') {
					        continue;
					    }
					    $filePath = $source . $file;
					    if (is_file($filePath)) {
					        $_root_files[] = new SplFileInfo($filePath);
					    }
					}
					$iterators[] = $_root_files;
			    } else {
			        $iterators[] = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
			    }

			    foreach ($iterators as $files) {
			        foreach ($files as $file) {
			        	if (count($allowedExtensions) > 0 && !in_array(pathinfo($file, PATHINFO_EXTENSION), $allowedExtensions)) {
			        		continue;
			        	}
			            $ignored = false;
						foreach ($ignoreFolders as $ignore) {
							if (stripos($file, $ignore) !== false) {
								$ignored = true;
								break;
							}
						}
						if ($ignored) {
							continue;
						}
						$relativePath = $file->getPathname() === $source ? $file->getFilename() : str_replace('\\', '/', str_replace($source . DIRECTORY_SEPARATOR, '', $file->getPathname()));
						$thePath = str_replace($source, '', $relativePath);
						// Check if the folder is allowed
						$folderName = explode('/', $thePath)[0];
						$isDir = false;
						if (is_dir($source . '/' . $folderName) && strpos($folderName, '.') === false) {
							$isDir = true;
						}
						if(in_array($thePath, $ignoreFiles)){
							continue;
						}
						if (is_dir($file)) {
							if (count(glob("$file/*")) > 0) { //If folder not empty
								$zip->addEmptyDir($relativePath . '/');
							}
						} else if (is_file($file)) {
							// Ignore files larger than 20 MB
							if (filesize($file) > $maxSize) {
								continue;
							}
							// Ignore archive files
							$ext = pathinfo($file, PATHINFO_EXTENSION);
							if (in_array($ext, $ignoreExtensions)) {
								continue;
							}
							$zip->addFromString($relativePath, file_get_contents($file));
						}
			        }
			    }
			} else if (is_file($source)) {
				// Add single file
			}
			return $zip->close();
		}
	}
	return false;
}

?>