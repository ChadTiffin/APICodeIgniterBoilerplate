<?php

class Budget extends Base_Controller {

	public $table = "budgets";

	public $validation_rules = [];

	private function calcCategoryTotals($year, $month, $cat_id) {
		$last_dom = date("Y-m-t", strtotime("$year-$month-01"));

		$return = [];

		//OUTFLOWS
		$all_outflows = $this->db
			->select("SUM(amount) as sum_amount")
			->from("transactions")
			->where("cat_id",$cat_id)
			->where("in_out",1)
			->where("tran_date <","$year-$month-01")
			->get()
			->row();

		$return['prev_outflows'] = 0;
		if ($all_outflows->sum_amount != 0)	
			$return['prev_outflows'] = $all_outflows->sum_amount;

		//INFLOWS
		$all_inflows = $this->db
			->select("SUM(amount) as sum_amount")
			->from("transactions")
			->where("cat_id",$cat_id)
			->where("in_out",0)
			->where("tran_date <","$year-$month-01")
			->get()
			->row();

		$return['prev_inflows'] = 0;
		if ($all_inflows->sum_amount != 0)
			$return['prev_inflows'] = $all_inflows->sum_amount;

		//PREV INJECTED
		$all_inflows = $this->db
			->select("SUM(amount_injected) as sum_amount")
			->from("budgets")
			->where("cat_id",$cat_id)
			->get()
			->row();

		$return['tot_prev_injected'] = 0;
		if ($all_inflows->sum_amount !=0)
			$return['tot_prev_injected'] = $all_inflows->sum_amount;

		//This month outflows
		$month_outflows = $this->db
			->select("SUM(amount) as sum_amount")
			->from("transactions")
			->where("cat_id",$cat_id)
			->where("in_out",1)
			->where("tran_date >=","$year-$month-01")
			->where("tran_date <=",$last_dom)
			->get()
			->row();

		$return['outflows'] = 0;
		if ($month_outflows->sum_amount != 0)
			$return['outflows'] = $month_outflows->sum_amount;

		//This month inflows
		$month_inflows = $this->db
			->select("SUM(amount) as sum_amount")
			->from("transactions")
			->where("cat_id",$cat_id)
			->where("in_out",0)
			->where("tran_date >=","$year-$month-01")
			->where("tran_date <=",$last_dom)
			->get()
			->row();

		$return['inflows'] = 0;
		if ($month_inflows->sum_amount != 0)
			$return['inflows'] = $month_inflows->sum_amount;
		
		$prev_balance = $return['prev_inflows']- $return['prev_outflows'] + $return['tot_prev_injected'];

		$return['prev_balance'] = $prev_balance;

		$return['balance'] = $return['prev_balance'] + $return['inflows'] - $return['outflows'];

		return $return;
	}

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function get($year=false,$month=false)
	{
		if (!$year && !$month)
			echo json_encode([
				'status' => "fail",
				"message" => "Year and date parameters need to be set"
			]);
		else
			echo json_encode($this->db->get_where($this->table,['bud_year' => $year, 'bud_month'=> $month])->result());
	}

