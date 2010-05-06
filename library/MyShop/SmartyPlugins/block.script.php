<?php
function smarty_block_script($params, $text, &$smarty)
{
    if(empty($text)) {
        return;
    }

    $layout = Zend_Controller_Action_HelperBroker::getExistingHelper('Layout');
    $layout->injectJs("$text");
    return "";
}
