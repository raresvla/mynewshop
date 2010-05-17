<?php
/**
 * MyShop_Solr
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @version 1.0
 */
class MyShop_Solr extends Apache_Solr_Service
{
    public $config;

    private $_query;
    private $_offset;
    private $_limit;
    private $_extraParams;
    private $_response;
    private $_results;

    /**
     * Create new Solr Service
     */
    public function  __construct()
    {
        $this->config = MyShop_Config::getInstance()->load('solr.ini');

        parent::__construct(
            $this->config->solr->host,
            $this->config->solr->port,
            $this->config->solr->path
        );
        if(!$this->ping()) {
            throw new Exception('Solr service not responding!');
        }
    }

    /**
     * Add/update a Socument to solr server
     *
     * @param array $params
     * @param boolean $commit
     * @param boolean $optimize
     * @return boolean
     */
    public function insert($params, $commit = true, $optimize = true)
    {
        $document = new Apache_Solr_Document();

        foreach($params as $key => $value) {
            if(is_array($value)) {
                foreach($value as $item) {
                    $document->setMultiValue($key, $item);
                }
            }
            else {
                $document->$key = $value;
            }
        }
        try {
            $this->addDocument($document);
            if($commit) {
                $this->commit();
            }
            if($optimize) {
                $this->optimize();
            }
        }
        catch(Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * This function returns the total number of elements to be paginated
     *
     * @return int
     */
    public function total()
    {
        $this->execute();
        $num  = $this->_results['response']['numFound'];
        
        return $num;
    }

    /**
     * Specify a where clause
     *
     * @param string $query
     * @return MyShop_Solr
     */
    public function where($query)
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * Specify a where clause
     *
     * @param string $query
     * @return MyShop_Solr
     */
    public function addWhere($query)
    {
        if(empty($this->_query)) {
            return $this->where($query);
        }
        
        $this->_query .= ' AND ' . $query;
        return $this;
    }

    /**
     * Specify and order clause
     *
     * @param string $order
     * @return MyShop_Solr
     */
    public function orderBy($order)
    {
        $this->_setExtraParam('sort', $order);
        return $this;
    }

    /**
     * This method appends 'limit' to query
     *
     * @param integer $limit
     * @return MyShop_Solr
     */
    public function limit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * This method applies 'offset' to query
     *
     * @param integer $offset
     * @return MyShop_Solr
     */
    public function offset($offset)
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * This method executes the query and returns current page results, as array
     *
     * @return array
     */
    public function fetchArray()
    {
        $this->_response = $this->search($this->_query, $this->_offset, $this->_limit, $this->_extraParams);
        if($this->_response->getHttpStatus() != 200) {
            throw new Exception('Solr service not responding');
        }

        $this->_results = json_decode($this->_response->getRawResponse(), true);
        return $this->_results['response']['docs'];
    }
}