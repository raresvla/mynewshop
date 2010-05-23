<?php
/**
 * Produse Controller
 *
 * @author Rares Vlasceanu
 */
class ProduseController extends Zend_Controller_Action
{
    /**
     * Parse params
     */
    public function init()
    {
        if($this->_hasParam('cPath')) {
            $cPath = $this->_getParam('cPath');
            if(!is_array($cPath)) {
                $cPath = (array) explode('_', $cPath);
                $this->_setParam('cPath', $cPath);
            }
        }
        $this->_helper->Layout->includeCss('products.css');
        $this->_helper->Layout->includeJs('products.js');
    }

    /**
     * Set breadcrumbs
     */
    public function postDispatch()
    {
        if(!$this->getRequest()->isDispatched()) {
            return;
        }

        if(!$this->_hasParam('categoryId')) {
            if(!$this->_hasParam('cPath')) {
                return;
            }
            $cPath = $this->_getParam('cPath');
            $categoryId = array_pop($cPath);
        }
        else {
            $categoryId = $this->_getParam('categoryId');
            $cPath = Doctrine::getTable('Categorii')->getCategoryPath($categoryId);
            foreach($cPath as &$item) {
                $item = $item['id'];
            }
            $cPath[] = $categoryId;
            $this->_setParam('cPath', $cPath);
        }
        $breadCrumbs = Doctrine::getTable('Categorii')->getBreadCrumbsTo(
            $categoryId,
            $this->_getParam('cPath')
        );

        //unshift categories breadcrumbs
        end($breadCrumbs);
        $this->_helper->Layout->addBreadcrumb(
            current($breadCrumbs),
            key($breadCrumbs),
            'unshift'
        );
        $this->view->assign('lastCategory', array(
            'title' => current($breadCrumbs),
            'href' => key($breadCrumbs)
            ));
        while(prev($breadCrumbs)) {
            $this->_helper->Layout->addBreadcrumb(
                current($breadCrumbs),
                key($breadCrumbs),
                'unshift'
            );
        }

        $cPath = $this->_getParam('cPath');
        $this->view->assign('topCategoryId', array_shift($cPath));
        $this->view->assign('categoryId', (empty($cPath) ? $this->_getParam('topCategoryId') : array_pop($cPath)));
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_forward('categorie');
    }

    /**
     * Show subcategories / products in category
     */
    public function categorieAction()
    {
        $cPath = $this->_getParam('cPath');
        if(empty($cPath) || !is_array($cPath)) {
            throw new Zend_Exception('Invalid products category listing params!');
        }

        $categoryId = array_pop($cPath);
        $category = Doctrine::getTable('Categorii')->find($categoryId);
        if(empty($category)) {
            throw new Zend_Exception('Category with id ' . $categoryId . ' hasn\'t been found!');
        }

        //category has no subcategories
        if(!sizeof($category->Categorii)) {
            $this->_setParam('category', $category);
            $this->_forward('listeaza-produse');
            return;
        }

        $this->view->assign('category', $category->toArray());
    }

    /**
     * Search products
     */
    public function cautaAction()
    {
        $q = trim($this->_getParam('q'));
        if(empty($q)) {
            $this->_redirect('/');
            return;
        }

        //fields boosts
        $searchFields = array(
            'denumire' => 10,
            'categorie' => 5,
            'caracteristici' => 3,
            'producator' => 1
        );
        $query = null;
        foreach($searchFields as $field => $boost) {
            $query .= ($query ? ' ' : null) . "{$field}:{$q}^{$boost}";
        }

        $solr = new MyShop_Solr();
        $solr->where($query);
        $solr->setReturnFields('*,score');
        //print_r($solr->fetchArray()); die();

        $this->view->assign('search', true);
        $this->_setParam('source', $solr);
        $this->_setParam('render', 'cauta');
        $this->_forward('list');
    }

    /**
     * List all products in category
     */
    public function listeazaProduseAction()
    {
        $cPath = $this->_getParam('cPath');
        if(empty($cPath) || !is_array($cPath)) {
            throw new Zend_Exception('Invalid products category listing params!');
        }

        $categoryId = array_pop($cPath);
        $category = $this->_getParam(
            'category',
            Doctrine::getTable('Categorii')->find($categoryId, Doctrine::HYDRATE_ARRAY)
        );
        if(empty($category)) {
            throw new Zend_Exception('Category with id ' . $categoryId . ' hasn\'t been found!');
        }
        $this->view->assign('category', $category);

        //source is Solr search
        $solr = new MyShop_Solr();
        $solr->where('categorieId:' . $categoryId);

        $this->_setParam('source', $solr);
        $this->_setParam('render', 'listeaza-produse');
        $this->_forward('list');
    }

