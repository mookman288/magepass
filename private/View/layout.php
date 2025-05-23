<!DOCTYPE html>
<html data-api="<?php $app -> url(); ?>"<?php if (isset($app -> skipHeartbeat)) { ?> data-skip-heartbeat="true"<?php } ?>>
	<head>
		<title><?php print($app -> title ?? null); ?> | <?php print($app -> config['app']['name']); ?></title>
		<link rel="icon" href="<?php $app -> url('favicon.ico'); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="stylesheet" href="<?php $app -> url('stylesheet.css'); ?>" />
	</head>
	<body>
		<main>
			<aside>
				<header>
					<span class="branding"><?php print($app -> config['app']['name']); ?></span>
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
					case 'VaultEdit':
					case 'Archive':
					case 'ArchiveCreate':
					case 'Record':
					case 'RecordCreate':
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
		<?php foreach($app -> messageKeys as $_messageKey) { ?>
			<?php if (!empty($_SESSION[$_messageKey])) { ?>
				<div class="alert <?php print($_messageKey); ?>">
				<?php foreach($_SESSION[$_messageKey] as $_message) { ?>
					<p><?php print($_message); ?></p>
				<?php } ?>
				</div>
			<?php } ?>
		<?php } ?>
				<?php print($app -> body ?? null); ?>
			</section>
		</main>
	</body>
	<script type="text/javascript" src="<?php $app -> url('script.js'); ?>"></script>
</html>
