<?php
class Game
{
	public $id = null;
	public $createdDate = null;
	public $title = null;
	public $description = null;
	public $instructions = null;
	public $category = null;
	public $source = null;
	public $game_type = 'html5';
	public $thumb_1 = null;
	public $thumb_2 = null;
	public $thumb_small = '';
	public $url = null;
	public $width = null;
	public $height = null;
	public $tags = null;
	public $views = null;
	public $upvote = null;
	public $downvote = null;
	public $slug = null;
	public $last_modified = null;
	public $is_mobile = true;
	public $published = true;
	public $fields = '';
	public $extra_fields = null;

	public function __construct($data = array())
	{
		if (isset($data['id'])) $this->id = (int)$data['id'];
		if (isset($data['createddate'])) $this->createdDate = $data['createddate'];
		if (isset($data['last_modified'])) $this->last_modified = $data['last_modified'];
		if (isset($data['title'])) {
			if(get_setting_value('disable_iconv')){
				$this->title = $data['title'];
			} else {
				// Convert $data['title'] to UTF-8 if it is not already
				//
				// Temporary disabled since v1.9.2, causing encoding bug for unicode language characters
				// This iconv() issue can be fixed by set DB collation to utf8_general_ci
				// if (mb_detect_encoding($data['title'], 'UTF-8, ISO-8859-1', true) !== 'UTF-8') {
				// 	$data['title'] = mb_convert_encoding($data['title'], 'UTF-8', 'ISO-8859-1');
				// }
				// $this->title = iconv("utf-8", "utf-8//ignore", $data['title']);
				$this->title = $data['title'];
			}
		}
		if (isset($data['description'])) {
			if(get_setting_value('disable_iconv')){
				$this->description = $data['description'];
			} else {
				// Convert $data['description'] to UTF-8 if it is not already
				if (mb_detect_encoding($data['description'], 'UTF-8, ISO-8859-1', true) !== 'UTF-8') {
					$data['description'] = mb_convert_encoding($data['description'], 'UTF-8', 'ISO-8859-1');
				}
				$this->description = iconv("utf-8", "utf-8//ignore", $data['description']);
			}
		}
		if (isset($data['instructions'])) {
			if(get_setting_value('disable_iconv')){
				$this->instructions = $data['instructions'];
			} else {
				// Convert $data['instructions'] to UTF-8 if it is not already
				if (mb_detect_encoding($data['instructions'], 'UTF-8, ISO-8859-1', true) !== 'UTF-8') {
					$data['instructions'] = mb_convert_encoding($data['instructions'], 'UTF-8', 'ISO-8859-1');
				}
				$this->instructions = iconv("utf-8", "utf-8//ignore", $data['instructions']);
			}
		}
		if (isset($data['category'])) $this->category = $data['category'];
		if (isset($data['source'])) $this->source = $data['source'];
		if (isset($data['game_type'])) $this->game_type = $data['game_type'];
		if (isset($data['thumb_1'])) $this->thumb_1 = $data['thumb_1'];
		if (isset($data['thumb_2'])) $this->thumb_2 = $data['thumb_2'];
		if (isset($data['thumb_small'])) $this->thumb_small = $data['thumb_small'];
		if (isset($data['url'])) $this->url = $data['url'];
		if (isset($data['width'])) $this->width = $data['width'];
		if (isset($data['height'])) $this->height = $data['height'];
		if (isset($data['tags'])) $this->tags = $data['tags'];
		if (isset($data['views'])) $this->views = $data['views'];
		if (isset($data['upvote'])) $this->upvote = $data['upvote'];
		if (isset($data['downvote'])) $this->downvote = $data['downvote'];
		if (isset($data['fields'])) $this->fields = $data['fields'];
		if (isset($data['is_mobile'])) $this->is_mobile = filter_var($data['is_mobile'], FILTER_VALIDATE_BOOLEAN) ? true : false;
		if (isset($data['published'])) $this->published = $data['published'];
		if (isset($data['slug'])){
			$this->slug = strtolower(str_replace(' ', '-', $data["slug"]));
		} else {
			if (isset($data['title'])) $this->slug = strtolower(str_replace(' ', '-', $data["title"]));
		}
		if(isset($data['extra_fields'])){
			if(is_array($data['extra_fields'])){
				$data['extra_fields'] = json_encode($data['extra_fields']);
			}
			$this->extra_fields = $data['extra_fields'];
		}
	}

