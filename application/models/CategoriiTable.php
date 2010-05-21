<?php
/**
 */
class CategoriiTable extends Doctrine_Table
{
    private $_storage;
    const CATALOG_ROUTE_NAME = 'catalog';

    /**
     * Generate category bread crumbs (full details path)
     *
     * @param integer $categoryId
     * @param array $cPath
     * @return array
     */
    public function getBreadCrumbsTo($categoryId, $cPath = null)
    {
        if(is_null($cPath)) {
            $cPath = $this->getCategoryPath($categoryId);
            return $this->_buildBreadCrumbs($cPath);
        }

        $sql = Doctrine_Query::create();
        $sql->select('c.*');
        $sql->from("{$this->getComponentName()} c INDEXBY id");
        $sql->whereIn('c.id', $cPath);
        $data = $sql->fetchArray();

        $breadCrumbs = array();
        foreach($data as $cid => &$category) {
            $breadCrumbs[array_search($cid, $cPath)] = array(
                'denumire' => $category['denumire'],
                'id' => $cid
            );
        }

        return $this->_buildBreadCrumbs($breadCrumbs);
    }

    /**
     * Build the array of bread crumbs
     *
     * @param array $cPathData
     * @return array
     */
    private function _buildBreadCrumbs($cPathData)
    {
        if(empty($cPathData)) {
            return array();
        }
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $ids = array();
        $names = array();
        $breadcrumbs = array();
        foreach($cPathData as $key => $item) {
            if(is_array($item)) {
                $id = $item['id'];
                $name = $item['denumire'];
            }
            else {
                $id = $key;
                $name = $item;
            }
            $ids[] = $id;
            $names[] = (string) MyShop_Util_String::getInstance($name)->escapeUrl();
            
            $cNamesPart = implode('/', $names);
            $cPathPart = implode('_', $ids);
            $url = $router->assemble(array($cNamesPart, $cPathPart, 1), self::CATALOG_ROUTE_NAME, false, false);

            $breadcrumbs[$url] = $name;
        }

        return $breadcrumbs;
    }

    /**
     * Build category path
     *
     * @param integer $categoryId
     * @param integer $depth
     * @return array
     */
    public function getCategoryPath($categoryId, $depth = 2)
    {
        if(!isset($this->_storage[$categoryId])) {
            $sql = Doctrine_Query::create();
            $sql->select('c.*');
            $sql->from($this->getComponentName() . ' c');
            $sql->where('c.id = ?', $categoryId);

            $i = 0;
            $parentAlias = 'c';
            while($i < $depth) {
                $newParentAlias = $parentAlias . $i;
                $sql->leftJoin("{$parentAlias}.Parent {$newParentAlias}");
                $sql->addSelect("{$newParentAlias}.*");
                $i++;
            }

            $category = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
            if(empty($category)) {
                return array();
            }

            $cPath = array();
            while($category) {
                array_unshift(
                    $cPath,
                    array(
                        'denumire' => $category['denumire'],
                        'id' => $category['id']
                    )
                );
                if(isset($category['Parent'])) {
                    $category = &$category['Parent'];
                }
                else {
                    $category = false;
                }
            }

            $this->_storage[$categoryId] = $cPath;
        }

        return $this->_storage[$categoryId];
    }
}