<?php
/**
 * Do not modify this file or code!
 * 
 * File: theme-functions.php
 * 
 * This script is used to define a collection of functions that are specifically designed 
 * for theme related functionality. This can include things like rendering HTML components,
 * handling pagination, or setting up multilingual support.
 * 
 * The functions defined in this file are designed to be used in various parts of the theme 
 * templates or plugins, offering a way to abstract common functionality and promote reusability.
 * 
 * Please note that this file may be updated frequently during CMS updates. 
 * It is recommended NOT to modify this file directly, as your changes may be 
 * overwritten during an update.
 */

// Global array to store all the hooks
$hooks = [];
// Global array to store all the filters
$filters = [];
// Global array to store all the shortcodes
$shortcodes = [];

function add_to_hook($hook_name, $callback) {
	// Function to add callbacks to hooks
	// Usage : add_to_hook('head', 'your_function_name') or add_to_hook('head', function() { echo 'test';});
	global $hooks;
	if (!isset($hooks[$hook_name])) {
		$hooks[$hook_name] = [];
	}
	$hooks[$hook_name][] = $callback;
}

function run_hook($hook_name) {
	// Function to execute the hooks
	global $hooks;
	if (isset($hooks[$hook_name])) {
		foreach ($hooks[$hook_name] as $callback) {
			call_user_func($callback);
		}
	}
}

function the_head($position) {
	// Run hooks based on the position parameter
	// Executed inside head tag
	global $base_taxonomy;
	if ($position === 'top') {
		if($base_taxonomy === '404'){
			// Page 404
			// Add noindex meta
			add_to_hook('head_top', function() {
				echo '<meta name="robots" content="noindex">'.PHP_EOL;
			});
		} else {
			if(get_setting_value('lang_code_in_url') && PRETTY_URL){ // Activate multilingual
				add_to_hook('head_top', function() {
					// Add alternate link tag
					global $lang_code;
					$language_ids = [];
					if(get_setting_value('allow_slug_translation')){
						// Skip this, prevent potential bug
						return;
					}
					if (isset($_SESSION['language_ids'])) {
						$language_ids = $_SESSION['language_ids']; // Cached
					} else {
						if (file_exists(ABSPATH . 'locales/public/')) {
							$files = scandir(ABSPATH . 'locales/public/');
							foreach ($files as $file) {
								if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
									$file_name_without_extension = pathinfo($file, PATHINFO_FILENAME);
									if (strlen($file_name_without_extension) >= 1 && strlen($file_name_without_extension) <= 3) {
										$language_ids[] = $file_name_without_extension;
									}
								}
							}
							$_SESSION['language_ids'] = $language_ids;
						} else if (file_exists(ABSPATH . TEMPLATE_PATH . '/locales/')) { // Backward compatibility
							$files = scandir(ABSPATH . TEMPLATE_PATH . '/locales/');
							foreach ($files as $file) {
								if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
									$file_name_without_extension = pathinfo($file, PATHINFO_FILENAME);
									if (strlen($file_name_without_extension) >= 1 && strlen($file_name_without_extension) <= 3) {
										$language_ids[] = $file_name_without_extension;
									}
								}
							}
							$_SESSION['language_ids'] = $language_ids;
						}
					}
					if(get_setting_value('disable_en_language') && count($language_ids) > 0){
						//
					} else {
						if (!in_array('en', $language_ids)) {
							$language_ids[] = 'en';
						}
					}
					if (!isset($_SESSION['language_ids'])) {
						$_SESSION['language_ids'] = $language_ids; // Cached
					}
					foreach ($language_ids as $lang_id) {
						$alternate_url = DOMAIN . $lang_id . substr($_SERVER['REQUEST_URI'], strlen($lang_code)+1);
						echo '<link rel="alternate" hreflang="'.$lang_id.'" href="'.$alternate_url.'">'.PHP_EOL;
					}
				});
			}
		}
		run_hook('head_top');
	} elseif ($position === 'bottom') {
		run_hook('head_bottom');
	}
}

function add_filter($tag, $function_to_add) {
	global $filters;
	/*
	Sample usage:
	.
	add_filter('filter_meta_description', function($desc) {
		return "Modified: " . $desc;
	});
	*/
	if (!isset($filters[$tag])) {
		$filters[$tag] = [];
	}
	$filters[$tag][] = $function_to_add;
}

