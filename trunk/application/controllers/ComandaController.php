<?php
/**
 * Comanda Controller
 *
 * @author Rares Vlasceanu
 */
class ComandaController extends Zend_Controller_Action
{
    /**
     * Do not allow non-members access
     */
    public function init()
    {
        $this->_helper->Acl->allow(MyShop_Helper_Acl::ROLE_MEMBER);
        
        $invoice = MyShop_Invoice::getInstance(MyShop_Basket::getInstance());
        $this->view->assign('invoice', $invoice);
    }

    /**
     * Add link to basket
     */
    public function postDispatch()
    {
        if($this->getRequest()->isDispatched()) {
            $this->_helper->Layout->addBreadCrumb(
                'Coş de cumpărături',
                '/cos-cumparaturi',
                'unshift'
            );
        }
    }

    /**
     * Shortcut to first step
     */
    public function indexAction()
    {
        $this->_forward('mod-facturare');
    }

    /**
     * First order processing step
     */
    public function modFacturareAction()
    {
        $this->_helper->Layout->addBreadCrumb('Mod de facturare');
        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->_helper->Layout->includeJs('order.js');

        $user = Doctrine::getTable('Membri')->find($_SESSION['profile']['id']);
        $this->view->assign('companiesCount', sizeof($user->Companii));
    }

    /**
     * Set bill type and redirect to next step
     */
    public function setModFacturareAction()
    {
        if(!$this->_hasParam('type')) {
            $this->_forward('mod-facturare');
            return;
        }

        $this->view->invoice->tip = $this->_getParam('type');
        $this->_redirect('/comanda/livrare');
    }

    /**
     * Second order processing step
     */
    public function livrareAction()
    {
        $this->_helper->Layout->addBreadCrumb('Mod de facturare', '/comanda/mod-facturare');
        $this->_helper->Layout->addBreadCrumb('Modalitate de livrare');
        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->_helper->Layout->includeJs('order.js');
        $this->_helper->Layout->includeCss('login-register.css');

        //window
        $this->_helper->Layout->includeJs('lib/window/window.js');
        $this->_helper->Layout->includeCss('window/default.css');
        $this->_helper->Layout->includeCss('window/lighting.css');

        $this->view->assign('data', Doctrine::getTable('Membri')->find($_SESSION['profile']['id']));
        $this->view->assign('regions', Doctrine::getTable('Judete')->fetchAll());

        if($this->getRequest()->isXmlHttpRequest()) {
            $template = ($this->view->invoice->type == 'fizica' ? 'livrare-lista-adrese.html' : 'livrare-lista-comanii.html');
            $this->getFrontController()->setParam('noViewRenderer', true);
            $this->getResponse()->setBody($this->view->render("comanda/{$template}"));
        }
    }

    /**
     * Set shipping details and redirect to last step
     */
    public function setLivrareAction()
    {
        $this->view->invoice->cumparator = $this->_getParam('cumparator');
        $this->view->invoice->destinatar = $this->_getParam('destinatar');

        if(!$this->view->invoice->isValid()) {
            $message = null;
            $msgTpl = "'%s' (%s)";
            foreach($this->view->invoice->invalidFields as $section => $fields) {
                $message .= ($message ? ' şi ' : '') . sprintf($msgTpl, implode("', '", $fields), ucfirst($section));
            }
            $message = "Valorile introduse / selectate pentru {$message} nu sunt valide!";
            $this->view->assign('error', $message);
            $this->_forward('livrare');
            return;
        }
        
        $this->_redirect('/comanda/confirma');
    }

    /**
     * Third order processing step
     */
    public function confirmaAction()
    {
        $this->_helper->Layout->addBreadCrumb('Mod de facturare', '/comanda/mod-facturare');
        $this->_helper->Layout->addBreadCrumb('Modalitate de livrare', "/comanda/livrare");
        $this->_helper->Layout->addBreadCrumb('Confirmă comanda');
        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->_helper->Layout->includeJs('order.js');

        //window
        $this->_helper->Layout->includeJs('lib/window/window.js');
        $this->_helper->Layout->includeCss('window/default.css');
        $this->_helper->Layout->includeCss('window/alphacube.css');
    }

    /**
     * Preview order
     */
    public function previewAction()
    {
        $sql = Doctrine_Query::create();
        $sql->select('MAX(id) + 1 as nextId');
        $sql->from('Comenzi');
        $data = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

        $buyer = $this->view->invoice->cumparator;
        $buyer['address'] = $this->view->invoice->getBillingAddress();
        $receiver = $this->view->invoice->destinatar;
        $receiver['address'] = $this->view->invoice->getShippingAddress();
        $order = array(
            'code' => "#{$data['nextId']}-" . date('dm') . (date('Y') - 2000),
            'date' => date('d-m-Y\, H:i:s'),
            'buyer' => $buyer,
            'receiver' => $receiver,
            'type' => $this->view->invoice->tip,
            'products' => $this->view->basket,
            'totalValue' => $this->view->basket->total(),
            'shippingCost' => $this->view->invoice->getShippingCost()
        );

        $this->view->assign('cfg', $this->view->basket->config);
        $this->view->assign('order', $order);
    }
}