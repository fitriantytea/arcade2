<div class="row">
	<div class="col">
		<form class="has-validation">
			<input type="hidden" name="viewpage" value="gamelist" />
			<input type="hidden" name="action" value="search" />
			<div class="input-group has-validation">
				<input class="form-control rounded has-icon" type="text" placeholder="<?php _e('Search game') ?>..." name="key" minlength="2" required />
				<span class="input-icon">
					<i class="fa fa-search"></i>
				</span>
			</div>
		</form>
	</div>
	<div class="col">
		<form>
			<input type="hidden" name="viewpage" value="gamelist" />
			<input type="hidden" name="action" value="category" />
			<div class="input-group">
				<select name="key" class="form-select" onchange="this.form.submit()">
					<option value="" disabled selected hidden><?php _e('Category') ?></option>
					<?php
					$cur_cat_name = null;
					if(isset($_GET['action'])){
						if($_GET['action'] == 'category'){
							$cur_cat_name = esc_string($_GET['key']);
						}
					}
					$selected = '';
					$results = array();
					$data = Category::getList();
					$categories = $data['results'];
					foreach ($categories as $cat) {
						if(!is_null($cur_cat_name) && $cur_cat_name == $cat->name){
							$selected = 'selected';
						} else {
							$selected = '';
						}
						echo '<option '.$selected.'>'.ucfirst($cat->name).'</option>';
					}
					?>
				</select>
			</div>
		</form>
	</div>
	<div class="col">
		<form class="has-validation">
			<input type="hidden" name="viewpage" value="gamelist" />
			<input type="hidden" name="action" value="source" />
			<div class="input-group has-validation">
				<input class="form-control rounded has-icon" type="text" placeholder="<?php _e('Source') ?>" name="key" minlength="2" required />
				<span class="input-icon">
					<i class="fa fa-code"></i>
				</span>
			</div>
		</form>
	</div>
	<div class="col">
		<form class="has-validation">
			<input type="hidden" name="viewpage" value="gamelist" />
			<input type="hidden" name="action" value="type" />
			<div class="input-group">
				<select class="form-control" id="game_type" name="key" onchange="this.form.submit()">
					<option value="" disabled selected hidden><?php _e('Game Type') ?></option>
					<?php
					$_game_types = get_game_types();
					foreach ($_game_types as $type_id => $type) {
						$selected = '';
						echo '<option value="' . $type_id . '" ' . $selected . '>' . $type['name'] . '</option>';
					}
					?>
				</select>
			</div>
		</form>
	</div>
</div>
		