add_filter('meta_description', function($data) {
	global $base_taxonomy;
	if($base_taxonomy === 'game'){ // Is single game page
		global $game;
		if($game){
			$value = $game->getExtraField('meta_description');
			if($value){ // Have meta_description value
				return $value;
			}
		}
	} else if($base_taxonomy === 'post'){
		global $post;
		if($post){
			$value = $post->getExtraField('meta_description');
			if($value){ // Have meta_description value
				return $value;
			}
		}
	}
	return $data;
});

function apply_filters($tag, $value) {
	global $filters;
	/*
	Sample usage:
	.
	$meta_description = apply_filters('filter_meta_description', $meta_description);
	*/
	if (!isset($filters[$tag])) {
		return $value;
	}
	foreach ($filters[$tag] as $func) {
		$value = call_user_func($func, $value);
	}
	return $value;
}

function add_shortcode($tag, $callback) {
	// Add a new shortcode
	/*
	Sample usage:
	.
	add_shortcode('bold', function($content) {
		return '<strong>' . $content . '</strong>';
	});
	.
	add_shortcode('italic', function($content, $atts = []) {
		return '<em>' . $content . '</em>';
	});
	*/
	global $shortcodes;
	$shortcodes[$tag] = $callback;
}

function run_shortcode($text) {
	global $shortcodes;
	foreach ($shortcodes as $tag => $callback) {
		if (is_callable($callback)) {
			// Match both self-closing and enclosing shortcodes
			$text = preg_replace_callback(
				"/\[$tag(.*?)(\/)?\](?:(.*?)\[\/$tag\])?/s",
				function($matches) use ($callback) {
					$params = [];
					if (preg_match_all('/\s*([\w-]+)="([^"]*)"/', $matches[1], $attr)) {
						$params = array_combine($attr[1], $attr[2]);
					}
					return call_user_func($callback, $matches[3] ?? '', $params);
				},
				$text
			);
		}
	}
	return $text;
}

function render_pagination($total_page, $cur_page = 1, $display_limit = 8, $pageType = 'category', $slug = '', $htmlOptions = []) {
	$defaults = [
		'container' => 'ul',
		'container_class' => 'pagination justify-content-center',
		'item' => 'li',
		'item_class' => 'page-item',
		'link' => 'a',
		'link_class' => 'page-link',
		'disabled_class' => 'disabled'
	];

	$htmlOptions = array_merge($defaults, $htmlOptions);
	
	$paginationHTML = "<{$htmlOptions['container']} class=\"{$htmlOptions['container_class']}\">";

	if($total_page) {
		$start = max(0, $cur_page - ceil($display_limit / 2));
		$end = min($start + $display_limit, $total_page);

		if ($start > 0) {
			$paginationHTML .= "<{$htmlOptions['item']} class=\"{$htmlOptions['item_class']}\"><{$htmlOptions['link']} class=\"{$htmlOptions['link_class']}\" href=\"". get_permalink($pageType, $slug) ."\">1</{$htmlOptions['link']}></{$htmlOptions['item']}>";
			$paginationHTML .= "<{$htmlOptions['item']} class=\"{$htmlOptions['item_class']} {$htmlOptions['disabled_class']}\"><span class=\"{$htmlOptions['link_class']}\">...</span></{$htmlOptions['item']}>";
		}

		for ($i = $start; $i < $end; $i++) {
			$disabled = $cur_page == ($i + 1) ? $htmlOptions['disabled_class'] : '';
			$page_number = $i + 1;
			$page_url = $page_number > 1 ? get_permalink($pageType, $slug, array('page' => $page_number)) : get_permalink($pageType, $slug);
			$paginationHTML .= "<{$htmlOptions['item']} class=\"{$htmlOptions['item_class']} {$disabled}\"><{$htmlOptions['link']} class=\"{$htmlOptions['link_class']}\" href=\"{$page_url}\">". ($page_number) ."</{$htmlOptions['link']}></{$htmlOptions['item']}>";
		}

		if ($end < $total_page) {
			$paginationHTML .= "<{$htmlOptions['item']} class=\"{$htmlOptions['item_class']} {$htmlOptions['disabled_class']}\"><span class=\"{$htmlOptions['link_class']}\">...</span></{$htmlOptions['item']}>";
			$paginationHTML .= "<{$htmlOptions['item']} class=\"{$htmlOptions['item_class']}\"><{$htmlOptions['link']} class=\"{$htmlOptions['link_class']}\" href=\"". get_permalink($pageType, $slug, array('page' => $total_page)) ."\">{$total_page}</{$htmlOptions['link']}></{$htmlOptions['item']}>";
		}
	}
	$paginationHTML .= "</{$htmlOptions['container']}>";
	echo $paginationHTML;
}

