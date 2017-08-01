<?php

class BaseModel extends CI_Model {

	public $table = "";
	public $soft_delete = false;
	public $order_by_priority = false;

	/* $relations expects = [
		'table' => {table},
		'key' => {table_key ex user_id},
		'joins' => [
			['table' => {table}, 'key' => {key}]
		]

		$filters expects = [
			['col','value'],
			['col operator','value']
		]
	] */
	public $relations = [];

	public function get($include_deleted = false, $filters = [], $include_children = true)
	{
		$this->db
			->select($this->table.".*")
			->from($this->table);

		if ($this->order_by_priority)
			$this->db->order_by("priority","ASC");

		//do not include deleted records if soft delete enabled on model
		if ($this->soft_delete)
			$filters[] = ['deleted',0];

		if ($this->relations) {
			foreach ($this->relations as $rel) {
				$this->db->join($rel['table'],$this->table.".".$rel['key']." = ".$rel['table'].".id",'left');
			}
		}

		foreach ($filters as $filter) {
			if (isset($filter[2]) && strtolower($filter[2]) == 'like')
				$this->db->like($filter[0],$filter[1]);
			else
				$this->db->where($filter[0],$filter[1]);
		}

		//var_dump($this->db->get_compiled_select());
		//die;
		$results = $this->db->get()->result_array();

		$with_children = [];

		//has relations, so we'll iterate each declared child and nest them into the result with a key of {table_name}
		if ($this->relations && $include_children) {
			foreach ($results as $result) {
			
				//iterate each declared child relation
				foreach ($this->relations as $rel) {
					$this->db
						->select("*")
						->from($rel['table']);

					//if any joins are declared, join and merge them into the child record
					if (isset($rel['joins'])) { //child of child is set
						foreach ($rel['joins'] as $join) {
							$this->db->join($join['table'],$rel['table'].".".$join['key']." = ".$join['table'].".id",'left');
						}
					}

					$child = $this->db
						->where($rel['table'].".id",$result[$rel['key']])
						->get()->row_array();

					$result[$rel['table']] = $child;
				}
				$with_children[] = $result;
			}
			return $with_children;
		}
		else 
			return $results;
		

	}

	public function delete($id) {

		if ($this->soft_delete) {
			$this->db->set("deleted",1)
			->where("id",$id)
			->update($this->table);
		}
		else {
			$this->db->where("id",$id)
				->delete($this->table);
		}
		
	}

	public function find($id, $include_deleted = false) {

		if ($include_deleted) 
			$result = $this->db->get_where($this->table,["id" => $id])->row_array();
		else
			$result = $this->db->get_where($this->table,["id" => $id,"deleted" => 0])->row_array();

		return $result;
	}

}