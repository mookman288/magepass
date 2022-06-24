
	<fieldset>
		<legend>Enable PIN System (this requires each user to set a PIN designed to discourage keylogging, but may disable autofill)</legend>
		<label for="enablePinYes" class="radio">
			Yes
			<input id="enablePinYes" name="enablePin" type="radio" value="true"<?php if (!empty($enablePin)) { ?> checked="checked"<?php } ?> />
		</label>
		<label for="enablePinNo" class="radio">
			No
			<input id="enablePinNo" name="enablePin" type="radio" value="false"<?php if (empty($enablePin)) { ?> checked="checked"<?php } ?> />
		</label>
	</fieldset>