<?php
/**
 * MyShop Invoice
 * Multiton pattern implementation
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 */
class MyShop_Invoice
{
    private static $_instances = array();

    private $_basket;
    private $_data;

    const DATA_NAMESPACE = 'INVOICE';
    const INVOICE_TYPE_DATA = 'type';
    const SHIPPING_DATA = 'shipping';

    /**
     * Get Invoice instance
     *
     * @param MyShop_Basket $basket
     * @return MyShop_Basket
     */
    public static function getInstance(MyShop_Basket $basket)
    {
        $basketId = (string) $basket;
        if(!isset(self::$_instances[$basketId]) || !self::$_instances[$basketId] instanceof self) {
            self::$_instances[$basketId] = new self($basket);
        }

        return self::$_instances[$basketId];
    }

    /**
     * Class constructor
     *
     * @param MyShop_Basket $basket
     * @return MyShop_Invoice
     */
    protected function  __construct(MyShop_Basket $basket)
    {
        if(!isset($_SESSION[self::DATA_NAMESPACE])) {
            $_SESSION[self::DATA_NAMESPACE] = array();
        }
        $this->_data = &$_SESSION[self::DATA_NAMESPACE];
        $this->_basket = $basket;
    }

    /**
     * Load submitted forms data
     *
     * @param string $dataType
     * @param mixed $data
     * @param boolean $owerwrite
     * @return MyShop_Invoice
     */
    public function load($dataType, $data, $overwrite = true)
    {
        if(isset($this->_data[$dataType]) && !$overwrite) {
            throw new Zend_Exception("Data for {$dataType} already exists! Do you want to overwrite?");
        }
        
        $this->_data[$dataType] = $data;
        return $this;
    }

    /**
     * Retreive saved information
     *
     * @param string $dataType
     * @return mixed
     */
    public function get($dataType)
    {
        if(!isset($this->_data[$dataType])) {
            return null;
        }

        return $this->_data[$dataType];
    }

    /**
     * Load submitted forms data
     *
     * @param string $name
     * @param array $value
     */
    public function  __set($name,  $value)
    {
        return $this->load($name, $value);
    }

    /**
     * Retreive submitted forms data
     *
     * @param string $name
     * @return mixed
     */
    public function  __get($name)
    {
        return $this->get($name);
    }
}