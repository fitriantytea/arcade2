<?php

if(isset($_SESSION['message'])){
	show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
	unset($_SESSION['message']);
}

if(isset($_GET['id'])){
	$game = Game::getById($_GET['id']);
	if($game){
		?>
<div class="section section-full">
	<ul class="nav nav-tabs custom-tab" role="tablist">
		<li class="nav-item" role="presentation">
			<a class="nav-link active"><?php _e('Edit game') ?></a>
		</li>
	</ul>
	<div class="general-wrapper">
		<div class="editgame-wrapper">
			<form id="form-uploadgame" action="request.php" enctype="multipart/form-data" autocomplete="off" method="post">
				<input type="hidden" name="action" value="editGame">
				<input type="hidden" name="redirect" value="dashboard.php?viewpage=gamelist&slug=edit&id=<?php echo $game->id ?>">
				<input type="hidden" name="id" value="<?php echo $game->id ?>">
				<div class="row">
					<div class="col-md-8">
						<div class="mb-3">
							<label class="form-label" for="title"><?php _e('Game title') ?>:</label>
							<input type="text" class="form-control" name="title" value="<?php echo $game->title ?>" required/>
						</div>
						<div class="mb-3">
							<label class="form-label" for="slug"><?php _e('Game slug') ?>:</label>
							<input type="text" class="form-control" name="slug" placeholder="game-title" value="<?php echo $game->slug ?>" minlength="3" maxlength="50" required>
						</div>
						<div class="mb-3">
							<label class="form-label" for="description"><?php _e('Description') ?>:</label>
							<textarea class="form-control" name="description" rows="3" required><?php echo $game->description ?></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label" for="instructions"><?php _e('Instructions') ?>:</label>
							<textarea class="form-control" name="instructions" rows="3"><?php echo $game->instructions ?></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label" for="game_type"><?php _e('Game Type') ?>:</label>
							<select class="form-control" id="game_type" name="game_type" disabled>
								<?php
								echo '<option value="' . $game->game_type . '" selected>' . $game->game_type . '</option>';
								?>
							</select>
						</div>
						<?php
						if($game->game_type == 'html5'){
							?>
							<div class="mb-3">
								<label class="form-label"><?php _e('Game URL') ?>:</label>
								<input type="text" class="form-control" name="url" value="<?php echo $game->url ?>" minlength="3" required>
							</div>
							<?php
						}
						?>
						<div class="mb-3">
							<label class="form-label"><?php _e('Game thumb_1') ?>:</label>
							<input type="text" class="form-control" name="thumb_1" value="<?php echo $game->thumb_1 ?>" minlength="3" required>
						</div>
						<div class="mb-3">
							<label class="form-label"><?php _e('Game thumb_2') ?>:</label>
							<input type="text" class="form-control" name="thumb_2" value="<?php echo $game->thumb_2 ?>" minlength="3" required>
						</div>
						<div class="mb-3">
							<label class="form-label"><?php _e('Game small thumbnail') ?>:</label>
							<input type="text" class="form-control" name="thumb_small" value="<?php echo $game->thumb_small ?>" minlength="3">
						</div>
						<div class="mb-3">
							<label class="form-label" for="width"><?php _e('Game width') ?>:</label>
							<input type="number" class="form-control" name="width" value="<?php echo $game->width ?>" required/>
						</div>
						<div class="mb-3">
							<label class="form-label" for="height"><?php _e('Game height') ?>:</label>
							<input type="number" class="form-control" name="height" value="<?php echo $game->height ?>" required/>
						</div>
						<div class="mb-3">
							<label class="form-label" for="category"><?php _e('Category') ?>:</label>
							<select multiple class="form-control" name="category[]" size="8" required/>
							<?php
							$selected_categories = commas_to_array($game->category);
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
							<input type="text" class="form-control" name="tags" value="<?php echo $game->get_tags() ?>" id="tags-upload" placeholder="<?php _e('Separated by comma') ?>">
						</div>
						<div class="tag-list">
							<?php
							$tag_list = get_tags('usage');
							if(count($tag_list)){
								echo '<div class="mb-3">';
								foreach ($tag_list as $tag_name) {
									echo '<span class="badge rounded-pill bg-secondary btn-tag" data-target="tags-upload" data-value="'.$tag_name.'">'.$tag_name.'</span>';
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
										<label class="form-label" for="<?php echo $field['field_key'] ?>"><?php _e($field['title']) ?>:
											<br>
											<small class="fst-italic text-secondary"><?php echo $field['field_key'] ?></small>
										</label>
										<?php
										$default_value = $game->getExtraField($field['field_key']);
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
					<input id="is_mobile" type="checkbox" name="is_mobile" <?php echo (isset($game->is_mobile) ? filter_var($game->is_mobile, FILTER_VALIDATE_BOOLEAN) : true) ? 'checked' : ''; ?>>
					<label class="form-label" for="is_mobile"><?php _e('Is mobile compatible') ?></label><br>
					<input id="published" type="checkbox" name="published" <?php echo (isset($game->published) ? filter_var($game->published, FILTER_VALIDATE_BOOLEAN) : true) ? 'checked' : ''; ?>>
					<label class="form-label" for="published"><?php _e('Published') ?></label><br>
					<p style="margin-left: 20px;" class="text-secondary">
						<?php _e('If unchecked, this game will set as Draft.') ?>
					</p>
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