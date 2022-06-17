const { ipcRenderer } = require('electron');
const fs = require('fs');
const path = require('path');

const config = require(path.join(__dirname, 'config.json'));

const html = function(file, type = 'div') {
	const html = fs.readFileSync(path.join(__dirname, 'templates', file + '.html'));

	let element = document.createElement(type);

	element.innerHTML = html;

	return element;
}

function loadPage(anchor, element) {
	let $anchor = document.querySelector(anchor);
	let $element = document.querySelector(element);
}

document.addEventListener('DOMContentLoaded', function() {
	let head = document.querySelector('head');

	head.querySelector('title').innerHTML = config.name + ' ' + config.subtitle;

	let body = document.querySelector('body');

	let layout = html('layout');
	let sidebar = html('sidebar');
	let main = html('login');

	layout.querySelector('#sidebar').insertBefore(sidebar, layout.querySelector('#sidebar').firstChild);
	layout.querySelector('#main').insertBefore(main, layout.querySelector('#main').firstChild);

	layout.querySelector('.name').innerHTML = config.name;
	layout.querySelector('.subtitle').innerHTML = config.subtitle;

	body.insertBefore(layout, body.firstChild);
});

ipcRenderer.send('requestForInitialize');

ipcRenderer.on('initialize', function(event, response) {

});

