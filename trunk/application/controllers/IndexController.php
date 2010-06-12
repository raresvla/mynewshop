<?php
/**
 * Index Controller
 *
 * @author Rares Vlasceanu
 */
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->assign('section', $this->_getParam('controller'));
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_helper->Seo->addBreadcrumb('Home');
        $sql = Doctrine_Query::create();
        $sql->select('p.id, p.denumire, p.pret, c.id, c.denumire, mg.foto as picture');
        $sql->from('Produse p INDEXBY p.id');
        $sql->leftJoin('p.MainPhoto mg WITH mg.main = 1');
        $sql->leftJoin('p.Promotii pm WITH (pm.data_inceput <= DATE(NOW()) AND pm.data_sfarsit >= DATE(NOW()))');
        $sql->leftJoin('p.Categorie c');
        $sql->where('(p.stoc_disponibil + p.stoc_rezervat) > 0 AND p.afisat = 1');

        //new products
        $npSql = clone $sql;
        $npSql->andWhere('p.noutati = 1');
        $npSql->limit(4);
        $npSql->orderBy('p.data_adaugare desc');
        $this->view->assign('newProducts', $npSql->fetchArray());

        //recommendations
        $recomm = clone $sql;
        $recomm->andWhere('p.recomandari = 1');
        $recomm->andWhereNotIn('p.id', array_keys((array) $this->view->newProducts));
        $recommendations = $recomm->fetchArray();
        $this->view->assign('recommendations', array_slice($recommendations, 0, 4));
    }

    /**
     * Example action
     */
    public function exampleAction()
    {

    }

    /**
     * Debug action
     */
    public function dumpAction()
    {
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/html; charset=utf-8', true);
        echo '<pre>';
        print_r($_SESSION);
        print_r($_SERVER);
        print_r($_COOKIE);
        echo '</pre>';
        $response->sendHeaders();
        die();
    }

    public function generateModelsAction()
    {
        $res = Doctrine_Core::generateModelsFromDb('../application/models', array('myshop'), array('generateTableClasses' => true));
        print_r($res);
        die();
    }
}

