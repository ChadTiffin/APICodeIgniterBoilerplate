<?php

class IncomeModel extends BaseModel {

	public $table = "income_sources";
	public $soft_delete = true;
	public $order_by_priority = true;
}