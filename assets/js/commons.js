var show_layer = true;
var auto_search_result = {};
var data_table_rendered;
var pp_code = "";
var pp_id = "";
var timer = window.setInterval(function() {
	if (xajax.callback != undefined) {
		window.clearInterval(timer);
		init_xajax_error();
	}
}, 100);

function ucfirst(str) {
	return str.charAt(0).toUpperCase() + str.slice(1);
}

function enable_tool_tips() {
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
	var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
		var tt = new bootstrap.Tooltip(tooltipTriggerEl);
		return tt;
	});
}

function enable_popover() {
	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
	var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
		var tt = new bootstrap.Popover(popoverTriggerEl);
		return tt;
	});
}

function hide_tooltip(id_element) {
	var exampleTriggerEl = document.getElementById(id_element);
	var tooltip = bootstrap.Tooltip.getInstance(exampleTriggerEl);
	tooltip.hide();
}

function init_xajax_error() {
	xajax.callback.global.onFailure = function(args) {
		xajax_error(args);
	}
};

function getCss(div) {
	return $('#' + div).css();
}

function copy_text(field_id, with_popup) {
	// Get the text field
	var copyText = document.getElementById(field_id);
	// Select the text field
	copyText.select();
	copyText.setSelectionRange(0, 99999); // For mobile devices
	// Copy the text inside the text field
	navigator.clipboard.writeText(copyText.value);
	// Alert the copied text
	if (with_popup != false) {
		popup("Copied", "Short Url copied in your clipboard", 50, 3000);
	}
}

function copy_to_clipboard(id_to_copy, caller) {
	copy_text(id_to_copy, false);
	$('#clipboard_copy_' + caller).hide();
	$('#clipboard_copy_done_' + caller).show();
	setTimeout(
		function() {
			$('#clipboard_copy_done_' + caller).hide();
			$('#clipboard_copy_' + caller).show();
		}, 2000
	);
}

window.addEventListener('popstate', function(event) {
	// The popstate event is fired each time when the current history entry
	// changes.
	// Stay on the current page.
	history.pushState(null, null, window.location.pathname);
}, false);

document.addEventListener("DOMContentLoaded", function() {
	$("#navbarNavDropdown .dropdown").hover(function() {
		var dropdownMenu = $(this).children(".dropdown-menu");
		if (dropdownMenu.is(":visible")) {
			dropdownMenu.parent().toggleClass("open");
		};
	});
	// make it as accordion for smaller screens
	if (window.innerWidth < 992) {
		// close all inner dropdowns when parent is closed
		document.querySelectorAll('.navbar .dropdown').forEach(function(everydropdown) {
			everydropdown.addEventListener('hidden.bs.dropdown', function() {
				// after dropdown is hidden, then find all submenus
				this.querySelectorAll('.submenu').forEach(function(everysubmenu) {
					// hide every submenu as well
					everysubmenu.style.display = 'none';
				});
			})
		});

		document.querySelectorAll('.dropdown-menu a').forEach(function(element) {
			element.hover(function(e) {
				let nextEl = this.nextElementSibling;
				if (nextEl && nextEl.classList.contains('submenu')) {
					// prevent opening link if link needs to open dropdown
					e.preventDefault();
					if (nextEl.style.display == 'block') {
						nextEl.style.display = 'none';
					} else {
						nextEl.style.display = 'block';
					}
				}
			});
		})
	}
	// end if innerWidth
});
// DOMContentLoaded end

