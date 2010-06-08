var caracteristiciSelectate = [];
var imaginiSelectate = [];

function showHide(section, id, event) {
	switch(event) {
		case 'on': {
			$('row_' + section + '_' + id).style.backgroundColor = '#CCD9F2';
			$('span_' + section + '_' + id).style.display = 'none';
			$('check_' + section + '_' + id).style.display = 'block';
			if(section == 'imagini') {
				$('img_' + id).style.borderColor = '#99B4E5';
			}
		}break;
		case 'off': {
			if(!($('check_' + section + '_' + id).checked)) {
				$('row_' + section + '_' + id).style.backgroundColor = '';
				$('check_' + section + '_' + id).style.display = 'none';
				$('span_' + section + '_' + id).style.display = 'block';
				if(section == 'imagini') {
					$('img_' + id).style.borderColor = '#FFFFFF';
				}				
			}
			else {
				$('row_' + section + '_' + id).style.backgroundColor = '#E7EDF9';
				if(section == 'imagini') {
					$('img_' + id).style.borderColor = '#CCD9F2';
				}
				
			}
		}break;
	}
}

function selectDeselect(element, type, id) {
	if(element.checked) {
		window[type + 'Selectate'].push(id);
	}
	else {
		window[type + 'Selectate'] = window[type + 'Selectate'].without(id);
	}
}

function markSelection(row) {
	var pattern = /(\w+)\_(\w+)\_(\d+)/;
	var details = pattern.exec(row.identify());
	if(details && details[1] != 'anonymous') {
		//clicked on a row
		var checkBox = $('check_' + details[2] + '_' + details[3]); 
		if(!checkBox.checked) {
			$('span_' + details[2] + '_' + details[3]).style.display = 'none';
			checkBox.style.display = 'block';
			checkBox.checked = true;
			window[details[2] + 'Selectate'].push(details[3]);
		}
		else {
			$('span_' + details[2] + '_' + details[3]).style.display = 'block';
			checkBox.style.display = 'none';
			checkBox.checked = false;
			window[details[2] + 'Selectate'] = window[details[2] + 'Selectate'].without(details[3]);
		}
	}
}

function getCaracteristici (id, firstRun) {
	var myAjax = new Ajax.Request(
		'ajax.php', 
		{
			method: 'get',
			parameters: 'sectiune=produse&actiune=getCaracteristici&produsId=' + id + '&firstRun=' + Number(!Object.isUndefined(firstRun)),
			onSuccess: function(transport) {
				$('caracteristici').update(transport.responseText);
			}
		}
	);
}

function getImagini (id, firstRun) {
	var myAjax = new Ajax.Request(
		'ajax.php', 
		{
			method: 'get',
			parameters: 'sectiune=produse&actiune=getImagini&produsId=' + id + '&firstRun=' + Number(!Object.isUndefined(firstRun)),
			onSuccess: function(transport) {
				$('imagini').update(transport.responseText);
			}
		}
	);
}

function addCaracteristica() {
	var myHandler = function() {
		var sectiune = $('sectiune');
		var caracteristica = $('caracteristica');
		var data = {sectiune: sectiune.options[sectiune.selectedIndex].text.replace(/&/g,"%26"), caracteristica: {id: caracteristica.options[caracteristica.selectedIndex].value, name: caracteristica.options[caracteristica.selectedIndex].text.replace(/&/g,"%26")}, valoare: $F('valoare').replace(/&/g,"%26")};
		if(data.valoare) {
			var caracteristici = $('caracteristici');
			caracteristici.update();
			caracteristici.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php?sectiune=produse&actiune=adaugaCaracteristica&produsId=' + $F('_produsId'), 
			{
				method: 'post',
				parameters: 'data=' + Object.toJSON(data),
				onSuccess: function(transport) {
					var caracteristici = $('caracteristici');
					caracteristici.update(transport.responseText);
					caracteristici.removeClassName('ajaxRequest');
					caracteristiciSelectate.clear();
				}
			});
			Windows.getFocusedWindow().destroy();
		}
	};
	_filterSectiuni();
	refreshOptions($('caracteristica'), {});
	var myWin = new windowManager(450, 200, $('_winCaracteristici'), {actionButton: {id: 'caracteristici_continua', handler: myHandler}, closeButton: {id: 'caracteristici_renunt'}}, {icon: {src: 'images/icons/add.png', height: '16', width: '16'}, text: 'Adaugă caracteristică'}, 'sectiune');
	var valoare = $('valoare');
	valoare.value = '';
	valoare.disabled = true;
	
}

