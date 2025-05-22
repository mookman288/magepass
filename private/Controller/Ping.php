<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Ping {
		public function get(App $app) {
			if (!empty($app -> user) && !empty($app -> userKey)) {
				return $app -> json();
			}

			return $app -> json(array(
				'message' => 'Your session has expired. Please log back in to continue.',
				'redirect' => $app -> getUrl('login')
			), 'FORBIDDEN');
		}
	}
?>