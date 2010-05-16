<?php
/**
 * AdministrareCont Controller
 *
 * @author Rares Vlasceanu
 */
class AdministrareContController extends Zend_Controller_Action
{
    /**
     * Do not allow access if user is not logged in
     */
    public function init()
    {
        $this->_helper->acl->allow(MyShop_Helper_Acl::ROLE_MEMBER);

        //init frame decorator helper
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->contentFrame('administrare-cont/frame.html');
        }

        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->_helper->Layout->includeJs('lib/window/window.js');
        $this->_helper->Layout->includeJs('account.js');
        $this->_helper->Layout->includeCss('window/default.css');
        $this->_helper->Layout->includeCss('window/lighting.css');
    }

    /**
     * Post dispatch hook
     */
    public function postDispatch()
    {
        if(!$this->getRequest()->isDispatched()) {
            return;
        }
        $this->_helper->Layout->addBreadcrumb('Administrare cont');
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_forward('detalii-personale');
    }

    /**
     * Edit personal details
     */
    public function detaliiPersonaleAction()
    {
        $this->view->assign('selectedTab', 1);
        $user = Doctrine::getTable('Membri')->find($_SESSION['profile']['id']);

        //handle updates
        if($this->_hasParam('data')) {
            $data = $this->_getParam('data');
            $user->synchronizeWithArray($data);
            if(!isset($data['newsletter'])) {
                $user->newsletter = 0;
            }
            if(!$user->validData(true)) {
                $response = array('code' => 0);
                if($user->duplicateField) {
                    $response['message'] = "Exista deja un cont cu '{$user->duplicateField}' egal cu '{$data[$user->duplicateField]}'!";
                }
                else {
                    $response['message'] = "Campul '{$user->notValidField}' este invalid!";
                }
            }
            else {
                $user->save();
                $response = array('code' => 1, 'message' => "Informaţiile au fost salvate cu succes.");
            }
        }
        
        $this->view->assign('data', $user->toArray());
        if($this->getRequest()->isXmlHttpRequest() && isset($response)) {
            $this->_helper->Layout->disable(true);
            $this->getResponse()->setBody(json_encode($response));
        }
    }

    /**
     * Change current password
     */
    public function modificaParolaAction()
    {
        $this->view->assign('selectedTab', 2);

        //handle updates
        if($this->_hasParam('data')) {
            $data = $this->_getParam('data');
            $user = Doctrine::getTable('Membri')->find($_SESSION['profile']['id']);

            $response = array('code' => 0);
            if($data['newPassword'] != $data['rePassword']) {
                $response['message'] = "Cele două parole nu corespund!";
            }
            else {
                if($user->password !== md5($data['password'])) {
                    $response['message'] = "Parola curentă introdusă nu corespunde!";
                }
                else {
                    $user->password = $data['newPassword'];
                    $user->save();

                    MyShop_Plugin_Authentication::setAuthCookie($user->username, md5($data['newPassword']));
                    
                    $response['code'] = 1;
                    $response['message'] = "Noua parolă a fost stabilită cu succes.";
                }
            }
        }

        if($this->getRequest()->isXmlHttpRequest() && isset($response)) {
            $this->_helper->Layout->disable(true);
            $this->getResponse()->setBody(json_encode($response));
        }
    }

    /**
     * Manage user's account associated addresses
     */
    public function managementAdreseAction()
    {
        $this->view->assign('selectedTab', 3);
        
        $user = Doctrine::getTable('Membri')->find($_SESSION['profile']['id']);
        $this->view->assign('addresses', $user->Adrese->toArray());
    }

    /**
     * Edit saved address
     */
    public function editeazaAdresaAction()
    {
        $this->_helper->Layout->disable();
        
        if($this->_hasParam('id')) {
            $id = $this->_getParam('id');
            $address = Doctrine::getTable('Adrese')->find($id, Doctrine::HYDRATE_ARRAY);
            $this->view->assign('data', $address);
        }

        $this->view->assign('jsHandler', $this->_getParam('jsHandler', 'Account'));
        $this->view->assign('forward', $this->_getParam('forward'));
        $this->view->assign('regions', Doctrine::getTable('Judete')->fetchAll());
    }

    /**
     * Add new address
     */
    public function adaugaAdresaAction()
    {
        $this->_forward('editeaza-adresa');
    }

    /**
     * Save new / edited address
     */
    public function salveazaAdresaAction()
    {
        if(!$this->_hasParam('data')) {
            throw new Zend_Exception('Data cannot be empty!');
        }

        $data = $this->_getParam('data');
        if(empty($data['id'])) {
            unset($data['id']);
            $address = new Adrese();
            $address->fromArray($data);
            $address->membru_id = $_SESSION['profile']['id'];
        }
        else {
            $address = Doctrine::getTable('Adrese')->find($data['id']);
            $address->synchronizeWithArray($data);
        }
        $address->save();

        $action = $controller = null;
        $forward = explode('/', $this->_getParam('forward', 'management-adrese'));
        $action = array_pop($forward);
        if($forward) {
            $controller = array_pop($forward);
        }
        $this->_forward($action, $controller);
    }

    /**
     * Delete saved address
     */
    public function stergeAdresaAction()
    {
        if(!$this->_hasParam('id')) {
            throw new Zend_Exception('Invalid address id!');
        }

        $address = Doctrine::getTable('Adrese')->find($this->_getParam('id'));
        $address->delete();

        $this->_forward('management-adrese');
    }

    /**
     * List user's companies
     */
    public function companiiAction()
    {
        $this->view->assign('selectedTab', 4);

        $user = Doctrine::getTable('Membri')->find($_SESSION['profile']['id']);
        $this->view->assign('companies', $user->Companii->toArray());
    }

    /**
     * Edit saved address
     */
    public function editeazaCompanieAction()
    {
        $this->_helper->Layout->disable();

        if($this->_hasParam('id')) {
            $id = $this->_getParam('id');
            $company = Doctrine::getTable('Companii')->find($id, Doctrine::HYDRATE_ARRAY);
            $this->view->assign('data', $company);
        }

        $this->view->assign('forward', $this->_getParam('forward'));
        $this->view->assign('jsHandler', $this->_getParam('jsHandler', 'Account'));
        $this->view->assign('regions', Doctrine::getTable('Judete')->fetchAll());
    }

    /**
     * Add new address
     */
    public function adaugaCompanieAction()
    {
        $this->_forward('editeaza-companie');
    }

    /**
     * Save new / edited company details
     */
    public function salveazaCompanieAction()
    {
        if(!$this->_hasParam('data')) {
            throw new Zend_Exception('Data cannot be empty!');
        }

        $data = $this->_getParam('data');
        if(empty($data['id'])) {
            unset($data['id']);
            $company = new Companii();
            $company->fromArray($data);
            $company->membru_id = $_SESSION['profile']['id'];
        }
        else {
            $company = Doctrine::getTable('Companii')->find($data['id']);
            $company->synchronizeWithArray($data);
        }
        $company->save();

        $action = $controller = null;
        $forward = explode('/', $this->_getParam('forward', 'companii'));
        $action = array_pop($forward);
        if($forward) {
            $controller = array_pop($forward);
        }
        $this->_forward($action, $controller);
    }

    /**
     * Delete saved company
     */
    public function stergeCompanieAction()
    {
        if(!$this->_hasParam('id')) {
            throw new Zend_Exception('Invalid company id!');
        }

        $address = Doctrine::getTable('Companii')->find($this->_getParam('id'));
        $address->delete();

        $this->_forward('companii');
    }
}