<?php

class TransactionModel extends BaseModel {

	public $table = "transactions";

	public $relations = [
		['table' => 'categories', 'key' => 'cat_id', 'joins' => [
				['table' => 'groups','key' => 'group_id']
			]
		],
		['table' => 'users', 'key' => 'user_id'],
		['table' => 'bank_accounts', 'key' => 'account_id']
	];
}