<?php
	$title = 'Register Account';
?>
<h1>Register</h1>
<form action="register" method="post">
<?php if ($app -> db -> query('SELECT COUNT(*) FROM user') -> fetchColumn()) { ?>
	<label for="inviteCode">Invite Code</label>
	<input id="inviteCode" name="inviteCode" type="text" size="40" value="<?php print($inviteCode ?? null); ?>" />
<?php } ?>
	<label for="username">Username</label>
	<input id="username" name="username" type="text" size="40" value="<?php print($username ?? null); ?>" />
	<label for="email">Email</label>
	<input id="email" name="email" type="email" size="40" value="<?php print($email ?? null); ?>" />
	<label for="password">Password</label>
	<input id="password" name="password" type="password" size="40" />
	<label for="confirm">Confirm Password</label>
	<input id="confirm" name="confirm" type="password" size="40" />
<?php if (!empty($app -> config['captcha']['hcaptchaSiteKey'])) { ?>
	<div class="h-captcha" data-sitekey="<?php print($app -> config['captcha']['hcaptchaSiteKey']); ?>"></div>
	<script src='https://js.hcaptcha.com/1/api.js' async defer></script>
<?php } ?>
	<input id="submit" name="submitted" type="submit" value="Register" />
</form>