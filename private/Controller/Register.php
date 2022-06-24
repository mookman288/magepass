<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Register {
		public function get(App $app) {
			$app -> view('register');
		}

		public function post(App $app) {
			$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
			$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_URL);
			$confirm = filter_input(INPUT_POST, 'confirm', FILTER_SANITIZE_STRING);
			$inviteCode = filter_input(INPUT_POST, 'inviteCode', FILTER_SANITIZE_STRING);
			$captcha = filter_input(INPUT_POST, 'h-captcha', FILTER_SANITIZE_STRING);

			try {
				if ($app -> db -> query('SELECT COUNT(*) FROM user') -> fetchColumn()) {
					if ($app -> config['app']['inviteCode'] != $app -> generateInviteCode($app -> config['app']['salt'])) {
						throw new \ErrorException("The invite code you supplied has expired or is invalid.");
					}
				}

				if (!$username) {
					throw new \ErrorException("You must supply a username.");
				}

				if (!$password) {
					throw new \ErrorException("You must supply a password.");
				}

				if ($confirm != $password) {
					throw new \ErrorException("Your passwords do not match.");
				}

				if (!empty($app -> config['app']['enablePin']) && !$pin) {
					throw new \ErrorException("You must supply a PIN.");
				}

				if (!empty($app -> config['captcha']['hcaptchaSiteKey']) && !empty($app -> config['captcha']['hcaptchaSecretKey'])) {
					if (!$app -> validateCaptcha($captcha)) {
						throw new \ErrorException("Your CAPTCHA response was incorrect.");
					}
				}

				$users = $app -> db -> query("SELECT * FROM user");

				while ($row = $users -> fetchObject()) {
					if ($app -> hashVerify($username, $row -> username)) {
						throw new \ErrorException("These credentials are taken. Please choose new credentials.");
					}
				}

				$statement = $app -> db -> prepare(
					"INSERT INTO user (username, email, password, salt) VALUES (:username, :email, :password, :salt)"
				);

				$salt = openssl_random_pseudo_bytes(2048);

				$key = $app -> getKey($password, $salt);

				$statement -> bindValue(':username', $app -> hash($username, $key));
				$statement -> bindValue(':password', $app -> hash($password, $key));
				$statement -> bindValue(':email', $app -> hash($email, $key));
				$statement -> bindValue(':salt', $salt);

				$statement -> execute();

				header("Location: login", TRUE, 302);
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$app -> view('register', array(
				'username' => $username,
				'email' => $email,
				'inviteCode' => $inviteCode
			));
		}
	}
?>