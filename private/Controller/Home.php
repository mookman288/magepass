<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Home {
		public function get(App $app) {
			$vaults = array();

			$statement = $app -> db -> prepare("SELECT * FROM vault WHERE user_id = :user_id");

			$statement -> bindValue(':user_id', $app -> user -> id);

			$statement -> execute();

			while ($row = $statement -> fetchObject()) {
				$row -> name = $app -> decrypt($row -> name, $app -> userKey);

				$vaults[] = $row;
			}

			$app -> view('home', array(
				'vaults' => $vaults
			));
		}
	}
?>