<?php
/**
 */
class JudeteTable extends Doctrine_Table
{
    /**
     * Fetch all regions
     *
     * @return array
     */
    public function fetchAll()
    {
        $sql = $this->createQuery();
        $sql->select('judet');
        $sql->orderBy('judet asc');

        $regions = array();
        foreach($sql->fetchArray() as $item) {
            $item = $item['judet'];
            if(strpos($item, ':') !== false) {
                $pattern = '/^([^:]+):(.+)$/';
                preg_match($pattern, $item, $matches);
                foreach(explode(';', $matches[2]) as $subItem) {
                    $regions[$matches[1]][] = $subItem;
                }
            }
            else {
                $regions[] = $item;
            }
        }

        return $regions;
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