<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Vault {
		public function get(App $app, $id) {
			$vaultKey = $app -> decrypt($_SESSION['vaultKey']);

			if (!empty($vaultKey)) {
				$vault = $app -> getVault($id);

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
			$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

			try {
				if (!$password) {
					throw new \ErrorException("You must supply a password for this vault.");
				}

				$vault = $app -> getVault($id, $password);

				if (!$app -> hashVerify($password, $vault -> password)) {
					throw new \ErrorException("The password supplied is incorrect.");
				}

				$vaultKey = $app -> getKey($password, $app -> user -> salt);

				$_SESSION['vaultKey'] = $app -> encrypt($vaultKey);
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return $this -> get($app, $id);
		}

		public function edit(App $app, $id) {
			$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
			$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
			$confirm = filter_input(INPUT_POST, 'confirm', FILTER_SANITIZE_STRING);

			try {
				if (!$name) {
					throw new \ErrorException("You must choose a name for this vault.");
				}

				if (!empty($password)) {
					if ($confirm != $password) {
						throw new \ErrorException("Your passwords do not match.");
					}

					$statement = $app -> db -> prepare(
						"UPDATE vault SET name = :name, password = :password WHERE id = :id"
					);

					$statement -> bindValue(':name',  $app -> encrypt($name, $app -> userKey));
					$statement -> bindValue(':password', $app -> hash($password));

					$statement -> execute();

					return $app -> redirect("home");
				}

				$statement = $app -> db -> prepare(
					"UPDATE vault SET name = :name WHERE id = :id"
				);

				$statement -> bindValue(':name',  $app -> encrypt($name, $app -> userKey));

				$statement -> execute();
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return $app -> get($app, $id);
		}
	}
?>