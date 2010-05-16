var sectiuniSelected = [];
var caracteristiciSelected = [];
var sectionDisplayed = null;

function getSectiuni() {
	var myAjax = new Ajax.Request('ajax.php', {
		method: 'get',
		parameters: 'sectiune=categorii&actiune=getSectiuniCaracteristici&categorieId=' + $F('_categorie'),
		onSuccess: function(transport) {
			$('sectiuniCaracteristici').update(transport.responseText);
			sectiuniSelected.clear();
			sectiuniSelected.push(section);
		}
	});
}

function getCaracteristici(section) {
	if(sectionDisplayed && (row = $('row_sectiuni_' + sectionDisplayed))) {
		row.removeClassName('selectedRow');
	}
	sectionDisplayed = section;
	
	var caracteristici = $('caracteristici');
	caracteristici.update();
	caracteristici.addClassName('ajaxRequest');
	
	var myAjax = new Ajax.Request('ajax.php', {
		method: 'get',
		parameters: 'sectiune=categorii&actiune=getCaracteristici&categorieId=' + $F('_categorie') + "&sectiuneId=" + section,
		onSuccess: function(transport) {
			caracteristiciSelected.clear();
			var caracteristici = $('caracteristici'); 
			caracteristici.update(transport.responseText);
			caracteristici.removeClassName('ajaxRequest');
		}
	});
}

function selectDeselect(element, type, id) {
	if(element.checked) {
		window[type + 'Selected'].push(id);
	}
	else {
		window[type + 'Selected'] = window[type + 'Selected'].without(id);
	}
}

function markSelection(row) {
	var pattern = /(\w+)\_(\w+)\_(\d+)/;
	var details = pattern.exec(row.identify());
	if(details && details[1] != 'anonymous') {
		row.addClassName('selectedRow');
		getCaracteristici(details[3]);
	}
}

function markSelection2(row) {
	var pattern = /(\w+)\_(\w+)\_(\d+)/;
	var details = pattern.exec(row.identify());
	if(details && details[1] != 'anonymous') {
		//clicked on a row
		var checkBox = $('check_' + details[2] + '_' + details[3]); 
		if(!checkBox.checked) {
			$('span_' + details[2] + '_' + details[3]).style.display = 'none';
			checkBox.style.display = 'block';
			checkBox.checked = true;
			caracteristiciSelected.push(details[3]);
		}
		else {
			$('span_' + details[2] + '_' + details[3]).style.display = 'block';
			checkBox.style.display = 'none';
			checkBox.checked = false;
			caracteristiciSelected = caracteristiciSelected.without(details[3]);
		}
	}
}

function showHide(section, id, event) {
	switch(event) {
		case 'on': {
			$('row_' + section + '_' + id).style.backgroundColor = '#CCD9F2';
			$('span_' + section + '_' + id).style.display = 'none';
			$('check_' + section + '_' + id).style.display = 'block';
		} break;
		case 'off': {
			if(!($('check_' + section + '_' + id).checked)) {
				$('row_' + section + '_' + id).style.backgroundColor = '';
				$('check_' + section + '_' + id).style.display = 'none';
				$('span_' + section + '_' + id).style.display = 'block';
			}
			else {
				$('row_' + section + '_' + id).style.backgroundColor = '#E7EDF9';
			}
		} break;
	}
}

function move(direction) {
	if(sectiuniSelected.length > 1) {
		alert('Pentru a muta o sectiune mai sus / jos, selectati doar acea sectiune!');
	}
	else {
		if(sectiuniSelected.length) {
			var sectiuni = $('sectiuniCaracteristici');
			sectiuni.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php', {
				method: 'get',
				parameters: 'sectiune=categorii&actiune=mutaSectiune&categorieId=' + $F('_categorie') + '&sectiuneId=' + sectiuniSelected[0] + '&direction=' + direction + '&activeRow=' + sectionDisplayed,
				onSuccess: function(transport) {
					var sectiuni = $('sectiuniCaracteristici'); 
					sectiuni.update(transport.responseText);
					sectiuni.removeClassName('ajaxRequest');
				}
			});
		}	
	}
}

