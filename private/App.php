<?php
	namespace MagePass;

	class App {
		public $root;
		public $code;
		public $config;
		public $db;
		public $description;
		public $method;
		public $request;
		public $route;
		public $routeName;
		public $title;
		public $uri;

		public function __construct() {
			$this -> session();

			$this -> root = realpath(sprintf("%s/../", __DIR__));
			$this -> config = $this -> getConfig();
			$this -> method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_URL) ?? filter_input(INPUT_ENV, 'REQUEST_METHOD', FILTER_SANITIZE_URL);
			$this -> request = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL) ?? filter_input(INPUT_ENV, 'REQUEST_URI', FILTER_SANITIZE_URL);

			$this -> uri = parse_url($this -> request)['path'] ?? '/';

			if (
				!empty($this -> config['database']['databaseHost']) &&
				!empty($this -> config['database']['databasePort']) &&
				!empty($this -> config['database']['databaseUser']) &&
				!empty($this -> config['database']['databaseName'])
			) {
				$this -> connect(
					$this -> config['database']['databaseHost'],
					$this -> config['database']['databasePort'],
					$this -> config['database']['databaseUser'],
					$this -> config['database']['databasePass'],
					$this -> config['database']['databaseName']
				);
			}
		}

		public function connect($databaseHost, $databasePort, $databaseUser, $databasePass = null, $databaseName = null) {
			$databaseHost = (!empty($databaseHost)) ? $databaseHost : 'localhost';
			$databasePort = (!empty($databasePort)) ? $databasePort : 3306;

			$dsn = "mysql:host=$databaseHost;port=$databasePort";

			if (!empty($databaseName)) {
				$dsn .= ";dbname=$databaseName";
			}

			$this -> db = new \PDO($dsn, $databaseUser, $databasePass, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false));
		}

		public function decrypt($data, $key) {
			$ivLength = openssl_cipher_iv_length($app -> config['app']['cipher']);

			if (stripos($app -> config['app']['cipher'], 'gcm') !== false) {
				$tag = substr($data, 0, 16);
				$iv = substr($data, 16, $ivLength);
				$data = substr($data, 16 + $ivLength);
				$content = openssl_decrypt($data, $app -> config['app']['cipher'], $key, 0, $iv, $tag);
			} else {
				$hmac = substr($data, 0, 64);
				$iv = substr($data, 64, $ivLength);
				$data = substr($data, 64 + $ivLength);
				$content = openssl_decrypt($data, $app -> config['app']['cipher'], $key, OPENSSL_RAW_DATA, $iv);
				$compare = hash_hmac('sha512', $content, $key, true);

				if (!hash_equals($hmac, $compare)) {
					throw new \ErrorException("Decrypted data failed hmac authentication.");
				}
			}

			return $content;
		}

		public function encrypt($data, $key) {
			$ivLength = openssl_cipher_iv_length($app -> config['app']['cipher']);
			$iv = openssl_random_pseudo_bytes($ivLength);

			if (stripos($app -> config['app']['cipher'], 'gcm') !== false) {
				$tag = openssl_random_psuedo_bytes(16);
				$content = openssl_encrypt($data, $app -> config['app']['cipher'], $key, 0, $iv, $tag);
				$content = sprintf("%s%s%s", $tag, $iv, $content);
			} else {
				$content = openssl_encrypt($data, $app -> config['app']['cipher'], $key, OPENSSL_RAW_DATA, $iv);
				$hmac = hash_hmac('sha512', $content, $key, true);
				$content = sprintf("%s%s%s", $hmac, $iv, $content);
			}

			return $content;
		}

		public function error($code = 500, $message = null) {
			$this -> code = intval($code) ?? 500;

			ob_get_clean();

			throw new \ErrorException($message);
		}

		public function generateInviteCode($salt) {
			return hash('crc32b', $salt . date('YmdH'));
		}

		private function getConfig() {
			if (file_exists($this -> path('private/config.ini'))) {
				$config = parse_ini_file($this -> path('private/config.ini'), true);

				if (!empty($config['app']['salt'])) {
					$config['app']['salt'] = hex2bin($config['app']['salt']);
				}

				if (!empty($config['app']['key'])) {
					$config['app']['key'] = hex2bin($config['app']['key']);
				}

				return $config;
			}

			return false;
		}

		public function getKey($password, $salt) {
			return hash_pbkdf2('sha512', $password, $salt, 100000, 2048, true);
		}

		public function getUrl($relative = null) {
			$url = '';

			for ($i = 1; $i < substr_count($this -> route, '/'); $i++) {
				$url .= "../";
			}

			return $url . $relative;
		}

		public function hash($value) {
			return password_hash($value, PASSWORD_DEFAULT);
		}

		public function hashVerify($value, $hash) {
			return password_verify($value, $hash);
		}

		public function path($pathFromRoot) {
			return sprintf("%s/%s", $this -> root, escapeshellcmd($pathFromRoot));
		}

		public function render($file, $parameters = array()) {
			$path = $this -> path("private/View/$file.php");

			if (!file_exists($path)) {
				$this -> error(500, "template '$file' does not exist.");
			} else {
				extract($parameters);

				$app = $this;

				include($path);

				$this -> title = $title ?? null;
				$this -> description = $description ?? null;
			}
		}

		private function session() {
			if (!empty($this -> config['app']['sessionLength'])) {
				ini_set('session.gc_maxlifetime', round(60 * $this -> config['app']['sessionLength']));
				session_set_cookie_params(round(60 * $this -> config['app']['sessionLength']));
			}

			session_start();

			$_SESSION['error'] = array();
			$_SESSION['success'] = array();
			$_SESSION['warning'] = array();
			$_SESSION['message'] = array();
		}

		public function setConfig($config) {
			$ini = array();

			foreach($config as $header => $configSet) {
				$ini[] = "[$header]";

				foreach($configSet as $key => $value) {
					$ini[] = "$key=$value";
				}

				$ini[] = '';
			}

			file_put_contents($this -> path('private/config.ini'), implode(PHP_EOL, $ini));
		}

		public function update($force = false) {
			$schema = array_diff(scandir($this -> path("private/Schema")), array('..', '.'));

			foreach($schema as $filename) {
				if (empty($force)) {
					$statement = $this -> db -> prepare('SELECT COUNT(*) from migration WHERE `schema` = :schema');

					$statement -> bindValue(':schema', $filename);

					$statement -> execute();

					if (!empty($statement -> fetchColumn())) break;
				}

				$sql = file_get_contents($this -> path("private/Schema/$filename"));

				$statement = $this -> db -> prepare($sql);

				$statement -> execute();

				$statement = $this -> db -> prepare("INSERT INTO migration (`schema`) VALUES (:schema)");

				$statement -> bindValue(':schema', $filename);

				$statement -> execute();
			}
		}

		public function url($relative = null) {
			print($this -> getUrl($relative));
		}

		public function validateCaptcha($response) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://hcaptcha.com/siteverify");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
				'secret' => $this -> config['captcha']['hcaptchaSecretKey'],
				'response' => $response
			)));
			curl_setopt($ch, CURLOPT_POSTREDIR, 3);

			$curlResponse = curl_exec($ch);

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if (gettype($ch) == 'resource') {
				if (curl_errno($ch)) {
					throw new \ErrorException(sprintf('There was a CAPTCHA connection error: %s', curl_errno($ch)));

					return false;
				}

				curl_close($ch);
			}

			if ($curlResponse) {
				switch($code) {
					case 200:
					case 206:
						$responseData = json_decode($curlResponse);

						if ($responseData -> success) {
							return true;
						}
					break;
					default:
						throw new \ErrorException(sprintf('There was a CAPTCHA connection error: %s', $code));

						return false;
					break;
				}
			}

			return false;
		}

		public function view($file, $parameters = array()) {
			ob_start();

			$this -> render($file, $parameters);

			$this -> body = ob_get_clean() ?? null;

			$this -> render('layout', $parameters);
		}
	}

	function app() {
		global $app;

		if (empty($app)) {
			$app = new App();
		}

		return $app;
	}
?>