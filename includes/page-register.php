<?php

if(!get_setting_value('user_register')){
	exit(_t('User registration is disabled!'));
}

if(is_login()){
	$user_data = get_user($_POST['username']);
	if($user_data['role'] === 'admin'){
		header('Location: '.DOMAIN.'admin/dashboard.php');
		return;
	} else {
		header('Location: '.get_permalink('user', $_SESSION['username']));
		return;
	}
}

require_once( ABSPATH . 'classes/User.php' );

$errors = array();

if(isset($_POST['action'])){

	if($_POST['action'] === 'register'){
		if(!check_errors()){
			$user = new User;
			$_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$user->storeFormValues($_POST);
			$user->insert();
			$_SESSION['message'] = [
				'type'		=> 'success',
				'text'		=> 'Your account has been created.'
			];
			header('Location: '.get_permalink('login'));
			return;
		}
	}
}

function check_errors(){
	global $errors;
	$val = 0;
	$_POST['username'] = strtolower($_POST['username']);
	$username = preg_replace('~[^A-Za-z0-9_.-]~','', $_POST['username']);
	$password = str_replace(' ','',$_POST['password']);
	if(!verify_csrf_token()){
		$errors[] = _t('CSRF invalid!');
		$val = 1;
	}
	if(User::getByUsername($_POST['username'])){
		$errors[] = _t('User %a already exist!', $_POST['username']);
		$val = 1;
	}
	if($username != $_POST['username']){
		$errors[] = _t('Username contains illegal characters!');
		$val = 1;
	}
	if($_POST['email']){
		if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$errors[] = _t('Email not valid!');
			$val = 1;
		} else {
			if(User::getByEmail($_POST['email'])){
				$errors[] = _t('Email %a already exist!', $_POST['email']);
				$val = 1;
			}
		}
	}
	if ($password != $_POST['password']) {
		$errors[] = _t('Password must not contain any space!');
		$val = 1;
	} else {
		if($password != $_POST['confirm_password']){
			$errors[] = _t('Password not match!');
			$val = 1;
		}
	}
	if(!$val){
		if(file_exists(ABSPATH.'includes/banned-username.json')){
			$usernames = json_decode(file_get_contents(ABSPATH.'includes/banned-username.json'), true);
			foreach ($usernames as $name) {
				if($username === $name){
					$errors[] = _t('Username %a is not available!', $_POST['username']);
					return 1;
				}
			}
		}
		if(file_exists(ABSPATH.'includes/banned-words.json')){
			$words = json_decode(file_get_contents(ABSPATH.'includes/banned-words.json'), true);
			foreach ($words as $word) {
				if(strpos('-'.$username, $word)){
					$errors[] = _t('Username contains banned word!');
					return 1;
				}
			}
		}
	}
	if(isset($_POST['captcha'])){
		if(isset($_SESSION['captcha'])){
			if(strtolower($_POST['captcha']) != $_SESSION['captcha']){
				$errors[] = _t('The captcha code does not match!');
				return 1;
			}
		}
	}
	return $val;
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php _e('Register') ?> | <?php echo SITE_TITLE ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<meta name="robots" content="noindex">
		<link rel="stylesheet" type="text/css" href="<?php echo DOMAIN ?>/vendor/bootstrap5/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo DOMAIN ?>admin/style/admin.css">
		<!-- Font Awesome icons (free version)-->
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" defer>
		<?php
		if(file_exists( ABSPATH . TEMPLATE_PATH . '/css/style.css')){
			echo '<link rel="stylesheet" type="text/css" href="'.get_template_path().'/css/style.css">';
		} elseif(file_exists( ABSPATH . TEMPLATE_PATH . '/style/style.css')){
			echo '<link rel="stylesheet" type="text/css" href="'.get_template_path().'/style/style.css">';
		}
		if(file_exists( ABSPATH . TEMPLATE_PATH . '/css/custom.css')){
			echo '<link rel="stylesheet" type="text/css" href="'.get_template_path().'/css/custom.css">';
		} elseif(file_exists( ABSPATH . TEMPLATE_PATH . '/style/custom.css')){
			echo '<link rel="stylesheet" type="text/css" href="'.get_template_path().'/style/custom.css">';
		}
		?>
	</head>
	<body class="login-body">
		<div class="register-container">
			<div class="register-form">
				<div class="container">
					<div class="login-logo text-center">
						<img src="../images/login-logo.png">
					</div>
					<form action="" method="POST">
						<?php
						if(count($errors) > 0){
							foreach ($errors as $msg) {
								show_alert($msg, 'warning');
							}
						}
						?>
						<input type="hidden" name="action" value="register" />
						<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
						<div class="mb-3">
							<label class="form-label"><?php _e('Username') ?></label>
							<input type="text" id="username" name="username" placeholder="<?php _e('Username') ?>" class="form-control" value="<?php echo (isset($_POST['username'])) ? $_POST['username'] : ''; ?>" minlength="4" required>
						</div>
						<div class="mb-3">
							<label class="form-label"><?php echo (get_setting_value('require_email')) ? _t('Email') : _t('Email (Optional)') ?></label>
							<input type="text" id="email" name="email" placeholder="<?php _e('Email') ?>" class="form-control" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : ''; ?>" <?php echo (get_setting_value('require_email')) ? 'required' : '' ?>>
						</div>
						<div class="mb-3">
							<label class="form-label"><?php _e('Birth date') ?></label>
							<input type="date" id="date" name="birth_date" class="form-control" value="<?php echo (isset($_POST['birth_date'])) ? $_POST['birth_date'] : date('Y-m-d'); ?>" required>
						</div>
						<label class="form-label"><?php _e('Gender') ?></label>
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
						<br>
						<div class="mb-3">
							<label class="form-label"><?php _e('Password') ?></label>
							<input type="password" id="password" name="password" autocomplete="new-password" placeholder="<?php _e('Password') ?>" class="form-control" value="" minlength="6" required>
						</div>
						<div class="mb-3">
							<label class="form-label"><?php _e('Re-type password') ?></label>
							<input type="password" name="confirm_password" placeholder="<?php _e('Password') ?>" class="form-control" value="" type="password" minlength="6" required>
						</div>
						<?php
						if(get_setting_value('captcha')){
							?>
							<div class="mb-3">
								<div class="mt-3"></div>
								<img src="<?php echo DOMAIN ?>includes/captcha.php" style="width: 140px;">
								<div class="mb-3"></div>
								<input type="text" id="captcha" name="captcha" placeholder="<?php _e('Enter captcha text above') ?>" class="form-control" value="" maxlength="10" required>
							</div>
							<?php
						}
						?>
						<button type="submit" class="btn btn-info btn-block"><?php _e('Register') ?></button>
						<br>
						<div class="text-center"><?php _e('Already have an account?') ?> <?php _e('Try') ?> <a href="<?php echo get_permalink('login') ?>"><?php _e('Login') ?></a></div>
						<div class="text-center mt-3"><a href="<?php echo DOMAIN ?>">< <?php _e('Back to Home') ?></a></div>
					</form>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="<?php echo DOMAIN ?>js/jquery-3.6.2.min.js"></script>
		<script type="text/javascript" src="<?php echo DOMAIN ?>/vendor/bootstrap5/js/bootstrap.min.js"></script>
	</body>
</html>