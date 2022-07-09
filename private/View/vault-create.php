<?php
	$title = 'Create Vault';
?>
<h1>Create Vault</h1>
<form action="<?php $app -> url('vault/create'); ?>" method="post">
	<label for="name">Name</label>
	<input id="name" name="name" type="text" size="40" value="<?php print($name ?? null); ?>" />
	<label for="password">Password</label>
	<input id="password" name="password" type="password" size="40" />
	<label for="confirm">Confirm Password</label>
	<input id="confirm" name="confirm" type="password" size="40" />
	<input id="submit" name="submitted" type="submit" value="Create" />
</form>