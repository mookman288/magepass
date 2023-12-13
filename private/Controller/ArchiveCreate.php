<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class ArchiveCreate {
		public function get(App $app, $vaultId) {
			header("Location: " . $app -> getUrl("vault/$vaultId"));
		}

		public function post(App $app, $vaultId) {
			$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

			$vaultKey = $app -> decrypt($_SESSION['vaultKey']);

			$vault = $app -> getVault($vaultId);
			try {
				if (empty($vault)) {
					throw new \ErrorException("You must select a vault you control.");
				}

				if (!$name) {
					throw new \ErrorException("You must choose a name for this archive.");
				}

				$statement = $app -> db -> prepare(
					"INSERT INTO archive (vault_id, name) VALUES (:vault_id, :name)"
				);

				$statement -> bindValue(':vault_id', $vaultId);
				$statement -> bindValue(':name',  $app -> encrypt($name, $vaultKey));

				$statement -> execute();

				header("Location: " . $app -> getUrl("vault/$vaultId"));
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			$archives = $app -> getArchives($vault -> id);

			return $app -> view('vault', array(
				'vaultKey' => !empty($vaultKey),
				'vault' => $vault,
				'archives' => $archives
			));
		}
	}
?>