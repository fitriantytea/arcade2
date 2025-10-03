<?php
if(isset($_POST['action'])){
	if($_POST['action'] == 'reset-priority'){
		$cats = Category::getList()['results'];
		foreach ($cats as $cat) {
			$cat->priority = 0;
			$cat->update();
		}
		$_GET['status'] = 'reset';
	}
}
if(isset($_GET['status'])){
	$class = 'success';
	$message = '';
	if($_GET['status'] == 'added'){
		$message = 'New category added!';
	} elseif($_GET['status'] == 'exist'){
		$class = 'warning';
		$message = 'Category already exist!';
	} elseif($_GET['status'] == 'deleted'){
		$class = 'warning';
		$message = 'Category deleted!';
	} elseif($_GET['status'] == 'updated'){
		$message = 'Category updated!';
		if(isset($_GET['info'])){
			$message = $message.' '.$_GET['info'];
		}
	} elseif($_GET['status'] == 'reset'){
		$message = 'Category priority set to 0!';
		if(isset($_GET['info'])){
			$message = $message.' '.$_GET['info'];
		}
	}
	show_alert($message, $class);
}

if (isset($_GET['slug'])){
	if($_GET['slug'] === 'edit'){
		include 'core/categories-edit.php';
	}
} else {
	include 'core/categories-list.php';
}

?>