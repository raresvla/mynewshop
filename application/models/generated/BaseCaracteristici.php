<?php

/**
 * BaseCaracteristici
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $categorie_id
 * @property integer $sectiune_id
 * @property string $caracteristica
 * @property integer $preview
 * @property Categorii $Categorii
 * @property SectiuniCaracteristici $SectiuniCaracteristici
 * @property Doctrine_Collection $ProduseCaracteristici
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseCaracteristici extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('caracteristici');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('categorie_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('sectiune_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('caracteristica', 'string', 100, array(
             'type' => 'string',
             'length' => 100,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('preview', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Categorii', array(
             'local' => 'categorie_id',
             'foreign' => 'id'));

        $this->hasOne('SectiuniCaracteristici', array(
             'local' => 'sectiune_id',
             'foreign' => 'id'));

        $this->hasMany('ProduseCaracteristici', array(
             'local' => 'id',
             'foreign' => 'id_caracteristica'));
    }
}