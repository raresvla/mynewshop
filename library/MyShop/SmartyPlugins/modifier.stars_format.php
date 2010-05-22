<?php
/**
 * Return rating css class name corresponding to product's rating
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage SmartyPlugins
 *
 * @param integer $number
 * @return string
 */
function smarty_modifier_stars_format($number)
{
    switch($number) {
        case 1: {
            return 'one';
        }
        case 2: {
            return 'two';
        }
        case 3: {
            return 'three';
        }
        case 4: {
            return 'four';
        }
        case 5: {
            return 'five';
        }
    }
}