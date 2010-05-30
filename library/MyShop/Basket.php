<?php
/**
 * Basket management class
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 */
class MyShop_Basket implements ArrayAccess, Iterator, Countable
{
    private static $_instance;
    
    private $_data;
    private $_userId;
    private $_sessionId;
    private $_index = 0;
    public $config;
    public $maxAvaliableReachedMessage = "Max avaliable quantity for product '%s' is %d!";
    public $notAvaliableMessage = "Product '%s' is no longer available!";

    const DATA_NAMESPACE = 'BASKET';

    /**
     * Get Basket instance
     *
     * @return MyShop_Basket
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Class constructor
     */
    protected function  __construct()
    {
        if(!isset($_SESSION[self::DATA_NAMESPACE])) {
            $_SESSION[self::DATA_NAMESPACE] = array();
        }
        $this->_data = &$_SESSION[self::DATA_NAMESPACE];
        $this->_sessionId = session_id();
        $this->config = MyShop_Config::getInstance();
    }

    /**
     * Set current user ID
     *
     * @param integer $userId
     * @return MyShop_Basket
     */
    public function setUserId($userId)
    {
        if(!empty($this->_userId) && $this->_userId != $userId) {
            $this->removeAll();
        }

        $load = empty($this->_userId);
        $this->_userId = $userId;
        if(sizeof($this->_data)) {
            $sql = Doctrine_Query::create();
            $sql->update('CartTrack');
            $sql->set('membru_id', '?', $this->_userId);
            $sql->where('session_id = ?', $this->_sessionId);
            $sql->execute();
        }
        else {
            $this->_loadFromShadowCopy();
        }

        return $this;
    }

    /**
     * Get the id of current user
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Load basket from shadow copy
     */
    private function _loadFromShadowCopy()
    {
        $sql = Doctrine_Query::create();
        $sql->select('c.id_produs, c.cantitate, p.denumire, p.cod_produs, p.stoc_disponibil, g.foto as foto');
        $sql->addSelect('cc.denumire AS categorie, IF(pm.pret_oferta, pm.pret_oferta, p.pret) AS price');
        $sql->from('CartTrack c');
        $sql->innerJoin('c.Produse p');
        $sql->leftJoin('p.Categorii cc');
        $sql->leftJoin('p.MainPhoto g WITH g.main = 1');
        $sql->leftJoin('p.Promotii pm WITH (pm.data_inceput <= DATE(NOW()) AND pm.data_sfarsit >= DATE(NOW()))');
        if(empty($this->_userId)) {
            $sql->where('session_id = ?', $this->_sessionId);
        }
        else {
            $sql->where('membru_id = ?', $this->_userId);
        }
        
        $shadowCopy = $sql->fetchArray();
        foreach($shadowCopy as &$product) {
            $data = $product['Produse'] + array(
                'categorie' => $product['categorie'],
                'foto' => $product['foto'],
                'price' => $product['price']
            );
            $this->_addToBasket($data, $product['cantitate'], false);
        }
    }

    /**
     * Add new product in basket
     *
     * @param integer $productId
     * @param integer $quantity
     * @return MyShop_Basket
     */
    public function add($productId, $quantity = 1)
    {
        $productData = $this->_getProductData($productId);
        $this->_addToBasket($productData, $quantity);

        return $this;
    }

    /**
     * Fetch product data
     *
     * @param integer $productId
     * @return array
     */
    private function _getProductData($productId)
    {
        if(empty($productId)) {
            throw new Zend_Exception('Product id cannot be empty!');
        }

        $sql = Doctrine_Query::create();
        $sql->select('p.denumire, p.cod_produs, p.stoc_disponibil, g.foto as foto');
        $sql->addSelect('cc.denumire AS categorie, IF(pm.pret_oferta, pm.pret_oferta, p.pret) AS price');
        $sql->from('Produse p');
        $sql->leftJoin('p.Categorii cc');
        $sql->leftJoin('p.MainPhoto g WITH g.main = 1');
        $sql->leftJoin('p.Promotii pm WITH (pm.data_inceput <= DATE(NOW()) AND pm.data_sfarsit >= DATE(NOW()))');
        $sql->where('p.id = ?', $productId);

        return $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
    }

    /**
     * Insert product in basket
     *
     * @param array $productData
     * @param integer $quantity
     * @param boolean $updateShadowCopy
     */
    private function _addToBasket($productData, $quantity = 1, $updateShadowCopy = true)
    {
        $productId = $productData['id'];
        if(isset($this->_data[$productId])) {
            $quantity += $this->_data[$productId]['quantity'];
        }
        if($quantity > $productData['stoc_disponibil']) {
            if(empty($productData['stoc_disponibil'])) {
                $message = $this->notAvaliableMessage;
            }
            else {
                $message = $this->maxAvaliableReachedMessage;
            }
            $productName = (!empty($productData['denumire']) ? '"' . $productData['denumire'] . '"' : '');
            throw new Zend_Exception(sprintf(
                $message,
                $productName,
                $productData['stoc_disponibil']
            ));
        }
        
        if(!isset($this->_data[$productId])) {
            $this->_data[$productId] = $productData;
        }
        $this->_data[$productId]['quantity'] = $quantity;
        
        if($updateShadowCopy) {
            $this->_updateShadowCopy($productId);
        }
    }

