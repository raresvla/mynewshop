var Products = {
    init: function() {
        $('content').delegate('click', {
            '.show-hide-next': this.showHideNext.bindAsEventListener(this),
            '.show-hide-next *': this.showHideNext.bindAsEventListener(this),
            'ul.rating a': this.rateProduct.bindAsEventListener(this),
            '.add-to-fav': this.addToFavorites.bindAsEventListener(this),
            '.action': this.performAction.bindAsEventListener(this)
        }).bind(this);
    },

    showHideNext: function(e) {
        var el = e.findElement('a');
        var section = el.next();
        section.toggleClassName('hidden');

        var sectionVisible = !section.hasClassName('hidden');
        if(!sectionVisible) {
            el.addClassName('closed');
        }
        else {
            el.removeClassName('closed');
        }
        setCookie(
            'hideSections[' + el.readAttribute('rel') + ']',
            (sectionVisible ? 0 : 1),
            30,
            '/'
        );
    },

    rateProduct: function(e) {
        var rating = e.findElement('a').readAttribute('rel').split('|');
        new Ajax.Request('/produse/voteaza', {
            method: 'post',
            parameters: {id: rating[0], rate: rating[1]},
            onSuccess: function(tr) {
                var rating = tr.responseText.split('|');
                e.element().up('ul')
                    .removeClassName(this.ratingClass(parseInt(rating[0])))
                    .addClassName(this.ratingClass(parseInt(rating[1])));
            }.bind(this),
            onFailure: function(tr) {
                alert(tr.responseText);
            }
        });
    },

    ratingClass: function(number) {
        var rating = null;
        switch(number) {
            case 1:rating = 'one'; break;
            case 2:rating = 'two'; break;
            case 3:rating = 'three'; break;
            case 4:rating = 'four'; break;
            case 5:rating = 'five'; break;
            default: rating = 'no'; break;
        }

        return rating + 'star';
    },

    addToFavorites: function(e) {
        var link = e.findElement('a');
        new Ajax.Request('/produse/adauga-la-favorite', {
            method: 'post',
            parameters: {id: link.readAttribute('rel')},
            onSuccess: function(tr) {
                link.up().remove();
                var header = $('header').down('.right-container');
                if(tr.headerJSON.basket) {
                    header.removeClassName('no-fav-products');
                }
                if(!header.down('.favorite-products')) {
                    header.insert({
                        bottom: new Element('p', {'className': 'favorite-products clear-fix'}).insert(new Element('a', {
                            'className': 'float-right',
                            'href': '/produse/favorite'
                        }).update('Produse favorite (1)'))
                    });
                }
                else {
                    header.down('.favorite-products').down('span').update(tr.headerJSON.favorites);
                }
            },
            onFailure: function(tr) {
                alert(tr.responseText);
            }
        });
    },

    performAction: function(e) {
        var elem = e.findElement('a');
        var action = elem.readAttribute('rel');
        if(action == 'remove-all' && window.confirm('Confirmaţi ştergerea ?')) {
            window.location.replace('/produse/sterge-favorite');
            return;
        }

        var selected = this.getSelectedItems();
        if(action != 'remove-all' && !selected.length) {
            return;
        }
        new Ajax.Request('/produse/update-favorite', {
            method: 'post',
            parameters: {actionToPerform: action, selected: selected.join(',')},
            onSuccess: function(tr) {
                $('content').update(tr.responseText);
                var header = $('header').down('.right-container');
                if(tr.headerJSON.basket) {
                    header.down('.basket').show().down('span').update(tr.headerJSON.basket);
                }
                if(!tr.headerJSON.favorites) {
                    header.addClassName('no-fav-products').down('.favorite-products').remove();
                }
                else {
                    header.down('.favorite-products').down('span').update(tr.headerJSON.favorites);
                }
            }
        });
    },

    getSelectedItems: function() {
        var selected = [];
        $('favoriteProducts').down('tbody').select('tr.hover').each(function(row) {
            var input = row.down('input');
            if(input.checked) {
                selected.push(input.value);
            }
        });

        return selected;
    }
};
document.observe('dom:loaded', Products.init.bind(Products));