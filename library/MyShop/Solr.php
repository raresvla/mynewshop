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
    public $maxScore;

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
     * @param integer $maxRetiers
     * @return boolean
     */
    public function insert($params, $commit = true, $optimize = true, $maxRetries = 5)
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
            $addRetries = 0;
            try {
                addDocument:
                $this->addDocument($document);
            }
            catch(Exception $e) {
                if($addRetries == $maxRetries) {
                    throw $e;
                }
                $addRetries++;
                goto addDocument;
            }

            $commitRetries = 0;
            if($commit) {
                try {
                    doCommit:
                    $this->commit();
                }
                catch(Exception $e) {
                    if($commitRetries == $maxRetries) {
                        throw $e;
                    }
                    $commitRetries++;
                    goto doCommit;
                }
            }

            $optimizeRetries = 0;
            if($optimize) {
                try {
                    doOptimize:
                    $this->optimize();
                }
                catch(Exception $e) {
                    if($optimizeRetries == $maxRetries) {
                        throw $e;
                    }
                    $optimizeRetries++;
                    goto doOptimize;
                }
            }
        }
        catch(Exception $e) {
            throw $e;
            return false;
        }

        return true;
    }

    /**
     * Index product information
     *
     * @param integer $productId
     * @return boolean
     */
    public function index($productId)
    {
        $sql = Doctrine_Query::create();
        $sql->select('p.*, c.*');
        $sql->addSelect('g.foto, pm.pret_oferta, pc.*, car.*');
        $sql->from('Produse p');
        $sql->leftJoin('p.MainPhoto g WITH g.main = 1');
        $sql->leftJoin('p.Promotii pm WITH (pm.data_inceput <= DATE(NOW()) AND pm.data_sfarsit >= DATE(NOW()))');
        $sql->leftJoin('p.ProduseCaracteristici pc');
        $sql->leftJoin('p.Categorie c');
        $sql->innerJoin('pc.Caracteristici car WITH car.preview = 1');
        $sql->where('p.id = ?', $productId);
        $produs = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        if(empty($produs)) {
            throw new Zend_Exception("Cannot find product with ID: {$productId}!");
        }

        $data = array(
            'id' => $produs['id'],
            'producator' => $produs['marca'],
            'codProdus' => $produs['cod_produs'],
            'denumire' => $produs['denumire'],
            'imagine' => $produs['MainPhoto']['foto'],
            'categorie' => $produs['Categorie']['denumire'],
            'categorieId' => $produs['categorie_id'],
            'greutate' => $produs['greutate'],
            'pret' => $produs['pret'],
            'rating' => intval($produs['rating']),
            'nou' => $produs['noutati'],
            'recomandat' => $produs['recomandari'],
            'dataAdaugarii' => $produs['data_adaugare'] . 'T00:00:00Z',
            'stocDisponibil' => $produs['stoc_disponibil'],
            'stocRezervat' => $produs['stoc_rezervat']
        );
        foreach($produs['ProduseCaracteristici'] as $car) {
            $data['caracteristici'][] = implode('#', array(
                trim($car['Caracteristici']['caracteristica'], "\r\n"),
                trim($car['valoare'], "\r\n")
            ));
        }

        return $this->insert($data);
    }

    /**
     * This function returns the total number of elements to be paginated
     *
     * @return int
     */
    public function count()
    {
        $this->fetchArray();
        $num = $this->_results['response']['numFound'];
        
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
        if(isset($this->_results['response']['maxScore'])) {
            $this->maxScore = $this->_results['response']['maxScore'];
        }
        return $this->_results['response']['docs'];
    }

    /**
     * Enables faceting counts
     *
     * @return MyShop_Solr
     */
    public function turnFacetingOn()
    {
        $this->_setExtraParam('facet', 'true');
        return $this;
    }

    /**
     * No faceting returned in search response
     * 
     * @return MyShop_Solr
     */
    public function turnFacetingOff()
    {
        if(!empty($this->_extraParams['facet'])) {
            unset($this->_extraParams['facet']);
        }
        
        return $this;
    }

    /**
     * Returns counts for facet queries / fields
     *
     * @return array
     */
    public function getFacetCounts()
    {
        if(empty($this->_results['facet_counts'])) {
            return array();
        }

        return $this->_results['facet_counts'];
    }

    /**
     * Returns fields set for faceting counts and their corresponding counters
     * after query execution
     *
     * @return array
     */
    public function getFacetFields()
    {
        if(empty($this->_results['facet_counts']['facet_fields'])) {
            return array();
        }

        return $this->_results['facet_counts']['facet_fields'];
    }
    
    /**
     * Returns counter for facet field (only after query execution)
     *
     * @param string $field
     * @return int | null
     */
    public function getFacetCountsForField($field)
    {
        if(!isset($this->_results['facet_counts']['facet_fields'][$field])) {
            return null;
        }
        if(empty($this->_results['facet_counts']['facet_fields'][$field])) {
            return 0;
        }

        return $this->_results['facet_counts']['facet_fields'][$field];
    }

    /**
     * Append facet query
     *
     * @param string $field
     * @param string $query
     * @return string
     */
    public function addFacetQuery($field, $query)
    {
        if(empty($this->_extraParams['facet.query'])) {
            $this->_extraParams['facet.query'] = array();
        }

        $searchFacet = "$field:$query;";
        $this->_extraParams['facet.query'][] = $searchFacet;
        return $searchFacet;
    }

    /**
     * Sets one more field whose facet counters should be returned in query response after execution
     * should be called before query execution
     *
     * @param string $field
     * @return MyShop_Solr
     */
    public function addFacetField($field)
    {
        if(empty($this->_extraParams['facet.field'])) {
            $this->_extraParams['facet.field'] = array();
        }

        $this->_extraParams['facet.field'][] = "$field";
        return $this;
    }
    
    /**
     * Adds a filter query (used for filtering results of original query)
     *
     * @param string $query
     * @return MyShop_Solr
     */
    public function addFilterQuery($query)
    {
        if(empty($this->_extraParams['fq'])) {
            $this->_extraParams['fq'] = array();
        }
        
        $this->_extraParams['fq'][] = $query;
        return $this;
    }

    /**
     * Unsets all extra parameters (filter queries, facet counts and any other)
     *
     * @return MyShop_Solr
     */
    public function clearExtraParams()
    {
        $this->_extraParams = array();
        return $this;
    }

    /**
     * Removes all filter queries
     * 
     * @return MyShop_Solr
     */
    public function clearFilterQueries()
    {
        if(!empty($this->_extraParams['fq'])) {
            $this->_extraParams['fq'] = array();
        }
        return $this;
    }

    /**
     * Returns all filter queries added
     *
     * @return array | null
     */
    public function getFilterQueries()
    {
        if(!isset($this->_extraParams['fq'])) {
            return null;
        }

        return (array) $this->_extraParams['fq'];
    }

    /**
     * Set query extra param
     *
     * @param string $name
     * @param mixed $value
     */
    private function _setExtraParam($name, $value)
    {
        if(empty($this->_extraParams) || !is_array($this->_extraParams)) {
            $this->_extraParams = array();
        }
        
        $this->_extraParams[$name] = $value;
    }

    /**
     * Retreive query extra param
     *
     * @param string $name
     * @return mixed
     */
    private function _getExtraParam($name)
    {
        if(!isset($this->_extraParams[$name])) {
            return null;
        }

        return $this->_extraParams[$name];
    }

    /**
     * This param indicates the minimum counts for facet fields should be included in the response.
     *
     * @param int $minCount
     * @return MyShop_Solr
     */
    public function setFacetMincount($minCount)
    {
        $this->_extraParams['facet.mincount'] = $minCount;
        return $this;
    }

    /**
     * Set maximum number of constraint counts that should be returned for the facet fields.
     * A negative value means unlimited. Solr's default value is 100.
     *
     * @param int $facetLimit
     * @return MyShop_Solr
     */
    public function setFacetLimit($facetLimit)
    {
        $this->_extraParams['facet.limit'] = $facetLimit;
        return $this;
    }

    /**
     * Set return fields list
     *
     * @param array | string $fields
     */
    public function setReturnFields($fields)
    {
        if(is_array($fields)) {
            $fields = implode(',', $fields);
        }

        return $this->_setExtraParam('fl', $fields);
    }
}