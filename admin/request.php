<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once( "../config.php" );
require_once( "../init.php" );
require_once( "admin-functions.php" );

if(count($_POST) == 0){
	$_POST = $_GET;
}

$action = isset( $_POST['action'] ) ? $_POST['action'] : "";
$username = isset( $_SESSION['username'] ) ? $_SESSION['username'] : "";

if ( !$username || !USER_ADMIN ) {
	exit('logout');
}
if(isset($_POST['redirect'])){
	$_POST['redirect'] = esc_url($_POST['redirect']);
}

if( ADMIN_DEMO ){
	if($action == 'getPageData' || $action == 'getGameData' || $action == 'getCategoryData'){
		//
	} else {
		if(isset($_POST['redirect'])){
			header('Location: '.$_POST['redirect']);
		}
		exit();
	}
}

switch ( $action ) {
	case 'deleteGame':
		$game = Game::getById( (int)$_POST['id'] );
		if($game){
			$game->delete();
			_trigger_auto_sitemap();
		}
		if(isset($_POST['redirect'])){
			header('Location: '.$_POST['redirect'].'&status=deleted');
		} else {
			echo 'ok';
		}
		break;
	case 'getGameData':
		$game = Game::getById( (int)$_POST['id'] );
		$game->tags = $game->get_tags();
		$json = json_encode($game);
		echo $json;
		break;
	case 'editGame':
		$_POST['description'] = html_purify($_POST['description']);
		$_POST['instructions'] = html_purify($_POST['instructions']);
		if(is_array($_POST['category'])){
			$_POST['category'] = implode(',', $_POST['category']);
		}
		if(!isset($_POST['is_mobile'])){
			$_POST['is_mobile'] = false;
		}
		if(!isset($_POST['published'])){
			// Is dafted from edit game page
			$_POST['published'] = false;
		}
		$pass = true;
		$info = null;
		if(isset($_POST['slug'])){
			$_game = Game::getBySlug($_POST['slug']);
			if($_game && $_game->id != $_POST['id']){
				$pass = false;
				$info = [
					'type' => 'warning',
					'text' => 'Slug already exist = '.$_POST['slug']
				];
			}
		}
		if($pass){
			$game = Game::getById( (int)$_POST['id'] );
			$game->storeFormValues( $_POST );
			$game->update();
			$info = [
				'type' => 'success',
				'text' => 'Game updated!'
			];
		}
		if(isset($_POST['redirect'])){
			$_SESSION['message'] = $info;
			header('Location: '.$_POST['redirect']);
			exit();
		}
		break;
	case 'newPage':
		$_POST['content'] = get_setting_value('purify_page') ? html_purify($_POST['content']) : $_POST['content'];
		if(!isset($_POST['nl2br'])){
			$_POST['nl2br'] = 0;
		} else {
			$_POST['nl2br'] = 1;
		}
		$page = new Page;
		$page->storeFormValues( $_POST );
		$page->insert();
		_trigger_auto_sitemap();
		break;
	case 'deletePage':
		$page = Page::getById( (int)$_POST['id'] );
		$page->delete();
		_trigger_auto_sitemap();
		break;
	case 'getPageData':
		$page = Page::getById( (int)$_POST['id'] );
		$json = json_encode($page);
		echo $json;
		break;
	case 'editPage':
		$info = null;
		$_POST['content'] = get_setting_value('purify_page') ? html_purify($_POST['content']) : $_POST['content'];
		if(!isset($_POST['nl2br'])){
			$_POST['nl2br'] = 0;
		} else {
			$_POST['nl2br'] = 1;
		}
		$page = Page::getById( (int)$_POST['id'] );
		$page->storeFormValues( $_POST );
		$page->update();
		if(isset($_POST['redirect'])){
			$info = [
				'type' => 'success',
				'text' => 'Page updated!'
			];
			$_SESSION['message'] = $info;
			header('Location: '.$_POST['redirect']);
			exit();
		}
		break;
	case 'editCategory':
		$info = null;
		$_POST['name'] = htmlspecialchars($_POST['name']);
		$category = new Category;
		$exist = $category->isCategoryExist( $_POST['name'] );
		if($exist){
			$_POST['description'] = html_purify($_POST['description']);
			$_POST['meta_description'] = html_purify($_POST['meta_description']);
			$_POST['slug'] = esc_slug($_POST['slug']);
			$category = Category::getById( (int)$_POST['id'] );
			if(isset($_POST['hide']) && $_POST['hide'] == 'on') {
				$_POST['priority'] = -1;
			} else {
				if($category->priority>=0){
					$_POST['priority'] = $_POST['priority'];
				} else {
					$_POST['priority'] = 0;
				}
			}
			$category->storeFormValues( $_POST );
			$category->update();
			$info = [
				'type' => 'success',
				'text' => 'Category updated!'
			];
		} else { //Update category name
			$_POST['description'] = html_purify($_POST['description']);
			$_POST['meta_description'] = html_purify($_POST['meta_description']);
			$_POST['slug'] = esc_slug($_POST['slug']);
			$category = Category::getById( (int)$_POST['id'] );
			$old_name = $category->name;
			$category->storeFormValues( $_POST );
			$category->update();
			//Update all related games
			$data = Category::getListByCategory($category->id, 10000);
			$games = $data['results'];
			$count = 0;
			foreach ($games as $game) {
				$game->category = str_replace($old_name, $_POST['name'], $game->category);
				$game->update_category();
				$count++;
			}
			$info = [
				'type' => 'success',
				'text' => 'Change '.$old_name.' to '.$_POST['name'].', '.$count.' games affected.'
			];
		}
		if(isset($_POST['redirect'])){
			$_SESSION['message'] = $info;
			header('Location: '.$_POST['redirect']);
			exit();
		}
		break;
	case 'deleteCategory':
		$category = Category::getById( (int)$_GET['id'] );
		$category->delete();
		$data = Category::getListByCategory((int)$_GET['id'], 10000);
		$games = $data['results'];
		foreach ($games as $game) {
			$game->delete();
		}
		_trigger_auto_sitemap();
		if(isset($_POST['redirect'])){
			header('Location: '.$_POST['redirect'].'&status=deleted');
		}
		break;
	case 'newCategory':
		$_POST['name'] = htmlspecialchars($_POST['name']);
		$_POST['description'] = html_purify($_POST['description']);
		$_POST['meta_description'] = html_purify($_POST['meta_description']);
		if(isset($_POST['slug'])){
			$_POST['slug'] = esc_slug($_POST['slug']);
		} else {
			$_POST['slug'] = esc_slug($_POST['name']);
		}
		$category = new Category;
		$exist = $category->isCategoryExist( $_POST['name'] );
		if($exist){
		  //echo 'Category already exist ';
		} else {
		  $category->storeFormValues( $_POST );
		  $category->insert();
		  _trigger_auto_sitemap();
		}
		if(isset($_POST['redirect'])){
			if($exist){
				header('Location: '.$_POST['redirect'].'&status=exist');
			} else {
				header('Location: '.$_POST['redirect'].'&status=added');
			}
		}
		break;
	case 'getCategoryData':
		$data = Category::getById( (int)$_POST['id'] );
		$json = json_encode($data);
		echo $json;
		break;
	case 'newCollection':
		require( dirname(__FILE__).'/../classes/Collection.php' );
		$_POST['name'] = esc_string($_POST['name']);
		if(!isset($_POST['slug'])){
			$_POST['slug'] = esc_slug($_POST['name']);
		} else {
			$_POST['slug'] = esc_slug($_POST['slug']);
		}
		$_POST['data'] = preg_replace('/[^0-9,]+/', '', $_POST['data']);
		$collection = new Collection;
		$exist = $collection->isCollectionExist( $_POST['name'] );
		if($exist){
		  //echo 'Collection already exist ';
		} else {
		  $collection->storeFormValues( $_POST );
		  $collection->insert();
		}
		if(isset($_POST['redirect'])){
			if($exist){
				header('Location: '.$_POST['redirect'].'&status=exist');
			} else {
				header('Location: '.$_POST['redirect'].'&status=added');
			}
		}
		break;
	case 'editCollection':
		require( dirname(__FILE__).'/../classes/Collection.php' );
		$_POST['name'] = esc_string($_POST['name']);
		if(!isset($_POST['slug'])){
			$_POST['slug'] = esc_slug($_POST['name']);
		} else {
			$_POST['slug'] = esc_slug($_POST['slug']);
		}
		$_POST['data'] = preg_replace('/[^0-9,]+/', '', $_POST['data']);
		$_POST['description'] = html_purify($_POST['description']);
		if(!isset($_POST['allow_dedicated_page'])){
			$_POST['allow_dedicated_page'] = false;
		}
		$collection = new Collection;
		$collection->storeFormValues( $_POST );
		$collection->update();
		if(isset($_POST['redirect'])){
			header('Location: '.$_POST['redirect'].'&status=updated');
		}
		break;
	case 'deleteCollection':
		require( dirname(__FILE__).'/../classes/Collection.php' );
		$collection = Collection::getById( (int)$_GET['id'] );
		$collection->delete();
		if(isset($_POST['redirect'])){
			header('Location: '.$_POST['redirect'].'&status=deleted');
		}
		break;
	case 'getCollectionData':
		require( dirname(__FILE__).'/../classes/Collection.php' );
		$data = [];
		$data['collection'] = Collection::getById( (int)$_POST['id'] );
		$data['list'] = [];
		if(isset($data['collection']->data)){
			$arr = commas_to_array($data['collection']->data);
			foreach ($arr as $id) {
				$game = Game::getById($id);
				if($game){
					$data['list'][] = array('id' => $id,'title' => $game->title);
				} else {
					$data['list'][] = array('id' => $id,'title' => 'Game not exist!');
				}
			}
		}
		$json = json_encode($data);
		echo $json;
		break;
	case 'addGame':
		add_game();
		break;
	case 'updateLogo':
		upload_logo();
		break;
	case 'updateLoginLogo':
		upload_login_logo();
		break;
	case 'updateIcon':
		upload_icon();
		break;
	case 'updateStyle':
		update_style();
		break;
	case 'updateTheme':
		update_theme();
		break;
	case 'updateLayout':
		update_layout();
		break;
	case 'updateLanguage':
		update_settings('language', $_POST['language']);
		if(isset($_POST['redirect'])){
			header('Location: '.$_POST['redirect'].'&status=saved');
		}
		break;
	case 'saveSettings':
		save_settings();
		break;
	case 'siteSettings':
		site_settings();
		break;
	case 'userSettings':
		user_settings();
		break;
	case 'listingsSettings':
		listings_settings();
		break;
	case 'otherSettings':
		other_settings();
		break;
	case 'set_save_thumbs':
		set_advanced_setting('set_save_thumbs');
		break;
	case 'set_small_thumb':
		set_advanced_setting('set_small_thumb');
		break;
	case 'set_protocol':
		set_advanced_setting('set_protocol');
		break;
	case 'set_prettyurl':
		set_advanced_setting('set_prettyurl');
		break;
	case 'set_www':
		set_advanced_setting('set_www');
		break;
	case 'set_custom_slug':
		set_advanced_setting('set_custom_slug');
		break;
	case 'set_unicode_slug':
		set_advanced_setting('set_unicode_slug');
		break;
	case 'set_custom_path':
		update_custom_path();
		break;
	case 'set_option':
		//New method, set_advanced_settings() replacement
		_set_option();
		break;
	case 'updatePurchaseCode':
		update_purchase_code();
		break;
	case 'updater':
		updater3();
		break;
	case 'pluginAction':
		plugin_action();
		break;
	default:
		exit;
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
function add_game(){
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
	if(!isset($_POST['url'])){
		$_POST['url'] = '';
	}
	$redirect = 0;
	if(isset($_POST['redirect'])){
		$redirect = $_POST['redirect'];
	}

	// Handling slug
	if(isset($_POST['slug'])){
		$slug = esc_slug($_POST['slug']);
	} else {
		$slug = esc_slug(strtolower(str_replace(' ', '-', $_POST["title"])));
	}
	$slug = preg_replace('/-{2,}/', '-', $slug);
	$slug = trim($slug, '-');
	$_POST['slug'] = $slug;

	// Handle categories as string
	if(is_array($_POST['category'])){
		$cats = implode(',', $_POST['category']);
		$_POST['category'] = $cats;
	}
	if(empty(trim($_POST['category']))){
		$_POST['category'] = 'Other';
	}

	// Category filter plugin
	if(file_exists(ABSPATH."content/plugins/category-filter")){
		$categories = commas_to_array($_POST['category']);
		$_POST['category'] = implode(',', array_map('category_name_filtering', $categories));
	}

	$game = new Game;
	$check = $game->getBySlug($slug);
	$status = 'failed';

	// Proceed only if the game doesn't already exist
	if(is_null($check)){
		// Handle the thumbnail upload if selected
		if(isset($_POST['thumb_method']) && $_POST['thumb_method'] == 'upload') {
			// Check if file was uploaded
			if(isset($_FILES['thumb_upload_1']) && $_FILES['thumb_upload_1']['error'] == UPLOAD_ERR_OK){
				// Allowed file types
				$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
				$file_type = mime_content_type($_FILES['thumb_upload_1']['tmp_name']);
				$file_size = $_FILES['thumb_upload_1']['size'];

				if(in_array($file_type, $allowed_types) && $file_size <= 1048576){ // 1MB = 1048576 bytes
					// Move uploaded file to the desired location
					$upload_dir = ABSPATH . 'thumbs/';
					$file_name_1 = $slug . '_1.' . pathinfo($_FILES['thumb_upload_1']['name'], PATHINFO_EXTENSION);
					move_uploaded_file($_FILES['thumb_upload_1']['tmp_name'], $upload_dir . $file_name_1);
					$_POST['thumb_1'] = '/thumbs/' . $file_name_1;
				} else {
					// File not valid
					$status = 'invalid_thumb_1';
				}
			}

			if(isset($_FILES['thumb_upload_2']) && $_FILES['thumb_upload_2']['error'] == UPLOAD_ERR_OK){
				$file_type = mime_content_type($_FILES['thumb_upload_2']['tmp_name']);
				$file_size = $_FILES['thumb_upload_2']['size'];

				if(in_array($file_type, $allowed_types) && $file_size <= 1048576){
					$file_name_2 = $slug . '_2.' . pathinfo($_FILES['thumb_upload_2']['name'], PATHINFO_EXTENSION);
					move_uploaded_file($_FILES['thumb_upload_2']['tmp_name'], $upload_dir . $file_name_2);
					$_POST['thumb_2'] = '/thumbs/' . $file_name_2;
				} else {
					$status = 'invalid_thumb_2';
				}
			}
		} else {
			// Handle remote URL thumbnails (existing functionality)
			$array_thumbnail_source = [];
			$array_thumbnail_source['thumb_1'] = $_POST['thumb_1'];
			$array_thumbnail_source['thumb_2'] = $_POST['thumb_2'];
			$_POST['data'] = array(
				'thumbnail_source' => $array_thumbnail_source
			);

			if(IMPORT_THUMB){
				$use_webp = get_setting_value('webp_thumbnail');
				import_thumbnail($_POST['thumb_2'], $slug, 2);
				$name = basename($_POST['thumb_2']);
				$extension = pathinfo(strtok($_POST['thumb_2'], '?'), PATHINFO_EXTENSION);
				$_POST['thumb_2'] = '/thumbs/'.$slug.'_2.'.$extension;
				if($use_webp){
					$_POST['thumb_2'] = str_replace('.'.$extension, '.webp', $_POST['thumb_2']);
				}
				import_thumbnail($_POST['thumb_1'], $slug, 1);
				$name = basename($_POST['thumb_1']);
				$extension = pathinfo(strtok($_POST['thumb_1'], '?'), PATHINFO_EXTENSION);
				$_POST['thumb_1'] = '/thumbs/'.$slug.'_1.'.$extension;
				if($use_webp){
					$_POST['thumb_1'] = str_replace('.'.$extension, '.webp', $_POST['thumb_1']);
				}
			}
		}

		// Continue processing game addition
		$game->storeFormValues($_POST);
		$game->insert();
		$status = 'added';

		// Handle categories and auto-insert if necessary
		$cats = commas_to_array($_POST['category']);
		foreach ($cats as $cat_name) {
			$category = new Category;
			if(!$category->isCategoryExist($cat_name)){
				$_POST['name'] = $cat_name;
				unset($_POST['slug']);
				$_POST['description'] = '';
				$category->storeFormValues($_POST);
				$category->insert();
			}
			$category->addToCategory($game->id, $category->id);
		}
	} else {
		$status = 'exist';
	}

	// Handle redirection and status response
	if(isset($_POST['source'])) {
		if(!$redirect){
			echo $status;
			echo ' - '.$_POST['title'];
		}
	}

	// Store or clear session data based on status
	$keys = ['title', 'slug', 'description', 'instructions', 'width', 'height', 'category', 'thumb_1', 'thumb_2', 'url', 'tags'];
	if($status != 'added'){
		if(($_POST['source'] == 'self' || $_POST['source'] == 'remote') && !isset($_POST['dont_store_fields'])){
			foreach ($keys as $item) {
				$_SESSION[$item] = $_POST[$item] ?? null;
			}
		}
	} else {
		if(isset($_SESSION['title'])){
			foreach ($keys as $item) {
				unset($_SESSION[$item]);
			}
			_trigger_auto_sitemap();
		}
	}

	if($redirect){
		header('Location: '.$redirect.'&status='.$status);
	}
}

function upload_logo(){
	$redirect = 0;
	if(isset($_POST['redirect'])){
		$redirect = $_POST['redirect'];
	}
	$target_dir = "../images/";
	$file_name = "site-logo." . strtolower(pathinfo($_FILES["logofile"]["name"], PATHINFO_EXTENSION));
	$target_file = $target_dir . $file_name;
	$uploadOk = 1;
	$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	$message = "";
	$type = "success";
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["logofile"]["tmp_name"]);
	  if($check !== false) {
		$message = "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	  } else {
		$message = "File is not an image.";
		$uploadOk = 0;
	  }
	}
	if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg"
	&& $fileType != "gif") {
		$type = "danger";
		$message = "Only JPG, JPEG, PNG & GIF files are allowed.";
		$uploadOk = 0;
	}
	else if ($_FILES["logofile"]["size"] > 2000000) {
		$type = "danger";
		$message = "Your file is too large, Max 2MB.";
		$uploadOk = 0;
	}
	if ($uploadOk == 0) {
		$message = "Your file was not uploaded.";
	} else {
	  if (move_uploaded_file($_FILES["logofile"]["tmp_name"], $target_file)) {
		$type = "success";
		$message = "Your file has been uploaded successfully.";
	  } else {
		$message = "There was an error uploading your file.";
	  }
	  update_setting('site_logo', 'images/'.$file_name);
	}
	if($redirect){
		$_SESSION['message'] = [
			'type' => $type,
			'text' => $message
		];
		header('Location: '.$redirect);
		exit();
	}
}

