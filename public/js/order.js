var Order = {
    content: null,
    form: null,
    
    init: function() {
        this.content = $('content');
        this.content.delegate('click', {
            '.next': this.nextStep.bindAsEventListener(this),
            '.next *': this.nextStep.bindAsEventListener(this),
            '.show-hide-next': this.showNextForm.bindAsEventListener(this),
            'table.sub-section *': this.selectSubSection.bindAsEventListener(this),
            '.do-action': this.doAction.bindAsEventListener(this),
            '.do-action *': this.doAction.bindAsEventListener(this)
        });
        this.submitObserverBinded = this.submitObserver.bindAsEventListener(this);
        
        Validation.add('bill-type', 'Selecta\u0163i modul de facturare', this.validateBillType.bind(this));
        this.setFormValidator();
    },
    
    setFormValidator: function() {
        if(!this.getActiveForm()) {
            return;
        }
        this.validator = new Validation(this.getActiveForm().identify(), {
            useTitles: true,
            focusOnError: true,
            onSubmit: false,
            onElementValidate: this.onElementValidate.bind(this)
        });
    },
    
    getActiveForm: function() {
        if(this.form === null) {
            if(this.dialogWindow) {
                this.form = this.dialogWindow.getContent().down('form');
            }
            if(!this.form) {
                this.form = this.content.down('form');
            }
        }
        if(!this.form) {
            return false;
        }
        return this.form;
    },

    _resetFormValidator: function(attachObserver) {
        this.form = null;
        this.setFormValidator();
        if(attachObserver) {
            this.attachFormObserver();
        }
    },

    attachFormObserver: function() {
        if(!this.getActiveForm()) {
            return;
        }
        this.getActiveForm().observe('submit', this.submitObserverBinded).focusFirstElement();
    },

    deattachFormObserver: function() {
        if(!this.getActiveForm()) {
            return;
        }
        this.getActiveForm().stopObserving('submit', this.submitObserverBinded);
    },

    onElementValidate: function(test, elem) {
        if(!test && elem.hasClassName('selected-sub-section')) {
            window.scrollTo(0, elem.up('.table-border').cumulativeOffset().top);
        }
    },

    validateBillType: function(value, elem) {
        if(!value) {
            return true;
        }
        if(parseInt(elem.up().previous().value)) {
            return true;
        }
        if(window.confirm('Contul dvs. nu are nicio companie asociat\u0103.\nDoriţi adăugarea unei noi companii ?')) {
            window.location.href = '/administrare-cont/companii';
            return false;
        }
        else {
            elem.checked = false;
            return false;
        }
    },

    nextStep: function() {
        if(!this.validator.validate()) {
            return;
        }

        this.form.submit();
    },

    selectSubSection: function(e) {
        var subsection = e.findElement('table');
        var clickedOnInput = (e.element().tagName.toLowerCase() == 'input');

        if(clickedOnInput && subsection.hasClassName('selected')) {
            e.element().checked = false;
            subsection.removeClassName('selected');
            return;
        }

        var container = subsection.up('.selectable-sub-section');
        var containerInput = container.down('.selected-sub-section');
        var sectionInput = subsection.down('input');

        container.select('table').each(function(table) {
            if(table != subsection) {
                table.removeClassName('selected');
            }
        })
        subsection.toggleClassName('selected');
        if(!clickedOnInput) {
            sectionInput.checked = subsection.hasClassName('selected');
        }
        containerInput.value = sectionInput.checked ? sectionInput.value : '';
    },

    showNextForm: function(e) {
        var input = e.element();
        if(input.tagName.toLowerCase() != 'input') {
            input = e.element().up('tr').down('input.show-hide-next');
        }
        var target = e.element().up('.table-border').down('.toggle-target');

        if(!input.checked) {
            target.show();
            target.down('input').focus();
        }
        else {
            target.hide();
        }
    },

    doAction: function(e) {
        var el = e.findElement('a');
        var action = el.readAttribute('rel');

        var url = null;
        var title = null;
        var params = {jsHandler: 'Order', forward: 'comanda/livrare'};
        var winDimensions = {};
        var className = 'bluelighting';
        switch(action) {
            case 'add-address': {
                url = '/administrare-cont/adauga-adresa';
                title = 'Adaug\u0103 adres\u0103';
                winDimensions = {width: 550, height: 200};
            }
            break;
            case 'add-company': {
                url = '/administrare-cont/adauga-companie';
                title = 'Adaug\u0103 companie';
                winDimensions = {width: 650, height: 380};
            }
            break;
            case 'preview': {
                url = '/comanda/preview';
                title = 'Comanda MyShop';
                winDimensions = {width: 750, height: 525};
                params.paymentMethod = this.form.serialize(true).plata;
                className = 'alphacube';
            }
            break;
        }

        new Ajax.Request(url, {
            method: 'get',
            parameters: params,
            onSuccess: function(tr) {
                this.showWindow(tr.responseText, {
                    width: winDimensions.width,
                    height: winDimensions.height,
                    title: title,
                    className: className
                });
                this._resetFormValidator(true);
            }.bind(this)
        });
    },

    submitObserver: function(e) {
        e.stop();
        if(!this.validator.validate()) {
            return;
        }

        var params = this.getActiveForm().serialize(true);
        new Ajax.Request(this.getActiveForm().readAttribute('action'), {
            method: 'get',
            parameters: Object.toQueryString(params),
            onSuccess: function(tr) {
                this.content.down('table.selectable-sub-section').down('tbody').update(tr.responseText);
                if(this.dialogWindow) {
                    this.dialogWindow.close();
                }
            }.bind(this)
        });
    },

    showWindow: function(content, options) {
        options = Object.extend({
            className: 'bluelighting',
            maximizable: false,
            minimizable: false,
            resizable: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            destroyOnClose: true,
            width: 400,
            height: 300
        }, options || {});

        this.dialogWindow = new Window(options);
        this.dialogWindow.setHTMLContent(content);
        this.dialogWindow.showCenter(true);
        this.dialogWindow.setCloseCallback(function() {
            this.deattachFormObserver();
            delete this.dialogWindow;
            this._resetFormValidator();
            return true;
        }.bind(this));

        return this.dialogWindow;
    }
};

document.observe('dom:loaded', Order.init.bind(Order));