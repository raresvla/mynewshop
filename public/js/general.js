var Menu = Class.create({
    elementId: null,
    activeItem: null,

    initialize: function(elementId) {
        this.elementId = $(elementId);
        this.elementId.delegate('click', {
            'li a.has-subcategories': this.menuClicked.bindAsEventListener(this)
        });
        Nifty('#' + this.elementId.previous().identify(), 'top');
    },

    menuClicked: function(e) {
        var clickedItem = e.element().up('li');
        if(clickedItem.hasClassName('active')) {
            return;
        }
        e.stop();
        if(this.activeItem) {
            new Effect.BlindUp(this.activeItem.down('ul.submenu'), {duration: 0.3});
            if(this.activeItem == clickedItem) {
                this.activeItem = null;
                return;
            }
        }
        this.activeItem = clickedItem;
        new Effect.BlindDown(this.activeItem.down('ul.submenu'), {duration: 0.3});
    }
});

var TableHightlight = Class.create({
    tableId: null,
    tableBody: null,
    activeRows: [],
    lastActiveRow: null,

    initialize: function(tableId) {
        this.tableId = $(tableId);
        this.tableBody = this.tableId.down('tbody');
        this.tableBody.observe('mouseover', this.mouseOverObserver.bindAsEventListener(this));
        $('wrap').observe('mouseover', this.mouseOutObserver.bindAsEventListener(this));
    },

    mouseOverObserver: function(e) {
        var row = e.findElement('tr.hover');
        if(row == this.lastActiveRow) {
            return;
        }

        this.activeRows.each(function(activeRow) {
            var input = activeRow.down('input.highlight-marker');
            if(!input || !input.checked) {
                activeRow.removeClassName('active');
                this.activeRows = this.activeRows.without(activeRow);
            }
        }.bind(this));

        var exists = this.activeRows.find(function(r) {
            if(r == row) {
                return true;
            }
            return false;
        })
        if(exists) {
            this.lastActiveRow = null;
            return;
        }
        
        this.activeRows.push(row.addClassName('active'));
        this.lastActiveRow = row;
    },

    mouseOutObserver: function(e) {
        if(e.element().descendantOf(this.tableBody)) {
            return;
        }
        if(!this.lastActiveRow) {
            return;
        }
        var input = this.lastActiveRow.down('input.highlight-marker');
        if(!input || !input.checked) {
            this.lastActiveRow.removeClassName('active');
            this.lastActiveRow = null;
        }
    }
});

Ajax.Responders.register({
    onCreate: function() {
        $('loadingAjax').show();
    },
    onComplete: function() {
        $('loadingAjax').hide();
    },
    onException: function(tr, ex) {
        if(window.console) {
            console.log(ex);
        }
    }
});

function setCookie(name, value, days, path) {
    if(Object.isUndefined('path')) {
        throw('Path parameter is required!');
    }
    var expires = "";
    if(days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        expires = "; expires="+date.toGMTString();
    }
    document.cookie = name + "=" + value + expires + "; path=" + path;
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1, c.length);
        }
        if (c.indexOf(nameEQ) == 0) {
            return c.substring(nameEQ.length, c.length);
        }
    }

    return null;
}

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

document.observe('dom:loaded', function() {
    Nifty("div.niftyable");
    new Menu('site-menu');

    $$('table.highlight-row').each(function(table) {
        new TableHightlight(table);
    });
});