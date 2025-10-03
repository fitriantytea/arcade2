<?php

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

if (!isset($url_params)) {
	// $url_params is undefined, which means that page-login.php is being loaded from admin.php instead of index.php.
	// $url_params is handled in index.php.
	$url_params = ['login'];
}

$errors = array();

if (defined('GOOGLE_LOGIN')){
	if(isset($_SESSION['google-login-data'])){
		$payload = $_SESSION['google-login-data'];
		if(isset($payload['sub'])){
			$is_ok = false;
			$username = str_replace(' ', '-', $payload['username']);
			$user_data = get_user($username);
			if(!$user_data){
				//User not exist
				//Register new user
				$user = new User;
				$_POST['username'] = $username;
				$_POST['password'] = password_hash($payload['sub'], PASSWORD_DEFAULT);
				$_POST['email'] = $payload['email'];
				$_POST['data'] = '{"google-login-id" : "'.$payload['sub'].'"}';
				$_POST['birth_date'] = date('Y-m-d');
				$_POST['gender'] = 'unset';
				$user->storeFormValues($_POST);
				$user->insert();
				$is_ok = true;
			} else {
				// User already exist, further security checks
				if(isset($user_data['data']) && $user_data['data'] != ''){
					$_data = json_decode($user_data['data'], true);
					if(isset($_data['google-login-id']) && $_data['google-login-id'] == $payload['sub']){
						$is_ok = true;
					} else {
						$errors[] = _t('User Google ID not match!');
					}
				} else {
					$errors[] = _t('User Google ID not found!');
				}
			}
			//
			if($is_ok){
				$_POST['username'] = $username;
				$_POST['password'] = $payload['sub'];
				$_POST['login'] = true;
				$_POST['remember'] = true;
			}
		}
		unset($_SESSION['google-login-data']);
	}
}

if ( isset( $_POST['login'] ) ) {
	$user_data = get_user($_POST['username']);
	if($user_data){
		if(password_verify($_POST['password'], $user_data['password'])){
			$_SESSION['username'] = $_POST['username'];

			if(isset($_POST['remember'])){
				CA_Auth::insert(str_encrypt($_SESSION['username'], 'f'));
			}

			if($user_data['role'] === 'admin'){
				header('Location: '.DOMAIN.'admin/dashboard.php');
				update_login_history('success');
				return;
			} else {
				header('Location: '.get_permalink('user', $_SESSION['username']));
				return;
			}
		}
	}
	$errors[] = _t('Incorrect username or password.');
}

if (isset($_POST['login'])) {
	$timer            = time() - 30;
	$ip_address      = getIpAddr();
	// Getting total count of hits on the basis of IP
	$conn = open_connection();
	$sql = "SELECT count(*) FROM loginlogs WHERE TryTime > :timer and IpAddress = :ip_address";
	$st = $conn->prepare($sql);
	$st->bindValue(":timer", $timer, PDO::PARAM_INT);
	$st->bindValue(":ip_address", $ip_address, PDO::PARAM_STR);
	$st->execute();
	$totalRows = $st->fetchColumn();
	$total_count     = $totalRows;
	if ($total_count == 10) {
		$errors[] = _t('To many failed login attempts. Please login after 30 sec.');
	} else {
		$total_count++;
		$rem_attm = 10 - $total_count;
		if ($rem_attm == 0) {
			$errors[] = _t('To many failed login attempts. Please login after 30 sec.');
		} else {
			$errors[] = _t('%a attempts remaining.', $rem_attm);
		}
		$try_time = time();;
		$sql = "INSERT INTO loginlogs(IpAddress,TryTime) VALUES(:ip_address, :try_time)";
		$st = $conn->prepare($sql);
		$st->bindValue(":ip_address", $ip_address, PDO::PARAM_STR);
		$st->bindValue(":try_time", $try_time, PDO::PARAM_INT);
		$st->execute();
	}
}

