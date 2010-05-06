<?php
/**
 * Strip chars from links
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage SmartyPlugins
 *
 * @param string $string
 * @return string
 */
function smarty_modifier_url_escape($string)
{
    return (string) MyShop_Util_String::getInstance($string)->escapeUrl();
}

