<div class="addgame-wrapper" id="fetch">
	<div class="mb-3">
		<label class="form-label"><?php _e('Distributor') ?></label> 
		<select name="distributor" class="form-control" id="distributor-options">
			<option value="" disabled selected hidden><?php _e('Choose game distributor') ?>...</option>
			<option value="#gamedistribution">GameDistribution</option>
			<option value="#gamepix">GamePix</option>
			<option value="#playsaurus">Playsaurus</option>
			<option value="#more-distributors">More</option>
		</select>
	</div>
	<div class="fetch-games tab-container fade" id="gamedistribution">
		<div class="alert alert-warning alert-dismissible fade show" role="alert">You need joined <a href="https://gamedistribution.com/publishers" target="_blank">GameDistribution</a> publisher program to be able to publish their games on your site.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>

		<form id="form-fetch-gamedistribution" class="gamedistribution">
			<div class="mb-3">
				<label class="form-label">Collection</label> 
				<select name="Collection" class="form-control">
					<option selected="selected" value="all">All</option>
					<option value="11">Top Hypercasual</option>
					<option value="8">Ubisoft</option>
					<option value="3">Hot</option>
					<option value="2">Exclusive</option>
					<option value="1">Top Picks</option>
					<option value="4">New</option>
					<option value="5">In Game Purchase</option>
					<option value="6">IceStone</option>
					<option value="7">Ubisoft</option>
					<option value="10">Gameloft</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Category</label> 
				<select name="Category" class="form-control">
					<option selected="selected" value="All">All</option>
					<option value="Puzzle">Puzzle</option>
					<option value="Casual">Casual</option>
					<option value="Adventure">Adventure</option>
					<option value="Hypercasual">Hypercasual</option>
					<option value="Shooter">Shooter</option>
					<option value="Agility">Agility</option>
					<option value="Simulation">Simulation</option>
					<option value="Art">Art</option>
					<option value="Sports">Sports</option>
					<option value="Battle">Battle</option>
					<option value="Match-3">Match-3</option>
					<option value="Strategy">Strategy</option>
					<option value="Care">Care</option>
					<option value=".IO">.IO</option>
					<option value="Boardgames">Boardgames</option>
					<option value="Educational">Educational</option>
					<option value="Cooking">Cooking</option>
					<option value="Bubble Shooter">Bubble Shooter</option>
					<option value="Football">Football</option>
					<option value="Bejeweled">Bejeweled</option>
					<option value="Girls">Girls</option>
					<option value="Cards">Cards</option>
					<option value="Basketball">Basketball</option>
					<option value="Action">Action</option>
					<option value="Quiz">Quiz</option>
					<option value="Arcade">Arcade</option>
					<option value="Combat">Combat</option>
					<option value="Farming">Farming</option>
					<option value="3D">3D</option>
					<option value="Clicker">Clicker</option>
					<option value="Boys">Boys</option>
					<option value="Baby">Baby</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Item</label> 
				<select name="Limit" class="form-control">
					<option selected="selected" value="10">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="40">40</option>
					<option value="70">70</option>
					<option value="100">100</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Offset</label> 
				<select name="Offset" class="form-control">
					<option selected="selected" value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
				</select>
			</div>
			<input type="submit" class="btn btn-primary btn-md" value="<?php _e('Fetch games') ?>"/>
		</form>
	</div>
	<div class="fetch-games tab-container fade" id="gamepix">
		<div class="alert alert-warning alert-dismissible fade show" role="alert">You need joined <a href="https://company.gamepix.com/publishers/" target="_blank">GamePix</a> publisher program to be able to publish their games on your site.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
		<div class="alert alert-warning alert-dismissible fade show" role="alert">This GamePix fetch uses the old API, so new games will not appear. To fetch the latest GamePix games using the new API, use the 'Fetch Games Extended' plugin.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
		<form id="form-fetch-gamepix" class="gamepix">
			<div class="mb-3">
				<label class="form-label">Sort By</label> 
				<select name="Sort" class="form-control">
					<option value="d" selected>Newest</option>
					<option value="q">Most Played</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Category</label> 
				<select name="Category" class="form-control">
					<option value="1">All</option>
					<option value="2">Arcade</option>
					<option value="3">Adventure</option>
					<option value="4">Junior</option>
					<option value="5">Board</option>
					<option value="6">Classic</option>
					<option value="7">Puzzle</option>
					<option value="8">Sports</option>
					<option value="9">Strategy</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Item</label> 
				<select name="Limit" class="form-control">
					<option selected="selected" value="10">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="40">40</option>
					<option value="70">70</option>
					<option value="100">100</option>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label">Offset</label> 
				<select name="Offset" class="form-control">
					<option selected="selected" value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
				</select>
			</div>
			<input type="submit" class="btn btn-primary btn-md" value="<?php _e('Fetch games') ?>"/>
		</form>
	</div>
	<div class="fetch-games tab-container fade" id="playsaurus">
		<form id="form-fetch-playsaurus" class="playsaurus">
			<div class="mb-3">
				<label class="form-label">Item</label> 
				<select name="Limit" class="form-control">
					<option selected="selected" value="100">All</option>
				</select>
			</div>
			<input type="submit" class="btn btn-primary btn-md" value="<?php _e('Fetch games') ?>"/>
		</form>
	</div>
	<div class="fetch-games tab-container fade" id="more-distributors">
		<p><b>You can fetch or add game from other HTML5 game distributors with "Fetch Games Extended" plugin.</b></p>
		<p>If "Fetch Games Extended" plugin not installed. follow step below:</p>
		<p>
			Click "Plugin" tab (Left sidebar) > Manage Plugins > Load Plugin Repository > ( Add ) Fetch Games Extended.
		</p>
		<p>
			Then you can access it under plugin page.
		</p>

	</div>
	<br>
	<div class="fetch-loading" style="display: none;">
		<h4><?php _e('Fetching games') ?> ...</h4>
	</div>
	<div id="action-info"></div>
	<div class="fetch-list mb-3" style="display: none;">
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
						<th>#</th>
						<th><?php _e('Thumbnail') ?></th>
						<th><?php _e('Game name') ?></th>
						<th><?php _e('Category') ?></th>
						<th><?php _e('URL') ?></th>
						<th><?php _e('Action') ?></th>
					</tr>
				</thead>
				<tbody id="gameList">
				</tbody>
			</table>
		</div>
		<button class="btn btn-primary btn-md" id="add-all"><?php _e('Add all') ?></button>
	</div>
	<div class="div-stop" style="display: none;">
		<button class="btn btn-danger btn-md" id="stop-add"><?php _e('Stop') ?></button>
	</div>
</div>