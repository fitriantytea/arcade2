<?php

defined('POST_ACTIVE') or die('Posts plugin not installed.');

require_once( TEMPLATE_PATH . '/functions.php' );

$post = null;

if ( isset($_GET['slug']) ) {
	$_GET['slug'] = htmlspecialchars($_GET['slug']);
	if(strlen($_GET['slug']) >= 2){
		$post = Post::getBySlug( $_GET['slug'] );
	}
}

function _is_post_page_valid(){
	// Used to validate the pagination
	// Set to 404 if current page is not exist
	// This script is inefficient, reason: Similar code is also executed in theme post-list.php (Double call)
	// But at least all themes is applied this rules instead of update all themes or possibly unsupported method for old theme
	global $url_params;
	$cur_page = 1;
	if(isset($url_params[1])){
		$_GET['page'] = $url_params[1];
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
	$items_per_page = get_setting_value('post_results_per_page');
	$data = Post::getList($items_per_page, 'created_date DESC', $items_per_page*($cur_page-1));
	$total_posts = $data['totalRows'];
	$total_page = $data['totalPages'];
	$posts = $data['results'];
	if(count($posts) >= 1){
		return true;
	} else {
		return false;
	}
}

if($post){
	if(PRETTY_URL){
		if(count($url_params) >= 3){
			// Post page only contains 3 parameter max
			// Show 404 screen
			require( ABSPATH . 'includes/page-404.php' );
			return;
		}
	}
	if($lang_code != 'en'){
		// If use translation (localization)
		// Begin translate the content if has translation
		$translated_fields = get_content_translation('post', $post->id, $lang_code, 'all');
		if(!is_null($translated_fields)){
			$post->title = isset($translated_fields['title']) ? $translated_fields['title'] : $post->title;
			$post->content = isset($translated_fields['content']) ? $translated_fields['content'] : $post->content;
		}
	}

	$page_title = $post->title . ' | '.SITE_TITLE;
	$meta_description = str_replace(array('"', "'"), "", strip_tags($post->content));
	require( TEMPLATE_PATH . '/post.php' );
} else {
	if(file_exists( TEMPLATE_PATH . '/post-list.php' )){
		if(PRETTY_URL){
			if(count($url_params) > 2){
				// Post list page can contains 3 parameter max
				// Show 404 screen
				require( ABSPATH . 'includes/page-404.php' );
				return;
			}
			if(isset($url_params[1]) && !is_numeric($url_params[1])){
				// Page number should be a number
				// Show 404 screen
				require( ABSPATH . 'includes/page-404.php' );
				return;
			}
		}
		if(_is_post_page_valid()){
			$page_title = _t('Posts') . ' | '.SITE_TITLE;
			$meta_description = _t('Posts') .' | '.SITE_DESCRIPTION;
			require( TEMPLATE_PATH . '/post-list.php' );
		} else {
			require( ABSPATH . 'includes/page-404.php' );
		}
	} else {
		require( ABSPATH . 'includes/page-404.php' );
	}
}

?>