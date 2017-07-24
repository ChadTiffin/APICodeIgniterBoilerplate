<?php

class Category extends Base_Controller {

	public $table = "categories";
	public $model = "CategoryModel";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["User","Admin","Root"]);
	}

	public function get($group_id = false)
	{
		$model = $this->model;
		echo json_encode($this->$model->get($group_id));
	}

	public function get_in_groups() {
		$model = $this->model;
		$groups = $this->$model->getInGroups();

		echo json_encode($groups);
	}

}
