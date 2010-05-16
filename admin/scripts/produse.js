var treeShowed = true;
var categorieId = null;
var pagina = 1;
var produseSelectate = [];

function fireEvent(obj, evt) {
	var fireOnThis = obj;
	if( document.createEvent ) {
		var evObj = document.createEvent('MouseEvents');
		evObj.initEvent( evt, true, false );
		fireOnThis.dispatchEvent(evObj);
	} else if( document.createEventObject ) {
		fireOnThis.fireEvent('on'+evt);
	}
}

function getCategorii (active) {
	var myAjax = new Ajax.Request(
		'ajax.php?sectiune=produse&actiune=getCategories', 
		{
			method: 'get',
			onSuccess: function(transport) {
				transport.responseText.evalScripts();
			}
		}
	);
}

function getProduse (categorieId, pagina) {
	if(Object.isUndefined(pagina)) {
		var pagina = 1;
	}
	window.pagina = pagina;
	window.categorieId = categorieId;
	var myAjax = new Ajax.Request(
		'ajax.php?sectiune=produse&actiune=getProduse&categorieId=' + categorieId + "&pagina=" + pagina, 
		{
			method: 'get',
			onSuccess: function(transport) {
				$('rightContent').update(transport.responseText);
				produseSelectate.clear();
			}
		}
	);	
}

function goTo (pagina) {
	produseSelectate.clear();
	getProduse(categorieId, pagina);
}

function showHideTree(td) {
	var leftContent = $('leftContent');
	var rightContent = $('rightContent'); 
	
	if(treeShowed) {
		var height = rightContent.getHeight();
		$('categories_tree').hide();
		leftContent.style.width = '15px';
		leftContent.style.height = height + 'px';
		leftContent.style.borderRight = '1px solid #EFEFEF';
		rightContent.style.marginLeft = '35px';
		td.className = 'wrapper_closed';
		td.style.width = '10px';
		treeShowed = false;
	}
	else {
		$('categories_tree').show();
		leftContent.style.width = '220px';
		leftContent.style.height = '100%'
		leftContent.style.borderRight = '1px solid #999999';
		rightContent.style.marginLeft = '240px';
		td.className = 'wrapper_opened';
		treeShowed = true;
	}
}

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
		window['produseSelectate'].push(id);
	}
	else {
		window['produseSelectate'] = window['produseSelectate'].without(id);
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
			produseSelectate.push(details[2]);
		}
		else {
			$('span_' + details[2]).style.display = 'block';
			checkBox.style.display = 'none';
			checkBox.checked = false;
			produseSelectate = produseSelectate.without(details[2]);
		}
	}
}

function addProdus () {
	popup('edit_produs.php?action=add&categorieId=' + categorieId, 'new', 1000, 625);
	fereastra.focus();
}

function editProdus () {
	if(produseSelectate.length != 1) {
		alert('Selectati mai intai produsul pe care doriti sa il editati.\nSelectia nu poate contine mai mult de un element.');
	}
	else {
		popup('edit_produs.php?action=edit&id=' + produseSelectate[0], 'new', 1000, 625);
		fereastra.focus();
	}
}

function stergeProdus () {
	if(produseSelectate.length && window.confirm('Confirmati stergerea produselor selectate ?')) {
		var myAjax = new Ajax.Request('ajax.php?sectiune=produse&actiune=stergeProduse&categorieId=' + categorieId + '&pagina=' + pagina, {
			method: 'post',
			parameters: 'ids=' + Object.toJSON(produseSelectate),
			onSuccess: function(transport) {
				$('rightContent').update(transport.responseText);
				produseSelectate.clear();
			}
		});
	}
}

Event.observe(window, 'load', function() {
	getCategorii();
	Event.observe('rightContent', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection(elementClicked.up());
	});
});