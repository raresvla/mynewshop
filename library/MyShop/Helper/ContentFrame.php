<?php
/**
 * ContentFrame Helper
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage Helper
 * @version 1.0
 */
class MyShop_Helper_ContentFrame extends Zend_Controller_Action_Helper_Abstract
{
    private $_template = null;
    private $_assignVar = 'sectionContent';

    /**
     * Allow direct call
     *
     * @param string $template
     * @param string $assignVar
     * @return MyShop_Helper_ContentFrame
     */
    public function direct($template = null, $assignVar = null)
    {
        //force set priority lower than ViewRenderer
        $stack = Zend_Controller_Action_HelperBroker::getStack();
        $stack->offsetUnset('ContentFrame');
        $stack->offsetSet(0, $this);

        if($template) {
            $this->setTemplate($template, $assignVar);
        }

        return $this;
    }

    /**
     * Set path to template file that will be used as content frame
     *
     * @param string $template
     * @param string $assignVar
     * @return MyShop_Helper_ContentFrame
     */
    public function setTemplate($template, $assignVar)
    {
        if(empty($template)) {
            throw new Zend_Exception('Template name cannot be null!');
        }

        $this->_template = $template;
        if(!empty($assignVar)) {
            $this->_assignVar = $assignVar;
        }

        return $this;
    }

    /**
     * Post dispatch hook
     *
     * @return void
     */
    public function postDispatch()
    {
        if(!$this->_actionController->getRequest()->isDispatched()) {
            return;
        }

        $response = $this->getResponse();
        $this->_actionController->view->assign($this->_assignVar, $response->getBody());
        $response->setBody($this->_actionController->view->render($this->_template));
    }
}