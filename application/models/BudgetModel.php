<?php

class BudgetModel extends BaseModel {

	public $table = "budgets";

	public function doRollover($year, $month) {

		$groups = $this->db->order_by("priority","ASC")->get("groups")->result_array();

		$last_day = date("Y-m-t", strtotime($year."-".$month."-01"));

		//get actual total income for the month
		$this->load->model("TransactionModel");
		$incomes = $this->TransactionModel->get(false,[
			['cat_id',0],
			['tran_date >=',$year."-".$month."-01"],
			['tran_date <=',$last_day]
		]);

		$totalIncome = 0;
		foreach ($incomes as $inc) {
			$totalIncome += $inc['amount'];
		}

		$remainingIncome = $totalIncome;

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
				if ($remainingIncome >= $cat['amount_alloc']) {
					$remainingIncome -= $cat['amount_alloc'];
					$inject_amount = $cat['amount_alloc'];
				}
				else {
					$inject_amount = $remainingIncome;
					$remainingIncome = 0;
				}

				$this->db
					->where("id",$cat['budget_id'])
					->set("amount_injected",$inject_amount)
					->update("budgets");
			}
		}

		$this->db->insert('budget_rollovers',[
				'rolled_over' => 1,
				'bud_year' => $year,
				'bud_month' => $month
			]);

		return true;
	}
}