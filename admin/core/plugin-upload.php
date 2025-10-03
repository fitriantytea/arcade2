<?php
if(!has_admin_access()){
	die('x');
}

if(ADMIN_DEMO){
	die('z');
}

if(isset($_SESSION['message'])){
	show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
	unset($_SESSION['message']);
}

$page_state = 'default';
$plugin_dir = null;

$error_messages = [];
$success_messages = [];

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'upload_plugin') {
		if (isset($_FILES['plugin_file'])) {
			$page_state = 'uploaded';
			$temp_dir = 'tmp/tmp_plugin/';
			$target_file = $temp_dir . strtolower(str_replace(' ', '-', basename($_FILES["plugin_file"]["name"])));
			$upload_ok = 1;
			$warning_messages = [];
			$plugin_json = null;
			
			if (!file_exists('tmp')) {
				mkdir('tmp', 0755, true);
			}
			if (file_exists($temp_dir)) {
				delete_files($temp_dir); // Make sure to define this function
			}
			mkdir($temp_dir, 0755, true);
			
			$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
			if ($file_type != 'zip') {
				$upload_ok = 0;
				$error_messages[] = 'File format must be zip!';
			}
			
			if ($upload_ok) {
				if (move_uploaded_file($_FILES["plugin_file"]["tmp_name"], $target_file)) {
					$zip = new ZipArchive;
					if ($zip->open($target_file) === TRUE) {
						$zip->extractTo($temp_dir);
						$zip->close();

						// Assuming the plugin folder name is the same as the zip name without .zip
						$plugin_dir = strtolower(str_replace(' ', '-', pathinfo($_FILES["plugin_file"]["name"], PATHINFO_FILENAME)));
						$plugin_json_path = $temp_dir . $plugin_dir . '/info.json';
						
						if (!file_exists($plugin_json_path)) {
							$error_messages[] = 'Plugin info (info.json) doesn\'t exist';
						} else {
							$plugin_json = json_decode(file_get_contents($plugin_json_path), true);
							if(!isset($plugin_json['name']) || !isset($plugin_json['version']) || !isset($plugin_json['description']) || !isset($plugin_json['require_version']) || !isset($plugin_json['website'])){
								$error_messages[] = 'Invalid info.json format: missing required fields.';
							} else {
								if (version_compare(VERSION, $plugin_json['require_version'], '<')) {
									$error_messages[] = 'Plugin requires CMS version ' . $plugin_json['require_version'] . ' or higher.';
								}
							}
						}

						if (file_exists(ABSPATH . 'content/plugins/' . $plugin_dir)) {
							$warning_messages[] = 'Plugin folder for this plugin already exists.';
							$warning_messages[] = 'Existing plugin folder will be overridden.';

							$existingPluginData = json_decode(file_get_contents('../content/plugins/'.$plugin_dir.'/info.json'), true);
							if(version_compare($plugin_json['version'], $existingPluginData['version'], '<=')){
								$warning_messages[] = 'A plugin with the same or a higher version is already installed!';
							}
						}
					} else {
						$error_messages[] = 'Failed to open the zip file.';
					}
				} else {
					$error_messages[] = 'Failed to move uploaded file.';
				}
			}
		}
	} else if ($_POST['action'] == 'install_uploaded_plugin') {
		$page_state = 'installed';
		if (isset($_POST['plugin_dir'])) {
			$plugin_dir = basename($_POST['plugin_dir']); // Use basename to sanitize input
			$zip_file_path = 'tmp/tmp_plugin/' . $plugin_dir . '.zip'; // Ensure correct path
			$plugin_extract_dir = '../content/plugins/';

			if (file_exists($zip_file_path)) {
				$zip = new ZipArchive;
				if ($zip->open($zip_file_path) === TRUE) {
					// Ensure the destination directory exists
					if (!file_exists($plugin_extract_dir)) {
						mkdir($plugin_extract_dir, 0755, true);
					}

					$zip->extractTo($plugin_extract_dir);
					$zip->close();

					// Clean up the temporary zip file
					unlink($zip_file_path);
					$success_messages[] = 'Plugin uploaded and installed successfully!';
				} else {
					$error_messages[] = 'Failed to open the zip file.';
				}
			} else {
				$error_messages[] = 'Plugin file zip does not exist!';
			}
			if(file_exists('tmp/tmp_plugin/')){
				delete_files('tmp/tmp_plugin/');
			}
		} else {
			$error_messages[] = 'No plugin directory specified!';
		}
	}
}
?>
<div class="row">
	<div class="col-lg-8">
		<div class="section">
			<h5>Upload Plugin</h5>
			<div class="bs-callout bs-callout-warning">
				<p><b>Make sure the plugin you're uploading is from a trusted source!</b></p>
				<p>Plugins from unknown or unverified sources, including nulled or cracked plugins, can harm your site by stealing private or important data, spreading malware or viruses, and installing backdoors, among other risks.</p>
			</div>
			<?php

			if($page_state == 'uploaded'){
				// Handle errors and warnings
				if(count($error_messages)){
					foreach ($error_messages as $value) {
						show_alert($value, 'danger');
					}
				} else {
					if(count($warning_messages)){
						foreach ($warning_messages as $value) {
							show_alert($value, 'warning');
						}
					}
					if(!is_null($plugin_json)){
						echo '<h5>Plugin Information</h5>';
						echo '<pre>' . print_r($plugin_json, true) . '</pre>';
						?>
						<form method="post" enctype="multipart/form-data">
							<input type="hidden" name="action" value="install_uploaded_plugin">
							<input type="hidden" name="plugin_dir" value="<?php echo $plugin_dir ?>">
							<button type="submit" class="btn btn-primary"><?php _e('Install Plugin') ?></button>
						</form>
						<?php
					}
				}
			} else if($page_state == 'installed') {
				foreach ($error_messages as $value) {
					show_alert($value, 'danger');
				}
				foreach ($success_messages as $value) {
					show_alert($value, 'success');
				}
				echo '<a href="dashboard.php?viewpage=plugin"><< Back to Plugin Manager</a>';
			} else {

			?>
			<div class="plugin-upload-wrapper">
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="upload_plugin">
					<div class="mb-3">
						<label class="form-label" for="plugin_file"><?php _e('Upload Plugin') ?> (zip):</label>
						<input type="file" class="form-control" name="plugin_file" accept=".zip" required>
					</div>
					<button type="submit" class="btn btn-primary"><?php _e('Upload') ?></button>
				</form>
			</div>
			<?php

			}

			?>
		</div>
	</div>
</div>