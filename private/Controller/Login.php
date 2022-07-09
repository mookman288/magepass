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
			$captcha = filter_input(INPUT_POST, 'h-captcha-response', FILTER_SANITIZE_STRING);

			try {
				if (!empty($app -> config['captcha']['hcaptchaSiteKey']) && !empty($app -> config['captcha']['hcaptchaSecretKey'])) {
					if (!$app -> validateCaptcha($captcha)) {
						throw new \ErrorException("Your CAPTCHA response was incorrect.");
					}
				}

				$users = $app -> db -> query("SELECT * FROM user");

				while ($row = $users -> fetchObject()) {
					if ($app -> hashVerify($username, $row -> username)) {
						if ($app -> hashVerify($password, $row -> password)) {
							$user = $row;

							$_SESSION['key'] = $app -> getKey($password, $user -> salt);
							$_SESSION['user'] = $user;

							header("Location: " . $app -> getUrl('home'), TRUE, 302);
						}
					}
				}

				if (empty($user)) {
					throw new \ErrorException("This user account could not be found. Please try again.");
				}


			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$app -> view('login', array(
				'username' => $username
			));
		}
	}
?>