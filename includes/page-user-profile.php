<?php

// Page user profile page

$exceeded_value = $rank_values[$cur_user->level-1];
$max_value = 0;
$min_value = $cur_user->xp-$exceeded_value;
if($cur_user->level < count($rank)){
	$max_value = $rank_values[$cur_user->level]-$exceeded_value;
} else {
	$max_value = 100;
	$min_value = 100;
}
$percentage_rank_progress = (100/($max_value))*$min_value;

?>
<div class="user-page">
	<div class="container">
		<h3 class="single-title"><?php _e('User Profile') ?></h3>
		<div class="row">
			<div class="col-md-4">
				<div class="section">
					<div class="text-center">
						<br>
						<div class="profile-photo">
							<img src="<?php echo get_user_avatar($cur_user->username) ?>">
						</div>
						<div class="profile-username">
							<?php echo $cur_user->username ?>
						</div>
						<div>
							<?php _e($cur_user->gender) ?>
						</div>
						<div class="profile-join">
							<?php _e('Joined %a', $cur_user->join_date) ?>
						</div>
						<div class="profile-bio text-secondary">
							"<?php echo $cur_user->bio ?>"
						</div>
						<br>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="section">
					<h3 class="section-title"><?php _e('Level') ?></h3>
					<img src="<?php echo DOMAIN.'images/ranks/level-'.$cur_user->level.'.png' ?>" class="level-badge">
					<strong><?php echo $cur_user->rank ?> (Lv.<?php echo $cur_user->level ?>)</strong>
					<p class="text-secondary"><?php _e('This player have exceeded %a xp', $rank[$cur_user->rank]) ?></p>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $cur_user->xp ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $percentage_rank_progress ?>%">
							<span class="sr-only"><?php echo $percentage_rank_progress ?>% <?php _e('Complete') ?></span>
						</div>
					</div>
				</div>
				<?php if(!$is_visitor){ ?>
					<div class="section">
						<h3 class="section-title"><?php _e('Favorite Games') ?></h3>
						<div class="profile-gamelist-horizontal favorite-gamelist">

							<?php

							if($cur_user){
								$favorite_games = $cur_user->favoriteGames();
								$total_favorite = count($favorite_games);
								if($total_favorite > 0){
									?>
									<button class="btn btn-left btn-arrow" id="f_prev">
										<i class="fa fa-chevron-left chevron-left" aria-hidden="true"></i>
									</button>
									<button class="btn btn-right btn-arrow" id="f_next">
										<i class="fa fa-chevron-right chevron-right" aria-hidden="true"></i>
									</button>
									<ul>
										<?php
										if($total_favorite > 15){
										//Max games to shown = 15
											$favorite_games = array_slice($favorite_games, $total_favorite-15, $total_favorite-1);
										}
										$games = [];
										foreach ($favorite_games as $item) {
											$game = new Game;
											$res = $game->getById($item['game_id']);
											if($res){
												$games[] = $res;
											}
										}
										foreach ($games as $game) {
											?>
											<li><div class="profile-game-item">
												<a href="<?php echo get_permalink('game', $game->slug) ?>">
													<div class="list-thumbnail"><img src="<?php echo get_small_thumb($game) ?>" class="small-thumb" alt="<?php echo esc_string($game->title) ?>"></div>
												</a>
											</div></li>

											<?php
										}
										?>
									</ul>
									<?php
								} else {
									echo('<p class="text-secondary">No record!</p>');
								}
							}
							?>
						</div>
					</div>
					<div class="section">
						<h3 class="section-title"><?php _e('Liked Games') ?></h3>
						<div class="profile-gamelist-horizontal profile-gamelist">

							<?php

							if($cur_user){
								if(isset($cur_user->data['likes']) && count($cur_user->data['likes']) > 0){
									?>

									<button class="btn btn-left btn-arrow" id="btn_prev">
										<i class="fa fa-chevron-left chevron-left" aria-hidden="true"></i>
									</button>
									<button class="btn btn-right btn-arrow" id="btn_next">
										<i class="fa fa-chevron-right chevron-right" aria-hidden="true"></i>
									</button>
									<ul>

										<?php
										$data = $cur_user->data['likes'];
										$total_likes = count($data);
										if($total_likes > 15){
										//Max likes to shown = 15
											$data = array_slice($data, $total_likes-15, $total_likes-1);
										}
										$games = [];
										foreach ($data as $id) {
											$game = new Game;
											$res = $game->getById($id);
											if($res){
												$games[] = $res;
											}
										}
										foreach ($games as $game) {
											?>
											<li><div class="profile-game-item">
												<a href="<?php echo get_permalink('game', $game->slug) ?>">
													<div class="list-thumbnail"><img src="<?php echo get_small_thumb($game) ?>" class="small-thumb" alt="<?php echo esc_string($game->title) ?>"></div>
												</a>
											</div></li>

											<?php
										}
										?>

									</ul>

									<?php
								} else {
									echo('<p class="text-secondary">No record!</p>');
								}
							}	

							?>
						</div>
					</div>
					<div class="section">
						<h3 class="section-title"><?php _e('Comments') ?></h3>
						<div class="profile-comments">
							<?php
							$sql = 'SELECT * FROM comments WHERE sender_id = :sender_id ORDER BY id DESC LIMIT 30';
							$st = $conn->prepare($sql);
							$st->bindValue(":sender_id", $cur_user->id, PDO::PARAM_INT);
							$st->execute();
							$row = $st->fetchAll(PDO::FETCH_ASSOC);

							if(count($row)){
								foreach ($row as $item) {
									?>
									<div class="profile-comment-item id-<?php echo $item['id'] ?>">
										<div class="comment-text">
											"<?php echo htmlspecialchars($item['comment']) ?>"
										</div>
										<div class="comment-date text-secondary">
											<?php echo $item['created_date'] ?> (Game id <?php echo $item['game_id'] ?>)
										</div>
										<div class="text-danger delete-comment" data-id="<?php echo $item['id'] ?>">
											<?php _e('Delete') ?>
										</div>
									</div>
									<?php
								}
							} else {
								echo('<p class="text-secondary">'._t('No record!').'</p>');
							}
							?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>