<?php
	$title = 'Login';
?>
<h1>Login</h1>
<form action="login" method="post">
	<label for="username">Username</label>
	<input id="username" name="username" type="text" size="40" value="<?php print($username ?? null); ?>" />
	<label for="password">Password</label>
	<input id="password" name="password" type="password" size="40" value="<?php print($password ?? null); ?>" />
<?php if (!empty($app -> config['app']['enablePin'])) { ?>
	<label for="pin">Enter PIN</label>
	<input id="pin" name="pin" type="text" size="8" readonly disabled />
	<div class="numpad" data-target="#pin">
	<?php for($i = 1; $i <= 9; $i++) { ?>
		<button type="button" value="<?php print($i); ?>"><?php print($i); ?></button>
		<?php if ($i % 3 === 0) { ?><br /><?php } ?>
	<?php } ?>
		<button type="button" value="0">0</button>
	</div>
<?php } ?>
<?php if (!empty($app -> config['captcha']['hcaptchaSiteKey'])) { ?>
	<div class="h-captcha" data-sitekey="<?php print($app -> config['captcha']['hcaptchaSiteKey']); ?>"></div>
	<script src='https://js.hcaptcha.com/1/api.js' async defer></script>
<?php } ?>
	<input id="submit" name="submitted" type="submit" value="Login" />
</form>