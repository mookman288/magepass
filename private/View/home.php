<?php
	$title = 'Home';
?>
<h1>Home</h1>
<h2>Vaults</h2>
<?php if (!empty($vaults)) { ?>
<?php foreach($vaults as $id => $vault) { ?>
	<div class="card">
		<h3><?php print($vault -> name); ?></h3>
		<form action="<?php $app -> url("vault/unlock/{$vault -> id}"); ?>" method="post">
			<label for="password-<?php print($id); ?>">Password</label>
			<input id="password-<?php print($id); ?>" name="password" type="password" />
			<input type="submit" value="Unlock" />
		</form>
	</div>
<?php } ?>
<?php } else { ?>
<p>You have not created a vault yet.</p>
<?php } ?>