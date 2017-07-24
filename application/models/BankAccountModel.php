<?php

class BankAccountModel extends BaseModel {

	public $table = "bank_accounts";
	public $soft_delete = true;
	public $order_by_priority = true;

	public function addBalance($accounts) {
		$new_accounts = [];
		foreach ($accounts as $account) {

			$account_transactions = $this->db->get_where("transactions",
				[
					'tran_date >' => $account['last_reconciled'],
					'account_id' => $account['id']
				]
			)->result();

			$running_balance = $account['last_reconciled_balance'];

			foreach ($account_transactions as $tran) {
				if ($tran->in_out == 0)
					$running_balance += $tran->amount;
				else
					$running_balance -= $tran->amount;
			}

			$running_balance = number_format($running_balance,2,'.','');

			$account['balance'] = $running_balance;

			$new_accounts[] = $account;
		}

		return $new_accounts;
	}
}