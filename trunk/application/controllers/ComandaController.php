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
        if($this->getRequest()->isDispatched() && $this->_hasParam('add-basket-breadcrumb')) {
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
        $this->_setParam('add-basket-breadcrumb', true);
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

        //window
        $this->_helper->Layout->includeJs('lib/window/window.js');
        $this->_helper->Layout->includeCss('window/default.css');
        $this->_helper->Layout->includeCss('window/lighting.css');

        $this->view->assign('data', Doctrine::getTable('Membri')->find($_SESSION['profile']['id']));
        $this->view->assign('regions', Doctrine::getTable('Judete')->fetchAll());

        if($this->getRequest()->isXmlHttpRequest()) {
            $template = ($this->view->invoice->tip == 'fizica' ? 'livrare-lista-adrese.html' : 'livrare-lista-companii.html');
            $this->getFrontController()->setParam('noViewRenderer', true);
            $this->getResponse()->setBody($this->view->render("comanda/{$template}"));
        }
        $this->_setParam('add-basket-breadcrumb', true);
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
        $this->_setParam('add-basket-breadcrumb', true);
    }

    /**
     * Preview order
     */
    public function previewAction()
    {
        $order = $this->view->invoice->fetchAllData();
        $order['paymentMethod'] = $this->_getParam('paymentMethod');

        $this->view->assign('cfg', $this->view->basket->config);
        $this->view->assign('preview', true);
        $this->view->assign('order', $order);
    }

    /**
     * Save order in database
     */
    public function trimiteAction()
    {
        $this->view->invoice->modalitatePlata = $this->_getParam('plata');

        try {
            $this->view->invoice->save();
            $this->view->assign('cfg', $this->view->basket->config);
            $this->view->assign('order', $this->view->invoice->fetchAllData());
            $this->view->assign('token', MyShop_Invoice::generateConfirmationToken($this->view->invoice->orderId));

            if($this->view->invoice->tip == MyShop_Invoice::INVOICE_TYPE_PERSONAL) {
                $buyerEmail = $this->view->invoice->cumparator['email'];
                $buyerName = $this->view->invoice->cumparator['nume'] . ' ' . $this->view->invoice->cumparator['prenume'];
            }
            else {
                $buyerEmail = $this->view->invoice->cumparator['email_sediu'];
                $buyerName = $this->view->invoice->cumparator['denumire'];
            }
            $email = new Zend_Mail();
            $email->addTo($buyerEmail, $buyerName);
            $email->addTo('rares@net.ase.ro', $buyerName);
            $email->setFrom($this->view->basket->config->ADRESA_EMAIL_CORESPONDENTA); //, 'MyShop'
            $email->setReplyTo($this->view->basket->config->ADRESA_EMAIL_CORESPONDENTA); //, 'MyShop'
            //$email->addBcc($this->view->basket->config->ADRESA_EMAIL_CORESPONDENTA);
            $email->setSubject('Comanda MyShop');
            $email->setBodyHtml($this->view->render('comanda/preview.html'), 'utf-8', Zend_Mime::ENCODING_8BIT);
            $email->setBodyText('This email can be viewed only in HTML format.');
            $email->setType(Zend_Mime::MULTIPART_RELATED);

            //add image attachment
            $attach = new Zend_Mime_Part(file_get_contents('img/icons/info-medium.png'));
            $attach->type = 'image/png';
            $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
            $attach->encoding = Zend_Mime::ENCODING_BASE64;
            $attach->filename = 'info.png';
            $attach->id = 'infoImage';

            $email->addAttachment($attach);
            $email->send(new Zend_Mail_Transport_Smtp());

            $this->view->invoice->clean();
        }
        catch(Exception $e) {
            $this->view->assign('error', 'A apărut o eroare la salvarea comenzii dvs. Vă rugăm reîncercaţi. (' . $e->getMessage() . ')');
            $this->_forward('confirma');
            return;
        }

        $this->_redirect('/comanda/trimisa');
    }

    /**
     * Display order sent confimation page
     */
    public function trimisaAction()
    {
        $this->_helper->Layout->addBreadCrumb('Trimite comanda');
    }

    /**
     * Confirm products reservation
     */
    public function confirmaRezervareaAction()
    {
        $req = $this->_getParam('req');
        if(($orderId = MyShop_Invoice::checkConfirmationToken($req)) === false) {
            $this->_redirect('/comanda/eroare-confirmare');
            return;
        }

        $order = Doctrine::getTable('Comenzi')->find($orderId);
        $order->status = 'confirmata';
        $this->_redirect('/comanda/confirmata/req/' . base64_encode($order->Membri->telefon));
    }

    /**
     * Display order sent confimation page
     */
    public function confirmataAction()
    {
        $this->_helper->Layout->addBreadCrumb('Confirmă comanda');
        if(($req = $this->_getParam('req'))) {
            $this->view->assign('telNumber', base64_decode($req));
        }
        $this->view->assign('cfg', MyShop_Config::getInstance());
    }

    /**
     * Display confirmation error page
     */
    public function eroareConfirmareAction()
    {
        $this->_helper->Layout->addBreadCrumb('Confirmă comanda');
        $this->view->assign('cfg', MyShop_Config::getInstance());
    }
}