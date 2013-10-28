function formatCountdown(number) {
	if (number <= 60) {
		if (number < 10) {
			return '0'+number;
		}
		else {
			return number;
		}
	}
}
$(document).ready(function() {
	$(document).keydown(function(e) {
		if (e.which == 37) { // arrow left
			var buttonToClick = $('div.wrapper.visible div.pagination .previous').first();
			if (typeof(buttonToClick) !== 'undefined') {
				buttonToClick.trigger('click');
				e.preventDefault();
			}
		}
		else if (e.which == 39) { // arrow right
			var buttonToClick = $('div.wrapper.visible div.pagination .next').first();
			if (typeof(buttonToClick) !== 'undefined') {
				buttonToClick.trigger('click');
				e.preventDefault();
			}
		}
		else { // exit this handler in case of other key pressed
			return;
		}
	});
});
function doSwitchViews(currentEntry, newEntry) {
	currentEntry.removeClass('visible');
	currentEntry.addClass('invisible');
	newEntry.removeClass('invisible');
	newEntry.addClass('visible');
};
function showEntry(currentEntry, direction) {
	if (typeof(currentEntry) !== 'undefined' && typeof(direction) !== 'undefined') {
		direction = parseInt(direction);
		var currentEntry = $('#'+currentEntry);
		var newEntry;
		if (direction == 1) {
			newEntry = currentEntry.next();
		}
		else if (direction == -1) {
			newEntry = currentEntry.prev();
		}
		else {
			return;
		}
		if (typeof(currentEntry) !== 'undefined' && typeof(newEntry) !== 'undefined') {
			if ($('html, body').scrollTop() > 0) {
				$('html, body').animate({ scrollTop: 0 }, 400);
				setTimeout(function() {
					doSwitchViews(currentEntry, newEntry);
				}, 400);
			}
			else {
				doSwitchViews(currentEntry, newEntry);
			}
		}
	}
}
function toggleDisplay(element) {
	if (typeof(element) !== 'undefined') {
		if (element.style.display != "block") {
			element.style.display = "block";
		}
		else {
			element.style.display = "none";
		}
	}
}
function toggleExpander(element) {
	var toToggle = document.getElementById(element.parentNode.id.replace('head_', 'body_'));
	if (toToggle.style.display != 'block') {
		toToggle.style.display = 'block';
	}
	else {
		toToggle.style.display = 'none';
	}
}
function inputHintHide(element) {
	if (element.value == element.defaultValue) {
		element.value = '';
		element.className = '';
	}
}
function inputHintShow(element) {
	if (element.value == '') {
		element.value = element.defaultValue;
		element.className = 'inputHint';
	}
}