<?php
/**
 * Layout decorator (helper class)
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 * @subpackage Helper
 */
class MyShop_Helper_Layout extends Zend_Controller_Action_Helper_Abstract
{
    protected $_layoutTemplate = null;
    protected $_enabled = true;
    protected $_moduleDir;
    protected $_moduleName;

    /* Layout placeholders for regions and dependencies */
    protected $_regions = array();
    protected $_scripts = array();
    protected $_styles = array();
    protected $_injectedJs;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $templatesDir = "{$GLOBALS['ROOT_DIR']}\\application\\views\\helpers\\layout";
        $this->_layoutTemplate = "{$templatesDir}\\layout.html";
        $this->setTemplate('header', "{$templatesDir}\\header.html");
        $this->setTemplate('sidebar', "{$templatesDir}\\sidebar.html");
        $this->setTemplate('footer', "{$templatesDir}\\footer.html");
    }

    /**
     * Method autoloaded at init time
     */
    public function direct()
    {
        $this->_moduleName = $this->getActionController()->getRequest()->getModuleName();

        $controller_dir = $this->getFrontController()->getControllerDirectory($this->_moduleName);
        $this->_moduleDir = realpath($controller_dir . "/../");

        if(!$this->_actionController->view) {
            $this->disable();
        }
    }

    /**
     * Setter magic method.
     * Sets the template to be fetched in order to fill a region of the layout with content.
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->_regions[$name]['template'] = $value;
    }

    /**
     * Method for setting a template for a region
     *
     * @param string $region
     * @param string $template
     */
    public function setTemplate($region, $template)
    {
        $this->_regions[$region]['template'] = $template;
    }

    /**
     * This method overrides the content of a region with a custom one.
     * The template will not be fetched if content is set.
     *
     * @param string $region
     * @param string $body
     */
    public function setBody($region, $body)
    {
        $this->_regions[$region]['body'] = $body;
    }

    /**
     * This method sets custom inline CSS style for a specific region
     *
     * @param string $region
     * @param string $prop
     * @param string $value
     */
    public function setStyle($region, $prop, $value)
    {
        if(isset($this->_regions[$region])){
            $this->_regions[$region]['style'] .= $prop . ': ' . $value;
        }
        else{
            $this->_regions[$region]['style'] = $prop . ': ' . $value;
        }
    }

    /**
     * This method changes the layout template to be used
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->_layoutTemplate = $layout;
    }

    /**
     * This method includes a stylesheet in current view
     *
     * @param string $cssName
     */
    public function includeCss($cssName)
    {
        if(in_array($cssName, $this->_styles)) {
            return;
        }
        $this->_styles[] = $cssName;
    }

    /**
     * This method includes a Javascript file in current view
     *
     * @param string $jsName
     */
    public function includeJs($jsName)
    {
        if(in_array($jsName, $this->_scripts)) {
            return;
        }
        $this->_scripts[] = $jsName;
    }

    /**
     * This method includes Javascript content inline.
     *
     * @param string $jsContent
     */
    public function injectJs($jsContent)
    {
        $this->_injectedJs .= $jsContent;
    }

    /**
     * This method "disables" the Layout Helper -> in fact it prevents content fething at postdispatch time
     *
     * @param boolean $disableViewRenderer
     */
    public function disable($disableViewRenderer = false)
    {
        $this->_enabled = false;
        if($disableViewRenderer) {
            $this->getFrontController()->setParam('noViewRenderer', true);
        }
    }

    /**
     * This method enables the Layout
     */
    public function enable()
    {
        $this->_enabled = true;
    }

    /**
     * Fetch regions and send the result to Layout
     */
    public function postDispatch()
    {
        if(!$this->_enabled) {
            $this->enable();
            return false;
        }
    	if(!$this->getRequest()->isDispatched()) {
            return false;
    	}
        if(!$this->_actionController->view || $this->_actionController->getRequest()->isXMLHttpRequest()) {
            $this->disable();
            return false;
        }

        //get action body
        $view = $this->getActionController()->view;
        $response = $this->getActionController()->getResponse();
        if(empty($this->_regions['content']['body'])) {
            $this->_regions['content']['body'] = $response->getBody();
        }

        //fetch layout regions
        foreach($this->_regions as $region => $data) {
            if(empty($data)) {
                continue;
            }
            if(empty($data['body']) && !empty($data['template']) && empty($data['type'])){
                $data['body'] = $view->render($data['template']);
            }
            if(empty($data['type'])){
                $data['type'] = 'text';
            }
            $view->assign($region, $data);
        }

        //assign dependencies
        $styles = "";
        foreach($this->_styles as $style) {
            $styles .= '<link rel="stylesheet" type="text/css" href="/css/' . $style . '" />' . "\n";
        }
        $scripts = "";
        foreach($this->_scripts as $script) {
            $scripts .= '<script type="text/javascript" src="/js/' . $script . '"></script>' . "\n";
        }
        $view->assign('styles', $styles);
        $view->assign('scripts', $scripts);

        //add injected js and breadcrumbs
        $view->assign('injectedJs', $this->_injectedJs);
        $response->setBody($view->render($this->_layoutTemplate));
    }
}