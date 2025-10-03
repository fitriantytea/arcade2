<?php
if (isset($_GET['status'])) {
	if ($_GET['status'] == 'success') {
		show_alert(isset($_GET['info']) ? $_GET['info'] : 'Game successfully update!', 'success');
	} elseif ($_GET['status'] == 'deleted') {
		show_alert(isset($_GET['info']) ? $_GET['info'] : 'Game removed!', 'danger');
	}
}

if (isset($_GET['slug'])){
	if($_GET['slug'] === 'edit'){
		include 'core/gamelist-edit.php';
	}
} else {
	include 'core/gamelist-list.php';
}
?>