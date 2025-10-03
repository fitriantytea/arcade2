<?php

$slug = isset($_GET['slug']) ? $_GET['slug'] : 'menus';

$tab_list = array(
	'menus' => 'Menus',
	'widgets' => 'Widgets',
);

if($slug == 'menus'){
	require_once( 'core/menus.php' );
} elseif($slug == 'widgets'){
	require_once( 'core/widgets.php' );
}

?>