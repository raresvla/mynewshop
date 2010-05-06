document.observe('dom:loaded', function() {
    new Validation('registerFrom', {
        useTitles: true,
        focusOnError: true
    });

    $('verify-username').observe('click', function() {
        new Ajax.Request('/cont/verifica-username', {
            method: 'get',
            parameters: 'username=' + $('username').value,
            onSuccess: function(transport) {
                var response = transport.responseText.evalJSON();
                var msgContainer = $('username').next('.validation-result').update(response.message);

                if(!response.code) {
                    msgContainer.removeClassName('validation-success').addClassName('validation-error');
                }
                else {
                    msgContainer.removeClassName('validation-error').addClassName('validation-success');
                }
                msgContainer.show();
            }
        });
    });
});