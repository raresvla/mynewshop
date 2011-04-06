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
        for($i=1; $i<=9; $i++) {
            $solr->index($i);
        }
        $solr->commit();
        $solr->optimize();
    }
}