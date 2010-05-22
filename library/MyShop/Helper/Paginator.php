<?php
/**
 * Paginator Helper
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage Helper
 * @version 1.0
 */
class MyShop_Helper_Paginator extends Zend_Controller_Action_Helper_Abstract
{
    public $source;
    public $limit = 10;
    public $queryString = null;

    private $_totalElements;
    private $_totalPages;
    private $_currentPage;
    private $_template;
    private $_requestCfg = array();
    private $_resultsParser;

    const LISTING_HTML_TEMPLATE = 'listing.html';

    /**
     * Allow direct call
     *
     * @param Doctrine_Query | MyShop_Solr $source
     * @return MyShop_Helper_Paginator
     */
    public function direct($source)
    {
        if(!$source instanceof Doctrine_Query && !$source instanceof MyShop_Solr) {
            throw new Zend_Exception('Source must be an instance of Doctrine_Query or MyShop_Solr!');
        }
        $request = $this->getActionController()->getRequest();

        $this->source = $source;
        $this->setRequestUrl($_SERVER['REQUEST_URI'], false);
        $this->queryString = http_build_query(array_diff_key(
            $request->getQuery(),
            array_flip(array('pagina'))
        ));
        $this->setRequestUrl($request->getPathInfo());

        return $this;
    }

    /**
     * Fetch results
     *
     * @return array
     */
    public function getResults()
    {
        if(!isset($this->_results)) {
            $this->source->limit($this->limit);
            $this->source->offset($this->getOffset());
            $this->_results = $this->source->fetchArray();
            if($this->_resultsParser) {
                $this->_results = call_user_func($this->_resultsParser, $this->_results);
            }
        }

        return $this->_results;
    }

    /**
     * Set function to be called for parsing results
     *
     * @param Closure | array $parser
     * @return MyShop_Helper_Paginator
     */
    public function setResultsParser($parser)
    {
        if(!$parser instanceof Closure && !is_array($parser)) {
            throw new Zend_Exception('Results parser not supported!');
        }

        $this->_resultsParser = $parser;
        return $this;
    }

    /**
     * Set results to be used when template is fetched, after some parsing being done
     *
     * @param array $results
     * @return MyShop_Helper_Paginator
     */
    public function setResults($results)
    {
        $this->_results = $results;
        
        return $this;
    }

    /**
     * Set pages request url
     *
     * @param string $url
     * @param boolean $formatted
     * @return MyShop_Helper_Paginator
     */
    public function setRequestUrl($url, $formatted = false)
    {
        $this->_requestCfg = array(
            'url' => $url,
            'formatted' => $formatted
        );

        return $this;
    }

    /**
     * Get current page
     *
     * @return integer
     */
    public function getPage()
    {
        $request = $this->_actionController->getRequest();
        if(!$this->_currentPage) {
            $page = $request->getParam('pagina');
            if(empty($page) || $page === 'NaN') {
                $page = 1;
            }
            $this->setPage($page);
        }

        return $this->_currentPage;
    }

    /**
     * Override page detection
     *
     * @param integer $page
     * @return MyShop_Helper_Paginator
     */
    public function setPage($page)
    {
        if(empty($page)) {
            $page = 1;
        }
        $this->_currentPage = intval($page);

        return $this;
    }

    /**
     * Get total results
     *
     * @return integer
     */
    public function getTotal()
    {
        if(!$this->_totalElements) {
            if(empty($this->source)) {
                throw new Zend_Exception('Cannot fetch total, the source is empty!');
            }
            $this->_totalElements = $this->source->count();
        }

        return $this->_totalElements;
    }

    /**
     * Getter for offset
     *
     * @return integer
     */
    public function getOffset()
    {
        return ($this->getPage() - 1) * $this->limit;
    }

    /**
     * Get current paginator page lower bound
     *
     * @return integer
     */
    public function getLowerBound()
    {
        if(!$this->getTotal()) {
            return 0;
        }

        return $this->getOffset() + 1;
    }

    /**
     * Get current paginator page upper bound
     *
     * @return integer
     */
    public function getUpperBound()
    {
        if(!$this->getTotal()) {
            return 0;
        }

        return min($this->getOffset() + $this->limit, $this->getTotal());
    }

    /**
     * Sets the template that will parse the given source results
     *
     * @param string $template
     * @return MyShop_Helper_Paginator
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        
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
        $this->_actionController->view->assign('paginator', $this);
        $this->_calculateLimits();
        if($this->_template) {
            $this->setResults($this->_actionController->view->render($this->_template));
        }
    }

    /**
     * Calculate pagination limis
     */
    private function _calculateLimits()
    {
        $this->_totalPages = ceil($this->getTotal() / $this->limit);
        if($this->getPage() > $this->_totalPages) {
            $this->setPage($this->_totalPages);
        }
    }

    /**
     * Get listing HTML
     */
    public function getHtml()
    {
        $view = $this->_actionController->view;
        $view->assign('paginator', $this);
        
        $list = $this->_getPagesListed();
        $pagesListed = array(
            'first' => array_shift($list),
            'last' => array_pop($list),
            'list' => $this->_getPagesListed()
        );
        if(empty($pagesListed['first'])) {
            $pagesListed['first'] = $pagesListed['last'];
        }
        if(empty($pagesListed['last'])) {
            $pagesListed['last'] = $pagesListed['first'];
        }
        $view->assign('pagesListed', $pagesListed);
        $view->assign('totalPages', $this->_totalPages);

        $view->assign('hidden', false);
        if ($this->_totalPages == 1 || ! $this->_totalElements) {
            $view->assign('hidden', true);
        }

        $templatesDir = "{$GLOBALS['ROOT_DIR']}\\application\\views\\helpers\\paginator";
        return $view->render($templatesDir . '\\' . self::LISTING_HTML_TEMPLATE);
    }

    /**
     * Get the list of pages that will be displayed
     *
     * @param integer $span
     * @return array
     */
    private function _getPagesListed($span = 3)
    {
        $this->_calculateLimits();
        
        $i = 0;
        $j = 1; //minimum radius
        $pages = array($this->_currentPage); //put current page
        $count = min($this->_totalPages, (2 * $span) + 1) - 2;

        while ($i < $count) {
            if (($this->_currentPage - $j) > 0) {
                $pages[] = $this->_currentPage - $j;
                $i++;
            }
            if (($this->_currentPage + $j) < $this->_totalPages) {
                $pages[] = $this->_currentPage + $j;
                $i++;
            }
            $j++;
        }
        sort($pages);

        return $pages;
    }

    /**
     * Generate request link
     *
     * @param integer $page
     * @return string
     */
    public function generateRequestLink($page = null)
    {
        if(is_null($page)) {
            $page = $this->getPage();
        }

        if($this->_requestCfg['formatted']) {
            return sprintf($this->_requestCfg['url'], $page);
        }

        $queryString = ($this->queryString ? $this->queryString . '&amp;' : null);
        return "{$this->_requestCfg['url']}?{$queryString}pagina={$page}";
    }

    /**
     * Generate inter-sections
     *
     * @param integer $baseLimit
     * @param integer $upLimit
     * @return array
     */
    public function getInterSections($baseLimit, $upLimit)
    {
        $sections = array();
        if($baseLimit > $upLimit) {
            $tmp = $upLimit;
            $upLimit = $baseLimit;
            $baseLimit = $tmp;
        }

        for($i=$baseLimit+1; $i<$upLimit; $i++) {
            if($i % $this->seoSectionStep == 0) {
                $sections[] = $i;
            }
        }

        return $sections;
    }
}