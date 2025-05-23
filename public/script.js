var endpoint = document.getElementsByTagName('html')[0].dataset.api.trim('/');

var triggerAddRecordButtonListener = function() {
	var addRecordButtons = document.getElementsByClassName('addRecord');

	for(var i = 0; i < addRecordButtons.length; i++) {
		var addRecordButton = addRecordButtons[i];

		addRecordButton.addEventListener('click', function(e) {
			e.preventDefault();

			var parent = this.parentNode;
			var index = parseInt(parent.parentNode.getElementsByClassName('addRecordField').length);

			if (isNaN(index) || index < 1) {
				index = 0;
			}

			var div = document.createElement('div');
			div.className = "recordField addRecordField";

			var labelName = document.createElement('label');
			var labelNameContent = document.createTextNode('Record Name #' + (index + 1));
			labelName.setAttribute('for', 'addRecordName' + index);
			labelName.appendChild(labelNameContent);

			div.appendChild(labelName);

			var inputName = document.createElement('input');
			inputName.setAttribute('id', 'addRecordName' + index)
			inputName.setAttribute('name', 'addRecordName[' + index + ']');
			inputName.setAttribute('type', 'text');
			inputName.setAttribute('size', 40);

			div.appendChild(inputName);

			var labelContent = document.createElement('label');
			var labelContentContent = document.createTextNode('Record Content #' + (index + 1));
			labelContent.setAttribute('for', 'addRecordContent' + index);
			labelContent.appendChild(labelContentContent);

			div.appendChild(labelContent);

			var inputContent = document.createElement('textarea');
			inputContent.setAttribute('id', 'addRecordContent' + index)
			inputContent.setAttribute('name', 'addRecordContent[' + index + ']');
			inputContent.setAttribute('rows', 5);
			inputContent.setAttribute('cols', 42);

			div.appendChild(inputContent);

			parent.parentNode.insertBefore(div, parent);
		}, false);
	}
}();

var forms = document.getElementsByTagName('form');

if (forms.length > 0) {
	for (i = 0; i < forms.length; i++) {
		var form = forms[i];

		form.addEventListener('submit', function(e) {
			var button = this.querySelectorAll('input[type=submit]')[0];
			var loading = document.createElement('span');

			button.setAttribute('disabled', 'disabled');

			loading.classList.add('loading');

			this.appendChild(loading);

			return true;
		});
	}
}

if (!document.getElementsByTagName('html')[0].dataset.skipHeartbeat) {
	var heartbeat = setInterval(function() {
		var request = new XMLHttpRequest();
		var refresh = false;

		request.onreadystatechange = function() {
			if (request.readyState === XMLHttpRequest.DONE) {
				response = JSON.parse(request.responseText);

				if (request.status !== 200 || !response.status || response.status != 'OK') {
					refresh = true;
				}

				if (refresh) {
					clearInterval(heartbeat);

					if (!response.data || !response.data.message) {
						var message = "Your session has expired. Please log back in to continue.";
					} else {
						var message = response.data.message;
					}

					if (!response.data || !response.data.redirect) {
						var redirect = location.href;
					} else {
						var redirect = response.data.redirect;
					}

					if (confirm(message)) {
						location.href = redirect;
					}
				}
			}
		};

		request.open('GET', endpoint + 'ping');
		request.send();
	}, 10000);
}