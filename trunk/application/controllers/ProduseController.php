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
            $this->view->assign('topCategoryId', array_shift($cPath));
            $this->view->assign('categoryId', (empty($cPath) ? $this->_getParam('topCategoryId') : array_pop($cPath)));
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

        $cPath = $this->_getParam('cPath');
        $categoryId = array_pop($cPath);
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

        $sql = Doctrine_Query::create();
        $sql->select('p.*, c.*');
        $sql->addSelect('g.foto, pm.pret_oferta, pc.*, car.*');
        $sql->from('Produse p');
        $sql->leftJoin('p.MainPhoto g WITH g.main = 1');
        $sql->leftJoin('p.Promotii pm WITH (pm.data_inceput <= DATE(NOW()) AND pm.data_sfarsit >= DATE(NOW()))');
        $sql->leftJoin('p.ProduseCaracteristici pc');
        $sql->leftJoin('p.Categorie c');
        $sql->innerJoin('pc.Caracteristici car WITH car.preview = 1');
        $sql->where('categorie_id = ? AND (p.stoc_disponibil + p.stoc_rezervat) > 0 AND p.afisat = 1', $categoryId);

        //init paginator
        $paginator = $this->_helper->Paginator($sql);
        $paginator->setTemplate('produse/listeaza-produse-pag.html');
        $requestUrl = str_replace('pagina-' . $this->_getParam('pagina'), 'pagina-%d', $_SERVER['REQUEST_URI']);
        $paginator->setRequestUrl($requestUrl, true);

        $this->view->assign('cPath', implode('_', $this->_getParam('cPath')));
        $this->view->assign('category', $category);
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
        
        $this->view->assign('product', $product);
        $this->_helper->Layout->addBreadcrumb($product['denumire']);
        $this->_helper->Layout->includeJs('lib/scriptaculous/builder.js');
        $this->_helper->Layout->includeJs('lib/lightbox/lightbox.js');
        $this->_helper->Layout->includeCss('lightbox/lightbox.css');
        $this->_helper->Layout->includeCss('rating.css');
    }
}