	public function storeFormValues($params)
	{
		$this->__construct($params);
		$this->createdDate = date('Y-m-d H:i:s');
		// Parse and store the publication date
		if (isset($params['cratedDate']))
		{

			/*if ( count($createdDate) == 3 ) {
			list ( $y, $m, $d ) = $createdDate;
			$this->createdDate = mktime ( 0, 0, 0, $m, $d, $y );
			}*/
		}
	}

	public static function getById($id)
	{
		$conn = open_connection();
		$id = (int)$id;
		$sql = "SELECT *, UNIX_TIMESTAMP(createdDate) AS createdDate FROM games WHERE id = $id limit 1";
		$cached_result = null;
		if(is_cached_query_allowed()){
			$data_value = get_cached_query($sql);
			if(!is_null($data_value)){
				$cached_result = json_decode($data_value, true);
			}
		}
		$row = null;
		if(is_null($cached_result)){
			$st = $conn->prepare($sql);
			$st->execute();
			$row = $st->fetch();
			if(is_cached_query_allowed()){
		    	set_cached_query($sql, json_encode($row));
		    }
		} else {
			$row = $cached_result;
		}
		if ($row) return new Game($row); //$row
	}

	public static function getByTitle($title)
	{
		$conn = open_connection();
		$sql = 'SELECT * FROM games WHERE title = :title';
		$st = $conn->prepare($sql);
		$st->bindValue(":title", $title, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new Game($row);
	}

	public static function getBySlug($slug)
	{
		$conn = open_connection();
		$slug = $conn->quote($slug);
		$sql = "SELECT * FROM games WHERE slug = $slug LIMIT 1";
		$cached_result = null;
		if(is_cached_query_allowed()){
			$data_value = get_cached_query($sql);
			if(!is_null($data_value)){
				$cached_result = json_decode($data_value, true);
			}
		}
		$row = null;
		if(is_null($cached_result)){
			$st = $conn->prepare($sql);
			//$st->bindValue(":slug", $slug, PDO::PARAM_STR);
			$st->execute();
			$row = $st->fetch();
			if(is_cached_query_allowed()){
				set_cached_query($sql, json_encode($row));
			}
		} else {
			$row = $cached_result;
		}
		if ($row) return new Game($row);
	}

	public static function getList(int $amount = 1000, $sort = 'id DESC', int $page = 0, $count = true)
	{
		$additional_condition = '';
		if(defined('IS_VISITOR_PAGE')){
			if(get_setting_value('hide_pc_on_mobile') && is_mobile_device()){
				$additional_condition = 'AND is_mobile = 1';
			}
		}
		$sql = "SELECT * FROM games WHERE published = 1 $additional_condition ORDER BY $sort LIMIT $amount OFFSET $page";
		$cached_result = null;
		if(is_cached_query_allowed() && $sort != 'RAND()'){
			$data_value = get_cached_query($sql);
			if(!is_null($data_value)){
				$cached_result = json_decode($data_value, true);
			}
		}
		$conn = open_connection();
		$rows = null;
		if(is_null($cached_result)){
			$st = $conn->prepare($sql);
			$st->execute();
			$rows = $st->fetchAll(PDO::FETCH_ASSOC);
			if(is_cached_query_allowed() && $sort != 'RAND()'){
				set_cached_query($sql, json_encode($rows));
			}
		} else {
			$rows = $cached_result;
		}
		$list = array();
		$total = count($rows);
		for($i=0; $i<$total; $i++)
		{
			$game = new Game($rows[$i]);
			$list[] = $game;
		}
		$totalRows = 0;
		if($count){
			$sql = "SELECT count(*) FROM games";
			$cached_result2 = null;
			if(is_cached_query_allowed()){
				$data_value = get_cached_query($sql);
				if(!is_null($data_value)){
					$cached_result2 = json_decode($data_value, true);
				}
			}
			if(is_null($cached_result2)){
				$totalRows = $conn->query($sql)->fetchColumn();
				if(is_cached_query_allowed()){
			    	set_cached_query($sql, json_encode($totalRows));
			    }
			} else {
				$totalRows = $cached_result2;
			}
		} else {
			$totalRows = count($list);
		}
		$totalPages = 0;
		if (count($list))
		{
			$totalPages = ceil($totalRows / $amount);
		}
		$result = (array(
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		));
		return $result;
	}

	public static function getListByType($game_type, int $amount = 20, int $page = 0){
		$additional_condition = '';
		if(defined('IS_VISITOR_PAGE')){
			if(get_setting_value('hide_pc_on_mobile') && is_mobile_device()){
				$additional_condition = 'AND is_mobile = 1';
			}
		}
		$conn = open_connection();
		$sql = "SELECT * FROM games WHERE game_type = :game_type
			AND published = 1 $additional_condition ORDER BY id DESC LIMIT $amount OFFSET $page";

		$st = $conn->prepare($sql);
		$st->bindValue(":game_type", $game_type, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetchAll();
		$list = array();
		foreach ($row as $item) {
			$list[] = new Game($item);
		}
		$sql = "SELECT count(*) FROM games WHERE source = :source";
		$st = $conn->prepare($sql);
		$st->bindValue(":source", $game_type, PDO::PARAM_STR);
		$st->execute();
		$totalRows = $st->fetchColumn();
		$totalPages = 0;
		if (count($list))
		{
			$totalPages = ceil($totalRows / $amount);
		}

		return (array(
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		));
	}

	public static function getDraftList(int $amount = 1000, $sort = 'id DESC', int $page = 0, $count = true)
	{
		// Get games on draft or unpublished
		$conn = open_connection();
		$sql = "SELECT * FROM games WHERE published = 0
			ORDER BY " . $sort . " LIMIT $amount OFFSET $page";

		$st = $conn->prepare($sql);
		$st->execute();
		$list = array();
		while ($row = $st->fetch())
		{
			$game = new Game($row);
			$list[] = $game;
		}
		$totalRows = 0;
		if($count){
			$totalRows = $conn->query('SELECT count(*) FROM games')->fetchColumn();
		} else {
			$totalRows = count($list);
		}
		$totalPages = 0;
		if (count($list))
		{
			$totalPages = ceil($totalRows / $amount);
		}
		return (array(
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		));
	}

	public function getSimilarGames(int $amount = 12){
		// Get list of similar games based on current game categories
		if(!is_null($this->id)){
			$current_game_id = $this->id;
			$conn = open_connection();
			$sql = "SELECT g.id
				FROM games g
				JOIN cat_links cl ON g.id = cl.gameid
				WHERE cl.categoryid IN (
					SELECT categoryid
					FROM cat_links
					WHERE gameid = $current_game_id
				) AND g.id != $current_game_id
				GROUP BY g.id
				ORDER BY COUNT(cl.categoryid) DESC, rand()
				LIMIT $amount";

			$stmt = $conn->prepare($sql);
			$stmt->execute();
			$gameIDs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
			//
			$is_only_mobile = false;
			if(defined('IS_VISITOR_PAGE')){
				if(get_setting_value('hide_pc_on_mobile') && is_mobile_device()){
					$is_only_mobile = true;
				}
			}
			//
			$list = array();
			foreach ($gameIDs as $gameId) {
				if (count($list) < $amount) {
					$game = new Game;
					$res = $game->getById($gameId);
					if ($res && $res->published) {
						if($is_only_mobile && $res->is_mobile){
							array_push($list, $res);
						}
						if(!$is_only_mobile){
							array_push($list, $res);
						}
					}
				} else {
					break;
				}
			}
			return array(
				"results" => $list,
				"totalRows" => count($list),
				"totalPages" => 1
			);
		} else {
			echo 'Error 191';
		}
	}

	public static function getTotalGames(){
		// Get total games amount excluding draft
		$conn = open_connection();
		$sql = "SELECT COUNT(*) FROM games WHERE published = 1";

		$st = $conn->prepare($sql);
		$st->execute();
		return $st->fetchColumn();
	}

	public static function searchGame($keyword, int $amount = 20, int $page = 0){
		$additional_condition = '';
		if(defined('IS_VISITOR_PAGE')){
			if(get_setting_value('hide_pc_on_mobile') && is_mobile_device()){
				$additional_condition = 'AND is_mobile = 1';
			}
		}
		$conn = open_connection();
		$sql = "SELECT * FROM games WHERE title LIKE :keyword
			AND published = 1 $additional_condition ORDER BY id DESC LIMIT $amount OFFSET $page";

		$st = $conn->prepare($sql);
		$st->bindValue(":keyword", '%'. $keyword .'%', PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetchAll();
		$list = array();
		foreach ($row as $item) {
			$list[] = new Game($item);
		}
		$sql = "SELECT count(*) FROM games WHERE title LIKE :keyword";
		$st = $conn->prepare($sql);
		$st->bindValue(":keyword", '%'. $keyword .'%', PDO::PARAM_STR);
		$st->execute();
		$totalRows = $st->fetchColumn();
		$totalPages = 0;
		if (count($list))
		{
			$totalPages = ceil($totalRows / $amount);
		}
		
		return (array(
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		));
	}

	public static function getListBySource($source, int $amount = 20, int $page = 0){
		$additional_condition = '';
		if(defined('IS_VISITOR_PAGE')){
			if(get_setting_value('hide_pc_on_mobile') && is_mobile_device()){
				$additional_condition = 'AND is_mobile = 1';
			}
		}
		$conn = open_connection();
		$sql = "SELECT * FROM games WHERE source = :source
			AND published = 1 $additional_condition ORDER BY id DESC LIMIT $amount OFFSET $page";

		$st = $conn->prepare($sql);
		$st->bindValue(":source", $source, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetchAll();
		$list = array();
		foreach ($row as $item) {
			$list[] = new Game($item);
		}
		$sql = "SELECT count(*) FROM games WHERE source = :source";
		$st = $conn->prepare($sql);
		$st->bindValue(":source", $source, PDO::PARAM_STR);
		$st->execute();
		$totalRows = $st->fetchColumn();
		$totalPages = 0;
		if (count($list))
		{
			$totalPages = ceil($totalRows / $amount);
		}

		return (array(
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		));
	}

	public static function getListByTag($tag, int $amount = 1000, $sort = 'id DESC', int $offset = 0, $count = true)
	{
		$additional_condition = '';
		if(defined('IS_VISITOR_PAGE')){
			if(get_setting_value('hide_pc_on_mobile') && is_mobile_device()){
				$additional_condition = 'AND games.is_mobile = 1';
			}
		}
		$allowed_sort_columns = ['id DESC', 'id ASC'];
		$sort_column = in_array($sort, $allowed_sort_columns) ? $sort : 'id DESC';
		// Calculate the OFFSET based on page number and amount per page
		$conn = open_connection();
		$tag = $conn->quote($tag);
		$sql = "SELECT games.* 
				FROM games
				INNER JOIN tag_links ON games.id = tag_links.game_id
				INNER JOIN tags ON tag_links.tag_id = tags.id
				WHERE tags.name = $tag AND games.published = 1 $additional_condition
				ORDER BY $sort_column LIMIT $amount OFFSET $offset";
		$cached_result = null;
		if(is_cached_query_allowed()){
			$data_value = get_cached_query($sql);
			if(!is_null($data_value)){
				$cached_result = json_decode($data_value, true);
			}
		}
		$rows = null;
		if(is_null($cached_result)){
			$st = $conn->prepare($sql);
			$st->execute();
			$rows = $st->fetchAll(PDO::FETCH_ASSOC);
			if(is_cached_query_allowed()){
		    	set_cached_query($sql, json_encode($rows));
		    }
		} else {
			$rows = $cached_result;
		}
		$list = [];
		$total = count($rows);
		for($i=0; $i<$total; $i++)
		{
			$game = new Game($rows[$i]);
			$list[] = $game;
		}
		$totalRows = 0;
		if($count){
			// Adjust the count query to include the same conditions as your main query
			$countSql = "SELECT COUNT(*) 
						 FROM games
						 INNER JOIN tag_links ON games.id = tag_links.game_id
						 INNER JOIN tags ON tag_links.tag_id = tags.id
						 WHERE tags.name = $tag AND games.published = 1 $additional_condition";
			$cached_result2 = null;
			if(is_cached_query_allowed()){
				$data_value = get_cached_query($countSql);
				if(!is_null($data_value)){
					$cached_result2 = json_decode($data_value, true);
				}
			}
			if(is_null($cached_result2)){
				$countSt = $conn->prepare($countSql);
				$countSt->execute();
				$totalRows = $countSt->fetchColumn();
				if(is_cached_query_allowed()){
			    	set_cached_query($countSql, json_encode($totalRows));
			    }
			} else {
				$totalRows = $cached_result2;
			}
		} else {
			$totalRows = count($list);
		}
		$totalPages = ceil($totalRows / $amount);
		return [
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		];
	}

	public static function update_views($slug)
	{
		$conn = open_connection();
		$sql = 'UPDATE games SET views = views + 1 WHERE slug = :slug';
		$st = $conn->prepare($sql);
		$st->bindValue(":slug", $slug, PDO::PARAM_STR);
		$st->execute();
		// Update trends
		$sql = 'SELECT slug FROM trends WHERE slug = :slug AND created = :created';
		$st = $conn->prepare($sql);
		$st->bindValue(":slug", $slug, PDO::PARAM_STR);
		$st->bindValue(":created", date('Y-m-d'), PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if($row){
			// Record is exist
			// Begin update
			$sql = 'UPDATE trends SET views = views + 1 WHERE slug = :slug AND created = :created';
			$st = $conn->prepare($sql);
			$st->bindValue(":slug", $slug, PDO::PARAM_STR);
			$st->bindValue(":created", date('Y-m-d'), PDO::PARAM_STR);
			$st->execute();
		} else {
			// Not exist
			// Begin create record
			$sql = 'INSERT INTO trends ( views, created, slug ) VALUES ( 1, :created, :slug )';
			$st = $conn->prepare($sql);
			$st->bindValue(":slug", $slug, PDO::PARAM_STR);
			$st->bindValue(":created", date('Y-m-d'), PDO::PARAM_STR);
			$st->execute();
		}
		// Remove old trends record
		if(rand(0, 1000) <= 10){ // The chance this script being executed is 1%
			$date = new \DateTime('now');
			// remove 30 days
			$date->sub(new DateInterval('P30D'));  
			$sql = "DELETE FROM trends WHERE created < '{$date->format('Y-m-d')}' ";
			$st = $conn->prepare($sql);
			$st->execute();
		}
	}

	public static function upvote($id)
	{
		$conn = open_connection();
		$sql = 'UPDATE games SET upvote = upvote + 1 WHERE id = :id';
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
	}

	public static function downvote($id)
	{
		$conn = open_connection();
		$sql = 'UPDATE games SET downvote = downvote + 1 WHERE id = :id';
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
	}

	public function getExtraField($key)
	{
		if($this->extra_fields != null){
			$fields = json_decode($this->extra_fields, true);
			if(isset($fields[$key])){
				return $fields[$key];
			}
		}
		return null;
	}

	public function get_fields()
	{
		if($this->fields != ''){
			return json_decode($this->fields, true);
		} else {
			return null;
		}
	}

	public function get_field($key)
	{
		if($this->fields != ''){
			$fields = json_decode($this->fields, true);
			if(isset($fields[$key])){
				return $fields[$key];
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	public function get_tags(){
		$conn = open_connection();
		$sql = 'SELECT tags.name
		FROM tags
		INNER JOIN tag_links ON tags.id = tag_links.tag_id
		WHERE tag_links.game_id = :game_id';
		$st = $conn->prepare($sql);
		$st->bindValue(':game_id', $this->id);
		$st->execute();
		$tag_names = $st->fetchAll(PDO::FETCH_COLUMN);
		if(count($tag_names)){
			return implode(',', $tag_names);
		} else {
			return '';
		}
	}

	public function getCategoryList(bool $all = false){
		// Get category list for this game
		// Replacing old ineficient method "category" string
		$conn = open_connection();
		$sql = "SELECT categoryid FROM cat_links WHERE gameid = :gameid";
		$st = $conn->prepare($sql);
		$st->bindValue('gameid', $this->id, PDO::PARAM_INT);
		$st->execute();
		$rows = $st->fetchAll(PDO::FETCH_ASSOC);
		$ids = [];
		foreach ($rows as $item) {
			$ids[] = $item['categoryid'];
		}
		if(count($ids)){
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			$sql = "SELECT id, name, slug, priority FROM categories WHERE id IN ($placeholders)";
			$st = $conn->prepare($sql);
			$st->execute($ids);
			$rows = $st->fetchAll(PDO::FETCH_ASSOC);
			if(!$all){
				// Excluding hidden categories
				foreach ($rows as $key => $item) {
					if((int)$item['priority'] < 0){
						unset($rows[$key]);
					}
				}
			}
			return $rows;
		}
		return [];
	}

	public function get_categories(){
		$conn = open_connection();
		$sql = 'SELECT tags.name
		FROM tags
		INNER JOIN tag_links ON tags.id = tag_links.tag_id
		WHERE tag_links.game_id = :game_id';
		$st = $conn->prepare($sql);
		$st->bindValue(':game_id', $this->id);
		$st->execute();
		$tag_names = $st->fetchAll(PDO::FETCH_COLUMN);
		if(count($tag_names)){
			return implode(',', $tag_names);
		} else {
			return '';
		}
	}

	public function update_category()
	{
		if (is_null($this->id)) trigger_error("Game::update(): Attempt to update an Game object that does not have its ID property set.", E_USER_ERROR);
		$prev_cats = Game::getById($this->id)->category; //Get previous category
		//
		$conn = open_connection();
		$sql = "UPDATE games SET category=:category WHERE id = :id";

		$st = $conn->prepare($sql);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->bindValue(":category", $this->category, PDO::PARAM_STR);
		$st->execute();
	}

	public function update_tags($tags = ''){
		$conn = open_connection();
		// Split the tag string into an array of tag names
		$tags = preg_replace('/[^\p{L}0-9\s,]/u', '', $tags);
		$tags = preg_replace('/\s+/', ' ', $tags);
		$tags = str_replace('#', '', $tags);
		$tags = strtolower($tags);
		$_tag_names = explode(",", $tags);
		$tag_names = [];
		foreach ($_tag_names as $_tag) {
			$_tag = trim($_tag);
			$_tag = str_replace(' ', '-', $_tag);
			$length = strlen($_tag);
			if($length >= 2 && $length <= 15){
				if(!in_array($_tag, $tag_names)){
					$tag_names[] = $_tag;
				}
			}
		}
		// Insert new tags into the tags table, and retrieve their ids
		$tag_ids = array();
		foreach ($tag_names as $tag_name) {
			if($tag_name == '') continue;
			// Check if the tag already exists in the tags table
			$sql = 'SELECT id FROM tags WHERE name = :name';
			$st = $conn->prepare($sql);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$st->bindValue(":name", $tag_name, PDO::PARAM_STR);
			$st->execute();
			$row = $st->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				// If the tag already exists, use its id
				$tag_ids[] = $row['id'];
			} else {
				// If the tag does not exist, insert it and use the new id
				$sql = 'INSERT INTO tags (name) VALUES (:name)';
				$st = $conn->prepare($sql);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$st->bindValue(":name", $tag_name, PDO::PARAM_STR);
				$st->execute();
				$tag_ids[] = $conn->lastInsertId();
			}
		}
		// Insert the game-tag relationships into the tag_links table
		foreach ($tag_ids as $tag_id) {
			$sql = 'INSERT INTO tag_links (game_id, tag_id) VALUES (:game_id, :tag_id)';
			$st = $conn->prepare($sql);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$st->bindValue(":game_id", $this->id, PDO::PARAM_INT);
			$st->bindValue(":tag_id", $tag_id, PDO::PARAM_INT);
			$st->execute();
			//
			$sql = 'UPDATE tags SET usage_count = usage_count + 1 WHERE id = :tag_id';
			$st = $conn->prepare($sql);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$st->bindValue(':tag_id', $tag_id, PDO::PARAM_INT);
			$st->execute();
		}
	}

	public function insert()
	{
		if (!is_null($this->id)) trigger_error("Game::insert(): Attempt to insert an Game object that already has its ID property set (to $this->id).", E_USER_ERROR);
		$conn = open_connection();
		$conn->beginTransaction(); // Start a transaction to ensure atomicity of inserts
		try {
			$sql = 'INSERT INTO games ( createdDate, title, description, instructions, category, source, game_type, thumb_1, thumb_2, thumb_small, url, width, height, slug, tags, views, upvote, downvote, data, extra_fields, is_mobile, published ) 
					  VALUES ( :createdDate, :title, :description, :instructions, :category, :source, :game_type, :thumb_1, :thumb_2, :thumb_small, :url, :width, :height, :slug, :tags, 0, 0, 0, :data, :extra_fields, :is_mobile, :published )';
			$st = $conn->prepare($sql);
			$st->bindValue(":createdDate", $this->createdDate, PDO::PARAM_STR);
			$st->bindValue(":title", $this->title, PDO::PARAM_STR);
			$st->bindValue(":description", $this->description, PDO::PARAM_STR);
			$st->bindValue(":instructions", $this->instructions, PDO::PARAM_STR);
			$st->bindValue(":category", $this->category, PDO::PARAM_STR);
			$st->bindValue(":source", $this->source, PDO::PARAM_STR);
			$st->bindValue(":game_type", $this->game_type, PDO::PARAM_STR);
			$st->bindValue(":thumb_1", $this->thumb_1, PDO::PARAM_STR);
			$st->bindValue(":thumb_2", $this->thumb_2, PDO::PARAM_STR);
			$st->bindValue(":thumb_small", $this->thumb_small, PDO::PARAM_STR);
			$st->bindValue(":url", $this->url, PDO::PARAM_STR);
			$st->bindValue(":width", $this->width, PDO::PARAM_STR);
			$st->bindValue(":height", $this->height, PDO::PARAM_STR);
			$st->bindValue(":slug", esc_slug($this->slug), PDO::PARAM_STR);
			$st->bindValue(":tags", ($this->tags) ? $this->tags : '', PDO::PARAM_STR);
			$st->bindValue(":data", isset($_POST['data']) ? json_encode($_POST['data']) : '', PDO::PARAM_STR);
			$st->bindValue(":extra_fields", $this->extra_fields, PDO::PARAM_STR);
			$st->bindValue(":is_mobile", $this->is_mobile, PDO::PARAM_BOOL);
			$st->bindValue(":published", $this->published, PDO::PARAM_BOOL);
			$st->execute();
			$game_id = $conn->lastInsertId();
			$this->id = $game_id;
			if(!is_null($this->tags) && $this->tags != ''){ // Have tags
				$this->update_tags($this->tags);
			}
			// Commit the transaction
			$conn->commit();
		} catch (Exception $e) {
			// Roll back the transaction on error
			$conn->rollBack();
			throw $e;
		}
	}

	public function update()
	{
		if (is_null($this->id)) trigger_error("Game::update(): Attempt to update an Game object that does not have its ID property set.", E_USER_ERROR);
		$prev_cats = Game::getById($this->id)->category; //Get previous category
		//
		$conn = open_connection();
		$conn->beginTransaction(); // Start a transaction
		try {
			$sql = "UPDATE games SET title=:title, slug=:slug, description=:description, instructions=:instructions, category=:category, thumb_1=:thumb_1, thumb_2=:thumb_2, thumb_small=:thumb_small, url=:url, width=:width, height=:height, fields=:fields, extra_fields=:extra_fields, last_modified=:last_modified, is_mobile=:is_mobile, published=:published WHERE id = :id";

			$st = $conn->prepare($sql);
			$st->bindValue(":id", $this->id, PDO::PARAM_INT);
			$st->bindValue(":last_modified", date('Y-m-d H:i:s'), PDO::PARAM_STR);
			$st->bindValue(":title", $this->title, PDO::PARAM_STR);
			$st->bindValue(":slug", $this->slug, PDO::PARAM_STR);
			$st->bindValue(":description", $this->description, PDO::PARAM_STR);
			$st->bindValue(":instructions", $this->instructions, PDO::PARAM_STR);
			$st->bindValue(":category", $this->category, PDO::PARAM_STR);
			$st->bindValue(":thumb_1", $this->thumb_1, PDO::PARAM_STR);
			$st->bindValue(":thumb_2", $this->thumb_2, PDO::PARAM_STR);
			$st->bindValue(":thumb_small", $this->thumb_small, PDO::PARAM_STR);
			$st->bindValue(":url", $this->url, PDO::PARAM_STR);
			$st->bindValue(":width", $this->width, PDO::PARAM_INT);
			$st->bindValue(":height", $this->height, PDO::PARAM_INT);
			$st->bindValue(":fields", $this->fields, PDO::PARAM_STR);
			$st->bindValue(":extra_fields", $this->extra_fields, PDO::PARAM_STR);
			$st->bindValue(":is_mobile", $this->is_mobile, PDO::PARAM_BOOL);
			$st->bindValue(":published", $this->published, PDO::PARAM_BOOL);
			$st->execute();

			// Update category listing
			if($prev_cats != $this->category){
				$st = $conn->prepare("DELETE FROM cat_links WHERE gameid = :id");
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$st->bindValue(":id", $this->id, PDO::PARAM_INT);
				$st->execute();

				$cats = commas_to_array($this->category);
				if(is_array($cats)){ //Add new category if not exist
					$length = count($cats);
					for($i = 0; $i < $length; $i++){
						$category = Category::getByName($cats[$i]);
						$category->addToCategory($this->id, $category->id);
					}
				}
			}

			// Update tags
			$old_tags = $this->get_tags();
			$new_tags = $this->tags;
			if($old_tags != $new_tags){
				// Tags has been changed
				if($old_tags != ''){
					$st = $conn->prepare("DELETE FROM tag_links WHERE game_id = :id");
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$st->bindValue(":id", $this->id, PDO::PARAM_INT);
					$st->execute();
				}
				if($new_tags != ''){
					$this->update_tags($this->tags);
				}
			}
			// Commit the transaction
			$conn->commit();
		} catch (Exception $e) {
			// Roll back the transaction on error
			$conn->rollBack();
			throw $e;
		}
			
	}

	public function delete()
	{
		if (is_null($this->id)) trigger_error("Game::delete(): Attempt to delete an Game object that does not have its ID property set.", E_USER_ERROR);

		$conn = open_connection();
		$st = $conn->prepare("DELETE FROM games WHERE id = :id LIMIT 1");
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->execute();

		$st = $conn->prepare("DELETE FROM cat_links WHERE gameid = :id");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->execute();
		// Remove trends
		$st = $conn->prepare("DELETE FROM trends WHERE slug = :slug");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$st->bindValue(":slug", $this->slug, PDO::PARAM_STR);
		$st->execute();
		// Remove tag_links
		$st = $conn->prepare("DELETE FROM tag_links WHERE game_id = :game_id");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$st->bindValue(":game_id", $this->id, PDO::PARAM_STR);
		$st->execute();
		//
		if ($this->source == 'self') // Remove game files
		{
			$src = '..' . $this->url;
			$this->remove_game_folder($src);
		}
		if(substr($this->thumb_1, 0, 8) == '/thumbs/'){ // Remove thumbnail files
			if(file_exists('..'.$this->thumb_1)){
				unlink('..'.$this->thumb_1);
			}
		}
		if(substr($this->thumb_2, 0, 8) == '/thumbs/'){ // Remove thumbnail files
			if(file_exists('..'.$this->thumb_2)){
				unlink('..'.$this->thumb_2);
			}
		}
		if(substr($this->thumb_small, 0, 8) == '/thumbs/'){ // Remove thumbnail files
			if(file_exists('..'.$this->thumb_small)){
				unlink('..'.$this->thumb_small);
			}
		}
		// Remove all content translations
		delete_content_translation('game', $this->id);
	}
	public function remove_game_folder($dir)
	{
		if (is_null($this->id)) trigger_error("Does not have its ID property set.", E_USER_ERROR);
		if (is_dir($dir))
		{
			$files = scandir($dir);
			foreach ($files as $file) if ($file != "." && $file != "..") $this->remove_game_folder("$dir/$file");
			rmdir($dir);
		}
		else if (file_exists($dir)) unlink($dir);
	}
}

?>
