function edit(id) {
	var row = $('row_' + id).childElements();
	var myHandler = function() {
		var parameters = {id: id, descriere: $F('descriere'), valoare: $F('valoare')};
		var myAjax = new Ajax.Request('ajax.php?sectiune=setari&actiune=editeazaSetarea', {
			method: 'post',
			parameters: Object.toQueryString(parameters),
			onSuccess: function() {
				row[1].update(parameters.descriere);
				row[2].update(parameters.valoare);
			}
		});
		this.win.close();
	}
	var myWin = new windowManager(400, 195, $('_winEditConfig'), {actionButton: {id: 'editConfig_continua', handler: myHandler}, closeButton: {id: 'editConfig_renunt'}}, {icon: {src: 'images/icons/edit.png', height: '16', width: '16'}, text: 'Editează setarea'}, 'descriere');
	
	$('variabila').update(row[0].innerHTML);
	$('descriere').update(row[1].innerHTML);
	$('valoare').value = row[2].innerHTML;
}