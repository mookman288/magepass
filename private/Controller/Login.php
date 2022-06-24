<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Login {
		public function get(App $app) {
			$app -> view('login');
		}

		public function post(App $app) {
			$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
			$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

			try {
				$users = $app -> db -> query("SELECT * FROM user");

				while ($row = $users -> fetchObject()) {
					if ($app -> hashVerify($username, $row -> username)) {
						if ($app -> hashVerify($password, $row -> password)) {
							$user = $row;

							$_SESSION['key'] = $app -> getKey($password, $salt);
							$_SESSION['user'] = $user;
						}
					}
				}

				if (empty($user)) {
					throw new \ErrorException("This user account could not be found. Please try again.");
				}



				header("Location: home", TRUE, 302);
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$app -> view('login', array(
				'username' => $username
			));
		}
	}
?>