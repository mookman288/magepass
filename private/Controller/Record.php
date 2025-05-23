<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Record {
		public function get(App $app, $vaultId, $archiveId, $id) {
			$archive = $app -> getArchive($archiveId);

			$vaultKey = $app -> getVaultKey($vaultId);

			if (empty($vaultKey)) {
				throw new \ErrorException("Your session has expired. Please login again.");
			}

			$vaultKey = $app -> decrypt($vaultKey);

			$records = $app -> getRecords($id, $vaultKey);

			foreach($records as $record) {
				if ($record -> id == $id) {
					return $app -> json($record);
				}
			}
		}

		public function post(App $app, $vaultId, $archiveId, $id) {
			$name = $app -> post('name');
			$content = $app -> post('content');
			$delete = $app -> post('delete');

			if (!empty($delete)) {
				$statement = $app -> db -> prepare(
					"DELETE FROM record WHERE id = :id"
				);

				$statement -> bindValue(':id', $id);

				$statement -> execute();

				$_SESSION['flash']['success'][] = "Your record was deleted.";
			} else {
				try {
					$vaultKey = $app -> getVaultKey($vaultId);

					if (empty($vaultKey)) {
						throw new \ErrorException("Your session has expired. Please login again.");
					}

					$vaultKey = $app -> decrypt($vaultKey);

					if (!$name) {
						throw new \ErrorException("You must choose a name for record #{$index}.");
					}

					if (!$content) {
						throw new \ErrorException("You must set content for record \"$name\".");
					}

					$statement = $app -> db -> prepare(
						"UPDATE record SET name = :name, content = :content, updated_at = NOW() WHERE id = :id"
					);

					$statement -> bindValue(':name',  $app -> encrypt($name, $vaultKey));
					$statement -> bindValue(':content',  $app -> encrypt($content, $vaultKey));
					$statement -> bindValue(':id', $id);

					$statement -> execute();

					$_SESSION['flash']['success'][] = "Your record was updated.";
				} catch(\ErrorException $e) {
					$_SESSION['flash']['error'][] = $e -> getMessage();
				}
			}

			return $app -> redirect("vault/$vaultId");
		}
	}
?>