function the_html_attrs() {
	/*
	* This function is used to print HTML attributes inside the <html> tag for multilingual support 
	* and Right-to-Left (RTL) language handling. It considers the language code (ISO 639-1) for 
	* setting the "lang" attribute and also checks if the language is RTL to set the "dir" attribute.
	*/
	global $lang_code;
	// List of RTL language codes.
	$rtl_langs = ['ar', 'fa', 'ur', 'he', 'iw', 'yi', 'ku', 'ps', 'sd', 'ug', 'dv'];
	// Check if current language is RTL.
	$dir = in_array($lang_code, $rtl_langs) ? 'rtl' : 'ltr';
	if(get_setting_value('disable_rtl')){
		$dir = 'ltr';
	}
	// Print the lang and dir attributes.
	echo "lang=\"{$lang_code}\" dir=\"{$dir}\"";
}

function the_canonical_link() {
	// Check if the custom override function exists
	if (function_exists('the_custom_canonical_link')) {
		the_custom_canonical_link();
		return;
	}
	$canonical_url = get_canonical_url();
	if($canonical_url && $canonical_url != ''){
		echo "<link rel=\"canonical\" href=\"{$canonical_url}\" />".PHP_EOL;
	}
}

function get_canonical_url(){
	global $url_params;
	global $base_taxonomy;
	$allowed_taxonomy = ['homepage', 'game', 'category', 'search', 'post', 'page', 'tag'];
	if($base_taxonomy != 'homepage' && $base_taxonomy != 'post'){
		if(!PRETTY_URL || count($url_params) <= 1 || count($url_params) > 3) return;
	}
	if(!in_array($base_taxonomy, $allowed_taxonomy)){
		return;
	}
	$canonical_url = '';
	if($base_taxonomy == 'homepage'){
		$canonical_url = DOMAIN;
		if(get_setting_value('lang_code_in_url')){
			global $lang_code;
			$canonical_url .= $lang_code;
		}
		if(get_setting_value('trailing_slash') && substr($canonical_url, -1) != '/'){
			$canonical_url .= '/';
		}
	} else if($base_taxonomy == 'post'){
		$canonical_url = get_permalink($url_params[0]);
	} else {
		if(get_setting_value('allow_slug_translation')){
			if($base_taxonomy == 'category' || $base_taxonomy == 'tag'){
				if(_t('slug:'.$url_params[1]) != 'slug:'.$url_params[1]){
					$url_params[1] = _t('slug:'.$url_params[1]);
				}
			}
		}
		$canonical_url = get_permalink($url_params[0], $url_params[1]);
		// If there is a pagination parameter, append it to the URL
        if (isset($url_params[2]) && is_numeric($url_params[2])) {
            $canonical_url .= '/' . intval($url_params[2]);
        }
	}
	return $canonical_url;
}

function fetch_games_by_type($type, $amount=12, $page=0, $count=true){
	// Fetches a list of games based on different criteria: 'new', 'random', 'popular', 'likes', and 'trending'.
	$data = [];
	if ($type == 'trending') {
		$conn = open_connection();
		$date = new \DateTime('now');
		$date->sub(new DateInterval('P7D'));
		$sql = "SELECT * FROM trends WHERE created >= '{$date->format('Y-m-d')}'";
		$st = $conn->prepare($sql);
		$st->execute();
		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		$list = array();
		if(count($row)){
			foreach ($row as $item) {
				if(isset($list[$item['slug']])){
					$list[$item['slug']] += (int)$item['views'];
				} else {
					$list[$item['slug']] = (int)$item['views'];
				}
			}
			arsort($list);
			$i = 0;
			foreach ($list as $slug => $views) {
				if($i < $amount){
					$game = Game::getBySlug($slug);
					if($game){
						$data[] = $game;
					}
				}
				$i++;
			}
		}
		return (array(
			"results" => $data,
			"totalRows" => count($list),
			"totalPages" => 1
		));
	} else {
		switch($type) {
			case 'new':
				$order_by = 'id DESC';
				break;
			case 'random':
				$order_by = 'RAND()';
				break;
			case 'popular':
				$order_by = 'views DESC';
				break;
			case 'likes':
				$order_by = 'upvote DESC';
				break;
			default:
				throw new InvalidArgumentException('Invalid type provided');
		}
		return Game::getList($amount, $order_by, $page, $count);
	}
}

