<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class VaultEdit {
		public function post(App $app, $id) {
			$name = filter_input(INPUT_POST, 'name');
			$password = filter_input(INPUT_POST, 'password');
			$confirm = filter_input(INPUT_POST, 'confirm');

			$vault = $app -> getVault($id);

			if (!isset($_SESSION[$vault -> sessionID])) {
				throw new \ErrorException("Your session has expired. Please login again.");
			}

			try {
				if (!$name) {
					throw new \ErrorException("You must choose a name for this vault.");
				}

				$statement = $app -> db -> prepare(
					"UPDATE vault SET name = :name, updated_at = NOW() WHERE id = :id"
				);

				$statement -> bindValue(':name',  $app -> encrypt($name, $app -> userKey));
				$statement -> bindValue(':id', $id);

				if (!empty($password)) {
					if ($confirm != $password) {
						throw new \ErrorException("Your vault passwords do not match.");
					}

					$statement = $app -> db -> prepare(
						"UPDATE vault password = :password WHERE id = :id"
					);

					$statement -> bindValue(':password', $app -> hash($password));
					$statement -> bindValue(':id', $id);

					$statement -> execute();

					/* @todo Get each archive here and reencrypt them. */

					return $app -> redirect("home");
				}

				$statement -> execute();
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return (new Vault()) -> get($app, $id);
		}
	}
?>