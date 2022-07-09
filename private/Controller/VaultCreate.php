<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class VaultCreate {
		public function get(App $app) {
			$app -> view('vault-create');
		}

		public function post(App $app) {
			$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
			$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

			try {
				if (!$name) {
					throw new \ErrorException("You must choose a name for this vault.");
				}

				if (!$password) {
					throw new \ErrorException("You must supply a password for this vault.");
				}

				if ($confirm != $password) {
					throw new \ErrorException("Your passwords do not match.");
				}

				$statement = $app -> db -> prepare(
					"INSERT INTO vault (user_id, name, password) VALUES (:user_id, :name, :password)"
				);

				$statement -> bindValue(':user_id', $_SESSION['user']['id']);
				$statement -> bindValue(':name',  $app -> encrypt($name, $key));
				$statement -> bindValue(':password', $app -> hash($password));

				$statement -> execute();

				header("Location: home", TRUE, 302);
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$app -> view('vault-create', array(
				'name' => $name
			));
		}
	}
?>