function stergeCaracteristici() {
	if(caracteristiciSelectate.length && window.confirm('Confirmaţi ştergerea caracteristicilor selectate ?')) {
		var caracteristici = $('caracteristici');
		caracteristici.update();
		caracteristici.addClassName('ajaxRequest');
		var myAjax = new Ajax.Request('ajax.php?sectiune=produse&actiune=stergeCaracteristici&produsId=' + $F('_produsId'),
		{
			method: 'post',
			parameters: 'selected=' + Object.toJSON(caracteristiciSelectate),
			onSuccess: function(transport) {
				var caracteristici = $('caracteristici');
				caracteristici.update(transport.responseText);
				caracteristici.removeClassName('ajaxRequest');
				caracteristiciSelectate.clear();
			}
		});
	}
}

function editCaracteristica() {
	if(!caracteristiciSelectate.length) {
		alert('Selectati mai intai caracteristica pe care doriti sa o editati!');
		return;
	}
	if(caracteristiciSelectate.length > 1) {
		alert('Nu pot fi editate mai multe caracteristici simultan!');
		return;	
	}
	
	var myHandler = function() {
		var sectiune = $('sectiune');
		var caracteristica = $('caracteristica');
		var valoare = $F('valoare')
		if(valoare) {
			var caracteristici = $('caracteristici');
			caracteristici.update();
			caracteristici.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php?sectiune=produse&actiune=editeazaCaracteristica&produsId=' + $F('_produsId'), 
			{
				method: 'post',
				parameters: 'caracteristicaId=' + caracteristiciSelectate[0] + '&valoare=' + valoare,
				onSuccess: function(transport) {
					var caracteristici = $('caracteristici');
					caracteristici.update(transport.responseText);
					caracteristici.removeClassName('ajaxRequest');
					caracteristiciSelectate.clear();
					refreshOptions($('caracteristica'), []);
					valoare.innerHTML = '';
					valoare.disabled = true;
				}
			});
			Windows.getFocusedWindow().destroy();
		}
	};
	var sectiune = $('sectiune');
	var caracteristica = $('caracteristica');
	var valoare = $('valoare'); 
	var row = $('row_caracteristici_' + caracteristiciSelectate).childElements();
	refreshOptions(sectiune, [{id:1, text: row[1].innerHTML.unescapeHTML()}]);
	refreshOptions(caracteristica, [{id:1, text: row[2].innerHTML.unescapeHTML()}]);
	valoare.innerHTML = row[3].innerHTML.replace(/<br>/g, '\n').unescapeHTML();
	valoare.disabled = false;
	sectiune.disabled = true;
	caracteristica.disabled = true;
	var myWin = new windowManager(450, 200, $('_winCaracteristici'), {
        actionButton: {
            id: 'caracteristici_continua',
            handler: myHandler
        },
        closeButton: {
            id: 'caracteristici_renunt'
        }
    }, {
        icon: {
            src: 'images/icons/edit.png',
            height: '16',
            width: '16'
        },
        text: 'Editează caracteristica'
    }, 'valoare');
}

function _filterSectiuni() {
	var myAjax = new Ajax.Request('ajax.php', {
		method: 'get',
		parameters: 'sectiune=produse&actiune=filterSectiuni&categorieId=' + $F('_categorieId') + '&produsId=' + $F('_produsId'),
		onSuccess: function(transport) {
			var selectObject = $('sectiune');
			refreshOptions(selectObject, transport.responseText.evalJSON());
			selectObject.disabled = false;
			selectObject.focus();
		}
	});
}

function _filterCaracteristici(sectiune) {
	if((index = sectiune.selectedIndex)) {
		var myAjax = new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'sectiune=produse&actiune=filterCaracteristici&categorieId=' + $F('_categorieId') + '&produsId=' + $F('_produsId') + '&sectiuneId=' + sectiune.options[index].value,
			onSuccess: function(transport) {
				var selectObject = $('caracteristica');
				if(refreshOptions(selectObject, transport.responseText.evalJSON())) {
					selectObject.disabled = false;
					selectObject.focus();
				}
			}
		});		
	}
}

function refreshOptions(selectObject, items) {
	var i;
	for(i = selectObject.options.length-1; i >=0 ; i --) {
		selectObject.remove(i);
	}
	for(var item in items) {
		if(!Object.isFunction(items[item])) {
			if(parseInt(items[item]['id']) == -1) {
				return false;
			}
			else {
				var newOpt = document.createElement('option');
				newOpt.text = items[item]['text'];
				newOpt.value = items[item]['id'];
				selectObject.options.add(newOpt);				
			}
		}
	}
	return true;
}

function _caracteristicaSelectata(select) {
	if(select.selectedIndex) {
		var valoare = $('valoare');
		valoare.disabled = false;
		valoare.focus();
	}
}

