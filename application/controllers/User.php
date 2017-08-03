<?php

class User extends Base_Controller {

	public $table = "users";
	public $validation_rules = [
		[
			'field' => "email",
			'label' => "Email",
			'rules' => "required|valid_email"
		],
		[
			'field' => 'username',
			'label' => "Username",
			'rules' => "is_unique[users.email]"
		]
	];
	public $api_excluded_fields = ["pw_hash","updated_at","deleted","allow_access"];
	public $model = "UserModel";

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo","Admin","Root","User"]);
	}

	public function find($id,$field='id') {
		$this->gatekeep(["Admin","Root"]);

		parent::find($id,$field);
	}

	public function self($api_key) {
		$result = $this->db->get_where($this->table,['api_key' => $api_key])->row_array();

		foreach ($this->api_excluded_fields as $field) {
			unset($result[$field]);
		}

		echo json_encode($result);
	}

	public function save() {
		$this->gatekeep(["Admin","Root"]);

		parent::save();
	}

	public function get() {
		$this->gatekeep(["Admin","Root"]);

		parent::get();
	}

	public function new() {
		$this->gatekeep(["Admin","Root"]);

		$this->load->library("form_validation");
		$this->form_validation->set_error_delimiters('', '');

		$validation_passes = true;

		if ($this->validation_rules != null) {
			$this->form_validation->set_rules($this->validation_rules);
			$validation_passes = $this->form_validation->run();
		}

		if (!$validation_passes) {
			//fails

			$form_errors = [];
			foreach ($this->validation_rules as $field) {
				if (form_error($field['field']) != "")
					$form_errors[$field['field']] = form_error($field['field']);
			}

			echo json_encode([
				"status" => "fail",
				"errors" => $form_errors
			]);
		}
		else {
			$data = $this->input->post();

			unset($data['key']);
			unset($data['id']);

			$data['updated_at'] = date("Y-m-d H:i:s");

			//create new API key
			$data['api_key'] = hash("sha256", mt_rand(10000,1000000000).time());

			//create new password
			$password = substr(md5(mt_rand(10000,100000000)),1,6);
			$data['pw_hash'] = $this->UserModel->createPasswordHash($password);

			//create username
			$data['username'] = strtolower(substr($data['first_name'], 0,2).substr($data['last_name'], 0,3));

			$this->db->insert($this->table,$data);
			$record_id = $this->db->insert_id();

			$email_data = [
				'username' => $data['username'],
				'password' => $password
			];

			//send user a welcome email
			$this->UserModel->sendEmail($data['email'],"emails/new_user_welcome",$email_data,"Welcome to ".APP_NAME,"no-reply@".FRONT_END_DOMAIN);

			echo json_encode([
				"status" => "success",
				'id' => $record_id,
			]);
		}
	}
}
