<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Archive {
		public function get(App $app, $vaultId, $id) {
			$archive = $app -> getArchive($id);

			return $app -> json($archive);
		}

		public function post(App $app, $vaultId, $id) {
			

			return $app -> redirect("vault/$vaultId/archive/$id");
		}
	}
?>