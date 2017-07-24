<?php

class BankAccount extends Base_Controller {

	public $table = "bank_accounts";
	public $model = "BankAccountModel";

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["User","Admin","Root"]);
	}

	public function get() {

		$model = $this->model;
		$accounts = $this->$model->get();

		$accounts = $this->$model->addBalance($accounts);

		echo json_encode($accounts);
	}

	public function make_default()
	{
		$id = $this->input->post("id");

		$this->db->set("priority",1)
			->update($this->table);

		$this->db->set("priority",0)
			->where("id",$id)
			->update($this->table);

		echo json_encode([
			"status" => "success"
		]);
	}

}