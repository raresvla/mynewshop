function editeaza(event, UIObject) {
	UIObject.disableButton('add');
	UIObject.disableButton('delete');
	UIObject.disableButton('caracteristici');
	
	UIObject.addButton({id: 'renunt', name: 'Renunţ', icon: 'images/icons/renunt.png', width: 80, link: renunt, parameters: {context: 'edit'}}, 'right');
	UIObject.addButton({id: 'save', name: 'Salvează', icon: 'images/icons/salveaza.png', width: 80, link: salveaza, parameters: {context: 'edit'}}, 'left');
	UIObject.change('edit', 'save');
	UIObject.hideButton('edit');
		
    var textBox = UIObject.changeElements({id: $('categorie').firstChild, type: 'text'}, {element: 'input', type: 'text', name: 'denumire', id: 'denumire', className: 'inputcol'});
    textBox.focus();
	
	var fileInput = $('changeImage');
	if(fileInput) {
		fileInput.update('<div style="margin-bottom:5px;">Schimbă:</div>' +
				'<input type="file" name="_icon" id="_icon" size="25" class="inputcol" />' +
				'<input type="submit" name="upload" id="upload" value="Upload" class="inputcol" />');
	}
	
	$('_context').value = 'edit';
}

function adauga(event, UIObject) {
	/* arrange the UI */
	UIObject.disableButton('edit');
	UIObject.disableButton('delete');
	UIObject.disableButton('caracteristici');
	
	UIObject.addButton({id: 'renunt', name: 'Renunţ', icon: 'images/icons/renunt.png', width: 80, link: renunt, parameters: {context: 'add'}}, 'right');
	UIObject.addButton({id: 'save', name: 'Salvează', icon: 'images/icons/salveaza.png', width: 80, link: salveaza, parameters: {context: 'add'}}, 'left');
	UIObject.change('add', 'save');
	UIObject.hideButton('add');
	
	if($F('_id')) {
        var textBox = UIObject.changeElements({id: $('categorie').firstChild, type: 'text'}, {element: 'input', type: 'text', name: 'denumire', id: 'denumire', className: 'inputcol', value: ''});
        textBox.focus();
		
		$('imageUploading').show();
		
		var fileInput = $('changeImage');
		if(fileInput) {
			fileInput.update('Selectaţi:<br />' +
					'<img src="images/spacer.gif" height="5" width="100%" /><br />' +
					'<input type="file" name="_icon" id="_icon" size="25" class="inputcol" />' +
					'<input type="submit" name="upload" id="upload" value="Upload" class="inputcol" />');
			$('icon').hide();
		}
		
		$('_context').value = 'add';
		parent.$('viewCurrentCateg').style.height = (document.body.scrollHeight + 20) + 'px';
	}
	else {
		/* base view */
		$('insert').show();
		$('stats').hide();
		$('denumire_RO').focus();
	}
}

function sterge(event, UIObject) { 
	if(window.confirm('Confirmati stergerea categoriei curente ?\n\nATENTIE: Vor fi sterse atat subcategoriile sale, cat si produsele inregistrate in acestea.')) {
		var myAjax = new Ajax.Request ('ajax.php', {
			method: 'get',
			parameters: 'sectiune=categorii&actiune=sterge&id=' + $F('_id'),
			onSuccess: function(transport) {
				if(transport.responseText != 'false') {
					if(transport.responseText == -1) {
						parent.getCategorii();
						window.location.replace('viewCategorie.php?base=1');						
					}
					else {
						parent.getCategorii('sd' + transport.responseText);
						window.location.replace('viewCategorie.php?categorieId=' + transport.responseText);
					}
				}
			}
		});
	}
}

function refreshIcon(newIcon) {
	var icon = $('icon');
	
	icon.src = "../imagini/categorii/" + newIcon;
	icon.show();
}

function caracteristici() {
	popup('edit_caracteristici.php?id=' + $F('_id'), 'new', 700, 650);
	fereastra.focus();
}

