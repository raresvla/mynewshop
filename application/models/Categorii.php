<?php

/**
 * Categorii
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Categorii extends BaseCategorii
{
    public function setUp()
    {
        parent::setUp();

        $this->hasOne('Categorii as Parent', array(
             'local' => 'parent_id',
             'foreign' => 'id')
        );
    }
}