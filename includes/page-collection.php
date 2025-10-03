<?php

require_once( TEMPLATE_PATH . '/functions.php' );

if(PRETTY_URL){
	if(count($url_params) > 3 || count($url_params) < 2){
		// Collection page only contains 3 parameter max,
		// If more than that or less than 2, the url is not valid
		// Show 404 screen
		require( ABSPATH . 'includes/page-404.php' );
		return;
	}
	if(isset($url_params[2]) && !is_numeric($url_params[2])){
		// Page number should be a number
		// Show 404 screen
		require( ABSPATH . 'includes/page-404.php' );
		return;
	}
}

$cur_page = 1;
if(isset($url_params[2])){
	$_GET['page'] = $url_params[2];
	if(!is_numeric($_GET['page'])){
		$_GET['page'] = 1;
	}
}
if(isset($_GET['page'])){
	$cur_page = htmlspecialchars($_GET['page']);
	if(!is_numeric($cur_page)){
		$cur_page = 1;
	}
}

if(get_setting_value('allow_slug_translation')){
	$_original_slug = get_translation_key($_GET['slug']);
	if($_original_slug && substr($_original_slug, 0, 5) == 'slug:'){
		// The slug have a translation
		$_GET['slug'] = str_replace('slug:', '', $_original_slug); // The slug variable is modified
	}
}

$collection = Collection::getBySlug(esc_slug($_GET['slug']));
if($collection && $collection->allow_dedicated_page){
	$data = Collection::getListByCollection($collection->name, 200);
	$games = $data['results'];
	$total_games = $data['totalRows'];
	$total_page = 0;
	if(isset($collection->description) && $collection->description != ''){
		$meta_description = $collection->description;
	} else {
		$meta_description = _t('Play %a Games', $collection->name).' | '.SITE_DESCRIPTION;
	}
	$archive_title = _t($collection->name);
	$page_title = _t('%a Games', $collection->name).' | '.SITE_DESCRIPTION;
	if(file_exists(TEMPLATE_PATH . '/collection.php')){
		require( TEMPLATE_PATH . '/collection.php' );
	} else {
		require( ABSPATH . 'includes/page-404.php' );
	}
} else {
	require( ABSPATH . 'includes/page-404.php' );
}

?>