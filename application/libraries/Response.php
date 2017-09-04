<?php

class Response {

	//RETURNS JSON RESPONSE
	/*EXAMPLE:
	{
		"status": "success" (default),
		"example_meta_data": true,
		"data" : {
			...
		}
	}
	*/
	public function json($data = [], $meta = []){

		if (!isset($meta['status'])) {//if $meta isn't set at all, assume status == success
			$meta = [];
			$meta['status'] = "success";

		} 
		elseif (gettype($meta) == "string") {
			$status = $meta;
			$meta = [];
			$meta['status'] = $status; //if $meta is string, pass it directly in as a status
		}

		$response = $meta;

		if ($data)
			$response['data'] = $data; //set data property with data if data ins't empty

		echo json_encode($response);die;

	}

}