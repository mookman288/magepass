<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Archive {
		public function get(App $app, $vaultId, $id) {
			$archive = $app -> getArchive($id);

			return $app -> json($archive);
		}

		public function post(App $app, $vaultId, $id) {
			$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

			$vault = $app -> getVault($vaultId);

			if (!isset($_SESSION[$vault -> sessionID])) {
				throw new \ErrorException("Your session has expired. Please login again.");
			}

			$vaultKey = $app -> decrypt($_SESSION[$vault -> sessionID]);

			try {
				if (!$name) {
					throw new \ErrorException("You must choose a name for this archive.");
				}

				$statement = $app -> db -> prepare(
					"UPDATE archive SET name = :name WHERE id = :id"
				);

				$statement -> bindValue(':name',  $app -> encrypt($name, $vaultKey));
				$statement -> bindValue(':id', $id);

				$statement -> execute();
			} catch(\ErrorException $e) {
				$_SESSION['error'][] = $e -> getMessage();
			}

			return $app -> redirect("vault/$vaultId");
		}
	}
?>