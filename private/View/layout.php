<!DOCTYPE html>
<html>
	<head>
		<title><?php print($app -> title ?? null); ?> | MagePass</title>
		<link rel="icon" href="<?php $app -> url('favicon.ico'); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="stylesheet" href="<?php $app -> url('stylesheet.css'); ?>" />
	</head>
	<body>
		<main>
			<aside>
				<header>
					<span class="branding">MagePass</span>
				</header>
				<nav>
					<ul>
			<?php
				switch($app -> routeName) {
					case 'Login':
			?>
						<li><a href="<?php $app -> url('register'); ?>">Register Account</a></li>
			<?php
					break;
					case 'Register':
			?>
						<li><a href="<?php $app -> url('login'); ?>">Login</a></li>
			<?php
					break;
					case 'Home':
					case 'Vault':
					case 'VaultCreate':
					case 'Archive':
					case 'ArchiveCreate':
			?>
						<li><a href="<?php $app -> url('home'); ?>">Home &ndash; Vaults</a></li>
						<li><a href="<?php $app -> url('vault/create'); ?>">Create Vault</a></li>
						<li><a href="<?php $app -> url('logout'); ?>">Logout</a></li>
						<li style="text-align: center;">
							<hr />
							<label for="inviteCode">Registration Invite Code</label>
							<input id="inviteCode" type="text" disabled readonly style="width: 100%;"
								value="<?php print($app -> generateInviteCode($app -> config['app']['salt'])); ?>" />
						</li>
			<?php
					break;
				}
				?>
					</ul>
				</nav>
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
		</main>
	</body>
</html>
