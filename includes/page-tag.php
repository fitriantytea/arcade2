<?php

require_once( TEMPLATE_PATH . '/functions.php' );

if(PRETTY_URL){
	if(count($url_params) > 3 || count($url_params) < 2){
		// Tag page only contains 3 parameter max,
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

$tag_name = null;

$_GET['slug'] = esc_slug($_GET['slug']);

if(get_setting_value('allow_slug_translation')){
	$_original_slug = get_translation_key($_GET['slug']);
	if($_original_slug){
		// The slug have a translation
		if(substr($_original_slug, 0, 5) == 'slug:'){
			$_GET['slug'] = str_replace('slug:', '', $_original_slug); // The slug variable is modified
		} else {
			// Fix bug for duplicated or same localization value, fix only for Tag
			if(_t($_original_slug) == $_GET['slug']){
				$_GET['slug'] = $_original_slug;
			}
		}
	}
}

$conn = open_connection();
$sql = 'SELECT * FROM tags WHERE name = :name';
$st = $conn->prepare($sql);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$st->bindValue(":name", $_GET['slug'], PDO::PARAM_STR);
$st->execute();
$tag = $st->fetch();
if ($tag) {
	$tag = (object) $tag;
	$tag_name = _t($_GET['slug']);
}

if(!is_null($tag_name)){
	$items_per_page = get_setting_value('category_results_per_page');
	$data = fetch_games_by_tag($_GET['slug'], $items_per_page, $items_per_page*($cur_page-1), true);
	$games = $data['results'];
	$total_games = $data['totalRows'];
	$total_page = $data['totalPages'];
	if($cur_page > $total_page){
		// Page number is more than actual maximum page
		require( ABSPATH . 'includes/page-404.php' );
		return;
	}
	$meta_description = _t('Play %a Games', $tag_name).' | '.SITE_DESCRIPTION;
	$page_title = _t('%a Games', $tag_name).' | '.SITE_DESCRIPTION;

	require( TEMPLATE_PATH . '/tag.php' );
} else {
	require( ABSPATH . 'includes/page-404.php' );
}

?>