<?php
/**
 * Cauta Controller
 *
 * @author Rares Vlasceanu
 */
class CautaController extends Zend_Controller_Action
{
    /**
     * Default action
     */
    public function indexAction()
    {
        $solr = new MyShop_Solr();
    }
}