function stergeSectiune() {
	if(sectiuniSelected.length && window.confirm('Confirmati stergerea sectiunilor selectate ?\nAtentie: Aceasta actiune implica stergerea caracteristicilor asociate.')) {
		var sectiuni = $('sectiuniCaracteristici');
		sectiuni.addClassName('ajaxRequest');
		var myAjax = new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'sectiune=categorii&actiune=stergeSectiune&categorieId=' + $F('_categorie') + '&sectiuni=' + sectiuniSelected.toJSON() + '&activeRow=' + sectionDisplayed,
			onSuccess: function(transport) {
				var sectiuni = $('sectiuniCaracteristici'); 
				sectiuni.update(transport.responseText);
				sectiuni.removeClassName('ajaxRequest');
				sectiuniSelected.clear();
			}
		});
		$('caracteristici').update('<div class="loader"><img src="images/icons/info2.png" width="16" height="16" class="valignMiddle" style="padding:5px 0px" /> Selectati o sectiune din lista de mai sus.</div>');
	}
}

function stergeCaracteristici() {
	if(caracteristiciSelected.length && window.confirm('Confirmati stergerea caracteristicilor selectate ?')) {
		var caracteristici = $('caracteristici');
		caracteristici.update();
		caracteristici.addClassName('ajaxRequest');
		var myAjax = new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'sectiune=categorii&actiune=stergeCaracteristica&categorieId=' + $F('_categorie') + '&sectiuneId=' + sectionDisplayed + '&caracteristici=' + caracteristiciSelected.toJSON(),
			onSuccess: function(transport) {
				var caracteristici = $('caracteristici');
				caracteristici.update(transport.responseText);
				caracteristici.removeClassName('ajaxRequest');
				caracteristiciSelected.clear();
			}
		});
	}
}

function addSectiune() {
	var myHandler = function() {
		var data = $F('sectiune');
		if(data) {
			var sectiuni = $('sectiuniCaracteristici');
			sectiuni.update();
			sectiuni.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php', {
				method: 'get',
				parameters: 'sectiune=categorii&actiune=adaugaSectiune&categorieId=' + $F('_categorie') + '&data=' + data + '&activeRow=' + sectionDisplayed,
				onSuccess: function(transport) {
					var sectiuni = $('sectiuniCaracteristici'); 
					sectiuni.update(transport.responseText);
					sectiuni.removeClassName('ajaxRequest');
					sectiuniSelected.clear();
				}
			});
			Windows.getFocusedWindow().destroy();
		}
	};
	$('sectiune').value = '';
	var myWin = new windowManager(400, 130, $('_winSectiuni'), {actionButton: {id: 'sectiuni_continua', handler: myHandler}, closeButton: {id: 'sectiuni_renunt'}}, {icon: {src: 'images/icons/add.png', height: '16', width: '16'}, text: 'Adauga sectiune'}, 'sectiune');
}

function editSectiune() {
	if(!sectiuniSelected.length) {
		alert('Selectati mai intai sectiunea pe care doriti sa o editati!');
		return;
	}
	if(sectiuniSelected.length > 1) {
		alert('Nu pot fi editate mai multe sectiuni simultan!');
		return;	
	}
	
	var myHandler = function() {
		data = $F('sectiune');
		if(data) {
			//trimite date prin AJAX
			var myAjax = new Ajax.Request('ajax.php', {
				method: 'get',
				parameters: 'sectiune=categorii&actiune=editSectiune&categorieId=' + $F('_categorie') + '&sectiuneId=' + sectiuniSelected[0] + '&data=' + data,
				onSuccess: function() {
					//update tabel
					var cells = $('row_sectiuni_' + sectiuniSelected[0]).childElements();
					cells[1].innerHTML = data;
					
					$('check_sectiuni_' + sectiuniSelected[0]).checked = false;
					showHide('sectiuni', sectiuniSelected[0], 'off');
					sectiuniSelected.clear();
				}
			});
			Windows.getFocusedWindow().destroy();
		}
		
	};
	var cells = $('row_sectiuni_' + sectiuniSelected[0]).childElements();
	var myWin = new windowManager(400, 130, $('_winSectiuni'), {actionButton: {id: 'sectiuni_continua', handler: myHandler}, closeButton: {id: 'sectiuni_renunt'}}, {icon: {src: 'images/icons/edit.png', height: '16', width: '16'}, text: 'Editeaza sectiune'}, 'sectiune');
	$('sectiune').value = cells[1].innerHTML.unescapeHTML();
}

