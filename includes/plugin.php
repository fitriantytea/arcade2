<?php

defined('ABSPATH') or die('abcd');

define('PLUGIN_PATH', 'content/plugins/');

$plugin_footer = [];
$plugin_header = [];
$plugin_admin_hooks = [];
$plugin_list = get_plugin_list();

function get_plugin_list(){
    global $plugin_footer;
    global $plugin_header;
    global $plugin_admin_hooks;
    $list = [];
    $dirs = scan_folder( PLUGIN_PATH );
    foreach ($dirs as $dir) {
        $info = get_plugin_info($dir);
        if($info){
            array_push($list, $info);
            // Only add files if plugin is active (no underscore prefix)
            if(substr($info['dir_name'], 0, 1) != '_'){
                if(file_exists($info['path'].'/footer.php')){
                    $plugin_footer[] = ABSPATH.PLUGIN_PATH.$info['dir_name'].'/footer.php';
                }
                if(file_exists($info['path'].'/header.php')){
                    $plugin_header[] = ABSPATH.PLUGIN_PATH.$info['dir_name'].'/header.php';
                }
                // Add admin-hook.php with active check
				if(file_exists($info['path'].'/admin-hook.php')){
					$plugin_admin_hooks[] = ABSPATH.PLUGIN_PATH.$info['dir_name'].'/admin-hook.php';
				}
            }
        }
    }
    return $list;
}

function get_plugin_info($name){
	$plugin_dir = ABSPATH . PLUGIN_PATH . $name;
	$json_path = $plugin_dir . '/info.json';

	if(file_exists($json_path)){
		$array = json_decode(file_get_contents($json_path), true);
		if(isset($array['name']) && isset($array['version']) && isset($array['author']) && isset($array['description']) && isset($array['require_version']) && isset($array['tested_version']) && isset($array['type']) && isset($array['target'])){
			$array['path'] = $plugin_dir;
			$array['dir_name'] = $name;
			return $array;
		}
		return false;
	}
	return false;
}

function is_plugin_exist($name){
	// $name = plugin-name
	global $plugin_list;

	foreach ($plugin_list as $plugin) {
		if($plugin['dir_name'] == $name){
			return true;
		}
	}
	return false;
}

function load_plugins($type){
	global $plugin_list;
	foreach ($plugin_list as $plugin) {
		if($plugin['target'] == $type){
			if(substr($plugin['dir_name'], 0, 1) != '_'){
				if(file_exists($plugin['path'] . '/public.php')){
					require_once($plugin['path'] . '/public.php');
				} else if(file_exists($plugin['path'].'/main.php')) {
					// since v1.7.8 main.php is deprecated, use public.php instead
					// this used for backward compatibility
					require_once($plugin['path'] . '/main.php');
				}
			}
		}
	}
}

function load_plugin_headers(){
	// Inject plugin header.php files into the theme header (if the theme calls load_plugin_headers()).
	global $plugin_header;
	if(count($plugin_header)){
		foreach ($plugin_header as $hd) {
			include_once $hd;
		}
	}
}

function load_plugin_footers(){
	// Inject plugin footer.php files into the theme footer (if the theme calls load_plugin_footers()).
	global $plugin_footer;
	if(count($plugin_footer)){
		foreach ($plugin_footer as $ft) {
			include_once $ft;
		}
	}
}

// Add new function to load admin hooks
function load_admin_hooks(){
    global $plugin_admin_hooks;
    if(count($plugin_admin_hooks)){
        foreach ($plugin_admin_hooks as $hook_file) {
            require_once $hook_file;
        }
    }
}

// Add this function to check if plugin has admin hooks
function has_admin_hooks($plugin_dir_name){
    global $plugin_admin_hooks;
    foreach ($plugin_admin_hooks as $hook_file) {
        if (strpos($hook_file, $plugin_dir_name.'/admin-hook.php') !== false) {
            return true;
        }
    }
    return false;
}

?>