<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Register {
		public function get(App $app) {
			$app -> view('register');
		}

		public function post(App $app) {
			$username = $app -> post('username');
			$password = $app -> post('password');
			$confirm = $app -> post('confirm');
			$email = $app -> post('email', FILTER_SANITIZE_URL);
			$inviteCode = $app -> post('inviteCode');
			$captcha = $app -> post('h-captcha-response');

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

				$salt = random_bytes(384);

				$key = $app -> getKey($password, $salt);

				$statement -> bindValue(':username', $app -> hash($username));
				$statement -> bindValue(':password', $app -> hash($password));
				$statement -> bindValue(':email', $app -> encrypt($email, $key));
				$statement -> bindValue(':salt', $salt);

				$statement -> execute();

				header("Location: " . $app -> getUrl('login'), TRUE, 302);
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