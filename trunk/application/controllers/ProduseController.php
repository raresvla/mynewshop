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
            $cPath = $this->_getParam('cPath');
            $categoryId = array_pop($cPath);
        }
        else {
            $categoryId = $this->_getParam('categoryId');
            $cPath = Doctrine::getTable('Categorii')->getCategoryPath($categoryId);
            print_r($cPath); die('here');
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
        $this->view->assign('search', true);

        $solr = new MyShop_Solr();
        $solr->where($this->_getParam('q'));
        
        $this->_setParam('source', $solr);
        $this->_setParam('fromAction', 'cauta');
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
        $this->_setParam('fromAction', 'listeaza-produse');
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
        
        $requestUrl = str_replace('pagina-' . $this->_getParam('pagina'), 'pagina-%d', $_SERVER['REQUEST_URI']);
        $paginator->setRequestUrl($requestUrl, true);

        $parser = function($results) {
            foreach($results as &$item) {
                foreach($item['caracteristici'] as $key => &$car) {
                    $data = explode('#', $car);
                    if($data[0] != 1) {
                        unset($item['caracteristici'][$key]);
                        continue;
                    }
                    $car = array_combine(
                        array('preview', 'name', 'value'),
                        $data
                    );
                }
            }
            return $results;
        };
        $paginator->setResultsParser($parser);
        $paginator->postDispatch();

        $this->render($this->_getParam('fromAction'));
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