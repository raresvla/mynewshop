//length validator
Validation.add('length6', 'Introduce\u0163i minim 6 caractere.', {
    minLength: 6
});

//username validator
Validation.add('username', 'Numele de utilizator poate con\u0163ine doar caractere alfanumerice sau _', function(value) {
    if(value.strip() < 3) {
        return false;
    }
    return (new RegExp("^[a-zA-Z][a-zA-Z0-9_]+$", "gi")).test(value);
});

//realname validator
Validation.add('realname', 'Numele poate con\u0163ine doar caractere alfabetice sau -', function(value) {
    if(value.strip() < 3) {
        return false;
    }
    return (new RegExp("^[ a-zA-Z\u0103îâşţĂÎÂŞŢ-]+$", "gi")).test(value);
});

//one of options, with message after second element
Validation.add('this-or-previous', 'Selecta\u0163i o opţiune', function(value, elem) {
    var parent = 'li';
    if(elem.next('input[type=hidden]')) {
        parent = elem.next('input[type=hidden]').value;
    }
    return elem.up(parent).select('input').detect(function(el) {
        return el.checked;
    });
});

//value equals with previous element value
Validation.add('eq-previous', 'Cele dou\u0103 valori nu corespund', function(value, elem) {
    var inputs = elem.up('form').select('input[type!=submit]');
    return inputs[inputs.indexOf(elem) - 1].value == value;
});