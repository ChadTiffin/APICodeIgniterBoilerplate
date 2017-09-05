<?php

class Base_Controller extends CI_Controller {

	public $allowed_origin = ""; //SET HERE. full url address of client ex. https://chadtiffin.com

	public $table = ""; //set in child controllers
	public $model = ""; //set in child controllers

	public $api_excluded_fields = []; //set in child controllers. Will prevent listed fields from being sent to front-end ex. ["password","id"]

	//methods listed here (in the child property) will be not availble to the front end (for example list delete here if you want to prevent the delete endpoint from being accessible)
	public $hidden_methods =[];

	function __construct() {
		parent::__construct();

		if ($this->model != "") 
			$this->load->model($this->model);

		$this->load->library("Response");

		if (isset($_SERVER['HTTP_ORIGIN']))
			header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
		else {
			//header('Access-Control-Allow-Origin: http://localhost:8081');
			header('Access-Control-Allow-Origin: '.$this->allowed_origin);	
		}

		header('Access-Control-Allow-Credentials: true');
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

		if ( "OPTIONS" === $_SERVER['REQUEST_METHOD'] ) 
			die();
		
		//check restricted routes
		$request_path = $_SERVER['REQUEST_URI'];

		$request_headers = $this->input->request_headers();

		$api_key = $request_headers['Authorization'];
		$user = $this->db
			->select("*")
			->from("users")
			->join("api_keys","user_id=users.id")
			->join("permission_groups","group_id=permission_groups.id")
			->where("api_key",$api_key)
			->get()
			->row_array();

		$access_allowed = true;
		$message = "";
		if ($user) {
			$restricted_routes = json_decode($user['restricted_routes']);
			if (in_array($request_path, $restricted_routes)) { //check for exact route
				$access_allowed = false;
				$message = "You do not have permission for this endpoint.";
			} 
			else {
				//check for parent route restriction
				foreach ($restricted_routes as $route) {

					if (strpos($request_path, $route) !== false) { //there is a parent route that matches this path that is restricted (ex. if "/users/" listed in resricted routes and path == '/users/find/12' then access denied)
						$access_allowed = false; 
						$message = "You do not have permission for this endpoint.";
					}						
				}
			}

			//check for allowed routes that may over-ride restricted routes
			$allowed_routes = json_decode($user['allowed_routes']);
			if (in_array($request_path, $allowed_routes)) { //check for exact route
				$access_allowed = true;
			} 
			else {
				//check for parent route restriction
				foreach ($allowed_routes as $route) {

					if (strpos($request_path, $route) !== false)  //there is a parent route that matches this path that is allowed
						$access_allowed = true; 
										
				}
			}

		}
		else {
			$access_allowed = false;
			$message = "API key invalid";
		}

		if (!$access_allowed) {
			$this->response->json([],[
				"status" => "denied",
				"message" => $message
			]);
		}
		
	}

	public function find($value,$field="id")
	{

		if (in_array("find", $this->hidden_methods))
			show_404();

		$model = $this->model;
		$this->load->model($model);

		$result = $this->$model->find($field,$value);

		foreach ($this->api_excluded_fields as $field) {
			unset($result[$field]);
		}

		$this->response->json($result);
	}

	public function save()
	{
		if (in_array("save", $this->hidden_methods))
			show_404();

		$model = $this->model;
		$result = $this->$model->save($this->input->post);

		$this->response->json($result);
	}

	//expects json input field of 'records'
	public function save_batch() {
		if (in_array("save_batch", $this->hidden_methods))
			show_404();

		$model = $this->model;
		$result = $this->model->saveBatch(json_decode($this->input->post['records']));

		$this->response->json([],["records_updated" => $result]); //generic success message
	}

	public function get()
	{
		if (in_array("get", $this->hidden_methods))
			show_404();

		$model = $this->model;

		$input = $this->input->get();

		$options= [];
		if (isset($input['filters']))
			$options['filters'] = $input['filters'];

		if (isset($input['order']))
			$options['order'] = $input['order'];

		if (isset($input['include_relations']))
			$options['include_relations'] = $input['include_relations'];

		if (isset($input['page']))
			$options['page'] = $input['page'];

		$result = $this->$model->get($options);

		$without_fields = [];
		if (!empty($this->api_excluded_fields)) {
			
			foreach ($result as $record) {
				foreach ($this->api_excluded_fields as $field) {
					unset($record[$field]);
				}
				$without_fields[] = $record;
			}
		}

		$this->response->json($without_fields);
	}

	public function delete()
	{
		if (in_array("delete", $this->hidden_methods))
			show_404();

		$id = $this->input->post("id");

		$model = $this->model;
		$this->$model->delete($id);

		$this->response->json();
	}

}
