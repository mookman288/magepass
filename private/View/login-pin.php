<?php
	$title = 'Enter PIN';
?>
<h1>Enter PIN</h1>
<form action="login-pin" method="post">
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
	<input id="submit" name="submitted" type="submit" value="Login" />
</form>
