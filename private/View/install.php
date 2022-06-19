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
	<label for="databaseHost">Database Host</label>
	<input id="databaseHost" name="databaseHost" type="text" size="40" value="<?php print($databaseHost ?? null); ?>"
		placeholder="localhost" />
	<label for="databasePort">Database Port</label>
	<input id="databasePort" name="databasePort" type="text" size="40" value="<?php print($databasePort ?? null); ?>"
		placeholder="3306" />
	<label for="databaseName">Database Name</label>
	<input id="databaseName" name="databaseName" type="text" size="40" value="<?php print($databaseName ?? null); ?>" />
	<label for="databaseUser">Database Username</label>
	<input id="databaseUser" name="databaseUser" type="text" size="40" value="<?php print($databaseUser ?? null); ?>" />
	<label for="databasePass">Database Password</label>
	<input id="databasePass" name="databasePass" type="text" size="40" value="<?php print($databasePass ?? null); ?>" />
	<fieldset>
		<legend>Enable PIN System (this requires each user to set a PIN designed to discourage keylogging, but may disable autofill)</legend>
		<label for="enablePinYes">
			Yes
			<input id="enablePinYes" name="enablePin" type="radio" value="true"<?php if (!empty($enablePin)) { ?> checked="checked"<?php } ?> />
		</label>
		<label for="enablePinNo">
			No
			<input id="enablePinNo" name="enablePin" type="radio" value="false"<?php if (empty($enablePin)) { ?> checked="checked"<?php } ?> />
		</label>
	</fieldset>
	<label for="hcaptchaSiteKey">HCaptcha Site Key (leave this blank to disable CAPTCHA)</label>
	<input id="hcaptchaSiteKey" name="hcaptchaSiteKey" type="text" size="40" value="<?php print($hcaptchaSiteKey ?? null); ?>" />
	<label for="hcaptchaSecretKey">HCaptcha Secret Key (leave this blank to disable CAPTCHA)</label>
	<input id="hcaptchaSecretKey" name="hcaptchaSecretKey" type="text" size="40" value="<?php print($hcaptchaSecretKey ?? null); ?>" />
	<input id="submit" name="submitted" type="submit" value="Install" />
</form>