<?php

/**
 * BaseFurnizori
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $denumire
 * @property Doctrine_Collection $Produse
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseFurnizori extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('furnizori');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('denumire', 'string', 100, array(
             'type' => 'string',
             'length' => 100,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Produse', array(
             'local' => 'id',
             'foreign' => 'furnizor_id'));
    }
}