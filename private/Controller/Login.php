<?php
	namespace MagePass\Controller;

	use MagePass\App as App;

	class Login {
		public function get(App $app) {
			$this -> view('login');
		}

		public function post(App $app) {

		}
	}
?>