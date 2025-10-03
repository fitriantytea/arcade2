<?php

require_once( TEMPLATE_PATH . '/functions.php' );

if ( !isset($_GET['slug']) || !$_GET['slug'] ) {
	require( ABSPATH . 'includes/page-homepage.php' );
	return;
}
if(count($url_params) > 2){
	// Have additional unofficial parameters
	require( ABSPATH . 'includes/page-404.php' );
	return;
}

$_GET['slug'] = htmlspecialchars($_GET['slug']);

$page = Page::getBySlug( $_GET['slug'] );
if($page){
	if($lang_code != 'en'){
		// If use translation (localization)
		// Begin translate the content if has translation
		$translated_fields = get_content_translation('page', $page->id, $lang_code, 'all');
		if(!is_null($translated_fields)){
			$page->title = isset($translated_fields['title']) ? $translated_fields['title'] : $page->title;
			$page->content = isset($translated_fields['content']) ? $translated_fields['content'] : $page->content;
		}
	}

	$page_title = $page->title . ' | '.SITE_TITLE; // Not used, overriden by theme-functions.php
	$meta_description = str_replace(array('"', "'"), "", strip_tags($page->content));
	$page->content = run_shortcode($page->content);
	
	require( TEMPLATE_PATH . '/page.php' );
} else {
	require( ABSPATH . 'includes/page-404.php' );
}

?>