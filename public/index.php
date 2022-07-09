<?php
	require_once(realpath(sprintf("%s/../private/App.php", __DIR__)));

	use MagePass\App as App;

	try {
		$app = new App();

		if (!empty($app -> config)) {
			$routes = array(
				'unprotected' => array(
					'/register' => 'Register',
					'/login' => 'Login',
					'/logout' => 'Logout'
				),
				'protected' => array(
					'/' => 'Home',
					'/home' => 'Home',
					'/vault/create' => 'VaultCreate',
					'/vault/(.*)' => 'Vault'
				)
			);
		} else {
			$routes = array(
				'unprotected' => array(
					'/' => 'Install'
				)
			);
		}

		ob_start();

		foreach($routes as $type => $set) {
			foreach($set as $pattern => $controller) {
				preg_match(sprintf("/^%s$/", preg_quote($pattern, '/')), $app -> uri, $results);

				if (!empty($results)) {
					$app -> route = $pattern;
					$app -> routeName = $controller;
					$controllerPath = $app -> path("private/Controller/{$controller}.php");

					if (file_exists($controllerPath)) {
						require_once($controllerPath);

						$controller = sprintf("MagePass\Controller\%s", $controller);

						if (class_exists($controller)) {
							$parameters = $results;

							if ($type == 'protected') {
								if (empty($app -> user) || empty($app -> userKey)) {
									$_SESSION['redirect'] = $app -> uri;

									header("Location: " . $app -> getUrl('login'), TRUE, 302);

									exit;
								}
							}

							$parameters[0] = $app;

							call_user_func_array(array((new $controller), strtolower($app -> method)), $parameters);
						}
					}

					break;
				}
			}
		}

		$render = ob_get_clean();

		if (!$render) {
			$app -> error(404);
		} else {
			print($render);
		}
	} catch(\PDOException | \ErrorException $e) {
		error_log(sprintf("[%s] %s", $app -> code, $e -> getMessage()));

		http_response_code($app -> code);
	}

	$_SESSION['error'] = null;
	$_SESSION['success'] = null;
	$_SESSION['warning'] = null;
	$_SESSION['message'] = null;
?>