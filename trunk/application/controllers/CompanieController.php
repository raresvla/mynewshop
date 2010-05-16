<?php
/**
 * Companie Controller
 *
 * @author Rares Vlasceanu
 */
class CompanieController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->assign('section', $this->_getParam('action'));
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_forward('contact');
    }

    /**
     * Display informations page
     */
    public function informatiiAction()
    {
        $this->_helper->Layout->addBreadcrumb('Informaţii');
    }

    /**
     * Display contact page
     */
    public function contactAction()
    {
        $this->_helper->Layout->addBreadcrumb('Contact');
        $this->_helper->Layout->includeJs('lib/validate.js');
        $this->_helper->Layout->includeJs('custom-validators.js');
        $this->view->assign('cfg', MyShop_Config::getInstance());
        
        if($this->_helper->acl->getRole() == MyShop_Helper_Acl::ROLE_MEMBER) {
            if(empty($this->view->data)) {
                $data = Doctrine::getTable('Membri')->find($_SESSION['profile']['id'])->toArray();
                $data['nume'] .= " {$data['prenume']}";
                $this->view->assign('data', $data);
            }
        }
        else {
            $captcha = new Zend_Captcha_Image(array(
                'imgDir' => APPLICATION_PATH . '/../public/img/captcha',
                'font' => APPLICATION_PATH . '/../library/verdana.ttf',
                'session' => new Zend_Session_Namespace('captcha', true),
                'lineNoiseLevel' => 5,
                'dotNoiseLevel' => 15,
                'wordlen' => 6,
                'imgUrl' => '/img/captcha'
            ));
            $captcha->generate();
            $this->view->assign('captcha', $captcha);
        }
    }

    /**
     * Send feedback email
     */
    public function trimiteFeedbackAction()
    {
        $vChainRealName = new Zend_Validate();
        $vChainRealName->addValidator(new Zend_Validate_StringLength(array('min' => 3)));
        $vChainRealName->addValidator(new Zend_Validate_Regex('/^[ a-zA-ZăîâşţĂÎÂŞŢ-]+$/'));
        $validators = array(
            'nume' => $vChainRealName,
            'email' => new Zend_Validate_EmailAddress(),
            'intrebare' => new Zend_Validate_StringLength(array('min' => 6))
        );

        if($this->_helper->acl->getRole() != MyShop_Helper_Acl::ROLE_MEMBER) {
            $captcha = new Zend_Captcha_Image(array(
                'session' => new Zend_Session_Namespace('captcha'),
            ));
            $validators['captcha'] = $captcha;
        }

        $valid = true;
        $errors = array();
        $data = $this->_getParam('contact');
        foreach($validators as $input => $validator) {
            if($input == 'captcha') {
                $value = $this->_getParam('captcha');
            }
            else {
                $value = $data[$input];
            }
            if(!($check = $validator->isValid($value))) {
                unset($data[$value]);
                $errors[] = $input;
            }
            $valid = $valid && $check;

        }

        if(!$valid) {
            $this->view->assign('data', $data);
            $this->view->assign('errors', $errors);
            $this->_forward('contact');
            return;
        }

        $this->_redirect('/companie/feedback-trimis');
    }

    /**
     * Inform that the feedback was sent
     */
    public function feedbackTrimisAction()
    {
        $this->view->assign('showSentMessage', true);
        $this->_forward('contact');
    }
}