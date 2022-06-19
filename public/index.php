<?php
	require_once(realpath(sprintf("%s/../private/App.php", __DIR__)));

	use MagePass\App as App;

	try {
		$app = new App();

		if (!empty($app -> config)) {
			$routes = array(
				'unprotected' => array(
					'/login' => 'Login'
				),
				'protected' => array(
					'/' => 'Home'
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
					$app -> route = $controller;
					$controllerPath = $app -> path("private/Controller/{$controller}.php");

					if (file_exists($controllerPath)) {
						require_once($controllerPath);

						$controller = sprintf("MagePass\Controller\%s", $controller);

						if (class_exists($controller)) {
							$parameters = $results;

							if ($type == 'protected') {

								if (empty($_SESSION['user'])) {
									$_SESSION['redirect'] = $app -> uri;
									$app -> route = "Login";

									$app -> view('login');

									break 2;
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
die();
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