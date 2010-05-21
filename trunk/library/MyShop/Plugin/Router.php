<?php
/**
 * This plugin checks defines custom application routes
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 * @subpackage Plugin
 */
class MyShop_Plugin_Router extends Zend_Controller_Plugin_Abstract
{
    /**
     * Called before Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        /**
         * Products catalog
         */
        $router->addRoute('catalog',
            new Zend_Controller_Router_Route_Regex(
                '^[a-zA-Z0-9\-/]+?/c-([0-9_]+)(-pagina-([0-9]+)/?)?$',
                array(
                    'controller' => 'produse',
                    'action' => 'categorie'
                ),
                array(
                    1 => 'cPath',
                    2 => 'viewing-subcategory',
                    3 => 'pagina'
                ),
                '%s/c-%s-pagina-%d/'
            )
        );

        /**
         * Product details
         */
        $router->addRoute('product-details',
            new Zend_Controller_Router_Route_Regex(
                '^[a-zA-Z0-9\-/]+?-([0-9]+)\.html$', //(([0-9_]+)-)?
                array(
                    'controller' => 'produse',
                    'action' => 'detalii-produs'
                ),
                array(
                    1 => 'pid'
                ),
                '%s-%d.html'
            )
        );
    }
}