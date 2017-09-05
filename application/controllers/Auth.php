<?php

class Auth extends Base_Controller {

	public $model = "UserModel";
	public $password_min_length = PSW_MIN_LENGTH;

	public $hidden_methods = ["save",'save_batch','delete','find','get'];

	public function login()
	{
		$model = $this->model
		$username = $this->input->post("email");
		$password = $this->input->post("password");

		$model = $this->user_model;
		$user_details = $this->$model->authenticate($username, $password);

		if ($user_details && $password != "") {
			//USER IS AUTHENTICATED

			//update last login
			$this->$model->save([
				"id" => $user_details->id,
				"last_login" => date("Y-m-d H:i:s")
			]);

			$response = [
				'status' => "success",
				"apiKey" => $user_details->api_key,
				'message' => "Login successful."
			];
			
		}
		else {
			$response = [
				'status' => "denied",
				'message' => "Login attempt failed."
			];
		}

		$this->response->json([],$response);
	}

	public function logout() {
		$model = $this->user_model;

		$this->response->json([],[
			"status" => "success",
			"message" => "You have been logged out"
		]);
	}

	public function new_api_key() {

		$user_id = $this->input->post("user_id");
		$key = hash("sha256", mt_rand(10000,1000000000).time().$user_id);

		$this->db->insert('api_keys',[
			'user_id' => $user_id,
			"api_key" => $key
		]);

		$this->response->json([],[
			"status" => "success",
			"newKey" => $key
		]);
	}

	public function password($request)
	{
		$model = $this->user_model;

		if ($request == "reset") {

			$post = $this->input->post();

			$this->load->library('form_validation');

			$this->form_validation
				->set_rules('password','Password','required|min_length['.$this->password_min_length."]")
				->set_error_delimiters('', ' ');

			if ($this->form_validation->run() == false) {

				$response = [
					'status' => "failed",
					'message' => validation_errors()
				];
			}
			elseif ($post['confirm'] != $post['password']) {
				$response = [
					'status' => "failed",
					'message' => "Your password doesn't match the confirmation field"
				];
			}
			else {
				//validate token
				$token_record = $this->db->get_where("user_tokens",['token' => $post['token']])->row();

				$booTokenValid = false;
				if ($token_record) {
					$user_details = $this->db->get_where('users',['id' => $token_record->user_id])->row();

					if ($user_details) {
						$booTokenValid = true;
					}
				}

				if ($booTokenValid) {
					//check length

					$result = $this->$model->changePassword($user_details->id,$post['confirm']);

					//delete token
					$r = $this->db->where('id',$token_record->id)
						->delete("user_tokens");

					$response = [
						'status' => "success",
						'message' => "Password changed successfully."
					];
				}
				else {
					$response = [
						'status' => "failed",
						'message' => 'Invalid token. Please go initiate another password reset request.'
					];
				}
			}

		}
		elseif ($request == "reset-request") {
			$post = $this->input->post();

			//find user by email
			$user = $this->db->get_where($this->$model->table, ['email' => $post['email']])->row();

			if ($user) {
				$token = $this->$model->generateUserToken($user->id,1);

				$data = [
					'token' => $token['token'],
					'expiry' => $token['expiry'],
					'issued' => date("Y-m-d H:i:s"),
					'user_id' => $user->id
				];

				//insert into pw_reset_table
				$r = $this->db->insert('user_tokens',$data);

				$this->$model->sendEmail($user->email, 'emails/password_reset', $data, "Password Reset Request for ".APP_NAME);

			}
			$response = [
				'status' => "success",
				"message" => "If we have your email on file we have sent you password instructions, which you should receive within 15 minutes."
			];
		}
		elseif ($request == 'change') {

			$post = $this->input->post();

			if ($post['new-password'] != $post['confirm-password']) {
				$response = [
					'status' => "failed",
					'message' => "Passwords don't match."
				];
			}
			elseif (strlen($post['new-password']) < PSW_MIN_LENGTH) {
				$response = [
					'status' => "failed",
					'message' => "Password has to be at least ".PSW_MIN_LENGTH." characters long."
				];
			}
			else {
				//validate old password
				$request_headers = $this->input->request_headers();

				$api_key = $request_headers['Authorization'];

				$user = $this->$model->getUserFromApiKey($api_key);

				if ($this->$model->authenticate($user['email'],$post['password'])) {
					$result = $this->$model->changePassword($user['id'],$post['new-password']);

					$response = [
						'status' => "success",
						'message' => "Password changed successfully."
					];
				}
				else {
					$response = [
						'status' => "failed",
						'message' => 'Password incorrect.'
					];
				}
			}
		}

		echo json_encode($response);
	}

}
