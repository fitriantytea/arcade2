<?php
/**
 * Class to handle game collections
 */

class Collection
{
	public $id = null;
	public $name = null;
	public $slug = null;
	public $data = null;
	public $description = null;
	public $allow_dedicated_page = false;

	public function __construct($data = array())
	{
		if (isset($data['id'])) $this->id = (int)$data['id'];
		if (isset($data['name'])) $this->name = $data['name'];
		if (isset($data['slug'])) $this->slug = $data['slug'];
		if (isset($data['data'])) $this->data = $data['data'];
		if (isset($data['description'])) $this->description = $data['description'];
		if (isset($data['allow_dedicated_page'])) $this->allow_dedicated_page = $data['allow_dedicated_page'];
		//
		if((int)$this->allow_dedicated_page == 1 || $this->allow_dedicated_page == true){
			$this->allow_dedicated_page = true;
		} else {
			$this->allow_dedicated_page = false;
		}
	}

	public function storeFormValues($params)
	{
		$this->__construct($params);
	}

	public static function getById($id)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM collections WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new Collection($row);
	}

	public static function getByName($name)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM collections WHERE name = :name LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new Collection($row);
	}

	public static function getBySlug($slug)
	{
		if($slug != ''){
			$conn = open_connection();
			$sql = "SELECT * FROM collections WHERE slug = :slug LIMIT 1";
			$st = $conn->prepare($sql);
			$st->bindValue(":slug", $slug, PDO::PARAM_STR);
			$st->execute();
			$row = $st->fetch();
			if ($row) return new Collection($row);
		} else {
			return null;
		}
	}

	public static function getIdByName($name)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM collections WHERE name = :name limit 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		return $row['id'];
	}

	public static function getList($numRows = 1000000)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM collections
			ORDER BY name ASC LIMIT :numRows";

		$st = $conn->prepare($sql);
		$st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
		$st->execute();
		$list = array();

		while ($row = $st->fetch())
		{
			$Collection = new Collection($row);
			$list[] = $Collection;
		}
		$totalRows = $conn->query('SELECT count(*) FROM collections')->fetchColumn();
		return (array(
			"results" => $list,
			"totalRows" => $totalRows
		));
	}

	public static function getListByCollection($name, $amount = 12, $page = 0)
	{
		if($amount > 300){
			$amount = 300; // Safety
		}
		$conn = open_connection();
		$sql = "SELECT * FROM collections WHERE name = :name";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch(PDO::FETCH_ASSOC);
		$list = array();
		if($row){
			// The data is exist
			if(isset($row['data'])){
				$data = explode(',', $row['data']);
				$i = 0;
				foreach ($data as $id)
				{
					if($i < $amount){
						$game = new Game;
						$res = $game->getById($id);
						if($res){
							array_push($list, $res);
						}
					}
					$i++;
				}
				return (array(
					"results" => $list,
					"totalRows" => count($list),
				));
			}
		}
		return null;
	}

	public function isCollectionExist($name)
	{
		$conn = open_connection();
		$sql = 'SELECT * FROM collections WHERE name = :name limit 1';
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row)
		{
			$this->id = $row['id'];
		}
		if ($row)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function insert()
	{ 
		if (!is_null($this->id)) trigger_error("Collection::insert(): Attempt to insert a Collection object that already has its ID property set (to $this->id).", E_USER_ERROR);

		$conn = open_connection();
		$sql = "INSERT INTO collections ( name, slug, description, data ) VALUES ( :name, :slug, :description, :data )";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $this->name, PDO::PARAM_STR);
		$st->bindValue(":slug", $this->slug, PDO::PARAM_STR);
		$st->bindValue(":data", $this->data, PDO::PARAM_STR);
		$st->bindValue(":description", !is_null($this->description) ? $this->description : '', PDO::PARAM_STR);
		$st->execute();
		$this->id = $conn->lastInsertId();
	}

	public function update()
	{
		if (is_null($this->id)) trigger_error("Collection::update(): Attempt to update a Collection object that does not have its ID property set.", E_USER_ERROR);
		//$prev_name = Collection::getById($this->id)->name;
		//
		$conn = open_connection();
		$sql = "UPDATE collections SET name=:name, slug=:slug, data=:data, description=:description, allow_dedicated_page=:allow_dedicated_page WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $this->name, PDO::PARAM_STR);
		$st->bindValue(":slug", $this->slug, PDO::PARAM_STR);
		$st->bindValue(":data", $this->data, PDO::PARAM_STR);
		$st->bindValue(":description", $this->description, PDO::PARAM_STR);
		$st->bindValue(":allow_dedicated_page", $this->allow_dedicated_page, PDO::PARAM_BOOL);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->execute();
	}

	public function delete()
	{
		if (is_null($this->id)) trigger_error("Collection::delete(): Attempt to delete a Collection object that does not have its ID property set.", E_USER_ERROR);

		$conn = open_connection();
		$st = $conn->prepare("DELETE FROM collections WHERE id = :id LIMIT 1");
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->execute();
	}

}

?>
