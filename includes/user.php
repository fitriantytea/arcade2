<?php

require('../config.php');
require('../init.php');

if($login_user){
	if(isset($_POST['action'])){
		if(ADMIN_DEMO && $login_user->role == 'admin'){
			$status = 'error';
			$info = 'Demo mode! Can\'t change Admin user profile';
			if(isset($_POST['redirect'])){
				header('Location: '.$_POST['redirect'].'&status='.$status.'&info='.$info);
			}
			return;
		}
		if($_POST['action'] == 'upload_avatar'){
			if(!verify_csrf_token()) die('ERR CSRF');
			$status = '';
			$message = '';
			if(isset($_FILES["avatar"])){
				if(!file_exists(ABSPATH . 'images/avatar')){
					mkdir('../images/avatar', 755, true);
				}
				$uploadOk = 1;
				$fileType = strtolower(pathinfo(basename($_FILES["avatar"]["name"]),PATHINFO_EXTENSION));

				$target_file = ABSPATH . 'images/avatar/'.$login_user->username.'.png';
				$check = getimagesize($_FILES["avatar"]["tmp_name"]);
				if($check) {
					//echo "File is an image - " . $check["mime"] . ".";
					$uploadOk = 1;
				} else {
					//echo "File is not an image.";
					$uploadOk = 0;
				}
				if ($uploadOk && $_FILES["avatar"]["size"] > 500000) {
					//echo "Sorry, your file is too large. max 500kb";
					$uploadOk = 0;
				}
				if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg") {
					//echo "Sorry, only JPG, JPEG, PNG files are allowed.";
					$uploadOk = 0;
				}
				if ($uploadOk == 0) {
					//echo "Sorry, your file was not uploaded.";
				} else {
					//Convert to PNG
					$conver_image = $_FILES['avatar']['tmp_name'];
					switch ($fileType) {
						case 'jpg':
						case 'jpeg':
						   $set_image = imagecreatefromjpeg($conver_image);
						break;
						case 'gif':
						   $set_image = imagecreatefromgif($conver_image);
						break;
						case 'png':
						   $set_image = imagecreatefrompng($conver_image);
						break;
					}
					imagepng($set_image, $conver_image);

					if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
						//echo "The file ". basename( $_FILES["avatar"]["name"]). " has been uploaded.";
						resize_avatar($target_file);
						$status = 'success';
					} else {
						echo "Sorry, there was an error uploading your file.";
					}
				}
			}
			if(!$uploadOk){
				$status = 'error';
				$message = 'Upload failed!';
			} else {
				$status = 'success';
				$message = 'Avatar uploaded!';
			}
			$_SESSION['alert'] = [
			    'status' => $status,
			    'message' => $message
			];
			if(isset($_POST['redirect'])){
				header('Location: '.$_POST['redirect']);
			}
		} elseif($_POST['action'] == 'edit_profile'){
			if(!verify_csrf_token()) die('ERR CSRF');
			$status = '';
			$message = '';
			$error = false;
			if($_POST['email']){
				if( $_POST['email'] != $login_user->email){
					if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
						$error = true;
						$status = 'error';
						$message = 'Email not valid!';
					} else {
						if(User::getByEmail($_POST['email'])){
							$error = true;
							$status = 'error';
							$message = 'Email already exist!';
						}
					}
				}
			}
			$login_user->bio = esc_string($_POST['bio']);
			if(!$error){
				$login_user->birth_date = $_POST['birth_date'];
				$login_user->gender = $_POST['gender'];
				$login_user->email = $_POST['email'];
				$login_user->update();
				$status = 'success';
				$message = 'Profile updated!';
			}
			$_SESSION['alert'] = [
			    'status' => $status,
			    'message' => $message
			];
			if(isset($_POST['redirect'])){
				header('Location: '.$_POST['redirect']);
			}
		} elseif($_POST['action'] == 'change_password'){
			if(!verify_csrf_token()) die('ERR CSRF');
			$status = '';
			$message = '';
			$error = false;
			$new_password = str_replace(' ','',$_POST['new_password']);
			if($new_password != $_POST['new_password']){
				$error = true;
				$status = 'error';
				$message = 'Password must not contain any space!';
			}
			if(!$error){
				if(!password_verify($_POST['cur_password'], get_current_user_hash())){
					$error = true;
					$status = 'error';
					$message = 'Incorrect password!';
				}
			}
			if(!$error){
				$login_user->password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
				$login_user->update();
				$status = 'success';
				$message = 'Password updated!';
			}
			$_SESSION['alert'] = [
			    'status' => $status,
			    'message' => $message
			];
			if(isset($_POST['redirect'])){
				header('Location: '.$_POST['redirect']);
			}
		} elseif($_POST['action'] == 'choose_avatar'){
			if(!verify_csrf_token()) die('ERR CSRF');
			$status = '';
			$message = '';
			$error = false;
			if(file_exists(ABSPATH.'images/avatar/default/'.$_POST['avatar'].'.png')){
				$login_user->avatar = $_POST['avatar'];
				$login_user->update();
				if(file_exists(ABSPATH.'images/avatar/'.$login_user->username.'.png')){
					unlink('../images/avatar/'.$login_user->username.'.png');
				}
			} else {
				$status = 'error';
				$message = 'Failed!';
				$error = true;
			}
			if(!$error){
				$status = 'success';
				$message = 'Avatar updated!';
			}
			$_SESSION['alert'] = [
			    'status' => $status,
			    'message' => $message
			];
			if(isset($_POST['redirect'])){
				header('Location: '.$_POST['redirect']);
			}
		} elseif($_POST['action'] == 'delete_account'){
			if(!verify_csrf_token()) die('ERR CSRF');
			$status = '';
			$message = '';
			$error = false;
			$cur_password = str_replace(' ','',$_POST['cur_password']);
			if($cur_password != $_POST['cur_password']){
				$error = true;
				$status = 'error';
				$message = 'Password must not contain any "space"!';
			}
			if(!$error){
				if(!password_verify($_POST['cur_password'], get_current_user_hash())){
					$error = true;
					$status = 'error';
					$message = 'Incorrect password!';
				}
			}
			if(!$error){
				$login_user->delete( $_POST['cur_password'] );
				CA_Auth::delete();
				unset( $_SESSION['username'] );
				header('Location: '.DOMAIN);
				return;
			}
			if($error){
				$_SESSION['alert'] = [
				    'status' => $status,
				    'message' => $message
				];
				if(isset($_POST['redirect'])){
					header('Location: '.$_POST['redirect']);
				}
			}	
		}
	}
}

function resize_avatar($path, $rs_width=100, $rs_height=100){
	if(file_exists($path)){
		$x = getimagesize($path);
		$width  = $x['0'];
		$height = $x['1'];
		$img = imagecreatefrompng($path);
		$img_base = imagecreatetruecolor($rs_width, $rs_height);
		imagecopyresampled($img_base, $img, 0, 0, 0, 0, $rs_width, $rs_height, $width, $height);
		imagepng($img_base, $path, 9);
	}
}

?>