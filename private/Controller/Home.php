<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Home {
		public function get(App $app) {
			$vaults = array();

			$result = $app -> db -> query("SELECT * FROM vault");

			while ($row = $result -> fetchObject()) {
				$vaults[] = $row;
			}

			$app -> view('home', array(
				'vaults' => $vaults
			));
		}
	}
?>