function update_login_history($status = 'null'){
	$ip_address = getIpAddr();
	$data = array(
		'username' => $_POST['username'],
		'password' => '***',
		'date' => date("Y-m-d H:i:s"),
		'status' => $status,
		'agent' => 'null',
		'country' => 'null',
		'city' => 'null',
	);
	if($_SERVER['HTTP_USER_AGENT']){
		$data['agent'] = $_SERVER['HTTP_USER_AGENT'];
	}
	$conn = open_connection();
	$sql = "INSERT INTO login_history(ip, data) VALUES(:ip_address, :data)";
	$st = $conn->prepare($sql);
	$st->bindValue(":ip_address", $ip_address, PDO::PARAM_STR);
	$st->bindValue(":data", json_encode($data), PDO::PARAM_STR);
	$st->execute();

	$sql = "SELECT * FROM login_history";
	$st = $conn->prepare($sql);
	$st->execute();
	$count = $st->rowCount();
	if($count > 100){
		$sql = "DELETE FROM login_history ORDER BY id ASC LIMIT 10";
		$st = $conn->prepare($sql);
		$st->execute();
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php _e('Login') ?> | <?php echo SITE_TITLE ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<meta name="robots" content="noindex">
		<link rel="stylesheet" type="text/css" href="<?php echo DOMAIN ?>/vendor/bootstrap5/css/bootstrap.min.css" />
		<!-- Font Awesome icons (free version)-->
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" defer>
		<link rel="stylesheet" type="text/css" href="<?php echo DOMAIN ?>admin/style/admin.css">
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
		if(defined('GOOGLE_LOGIN')){
			echo '<script src="https://accounts.google.com/gsi/client" async defer></script>';
		}
		?>
	</head>
	<body class="login-body">
		<div class="login-container">
			<div class="login-form">
				<div class="container">
					<div class="login-logo text-center">
						<img src="<?php echo DOMAIN ?>images/login-logo.png">
					</div>
					<?php
					if(count($url_params) == 1) {
					?>
						<form method="POST" enctype="multipart/form-data">
							<?php
							if(count($errors) > 0){
								foreach ($errors as $msg) {
									show_alert($msg, 'warning');
								}
							}
							if(isset($_SESSION['message'])){
								// Come from registration
								show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
								unset($_SESSION['message']);
							}
							?>
							<input type="hidden" name="login" value="true" />
							<div class="mb-3">
								<input type="text" id="username" name="username" placeholder="<?php _e('Username') ?>" class="form-control" value="" required>
							</div>
							<div class="mb-3">
								<input type="password" id="password" name="password" autocomplete="new-password" placeholder="<?php _e('Password') ?>" class="form-control" value="" required>
							</div>
							<div class="form-check">
								<input type="checkbox" class="form-check-input" name="remember" id="remember-me" checked>
								<label class="form-check-label" for="remember-me"><?php _e('Remember me') ?></label>
							</div>
							<br>
							<div class="text-center">
								<button type="submit" class="btn btn-info btn-block"><?php _e('Login') ?></button>
							</div>
							<?php if(defined('GOOGLE_LOGIN')){
								render_google_login_btn();
							} ?>
							<div class="login-links mt-3">
							<?php if(get_setting_value('user_register')){ ?>
								<div class="text-center link-register"><?php _e('Or') ?> <a href="<?php echo get_permalink('register') ?>"><?php _e('Register') ?></a></div>
							<?php } ?>
							<?php if(get_plugin_info('mailer') && get_plugin_info('forgot-password') && get_pref_bool('forgot-password-enabled')){ ?>
								<div class="text-center link-forgot-password"><a href="<?php echo get_permalink('login', 'forgot') ?>"><?php _e('Forgot password?') ?></a></div>
							<?php } ?>
							</div>
							<div class="text-center mt-3"><a href="<?php echo DOMAIN ?>">< <?php _e('Back to Home') ?></a></div>
						</form>
					<?php
					} else if(count($url_params) == 2 && $url_params[1] == 'forgot') {
						if(get_plugin_info('mailer') && get_plugin_info('forgot-password') && get_pref_bool('forgot-password-enabled')){
							if(!isset($_POST['action'])){
							?>
							<form method="post" enctype="multipart/form-data">
								<input type="hidden" name="action" value="forgot-password">
								<div class="mb-3">
									<input type="email" class="form-control" name="email" value="" placeholder="Your email" required>
								</div>
								<div class="text-center">
									<button class="btn btn-primary btn-sm"><?php _e('Request a new password') ?></button>
								</div>
							</form>
							<?php
							} else {
								if(isset($_POST['email'])){
									require_once get_plugin_info('forgot-password')['path'] . '/fp_req.php';
									fp_req_password($_POST['email']);
									show_alert('New password sent to your mail.', 'success');
								}
							}
							?>
							<div class="text-center mt-3"><a href="<?php echo get_permalink('login') ?>">< <?php _e('Back to Login') ?></a></div>
							<?php
						}
					}
					?>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="<?php echo DOMAIN ?>js/jquery-3.6.2.min.js"></script>
		<script type="text/javascript" src="<?php echo DOMAIN ?>/vendor/bootstrap5/js/bootstrap.min.js"></script>
	</body>
</html>