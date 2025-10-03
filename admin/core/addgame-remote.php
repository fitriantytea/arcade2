<div class="addgame-wrapper" id="remote">
	<form id="form-remote" action="request.php" autocomplete="off" method="post" enctype="multipart/form-data">
		<input type="hidden" name="action" value="addGame"/>
		<input type="hidden" name="source" value="remote"/>
		<input type="hidden" name="redirect" value="dashboard.php?viewpage=addgame&slug=remote">
		<input type="hidden" name="tags" value=""/>
		<div class="row">
			<div class="col-md-8">
				<div class="mb-3">
					<label class="form-label" for="title"><?php _e('Game title') ?>:</label>
					<input type="text" class="form-control" name="title" value="<?php echo (isset($_SESSION['title'])) ? $_SESSION['title'] : "" ?>" id="game-title-remote" required />
				</div>
				<?php
				if(CUSTOM_SLUG){ ?>
					<div class="mb-3">
						<label class="form-label" for="slug"><?php _e('Game slug') ?>:</label>
						<input type="text" class="form-control" name="slug" placeholder="game-title" value="<?php echo (isset($_SESSION['slug'])) ? $_SESSION['slug'] : "" ?>" minlength="3" maxlength="50" id="game-slug-remote" required>
					</div>
				<?php }
				?>
				<div class="mb-3">
					<label class="form-label" for="description"><?php _e('Description') ?>:</label>
					<textarea class="form-control" name="description" rows="3" required><?php echo (isset($_SESSION['description'])) ? $_SESSION['description'] : "" ?></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="instructions"><?php _e('Instructions') ?>:</label>
					<textarea class="form-control" name="instructions" rows="3"><?php echo (isset($_SESSION['instructions'])) ? $_SESSION['instructions'] : "" ?></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="thumb_method"><?php _e('Thumbnail Method') ?>:</label>
					<select class="form-control" id="thumb_method" name="thumb_method">
						<option value="url" selected><?php _e('Use URL') ?></option>
						<option value="upload"><?php _e('Upload File') ?></option>
					</select>
				</div>
				<div class="ms-4">
					<!-- URL Thumbnail -->
					<p class="form-text text-muted">The thumbnail size isn't strict, but 512x384 and 512x512 are recommended for the best fit with official themes. Other sizes may not display optimally.</p>
					<div class="mb-3" id="thumb_url_wrapper">
						<label class="form-label" for="thumb_1"><?php _e('Thumbnail') ?> 512x384:</label>
						<input type="text" class="form-control" name="thumb_1" id="thumb_1" placeholder="https://example.com/yourgames/thumb_1.jpg" value="<?php echo (isset($_SESSION['thumb_1'])) ? $_SESSION['thumb_1'] : "" ?>" required />
					</div>

					<div class="mb-3" id="thumb_url_wrapper_2">
						<label class="form-label" for="thumb_2"><?php _e('Thumbnail') ?> 512x512:</label>
						<input type="text" class="form-control" name="thumb_2" id="thumb_2" placeholder="https://example.com/yourgames/thumb_2.jpg" value="<?php echo (isset($_SESSION['thumb_2'])) ? $_SESSION['thumb_2'] : "" ?>" required />
					</div>

					<!-- Upload Thumbnail -->
					<div class="mb-3" id="thumb_upload_wrapper" style="display:none;">
						<label class="form-label" for="thumb_upload_1"><?php _e('Upload Thumbnail') ?> 512x384:</label>
						<input type="file" class="form-control" name="thumb_upload_1" id="thumb_upload_1" accept="image/jpeg, image/png, image/webp" />
					</div>

					<div class="mb-3" id="thumb_upload_wrapper_2" style="display:none;">
						<label class="form-label" for="thumb_upload_2"><?php _e('Upload Thumbnail') ?> 512x512:</label>
						<input type="file" class="form-control" name="thumb_upload_2" id="thumb_upload_2" accept="image/jpeg, image/png, image/webp" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="game_type"><?php _e('Game Type') ?>:</label>
					<select class="form-control" id="game_type" name="game_type">
						<?php
						$_game_types = get_game_types();
						foreach ($_game_types as $type_id => $type) {
							$selected = ($type_id === 'html5') ? 'selected' : '';
							echo '<option value="' . $type_id . '" ' . $selected . '>' . $type['name'] . '</option>';
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label" for="url"><?php _e('Game URL') ?>:</label>
					<input type="text" class="form-control" name="url" value="<?php echo (isset($_SESSION['url'])) ? $_SESSION['url'] : "" ?>" placeholder="https://example.com/yourgames/index.html" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="width"><?php _e('Game width') ?>:</label>
					<input type="number" class="form-control" name="width" value="<?php echo (isset($_SESSION['width'])) ? $_SESSION['width'] : "720" ?>" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="height"><?php _e('Game height') ?>:</label>
					<input type="number" class="form-control" name="height" value="<?php echo (isset($_SESSION['height'])) ? $_SESSION['height'] : "1080" ?>" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="category"><?php _e('Category') ?>:</label>
					<select multiple class="form-control" name="category[]" size="8" required />
						<?php
						$results = array();
						$data = Category::getList();
						$categories = $data['results'];
						foreach ($categories as $cat) {
							$selected = (in_array($cat->name, $selected_categories)) ? 'selected' : '';
							echo '<option '.$selected.'>'.$cat->name.'</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="mb-3">
					<label class="form-label" for="tags"><?php _e('Tags') ?>:</label>
					<input type="text" class="form-control" name="tags" value="<?php echo (isset($_SESSION['tags'])) ? $_SESSION['tags'] : "" ?>" id="tags-remote" placeholder="<?php _e('Separated by comma') ?>">
				</div>
				<div class="tag-list">
					<?php
					$tag_list = get_tags('usage');
					if(count($tag_list)){
						echo '<div class="mb-3">';
						foreach ($tag_list as $tag_name) {
							echo '<span class="badge rounded-pill bg-secondary btn-tag" data-target="tags-remote" data-value="'.$tag_name.'">'.$tag_name.'</span>';
						}
						echo '</div>';
					}
					?>
				</div>
				<?php
				$extra_fields = get_extra_fields('game');
				if(count($extra_fields)){
					?>
					<div class="extra-fields">
						<?php
						foreach ($extra_fields as $field) {
							?>
							<div class="mb-3">
								<label class="form-label" for="<?php echo $field['field_key'] ?>"><?php _e($field['title']) ?>:</label>
								<?php
								$default_value = $field['default_value'];
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
		<input id="is_mobile" type="checkbox" name="is_mobile" <?php echo (isset($_SESSION['is_mobile']) ? filter_var($_SESSION['is_mobile'], FILTER_VALIDATE_BOOLEAN) : true) ? 'checked' : ''; ?>>
			<label class="form-label" for="is_mobile"><?php _e('Is mobile compatible') ?></label><br>
		<input id="published" type="checkbox" name="published" <?php echo (isset($_SESSION['published']) ? filter_var($_SESSION['published'], FILTER_VALIDATE_BOOLEAN) : true) ? 'checked' : ''; ?>>
		<label class="form-label" for="published"><?php _e('Published') ?></label><br>
		<p style="margin-left: 20px;" class="text-secondary">
			<?php _e('If unchecked, this game will set as Draft.') ?>
		</p>
	</div>
	<button type="submit" class="btn btn-primary btn-md"><?php _e('Add game') ?></button>
</form>
</div>

<script>
	document.getElementById('thumb_method').addEventListener('change', function() {
		var selectedMethod = this.value;

		if (selectedMethod === 'upload') {
			document.getElementById('thumb_url_wrapper').style.display = 'none';
			document.getElementById('thumb_url_wrapper_2').style.display = 'none';
			document.getElementById('thumb_upload_wrapper').style.display = 'block';
			document.getElementById('thumb_upload_wrapper_2').style.display = 'block';
			document.getElementById('thumb_1').required = false;
			document.getElementById('thumb_2').required = false;
			document.getElementById('thumb_upload_1').required = true;
			document.getElementById('thumb_upload_2').required = true;
		} else {
			document.getElementById('thumb_url_wrapper').style.display = 'block';
			document.getElementById('thumb_url_wrapper_2').style.display = 'block';
			document.getElementById('thumb_upload_wrapper').style.display = 'none';
			document.getElementById('thumb_upload_wrapper_2').style.display = 'none';
			document.getElementById('thumb_1').required = true;
			document.getElementById('thumb_2').required = true;
			document.getElementById('thumb_upload_1').required = false;
			document.getElementById('thumb_upload_2').required = false;
		}
	});
	document.getElementById('game_type').addEventListener('change', function() {
        const urlField = document.querySelector('input[name="url"]').parentElement;
        const widthField = document.querySelector('input[name="width"]').parentElement;
        const heightField = document.querySelector('input[name="height"]').parentElement;
        
        if (this.value === 'html5') {
            urlField.style.display = 'block';
            urlField.querySelector('input').required = true;
        } else {
            urlField.style.display = 'none';
            urlField.querySelector('input').required = false;
        }
    });
</script>