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
	activeElement = (!Object.isString(active) ? 'sd0' : active);

	var myAjax = new Ajax.Request(
		'ajax.php?sectiune=categorii&actiune=getCategories', 
		{
			method: 'get',
			onSuccess: function(transport) {
				transport.responseText.evalScripts();
				fireEvent($(activeElement), 'click');
			}
		}
	);
}

function refreshSelected (text) {
	var selectedLink = $$('a.nodeSel');
	selectedLink[0].update(text);
}

Event.observe(window, 'load', getCategorii);