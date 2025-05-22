<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Install {
		public function get(App $app) {
			if (version_compare(phpversion(), '7.3', '<')) {
				$_SESSION['error'][] = "This environment does not satisfy the requirement of at least PHP 7.3.";
			}

			$_SESSION['databaseNameExample'] = substr(
				sprintf("%s%s",
					range('a', 'z')[random_int(0, 25)],
					bin2hex(openssl_random_pseudo_bytes(16))
				),
			0, 16);

			$app -> view('install', array('databaseNameExample' => $_SESSION['databaseNameExample']));
		}

		public function post(App $app) {
			$sessionPath = false;
			$appName = $app -> post('appName');
			$databaseHost = $app -> post('databaseHost');
			$databasePort = $app -> post('databasePort');
			$databaseName = $app -> post('databaseName');
			$databaseUser = $app -> post('databaseUser');
			$databasePass = $app -> post('databasePass');
			$sessionLength = $app -> post('sessionLength', FILTER_SANITIZE_NUMBER_FLOAT);
			$hcaptchaSiteKey = $app -> post('hcaptchaSiteKey');
			$hcaptchaSecretKey = $app -> post('hcaptchaSecretKey');

			try {
				if (empty($appName)) {
					$appName = 'MageLock';
				}

				if (empty($databaseHost)) {
					$databaseHost = 'localhost';
				}

				if (empty($databasePort)) {
					$databasePort = 3306;
				}

				if ($databaseName == $_SESSION['databaseNameExample']) {
					$app -> connect($databaseHost, $databasePort, $databaseUser, $databasePass, '');

					$app -> db -> query("CREATE DATABASE IF NOT EXISTS `{$databaseName}`");

					$app -> db -> query(sprintf(
						"GRANT ALL ON `{$databaseName}`.* TO %s@%s",
						$app -> db -> quote($databaseUser),
						$app -> db -> quote($databaseHost ?? 'localhost')
					));

					$app -> db -> query("FLUSH PRIVILEGES");
				}

				if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
					$whoami = posix_getpwuid(posix_geteuid())['name'] ?? null;
				}

				if (empty($whoami)) {
					if (function_exists('shell_exec') && is_callable('shell_exec')) {
						$whoami = trim(shell_exec('whoami'));
					} elseif (function_exists('exec') && is_callable('exec')) {
						$whoami = exec('whoami');
					}
				}

				if (!empty($whoami)) {
					if (@mkdir($app -> sessionPath, 2700)) {
						if (@chown($app -> sessionPath, $whoami)) {
							$sessionPath = true;
						}
					}
				}

				$app -> connect($databaseHost, $databasePort, $databaseUser, $databasePass, $databaseName);

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
						'name' => $appName,
						'dev' => false,
						'sessionLength' => $sessionLength,
						'sessionPath' => $sessionPath,
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
			} catch (\Throwable $e) {
				$_SESSION['flash']['error'][] = $e -> getMessage();
			}

			return $app -> view('install', array(
				'databaseHost' => $databaseHost,
				'databasePort' => $databasePort,
				'databaseName' => $databaseName,
				'databaseUser' => $databaseUser,
				'databasePass' => $databasePass,
				'sessionLength' => $sessionLength,
				'hcaptchaSiteKey' => $hcaptchaSiteKey,
				'hcaptchaSecretKey' => $hcaptchaSecretKey
			));
		}
	}
?>