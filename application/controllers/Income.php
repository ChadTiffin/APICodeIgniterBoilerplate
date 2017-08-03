<?php

class Income extends Base_Controller {

	public $table = "income_sources";
	public $model = "IncomeModel";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}
}
