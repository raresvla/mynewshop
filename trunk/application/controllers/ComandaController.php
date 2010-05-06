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
        $invoice = MyShop_Invoice::getInstance(MyShop_Basket::getInstance());

        $this->_helper->Layout->addBreadCrumb('Mod de facturare');
        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->_helper->Layout->includeJs('order.js');

        $user = Doctrine::getTable('Membri')->find($_SESSION['profile']['id']);
        $this->view->assign('companiesCount', sizeof($user->Companii));
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

        $billType = $this->_getParam('type');
        $this->view->assign('billType', $billType);
        $this->view->assign('data', Doctrine::getTable('Membri')->find($_SESSION['profile']['id']));
        $this->view->assign('regions', Doctrine::getTable('Judete')->fetchAll());

        if($this->getRequest()->isXmlHttpRequest()) {
            $template = ($billType == 'fizica' ? 'livrare-lista-adrese.html' : 'livrare-lista-comanii.html');
            $this->getFrontController()->setParam('noViewRenderer', true);
            $this->getResponse()->setBody($this->view->render("comanda/{$template}"));
        }
    }

    /**
     * Third order processing step
     */
    public function confirmaAction()
    {
        $billType = $this->_getParam('billType');
        $this->_helper->Layout->addBreadCrumb('Mod de facturare', '/comanda/mod-facturare');
        $this->_helper->Layout->addBreadCrumb('Modalitate de livrare', "comanda/livrare?type={$billType}");
        $this->_helper->Layout->addBreadCrumb('Confirmă');

        $this->view->assign('billType', $billType);
    }
}