var Account = {
    tabs: [],
    currentTab: null,
    loading: false,
    content: null,
    dialogWindow: null,
    form: null,
    validator: null,

    init: function() {
        var tabs = $('sectionsTabs');
        tabs.delegate('click', {
            'a': this.changeSection.bindAsEventListener(this)
        });
        this.tabs = tabs.childElements();
        this.currentTab = this.tabs.find(function(tab) {
            if(tab.down('a').hasClassName('active')) {
                return true;
            }
            return false;
        });
        this.content = $('sectionContent');
        this.content.delegate('click', {
            'table.table-border *': this.selectSubSection.bindAsEventListener(this),
            'a.action': this.performAction.bindAsEventListener(this)
        });
        this.submitSectionDataBinded = this.submitSectionData.bindAsEventListener(this);
        this.attachFormObserver();
        this.setFormValidator();
    },

    changeSection: function(e) {
        var linkClicked = e.element();
        if(linkClicked.hasClassName('active')) {
            return;
        }
        if(this.loading) {
            return;
        }

        this.loading = true;
        new Ajax.Request(linkClicked.readAttribute('rel'), {
            method: 'get',
            onSuccess: function(tr) {
                this.loading = false;
                this.deattachFormObserver();

                //set tabs
                this.currentTab.down().removeClassName('active');
                this.currentTab = linkClicked.up();
                this.currentTab.down().addClassName('active');

                //set new content
                this.content.update(tr.responseText)
                this._resetFormObservers();
            }.bind(this),
            onFailure: function() {
                this.loading = false;
            }.bind(this)
        });
    },

    _resetFormObservers: function() {
        this.form = null;
        this.attachFormObserver();
        this.setFormValidator();
    },

    getSectionForm: function() {
        if(this.form === null) {
            this.form = this.content.down('form');
            if(this.form && this.form.hasClassName('do-not-attach-observers')) {
                this.form = null;
            }
            if(!this.form && this.dialogWindow) {
                this.form = this.dialogWindow.getContent().down('form');
            }
        }
        if(!this.form) {
            return false;
        }
        return this.form;
    },

    attachFormObserver: function() {
        if(!this.getSectionForm()) {
            return;
        }
        this.getSectionForm().observe('submit', this.submitSectionDataBinded).focusFirstElement();
    },

    deattachFormObserver: function() {
        if(!this.getSectionForm()) {
            return;
        }
        this.getSectionForm().stopObserving('submit', this.submitSectionDataBinded);
    },

    setFormValidator: function() {
        if(!this.getSectionForm()) {
            return;
        }
        this.validator = new Validation(this.getSectionForm().identify(), {
            useTitles: true,
            focusOnError: true,
            onSubmit: false
        });
    },

    submitSectionData: function(e) {
        e.stop();
        if($('lastMessage')) {
            $('lastMessage').remove();
        }
        if(!this.validator.validate()) {
            return;
        }

        new Ajax.Request(this.getSectionForm().readAttribute('action'), {
            method: 'get',
            parameters: Object.toQueryString(this.getSectionForm().serialize(true)),
            onSuccess: function(tr) {
                try {
                    var response = tr.responseText.evalJSON();
                    if(response.message) {
                        var message = new Element('div', {
                            'class': (response.code ? 'done' : 'error'),
                            'id': 'lastMessage'
                        }).update(response.message);
                        this.content.insert({
                            top: message
                        });
                        Nifty("div#lastMessage");
                    }
                    if(response.update) {
                        this.content.update(response.update);
                    }
                }
                catch(e) {
                    this.content.update(tr.responseText);
                }
                if(this.dialogWindow) {
                    this.dialogWindow.close();
                }
            }.bind(this)
        });
    },

    selectSubSection: function(e) {
        var subsection = e.findElement('table');
        if(subsection.hasClassName('do-not-attach-observers')) {
            return;
        }
        var clickedOnInput = (e.element().tagName.toLowerCase() == 'input');

        if(clickedOnInput && subsection.hasClassName('selected')) {
            e.element().checked = false;
            subsection.removeClassName('selected');
            return;
        }
        this.content.select('table').each(function(table) {
            if(table != subsection) {
                table.removeClassName('selected');
            }
        })
        subsection.toggleClassName('selected');
        if(!clickedOnInput) {
            subsection.down('input').checked = subsection.hasClassName('selected');
        }        
    },

    getSelectedSectionValue: function() {
        var table = this.content.select('table').find(function(table) {
            if(table.hasClassName('do-not-attach-observers')) {
                return false;
            }
            return table.down('input').checked;
        });
        
        if(!table) {
            return false;
        }
        return table.down('input').value;
    },

    performAction: function(e) {
        var el = e.element();
        var action = el.readAttribute('rel');
        var sectionValue = this.getSelectedSectionValue();
        if(!sectionValue) {
            action = action.split('|');
            sectionValue = action[1];
            action = action[0];
        }

        var url = null;
        var params = {};
        var title = null;
        switch(action) {
            case 'add-address':
            case 'edit-address':
            {
                if(action == 'edit-address') {
                    url = '/administrare-cont/editeaza-adresa';
                    if(!sectionValue) {
                        alert('Selecta\u0163i mai întâi una dintre opţiuni!');
                        return;
                    }
                    params.id = sectionValue;
                    title = 'Editeaz\u0103 adresa';
                }
                else {
                    url = '/administrare-cont/adauga-adresa';
                    title = 'Adaug\u0103 adres\u0103';
                }

                new Ajax.Request(url, {
                    method: 'get',
                    parameters: params,
                    onSuccess: function(tr) {
                        this.showWindow(tr.responseText, {
                            width: 550,
                            height: 200,
                            title: title
                        });
                        this._resetFormObservers();
                    }.bind(this)
                });
            }
            break;

            case 'delete-company':
            case 'delete-address':
            {
                if(!sectionValue) {
                    alert('Selecta\u0163i mai întâi una dintre opţiuni!');
                    return;
                }
                if(!window.confirm('Confirma\u0163i ştergerea ' +
                    (action == 'delete-address' ? 'adresei' : 'companiei') + ' ?')) {
                    return;
                }

                if(action == 'delete-address') {
                    url = '/administrare-cont/sterge-adresa';
                }
                else {
                    url = '/administrare-cont/sterge-companie';
                }
                new Ajax.Request(url, {
                    method: 'get',
                    parameters: {id: sectionValue},
                    onSuccess: function(tr) {
                        this.content.update(tr.responseText);
                    }.bind(this)
                });
            }
            break;

            case 'add-company':
            case 'edit-company':
            {
                if(action == 'edit-company') {
                    url = '/administrare-cont/editeaza-companie';
                    if(!sectionValue) {
                        alert('Selecta\u0163i mai întâi una dintre opţiuni!');
                        return;
                    }
                    params.id = sectionValue;
                    title = 'Editare detalii companie';
                }
                else {
                    url = '/administrare-cont/adauga-companie';
                    title = 'Adaug\u0103 companie';
                }

                new Ajax.Request(url, {
                    method: 'get',
                    parameters: params,
                    onSuccess: function(tr) {
                        this.showWindow(tr.responseText, {
                            width: 650,
                            height: 380,
                            title: title
                        });
                        this._resetFormObservers();
                    }.bind(this)
                });
            }
            break;

            case 'order-details': {
                new Ajax.Request('/administrare-cont/vezi-comanda', {
                    method: 'get',
                    parameters: {id: sectionValue},
                    onSuccess: function(tr) {
                        this.showWindow(tr.responseText, {
                            width: 750,
                            height: 525,
                            title: 'Comanda MyShop',
                            className: 'alphacube'
                        });
                    }.bind(this)
                });
            } break;
        }
    },

    showWindow: function(content, options) {
        options = Object.extend({
            className: 'bluelighting',
            maximizable: false,
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
            delete this.validator;
            return true;
        }.bind(this));

        return this.dialogWindow;
    }
};

document.observe('dom:loaded', Account.init.bind(Account));