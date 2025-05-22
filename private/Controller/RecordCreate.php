<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class RecordCreate {
		public function get(App $app, $vaultId) {
			header("Location: " . $app -> getUrl("vault/$vaultId"));
		}

		public function post(App $app, $vaultId, $archiveId) {
			$recordNames = $app -> post('addRecordName');
			$recordContent = $app -> post('addRecordContent');

			try {
				$vaultKey = $app -> getVaultKey($vaultId);

				if (empty($vaultKey)) {
					throw new \ErrorException("Your session has expired. Please login again.");
				}

				$vaultKey = $app -> decrypt($vaultKey);

				$archive = $app -> getArchive($archiveId);

				foreach($recordNames as $index => $name) {
					$content = $recordContent[$index];

					if (!$name) {
						throw new \ErrorException("You must choose a name for record #{$index}.");
					}

					if (!$content) {
						throw new \ErrorException("You must set content for record \"$name\".");
					}

					$statement = $app -> db -> prepare(
						"INSERT INTO record (archive_id, name, content) VALUES (:archive_id, :name, :content)"
					);

					$statement -> bindValue(':archive_id', $archiveId);
					$statement -> bindValue(':name',  $app -> encrypt($name, $vaultKey));
					$statement -> bindValue(':content',  $app -> encrypt($content, $vaultKey));

					$statement -> execute();

					$_SESSION['flash']['success'][] = "Your record \"$name\" was created.";
				}
			} catch(\ErrorException $e) {
				$_SESSION['flash']['error'][] = $e -> getMessage();
			}

			return $app -> redirect("vault/$vaultId");
		}
	}
?>