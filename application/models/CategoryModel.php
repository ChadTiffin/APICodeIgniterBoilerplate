<?php

class CategoryModel extends BaseModel {

	public $table = "categories";
	public $soft_delete = true;
	public $order_by_priority = true;

	public function getInGroups()
	{
		$this->load->model("GroupModel");
		$groups = $this->GroupModel->get();

		$return = [];
		foreach ($groups as $group) {
			$cats = $this->get($group['id']);

			$group['categories'] = $cats;

			$return[] = $group;
		}

		return $return;
	}

	public function get($group_id = false)
	{
		if ($group_id)
			$query = $this->db->where("group_id",$group_id);
		
		 return $this->db
		 	->order_by("priority","ASC")
		 	->get($this->table)
		 	->result_array();
	}

}