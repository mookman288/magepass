<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Archive {
		public function get(App $app, $vaultId, $id) {
			$archive = $app -> getArchive($id);

			return $app -> json($archive);
		}

		public function post(App $app, $vaultId, $id) {
			$name = $app -> post('name');

			try {
				$vaultKey = $app -> getVaultKey($vaultId);

				if (empty($vaultKey)) {
					throw new \ErrorException("Your session has expired. Please login again.");
				}

				$vaultKey = $app -> decrypt($vaultKey);

				if (!$name) {
					throw new \ErrorException("You must choose a name for this archive.");
				}

				$statement = $app -> db -> prepare(
					"UPDATE archive SET name = :name, updated_at = NOW() WHERE id = :id"
				);

				$statement -> bindValue(':name',  $app -> encrypt($name, $vaultKey));
				$statement -> bindValue(':id', $id);

				$statement -> execute();

				$_SESSION['flash']['success'][] = "Your archive was updated.";
			} catch(\ErrorException $e) {
				$_SESSION['flash']['error'][] = $e -> getMessage();
			}

			return $app -> redirect("vault/$vaultId");
		}
	}
?>