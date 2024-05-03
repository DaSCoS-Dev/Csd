var myModal = new bootstrap.Modal(document.getElementById('div_popup'));
autoCloseLayer = true;
show_layer = true;

function change_popup_label(testo) {
	$('#div_popup_label').html(testo);
}

function change_popup_testo(testo) {
	$('#div_popup_content').html(testo);
}

function show_loading_layer() {
	myModal.show();
}

function hide_loading_layer() {
	if (autoCloseLayer) {
		myModal.hide();
		change_popup_label('Wait');
		change_popup_testo('Running, please wait a few...');
		$('#div_popup_header').removeClass('bg-danger bg-gradient text-white');
		show_layer = true;
	}
}

function show_loading_layer_telling(title, text) {
	if (show_layer) {
		change_popup_label(title);
		change_popup_testo(text);
		show_loading_layer();
	}
}

function popup(title, content, open_delay, close_delay) {
	show_layer = true;
	if (parseInt(open_delay) > 0) {
		setTimeout(function() {
			change_popup_label(title);
			change_popup_testo(content);
			show_loading_layer();
		}, open_delay);
	} else {
		return popup(title, content, 10, close_delay);
	}
	if (parseInt(close_delay) > 0) {
		setTimeout(function() {
			hide_loading_layer();
		}, close_delay);
	}
}

function popup_div(title, content, close_delay) {
	popup(title, content, 10, close_delay);
}

function xajax_error(response) {
	autoCloseLayer = true;
	show_layer = true;
	error("Xajax Error " + response.request.status, response.request.response,
			50000);
}

function error(titolo, message, close_delay) {
	autoCloseLayer = true;
	show_layer = true;
	alert(message, titolo, close_delay);
}
// Semplice override del alert standard di javascript
function alert(message, title, close_delay) {
	if (title == undefined || title == "") {
		title = '<i class="bi bi-exclamation-triangle"></i> Error';
	}
	var content = '<div class="alert alert-danger d-flex align-items-center" role="alert">'
			+ '<div>' + message + '</div>' + '</div>';
	show_layer = true;
	change_popup_label(title);
	change_popup_testo(content);
	$('#div_popup_header').addClass('bg-danger bg-gradient text-white');
	show_loading_layer();
	if (parseInt(close_delay) > 0) {
		setTimeout(function() {
			hide_loading_layer();
		}, close_delay);
	}
}

// Semplice override del message informativo standard di javascript
// args e' un oggetto js in stile json: { "message" : "Eliminazione annullata",
// "redirect" : null, "close_delay" : 1500 }
function information(args) {
	var message = args.message;
	var redirect = args.redirect;
	var close_delay = args.close_delay;
	var title = args.title;
	if (title == '' || title == undefined) {
		title = '<i class="bi bi-info-circle"></i> Info';
	}
	var content = '<div class="alert alert-primary d-flex align-items-center" role="alert">'
			+ '<div> ' + message + '</div>' + '</div>';
	change_popup_label(title);
	change_popup_testo(content);
	show_loading_layer();
	if (parseInt(close_delay) > 0) {
		setTimeout(function() {
			hide_loading_layer();
		}, close_delay);
	}
}

function message(args) {
	information(args);
}

function modal_confirm(title, message, call_back_function_ok) {
	if (title == '' || title == undefined) {
		title = '<i class="bi bi-info-circle"></i> Info';
	}
	var content = '<div id="modal_text" class="alert alert-primary d-flex align-items-center" role="alert">'
			+ '<div> ' + message + '</div>' + '</div>';
	change_popup_label(title);
	change_popup_testo(content);
	$("#modal_ok_button").click(	call_back_function_ok );
	show_loading_layer();
}