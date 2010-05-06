<?php
/**
 * Cont Controller
 *
 * @author Rares Vlasceanu
 */
class ContController extends Zend_Controller_Action
{
    /**
     * Do not allow if user is already logged in
     */
    public function init()
    {
        if($this->_helper->Acl->loggedIn() && $this->_getParam('action') != 'logout') {
            $this->_redirect('/');
        }
    }

    /**
     * Display login form
     */
    public function autentificareAction()
    {
        $this->_helper->Layout->addBreadcrumb('Autentificare', '/cont/autentificare');
        $aclMessage = $this->_helper->Acl->getLastMessage();
        if($aclMessage) {
            $this->view->assign('message', $aclMessage);
        }

        if($this->_getParam('fromRegister')) {
            $message = 'Contul dumneavoastra a fost creat cu succes.';
            $message .= 'Un email a fost trimis pe adresa specificata ce contine parola de acces.';
            $this->view->assign('info', $message);
        }

        $this->_helper->Layout->includeCss('login-register.css');
    }

    /**
     * Process login data
     */
    public function valideazaAutentificareAction()
    {
        $data = $this->_getParam('login');
        $password = $data['password'];
        if(!$this->_hasParam('originalRequest')) {
            $password = md5($password);
        }

        $user = new Membri();
        if(empty($data) || !$user->login($data['username'], $password)) {
            unset($data['password']);
            $this->view->assign('message', 'Username sau parola geşite. Va rugăm reîncercaţi.');
            $this->view->assign('login', $data);
            $this->_forward('autentificare');
            return;
        }

        if(isset($data['keepMeLoggedIn']) && $data['keepMeLoggedIn']) {
            MyShop_Plugin_Authentication::setAuthCookie($data['username'], md5($data['password']));
        }

        //save session data
        $_SESSION['profile'] = array_intersect_key(
            $user->toArray(),
            array_flip(array(
                'id', 'username', 'nume', 'prenume', 'email'
            ))
        );
        MyShop_Basket::getInstance()->setUserId($_SESSION['profile']['id']);

        $originalRequest = $this->_getParam('originalRequest');
        if($originalRequest) {
            $this->_forward($originalRequest['action'], $originalRequest['controller']);
        }
        else {
            $this->_redirect('/');
        }
    }

    /**
     * Destroy users session and unset authentication cookie
     */
    public function logoutAction()
    {
        Zend_Session::destroy();
        MyShop_Plugin_Authentication::unsetAuthCookie();

        $this->_redirect('/');
    }

    /**
     * New user registration
     */
    public function inregistrareAction()
    {
        $this->_helper->Layout->addBreadcrumb('Creare cont', '/cont/inregistrare');
        $this->view->assign('regions', Doctrine::getTable('Judete')->fetchAll());
        $this->view->assign('title', 'Creare cont');
        
        $this->_helper->Layout->includeCss('login-register.css');
        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->_helper->Layout->includeJs('register.js');
    }
    
    /**
     * Check if username is valid
     */
    public function verificaUsernameAction()
    {
        $this->getFrontController()->setParam('noViewRenderer', true);

        $username = $this->_getParam('username');
        $membru = Doctrine::getTable('Membri')->findOneByUsername($username);

        if($membru) {
            $response = array(
                'message' => 'Exista deja un cont înregistrat cu acest nume!',
                'code' => 0
            );
        }
        else {
            $response = array(
                'message' => 'Numele de utilizator este disponibil',
                'code' => 1
            );
        }

        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Register / save account information
     */
    public function salveazaAction()
    {
        $data = $this->_getParam('account');
        $context = $this->_getParam('context', 'inregistrare');
        if(empty($data)) {
            $this->_forward($context);
            return;
        }

        if($context == 'inregistrare') {
            $membru = new Membri();
        }
        else {
            $membru = Doctrine::getTable('Members')->find($_SESSION['profile']['id']);
        }
        $membru->synchronizeWithArray($data);

        if(!$membru->validData($context != 'inregistrare')) {
            if($membru->duplicateField) {
                $message = "Exista deja un cont inregistrat cu '{$membru->duplicateField}' egal cu '{$data[$membru->duplicateField]}' !";
                unset($data[$membru->duplicateField]);
            }
            else {
                $message = "Campul '{$membru->notValidField}' este invalid !";
                unset($data[$membru->notValidField]);
            }
            $this->view->assign('account', $data);
            $this->view->assign('message', $message);
            $this->_forward($context);
            return;
        }

        //save information and send email
        try {
            $membru->save();
            $membru->refresh();

            if($context == 'inregistrare') {
                $this->view->assign('account', $membru);
                $emailBody = $this->view->render('cont/email-inregistrare.html');

                $mail = new Zend_Mail();
                $mail->setFrom('raresvla@yahoo.com');
                $mail->addTo($membru->email, "{$membru->prenume} {$membru->nume}");
                $mail->setBodyHtml($emailBody);
                $mail->setSubject('MyShop va ureaza Bun venit !');
                //$mail->send(new Zend_Mail_Transport_Smtp());
            }
            else {
                $_SESSION['profile'] = $membru->toArray();
            }
        }
        catch(Exception $e) {
            $this->view->assign('message', $e->getMessage());
            $this->_forward($context);
            return;
        }

        if($context == 'inregistrare') {
            $this->_setParam('fromRegister', true);
            $this->_forward('index', 'autentificare');
        }
        else {
            $this->_forward('index', 'index');
        }
    }
}

