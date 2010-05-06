<?php
/**
 */
class ProduseTable extends Doctrine_Table
{
    /**
     * Organize product description in subsections
     *
     * @param array $description
     */
    public function organizeDescription(&$description)
    {
        $sections = array();
        foreach($description as &$item) {
            $section = $item['Caracteristici']['SectiuniCaracteristici']['sectiune'];
            $sections[$section][] = array(
                'name' => $item['Caracteristici']['caracteristica'],
                'value' => $item['valoare']
            );
        }

        $description = $sections;
    }
}