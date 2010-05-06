var Products = {
    init: function() {
        $('content').delegate('click', {
            '.show-hide-next': this.showHideNext,
            '.show-hide-next *': this.showHideNext
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
    }
};
document.observe('dom:loaded', Products.init.bind(Products));