function salveaza(event, UIObject, parameters) {
	blank = [];
	param = parameters;
	var error = false;
	
    var value = $F('denumire');
    if(value.length) {
        window['denumire'] = value;
    }
    else {
        blank.push('denumire');
    }
	
	if((cate = blank.length)) {
		/* Campuri invalide */
		var buffer = '';
		for(i=0; i<cate; i++) {
			buffer += ' - ' + blank[i] + ';\n';
		}
		
		error = true;
		alert('Urmatoarele campuri sunt obligatorii:\n' + buffer + '\nIntroduceti continut valid in campurile specificate!');
	}
	if((fileInput = $('changeImage')) && parameters.context!= 'edit' && !error) {
		/* verificare imagine */
		if(!$F('_icon')) {
			error = true;
			alert('Incarcati un icon!\n(Tip: .png/.gif, fundal transparent; Dimensiune: 100x100 px)');
		}
	}
	
	if(!error) {
		var myAjax = new Ajax.Request('ajax.php?sectiune=categorii&actiune=salveaza&context=' + parameters.context +'&id=' + $F('_id'), {
			method: 'post',
			parameters: "denumire=" + denumire,
			onSuccess: function(transport) {
				if(transport.responseText.length != 1 || parseInt(transport.responseText) != 1) {
					alert('EROARE:\n' + transport.responseText + '.\n\nContactati Administratorul site-ului in legatura cu aceasta problema!');
					
					renunt(null, UIObject, {context: 'edit'});
				}
				else {
					if(param.context == 'edit') {
						strong = UIObject.changeElements({id: $('denumire'), type: 'input'}, {element: 'strong', value: window['denumire']}, true);
						strong.style.lineHeight = '19px';
						UIObject.deleteButton('renunt');
						UIObject.enableButton('add');
						UIObject.enableButton('delete');
						UIObject.enableButton('caracteristici');
						
						UIObject.change('save', 'edit');
						UIObject.showButton('edit');
						UIObject.deleteButton('save');
						
						var fileInput = $('changeImage');
						if(fileInput) {
							fileInput.update('');
						}
						
						//refresh text in parent
						parent.refreshSelected(strong.innerHTML);
						delete strong;
					}
					else {
						var parentSelected = parent.$$('a.nodeSel');
						parent.getCategorii('sd' + parentSelected[0].identify());
						
						window.location.reload(true);
					}
				}
			}
		});
	}
}

function renunt(event, UIObject, parameters) {
	UIObject.deleteButton('renunt');
	switch(parameters.context) {
		case 'edit': {
			var strong = UIObject.changeElements({id: $('denumire'), type: 'input'}, {element: 'strong'}, true);
			strong.style.lineHeight = '19px';
		
			UIObject.enableButton('add');
			UIObject.enableButton('delete');
			UIObject.enableButton('caracteristici');
			
			UIObject.change('save', 'edit');
			UIObject.showButton('edit');
			UIObject.deleteButton('save');
			
			var fileInput = $('changeImage');
			if(fileInput) {
				fileInput.update('');
				clearUpload();
			}
		} break;
		case 'add': {
			UIObject.enableButton('edit');
			UIObject.enableButton('delete');
			UIObject.enableButton('caracteristici');
			
			UIObject.change('save', 'add');
			UIObject.showButton('add');
			UIObject.deleteButton('save');
							
			if($F('_id')) {
				var strong = UIObject.changeElements({id: $('denumire'), type: 'input'}, {element: 'strong'}, true);
				strong.style.lineHeight = '19px';
				
				if(!$F('_parentId')) {
					$('imageUploading').hide();
				}
				
				var fileInput = $('changeImage');
				if(fileInput) {
					fileInput.update('');
					clearUpload();
					$('icon').show();
				}
			}
			else {
				/* base view */
				$('insert').hide();
				$('stats').show();				
			}
			parent.$('viewCurrentCateg').style.height = (document.body.scrollHeight + 20) + 'px';
		} break;
	}
}

function clearUpload() {
	var myAjax = new Ajax.Request ('ajax.php', {
		method: 'get',
		parameters: 'sectiune=categorii&actiune=clearUpload',
		onSuccess: function(transport) {
			if(transport.responseText != 'false') {
				refreshIcon(transport.responseText);
			}
		}
	});
}