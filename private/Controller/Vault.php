<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Vault {
		public function get(App $app, $id) {
			$vault = $app -> getVault($id);

			$vaultKey = $app -> decrypt($_SESSION[$vault -> sessionID]);

			if (!empty($vaultKey)) {
				$archives = $app -> getArchives($vault -> id);

				return $app -> view('vault', array(
					'vaultKey' => !empty($vaultKey),
					'vault' => $vault,
					'archives' => $archives
				));
			}

			return $app -> redirect("home");
		}

		public function post(App $app, $id) {
			$password = $app -> post('password');

			try {
				if (!$password) {
					throw new \ErrorException("You must supply a password for this vault.");
				}

				$vault = $app -> getVault($id, $password);

				if (!$app -> hashVerify($password, $vault -> password)) {
					throw new \ErrorException("The password supplied is incorrect.");
				}

				$vaultKey = $app -> getKey($password, $app -> user -> salt);

				$_SESSION[$vault -> sessionID] = $app -> encrypt($vaultKey);
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return $this -> get($app, $id);
		}

		public function edit(App $app, $id) {
			$name = $app -> post('name');
			$password = $app -> post('password');
			$confirm = $app -> post('confirm');

			try {
				if (!$name) {
					throw new \ErrorException("You must choose a name for this vault.");
				}

				if (!empty($password)) {
					if ($confirm != $password) {
						throw new \ErrorException("Your passwords do not match.");
					}

					$statement = $app -> db -> prepare(
						"UPDATE vault SET name = :name, password = :password, updated_at = NOW() WHERE id = :id"
					);

					$statement -> bindValue(':name',  $app -> encrypt($name, $app -> userKey));
					$statement -> bindValue(':password', $app -> hash($password));

					$statement -> execute();

					return $app -> redirect("home");
				}

				$statement = $app -> db -> prepare(
					"UPDATE vault SET name = :name, updated_at = NOW() WHERE id = :id"
				);

				$statement -> bindValue(':name',  $app -> encrypt($name, $app -> userKey));

				$statement -> execute();

				$_SESSION['flash']['success'][] = "Your vault was updated.";
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return $app -> get($app, $id);
		}
	}
?>