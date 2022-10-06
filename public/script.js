var addRecordButton = document.getElementById('addRecord');

addRecordButton.addEventListener('click', function(e) {
	e.preventDefault();

	var index = parseInt(document.getElementsByClassName('addRecord').length);

	if (isNaN(index) || index < 1) {
		index = 0;
	}

	var div = document.createElement('div');
	div.className = "addRecord";

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

	addRecordButton.parentNode.insertBefore(div, addRecordButton);
}, false);

var addRecordTemplk