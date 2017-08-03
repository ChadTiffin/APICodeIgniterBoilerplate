<?php

class Import extends Base_Controller {

	public $table = "pending_transaction_imports";

	public $csv_upload_path = "../uploads/csv_imports/";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function upload_csv() {
		$config['upload_path']          = $this->csv_upload_path;
		$config['allowed_types']        = 'csv';
		$config['max_size']             = 2000;
		$config['file_name']            = time().".csv";

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('file'))
		{

			echo json_encode([
				'status' => 'failed',
				'msg' => "There was a problem uploading",
				'errors' => $this->upload->display_errors()
			]);
		}
		else
		{
			$data = $this->upload->data();

			echo json_encode([
				'status' => 'success',
				'filename' => $data['file_name']
			]);
		}
	}

	public function import_csv_to_pending() {

		$post = $this->input->post();

		$csv_filename = $_SERVER['DOCUMENT_ROOT']."/".$this->csv_upload_path.$post['filename'];
		$csv_format = $post['format'];

		if ($fh = fopen($csv_filename,'r')) {

			if ($csv_format == "RBC" || $csv_format == "PC Financial") { //some file formats include column header lines
				$file_line = fgetcsv($fh,0,","); //read and ignore header line
			}
			
			$cnt = 0;
			while (!feof($fh)) {
				$cnt++;

				$file_line = fgetcsv($fh,0,",");

				if (count($file_line) > 1) { //make sure this is not an empty line

					if ($csv_format == "RBC") {
						//check for transaction
						$amount = round($file_line[6],2);
						$date = date("Y-m-d",strtotime($file_line[2]));
						$charge_des = $file_line[4];
					}
					elseif ($csv_format == "CIBC") {
						//check for transaction
						$amount = round($file_line[2],2);

						//$date = $date_parts[2]."-".$date_parts[0]."-".$date_parts[1];
						$date = $file_line[0];
						$charge_des = $file_line[1];
					}
					elseif ($csv_format == "PC Financial") {
						$amount = round($file_line[2],2);
						$charge_des = $file_line[1];
						$date = date("Y-m-d",strtotime($file_line[0]));
					}

					$this->db->insert("pending_transaction_imports",[
						'amount' => $amount, 
						'memo' => $charge_des, 
						'tran_date' => $date, 
						'date_uploaded' => date("Y-m-d H:i:s")
					]);
				}
			}
		}

		echo json_encode([
			'status' => 'success',
		]);
	}

	public function get() {
		$imports = $this->db->get($this->table)->result_array();

		$new_imports = [];
		foreach ($imports as $tran) {
			$matching = $this->db->get_where("transactions",["tran_date" => $tran['tran_date'],"amount" => $tran['amount']])->result();

			$tran['no_match'] = true;
			if ($matching) {
				$tran['no_match'] = false;
			}

			if ($tran['amount'] != 0)
				$new_imports[] = $tran;
		}

		echo json_encode($new_imports);
	}

	public function merge() {
		$post = $this->input->post();

		//get the user who inserted
		$user = $this->db->get_where("users",["id" => $_SESSION['user_id']])->row();

		$data = [];
		$delete_ids = [];
		foreach (json_decode($post['transactions'],true) as $tran) {
			$tran['user_id'] = $user->id;
			$tran['account_id'] = $post['account_id'];

			$delete_ids[] = $tran['id'];
			unset($tran['id']);

			$data[] = $tran;
		}

		$this->db->insert_batch("transactions",$data);

		foreach ($delete_ids as $id) {
			$this->db
				->where("id",$id)
				->delete("pending_transaction_imports");
		}

		echo json_encode([
			'status' => 'success'
		]);
	}

	public function purge()
	{
		$post = $this->input->post();

		if ($post['purge']) {
			$this->db->truncate("pending_transaction_imports");
		}

		echo json_encode([
			'status' => 'success'
		]);
	}
}
