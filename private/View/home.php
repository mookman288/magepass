<?php
	$title = 'Home';
?>
<h1>Home</h1>
<h2>Vaults</h2>
<?php if (!empty($vaults)) { ?>

<?php } else { ?>
<p>You have not created a vault yet.</p>
<?php } ?>