<div class="section section-full">

<?php

$selected_tab = isset($_GET['slug']) ? $_GET['slug'] : 'upload';
$tabs = array(
	'upload'	=> 'Upload game',
	'fetch'		=> 'Fetch games',
	'remote'	=> 'Remote add',
	'json'		=> 'JSON Importer'
);

if(!check_purchase_code() && !ADMIN_DEMO){
	echo('<div class="bs-callout bs-callout-warning"><p>Please provide your <b>Item Purchase code</b>. You can submit or update your Purchase code on site settings.</p><p>To be able to add a game, you need to provide your Item Purchase code. <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank">Where to get Envato purchase code?</a></p></div>');
} else {
	?>
	<input type="hidden" name="p_code" value="<?php echo (ADMIN_DEMO ? 'holy-moly' : check_purchase_code()) ?>" id="p_code" />
	<ul class="nav nav-tabs custom-tab" role="tablist">
		<?php
		foreach ($tabs as $tab_key => $tab_value) {
			$active = ($tab_key === $selected_tab) ? 'active' : '';
			?>
			<li class="nav-item" role="presentation">
				<a class="nav-link <?php echo $active ?>" href="dashboard.php?viewpage=addgame&slug=<?php echo $tab_key ?>"><?php _e($tab_value) ?></a>
			</li>
			<?php
		}
		?>
	</ul>
	<!-- Tab panes -->
	<div class="general-wrapper">
		<div class="tab-content">
			<?php
			$selected_categories = []; //Used for showing last selected categories
			if(isset($_SESSION['category'])){
				if(is_array($_SESSION['category'])){
					$selected_categories = (array)$_SESSION['category'];
				} else {
					$selected_categories = commas_to_array($_SESSION['category']);
				}
			}
			if(isset($_GET['status'])){
				echo '<div class="mb-4"></div>';
				if($_GET['status'] == 'added'){
					show_alert('Game added!', 'success');
				} elseif($_GET['status'] == 'exist'){
					show_alert('Game already exist!', 'warning');
				} elseif($_GET['status'] == 'error'){
					$error = json_decode($_GET['error-data']);
					foreach ($error as $value) {
						show_alert($value, 'warning');
					}
				}
			}
			if($selected_tab === 'upload'){
				include 'core/addgame-upload.php';
			} else if($selected_tab === 'fetch'){
				include 'core/addgame-fetch.php';
			} else if($selected_tab === 'remote'){
				include 'core/addgame-remote.php';
			} else if($selected_tab === 'json'){
				include 'core/addgame-json.php';
			}
			?>
		</div>
	</div>
<?php } ?>
</div>