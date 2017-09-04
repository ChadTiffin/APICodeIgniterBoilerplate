<?php

class BaseModel extends CI_Model {

	public $table = "";
	public $soft_delete = false;
	public $order_by_priority = false;
	public $page_limit = 50; //optionally over-ride in child model

	/* $relations expects = [
			[
				'table' => {table},
				'key' => {table_key ex user_id},
				'joins' => [
					['table' => {table}, 'key' => {key}].
				'hasMany' => true (optional, if not set, single child record assumed)
			]
		]

		$filters expects = [
			['col','value'],
			['col operator','value']
		]
	] */
	public $relations = [];

	private function appendChildren($result, $relations) {
		//has relations, so we'll iterate each declared child and nest them into the result with a key of {table_name}

		//iterate each declared child relation
		if ($result) {
			foreach ($relations as $rel) {

				$this->db
					->select("*")
					->from($rel['table']);

				//if any joins are declared, join and merge them into the child record
				if (isset($rel['joins'])) { //child of child is set
					foreach ($rel['joins'] as $join) {
						$this->db->join($join['table'],$rel['table'].".".$join['key']." = ".$join['table'].".id",'left');
					}
				}

				if (isset($rel['hasMany']) && $rel['hasMany']) {
					$child = $this->db
						->where($rel['table'].".".$rel['key'],$result['id'])
						->get()->result_array();

					if (isset($rel['model'])) {
						$this->load->model($rel['model']);

						if (isset($this->$rel['model']->hidden_fields)) {
							foreach ($child as $record) {
								foreach ($this->$rel['model']->hidden_fields as $hidden) {
									unset($record[$hidden]);
								}
							}
						}
					}
				}
				else {
					$child = $this->db
						->where($rel['table'].".id",$result[$rel['key']])
						->get()->row_array();

					if (isset($rel['model'])) {
						$this->load->model($rel['model']);

						$model = $rel['model'];

						if (isset($this->$model->hidden_fields)) {

							foreach ($this->$model->hidden_fields as $hidden) {
								unset($child[$hidden]);
							}
							
						}
					}
				}
					
				$result[$rel['table']] = $child;
			}
		}

		return $result;
	}

	/*
	options = [
		'include_deleted' => false (default),
		'filters' => [],
		'order' => [column, ASC/DESC],
		'page' => 1,
		'include_relations' => true
	]
	*/

	public function get($options)
	{
		$this->db
			->select($this->table.".*")
			->from($this->table);

		////////////////////////////
		// ORDERING
		////////////////////////////
		if ($this->order_by_priority)
			$this->db->order_by("priority","ASC");

		if (isset($options['order']))
			$this->db->order_by($options['order'][0],$options['order'][1]);
		
		//do not include deleted records if soft delete enabled on model
		if ($this->soft_delete)
			$options['filters'][] = ['deleted',0];

		/////////////////////////////////
		// JOINS SPECIFIED IN RELATIONS
		/////////////////////////////////
		if (isset($options['include_relations']) && $options['include_relations'] ) {
			foreach ($this->relations as $rel) {

				if (!isset($rel['hasMany']) || (isset($rel['hasMany']) && !$rel['hasMany']))
					$this->db->join($rel['table'],$this->table.".".$rel['key']." = ".$rel['table'].".id",'left');
			}
		}

		/////////////////////////
		// FILTERING
		/////////////////////////
		$filters = $options['filters'];
		foreach ($filters as $filter) {

			if (isset($filter[2]) && strtolower($filter[2]) == 'and') {
				if (isset($filter[3]) && strtolower($filter[3]) == 'like')
					$this->db->like($filter[0],$filter[1]);
				else
					$this->db->where($filter[0],$filter[1]);
			}
			elseif (isset($filter[2]) && strtolower($filter[2]) == 'or') {
				if (isset($filter[3]) && strtolower($filter[3]) == 'like')
					$this->db->or_like($filter[0],$filter[1]);
				else
					$this->db->or_where($filter[0],$filter[1]);
			}
			else
				$this->db->where($filter[0],$filter[1]);
		}

		///////////////////////
		// PAGINATION
		///////////////////////
		if (isset($options['page']))
			$this->db->limit($this->page_limit, $options['page'] * $this->page_limit);

		$results = $this->db->get()->result_array();

		////////////////////////////
		// APPENDING RELATIONS
		////////////////////////////
		$with_relations = [];

		//has relations, so we'll iterate each declared child and nest them into the result with a key of {table_name}
		if ($this->relations && $include_relations) {
			foreach ($results as $result) {
			
				$with_relations[] = $this->appendChildren($result, $this->relations);
			}
			return $with_relations;
		}
		else 
			return $results;
		
	}

	//returns array of errors on fail, or integer of updated record on success
	public function save($data)
	{
		$this->load->library("form_validation");
		$this->form_validation->set_error_delimiters('', '');

		$validation_passes = true;

		if ($this->validation_rules != null) {
			$this->form_validation->set_rules($this->validation_rules);
			$validation_passes = $this->form_validation->run();
		}

		if (!$validation_passes) {
			//fails

			$form_errors = [];
			foreach ($this->validation_rules as $field) {
				if (form_error($field['field']) != "")
					$form_errors[$field['field']] = form_error($field['field']);
			}

			return $form_errors;
		}
		else {
			$data = $this->input->post();

			unset($data['key']);

			$data['updated_at'] = date("Y-m-d H:i:s");

			if (isset($data['id']) && $data['id'] != 'undefined' && $data['id'] != 0) {

				$this->db
					->set($data)
					->where('id',$data['id'])
					->update($this->table);

				$record_id = $data['id'];
			}
			else {

				$this->db->insert($this->table,$data);

				$record_id = $this->db->insert_id();
			}

			return $record_id;
		}
	}

	//returns number of records saved
	public function saveBatch($records){
		$this->load->library("form_validation");
		$this->form_validation->set_error_delimiters('', '');

		$validation_passes = true;

		$form_errors = [];
		$cnt = 0;
		foreach ($records as $record) {
			$this->form_validation->set_data($record);
			
			$validation_passes = true;
			if ($this->validation_rules != null) {
				$this->form_validation->set_rules($this->validation_rules);
				$validation_passes = $this->form_validation->run();
			}

			if ($validation_passes) {

				$record['updated_at'] = date("Y-m-d H:i:s");

				if (isset($record['id']) && $record['id'] != 'undefined' && $record['id'] != 0) {

					$this->db
						->set($record)
						->where('id',$record['id'])
						->update($this->table);

					$record_id = $record['id'];
				}
				else {

					$this->db->insert($this->table,$record);

					$record_id = $this->db->insert_id();
				}
				$cnt++;
			}
		}
		return $cnt;
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

	public function find($field, $value, $include_children = true) {

		$result = $this->db->get_where($this->table,[$field => $value])->row_array();

		if ($this->relations && $include_children) {
			$result = $this->appendChildren($result, $this->relations);
		}

		return $result;
	}

}