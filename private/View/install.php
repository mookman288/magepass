<?php
	$title = 'Install &amp; Setup';
?>
<h1>Installation</h1>
<h2>Requirements</h2>
<ul>
	<li>PHP 7.3+</li>
	<li>Apache or Nginx<sup>1</sup></li>
	<li>MySQL or MariaDB</li>
</ul>
<p>
	<sup>1</sup>
	If you're using Nginx, please implement the following rules generated from
	<a href="https://winginx.com/en/htaccess" target="_blank" rel="noopener">Winginx</a>:
</p>
<pre><code>autoindex off;

location / {
	rewrite ^(.*)$ https://$http_host$request_uri redirect;
	if ($http_host ~* "^www\.(.*)$") {
		set $http_host_1 $1;
		rewrite ^(.*)$ https://$http_host_1/$1 redirect;
	}
	if (!-e $request_filename) {
		rewrite ^(.+)$ /index.php break;
	}
}</code></pre>
<h2>Setup</h2>
<form action="" method="post">
	<label for="appName">Application Name</label>
	<input id="appName" name="appName" type="text" size="40" value="<?php print($appName ?? null); ?>"
		placeholder="MageLock" />
	<label for="databaseHost">Database Host</label>
	<input id="databaseHost" name="databaseHost" type="text" size="40" value="<?php print($databaseHost ?? null); ?>"
		placeholder="localhost" />
	<label for="databasePort">Database Port</label>
	<input id="databasePort" name="databasePort" type="text" size="40" value="<?php print($databasePort ?? null); ?>"
		placeholder="3306" />
	<label for="databaseName">Database Name</label>
	<input id="databaseName" name="databaseName" type="text" size="40"
		value="<?php print($databaseName ?? $databaseNameExample); ?>" />
	<label for="databaseUser">Database Username</label>
	<input id="databaseUser" name="databaseUser" type="text" size="40" value="<?php print($databaseUser ?? null); ?>" />
	<label for="databasePass">Database Password</label>
	<input id="databasePass" name="databasePass" type="text" size="40" value="<?php print($databasePass ?? null); ?>" />
	<label for="sessionLength">Session Length</label>
	<select id="sessionLength" name="sessionLength">
		<option value="0">Server Default Setting</option>
		<option value="5"<?php if (($sessionLength ?? null) == 5) { ?> selected="selected"<?php } ?>>
			5 Minutes
		</option>
		<option value="15"<?php if (($sessionLength ?? null) == 15) { ?> selected="selected"<?php } ?>>
			15 Minutes
		</option>
		<option value="30"<?php if (($sessionLength ?? null) == 30) { ?> selected="selected"<?php } ?>>
			30 Minutes
		</option>
		<option value="45"<?php if (($sessionLength ?? null) == 45) { ?> selected="selected"<?php } ?>>
			45 Minutes
		</option>
	<?php for ($i = 1; $i <= 8; $i++) { ?>
		<option value="<?php print($i * 60); ?>"<?php if (($sessionLength ?? null) == ($i * 60)) { ?> selected="selected"<?php } ?>>
			<?php print($i); ?> Hours
		</option>
	<?php } ?>
	</select>
	<label for="hcaptchaSiteKey">HCaptcha Site Key (leave this blank to disable CAPTCHA)</label>
	<input id="hcaptchaSiteKey" name="hcaptchaSiteKey" type="text" size="40" value="<?php print($hcaptchaSiteKey ?? null); ?>" />
	<label for="hcaptchaSecretKey">HCaptcha Secret Key (leave this blank to disable CAPTCHA)</label>
	<input id="hcaptchaSecretKey" name="hcaptchaSecretKey" type="text" size="40" value="<?php print($hcaptchaSecretKey ?? null); ?>" />
	<input id="submit" name="submitted" type="submit" value="Install" />
</form>