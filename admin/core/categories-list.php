<div class="row">
	<div class="col-lg-8">
		<div class="section">
			<ul class="category-list">
				<?php
				$results = array();

				$data = Category::getList();
				$categories = $data['results'];

				if($data['totalRows'] > 0){
					foreach ($categories as $cat) {
						echo '<li class="category-item d-flex align-items-center">';
						if($cat->priority<0){
							echo '<span style="opacity: 0.3;">'.esc_string($cat->name).'</span>';
						}
						else{
							echo esc_string($cat->name);
						}
						$count = Category::getCategoryCount($cat->id);
						if($count > 0){
							echo '<span class="badge badge-primary badge-pill">';
							echo esc_int($count);
							echo '</span>';
						}
						echo '<div style="margin-left: auto;">';
						echo '<span class="actions"><a class="editcategory" href="dashboard.php?viewpage=categories&slug=edit&id='.esc_int($cat->id).'"><i class="fa fa-pencil-alt circle" aria-hidden="true"></i></a><a class="remove-category text-danger" href="#" id="'.esc_int($cat->id).'"><i class="fa fa-trash circle" aria-hidden="true"></i></a></span>';
						echo '</div></li>';
					}
				} else {
					_e('No categories found!');
				}

				?>
			</ul>
			<?php
			if(count($categories) > 0){
				?>
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="reset-priority">
					<div class="mb-3">
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Reset Priority') ?></button>
					</div>
				</form>
				<?php
			}
			?>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="section">
			<form id="form-newcategory" action="request.php" method="post">
				<input type="hidden" name="action" value="newCategory">
				<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=categories">
				<div class="mb-3">
					<label class="form-label" for="category"><?php _e('Add new category') ?>:</label>
					<input type="text" class="form-control" name="name" placeholder="Name" value="" minlength="2" maxlength="30" required>
				</div>
				<div class="mb-3">
					<label class="form-label" for="description"><?php _e('Description') ?>:</label>
					<textarea type="text" class="form-control" name="description" rows="3" placeholder="(Optional) Category description"></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="meta_description"><?php _e('Meta Description') ?>:</label>
					<textarea class="form-control" name="meta_description" rows="3" placeholder="(Optional) Category meta description"></textarea>
				</div>
				<?php
					if(CUSTOM_SLUG){ ?>
					<div class="mb-3">
						<label class="form-label" for="slug"><?php _e('Category Slug') ?>:</label>
						<input type="text" class="form-control" name="slug" placeholder="adventure-game" value="" minlength="3" maxlength="30" required>
					</div>
					<?php }
				?>
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
				<button type="submit" class="btn btn-primary btn-md"><?php _e('Add') ?></button>
			</form>
		</div>
	</div>
</div>