function fetch_collection($name, $amount = 12){
	// Fetches a game collection based on a specified name.
	$data = Collection::getListByCollection( $name, $amount );
	return $data;
}

function fetch_games_by_category($cat_name, $amount, $page = 0) {
	// Fetches a list of games from a specific category.
	$cat_id = Category::getIdByName($cat_name);
	$data = Category::getListByCategory($cat_id, $amount, $page);
	return $data;
}

function fetch_games_by_tag($tag_name, $amount, $offset = 0, $count = false) {
	// Fetches a list of games from a specific tag.
	$data = Game::getListByTag($tag_name, $amount, 'id DESC', $offset, $count);
	return $data;
}

function fetch_similar_games($game, $amount, $page = 0, $random = true) {
	// This function is used to get the list of similar games based on current $game categories
	// Mostly used for single-game page "Similar Games" section
	// $category_list = $game->getCategoryList(); // Excluding hidden categories
	// $ids = array_map(function($category) {
	// 	return $category['id'];
	// }, $category_list);
	// $data = Category::getListByCategories($ids, $amount, $page, $random);
	// return $data;
	return $game->getSimilarGames($amount);
}

function fetch_all_categories($show_hidden_category = false, $show_empty_category = false){
	// Get the list of all categories
	$data = Category::getList();
	$results = $data['results'];
	foreach ($results as $key => $category) {
		if(!$show_hidden_category && $category->priority < 0){
			unset($results[$key]);
			continue;
		}
		if(!$show_empty_category && Category::getCategoryCount($category->id) == 0){
			unset($results[$key]);
			continue;
		}
	}
	return $results;
}

function get_page_title($title_template = 'default'){
	// Check if the custom override function exists
	if (function_exists('get_custom_page_title')) {
		$custom_title = get_custom_page_title();
		if($custom_title != 'default'){
			return htmlspecialchars($custom_title);
		}
	}
	global $base_taxonomy;
	$content_title = null;
	switch($base_taxonomy){
		case 'game':
			global $game;
			$content_title = $game->title;
			break;
		case 'full':
			global $game;
			$content_title = $game->title;
			break;
		case 'splash':
			global $game;
			$content_title = $game->title;
			break;
		case 'category':
			global $category;
			$content_title = _t($category->name);
			break;
		case 'tag':
			global $tag_name;
			$content_title = _t($tag_name);
			break;
		case 'user':
			global $url_params;
			$content_title = $url_params[1];
			break;
		case 'page':
			global $page;
			$content_title = $page->title;
			break;
		case 'post':
			global $post;
			if(isset($post)){
				$content_title = $post->title;
			} else {
				$content_title = _t('Posts');
			}
			break;
		case '404':
			$content_title = '404';
			break;
		case 'search':
			$content_title = _t('Search %a Games', $_GET['slug']);
			break;
		default:
			global $page_title;
			if(isset($page_title)){
				$content_title = $page_title;
			} else {
				$content_title = get_site_info('title');
			}
	}
	if($title_template == 'default'){
		if($base_taxonomy == 'user'){
			return htmlspecialchars($content_title);
		} else {
			return htmlspecialchars($content_title . ' | ' . get_site_info('description'));
		}
	} else {
		$title_template = str_replace('{content_title}', $content_title, $title_template);
		$title_template = str_replace('{site_description}', get_site_info('description'), $title_template);
		$title_template = str_replace('{site_title}', get_site_info('title'), $title_template);
		return htmlspecialchars($title_template);
	}
}

function get_current_user_data(){
	// Get current logged-in visitor data or info
	// return null if visitor is not logged-in user
	if(is_login()){
		global $login_user;
		return $login_user;
	} else {
		return null;
	}
}

