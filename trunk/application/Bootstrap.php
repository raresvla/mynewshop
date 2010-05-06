<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function run()
    {
        //set autoloader
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
        $autoloader->registerNamespace('MyShop_');
        $autoloader->registerNamespace('Doctrine_');

        //set session
        if(!session_id()) {
            Zend_Session::start();
        }

        //set plugins
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new MyShop_Plugin_Init());
        $front->registerPlugin(new MyShop_Plugin_Authentication());
        $front->registerPlugin(new MyShop_Plugin_Router());
        
        parent::run();
    }

    /**
     * Close DB Connections
     */
    public function  __destruct()
    {
        foreach(Doctrine_Manager::getInstance()->getConnections() as $conn) {
            $conn->close();
        }
    }
}