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
		public $title;
		public $uri;

		public function __construct() {
			$this -> session();

			$this -> root = realpath(sprintf("%s/../", __DIR__));
			$this -> config = $this -> getConfig();
			$this -> method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_URL) ?? filter_input(INPUT_ENV, 'REQUEST_METHOD', FILTER_SANITIZE_URL);
			$this -> request = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL) ?? filter_input(INPUT_ENV, 'REQUEST_URI', FILTER_SANITIZE_URL);
			$this -> uri = parse_url($this -> request)['path'] ?? '/';
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

		public function error($code = 500, $message = null) {
			$this -> code = intval($code) ?? 500;

			ob_get_clean();

			throw new \ErrorException($message);
		}

		private function getConfig() {
			if (file_exists($this -> path('private/config.ini'))) {
				$config = parse_ini_file($this -> path('private/config.ini'), true);

				return $config;
			}

			return false;
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
			if (!session_name()) session_start();

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

		public function view($file, $parameters = array()) {
			ob_start();

			$this -> render($file, $parameters);

			$this -> body = ob_get_clean() ?? null;

			$this -> render('layout', $parameters);
		}

		public function path($pathFromRoot) {
			return sprintf("%s/%s", $this -> root, escapeshellcmd($pathFromRoot));
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