function addCaracteristica() {
	if(!sectionDisplayed) {
		alert('Selectati mai intai o sectiune din cele disponibile\nin partea de sus a ferestrei!');
		return;
	}
	var myHandler = function() {
		var data = {caracteristica: $F('caracteristica'), preview: $('caracteristica_preview').checked};
		if(data.caracteristica) {
			var caracteristici = $('caracteristici');
			caracteristici.update();
			caracteristici.addClassName('ajaxRequest');
			var myAjax = new Ajax.Request('ajax.php', {
				method: 'get',
				parameters: 'sectiune=categorii&actiune=adaugaCaracteristica&categorieId=' + $F('_categorie') + '&sectiuneId=' + sectionDisplayed + '&data=' + Object.toJSON(data),
				onSuccess: function(transport) {
					var caracteristici = $('caracteristici');
					caracteristici.update(transport.responseText);
					caracteristici.removeClassName('ajaxRequest');
				}
			});
			Windows.getFocusedWindow().destroy();
		}
	};
	$('caracteristica').value = '';
	var myWin = new windowManager(400, 140, $('_winCaracteristici'), {actionButton: {id: 'caracteristici_continua', handler: myHandler}, closeButton: {id: 'caracteristici_renunt'}}, {icon: {src: 'images/icons/add.png', height: '16', width: '16'}, text: 'Adauga caracteristica'}, 'caracteristica');
}

function editCaracteristica() {
	if(!sectionDisplayed) {
		return;
	}
	var cate = caracteristiciSelected.length;
	if(!cate) {
		alert('Selectati mai intai caracteristica pe care doriti sa o editati!');
		return;
	}
	if(cate > 1) {
		alert('Selectati o singura caracteristica spre editare!');
		return;
	}
	var myHandler = function() {
		var data = {caracteristica: $F('caracteristica'), preview: $('caracteristica_preview').checked};
		if(data.caracteristica) {
			var myAjax = new Ajax.Request('ajax.php', {
				method: 'get',
				parameters: 'sectiune=categorii&actiune=editCaracteristica&id=' + caracteristiciSelected[0] + '&data=' + Object.toJSON(data),
				onSuccess: function(transport) {
					//update tabel
					var row = $('row_caracteristici_' + caracteristiciSelected[0]);
					var cells = row.childElements();
					cells[1].innerHTML = data.caracteristica;
					cells[2].innerHTML = (data.preview ? "<img src=\"images/icons/ok.png\" alt=\"\" height=\"16\" width=\"16\">" : "&nbsp;");
					
					$('check_caracteristici_' + caracteristiciSelected[0]).checked = false;
					showHide('caracteristici', caracteristiciSelected[0], 'off');
					caracteristiciSelected.clear();
				}
			});
			Windows.getFocusedWindow().destroy();
		}	
	};
	var cells = $('row_caracteristici_' + caracteristiciSelected[0]).childElements();
	var myWin = new windowManager(400, 130, $('_winCaracteristici'), {actionButton: {id: 'caracteristici_continua', handler: myHandler}, closeButton: {id: 'caracteristici_renunt'}}, {icon: {src: 'images/icons/edit.png', height: '16', width: '16'}, text: 'Editeaza caracteristica'}, 'caracteristica');
	$('caracteristica').value = cells[1].innerHTML.unescapeHTML();
	$('caracteristica_preview').checked = (cells[2].innerHTML.include('ok.png') ? true : false);
}

Event.observe(window, 'load', function() {
	getSectiuni();
	Event.observe('sectiuniCaracteristici', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection(elementClicked.up());
	});
	Event.observe('caracteristici', 'click', function(event) {
		var elementClicked = Event.element(event);
		markSelection2(elementClicked.up());
	});	
})