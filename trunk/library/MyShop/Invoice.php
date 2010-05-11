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

    public $invalidFields = array();
    private $_basket;
    private $_data;
    private $_cache;
    

    const DATA_NAMESPACE = 'INVOICE_%s';
    const INVOICE_TYPE_PERSONAL = 'fizica';
    const INVOICE_TYPE_BUSINESS = 'juridica';

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
        $namespace = sprintf(self::DATA_NAMESPACE, (string) $basket);
        if(!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = array();
        }
        $this->_data = &$_SESSION[$namespace];
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

        //reset if setting type
        if($dataType == 'tip' && (!isset($this->_data['tip']) || $this->_data['tip'] != $data)) {
            $this->_data = array();
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
        if($name == 'cumparator' && $this->tip == self::INVOICE_TYPE_BUSINESS) {
            $companies = $this->_getUserCompanies();
            return $companies[$this->_data['cumparator']['companie']];
        }
        return $this->get($name);
    }

    /**
     * Implemented for empty checks
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Validate submitted information
     *
     * @return boolean
     */
    public function isValid()
    {
        $name = new Zend_Validate_Regex('/^[a-zA-ZăîâşţĂÎÂŞŢ-]+$/');
        $length3 = new Zend_Validate_StringLength(array('min' => 3));
        $length6 = new Zend_Validate_StringLength(array('min' => 6));
        $email = new Zend_Validate_EmailAddress();
        
        $vChainRealName = new Zend_Validate();
        $vChainRealName->addValidator($length3);
        $vChainRealName->addValidator($name);

        if($this->tip == self::INVOICE_TYPE_PERSONAL) {
            $buyerValidator = array(
                'nume' => $vChainRealName,
                'prenume' => $vChainRealName,
                'telefon' => $length6,
                'email' => $email,
                'adresa' => new Zend_Validate_InArray(array_keys($this->_getUserAddresses()))
            );
        }
        else {
            $buyerValidator = array(
                'companie' => new Zend_Validate_InArray(array_keys($this->_getUserCompanies()))
            );
        }

        $valid = true;
        foreach($buyerValidator as $field => $validator) {
            $test = $validator->isValid($this->_data['cumparator'][$field]);
            if(!$test) {
                $this->invalidFields['cumparator'][] = $field;
            }
            $valid = $valid && $test;
        }

        if(empty($this->destinatar['cumparator'])) {
            $receiverValidator = array(
                'nume' => $vChainRealName,
                'prenume' => $vChainRealName,
                'adresa' => $length3,
                'oras' => $length3,
                'judet' => $length3
            );
            foreach($receiverValidator as $field => $validator) {
                $test = $validator->isValid($this->_data['destinatar'][$field]);
                if(!$test) {
                    $this->invalidFields['destinatar'][] = $field;
                }
                $valid = $valid && $test;
            }
        }

        return $valid;
    }

    /**
     * Get current user's saved addresess
     *
     * @return array
     */
    private function _getUserAddresses()
    {
        $userId = $this->_basket->getUserId();
        if(empty($userId)) {
            throw new Zend_Exception('Userid cannot be empty!');
        }

        if(empty($this->_cache['addresses'])) {
            $sql = Doctrine_Query::create();
            $sql->select('m.id, a.*');
            $sql->from('Membri m');
            $sql->leftJoin('m.Adrese a INDEXBY a.id');
            $sql->where('m.id = ?', $userId);
            $data = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
            
            $this->_cache['addresses'] = $data['Adrese'];
        }

        return $this->_cache['addresses'];
    }

    /**
     * Get current user's saved companies
     *
     * @return array
     */
    private function _getUserCompanies()
    {
        $userId = $this->_basket->getUserId();
        if(empty($userId)) {
            throw new Zend_Exception('Userid cannot be empty!');
        }

        if(empty($this->_cache['companies'])) {
            $sql = Doctrine_Query::create();
            $sql->select('m.id, c.*');
            $sql->from('Membri m');
            $sql->leftJoin('m.Companii c INDEXBY c.id');
            $sql->where('m.id = ?', $userId);
            $data = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
            
            $this->_cache['companies'] = $data['Companii'];
        }
        
        return $this->_cache['companies'];
    }

    /**
     * Check if buyer is also the receiver
     * 
     * @return boolean
     */
    public function buyerIsReceiver()
    {
        return !empty($this->destinatar['cumparator']);
    }

    /**
     * Returns order billing address
     *
     * @return array
     */
    public function getBillingAddress()
    {
        if(empty($this->cumparator)) {
            return false;
        }

        if($this->tip == self::INVOICE_TYPE_PERSONAL) {
            $addresses = $this->_getUserAddresses();
            $address = $addresses[$this->cumparator['adresa']];
        }
        else {
            $companies = $this->_getUserCompanies();
            //print_r($companies); die();
            //die();
        }

        return $address;
    }

    /**
     * Returns order shipping address
     *
     * @return array
     */
    public function getShippingAddress()
    {
        if(empty($this->cumparator)) {
            return false;
        }
        if(!$this->buyerIsReceiver()) {
            return $this->destinatar;
        }

        $addresses = $this->_getUserAddresses();
        return $addresses[$this->cumparator['adresa']];
    }

    /**
     * Calculate shipping cost
     *
     * @return float
     */
    public function getShippingCost()
    {
        $value = 0;
        if($this->_basket->total() < floatval($this->_basket->config->VALOARE_COST_TRANSPORT_ZERO)) {
            $address = $this->getShippingAddress();
            if(!$this->isProvince($address['judet'])) {
                $value = $this->_basket->config->TAXA_TRANSPORT_BUCURESTI;
            }
            else {
                $this->_basket->config->TAXA_TRANSPORT_PROVINCIE;
            }
        }

        return $value;
    }

    /**
     * Check if region is in provice
     *
     * @param string $region
     * @return boolean
     */
    public function isProvince($region)
    {
        return !(strpos($region, 'Sector') !== false);
    }
}