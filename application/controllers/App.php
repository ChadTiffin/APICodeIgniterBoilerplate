<?php

class App extends CI_Controller {

	public function new_transaction()
	{

		$data = [
			'page_view' => 'test',
			'page_title' => "New Transaction",
			'page_icon' => "credit-card"
		];
		$this->load->view("template",$data);
	}

}
