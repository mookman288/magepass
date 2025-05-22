<?php
	$title = 'Home';
?>
<h1>Home</h1>
<h2>Vaults</h2>
<?php if (!empty($vaults)) { ?>
<?php foreach($vaults as $id => $vault) { ?>
	<div class="card">
		<h3><?php print($vault -> name); ?></h3>
	<?php if (!empty($app -> getVaultKey($vault -> id))) { ?>
		<p>This vault is currently unlocked.</p>
		<a href="<?php $app -> url("vault/{$vault -> id}"); ?>" class="button">
			View Vault
		</a>
	<?php } else { ?>
		<form action="<?php $app -> url("vault/{$vault -> id}"); ?>" method="post">
			<label for="password-<?php print($id); ?>">Password</label>
			<input id="password-<?php print($id); ?>" name="password" type="password" />
			<input type="submit" value="Unlock" />
		</form>
	<?php } ?>
	</div>
<?php } ?>
<?php } else { ?>
<p>You have not created a vault yet.</p>
<?php } ?>
<h2>How-To Guide</h2>
<h3>How does this work?</h3>
<p>
	<?php print($app -> config['app']['name']); ?> allows you to organize and encrypt information, like logins, access
	credentials, and more. Instead of memorizing information like passwords for every account,
	<?php print($app -> config['app']['name']); ?> simplifies this to only a handful of passwords. Don't forget your
	account password, though! <?php print($app -> config['app']['name']); ?> uses your account password to encrypt
	your information. You will lose access to all of the information inside your account if you forget your
	password.
</p>
<h3>What are Vaults?</h3>
<p>
	Vaults are containers that allow you to organize and categorize large sets of data you want to protect. For instance,
	you might have a "personal" vault and a "work" vault. This method of categorization will help you organize your
	information. It's up to you!
</p>
<p>
	Each vault is password protected. Try to use a memorable vault password, because if you lose your vault password,
	you'll lose access to your data inside.
</p>
<h3>What are Archives?</h3>
<p>
	Now that we're inside the vault, archives act as a "safety deposit box" of sorts. Each archive contains a collection
	of encrypted information, also known as "records." You can have any number of records assigned to a single archive.
	If you want to store login access for a website, you might have a "website" record, "username" record, "password"
	record, and if you use two-factor authentication, a record for your 2FA backup codes.
</p>
<h3>Can other people use this with me?</h3>
<p>
	Yes! You can register multiple accounts using the registration invite code at the bottom of the main menu. Registration
	invite codes are extremely time limited, so you need to create a new account immediately after copying the code.
</p>