function waitForElm(selector) {
	return new Promise(resolve => {
		if (document.querySelector(selector)) {
			return resolve(document.querySelector(selector));
		}

		const observer = new MutationObserver(mutations => {
			if (document.querySelector(selector)) {
				resolve(document.querySelector(selector));
				observer.disconnect();
			}
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	});
}

function startTime() {
	var today = new Date();
	var hr = today.getHours();
	var min = today.getMinutes();
	var sec = today.getSeconds();
	ap = (hr < 12) ? "<span>AM</span>" : "<span>PM</span>";
	hr = (hr == 0) ? 12 : hr;
	hr = (hr > 12) ? hr - 12 : hr;
	// Add a zero in front of numbers<10
	hr = checkTime(hr);
	min = checkTime(min);
	sec = checkTime(sec);
	document.getElementById("timer_section").innerHTML = hr + ":" + min + ":" +
		sec + " " + ap;

	var months = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago.',
		'Set', 'Ott', 'Nov', 'Dic'
	];
	var days = ['Domenica', 'Lunedi', 'Martedi', 'Mercoledi', 'Giovedi',
		'Venerdi', 'Sabato'
	];
	var curWeekDay = days[today.getDay()];
	var curDay = today.getDate();
	var curMonth = months[today.getMonth()];
	var curYear = today.getFullYear();
	var date = curWeekDay + ", " + curDay + " " + curMonth + " " + curYear;
	document.getElementById("date_section").innerHTML = date;

	var time = setTimeout(function() {
		startTime()
	}, 1000);
}

function getRndInteger(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

function checkTime(i) {
	if (i < 10) {
		i = "0" + i;
	}
	return i;
}

function bind_on_change(id, lib, func, min_length) {
	if (min_length == undefined) {
		min_length = 4;
	}
	$("#" + id).on("change", function(e) {
		show_layer = false;
		var dati = e.target.value;
		if (check_length(id, min_length)) {
			xajax_execute(lib, func, dati);
		} else {
			show_too_short(id, min_length);
		}
	})
}

function show_too_short(id, min_length) {
	var string = $('#' + id).val();
	var diff = min_length - string.length;
	$('#' + id).val("You need " + diff + " character(s) more...wait please");
	$('#' + id).prop("disabled", true);;
	setTimeout(function() {
		$('#' + id).val(string);
		$('#' + id).prop("disabled", false);
		$('#' + id).focus()
	}, 1500);
}

function check_length(id, min_length) {
	var string = $('#' + id).val();
	if (string.length < min_length) {
		return false;
	} else {
		return true;
	}
}

function render_auto_search(divname, tipo, libreria, width, min_length) {
	if (width != undefined) {
		var larg = width;
	} else {
		var larg = null;
	}
	var $jquery_element = $('#' + divname);
	if (min_length == undefined) {
		var min_length = 3;
	}
	var pu = localStorage.getItem('CUP');
	$jquery_element
		.select2({
			width: larg,
			enabled: true,
			delay: 250,
			minimumInputLength: min_length,
			data: auto_search_result,
			placeholder: "Search through your saved URLs...",
			// allowClear : true,
			language: 'en',
			formatNoMatches: function(term) {
				return "No results found";
			},
			formatSearching: function() {
				return "Searching...";
			},
			initSelection: function(element, callback) {
				callback(auto_search_result);
			},
			formatInputTooShort: function(input, min) {
				return "At least " + (min - input.length) +
					" character more";
			},
			ajax: {
				url: "/ajax_requests/ajax_" + tipo + "/auto_search",
				dataType: 'json',
				type: "POST",
				quietMillis: 100,
				data: function(params) {
					var query = {
						term: params.term,
						CUP: pu
					}
					return query;
				},
			}
		});
	$jquery_element.on('select2:select', function(e) {
		show_layer = false;
		var dati = e.params.data;
		xajax_execute(libreria, 'autosearch', dati);
	})
}

function crea_pp() {
	paypal.Buttons({
		style: {
			shape: 'rect',
			color: 'gold',
			layout: 'vertical',
			label: 'paypal'
		},
		createSubscription: function(data, actions) {
			return actions.subscription.create({
				plan_id: pp_id,
				custom_id: pp_code
			});
		},
		onApprove: function(data, actions) {
			return actions.subscription.get().then(
				function(orderData) {
					xajax_execute('Payments/Main_paypal', 'paypal_acquistato', orderData)
				}
			)
		},
		onCancel: function(data) {
			xajax_execute('Payments/Main_paypal', 'paypal_annullato', data)
		},
	}).render('#paypal-button');
	// $('#paypal-button-container').hide();
}

function load_content(url) {
	var iframe = document.getElementById("full_stat_content");
	iframe.src = url;
}

function redraw_data_table(id_tabella) {
	if (data_table_rendered != undefined){
		data_table_rendered.draw();
	}
}

function do_data_table(id_tabella, sorting, section, data_type, total) {
	if (sorting == undefined) {
		sort_order = [
			[1, "desc"]
		];
	} else {
		sort_order = sorting;
	}
	if (data_type == undefined) {
		dataTypes = [];
	} else {
		dataTypes = data_type;
	}
	if (total == undefined) {
		total = 10;
	}
	var ls_entita = localStorage.getItem('CUP');
	if (section.includes("_")) {
		myArray = section.split("_");
		my_function = myArray.pop();
		my_ajax_section = myArray.join("_");
	} else {
		my_ajax_section = section;
		my_function = section;
	};
	data_table_rendered = new DataTable("#" + id_tabella, {
		bJQueryUI: false,
		sPaginationType: "full_numbers",
		aoColumnDefs: dataTypes,
		iTotalRecords: total,
		iTotalDisplayRecords: total,
		// sScrollY: "280px",
		scrollX: true,
		bPaginate: true,
		// bScrollCollapse: true,
		bProcessing: true,
		bDeferRender: true,
		aaSorting: sort_order,
		// iDeferLoading : total,
		bServerSide: true,
		sServerMethod: "POST",
		ajax: {
			url: "/ajax/" + ucfirst(my_ajax_section) + "/ajax_" + my_ajax_section + "/get_" + my_function +
				"_tabella",
			dataType: 'json',
			type: "POST",
			data: function(d) {
				d.filtro_data = $('#filtro_data').val();
				d.filtro_tipo = $('#drop_down_ID_Tipo_Record_lista')
					.val();
				d.filtro_cliente = $('#drop_down_ID_Cliente_lista')
					.val();
				d.CUP = ls_entita;
			},
		},
	});
}

function validateFormAndExecute(formId, functionToExecute) {
    // Seleziona il form tramite l'id
    var form = document.getElementById( formId );
    
    // Verifica se il form è valido
    if (form.checkValidity()) {
        // Se il form è valido, chiama la funzione xajax_execute
    	functionToExecute();
    } else {
        // Se il form non è valido, mostra i messaggi di errore del browser
        form.reportValidity();
    }
}