<?php

/**
 * BaseOptiuni
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $denumire_ro
 * @property string $denumire_en
 * @property Doctrine_Collection $OptiuniProduse
 * @property Doctrine_Collection $ValoriOptiuni
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseOptiuni extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('optiuni');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('denumire_ro', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('denumire_en', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '',
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('OptiuniProduse', array(
             'local' => 'id',
             'foreign' => 'optiune_id'));

        $this->hasMany('ValoriOptiuni', array(
             'local' => 'id',
             'foreign' => 'id_optiune'));
    }
}