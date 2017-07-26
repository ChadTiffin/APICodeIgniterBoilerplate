<?php

class Transaction extends Base_Controller {

	public $table = "transactions";
	public $model = "TransactionModel";

	public $validation_rules = [
		[
			"field" => 'tran_date',
			"label" => "Transaction Date",
			"rules" => "required|min_length[10]"
		],
		[
			"field" => 'cat_id',
			"label" => "Category",
			"rules" => "required"
		],
		[
			"field" => 'amount',
			"label" => "Amount",
			"rules" => "required|numeric"
		]
	];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["User","Admin","Root"]);
	}

	public function get(
		$date_from = false, 
		$date_to = false, 
		$group_id = false, 
		$category_id = false, 
		$amount_from = false, 
		$amount_to = false, 
		$description_keyword = false, 
		$user_id = false) {

		$this->db
			->select('
				tran_date, 
				transactions.description as description, 
				cat_id, 
				transactions.id as id, 
				in_out, 
				amount, 
				first_name, 
				last_name, 
				user_id, 
				group_id, 
				groups.name as group_name, 
				account_id,
				bank_accounts.description as account_name,
				categories.description as cat_description')
			->from("transactions")
			->join("users","user_id=users.id",'left')
			->join("categories","cat_id=categories.id",'left')
			->join("groups","group_id=groups.id",'left')
			->join("bank_accounts",'bank_accounts.id=account_id','left');

		if (!$date_from && !$date_to) {
			//return only this year if date values not set
			$this->db
				->where("tran_date >= ",date("Y-01-01"))
				->where("tran_date <= ",date("Y-12-31"));
		}
		else {
			if ($date_from !== 'null' && $date_from != "undefined")
				$this->db->where("tran_date >=",$date_from);

			if ($date_to !== 'null' && $date_to != "undefined")
				$this->db->where("tran_date <=",$date_to);
		}

		if ($group_id !== 'null' && $group_id != "undefined")
			$this->db->where("group_id",$group_id);

		if ($category_id !== 'null' && $category_id != "undefined")
			$this->db->where("cat_id",$category_id);

		if ($amount_from !== 'null' && $amount_from != "undefined")
			$this->db->where("amount >=", $amount_from);

		if ($amount_to !== 'null' && $amount_to != "undefined")
			$this->db->where("amount <=", $amount_to);

		if ($description_keyword !== 'null' && $description_keyword != "undefined")
			$this->db->like('transactions.description', $description_keyword);

		if ($user_id !== 'null' && $user_id != "undefined")
			$this->db->where("user_id",$user_id);

		$transactions = $this->db
			->order_by("tran_date","ASC")
			->order_by("id","ASC")
			//->get_compiled_select();
			->get()->result_array();

		echo json_encode([
			'status' => 'success',
			'transactions' => $transactions
		]);
	}

	public function save() {
		//check to make sure this transaction is not being posted BEFORE last reconciliation (which would through the reconciled balances off)

		$post = $this->input->post();

		$this->load->model("BankAccountModel");
		$account = $this->BankAccountModel->find($post['account_id']);

		if (strtotime($account['last_reconciled']) >= strtotime($post['tran_date'])) {
			//Throw warning
			echo json_encode([
				'status' => 'fail',
				'msg' => "Can't post a transaction with a date that preceeds this account's last reconciliation date."
			]);
		}
		else {
			parent::save();
		}
	}

	public function transfer() {
		$this->load->library("form_validation");
		$this->form_validation->set_error_delimiters('', '');

		$validation_passes = true;
			
		$this->form_validation->set_rules([
			[
				'field' => 'amount',
				'label' => "Amount",
				"rules" => "required"
			],
			[
				'field' => 'from_id',
				'label' => "From Category",
				"rules" => "required|numeric"
			],
			[
				'field' => 'to_id',
				'label' => "To Category",
				"rules" => "required|numeric"
			],
			[
				'field' => 'tran_date',
				'label' => "Date",
				"rules" => "required|min_length[10]"
			]
		]);

		$validation_passes = $this->form_validation->run();

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
			$input = $this->input->post();

			$from_cat = $this->db->get_where("categories",["id" => $input['from_id']])->row();
			$to_cat = $this->db->get_where("categories",["id" => $input['to_id']])->row();

			$updated_at = date("Y-m-d H:i:s");

			$data = [
				"tran_date" => $input['tran_date'],
				"amount" => $input['amount'],
				"in_out" => 1,
				"cat_id" => $input['from_id'],
				"description" => "Transfer TO ".$to_cat->description.": ".$input['description'],
				'updated_at' => $updated_at
			];

			$this->db->insert($this->table,$data);

			$data = [
				"tran_date" => $input['tran_date'],
				"amount" => $input['amount'],
				"in_out" => 0,
				"cat_id" => $input['to_id'],
				"description" => "Transfer FROM ".$from_cat->description.": ".$input['description'],
				'updated_at' => $updated_at
			];

			$this->db->insert($this->table,$data);

			echo json_encode([
				"status" => "success"
			]);
		}
	}

}
