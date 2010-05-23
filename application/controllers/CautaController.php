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
        //$solr->deleteByQuery('*:*');
        //$solr->commit();
        $solr->index(1);
        $solr->index(2);
        $solr->index(3);
    }
}