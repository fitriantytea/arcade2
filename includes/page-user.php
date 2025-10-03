<?php

if(isset($url_params[1]) && $url_params[1] != ''){
	$url_params[1] = htmlspecialchars($url_params[1]);
} else {
	header( "Location: /" );
	return;
}

$rank;
if(file_exists(ABSPATH.'includes/rank.json')){
	$rank = json_decode(file_get_contents(ABSPATH.'includes/rank.json'), true);
	$rank_values = array_values($rank);
}

$page_title = $url_params[1];
$meta_description = SITE_DESCRIPTION;

require_once( TEMPLATE_PATH . '/functions.php' );

if(file_exists(TEMPLATE_PATH.'/user.php')){
	require(TEMPLATE_PATH.'/user.php');
	return;
}

//Start page

require( TEMPLATE_PATH.'/includes/header.php' );

$is_visitor = true;
$cur_user = null;

if($login_user && $login_user->username === $url_params[1]){
	$is_visitor = false;
	$cur_user = $login_user;
} else {
	$cur_user = User::getByUsername(strtolower($url_params[1]));
}

if(isset($url_params[2]) && $url_params[2] == 'edit'){
	$_GET['edit'] = true;
}

if($cur_user){
	if(isset($_GET['edit']) && !$is_visitor){
		//Edit user profile
		require( ABSPATH . 'includes/page-user-edit.php' );
	} else {
		//User profile page
		require( ABSPATH . 'includes/page-user-profile.php' );
	}
} else {
	//User is not exist
	?>
	<div class="container">
		<p>
			<h2 class="text-center"><?php _e('User does not exist!') ?></h2>
		</p>
	</div>
	<?php
}

require( TEMPLATE_PATH.'/includes/footer.php' );

//End page

?>