function get_theme_header(){
	global $page_title;
	global $meta_description;
	global $login_user;
	if(file_exists(TEMPLATE_PATH . '/header.php')){
		include TEMPLATE_PATH . '/header.php';
	} else if(file_exists(TEMPLATE_PATH . '/includes/header.php')){
		include TEMPLATE_PATH . '/includes/header.php';
	}
}

function get_theme_sidebar(){
	global $page_title;
	global $meta_description;
	global $login_user;
	if(file_exists(TEMPLATE_PATH . '/sidebar.php')){
		include TEMPLATE_PATH . '/sidebar.php';
	} else if(file_exists(TEMPLATE_PATH . '/includes/sidebar.php')){
		include TEMPLATE_PATH . '/includes/sidebar.php';
	} else if(file_exists(TEMPLATE_PATH . '/parts/sidebar.php')){
		include TEMPLATE_PATH . '/parts/sidebar.php';
	}
}

function get_theme_footer(){
	global $page_title;
	global $meta_description;
	global $login_user;
	if(file_exists(TEMPLATE_PATH . '/footer.php')){
		include TEMPLATE_PATH . '/footer.php';
	} else if(file_exists(TEMPLATE_PATH . '/includes/footer.php')){
		include TEMPLATE_PATH . '/includes/footer.php';
	}
}

function can_show_leaderboard(){
	// Check if current $game can show leaderboard or not by checking game source
	// return true if current game is self-upload/hosted
	global $game;
	if(isset($game)){
		if($game->source == 'self'){
			return true;
		}
	}
	return false;
}

