<?php
require_once 'Smarty/Smarty.class.php';

/**
 * Implements Zend_View_Interface functionality over smarty
 *
 * @author Rares Vlasceanu
 * @package RSoft
 * @subpackage View
 */
class MyShop_Smarty extends Smarty implements Zend_View_Interface
{
    /**
     * Smarty object
     * @var Smarty
     */
    protected $_actionController;

    /**
     * Constructor
     *
     * @param string $tmplPath
     * @param array $extraParams
     * @return void
     */
    public function __construct($tmplPath = null, $extraParams = array())
    {
        parent::Smarty();

        $this->compile_dir = $GLOBALS['ROOT_DIR'] . DIR_SEP . 'tmp' . DIR_SEP . 'templates_c';
        $this->plugins_dir = array('plugins','../MyShop/SmartyPlugins');
        $this->use_sub_dirs = true;

        if (null !== $tmplPath) {
            $this->setScriptPath($tmplPath);
        }

        foreach ($extraParams as $key => $value) {
            parent::assign($key, $value);
        }
    }

    public function setCompileId( $id = ''){
        $this->compile_id = $id;
    }

    /**
     * Return the template engine object
     *
     * @return Smarty
     */
    public function getEngine(){
        return $this;
    }

    /**
     * assign variables using a comma delimited string evaluating each value like a php variable
     *
     * @param array|string - comma delimited string with variable names or array with variables names
     */
    function assign_by_eval($vars)
    {
        if( !is_array( $vars ) ){
            $vars = explode(',', $vars );
        }

        foreach( $vars as $var ){
            if( isset($GLOBALS[$var]) )
                $this->assign($var, $GLOBALS[$var]);
        }
    }

    /**
     * Set the path to the templates
     *
     * @param string $path The directory to set as the path.
     * @return void
     */
    public function setScriptPath($path)
    {
        if (is_readable($path)) {
            $this->template_dir = $path;
            $this->compile_id 	= $path;
            return;
        }

        throw new Exception('Invalid path provided');
    }

    /**
     * Retrieve the current template directory
     *
     * @return string
     */
    public function getScriptPaths()
    {
        return (array)$this->template_dir;
    }

    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function setBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function addBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * Assign a variable to the template
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val)
    {
        parent::assign($key, $val);
    }

    /**
     * Retrieve an assigned variable
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->_tpl_vars[$key];
    }

    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset( $this->_tpl_vars[$key]);
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->clear_assign($key);
    }

    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing an array
     * of key => value pairs to set en masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or array of key
     * => value pairs)
     * @param mixed $value (Optional) If assigning a named variable, use this
     * as the value.
     * @return void
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            parent::assign($spec);
            return;
        }

        parent::assign($spec, $value);
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or
     * property overloading ({@link __get()}/{@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->clear_all_assign();
    }

    public function setActionController(Zend_Controller_Action $action){
        $this->_actionController = $action;
    }

    /**
     * Action controller
     *
     * @return Zend_Controller_Action
     */
    public function getActionController(){
        return $this->_actionController;
    }
    /**
     * Processes a template and returns the output.
     *
     * @param string $name The template to process.
     * @return string The output.
     */
    public function render($name)
    {
        return $this->fetch($name);
    }
}