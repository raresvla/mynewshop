uiButton = Class.create({
	id: null,
	name: null,
	icon: null,
	link: null,
	parameters: null,
	linkClass: 'blue_box',
	floatClass: {left: 'fLeft', right: 'fRight'},
	marginClass: {left: 'marginRight', right: 'marginLeft'},
	hidden: false,
	disabled: false,

	initialize: function(id, name, icon, width, link, parameters, hidden) {
		this.id = id;
		this.name = name;
		this.icon = icon;
		this.width = width;
		this.link = link;
		this.parameters = parameters;
		this.hidden = (!hidden ? false : true);
	},
	
	linkTo: function(container, position, UIObject) {
		var button = document.createElement('a');
		button.setAttribute('id', this.id);
		if(Object.isFunction(this.link)) {
			button.setAttribute('href', 'javascript://');
			this.bLink = this.link.bindAsEventListener(this, UIObject, this.parameters);
			Event.observe(button, 'click', this.bLink);
		}
		else {
			button.setAttribute('href', this.link);
		}
		button.className = this.linkClass + " " + eval('this.floatClass.' + position) + " " + eval('this.marginClass.' + position);		
		if(this.icon) {
			var icon = document.createElement('img');
			icon.src = this.icon;
			button.appendChild(icon);
		}
		var text = document.createElement('span');
		text.innerHTML = (this.icon ? "&nbsp;" : "") + this.name;
		button.appendChild(text);
		button.style.width = this.width + 'px';
		button.style.display = (this.hidden ? 'none' : 'block');
		
		$(container).appendChild(button);
	},
	
	disable: function() {
		if(!this.disabled) {
			var DOMElement = $(this.id);
			var DOMImage = DOMElement.down();
			 
			if(Object.isFunction(this.link)) {
				Event.stopObserving(DOMElement, 'click', this.bLink);
			}
			else {
				DOMElement.setAttribute('href', 'javascript://');
			}
			DOMElement.addClassName('disabled');
			
			var temp = DOMImage.src.split(/\./);
			temp[0] += "_disabled";
			DOMImage.src = temp.join(".");
			this.disabled = true;
		}
	},
	
	enable: function() {
		if(this.disabled) {
			var DOMElement = $(this.id);
			var DOMImage = DOMElement.down();
			
			if(Object.isFunction(this.link)) {
				Event.observe(DOMElement, 'click', this.bLink);
			}
			else {
				DOMElement.setAttribute('href', this.link);
			}
			DOMElement.removeClassName('disabled');
			DOMImage.src = DOMImage.src.replace(/\_disabled/, "");
			this.disabled = false;
		}
	},
	
	hide: function() {
		$(this.id).style.display = 'none';
		this.hidden = true;
	},
	
	show: function() {
		$(this.id).style.display = 'block';
		this.hidden = false;
	},
	
	remove: function() {
		if(Object.isFunction(this.link)) {
			Event.stopObserving($(this.id), 'click', this.bLink);
		}
		$(this.id).remove();
	}
});

UI = Class.create({
	container: null,
	buttons: null,
	ids: null,

	initialize: function(container) {
		this.container = container;
		this.buttons = [];
		this.ids = [];
	},
	
	addButton: function(details, pos) {
		var myButton = new uiButton(details.id, details.name, details.icon, details.width, details.link, details.parameters, details.hidden);
		myButton.linkTo(this.container, pos, this);
		
		this.buttons.push({button: myButton, position: pos});
		this.ids.push(details.id);
	},
	
	disableButton: function(buttonId) {
		if((index = this.ids.indexOf(buttonId)) != -1)
			this.buttons[index].button.disable();
	},
	
	enableButton: function(buttonId) {
		if((index = this.ids.indexOf(buttonId)) != -1)
			this.buttons[index].button.enable();
	},
	
	deleteButton: function(buttonId) {
		if((index = this.ids.indexOf(buttonId)) != -1) {
			this.buttons[index].button.remove();
			delete this.buttons[index];
			delete this.ids[index];
			
			this.buttons = this.buttons.compact();
			this.ids = this.ids.compact();
		}
	},
	
	hideButton: function(buttonId) {
		if((index = this.ids.indexOf(buttonId)) != -1)
			this.buttons[index].button.hide();
	},
	
	showButton: function(buttonId) {
		if((index = this.ids.indexOf(buttonId)) != -1)
			this.buttons[index].button.show();
	},
	
	change: function(button1, button2) {
		var index1 = this.ids.indexOf(button1);
		var index2 = this.ids.indexOf(button2);
		
		var temp = this.buttons[index1];
		this.buttons[index1] = this.buttons[index2];
		this.buttons[index2] = temp;
		
		temp = this.ids[index1];
		this.ids[index1] = this.ids[index2];
		this.ids[index2] = temp;
		
		this._clear();
		this._repaint();
	},
	
	_clear: function() {
		this.buttons.each(function(buttonObject) {
			if(!Object.isUndefined(buttonObject.button))
				buttonObject.button.remove();
		});
	},
	
	_repaint: function() {
		var parent = this;
		this.buttons.each(function(buttonObject) {
			buttonObject.button.linkTo(parent.container, buttonObject.position, parent);
			/* reset disabling */
			if(buttonObject.button.disabled) {
				buttonObject.button.disabled = false;
				buttonObject.button.disable();
			}
		});		
	},
	
	changeElements: function(element1, element2, revert) {
		var keys = Object.keys(element2);
		var type = keys.shift();
		type = eval('element2.' + type);
		
		var container = $(element1.id).up();
		var myElement = document.createElement(type);
		keys.each(function(key) {
			if(key == 'value') {
				eval('myElement.' + (type == 'input' ? 'value' : 'innerHTML') + ' = element2.' + key);
			}
			else {
				eval('myElement.' + key + ' = element2.' + key);
			}
		});
		
		/* if value is not defined */
		if(keys.indexOf('value') == -1) {
			var value = (element1.type == 'text' ? element1.id.innerHTML : element1.id.value);
			eval('myElement.' + (type == 'input' ? 'value' : 'innerHTML') + ' = value;');
		}
		
		if(!revert) {
			var backUp = document.createElement('input');
			backUp.type = 'hidden';
			backUp.id = '_' + element2.id;
			
			backUp.value = (element1.type == 'text' ? element1.id.innerHTML : element1.id.value);
			container.appendChild(backUp);
		}
		else {
			var backUp = $('_' + element1.id.identify());
			if(keys.indexOf('value') == -1) {
				myElement.innerHTML = backUp.value;
			}
			backUp.remove();
		}

		container.removeChild(element1.id);
		container.appendChild(myElement);
		
		return myElement;
	}
});