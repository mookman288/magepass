<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class VaultCreate {
		public function get(App $app) {
			$app -> view('vault-create');
		}

		public function post(App $app) {
			$name = $app -> post('name');
			$password = $app -> post('password');
			$confirm = $app -> post('confirm');

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

				$statement -> bindValue(':user_id', $app -> user -> id);
				$statement -> bindValue(':name',  $app -> encrypt($name, $app -> userKey));
				$statement -> bindValue(':password', $app -> hash($password));

				$statement -> execute();

				$_SESSION['flash']['success'][] = "Your vault was created.";

				return $app -> redirect("home");
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$app -> view('vault-create', array(
				'name' => $name
			));
		}
	}
?>