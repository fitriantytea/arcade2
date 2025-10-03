<?php

//New window page for gameplay

require_once( TEMPLATE_PATH . '/functions.php' );

if ( !isset($_GET['slug']) || !$_GET['slug'] ) {
	require( ABSPATH . 'includes/page-404.php' );
	return;
}

$_GET['slug'] = htmlspecialchars($_GET['slug']);

$game = Game::getBySlug( $_GET['slug'] );
if($game){
	if($game->source == 'self' && get_setting_value('splash')){
		require( ABSPATH . 'includes/page-splash.php' );
		return;
	}
	if($game->game_type != 'html5'){
		$game_types = get_game_types();
		if(isset($game_types[$game->game_type])){
			header('Location: '.$game_types[$game->game_type]['template'] . '?game='.$game->slug);
		} else {
			return;
		}
		return;
	}
	$page_title = $game->title;
	$meta_description = str_replace(array('"', "'"), "", strip_tags($game->description));

	?>

	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $page_title ?></title>
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
		<meta name="description" content="<?php echo $meta_description ?>">
		<meta name="robots" content="noindex">
		<style type="text/css">
			body {
			    color: #eee;
			    line-height: 1.43;
			    position: inherit;
			    margin: 0;
			    padding: 0;
			    background-color: #000;
			    overflow: hidden;
			    height: 100%;
			}
			#game-content {
			    position: absolute;
			    top: 0;
			    left: 0;
			    width: 0;
			    height: 0;
			    overflow: hidden;
			    max-width: 100%;
			    max-height: 100%;
			    min-width: 100%;
			    min-height: 100%;
			    box-sizing: border-box;
			}
		</style>
	</head>
	<body>
		<?php
		$url = esc_url($game->url);
		if($game->source == 'gamedistribution'){
			//GameDistributon new url
			$url .= '?gd_sdk_referrer_url='.get_permalink('full', $game->slug);
		} elseif($game->source == 'remote'){
			if(strpos($url, "https://html5.gamedistribution.com") === 0){
				// Add extra parameter for GameDistribution
				$url .= '?gd_sdk_referrer_url='.get_permalink('full', $game->slug);
			}
		} elseif($game->source == 'gamepix'){
			$url = get_game_url($game);
		}
		?>
		<iframe id="game-content" frameborder="0" allow="autoplay" allowfullscreen="" seamless="" scrolling="no" src="<?php echo $url ?>"></iframe>
	</body>
	</html>

	<?php
} else {
	require( ABSPATH . 'includes/page-404.php' );
}

?>