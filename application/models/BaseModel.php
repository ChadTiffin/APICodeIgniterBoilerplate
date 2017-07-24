<?php

class BaseModel extends CI_Model {

	public $table = "";
	public $soft_delete = false;
	public $order_by_priority = false;

	public function get($include_deleted = false)
	{
		if ($this->order_by_priority)
			$this->db->order_by("priority","ASC");

		if ($include_deleted)
			return $this->db->get($this->table)->result();	
		else
			return $this->db->get_where($this->table,["deleted" => 0])->result_array();
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