function addImagine() {
	var myHandler = function() {
		if(Windows.getFocusedWindow().getSize().height == 130) {
			alert('Incarcati mai intai imaginea, folosind functia "Upload" !');
			return;
		}
		
		if($('imagine').value) {
			var imagini = $('imagini');
			imagini.update();
			imagini.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php', 
			{
				method: 'get',
				parameters: 'sectiune=produse&actiune=salveazaImaginea&produsId=' + $F('_produsId'),
				onSuccess: function(transport) {
					var imagini = $('imagini');
					imagini.update(transport.responseText);
					imagini.removeClassName('ajaxRequest');
					imaginiSelectate.clear();
				}
			});
			Windows.getFocusedWindow().destroy();
		}
	};
	$('source').value = 'new';
    var myWin = new windowManager(370, 130, $('_winImagini'), {
        actionButton: {
            id: 'imagini_continua',
            handler: myHandler
        },
        closeButton: {
            id: 'imagini_renunt'
        }
    }, {
        icon: {
            src: 'images/icons/add.png',
            height: '16',
            width: '16'
        },
        text: 'Adaugă imagine'
    }, 'imagine');
	Windows.getFocusedWindow().updateHeight();
}

function editImagine() {
	var myHandler = function() {
		if(Windows.getFocusedWindow().getSize().height == 130) {
			alert('Incarcati mai intai imaginea, folosind functia "Upload" !');
			return;
		}
		
		if($('imagine').value) {
			var imagini = $('imagini');
			imagini.update();
			imagini.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php', 
			{
				method: 'get',
				parameters: 'sectiune=produse&actiune=salveazaImaginea&produsId=' + $F('_produsId') + '&imagineId=' + imaginiSelectate[0],
				onSuccess: function(transport) {
					var imagini = $('imagini');
					imagini.update(transport.responseText);
					imagini.removeClassName('ajaxRequest');
					imaginiSelectate.clear();
				}
			});
			Windows.getFocusedWindow().destroy();
		}
	};
	$('source').value = 'edit';
	var img = $('row_imagini_' + imaginiSelectate[0]).childElements()[1].childElements()[0].src;
	var myWin = new windowManager(370, 310, $('_winImagini'), {actionButton: {id: 'imagini_continua', handler: myHandler}, closeButton: {id: 'imagini_renunt'}}, {icon: {src: 'images/icons/edit.png', height: '16', width: '16'}, text: 'Editează imagine'}, 'imagine');
	showImage(img.replace(/\/50\//, '/200/'));
	//showImage('../thumbs/200/' + img);
}

function stergeImagini() {
	if(imaginiSelectate.length && window.confirm('Confirmati stergerea imaginilor selectate?')) {
		var imagini = $('imagini');
		imagini.update();
		imagini.addClassName('ajaxRequest');
		var myAjax = new Ajax.Request('ajax.php?sectiune=produse&actiune=stergeImagini', 
		{
			method: 'post',
			parameters: 'ids=' + Object.toJSON(imaginiSelectate),
			onSuccess: function(transport) {
				var imagini = $('imagini');
				imagini.update(transport.responseText);
				imagini.removeClassName('ajaxRequest');
				imaginiSelectate.clear();	
			}
		});
	}
}

function setDefault() {
	var cate = imaginiSelectate.length;
	if(!cate) {
		return;
	}
	if(cate > 1) {
		alert('O singură imagine poate fi setată ca implicită!');
		return;
	}
	else {
		var imagini = $('imagini');
		imagini.update();
		imagini.addClassName('ajaxRequest');
		var myAjax = new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'sectiune=produse&actiune=setDefaultImage&id=' + imaginiSelectate[0],
			onSuccess: function(transport) {
				var imagini = $('imagini');
				imagini.update(transport.responseText);
				imagini.removeClassName('ajaxRequest');
				imaginiSelectate.clear();
			}
		});
	}
}

function showImage(src) {
	var newImage = new Image();
	newImage.src = src;
	newImage.style.border = '1px solid #EFEFEF';
	
	var container = $('imgContainer');
	container.firstDescendant().remove();
	container.appendChild(newImage);
	container.up().style.height = '300px';
	Windows.getFocusedWindow().updateHeight();
	Windows.getFocusedWindow().showCenter();
}

Event.observe(window, 'load', function() {
	produsId = $F('_produsId');
	getCaracteristici(produsId, true);
	getImagini(produsId, true);

	Event.observe('caracteristici', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection(elementClicked.up());
	});
	Event.observe('imagini', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection(elementClicked.up());
	});
		
	$('denumire').focus();
});