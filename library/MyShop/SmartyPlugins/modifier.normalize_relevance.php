<?php
/**
 * Normalize item score with max score
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage SmartyPlugins
 *
 * @param float $score
 * @param float $maxScore
 * @return string
 */
function smarty_modifier_normalize_relevance($score, $maxScore)
{
    $result = ($score / $maxScore) * 100;

    $modifier = 1;
    while(($maxScore * 10) < 1) {
        $modifier *= 2;
        $maxScore *= 10;
    }
    $result /= $modifier;

    $relevance = 25;
    if($result > 75) {
        $relevance = 100;
    }
    elseif($result > 50) {
        $relevance = 75;
    }
    elseif($result > 25) {
        $relevance = 50;
    }
    elseif($result > 12) {
        $relevance = 25;
    }

    return $relevance;
}