function render_game_comments($game_id){
	if(get_setting_value('comments')){
		if (function_exists('custom_render_game_comments')) {
			custom_render_game_comments($game_id);
			return;
		} else {
			?>
			<div id="tpl-comment-section" data-id="<?php echo esc_int($game_id) ?>">
				<?php if(is_login()){ ?>
				<div id="comment-form">
					<div class="comment-profile-avatar">
						<img src="<?php echo get_user_avatar() ?>">
					</div>
					<div class="comment-form-wrapper" id="tpl-comment-form">
						<div class="tpl-alert-tooshort" style="display: none;"><?php _e('Your comment is too short. Please enter at least {{min}} characters.') ?></div>
						<textarea class="form-control tpl-comment-input" rows="3" placeholder="Enter your comment here..."></textarea>
						<div class="post-comment-btn-wrapper">
							<button class="btn btn-primary tpl-post-comment-btn btn-sm"><?php _e('Post comment') ?></button>
						</div>
					</div>
				</div>
				<?php } else { ?>
					<div class="comment-require-login-wrapper">
						<div class="comment-profile-avatar">
							<img src="<?php echo DOMAIN . 'images/default_profile.png' ?>">
						</div>
						<div class="comment-alert">
							<?php _e('You must log in to write a comment.') ?>
						</div>
					</div>
				<?php } ?>
				<div id="tpl-comment-list">
				</div>
				<!-- Comment template -->
				<div id="tpl-comment-template" style="display:none;">
					<!-- User comment template -->
					<div class="tpl-user-comment" data-id="{{comment_id}}">
						<div class="user-comment-wrapper">
							<div class="user-comment-avatar">
								<img class="tpl-user-comment-avatar" src="{{profile_picture_url}}" alt="User Avatar">
							</div>
							<div class="comment-content">
								<div class="tpl-comment-author">{{fullname}}</div>
								<div class="tpl-comment-timestamp">{{created}}</div>
								<div class="tpl-comment-text">{{content}}</div>
								<div class="comment-actions">
									<div class="comment-action-left">
										<div class="reply-wrapper">
											<a href="#" onclick="return false;" class="tpl-btn-show-replies" data-id="{{comment_id}}"><i class="fa fa-comment-o" aria-hidden="true"></i> <?php _e('Show replies') ?></a>
											<a href="#" onclick="return false;" class="tpl-btn-hide-replies" data-id="{{comment_id}}"><i class="fa fa-comment-o" aria-hidden="true"></i> <?php _e('Hide replies') ?></a>
										</div>
									</div>
									<?php if(is_login()){ ?>
									<div class="comment-action-right">
										<a href="#" class="tpl-comment-reply" data-id="{{comment_id}}">
											<i class="fa fa-reply" aria-hidden="true"></i> Reply
										</a>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="tpl-reply-form-wrapper"></div>
						<div class="tpl-comment-children"></div>
					</div>
					<!-- Reply form template -->
					<div class="tpl-reply-form">
						<div class="comment-reply-wrapper">
							<textarea class="form-control tpl-reply-input" placeholder="Your reply..."></textarea>
							<div class="reply-action-buttons">
								<button class="btn btn-sm tpl-btn-cancel-reply" data-id="{{comment_id}}"><?php _e('Cancel') ?></button>
								<button class="btn btn-primary btn-sm tpl-btn-send-reply" data-id="{{comment_id}}"><?php _e('Reply') ?></button>
							</div>
						</div>
					</div>
				</div>
				<div id="tpl-btn-load-more-comments" class="btn" style="display: none;"><?php _e('Load more comments') ?> <i class="fa fa-chevron-down" aria-hidden="true"></i></div>
			</div>
			<?php
		}
	}
}

function get_site_info($type){
	if($type == 'title'){
		if(_t('_site_title') !== '_site_title'){ // Have a translation
			return _t('_site_title');
		} else {
			return SITE_TITLE;
		}
	} else if($type == 'description'){
		if(_t('_site_description') !== '_site_description'){ // Have a translation
			return _t('_site_description');
		} else {
			return SITE_DESCRIPTION;
		}
	} else if($type == 'meta_description'){
		if(_t('_meta_description') !== '_meta_description'){ // Have a translation
			return _t('_meta_description');
		} else {
			return META_DESCRIPTION;
		}
	}
}

function fetch_all_tags($sort = 'random', $limit = 100){
	// $sort = name, usage, random
	return get_tags($sort, $limit);
}

function is_home(){
	global $base_taxonomy;
	if($base_taxonomy == 'homepage'){
		return true;
	}
	return false;
}

function is_game(){
	global $base_taxonomy;
	if($base_taxonomy == 'game'){
		global $game;
		if(isset($game)){
			return true;
		}
	}
	return false;
}

function is_search(){
	global $base_taxonomy;
	if($base_taxonomy == 'search'){
		return true;
	}
	return false;
}

function is_category(){
	global $base_taxonomy;
	if($base_taxonomy == 'category'){
		return true;
	}
	return false;
}

function is_page(){
	global $base_taxonomy;
	if($base_taxonomy == 'page'){
		global $page;
		if(isset($page)){
			return true;
		}
	}
	return false;
}

function is_post(){
	global $base_taxonomy;
	if($base_taxonomy == 'post'){
		global $post;
		if(isset($post)){
			return true;
		}
	}
	return false;
}

function is_tag(){
	global $base_taxonomy;
	if($base_taxonomy == 'tag'){
		global $tag;
		if(isset($tag)){
			return true;
		}
	}
	return false;
}

function home_url( $path = '' ){
	return rtrim(DOMAIN, '/') . $path;
}

function render_nav_menu($name = 'top_nav', $args = array()){
	$defaults = array(
		'container'			=> '',
		'container_id'		=> '',
		'container_class'	=> '',
		'no_ul'				=> false,
		'ul_id'				=> '',
		'ul_class'			=> '',
		'li_id'				=> '',
		'li_class'			=> 'nav-item',
		'li_class_parent'	=> 'dropdown',
		'a_class'			=> 'nav-link',
		'a_class_parent'	=> 'dropdown-toggle',
		'after_parent'		=> '',
		'children'			=> array(),
	);
 
	$args = merge_args( $args, $defaults );

	$array_menu = nav_menu_array($name);
	if(count($array_menu)){
		if($args['container'] != ''){
			echo '<'.$args['container'];
			echo !empty($args['container_id']) ? ' id="'.$args['container_id'].'"' : '';
			echo !empty($args['container_class']) ? ' class="'.$args['container_class'].'"' : '';
			echo '>';
		}
		if(!$args['no_ul']){
			echo '<ul';
			echo !empty($args['ul_id']) ? ' id="'.$args['ul_id'].'"' : '';
			echo !empty($args['ul_class']) ? ' class="'.$args['ul_class'].'"' : '';
			echo '>';
		}
		foreach($array_menu as $menu){
			$parent_class = '';
			if(isset($menu['children'])){
				$parent_class = !empty($args['li_class_parent']) ? $args['li_class_parent'] : '';
			}
			echo '<li';
			echo !empty($args['li_id']) ? ' id="'.$args['li_id'].'"' : '';
			echo !empty($args['li_class']) ? ' class="'.$args['li_class'].' '.$parent_class.'"' : '';
			echo '>';
			if(isset($menu['children'])){
				$menu['url'] = '#';
			}
			$a_class_parent = '';
			if(isset($menu['children'])){
				$a_class_parent = $args['a_class_parent'];
				$menu['url'] = '#';
			}
			echo '<a class="'.$args['a_class'].' '.$a_class_parent.'" href="'.$menu['url'].'"';
			if(isset($menu['children'])){
				echo ' data-bs-toggle="dropdown"';
			}
			echo '>';
			echo _t($menu['label']);
			if(isset($menu['children'])){
				echo $args['after_parent'];
			}
			echo '</a>';
			if(isset($menu['children'])){
				render_nav_children($menu['children'], $args['children']);
			}
			echo '</li>';
		}
		if(!$args['no_ul']){
			echo '</ul>';
		}
		if($args['container'] != ''){
			echo '</'.$args['container'].'>';
		}
	}
}

function render_nav_children($array_menu, $args){
    $defaults = array(
        'no_ul'             => false,
        'ul_id'             => '',
        'ul_class'          => 'dropdown-menu',
        'li_id'             => '',
        'li_class'          => 'nav-item-child',
        'li_class_parent'	=> 'dropdown-submenu',
        'a_class'           => 'nav-link-child',
		'a_class_parent'	=> 'dropdown-toggle',
        'submenu_ul_class'  => 'submenu dropdown-menu' // class for sub-menu <ul>
    );
 
    $args = merge_args($args, $defaults);

    if (count($array_menu)) {
        if (!$args['no_ul']) {
            echo '<ul role="menu" ';
            echo !empty($args['ul_id']) ? ' id="' . $args['ul_id'] . '"' : '';
            echo !empty($args['ul_class']) ? ' class="' . $args['ul_class'] . '"' : '';
            echo '>';
        }
        foreach ($array_menu as $menu) {
        	$is_parent = false;
        	if(!empty($menu['children'])){
        		$is_parent =  true;
        	}
            echo '<li';
            echo !empty($args['li_id']) ? ' id="' . $args['li_id'] . '"' : '';
            echo !empty($args['li_class']) ? ' class="' . $args['li_class'] . ' ' . ($is_parent ? $args['li_class_parent'] : '') . '"' : '';
            echo '>';
            $a_class_parent = '';
            if ($is_parent) {
            	// Is parent
            	$menu['url'] = '#';
            	$a_class_parent = $args['a_class_parent'];
            }
            echo '<a class="' . $args['a_class'] . ' ' . $a_class_parent.'" href="' . $menu['url'] . '">';
            echo _t($menu['label']);
            echo '</a>';
            if ($is_parent) {
                // Recursive call to render children
                $child_args = $args;
                $child_args['ul_class'] = $args['submenu_ul_class']; // Change class for sub-menu
                render_nav_children($menu['children'], $child_args);
            }
            echo '</li>';
        }
        if (!$args['no_ul']) {
            echo '</ul>';
        }
    }
}

function get_content_title_translation($content_type, $content_id, $original_title){
	// This function can only be used if "lang code in url" is activated
	// This function is used to translate content title, especialy games in content loop or list.
	// This function is marked as slow-execution script that may can slow down your site load
	// Reason why it slow : each call do database iteration to fetch the translated title
	global $lang_url_enabled;
	global $language_file_exist;
	global $lang_code;
	if(PRETTY_URL && $lang_url_enabled && $language_file_exist){
		if($lang_code != 'en'){
			$translated_title = get_content_translation($content_type, $content_id, $lang_code, 'title');
			if($translated_title){
				return esc_string($translated_title);
			}
		}
	}
	return esc_string($original_title);
}

function get_slug_translation($slug){
	$translated_slug = _t('slug:'.$slug);
	if($translated_slug == 'slug:'.$slug){
		// No translation, return original
		return $slug;
	} else {
		return $translated_slug;
	}
}

?>