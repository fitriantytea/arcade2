<?php

require_once( TEMPLATE_PATH . '/functions.php' );

$page_title = get_site_info('title');
$meta_description = get_site_info('meta_description');

require( TEMPLATE_PATH . '/home.php' );

?>