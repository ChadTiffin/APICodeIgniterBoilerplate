<?php

class Group extends Base_Controller {

	public $table = "groups";
	public $model = "GroupModel";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function get_with_categories() {
		$groups = $this->db
			->order_by("priority","ASC")
			->get_where("groups",["deleted" => 0])
			->result_array();

		$nested_groups = [];
		foreach ($groups as $group) {
			$cats = $this->db
				->get_where("categories",["group_id" => $group['id'],"deleted" => 0])
				->result_array();

			$group['categories'] = $cats;

			$nested_groups[] = $group;
		}

		echo json_encode([
			"status" => "success",
			"groups" => $nested_groups
		]);
	}
}
