<?php
	$title = $vault -> name ?? 'Vault Not Found';

	if (empty($vault) || !$vaultKey) {
?>
	<p>The vault could not be found. <a href="<?php $this -> url('home'); ?>">Click here</a> to try again.</p>
<?php
	} else {
?>
	<h1><?php print($vault -> name); ?></h1>
	<h2>Edit Vault</h2>
	<form action="<?php $app -> url("vault/{$vault -> id}/edit"); ?>" method="post">
		<label for="nameEdit<?php print($vault-> id); ?>">Name</label>
		<input id="nameEdit<?php print($vault-> id); ?>" name="name" type="text" size="40" value="<?php print($vault -> name ?? null); ?>" />
<!--
		<label for="passwordEdit<?php print($vault-> id); ?>">Password</label>
		<input id="passwordEdit<?php print($vault-> id); ?>" name="password" type="password" size="40" />
		<label for="confirmEdit<?php print($vault-> id); ?>">Confirm Password</label>
		<input id="confirmEdit<?php print($vault-> id); ?>" name="confirm" type="password" size="40" />
-->
		<input id="submitEdit<?php print($vault-> id); ?>" name="submitted" type="submit" value="Edit"
			onclick="return window.confirm('Are you sure you want to edit this vault?');" />
	</form>
	<hr />
	<h2>Archives</h2>
<?php
		if (!empty($archives)) {
			foreach($archives as $id => $archive) {
?>
	<div class="card full archive" data-endpoint="<?php $app -> url("vault/{$vault -> id}/archive/{$archive -> id}"); ?>">
		<h3 class="link"><?php print($archive -> name); ?></h3>
		<form action="<?php $app -> url("vault/{$vault -> id}/archive/{$archive -> id}"); ?>" method="post">
			<label for="nameArchive<?php print($archive -> id); ?>">Name</label>
			<input id="nameArchive<?php print($archive -> id); ?>" name="name" type="text" size="40" value="<?php print($archive -> name); ?>" />
			<input type="submit" value="Edit Archive Settings" />
		</form>
		<noscript>Please enable JavaScript to interact with archives further.</noscript>
		<hr />
<?php
				if (!empty($archive -> content)) {
					foreach($archive -> content as $id => $record) {
?>
		<form action="<?php
			$app -> url("vault/{$vault -> id}/archive/{$archive -> id}/record/{$record -> id}");
		?>" method="post">
			<label for="nameRecord<?php print($record -> id); ?>">Name</label>
			<input id="nameRecord<?php print($record -> id); ?>" name="name" type="text" size="40"
				value="<?php print($record -> name); ?>" />
			<label for="content<?php print($record -> id); ?>">Content</label>
			<textarea id="content<?php print($record -> id); ?>" name="content" cols="42" rows="4"><?php
				print($record -> content);
			?></textarea>
			<label for="delete<?php print($record -> id); ?>">
				<input id="delete<?php print($record -> id); ?>" type="checkbox" name="delete" value="true" />
				Delete this record permanently
			</label>
			<input type="submit" value="Save Record" />
		</form>
		<hr />
<?php
					}
				}
?>
		<form action="<?php $app -> url("vault/{$vault -> id}/archive/{$archive -> id}/record"); ?>" method="post">
			<div>
				<button class="addRecord" type="button">
					+ Add Record to Archive
				</button>
				<input type="submit" value="Save New Records" />
			</div>
		</form>
	</div>
<?php
			}
		} else {
?>
	<p>There are no archives. Would you like to create one?</p>
<?php 	} ?>
	<h2>Create Archive</h2>
	<form action="<?php $app -> url("vault/{$vault -> id}/archive"); ?>" method="post">
		<label for="nameCreate<?php print($archive -> id); ?>">Name</label>
		<input id="nameCreate<?php print($archive -> id); ?>" name="name" type="text" size="40" />
		<input id="submitCreate<?php print($archive -> id); ?>" name="submitted" type="submit" value="Create" />
	</form>
<?php } ?>