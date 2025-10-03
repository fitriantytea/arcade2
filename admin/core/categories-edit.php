<?php

if(isset($_SESSION['message'])){
	show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
	unset($_SESSION['message']);
}

if(isset($_GET['id'])){
	$category = Category::getById($_GET['id']);
	if($category){
		?>
<div class="section section-full">
	<ul class="nav nav-tabs custom-tab" role="tablist">
		<li class="nav-item" role="presentation">
			<a class="nav-link active"><?php _e('Edit category') ?></a>
		</li>
	</ul>
	<div class="general-wrapper">
		<form action="request.php" method="post">
			<input type="hidden" name="action" value="editCategory">
			<input type="hidden" name="redirect" value="dashboard.php?viewpage=categories&slug=edit&id=<?php echo $_GET['id'] ?>">
			<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>"/>
			<div class="row">
				<div class="col-md-8">
					<div class="mb-3">
							<label class="form-label" for="title"><?php _e('Category Name') ?>:</label>
							<?php show_alert('Change category name will update all related games category string.', 'warning') ?>
							<input type="text" class="form-control" id="edit-name" name="name" placeholder="Name of the game" required minlength="2" maxlength="30" value="<?php echo $category->name ?>">
						</div>
						<div class="mb-3">
							<label class="form-label" for="slug"><?php _e('Category Slug') ?>:</label>
							<input type="text" class="form-control" id="edit-slug" name="slug" placeholder="online-games" required minlength="2" maxlength="30" value="<?php echo $category->slug ?>">
						</div>
						<div class="mb-3">
							<label class="form-label" for="description"><?php _e('Description') ?>:</label>
							<textarea class="form-control" name="description" id="edit-description" rows="3" placeholder="(Optional) Category description" minlength="3" maxlength="100000"><?php echo $category->description ?></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label" for="meta_description"><?php _e('Meta Description') ?>:</label>
							<textarea class="form-control" name="meta_description" id="edit-meta_description" rows="3" placeholder="(Optional) Category meta description" minlength="3" maxlength="100000"><?php echo $category->meta_description ?></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label" for="edit-priority"><?php _e('Priority') ?>:</label>
							<input type="number" class="form-control" id="edit-priority" name="priority" value="<?php echo $category->priority ?>" />
						</div>
						<div class="mb-3">
							<label class="form-label" for="cat-id"><?php _e('ID') ?>:</label>
							<input type="text" class="form-control" id="cat-id" value="<?php echo $category->id ?>" disabled />
						</div>
						<div class="mb-3">
							<input id="edit-hide" class="edit-hide" name="hide" type="checkbox" <?php echo ($category->priority < 0) ? 'checked' : '' ?>>
							<label class="form-label" for="edit-hide"><?php _e('Hide') ?></label><br>
						</div>
				</div>
				<div class="col-md-4">
					<?php
					$extra_fields = get_extra_fields('category');
					if(count($extra_fields)){
						?>
						<div class="extra-fields">
							<?php
							foreach ($extra_fields as $field) {
								?>
								<div class="mb-3">
									<label class="form-label" for="<?php echo $field['field_key'] ?>"><?php _e($field['title']) ?>:
										<br>
										<small class="fst-italic text-secondary"><?php echo $field['field_key'] ?></small>
									</label>
									<?php
									$default_value = $category->getExtraField($field['field_key']);
									$placeholder = $field['placeholder'];
									if($field['type'] === 'textarea'){
										echo '<textarea class="form-control" name="extra_fields['.$field['field_key'].']" rows="3">'.$default_value.'</textarea>';
									} else if($field['type'] === 'number'){
										echo '<input type="number" name="extra_fields['.$field['field_key'].']" class="form-control" placeholder="'.$placeholder.'" value="'.$default_value.'">';
									} else if($field['type'] === 'text'){
										echo '<input type="text" name="extra_fields['.$field['field_key'].']" class="form-control" placeholder="'.$placeholder.'" value="'.$default_value.'">';
									}
									?>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<input type="submit" class="btn btn-primary" value="<?php _e('Save changes') ?>">
		</form>
	</div>
		<?php
	}
}

?>