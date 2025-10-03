<?php

// User profile edit page

?>
<div class="user-page">
	<div class="container">
		<h3 class="single-title">Edit Profile</h3>
		<?php
		if(isset($_SESSION['alert'])){
			$type = 'success';
			$status = $_SESSION['alert']['status'];
			$message = $_SESSION['alert']['message'];
			if($status == 'error'){
				$type = 'danger';
			}
			show_alert(_t($message), $type);
			unset($_SESSION['alert']);
		}
		?>
		<div class="row">
			<div class="col-md-8">
				<div class="section">
					<form id="form-settings" action="<?php echo DOMAIN.'includes/user.php' ?>" method="post">
						<input type="hidden" name="action" value="edit_profile">
						<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
						<input type="hidden" name="redirect" value="<?php echo get_permalink('user', $login_user->username, ['edit' => 'edit']) ?>">
						<div class="mb-3 row">
							<label for="email" class="col-sm-2 col-form-label"><?php _e('Email') ?>:</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="email" minlength="4" value="<?php echo $login_user->email ?>">
							</div>
						</div>
						<div class="mb-3 row">
							<label for="birth_date" class="col-sm-2 col-form-label"><?php _e('Birth date') ?>:</label>
							<div class="col-sm-10">
								<input type="date" class="form-control" name="birth_date" value="<?php echo $login_user->birth_date ?>" required>
							</div>
						</div>
						<div class="mb-3 row">
							<label for="bio" class="col-sm-2 col-form-label"><?php _e('About me') ?>:</label>
							<div class="col-sm-10">
								<textarea class="form-control" name="bio" rows="3"><?php echo $login_user->bio ?></textarea>
							</div>
						</div>
						<div class="mb-3 row">
							<label for="gender" class="col-sm-2 col-form-label"><?php _e('Gender') ?>:</label>
							<div class="col-sm-10">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="gender" id="gender1" value="male">
									<label class="form-check-label" for="gender1">
										<?php _e('Male') ?>
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="gender" id="gender2" value="female">
									<label class="form-check-label" for="gender2">
										<?php _e('Female') ?>
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="gender" id="gender3" value="unset" checked>
									<label class="form-check-label" for="gender3">
										<?php _e('Unset') ?>
									</label>
								</div>
							</div>
						</div>
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Update') ?></button>
					</form>
				</div>
			</div>
			<div class="col-md-4">
				<?php if(get_setting_value('upload_avatar')){ ?>
					<div class="section">
						<h3 class="section-title"><?php _e('Upload Avatar') ?></h3>
						<form action="<?php echo DOMAIN.'includes/user.php' ?>" method="post" enctype="multipart/form-data">
							<div class="mb-3">
								<input type="hidden" name="action" value="upload_avatar">
								<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
								<input type="hidden" name="redirect" value="<?php echo get_permalink('user', $login_user->username, ['edit' => 'edit']) ?>">
								<label for="file"><?php _e('Supported format') ?>: png, jpg, jpeg (Max 500kb)</label><br>
								<input type="file" class="form-control" name="avatar" accept=".png,.jpg,.jpeg"/><br>
								<button type="submit" class="btn btn-primary btn-md"><?php _e('Upload') ?></button>
							</div>
						</form>
					</div>
				<?php } ?>
				<div class="section">
					<h3 class="section-title"><?php _e('Choose Avatar') ?></h3>
					<form action="<?php echo DOMAIN.'includes/user.php' ?>" method="post" enctype="multipart/form-data">
						<div class="mb-3">
							<input type="hidden" name="action" value="choose_avatar">
							<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
							<input type="hidden" name="redirect" value="<?php echo get_permalink('user', $login_user->username, ['edit' => 'edit']) ?>">
							<div class="row avatar-chooser">
								<?php
								if(file_exists(ABSPATH.'images/avatar/default/')){
									$avatars = scan_files('images/avatar/default/');
									foreach ($avatars as $avatar) {
										if(substr($avatar, -4) === '.png'){
											$name = basename($avatar, '.png');
											?>
											<div class="col-3">
												<input type="radio" class="input-hidden" id="avatar-<?php echo $name ?>" name="avatar" value="<?php echo $name ?>" />
												<label for="avatar-<?php echo $name ?>">
													<img src="<?php echo DOMAIN.$avatar ?>">
												</label>
											</div>
											<?php
										}
									}
								}
								?>
							</div>
							<br>
							<button type="submit" class="btn btn-primary btn-md"><?php _e('Change avatar') ?></button>
						</div>
					</form>
				</div>
				<div class="section">
					<h3 class="section-title"><?php _e('Change password') ?></h3>
					<form action="<?php echo DOMAIN.'includes/user.php' ?>" method="post" enctype="multipart/form-data">
						<div class="mb-3">
							<input type="hidden" name="action" value="change_password">
							<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
							<input type="hidden" name="redirect" value="<?php echo get_permalink('user', $login_user->username, ['edit' => 'edit']) ?>">
							<div class="mb-3">
								<label><?php _e('Current password') ?>:</label>
								<input type="password" class="form-control" name="cur_password" autocomplete="new-password" minlength="6" value="" required>
							</div>
							<div class="mb-3">
								<label><?php _e('New password') ?>:</label>
								<input type="password" class="form-control" name="new_password" minlength="6" value="" required>
							</div>
							<button type="submit" class="btn btn-primary btn-md"><?php _e('Update') ?></button>
						</div>
					</form>
				</div>
				<?php if(!USER_ADMIN){ ?>

					<div class="section">
						<h3 class="section-title"><?php _e('Delete account') ?></h3>
						<form action="<?php echo DOMAIN.'includes/user.php' ?>" method="post" enctype="multipart/form-data">
							<div class="mb-3">
								<input type="hidden" name="action" value="delete_account">
								<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
								<input type="hidden" name="redirect" value="<?php echo get_permalink('user', $login_user->username, ['edit' => 'edit']) ?>">
								<div class="mb-3">
									<label><?php _e('Your password') ?>:</label>
									<input type="password" class="form-control" name="cur_password" autocomplete="new-password" minlength="6" value="" required>
								</div>
								<button type="submit" class="btn btn-danger btn-md"><?php _e('Delete') ?></button>
							</div>
						</form>
					</div>

				<?php } ?>
			</div>
		</div>
	</div>
</div>