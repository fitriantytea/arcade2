<?php

if(isset($_SESSION['message'])){
	show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
	unset($_SESSION['message']);
}

if(isset($_GET['id'])){
	$page = Page::getById($_GET['id']);
	if($page){
		?>
<div class="section section-full">
	<ul class="nav nav-tabs custom-tab" role="tablist">
		<li class="nav-item" role="presentation">
			<a class="nav-link active"><?php _e('Edit page') ?></a>
		</li>
	</ul>
	<div class="general-wrapper">
		<div class="editpage-wrapper">
			<form action="request.php" enctype="multipart/form-data" autocomplete="off" method="post">
				<input type="hidden" name="action" value="editPage">
				<input type="hidden" name="redirect" value="dashboard.php?viewpage=pages&slug=edit&id=<?php echo $page->id ?>">
				<input type="hidden" name="id" value="<?php echo $page->id ?>">
				<div class="row">
					<div class="col-md-8">
						<div class="mb-3">
							<label class="form-label" for="title"><?php _e('Page title') ?>:</label>
							<input type="text" class="form-control" name="title" value="<?php echo $page->title ?>" required/>
						</div>
						<div class="mb-3">
							<label class="form-label" for="slug"><?php _e('Page slug') ?>:</label>
							<input type="text" class="form-control" name="slug" placeholder="page-title" value="<?php echo $page->slug ?>" minlength="3" maxlength="50" required>
						</div>
						<div class="mb-3">
							<label class="form-label" for="content"><?php _e('Content') ?>:</label>
							<textarea class="form-control" name="content" placeholder="The HTML content of the page" maxlength="100000" rows="12" required><?php echo $page->content ?></textarea>
						</div>
					</div>
					<div class="col-md-4">
						<?php
						$extra_fields = get_extra_fields('page');
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
										$default_value = $page->getExtraField($field['field_key']);
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
				<div class="mb-3">
					<input id="edit-nl2br" type="checkbox" name="nl2br" <?php echo $page->nl2br == 1 ? 'checked' : ''; ?>>
					<label class="form-label" for="edit-nl2br"><?php _e('Enable nl2br Formatting') ?></label>
					<span class="tooltip-info" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Convert line breaks in text to HTML <br> tags for proper formatting in the web view." data-bs-original-title="Convert line breaks in text to HTML <br> tags for proper formatting in the web view.">
						<i class="fas fa-question"></i>
					</span>
				</div>
				<button type="submit" class="btn btn-primary btn-md"><?php _e('Save changes') ?></button>
			</form>
		</div>
	</div>
</div>
		<?php
	}
}

?>