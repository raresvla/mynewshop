var clientiSelectati = [];
var sorting = {element: 'numele', direction: 'asc'};
var pagina = 1;

function showHide(id, event) {
	switch(event) {
		case 'on': {
			$('row_' + id).style.backgroundColor = '#CCD9F2';
			$('span_' + id).style.display = 'none';
			$('check_' + id).style.display = 'block';
		} break;
		case 'off': {
			if(!($('check_' + id).checked)) {
				$('row_' + id).style.backgroundColor = '';
				$('check_' + id).style.display = 'none';
				$('span_' + id).style.display = 'block';
			}
			else {
				$('row_' + id).style.backgroundColor = '#E7EDF9';
			}
		} break;
	}
}

function selectDeselect(element, id) {
	if(element.checked) {
		window['clientiSelectati'].push(id);
	}
	else {
		window['clientiSelectati'] = window['clientiSelectati'].without(id);
	}
}

function markSelection(row) {
	var pattern = /(\w+)\_(\d+)/;
	var details = pattern.exec(row.identify());
	if(details && !details[1].startsWith('anonymous')) {
		//clicked on a row
		var checkBox = $('check_' + details[2]); 
		if(!checkBox.checked) {
			$('span_' + details[2]).style.display = 'none';
			checkBox.style.display = 'block';
			checkBox.checked = true;
			clientiSelectati.push(details[2]);
		}
		else {
			$('span_' + details[2]).style.display = 'block';
			checkBox.style.display = 'none';
			checkBox.checked = false;
			clientiSelectati = clientiSelectati.without(details[2]);
		}
	}
}

function getClienti(pagina, sorting) {
	if(!Object.isUndefined(pagina)) {
		window.pagina = pagina;
	}
	if(!Object.isUndefined(sorting)) {
		window.sorting = sorting;
	}
	var myAjax = new Ajax.Request('ajax.php?sectiune=clienti&actiune=getClienti', {
		method: 'post',
		parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting),
		onSuccess: function(transport) {
			var response = transport.responseText.evalJSON();
			$('tableHeader').update(response.header);
			$('tableContent').update(response.table);
			$('listingContainer').update(response.listing);
			clientiSelectati.clear();
		}
	});
}

function activeazaClienti () {
	if(clientiSelectati.length) {
		var myAjax = new Ajax.Request('ajax.php?sectiune=clienti&actiune=activeazaClienti', {
			method: 'post',
			parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting) + '&clienti=' + Object.toJSON(clientiSelectati),
			onSuccess: function(transport) {
				var response = transport.responseText.evalJSON();
				$('tableContent').update(response.table);
				$('listingContainer').update(response.listing);
				clientiSelectati.clear();
			}
		});
	}
}

function suspendaClienti () {
	if(clientiSelectati.length && window.confirm('Confirmati suspendarea clientilor selectati ?')) {
		var myAjax = new Ajax.Request('ajax.php?sectiune=clienti&actiune=suspendaClienti', {
			method: 'post',
			parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting) + '&clienti=' + Object.toJSON(clientiSelectati),
			onSuccess: function(transport) {
				var response = transport.responseText.evalJSON();
				$('tableContent').update(response.table);
				$('listingContainer').update(response.listing);
				clientiSelectati.clear();
			}
		});
	}
}

function stergeClienti () {
	if(clientiSelectati.length && window.confirm('Confirmati stergerea clientilor selectati ?')) {
		var myAjax = new Ajax.Request('ajax.php?sectiune=clienti&actiune=stergeClienti', {
			method: 'post',
			parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting) + '&clienti=' + Object.toJSON(clientiSelectati),
			onSuccess: function(transport) {
				var response = transport.responseText.evalJSON();
				$('tableContent').update(response.table);
				$('listingContainer').update(response.listing);
				clientiSelectati.clear();
			}
		});
	}
}

Event.observe(window, 'load', function() {
	getClienti();
	Event.observe('tableContent', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection(elementClicked.up());
	});
});