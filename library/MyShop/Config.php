<?php
/**
 * Singleton implementation of Zend_Config
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage Config
 * @version 1.0
 */
class MyShop_Config extends Zend_Config
{
    private static $_instance;
    private $_path;

    /**
     * Instance getter
     *
     * @return MyShop_Config
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
    public function  __construct()
    {
        parent::__construct(array(), true);
        
        $this->_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs';
    }

    /**
     * Load config file
     *
     * @param string $configFile
     * @return MyShop_Config
     */
    public function load($configFile)
    {
        $fileName = $this->_path . DIRECTORY_SEPARATOR . $configFile;
        if(!file_exists($fileName)) {
            throw new Zend_Config_Exception("File does not exist! ({$configFile})");
        }

        $config = new Zend_Config_Ini($fileName);
        $this->merge($config);
        unset($config);
 
        return $this;
    }

    /**
     * Load configuration settings set in database
     *
     * @return MyShop_Config
     */
    public function loadDatabaseConfig()
    {
        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        if(empty($conn)) {
            throw new Zend_Config_Exception('No connection to DB defined, cannot load info!');
        }

        $sql = $conn->createQuery();
        $sql->select('parametru, valoare');
        $sql->from('Config INDEXBY parametru');
        $data = $sql->fetchArray();
        foreach($data as &$item) {
            $item = $item['valoare'];
        }
        
        $config = new Zend_Config($data);
        $this->merge($config);
        unset($config);

        return $this;
    }
}