    /**
     * Remove product from basket
     *
     * @param integer $productId
     * @param integer $quantity
     * @return MyShop_Basket
     */
    public function remove($productId, $quantity = null)
    {
        if(!empty($productId) && isset($this->_data[$productId])) {
            if(empty($quantity) || $quantity > $this->_data[$productId]['quantity']) {
                $quantity = $this->_data[$productId]['quantity'];
            }
            $this->_data[$productId]['quantity'] -= $quantity;
            if(empty($this->_data[$productId]['quantity'])) {
                unset($this->_data[$productId]);
            }
            $this->_updateShadowCopy($productId);
        }

        return $this;
    }

    /**
     * Update cart shadow copy
     *
     * @param integer $productId
     * @return boolean | integer copy ID
     */
    private function _updateShadowCopy($productId)
    {
        if(!isset($this->_data[$productId])) {
            return $this->_removeShadowCopy($productId);
        }

        $sql = Doctrine_Query::create();
        $sql->select('*');
        $sql->from('CartTrack');
        $sql->where('(session_id = ? or membru_id = ?) and id_produs = ?');
        $copy = $sql->fetchOne(array($this->_sessionId, $this->_userId, $productId));

        if(empty($copy)) {
            $copy = new CartTrack();
            $copy->session_id = $this->_sessionId;
            $copy->membru_id = $this->_userId;
            $copy->id_produs = $productId;
        }
        $copy->cantitate = $this->_data[$productId]['quantity'];

        return $copy->save();
    }

    /**
     * Delete basket shadow copy
     *
     * @param integer $productId
     * @param boolean $all
     * @return integer
     */
    private function _removeShadowCopy($productId = null, $all = false)
    {
        $sql = Doctrine_Query::create();
        $sql->delete();
        $sql->from('CartTrack');
        $sql->where('session_id = ? or membru_id = ?', array($this->_sessionId, $this->_userId));
        if(!is_null($productId)) {
            $sql->andWhere('id_produs = ?', $productId);
        }
        elseif(!$all) {
            throw new Zend_Exception('Either provide $productId parameter or set $all to true!');
        }

        return $sql->execute();
    }

    /**
     * Remove all products from basket
     *
     * @return MyShop_Basket
     */
    public function removeAll()
    {
        if(sizeof($this->_data)) {
            $this->_removeShadowCopy(null, true);
        }
        $this->_data = array();
        
        return $this;
    }

    /**
     * Return value without configurated VAT value
     *
     * @param float $value
     * @return float
     */
    public function valueWithoutVat($value)
    {
        if(!$value) {
            return $value;
        }

        return $value / (1 + ($this->config->TVA / 100));
    }

    /**
     * Count total value of products in basket
     *
     * @return float
     */
    public function total()
    {
        $total = 0;
        foreach($this->_data as &$product) {
            $total += ($product['price'] * $product['quantity']);
        }

        return $total;
    }

    /**
     * Add product to basket / set qunatity if product exists
     * ArrayAccess implementation
     *
     * @param integer $productId
     * @param integer $quantity
     * @return MyShop_Basket
     */
    public function offsetSet($productId, $quantity)
    {
        if(isset($this->_data[$productId])) {
            $quantity -= $this->_data[$productId]['quantity'];
        }

        return $this->add($productId, $quantity);
    }

    /**
     * Check if product exists in basket
     * ArrayAccess implementation
     *
     * @param integer $productId
     * @return boolean
     */
    public function offsetExists($productId)
    {
        return isset($this->_data[$productId]);
    }

    /**
     * Remove product from basket
     * ArrayAccess implementation
     *
     * @param integer $productId
     * @return MyShop_Basket
     */
    public function offsetUnset($productId)
    {
        return $this->remove($productId);
    }

    /**
     * Get basket item data
     * ArrayAccess implementation
     *
     * @param integer $productId
     * @return array
     */
    public function offsetGet($productId)
    {
        if(!isset($this->_data[$productId])) {
            return null;
        }

        return $this->_data[$productId];
    }

    /**
     * Count products in basket
     * Countable implementation
     *
     * @return integer
     */
	public function count()
    {
        $count = 0;
        foreach($this->_data as &$product) {
            $count += $product['quantity'];
        }

		return $count;
	}

	/**
     * Get current index
     * Iterable implementation
     *
     * @return integer
     */
	public function current()
    {
		return current($this->_data);
	}

    /**
     * Get current key
     * Iterable implementation
     *
     * @return integer
     */
	public function key()
    {
		return key($this->_data);
	}

    /**
     * Advance array pointer
     * Iterable implementation
     */
	public function next()
    {
		next($this->_data);
        $this->_index++;
	}

    /**
     * Rewind array pointer
     * Iterable implementation
     */
	public function rewind()
    {
		reset($this->_data);
        $this->_index = 0;
	}

    /**
     * Advance array pointer
     *
     * @return boolean
     */
	public function valid()
    {
		return $this->_index < sizeof($this->_data);
	}

    /**
     * Generate basket identifier
     */
    public function  __toString()
    {
        return sprintf('MyShopBasket_%d_%s', $this->_userId, $this->_sessionId);
    }
}