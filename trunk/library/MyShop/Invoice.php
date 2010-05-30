<?php
/**
 * MyShop Invoice
 * Multiton pattern implementation
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 *
 * @property integer orderId
 * @property string tip
 * @property array cumparator
 * @property array destinatar
 * @property string modalitatePlata
 * @property string specificatii
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
    const INVOICE_CODE = '#%d-%s';
    const ORDER_CONFIRMATION_SALT = 'MSOC-MDEb02BHGSOSu8';

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
        if($dataType == 'cumparator' && $this->tip == self::INVOICE_TYPE_BUSINESS) {
            $companies = $this->_getUserCompanies();
            return $companies[$this->_data['cumparator']['companie']];
        }
        if($dataType == 'destinatar' && $this->buyerIsReceiver()) {
            return $this->cumparator;
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

        if(!$this->buyerIsReceiver()) {
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
        return !empty($this->_data['destinatar']['cumparator']);
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

        $address = array();
        if($this->tip == self::INVOICE_TYPE_PERSONAL) {
            $addresses = $this->_getUserAddresses();
            $address = $addresses[$this->cumparator['adresa']];
        }
        else {
            $companies = $this->_getUserCompanies();
            $companyId = $this->_data['cumparator']['companie'];
            $fields = array('adresa_sediu', 'oras_sediu', 'judet_sediu', 'cod_postal_sediu');
            $data = array_intersect_key($companies[$companyId], array_flip($fields));
            foreach($data as $key => $value) {
                $address[str_replace('_sediu', '', $key)] = $value;
            }
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

        return $this->getBillingAddress();
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
     * Generate order code (next available)
     *
     * @return string
     */
    public function generateOrderCode()
    {
        $sql = Doctrine_Query::create();
        $sql->select('MAX(id) + 1 as nextId');
        $sql->from('Comenzi');
        $data = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

        return sprintf(self::INVOICE_CODE, $data['nextId'], date('dm') . (date('Y') - 2000));
    }

    /**
     * Return info needed to preview order / generate order confirm email
     *
     * @return array
     */
    public function fetchAllData()
    {
        $buyer = $this->cumparator;
        $buyer['address'] = $this->getBillingAddress();
        $receiver = $this->destinatar;
        $receiver['address'] = $this->getShippingAddress();
        $orderData = array(
            'code' => $this->generateOrderCode(),
            'date' => date('d-m-Y\, H:i:s'),
            'buyer' => $buyer,
            'receiver' => $receiver,
            'buyerIsReceiver' => $this->buyerIsReceiver(),
            'type' => $this->tip,
            'products' => $this->_basket,
            'totalValue' => $this->_basket->total(),
            'shippingCost' => $this->getShippingCost(),
            'paymentMethod' => $this->modalitatePlata
        );

        return $orderData;
    }

    /**
     * Save order in database
     *
     * @return boolean (true)
     */
    public function save()
    {
        Doctrine_Manager::connection()->beginTransaction();

        $order = new Comenzi();
        $order->membru_id = $this->_basket->getUserId();
        $order->cod_comanda = $this->generateOrderCode();
        $order->total_fara_tva = $this->_basket->valueWithoutVat($this->_basket->total());
        $order->total_taxe = $this->getShippingCost();
        $order->total_tva = ($this->_basket->total() - $order->total_fara_tva);
        $order->mod_plata = $this->modalitatePlata;
        $order->specificatii = $this->specificatii;

        foreach($this->_basket as $item) {
            $orderItem = new Facturi();
            $orderItem->produs_id = $item['id'];
            $orderItem->pret = $item['price'];
            $orderItem->cantitate = $item['quantity'];
            $order->Facturi[] = $orderItem;

            $product = Doctrine::getTable('Produse')->find($item['id']);
            $product->stoc_disponibil -= $item['quantity'];
            $product->stoc_rezervat += $item['quantity'];
            $product->save();
            $product->free();
        }

        $shippingAddress = $this->getShippingAddress();
        $client = new Clienti();
        $client->tip_client = $this->tip;
        $client->membru_id = $this->_basket->getUserId();
        if($this->tip == self::INVOICE_TYPE_PERSONAL) {
            $client->adresa_id = $this->cumparator['adresa'];
            $client->nume = $this->cumparator['nume'];
            $client->prenume = $this->cumparator['prenume'];
            $client->telefon = $this->cumparator['telefon'];
            $client->email = $this->cumparator['email'];
        }
        else {
            $client->companie_id = $this->_data['cumparator']['companie'];
        }
        $client->destinatar_nume = $this->destinatar['nume'];
        $client->destinatar_prenume = $this->destinatar['prenume'];
        $client->destinatar_adresa = $shippingAddress['adresa'];
        $client->destinatar_oras = $shippingAddress['oras'];
        $client->destinatar_judet = $shippingAddress['judet'];
        $client->destinatar_cod_postal = $shippingAddress['cod_postal'];
        $order->Clienti[] = $client;

        $order->save();
        $order->refresh();
        $this->orderId = $order->id;

        try {
            $solr = new MyShop_Solr();
            Doctrine_Manager::connection()->commit();
            foreach($this->_basket as $item) {
                $solr->index($item['id']);
            }
        }
        catch(Doctrine_Exception $e) {
            Doctrine_Manager::connection()->rollback();
        }

        return true;
    }

    /**
     * Create current order confirmation token
     *
     * @param integer $orderId
     * @return string
     */
    public static function generateConfirmationToken($orderId)
    {
        $data = array(
            'cid' => $orderId,
            'ttl' => time() + 24 * 60 * 60,
        );
        $data['signature'] = self::_generateSignature($data);

        return base64_encode(json_encode($data));
    }

    /**
     * Check confirmation token and return extracted order id
     *
     * @param string $token
     * @return integer
     */
    public static function checkConfirmationToken($token)
    {
        if(empty($token)) {
            return false;
        }

        $data = json_decode(base64_decode($token), true);
        $signature = array_pop($data);
        if($signature !== self::_generateSignature($data)) {
            return false;
        }
        if(time() > $data['ttl']) {
            return false;
        }

        return $data['cid'];
    }

    /**
     * Generate data signature
     *
     * @param array $data
     * @return string
     */
    private static function _generateSignature(&$data)
    {
        return sha1(http_build_query((array) $data) . self::ORDER_CONFIRMATION_SALT);
    }

    /**
     * Clean invoice / basket data
     */
    public function clean()
    {
        $this->_basket->removeAll();
        unset($this->_basket);
        $this->_data = array();
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