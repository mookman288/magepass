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
			$enablePin = filter_input(INPUT_POST, 'enablePin', FILTER_VALIDATE_BOOLEAN);
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

				$app -> setConfig(array(
					'app' => array(
						'enablePin' => $enablePin,
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
				'enablePin' => $enablePin,
				'hcaptchaSiteKey' => $hcaptchaSiteKey,
				'hcaptchaSecretKey' => $hcaptchaSecretKey
			));
		}
	}
?>