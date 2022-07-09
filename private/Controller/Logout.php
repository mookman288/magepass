<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Logout {
		public function get(App $app) {
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();

				setcookie(
					session_name(),
					'',
					time() - 42000,
					$params["path"] ?? null,
					$params["domain"] ?? null,
					$params["secure"] ?? null,
					$params["httponly"] ?? null
				);
			}

			session_destroy();

			header("Location: " . $app -> getUrl('login'), TRUE, 302);

			exit;
		}
	}
?>