    /**
     * List new products
     */
    public function noiAction()
    {
        $this->_helper->Layout->addBreadcrumb('Produse noi');
        $this->view->assign('title', 'Produse noi');
        $this->view->assign('noProductFound', 'Nu au fost găsite produse.');
        $this->view->assign('section', 'new-products');

        $solr = new MyShop_Solr();
        $solr->where('nou:1');
        $solr->orderBy('dataAdaugarii desc');

        $this->_setParam('source', $solr);
        $this->_setParam('render', 'listeaza-produse');
        $this->_forward('list');
    }

    /**
     * List recommended products
     */
    public function recomandateAction()
    {
        $this->_helper->Layout->addBreadcrumb('Produse recomandate');
        $this->view->assign('title', 'Produse recomandate');
        $this->view->assign('noProductFound', 'Nu au fost găsite produse.');
        $this->view->assign('section', 'recommended-products');

        $solr = new MyShop_Solr();
        $solr->where('recomandat:1');

        $this->_setParam('source', $solr);
        $this->_setParam('render', 'listeaza-produse');
        $this->_forward('list');
    }

    /**
     * List products
     */
    public function listAction()
    {
        $source = $this->_getParam('source');

        try {
            $paginator = $this->_helper->Paginator($source);
            $paginator->setTemplate('produse/listeaza-produse-pag.html');
        }
        catch(Exception $e) {
            $this->_forward('index');
            return;
        }

        if(empty($this->view->search)) {
            $requestUrl = str_replace(
                'pagina-' . $this->_getParam('pagina'),
                'pagina-%d', $_SERVER['REQUEST_URI']
            );
            $paginator->setRequestUrl($requestUrl, true);
        }

        $parser = function($results) {
            foreach($results as &$item) {
                foreach($item['caracteristici'] as $key => &$car) {
                    $car = array_combine(
                        array('name', 'value'),
                        explode('#', $car)
                    );
                }
            }
            return $results;
        };
        $paginator->setResultsParser($parser);
        
        $this->_helper->viewRenderer->setScriptAction($this->_getParam('render'));
        $this->_helper->Layout->includeCss('rating.css');
    }

    /**
     * Product details page
     */
    public function detaliiProdusAction()
    {
        $pid = $this->_getParam('pid');
        if(empty($pid)) {
            throw new Zend_Exception('Product id cannot be empty!');
        }

        $sql = Doctrine_Query::create();
        $sql->select('p.*, c.*, mg.foto, g.foto, pm.pret_oferta, pc.*, car.*, sc.*');
        $sql->addSelect('(p.stoc_disponibil + p.stoc_rezervat) AS stoc_total');
        $sql->from('Produse p');
        $sql->leftJoin('p.GalerieFoto g WITH g.main != 1');
        $sql->leftJoin('p.MainPhoto mg WITH mg.main = 1');
        $sql->leftJoin('p.Promotii pm WITH (pm.data_inceput <= DATE(NOW()) AND pm.data_sfarsit >= DATE(NOW()))');
        $sql->leftJoin('p.ProduseCaracteristici pc');
        $sql->leftJoin('p.Categorie c');
        $sql->leftJoin('pc.Caracteristici car');
        $sql->leftJoin('car.SectiuniCaracteristici sc');
        $sql->orderBy('sc.ordine asc');
        $sql->where('p.id = ?', $pid);
        
        $product = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        if(empty($product)) {
            throw new Zend_Exception('Product not found! (debug: id: ' . $pid . ')');
        }
        Doctrine::getTable('Produse')->organizeDescription($product['ProduseCaracteristici']);
        $this->_setParam('categoryId', $product['categorie_id']);
        
        $this->view->assign('product', $product);
        $this->_helper->Layout->addBreadcrumb($product['denumire']);
        $this->_helper->Layout->includeJs('lib/scriptaculous/builder.js');
        $this->_helper->Layout->includeJs('lib/lightbox/lightbox.js');
        $this->_helper->Layout->includeCss('lightbox/lightbox.css');
        $this->_helper->Layout->includeCss('rating.css');
    }
}