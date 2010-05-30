var windowManager = Class.create({
	win: null,
	title: null,
	content: null,
	buttons: null,
	
	initialize: function(width, height, content, buttons, title, _focus) {
		this.title = '<img src="' + title.icon.src + '" width="' + title.icon.width + '" height="' + title.icon.height + '" alt="" class="valignMiddle" /> <strong>' + title.text + '</strong>';
		this.content = content;
		this.buttons = buttons;
		
		this.win = new Window( {
			className: "alphacube",
			title: this.title,
			width: width,
			height: height,
			resizable: false,
			minimizable: false,
			maximizable: false,
			hideEffect: Element.hide,
			showEffect: Element.show,
            destroyOnClose: true
		});
		if(Object.isElement(this.content)) {
			this.win.setHTMLContent(this.content.innerHTML.replace('|', '<br />'));
		}
		else {
			this.win.setHTMLContent(this.content);
		}
		this.win.showCenter();

        if(this.buttons.closeButton) {
            this.closeMethodBind = (this.buttons.closeButton.handler || Prototype.emptyFunction).wrap(function(closeHandler) {
                closeHandler();
                this.closeMethod();
            }).bindAsEventListener(this);
            Event.observe(this.buttons.closeButton.id, 'click', this.closeMethodBind);
        }
		
		if(this.buttons.actionButton) {
			this.actionMethod = this.buttons.actionButton.handler;
			this.actionMethodBind = this.actionMethod.bindAsEventListener(this);
			Event.observe(this.buttons.actionButton.id, 'click', this.actionMethodBind);
		}
		
		if(_focus) {
			$(_focus).focus();
		}

        this.win.myCloseObserver = {
            onClose: function(eventName, win) {
                if(win != this.win) {
                    return;
                }
                
                if(this.buttons.actionButton) {
                    Event.stopObserving(this.buttons.actionButton.id, 'click', this.actionMethodBind);
                }
                if(this.buttons.closeButton) {
                    Event.stopObserving(this.buttons.closeButton.id, 'click', this.closeMethodBind);
                }
                Windows.removeObserver(this.win.myCloseObserver);
            }.bind(this)
        }
        Windows.addObserver(this.win.myCloseObserver);
	},
	
	closeMethod: function() {
		this.win.close();
	}
});