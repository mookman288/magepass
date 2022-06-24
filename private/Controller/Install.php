<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Install {
		public function get(App $app) {
			if (version_compare(phpversion(), '7.3', '<')) {
				$_SESSION['error'][] = "This environment does not satisfy the requirement of at least PHP 7.3.";
			}

			$app -> view('install');
		}

		public function post(App $app) {
			$databaseHost = filter_input(INPUT_POST, 'databaseHost', FILTER_SANITIZE_STRING);
			$databasePort = filter_input(INPUT_POST, 'databasePort', FILTER_SANITIZE_STRING);
			$databaseName = filter_input(INPUT_POST, 'databaseName', FILTER_SANITIZE_STRING);
			$databaseUser = filter_input(INPUT_POST, 'databaseUser', FILTER_SANITIZE_STRING);
			$databasePass = filter_input(INPUT_POST, 'databasePass', FILTER_SANITIZE_STRING);
			$sessionLength = filter_input(INPUT_POST, 'sessionLength', FILTER_SANITIZE_NUMBER_FLOAT);
			$hcaptchaSiteKey = filter_input(INPUT_POST, 'hcaptchaSiteKey', FILTER_SANITIZE_STRING);
			$hcaptchaSecretKey = filter_input(INPUT_POST, 'hcaptchaSecretKey', FILTER_SANITIZE_STRING);

			try {
				if (empty($databaseHost)) {
					$databaseHost = 'localhost';
				}

				if (empty($databasePort)) {
					$databasePort = 3306;
				}

				$app -> connect($databaseHost, $databasePort, $databaseUser, $databasePass, $databaseName);

				if (empty($databaseName)) {
					$databaseName = 'magepass';

					$app -> db -> query("CREATE DATABASE IF NOT EXISTS `magepass`");

					$app -> db -> query(sprintf(
						"GRANT ALL ON `magepass`.* TO %s@%s",
						$app -> db -> quote($databaseUser),
						$app -> db -> quote($databaseHost ?? 'localhost')
					));

					$app -> db -> query("FLUSH PRIVILEGES");

					$app -> connect($databaseHost, $databasePort, $databaseUser, $databasePass, $databaseName);
				}

				$app -> update(true);

				$salt = openssl_random_pseudo_bytes(2048);
				$key = openssl_random_pseudo_bytes(2048);
				$ciphers = openssl_get_cipher_methods();
				$cipher = $ciphers[0];

				foreach(array('256', '192', '128') as $bit) {
					if (in_array("aes-$bit-gcm", $ciphers)) {
						$cipher = "aes-$bit-gcm";

						break;
					} elseif (in_array("aes-$bit-ctr", $ciphers)) {
						$cipher = "aes-$bit-ctr";

						break;
					}
				}

				$app -> setConfig(array(
					'app' => array(
						'dev' => false,
						'inviteCode' => $app -> generateInviteCode($salt),
						'sessionLength' => $sessionLength,
						'cipher' => $cipher,
						'salt' => bin2hex($salt),
						'key' => bin2hex($key)
					),
					'captcha' => array(
						'hcaptchaSiteKey' => $hcaptchaSiteKey,
						'hcaptchaSecretKey' => $hcaptchaSecretKey
					),
					'database' => array(
						'databaseHost' => $databaseHost,
						'databasePort' => $databasePort,
						'databaseName' => $databaseName,
						'databaseUser' => $databaseUser,
						'databasePass' => $databasePass,
					)
				));

				$_SESSION['success'][] = "The application has been successfully installed.";

				header("Location: {$app -> uri}", TRUE, 301);
			} catch (\PDOException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$app -> view('install', array(
				'databaseHost' => $databaseHost,
				'databasePort' => $databasePort,
				'databaseName' => $databaseName,
				'databaseUser' => $databaseUser,
				'databasePass' => $databasePass,
				'sessionLength' => $sessionLength,
				'enablePin' => $enablePin,
				'hcaptchaSiteKey' => $hcaptchaSiteKey,
				'hcaptchaSecretKey' => $hcaptchaSecretKey
			));
		}
	}
?>