function upload_login_logo(){
	$redirect = 0;
	if(isset($_POST['redirect'])){
		$redirect = $_POST['redirect'];
	}
	$target_dir = "../images/";
	$file_name = strtolower(str_replace(' ', '-', basename($_FILES["logofile"]["name"])));
	$target_file = $target_dir . $file_name;
	$uploadOk = 1;
	$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	$message = "";
	$classMessage="";
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["logofile"]["tmp_name"]);
	  if($check !== false) {
		$message.= "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	  } else {
		$message.= "File is not an image.";
		$uploadOk = 0;
	  }
	}
	if($fileType != "png" && $fileType != "gif") {
		$classMessage .= "alert-danger";
		$message.= "Only PNG and GIF file are allowed.";
		$uploadOk = 0;
	}
	else if ($_FILES["logofile"]["size"] > 2000000) {
		$classMessage .= "alert-danger";
		$message.= "Your file is too large, Max 2 MB.";
		$uploadOk = 0;
	}
	if ($uploadOk == 0) {
	  $message.= "Your file was not uploaded.";
	} else {
		if (move_uploaded_file($_FILES["logofile"]["tmp_name"],$target_dir . 'login-logo.png')) {
			// update_settings('login_logo', 'images/'.$file_name);
			$classMessage .= "alert-success";
			$message .= "Your file has been uploaded successfully.";
	  } else {
		$message.= "There was an error uploading your file.";
	  }
	}
	if($redirect){
		$_SESSION['message'] = [
			'type' => 'success',
			'text' => $message
		];
		header('Location: '.$redirect);
		exit();
	}
}
function upload_icon(){
	$target_file = '../favicon.ico';
	$uploadOk = 1;
	$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["iconfile"]["tmp_name"]);
	  if($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	  } else {
		echo "File is not an image.";
		$uploadOk = 0;
	  }
	}
	if ($_FILES["iconfile"]["size"] > 500000) {
	  echo "Sorry, your file is too large.";
	  $uploadOk = 0;
	}
	if($fileType != "ico" ) {
	  echo "Sorry, only ICO files are allowed.";
	  $uploadOk = 0;
	}
	if ($uploadOk == 0) {
	  echo "Sorry, your file was not uploaded.";
	} else {
	  if (move_uploaded_file($_FILES["iconfile"]["tmp_name"], $target_file)) {
		//
	  } else {
		echo "Sorry, there was an error uploading your file.";
	  }
	}
	if(isset($_POST['redirect'])){
		$_SESSION['message'] = [
			'type' => 'success',
			'text' => 'Your file has been uploaded successfully.'
		];
		header('Location: '.$_POST['redirect']);
	}
}
function update_style(){
	file_put_contents('../'. TEMPLATE_PATH . '/style/style.css', $_POST['style']);
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function update_layout(){
	foreach ($_POST as $item => $value) {
		if(substr($item, -3) == 'php'){
			$path = str_replace("_",".",$item);
			file_put_contents('../'. TEMPLATE_PATH . '/'.$path, $value);
		}
	}
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function update_theme(){
	// Deprecated since v1.6.2
	update_setting('theme_name', htmlspecialchars($_POST['theme']));
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function update_settings($name, $value){
	// Deprecated since v1.6.2
	$conn = open_connection();
	$sql = "UPDATE options SET value = :value WHERE name = :name";
	$st = $conn->prepare($sql);
	$st->bindValue(":name", $name, PDO::PARAM_STR);
	$st->bindValue(":value", $value, PDO::PARAM_STR);
	$st->execute();
}

function save_settings(){
	$form_data = $_POST['data'];
	$category = $_POST['category'];
	$setting_group = get_setting_group($category);
	$combined_settings = [];
	$changed_settings = [];
	$type_text = false;
	foreach ($setting_group as $setting) {
		if ($setting['type'] === 'bool') {
			if (isset($form_data[$setting['name']])) {
				$combined_settings[$setting['name']] = 1;
			} else {
				$combined_settings[$setting['name']] = 0;
			}
		} else if ($setting['type'] === 'number') {
			$combined_settings[$setting['name']] = $form_data[$setting['name']];
		} else {
			$type_text = true;
		}
	}
	if($type_text){
		foreach ($form_data as $key => $value) {
			$combined_settings[$key] = $value;
		}
	}
	foreach ($combined_settings as $key => $value) {
		// Check value difference between current data with database value
		// So there will be no MySql operation for non-changed value
		if($value != get_setting_value($key)){
			$changed_settings[$key] = $value; // For debugging purpose
			update_setting($key, $value);
		}
	}
	if(isset($_POST['redirect'])){
		$_SESSION['message'] = [
			'type' => 'success',
			'text' => 'Settings have been saved!'
		];
		header('Location: '.$_POST['redirect']);
	}
}
function site_settings(){
	update_settings('site_title', htmlspecialchars($_POST['title']));
	update_settings('site_description', htmlspecialchars($_POST['description']));
	update_settings('meta_description', htmlspecialchars($_POST['meta_description']));
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function user_settings(){
	update_settings('comments', (isset($_POST['comments']) ? 'true' : 'false'));
	update_settings('upload_avatar', (isset($_POST['upload_avatar']) ? 'true' : 'false'));
	update_settings('user_register', (isset($_POST['user_register']) ? 'true' : 'false'));
	update_settings('show_login', (isset($_POST['show_login']) ? 'true' : 'false'));
	update_settings('moderate_comment', (isset($_POST['moderate_comment']) ? 'true' : 'false'));
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function listings_settings(){
	update_settings('search_results_per_page', esc_int($_POST['search_results_per_page']));
	update_settings('category_results_per_page', esc_int($_POST['category_results_per_page']));
	update_settings('post_results_per_page', esc_int($_POST['post_results_per_page']));
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function other_settings(){
	update_settings('splash', (isset($_POST['splash']) ? 'true' : 'false'));
	update_settings('show_ad_on_splash', (isset($_POST['show_ad_on_splash']) ? 'true' : 'false'));
	update_settings('trailing_slash', (isset($_POST['trailing_slash']) ? 'true' : 'false'));
	update_settings('lang_code_in_url', (isset($_POST['lang_code_in_url']) ? 'true' : 'false'));
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function set_advanced_setting($type){
	if($type == 'set_save_thumbs'){
		$val = 'false';
		if(isset($_POST['value'])){
			$val = 'true';
		}
		update_settings('import_thumb', $val);
	} elseif($type == 'set_small_thumb'){
		$val = 'false';
		if(isset($_POST['value'])){
			$val = 'true';
		}
		update_settings('small_thumb', $val);
	} elseif($type == 'set_protocol'){
		$val = 'http://';
		if(isset($_POST['value'])){
			$val = 'https://';
		}
		update_settings('url_protocol', $val);
	} elseif($type == 'set_prettyurl'){
		$val = 'false';
		if(isset($_POST['value'])){
			$val = 'true';
		}
		update_settings('pretty_url', $val);
	} elseif($type == 'set_custom_slug'){
		$val = 'false';
		if(isset($_POST['value'])){
			$val = 'true';
		}
		update_settings('custom_slug', $val);
	} elseif($type == 'set_unicode_slug'){
		$val = 'false';
		if(isset($_POST['value'])){
			$val = 'true';
		}
		update_settings('unicode_slug', $val);
	} elseif($type == 'set_www'){
		$val = 'false';
		if(isset($_POST['value'])){
			$val = 'true';
		}
		update_settings('use_www', $val);
	}
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function _set_option(){
	//set_advanced_setting($type) replacement
	//Use prefs ! options
	$status = 'error';
	if(isset($_POST['key'])){
		$status = 'saved';
		if(!$_POST['value']){ //if null
			$_POST['value'] = 0;
		}
		update_option($_POST['key'], $_POST['value']);
	}
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status='.$status);
	}
}
function update_purchase_code(){
	$message = 'Item Purchase code updated!';
	$status = 'success';
	$curl = curl_request('https://api.cloudarcade.net/v2/validate.php?code='.$_POST['code'].'&ref='.DOMAIN.'&v='.VERSION.'&action=update_code');
	if(is_valid_json($curl)){
		$json = json_decode($curl, true);
		if($json['status'] === 'success' || $json['status'] === 'valid'){
			$message = 'Item Purchase code updated!';
			$status = 'success';
			update_setting('purchase_code', $_POST['code']);
		} else {
			$message = $json['message'];
			$status = 'error';
		}
	} else {
		$status = 'error';
		$message = 'Error! json not valid!';
	}
	if(isset($_POST['redirect'])){
		$_SESSION['message'] = [
			'type' => $status,
			'text' => $message
		];
		header('Location: '.$_POST['redirect']);
	}
}
function set_save_thumbs(){
	$bool = 'false';
	if(IMPORT_THUMB){
		$bool = 'true';
	}
	$val = 'false';
	if(isset($_POST['value'])){
		$val = 'true';
	}
	update_settings('import_thumb', $val);
	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status=saved');
	}
}
function upload_thumb($url){
	if($url) {
		$data = file_get_contents($url);
		$name = basename($url);
		$new = '../thumbs/'.$name;
		file_put_contents($new, $data);
	}
}

function update_custom_path(){
	$arr = array();
	$list = ['game','category','page','search','tag','login','register','user','post','full','splash'];
	$fill = $_POST['list'];
	$i = 0;
	foreach ($fill as $value) {
		if($value){
			$value = esc_slug($value);
		}
		if($value){
			$arr[$value] = $list[$i];
		}
		$i++;
	}
	$res = '';
	if(count($arr)){
		$res = json_encode($arr);
	}
	update_setting('custom_path', $res);
	if(isset($_POST['redirect'])){
		$_SESSION['message'] = [
			'type' => 'success',
			'text' => 'Settings have been saved!'
		];
		header('Location: '.$_POST['redirect']);
	}
}
// function updater2(){
// 	$status = 'null';
// 	$info_data = '';
// 	$code = esc_string($_POST['code']);
// 	if(!ADMIN_DEMO && USER_ADMIN){
// 		$curl = curl_request('https://api.cloudarcade.net/verify/verify.php?code='.$code.'&ref='.DOMAIN.'&action=update&v='.VERSION);
// 		$data = json_decode($curl, true);
// 		if(isset($data['log'])){
// 			backup_cms('../', 'part');
// 			if(isset($data['content'])){
// 				$path = $data['path'];
// 				file_put_contents("rf_execute.php", htmlspecialchars_decode($data['content']));
// 				include 'rf_execute.php';
// 				unlink('rf_execute.php');
// 			}
// 			$status = 'updated';
// 		} elseif(isset($data['error'])) {
// 			$status = 'error';
// 			$info_data = $data['description'];
// 		} else {
// 			$status = 'error';
// 			$info_data = json_encode($data);
// 		}
// 		$result = array(
// 			'status' => $status,
// 			'info' => $info_data,
// 		);
// 		echo json_encode($result);
// 	}
// 	if(isset($_POST['redirect'])){
// 		header('Location: '.$_POST['redirect'].'&status='.$status.'&info='.$info_data);
// 	}
// }
function updater3(){
	$status = 'null';
	$info_data = '';
	$code = esc_string($_POST['code']);
	if(!ADMIN_DEMO && USER_ADMIN){
		$curl = curl_request('https://api.cloudarcade.net/verify/verify.php?code='.$code.'&ref='.DOMAIN.'&action=update&v='.VERSION);
		$data = json_decode($curl, true);
		if(isset($data['log'])){
			backup_cms('../', 'part');
			if(isset($data['content'])){
				$path = $data['path'];
				$file_content = htmlspecialchars_decode($data['content']);
				if(true){
					file_put_contents("rf_execute.php", $file_content);
					include 'rf_execute.php';
					unlink('rf_execute.php');
					$status = 'updated';
				} else {
					$status = 'error';
					$info_data = 'Invalid content detected';
				}
			} else {
				$status = 'error';
				$info_data = 'No content found';
			}
		} elseif(isset($data['error'])) {
			$status = 'error';
			$info_data = $data['description'];
		} else {
			$status = 'error';
			$info_data = json_encode($data);
		}
		$result = array(
			'status' => $status,
			'info' => $info_data,
		);
		echo json_encode($result);
	}

	if(isset($_POST['redirect'])){
		header('Location: '.$_POST['redirect'].'&status='.$status.'&info='.$info_data);
	}
}
function plugin_action(){
	$status = '';
	$info = '';
	if(isset($_POST['plugin_action'])){
		if(isset($_POST['name'])){
			$target_dir = ABSPATH . 'content/plugins/' . $_POST['name'];
			if(is_dir( $target_dir )){
				if($_POST['plugin_action'] == 'activate'){
					rename($target_dir, ABSPATH . 'content/plugins/' . substr($_POST['name'], 1));
					$status = 'success';
					$info = 'Plugin activated!';
				} else if($_POST['plugin_action'] == 'deactivate'){
					rename($target_dir, ABSPATH . 'content/plugins/' . '_' . $_POST['name']);
					$status = 'warning';
					$info = 'Plugin deactivated!';
				} else if($_POST['plugin_action'] == 'remove'){
					delete_files($target_dir);
					if(file_exists($target_dir)){
						rmdir($target_dir);
					}
					$status = 'warning';
					$info = 'Plugin removed!';
				}
			}
		}
		if(isset($_POST['url']) && $_POST['plugin_action'] == 'add_plugin'){
			if(isset($_POST['reqversion']) && esc_int(VERSION) >= esc_int($_POST['reqversion'])){
				$target = ABSPATH.'content/plugins/tmp_plugin.zip';
				// Create a cURL resource
				$ch = curl_init();
				// Set cURL options for retrieving the remote file
				curl_setopt($ch, CURLOPT_URL, $_POST['url']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
				// Download the remote file and save it to the target file
				$remoteFile = curl_exec($ch);
				if($remoteFile !== false){
					$localFile = fopen($target, 'w');
					if($localFile){
						fwrite($localFile, $remoteFile);
						fclose($localFile);

						if(file_exists($target)){
							$zip = new ZipArchive;
							$res = $zip->open($target);
							if ($res === TRUE) {
								$zip->extractTo(ABSPATH.'content/plugins/');
								$zip->close();
								$status = 'success';
								$info = 'Plugin installed!';
							} else {
								echo 'doh!';
							}
							unlink($target);
						} else {
							echo 'not found';
						}
					} else {
						echo 'Could not create local file';
					}
				} else {
					echo 'Could not download remote file';
				}
				// Close the cURL resource
				curl_close($ch);
			} else {
				$status = 'error';
				$info = 'Failed to install!. You\'re using CA v'.VERSION.' and this plugin require CA v'.$_POST['reqversion'];
			}
		}
	}
	if(isset($_POST['redirect'])){
		$_SESSION['message'] = [
			'type' => $status,
			'text' => $info
		];
		header('Location: '.$_POST['redirect']);
	}
}

function _trigger_auto_sitemap(){
	if(PRETTY_URL){
		if(!file_exists(dirname(__FILE__).'/../sitemap.xml') || _file_age(dirname(__FILE__).'/../sitemap.xml') > 120){
			if(file_exists(dirname(__FILE__).'/../content/plugins/sitemap/info.json')){ // Sitemap plugin exist
				if(get_setting_value('auto_sitemap')){
					define( '_AUTO_SITEMAP', true );
					global $conn;
					require_once(dirname(__FILE__).'/../sitemap.php');
				}
			}
		}
	}
}

function _file_age($file){
	if (file_exists($file)) {
		// Get the file modification time
		$file_modification_time = filemtime($file);
		// Get the current time
		$current_time = time();
		// Calculate the file age in seconds
		$file_age_in_seconds = $current_time - $file_modification_time;
		// Convert the file age to minutes
		$file_age_in_minutes = $file_age_in_seconds / 60;
		return $file_age_in_minutes;
	} else {
		echo "The file does not exist.";
		return null;
	}
}

?>