var comenziSelectate = [];
var sorting = {element: 'data', direction: 'desc'};
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
		window['comenziSelectate'].push(id);
	}
	else {
		window['comenziSelectate'] = window['comenziSelectate'].without(id);
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
			comenziSelectate.push(details[2]);
		}
		else {
			$('span_' + details[2]).style.display = 'block';
			checkBox.style.display = 'none';
			checkBox.checked = false;
			comenziSelectate = comenziSelectate.without(details[2]);
		}
	}
}

function getComenzi(pagina, sorting) {
	if(!Object.isUndefined(pagina)) {
		window.pagina = pagina;
	}
	if(!Object.isUndefined(sorting)) {
		window.sorting = sorting;
	}
	var myAjax = new Ajax.Request('ajax.php?sectiune=comenzi&actiune=getComenzi', {
		method: 'post',
		parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting),
		onSuccess: function(transport) {
			var response = transport.responseText.evalJSON();
			$('tableHeader').update(response.header);
			$('tableContent').update(response.table);
			$('listingContainer').update(response.listing);
			comenziSelectate.clear();
		}
	});
}

function stergeComenzi () {
	if(comenziSelectate.length && window.confirm('Confirmaţi ştergerea comenzilor selectate ?')) {
		var myAjax = new Ajax.Request('ajax.php?sectiune=comenzi&actiune=stergeComenzi', {
			method: 'post',
			parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting) + '&comenzi=' + Object.toJSON(comenziSelectate),
			onSuccess: function(transport) {
				var response = transport.responseText.evalJSON();
				$('tableContent').update(response.table);
				$('listingContainer').update(response.listing);
				comenziSelectate.clear();
			}
		});
	}
}

function statusPlata () {
	if(!comenziSelectate.length) {
		alert('Selectaţi mai întâi comanda pe care doriţi să o modificaţi!');
		return;
	}
	if(comenziSelectate.length > 1) {
		alert('Nu pot fi editate mai multe comenzi simultan!');
		return;	
	}
	
	var myHandler = function() {
		var myAjax = new Ajax.Request('ajax.php?sectiune=comenzi&actiune=statusPlata', {
			method: 'post',
			parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting) + '&comandaId=' + comenziSelectate[0] + '&status=' + $F('statusPlata'),
			onSuccess: function(transport) {
				var response = transport.responseText.evalJSON();
				$('tableContent').update(response.table);
				comenziSelectate.clear();
			}
		});
		this.win.close();
	};
	
	var myWin = new windowManager(315, 150, $('_winStatusPlata'), {actionButton: {id: 'statusPlata_continua', handler: myHandler}, closeButton: {id: 'statusPlata_renunt'}}, {icon: {src: 'images/icons/status_plata.png', height: '16', width: '16'}, text: 'Status plată'}, 'statusPlata');
	
	//select options
	var pattern = /\_([a-zA-Z]+)\.png/;
	var row = $('row_' + comenziSelectate[0]).childElements();
	$('statusPlata_comandaId').innerHTML = '<strong>' + row[2].innerHTML + '</strong>, ' + row[1].innerHTML;
	$('statusPlata_cumparator').innerHTML = '<strong>' + row[3].innerHTML + '</strong>';
	var optionSelected = pattern.exec(row[7].descendants()[0].src)[1];
	var selectBox = $('statusPlata');
	for(var i=0; i<selectBox.options.length; i++) {
		if(selectBox.options[i].value == optionSelected) {
			selectBox.selectedIndex = i;
			continue;
		}
	}
}

function statusComanda () {
	if(!comenziSelectate.length) {
		alert('Selectaţi mai întâi comanda pe care doriţi să o modificaţi!');
		return;
	}
	if(comenziSelectate.length > 1) {
		alert('Nu pot fi editate mai multe comenzi simultan!');
		return;	
	}
	
	var myHandler = function() {
		var myAjax = new Ajax.Request('ajax.php?sectiune=comenzi&actiune=statusComanda', {
			method: 'post',
			parameters: 'pagina=' + window.pagina + '&sorting=' + Object.toJSON(window.sorting) + '&comandaId=' + comenziSelectate[0] + '&status=' + $F('statusComanda'),
			onSuccess: function(transport) {
				var response = transport.responseText.evalJSON();
				$('tableContent').update(response.table);
				comenziSelectate.clear();
			}
		});
		this.win.close();
	};
	
	var myWin = new windowManager(315, 150, $('_winStatusComanda'), {actionButton: {id: 'statusComanda_continua', handler: myHandler}, closeButton: {id: 'statusComanda_renunt'}}, {icon: {src: 'images/icons/status_comanda.png', height: '16', width: '16'}, text: 'Status comandă'}, 'statusComanda');
	
	//select options
	var pattern = /\_([a-zA-Z]+)\.png/;
	var row = $('row_' + comenziSelectate[0]).childElements();
	$('statusComanda_comandaId').innerHTML = '<strong>' + row[2].innerHTML + '</strong>, ' + row[1].innerHTML;
	$('statusComanda_cumparator').innerHTML = '<strong>' + row[3].innerHTML + '</strong>';
	var optionSelected = pattern.exec(row[8].descendants()[0].src)[1];
	var selectBox = $('statusComanda');
	for(var i=0; i<selectBox.options.length; i++) {
		if(selectBox.options[i].value == optionSelected) {
			selectBox.selectedIndex = i;
			continue;
		}
	}
}

function detaliiComanda () {
	if(!comenziSelectate.length) {
		alert('Selectaţi mai întâi comanda pe care doriţi să o vizualizaţi!');
		return;
	}
	if(comenziSelectate.length > 1) {
		alert('Nu pot fi editate mai multe comenzi simultan!');
		return;	
	}
	var myAjax = new Ajax.Request('ajax.php?sectiune=comenzi&actiune=detaliiComanda', {
		method: 'post',
		parameters: '&comandaId=' + comenziSelectate[0],
		onSuccess: function(transport) {
			var myWin = new windowManager(700, 500, transport.responseText, {closeButton: {id: 'detaliiComanda_renunt'}}, {icon: {src: 'images/icons/vezi_comenzi.png', height: '16', width: '16'}, text: 'Detalii comanda'});
		}
	});
}

Event.observe(window, 'load', function() {
	getComenzi();
	Event.observe('tableContent', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection(elementClicked.up());
	});
});