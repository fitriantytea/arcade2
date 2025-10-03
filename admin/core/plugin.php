<?php

if(ADMIN_DEMO){
	echo('Restricted for "DEMO" mode.');
	return;
}

if(isset($_SESSION['message'])){
	show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
	unset($_SESSION['message']);
}

if(isset($_GET['name'])){
	$_GET['name'] = esc_slug($_GET['name']);
	if(is_plugin_exist($_GET['name'])){
		$plugin = get_plugin_info($_GET['name']);
		echo '<h4 class="plugin-title">';
		echo $plugin['name'];
		if(isset($plugin['documentation'])){
			if (filter_var($plugin['documentation'], FILTER_VALIDATE_URL)) {
				echo '<a href="'.$plugin['documentation'].'" target="_blank" class="tooltip-doc-plugin" data-bs-toggle="tooltip" data-bs-placement="left" title="'._t('Click here to visit plugin manual or documentation.').'"><i class="fas fa-question"></i></a>';
			}
		}
		echo '</h4>';
		if(file_exists($plugin['path'] . '/admin-page.php')){
			require_once($plugin['path'] . '/admin-page.php');
		} else {
			// since v1.7.8 page.php is deprecated, use admin-page.php instead
			// this used for backward compatibility
			require_once($plugin['path'] . '/page.php');
		}
	} else {
		echo('<div class="section">');
		_e('Plugin %a is missing or removed.', $_GET['name']);
		echo('</div>');
	}
} else {
	if(isset($_GET['slug']) && $_GET['slug'] == 'upload'){
		include 'core/plugin-upload.php';
	} else {
		if(isset($_GET['status'])){
			if($_GET['status'] == 'success'){
				show_alert(isset($_GET['info']) ? $_GET['info'] : 'Plugin successfully installed!', 'success');
			} elseif($_GET['status'] == 'warning'){
				show_alert(isset($_GET['info']) ? $_GET['info'] : 'Failed to install!', 'warning');
			} elseif($_GET['status'] == 'error'){
				show_alert(isset($_GET['info']) ? $_GET['info'] : 'Error!', 'danger');
			}
		}

		?>
		<div id="action-alert" style="display: none;">
			<?php show_alert('Plugin updated!', 'success') ?>
		</div>
		<div class="row">
			<div class="col-lg-8">
				<div class="section section-full">
					<?php

					if(count($plugin_list) > 0){ ?>
						<div class="table-responsive">
							<table class="table custom-table">
								<thead>
									<tr>
										<th>#</th>
										<th><?php _e('Plugin') ?></th>
										<th><?php _e('Description') ?></th>
										<th><?php _e('Action') ?></th>
									</tr>
								</thead>
								<tbody class="plugin-list">
									<?php
									
									$index = 0;
									foreach ($plugin_list as $plugin) {
										$index++;
										$is_active = substr($plugin['dir_name'], 0, 1) == '_' ? false : true;
										$plugin_class = $is_active ? 'plugin-active' : 'plugin-inactive';
										$has_plugin_update = false;
										//
										if(!is_null($available_plugin_updates) && count($available_plugin_updates)){
											if(in_array($plugin['dir_name'], $available_plugin_updates)){
												$plugin_class .= ' plugin-update-available';
												$has_plugin_update = true;
											}
										}
										?>
										<tr class='<?php echo $plugin_class ?>'>
											<th scope="row"><?php echo $index ?></th>
											<td>
												<strong>
													<?php echo $plugin['name'] ?>
													<?php
													if($has_plugin_update){
														echo '<i class="plugin-update-icon text-success fas fa-exclamation-circle"></i>';
													}
													?>
												</strong>
												<br>
												Version <?php echo $plugin['version'] ?> | By <a href="<?php echo $plugin['website'] ?>" target="_blank"><?php echo $plugin['author'] ?></a>
											</td>
											<td><?php echo $plugin['description'] ?></td>
											<td><?php if($is_active) {
												echo('<a href="#" id="'.$plugin['dir_name'].'" class="deactivate-plugin">'._t('Deactivate').'</a>');
											} else {
												echo('<a href="#" id="'.$plugin['dir_name'].'" class="activate-plugin">'._t('Activate').'</a>');
											} ?> | <a href="#" id="<?php echo $plugin['dir_name'] ?>" class="remove-plugin text-danger"><?php _e('Remove') ?></a>
											<?php
											if($has_plugin_update){
												?>
												<div class="plugin-update-btn b-<?php echo $plugin['dir_name'] ?>">
													<a href="#" data-id="<?php echo $plugin['dir_name'] ?>" class="update-plugin text-success" data-path="https://api.cloudarcade.net/plugin-repo/plugins/<?php echo $plugin['dir_name'] ?>/<?php echo $plugin['dir_name'] ?>"><?php _e('Update') ?></a>
												</div>
												<?php
											}
											?>
											</td>
										</tr>
										<?php
									}

									?>
								</tbody>
							</table>
						</div>
					<?php } else {
						echo '<div class="general-wrapper">';
						_e('No plugins installed!');
						echo '</div>';
					} ?>
						
				</div>
			</div>
			<div class="col-lg-4">
				<div class="section">
					<!-- Add New Plugin -->
					<div class="mb-4">
						<h5 class="mb-3"><?php _e('Add New Plugin') ?></h5>
						<!-- Load Plugin Repository -->
						<div class="mb-3">
							<button type="button" class="load-plugin-repo btn btn-success w-100">
								<?php _e('Load Plugin Repository') ?>
							</button>
						</div>
						<!-- Upload Plugin -->
						<div class="mb-3">
							<a href="dashboard.php?viewpage=plugin&slug=upload" class="btn btn-primary w-100"><?php _e('Upload Plugin') ?></a>
						</div>
					</div>
					<hr>
					<div class="mb-4">
						<div class="mb-3">
							<a href="https://cloudarcade.net/plugin-activity/" target="_blank" class="btn btn-primary w-100"><?php _e('View Plugin Activity Log') ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal -->
		<div class="modal fade" id="plugin-repo" tabindex="-1" role="dialog" aria-labelledby="plugin-repo-modal-label" aria-hidden="true">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="plugin-repo-label"><?php _e('Plugin Repository') ?></h5>
				<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
			  </div>
			  <div class="modal-body">
				<div class="plugin-repo-search">
					<input type="text" class="form-control" placeholder="<?php _e('Search plugin') ?>" id="plugin-search">
				</div>
				<div class="mb-3"></div>
				<div class="plugin-repo-container"></div>
				<div class="mb-3"></div>
				<input type="button" class="btn btn-secondary" data-bs-dismiss="modal" value="<?php _e('Close') ?>" />
			  </div>
			</div>
		  </div>
		</div>
		<script type="text/javascript">
			$(document).ready(function(){
				$('button.load-plugin-repo').click(function() {
					let btn = $(this);
					$(this).hide();
					let wrapper = $('.plugin-repo-container');
					wrapper.html('<h3>Loading...</h3>');
					$.ajax({
						url: 'includes/ajax-actions.php',
						type: 'POST',
						dataType: 'json',
						data: {action: 'get_plugin_repo_list'},
						complete: function (data) {
							if(data.status == 200){
								$('#plugin-repo').modal('show');
								wrapper.html(data.responseText);
								//
								$('a.add-plugin-repo').click(function() {
									window.open('request.php?action=pluginAction&reqversion='+$(this).data('reqversion')+'&url='+$(this).data('url')+'&plugin_action=add_plugin&redirect=dashboard.php?viewpage=plugin', '_self');
								});
							} else {
								wrapper.html('<h3>Failed to load!</h3>');
							}
							btn.show();
						}
					});
				});
				$('#plugin-search').bind('keydown keypress keyup change', function() {
					let value = this.value.toLowerCase();
					if(value.length){
						let $tr = $(".plugin-repo-container tr").hide();
						$tr.filter(function() {
							return ($(this).find('.plugin-repo-name').text().toLowerCase()).indexOf(value) >= 0;
						}).show();
					}
				});
			});
		</script>
	<?php
	}
}
?>
	