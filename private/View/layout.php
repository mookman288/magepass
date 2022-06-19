<!DOCTYPE html>
<html>
	<head>
		<title><?php print($app -> title ?? null); ?> | MagePass</title>
		<link rel="icon" href="favicon.ico" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="stylesheet" href="stylesheet.css" />
	</head>
	<body>
		<main>
			<aside>
				<header>
					<span class="branding">MagePass</span>
				</header>
			<?php
				switch($app -> route) {
					case 'Login':
			?>
				<nav>
					<ul>
						<li><a href="register">Register Account</a></li>
					</ul>
				</nav>
			<?php
					break;
				}
			?>
			</aside>
			<section>
			<?php if (!empty($_SESSION['error'])) { ?>
				<div class="alert error">
				<?php foreach($_SESSION['error'] as $error) { ?>
					<p><?php print($error); ?></p>
				<?php } ?>
				</div>
			<?php } ?>
			<?php if (!empty($_SESSION['message'])) { ?>
				<div class="alert message">
				<?php foreach($_SESSION['message'] as $message) { ?>
					<p><?php print($message); ?></p>
				<?php } ?>
				</div>
			<?php } ?>
			<?php if (!empty($_SESSION['success'])) { ?>
				<div class="alert success">
				<?php foreach($_SESSION['success'] as $success) { ?>
					<p><?php print($success); ?></p>
				<?php } ?>
				</div>
			<?php } ?>
			<?php if (!empty($_SESSION['success'])) { ?>
				<div class="alert success">
				<?php foreach($_SESSION['success'] as $success) { ?>
					<p><?php print($success); ?></p>
				<?php } ?>
				</div>
			<?php } ?>
				<?php print($app -> body ?? null); ?>
			</section>
			<footer>

			</footer>
		</main>
	</body>
</html>
