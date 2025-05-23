<?php
	namespace MagePass;

	class App {
		public $code;
		public $config;
		public $db;
		public $description;
		public $messageKeys;
		public $method;
		public $request;
		public $root;
		public $route;
		public $routeName;
		public $uri;
		public $title;
		public $user;
		public $userKey;

		protected $configPath;
		protected $sessionPath;

		public function __construct() {
			$this -> root = realpath(sprintf("%s/../", __DIR__));
			$this -> configPath = $this -> path('private/config.ini');
			$this -> sessionPath = $this -> path('private/Session');
			$this -> messageKeys = array(
				'error',
				'message',
				'success',
				'warning'
			);

			$this -> config = $this -> getConfig();
			$this -> method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_URL) ?? filter_input(INPUT_ENV, 'REQUEST_METHOD', FILTER_SANITIZE_URL);
			$this -> request = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL) ?? filter_input(INPUT_ENV, 'REQUEST_URI', FILTER_SANITIZE_URL);

			$this -> uri = parse_url($this -> request)['path'] ?? '/';

			$this -> session();

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

			$this -> includes();
		}

		protected function clean($input) {
			//Strip the tags.
			$input = strip_tags($input);

			//Replace all unacceptable characters.
			$string = preg_replace('/\x00|<[^>]*>?/', '', $input);

			//Return the string.
			return $input;
		}

		protected function cleanup() {
			if (!empty($this -> config['app']['sessionPath'])) {
				foreach(scandir($this -> sessionPath) as $file) {
					//If this is not a dotfile.
					if (substr($file, 0, 1) !== '.') {
						$filePath = "{$this -> sessionPath}/$file";

						if (is_file($filePath) && is_writable($filePath)) {
							if ((filemtime($filePath) + $this -> config['app']['sessionLength']) < time()) {
								unlink($filePath);
							}
						}
					}
				}
			}
		}

		protected function connect($databaseHost, $databasePort, $databaseUser, $databasePass = null, $databaseName = null) {
			$databaseHost = (!empty($databaseHost)) ? $databaseHost : 'localhost';
			$databasePort = (!empty($databasePort)) ? $databasePort : 3306;

			$dsn = "mysql:host=$databaseHost;port=$databasePort";

			if (!empty($databaseName)) {
				$dsn .= ";dbname=$databaseName";
			}

			$this -> db = new \PDO($dsn, $databaseUser, $databasePass, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false));
		}

		public function decrypt($data, $key = null) {
			$key = $key ?? $this -> config['app']['key'];
			$ivLength = openssl_cipher_iv_length($this -> config['app']['cipher']);

			if (stripos($this -> config['app']['cipher'], 'gcm') !== false) {
				$tag = substr($data, 0, 16);
				$iv = substr($data, 16, $ivLength);
				$data = substr($data, 16 + $ivLength);
				$content = openssl_decrypt($data, $this -> config['app']['cipher'], $key, 0, $iv, $tag);
			} else {
				$hmac = substr($data, 0, 64);
				$iv = substr($data, 64, $ivLength);
				$data = substr($data, 64 + $ivLength);
				$content = openssl_decrypt($data, $this -> config['app']['cipher'], $key, OPENSSL_RAW_DATA, $iv);
				$compare = hash_hmac('sha512', $content, $key, true);

				if (!hash_equals($hmac, $compare)) {
					throw new \ErrorException("Decrypted data failed hmac authentication.");
				}
			}

			//https://stackoverflow.com/a/1369946/1617361
			$unserialized = @unserialize($content);

			if ($unserialized !== false && $unserialized !== 'b:0;') {
				$content = $unserialized;
			}

			return $content;
		}

		public function encrypt($data, $key = null) {
			$key = $key ?? $this -> config['app']['key'];
			$ivLength = openssl_cipher_iv_length($this -> config['app']['cipher']);
			$iv = random_bytes($ivLength);

			if (is_array($data) || is_object($data)) {
				$data = serialize($data);
			}

			if (stripos($this -> config['app']['cipher'], 'gcm') !== false) {
				$tag = random_bytes(16);
				$content = openssl_encrypt($data, $this -> config['app']['cipher'], $key, 0, $iv, $tag, '', 16);
				$content = sprintf("%s%s%s", $tag, $iv, $content);
			} else {
				$content = openssl_encrypt($data, $this -> config['app']['cipher'], $key, OPENSSL_RAW_DATA, $iv);
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

		protected function generateInviteCode($salt) {
			return substr(hash('sha512', $salt . date('YmdH')), date('d'), 16);
		}

		public function getArchive($id) {
			$statement = $this -> db -> prepare("SELECT * FROM archive WHERE id = :id");

			$statement -> bindValue(':id', $id);

			$statement -> execute();

			$archive = $statement -> fetchObject();

			$vaultKey = $this -> getVaultKey($archive -> vault_id);

			if (empty($vaultKey)) {
				throw new \ErrorException("Your session has expired. Please login again.");
			}

			$vaultKey = $this -> decrypt($vaultKey);

			$archive -> name = $this -> decrypt($archive -> name, $vaultKey);
			$archive -> content = $this -> getRecords($archive -> id, $vaultKey);

			return $archive;
		}

		public function getArchives($id) {
			$vaultKey = $this -> getVaultKey($id);

			if (empty($vaultKey)) {
				throw new \ErrorException("Your session has expired. Please login again.");
			}

			$vaultKey = $this -> decrypt($vaultKey);

			$archives = array();

			$statement = $this -> db -> prepare("SELECT * FROM archive WHERE vault_id = :vault_id");

			$statement -> bindValue(':vault_id', $id);

			$statement -> execute();

			while ($archive = $statement -> fetchObject()) {
				$archive -> name = $this -> decrypt($archive -> name, $vaultKey);
				$archive -> content = $this -> getRecords($archive -> id, $vaultKey);

				$archives[$archive -> id] = $archive;
			}

			return $archives;
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
			if (isset($this -> config['app']['argon2'])) {
				return sodium_crypto_pwhash(
					2048,
					$password,
					$salt,
					SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
					SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
					SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
				);
			}

			return hash_pbkdf2('sha512', $password, $salt, $this -> config['app']['iterations'] ?? 100000, 2048, true);
		}

		public function getUrl($relative = null) {
			$route = sprintf("%s/", rtrim($this -> route, '/'));
			$url = '';

			for ($i = 1; $i < substr_count($route, '/'); $i++) {
				$url .= "../";
			}

			return $url . $relative;
		}

		public function getRecords($archiveId, $vaultKey) {
			$records = array();
			$recordStatement = $this -> db -> prepare("SELECT * FROM record WHERE archive_id = :archive_id");

			$recordStatement -> bindValue(':archive_id', $archiveId);

			$recordStatement -> execute();

			while ($record = $recordStatement -> fetchObject()) {
				$record -> name = $this -> decrypt($record -> name, $vaultKey);
				$record -> content = $this -> decrypt($record -> content, $vaultKey);

				$records[$record -> id] = $record;
			}

			return $records;
		}

		public function getVault($id) {
			try {
				$statement = $this -> db -> prepare("SELECT * FROM vault WHERE id = :id AND user_id = :user_id");

				$statement -> bindValue(':id', $id);
				$statement -> bindValue(':user_id', $this -> user -> id);

				$statement -> execute();

				while ($row = $statement -> fetchObject()) {
					$statement = $this -> db -> prepare("SELECT * FROM vault WHERE id = :id AND user_id = :user_id");

					$statement -> bindValue(':id', $id);
					$statement -> bindValue(':user_id', $this -> user -> id);

					$statement -> execute();

					$vault = $statement -> fetchObject();

					$vault -> name = $this -> decrypt($vault -> name, $this -> userKey);

					$vault -> sessionID = $this -> getVaultSessionId($vault -> id);

					break;
				}
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return $vault;
		}

		public function getVaultKey($id) {
			return $_SESSION[$this -> getVaultSessionId($id)] ?? null;
		}

		protected function getVaultSessionId($id) {
			return sprintf('vaultKey_%s', $id);
		}

		public function hash($value) {
			return password_hash($value, PASSWORD_BCRYPT, array('cost' => 14));
		}

		public function hashVerify($value, $hash) {
			return password_verify($value, $hash);
		}

		protected function includes() {
			$includes = array(
				'Library',
				'Controller'
			);

			$files = array();

			foreach($includes as $include) {
				$files = array_merge($files, glob("{$this -> path("private/$include/")}*.php"));
			}

			foreach($files as $file) {
				require_once($file);
			}
		}

		public function json($data = null, $status = 'OK') {
			header('Content-Type: application/json');

			switch($status) {
				case 'ERROR':
					http_response_code(500);
				break;
				case 'FORBIDDEN':
					http_response_code(403);
				break;
				case 'USERERROR':
					http_response_code(400);
				break;
			}

			print(json_encode(array(
				'status' => $status,
				'data' => $data
			)));
		}

		public function path($pathFromRoot) {
			return sprintf("%s/%s", $this -> root, escapeshellcmd($pathFromRoot));
		}

		protected function input($input, $clean = true, $type = null) {
			if (!empty($clean)) {
				if (!defined($clean) || empty($type)) {
					if (!is_array($input)) {
						return $this -> clean($input);
					} else {
						foreach($input as $key => $value) {
							$input[$key] = $this -> input($value, $clean, $type);
						}
					}
				} else {
					return filter_input($type, $input, $clean);
				}
			}

			return $input;
		}

		public function post($field, $clean = true) {
			if (isset($_POST[$field])) {
				return $this -> input($_POST[$field], $clean, INPUT_POST);
			}

			return null;
		}

		public function query($field, $clean = true) {
			if (isset($_GET[$field])) {
				return $this -> input($_GET[$field], $clean, INPUT_GET);
			}

			return null;
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
				$this -> skipHeartbeat = $skipHeartbeat ?? null;
			}
		}

		public function redirect($route = "home") {
			session_write_close();

			header("Location: " . $this -> getUrl($route));

			exit;
		}

		private function session() {
			if (!empty($this -> config['app']['sessionPath'])) {
				//Set the session path.
				ini_set('session.save_path', $this -> sessionPath);
			}

			//Set how long the session length should be.
			$sessionLength = round(60 * $this -> config['app']['sessionLength']);

			//Make the garbage cleanup probability more aggressive (and decreases performance.)
			ini_set('session.gc_probability', 1);
			ini_set('session.gc_divisor', 10);

			//Use strict mode which avoids fixation attacks.
			ini_set('session.use_strict_mode', 1);

			if (!empty($this -> config['app']['sessionLength'])) {
				ini_set('session.gc_maxlifetime', $sessionLength);
				session_set_cookie_params($sessionLength);
			}

			$this -> cleanup();

			session_start();

			$_SESSION['last_activity'] = time();

			$_SESSION['flash'] = $_SESSION['flash'] ?? array();

			foreach($this -> messageKeys as $messageKey) {
				$_SESSION[$messageKey] = array();

				if (!empty($_SESSION['flash'][$messageKey])) {
					foreach($_SESSION['flash'][$messageKey] as $message) {
						$_SESSION[$messageKey][] = $message;
					}
				}

				$_SESSION['flash'][$messageKey] = array();
			}

			if (!empty($_SESSION['key']) && !empty($_SESSION['user'])) {
				$this -> userKey = $this -> decrypt($_SESSION['key']);
				$this -> user = $this -> decrypt($_SESSION['user']);
			}
		}

		protected function setConfig($config) {
			$ini = array();

			foreach($config as $header => $configSet) {
				$ini[] = "[$header]";

				foreach($configSet as $key => $value) {
					$ini[] = "$key=$value";
				}

				$ini[] = '';
			}

			file_put_contents($this -> configPath, implode(PHP_EOL, $ini));
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