	public function get_budget($year=null,$month=null)
	{

		if (!isset($year))
			$year = date("Y");
		if (!isset($month))
			$month = date("m");

		//check to see if budget exists first
		$budget = $this->db->get_where("budgets",["bud_year" => $year, "bud_month" => $month])->result();

		if (!$budget) {
			$return = [
				'status' => "failed",
				"error" => "not found",
				'message' => "No budget exists for $year-$month"
			];
		}
		else {

			$return = [];

			$first_day = $year."-".$month."-01";
			$last_day = date("Y-m-t",strtotime($first_day));

			$this_month_income = $this->db
				->select("SUM(amount) as total_income")
				->from("transactions")
				->where('tran_date >=', $first_day)
				->where('tran_date <=', $last_day)
				->where("cat_id",0)
				->where("in_out",0)
				->get()
				->row();

			$groups = $this->db->order_by("priority","ASC")->get("groups")->result_array();

			$ret_groups = [];

			$totalBudgetBalance = 0;
			foreach ($groups as $group) {

				$cats = $this->db
					->select("categories.id as id, budgets.id as budget_id, description, amount_alloc, cat_id, priority")
					->from("categories")
					->join("budgets","cat_id=categories.id","left")
					->where("group_id",$group['id'])
					->where("bud_month",$month)
					->where("bud_year",$year)
					->order_by("priority","ASC")
					->get()
					->result_array();

				//calculate balances
				$new_cats = [];

				$cats_balance = 0;

				foreach ($cats as $cat) {
					$calcs = $this->calcCategoryTotals($year,$month,$cat['id']);

					foreach ($calcs as $key => $value) {
						$cat[$key] = $value;
					}

					$new_cats[] = $cat;
					$cats_balance += $cat['balance'];
					$totalBudgetBalance += $cat['balance'];
				}

				$group['categories'] = $new_cats;
				$group['name'] = $group['name'];
				$group['balance'] = $cats_balance;

				//var_dump($group['name']);
				//var_dump($cats_balance);

				if (!empty($group['categories'])) {
					$ret_groups[] = $group;
				}
			}

			//var_dump($ret_groups);

			// SURPLUS CALCS
			//=================================
			/*
			Surplus balance is calculated from the dfference of actual bank account balances vs total budget balance. This way categories can be added/removed from the budget and the surplus will be adjusted accordingly
			*/

			$this->load->model("BankAccountModel");
			$bankAccounts = $this->BankAccountModel->get();
			$bankAccountsWithBalance = $this->BankAccountModel->addBalance($bankAccounts);

			$totalBankBalance = 0;
			foreach ($bankAccountsWithBalance as $account) {

				if ($account['off_budget'] == 0)
					$totalBankBalance += $account['balance'];
			}

			//get total income this month
			$incomes = $this->db
				->select("SUM(amount) as total_sum")
				->from("transactions")
				->where("tran_date >=",$first_day)
				->where("tran_date <=",$last_day)
				->where("cat_id",0)
				->get()->row();

			$total_incomes = $incomes->total_sum;

			$surplus = $this->calcCategoryTotals($year,$month,1); //1 is surplus cat_id
			$surplus['description'] = "Surplus";
			$surplus['balance'] = $totalBankBalance - $totalBudgetBalance - $total_incomes;
			$surplus['id'] = 1;

			$ret_groups[] = [
				'categories' => [$surplus],
				'name' => "Surplus"
			];

			$incomes = $this->db
				->order_by("priority", "ASC")
				->get_where("income_sources",["bud_year" => $year, 'bud_month' => $month,'deleted'=>0])
				->result();

			if ($month == 1) {
				$last_month = 12;
				$last_year = $year - 1;
			}
			else {
				$last_month = $month - 1;
				$last_year = $year;
			}

			$rollover_check = $this->db->get_where("budget_rollovers",["bud_month" => $last_month, "bud_year" => $last_year])->row();

			$rollover_needed = true;

			if ($rollover_check) {
				if ($rollover_check->rolled_over)
					$rollover_needed = false;
			}

			$return = [
				'status' => 'success',
				'rollover_needed' => $rollover_needed,
				'income_sources' => $incomes,
				'total_income' => $this_month_income->total_income,
				'groups' => $ret_groups,
				'budget_year' => $year,
				'budget_month' => $month
			];
		}

		echo json_encode($return);
	}

	public function save_order()
	{
		$post = $this->input->post();

		$index = 0;
		foreach (json_decode($post['list']) as $item) {
			//var_dump($index);
			
			//var_dump($cat);

			$this->db
				->where("id",$item->id)
				->set("priority",$index)
				->update($post['list_type']);

			$index++;
		}

		echo json_encode([
			'status' => 'success'
		]);
	}

	public function copy_last()
	{
		$post = $this->input->post();

		if ($post) {

			$year = $post['year'];
			$month = $post['month'];

			//check to see if budget exists first
			$budget = $this->db->get_where("budgets",["bud_year" => $year, "bud_month" => $month])->result();

			if (!$budget) {
				//get latest year/month

				$last_month = $this->db
					->select("bud_year, bud_month")
					->from("budgets")
					->order_by("bud_year","DESC")
					->order_by("bud_month","DESC")
					->get()
					->row();

				if ($last_month) {

					$prev_year = $last_month->bud_year;
					$prev_month = $last_month->bud_month;

					//get the last budget now
					$last_budget = $this->db->get_where("budgets",["bud_year" => $prev_year,'bud_month' => $prev_month])->result();

					foreach ($last_budget as $entry) {
						$insert_entry = [
							'amount_alloc' => $entry->amount_alloc,
							'priority' => $entry->priority,
							'cat_id' => $entry->cat_id,
							'bud_month' => $month,
							'bud_year' => $year,
							'updated_at' => date("Y-m-d H:i:s"),
							'amount_injected' => 0
						];

						$this->db->insert("budgets",$insert_entry);
					}

					//get the last set of income sources
					$last_incomes = $this->db->get_where("income_sources",["bud_year" => $prev_year,'bud_month' => $prev_month])->result();

					foreach ($last_incomes as $entry) {
						$insert_entry = [
							'amount' => $entry->amount,
							'priority' => $entry->priority,
							'bud_month' => $month,
							'bud_year' => $year,
							'updated_at' => date("Y-m-d H:i:s"),
							'deleted' => $entry->deleted
						];

						$this->db->insert("income_sources",$insert_entry);
					}

					$return = [
						'status' => 'success',
						'msg' => "Budget copied successfully"
					];
				}
				else {
					$return = [
						'status' => 'failed',
						'msg' => "No previous budget found"
					];
				}
			}
			else {
				$return = [
					'status' => 'failed',
					'msg' => "A budget for this month exists already"
				];
			}
		}
		else {
			$return = [
				'status' => 'failed',
				'msg' => "Need to POST copy=true"
			];
		}
		echo json_encode($return);
	}

	public function rollover() {
		$post = $this->input->post();

		$this->load->model("BudgetModel");

		$this->BudgetModel->doRollover($post['year'],$post['month']);

		echo json_encode([
			'status' => 'success'
		]);
	}

}
