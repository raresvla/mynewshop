<?php
/**
 * Check for duplicate records
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage Validator
 */
class MyShop_Validator_NoRecordExists extends Zend_Validate_Abstract
{
    /**
     * @var Doctrine_Table
     */
    private $_dataModel;
    private $_field;

    /**
     * Class constructor
     *
     * @param Doctrine_Table $dataModel
     * @param string $fieldToCheck
     */
    public function __construct($dataModel, $fieldToCheck = null)
    {
        if(!$dataModel instanceof Doctrine_Table) {
            throw new Zend_Exception('Model must be an instance of Doctrine_Table!');
        }

        $this->_dataModel = $dataModel;
        $this->_field = $fieldToCheck;
    }

    /**
     * Set Field to check
     *
     * @param string $fieldToCheck
     */
    public function setField($fieldToCheck)
    {
        $this->_field = $field;
    }

    /**
     * Return check field
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Validator test method
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        if(empty($this->_field)) {
            throw new Zend_Exception('Field to check cannot be empty!');
        }

        $record = $this->_dataModel->findOneBy($this->_field, $value, Doctrine::HYDRATE_NONE);
        if($record) {
            $this->_error('duplicate');
            return false;
        }

        return true;
    }
}