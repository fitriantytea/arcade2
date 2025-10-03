<?php

require_once( TEMPLATE_PATH . '/functions.php' );

if(PRETTY_URL){
	if(count($url_params) > 3 || count($url_params) < 2){
		// Search page only contains 3 parameter max,
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

$_GET['slug'] = htmlspecialchars(str_replace('-', ' ', $_GET['slug']));

$cur_page = 1;

if(isset($url_params[2])){
	$cur_page = (int)$url_params[2];
}
$items_per_page = get_setting_value('search_results_per_page');
$data = Game::searchGame($_GET['slug'], $items_per_page, $items_per_page*($cur_page-1));
$games = $data['results'];
$total_games = $data['totalRows'];
$total_page = $data['totalPages'];
$meta_description = _t('Search %a Games', $_GET['slug']).' | '.SITE_DESCRIPTION;
$archive_title = _t('Search %a', $_GET['slug']);
$page_title = _t('Search %a Games', $_GET['slug']).' | '.SITE_DESCRIPTION;

require( TEMPLATE_PATH . '/search.php' );

?>