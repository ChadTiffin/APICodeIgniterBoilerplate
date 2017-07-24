<?php

class GroupModel extends BaseModel {

	public $table = "groups";
	public $soft_delete = true;
	public $order_by_priority = true;
}