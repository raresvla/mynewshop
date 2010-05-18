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
        $produs = Doctrine::getTable('Produse')->find(1);
        $data = array(
            'id' => $produs->id,
            'producator' => $produs->marca,
            'codProdus' => $produs->cod_produs,
            'denumire' => $produs->denumire,
            'categorie' => $produs->Categorii->denumire,
            'caracteristici' => array('rosu', 'negru'),
            'greutate' => $produs->greutate,
            'pret' => $produs->pret,
            'popularitate' => '11',
            'inStoc' => true
        );

        $solr = new MyShop_Solr();
        //$solr->deleteByQuery('note*');
        $solr->index(1);
        $solr->index(2);
        $solr->index(3);
    }
}