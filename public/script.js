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
			div.className = "addRecordField";

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
			inputContent.setAttribute('rows', 4);
			inputContent.setAttribute('cols', 42);

			div.appendChild(inputContent);

			parent.parentNode.insertBefore(div, parent);
		}, false);
	}
}();

var archives = document.getElementsByClassName('archive');

if (archives.length > 0) {
	for (i = 0; i < archives.length; i++) {
		var archive = archives[i];
		var endpoint = archive.dataset.endpoint;
		var header = archive.getElementsByTagName('h3')[0];

		header.addEventListener('click', function(e) {
			e.preventDefault();

			var request = new XMLHttpRequest();

			request.onreadystatechange = function() {
				if (request.readyState === XMLHttpRequest.DONE) {
					if (request.status !== 200) {
						if (confirm("There was an error communicating with the application. Refresh the page to try again?")) {
							location.reload(true);
						}
					} else {
						response = JSON.parse(request.responseText);

						if (!response.data) {
							if (confirm("There was an error retrieving the archive data. Refresh the page to try again?")) {
								location.reload(true);
							}
						} else {
							document.getElementById('archive').remove();

							var div = document.createElement('div');
							div.id = "archive";
						}

					}
				}
			};

			request.open('GET', endpoint);
			request.send();
		});
	}
}

var forms = document.getElementsByTagName('form');

if (forms.length > 0) {
	for (i = 0; i < forms.length; i++) {
		var form = forms[i];

		form.addEventListener('submit', function(e) {
			console.log(form.querySelectorAll('input[type=submit]'), form.querySelectorAll('input[type=submit]')[0]);
			e.preventDefault();
			return false;
			var button = form.querySelectorAll('input[type=submit]')[0];
			var loading = document.createElement('span');

			button.setAttribute('disabled', 'disabled');

			loading.classList.add('loading');

			form.appendChild(loading);

			return true;
		});
	}
}
