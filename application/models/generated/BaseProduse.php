<?php

/**
 * BaseProduse
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $categorie_id
 * @property integer $furnizor_id
 * @property string $marca
 * @property string $cod_produs
 * @property string $denumire
 * @property string $descriere
 * @property decimal $pret
 * @property decimal $pret_oferta
 * @property decimal $greutate
 * @property integer $stoc_disponibil
 * @property integer $stoc_rezervat
 * @property date $data_adaugare
 * @property timestamp $ultima_modificare
 * @property integer $rating
 * @property integer $afisat
 * @property integer $noutati
 * @property integer $recomandari
 * @property integer $oferta
 * @property Categorii $Categorii
 * @property Furnizori $Furnizori
 * @property Doctrine_Collection $CartTrack
 * @property Doctrine_Collection $ComentariiProduse
 * @property Doctrine_Collection $Facturi
 * @property Doctrine_Collection $GalerieFoto
 * @property Doctrine_Collection $OptiuniProduse
 * @property Doctrine_Collection $ProduseCaracteristici
 * @property Doctrine_Collection $ProduseFavorite
 * @property Doctrine_Collection $ProduseRating
 * @property Doctrine_Collection $Promotii
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseProduse extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('produse');
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
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('furnizor_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('marca', 'string', 100, array(
             'type' => 'string',
             'length' => 100,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('cod_produs', 'string', 100, array(
             'type' => 'string',
             'length' => 100,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('denumire', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('descriere', 'string', null, array(
             'type' => 'string',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('pret', 'decimal', 8, array(
             'type' => 'decimal',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0.00',
             'notnull' => true,
             'autoincrement' => false,
             'scale' => '2',
             ));
        $this->hasColumn('pret_oferta', 'decimal', 8, array(
             'type' => 'decimal',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0.00',
             'notnull' => true,
             'autoincrement' => false,
             'scale' => '2',
             ));
        $this->hasColumn('greutate', 'decimal', 8, array(
             'type' => 'decimal',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0.00',
             'notnull' => true,
             'autoincrement' => false,
             'scale' => '2',
             ));
        $this->hasColumn('stoc_disponibil', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('stoc_rezervat', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('data_adaugare', 'date', null, array(
             'type' => 'date',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0000-00-00',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('ultima_modificare', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0000-00-00 00:00:00',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('rating', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('afisat', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '1',
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('noutati', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('recomandari', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('oferta', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => false,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Categorii', array(
             'local' => 'categorie_id',
             'foreign' => 'id'));

        $this->hasOne('Furnizori', array(
             'local' => 'furnizor_id',
             'foreign' => 'id'));

        $this->hasMany('CartTrack', array(
             'local' => 'id',
             'foreign' => 'id_produs'));

        $this->hasMany('ComentariiProduse', array(
             'local' => 'id',
             'foreign' => 'produs_id'));

        $this->hasMany('Facturi', array(
             'local' => 'id',
             'foreign' => 'produs_id'));

        $this->hasMany('GalerieFoto', array(
             'local' => 'id',
             'foreign' => 'produs_id'));

        $this->hasMany('OptiuniProduse', array(
             'local' => 'id',
             'foreign' => 'id_produs'));

        $this->hasMany('ProduseCaracteristici', array(
             'local' => 'id',
             'foreign' => 'id_produs'));

        $this->hasMany('ProduseFavorite', array(
             'local' => 'id',
             'foreign' => 'produs_id'));

        $this->hasMany('ProduseRating', array(
             'local' => 'id',
             'foreign' => 'id_produs'));

        $this->hasMany('Promotii', array(
             'local' => 'id',
             'foreign' => 'id_produs'));
    }
}