<br>
<div class="section section-full">
	<div class="table-responsive">
		<table class="table custom-table">
			<thead>
				<tr>
					<th>#</th>
					<th><?php _e('ID') ?></th>
					<th><?php _e('Thumbnail') ?></th>
					<th><?php _e('Mobile') ?></th>
					<th><?php _e('Game Name') ?></th>
					<th><?php _e('Category') ?></th>
					<th><?php _e('Source') ?></th>
					<th><?php _e('Type') ?></th>
					<th><?php _e('URL') ?></th>
					<th><?php _e('Action') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$index = 0;
				$cur_page = 1;
				if(isset($_GET['page'])){
					$cur_page = $_GET['page'];
				}
				$data = null;
				if(isset($_GET['action'])){
					if($_GET['action'] == 'search'){
						$data = Game::searchGame($_GET['key'], 20, 20*($cur_page-1));
					} elseif($_GET['action'] == 'category'){
						$cat_id = Category::getIdByName($_GET['key']);
						if(!is_null($cat_id)){
							$data = Category::getListByCategory($cat_id, 20, 20*($cur_page-1));
						}
					} elseif($_GET['action'] == 'source'){
						$data = Game::getListBySource($_GET['key'], 20, 20*($cur_page-1));
					} elseif($_GET['action'] == 'type'){
						$data = Game::getListByType($_GET['key'], 20, 20*($cur_page-1));
					}
				}
				if(is_null($data)) {
					$data = get_game_list('new', 20, 20*($cur_page-1));
				}
				$games = $data['results'];
				$total_game = $data['totalRows'];
				$total_page = $data['totalPages'];
				foreach ( $games as $game ) {
					$index++;
					$categories = $game->category;
					?>
				<tr id="game-<?php echo esc_int($game->id)?>">
					<th scope="row"><?php echo esc_int($index+(20*($cur_page-1))); ?></th>
					<td>
						<?php echo esc_int($game->id) ?>
					</td>
					<td><img src="<?php echo get_small_thumb($game) ?>" width="60px" height="auto" class="gamelist"></td>
					<td class="td-ellipsis">
						<?php if($game->is_mobile){
							echo '<i class="text-success fas fa-check-circle"></i>';
						} else {
							echo '<i class="text-danger fas fa-times-circle"></i>';
						} ?>
					</td>
					<td class="td-ellipsis">
						<?php echo esc_string($game->title) ?>
					</td>
					<td class="td-ellipsis"><span class="categories"><?php echo esc_string($categories)?></span></td>
					<td>
						<?php echo esc_string($game->source) ?>
					</td>
					<td>
						<?php echo esc_string($game->game_type) ?>
					</td>
					<td><a href="<?php echo get_permalink('game', $game->slug) ?>" target="_blank"><?php _e('Play') ?></a></td>
					<td>
						<span class="actions">
							<a class="editgame" href="dashboard.php?viewpage=gamelist&slug=edit&id=<?php echo esc_int($game->id)?>"><i class="fa fa-pencil-alt circle" aria-hidden="true"></i></a>
							<a class="deletegame" data-id="<?php echo esc_int($game->id) ?>" href="#"><i class="fa fa-trash circle" aria-hidden="true"></i></a>
						</span>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="general-wrapper">
		<p><?php _e('%a games in total.', esc_int($total_game)) ?></p>
		<div class="pagination-wrapper">
			<nav aria-label="Page navigation">
				<ul class="pagination pg-blue justify-content-center">
					<?php
					$cur_page = 1;
					$params = '';
					if(isset($_GET['page'])){
						$cur_page = $_GET['page'];
					}
					if(isset($_GET['action'])){
						$params .= "&action=".$_GET['action'];
					}
					if(isset($_GET['key'])){
						$params .= "&key=".$_GET['key'];
					}
					if($total_page){
						$max = 8;
						$start = 0;
						$end = $max;
						if($max > $total_page){
							$end = $total_page;
						} else {
							$start = $cur_page-$max/2;
							$end = $cur_page+$max/2;
							if($start < 0){
								$start = 0;
							}
							if($end - $start < $max-1){
								$end = $max;
							}
							if($end > $total_page){
								$end = $total_page;
							}
						}
						if($start > 0){
							echo '<li class="page-item"><a class="page-link" href="'.DOMAIN.'admin/dashboard.php?viewpage=gamelist'.$params.'&page=1">1</a></li>';
							echo('<li class="page-item disabled"><span class="page-link">...</span></li>');
						}
						for($i = $start; $i<$end; $i++){
							$disabled = '';
							if($cur_page){
								if($cur_page == ($i+1)){
									$disabled = 'active disabled';
								}
							}
							echo '<li class="page-item '.$disabled.'"><a class="page-link" href="'.DOMAIN.'admin/dashboard.php?viewpage=gamelist'.$params.'&page='.($i+1).'">'.($i+1).'</a></li>';
						}
						if($end < $total_page){
							echo('<li class="page-item disabled"><span class="page-link">...</span></li>');
							echo '<li class="page-item"><a class="page-link" href="'.DOMAIN.'admin/dashboard.php?viewpage=gamelist'.$params.'&page='.($total_page).'">'.($total_page).'</a></li>';
						}
					}
					?>
				</ul>
			</nav>
			<div class="text-center">
				<form>
					<input type="hidden" value="gamelist" name="viewpage" />
					<div class="mb-3">
						<label class="form-label" for="page">Page:</label>
						<select name="page" required>
							<?php
							if($total_page){
								for($i = 0; $i < $total_page; $i++ ){
									$selected = '';
									if(($i+1) == $cur_page){
										$selected = 'selected';
									}
									echo('<option value="'.($i+1).'" '.$selected.'>'.($i+1).'</option>');
								}
							}
							?>
						</select>
						<input type="submit" value="Go"/>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<br>
<!-- DRAFT -->
<?php

$index = 0;
$data = Game::getDraftList();
$games = $data['results'];
$total_game = $data['totalRows'];
$total_page = $data['totalPages'];

if(count($games) > 0){

?>
	<div class="section section-full">
		<h3 class="section-title">
			<?php _e('Draft') ?>
		</h3>
		<div class="table-responsive">
			<table class="table custom-table">
				<thead>
					<tr>
						<th>#</th>
						<th><?php _e('ID') ?></th>
						<th><?php _e('Thumbnail') ?></th>
						<th><?php _e('Mobile') ?></th>
						<th><?php _e('Game Name') ?></th>
						<th><?php _e('Category') ?></th>
						<th><?php _e('Source') ?></th>
						<th><?php _e('URL') ?></th>
						<th><?php _e('Action') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $games as $game ) {
						$index++;
						$categories = $game->category;
						?>
					<tr id="game-<?php echo esc_int($game->id)?>">
						<th scope="row"><?php echo esc_int($index); ?></th>
						<td>
							<?php echo esc_int($game->id) ?>
						</td>
						<td><img src="<?php echo get_small_thumb($game) ?>" width="60px" height="auto" class="gamelist"></td>
							<td class="td-ellipsis">
							<?php if($game->is_mobile){
								echo '<i class="text-success fas fa-check-circle"></i>';
							} else {
								echo '<i class="text-danger fas fa-times-circle"></i>';
							} ?>
						</td>
						<td class="td-ellipsis">
							<?php echo esc_string($game->title) ?>
						</td>
						<td class="td-ellipsis"><span class="categories"><?php echo esc_string($categories)?></span></td>
						<td>
							<?php echo esc_string($game->source) ?>
						</td>
						<td><a href="<?php echo get_permalink('game', $game->slug) ?>" target="_blank"><?php _e('Play') ?></a></td>
						<td>
							<span class="actions">
								<a class="editgame" href="dashboard.php?viewpage=gamelist&slug=edit&id=<?php echo esc_int($game->id)?>"><i class="fa fa-pencil-alt circle" aria-hidden="true"></i></a>
								<a class="deletegame" data-id="<?php echo esc_int($game->id) ?>" href="#"><i class="fa fa-trash circle" aria-hidden="true"></i></a>
							</span>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
<?php } ?>