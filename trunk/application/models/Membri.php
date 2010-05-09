<?php

/**
 * Membri
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Membri extends BaseMembri
{
    public $notValidField;
    public $duplicateField;
    
    /**
     * User data validation
     *
     * @param boolean $update
     * @return boolean
     */
    public function validData($update = false)
    {
        $name = new Zend_Validate_Regex('/^[a-zA-ZăîâşţĂÎÂŞŢ-]+$/');
        $length3 = new Zend_Validate_StringLength(array('min' => 3));
        $length6 = new Zend_Validate_StringLength(array('min' => 6));

        $vChainUsername = new Zend_Validate();
        $vChainUsername->addValidator($length3);
        $vChainUsername->addValidator(new Zend_Validate_Regex('/^[a-zA-Z][a-zA-Z0-9_]+$/i'));
        if(!$update) {
            $vChainUsername->addValidator(new MyShop_Validator_NoRecordExists($this->getTable(), 'username'));
        }

        $vChainEmail = new Zend_Validate();
        $vChainEmail->addValidator(new Zend_Validate_EmailAddress());
        if(!$update || array_key_exists('email', $this->getModified())) {
            $vChainEmail->addValidator(new MyShop_Validator_NoRecordExists($this->getTable(), 'email'));
        }

        $vChainRealName = new Zend_Validate();
        $vChainRealName->addValidator($length3);
        $vChainRealName->addValidator($name);

        $toValidate = array(
            'username' => $vChainUsername,
            'email' => $vChainEmail,
            'adresare' => new Zend_Validate_InArray(array('Domnul', 'Doamna')),
            'nume' => $vChainRealName,
            'prenume' => $vChainRealName
        );
        if(!$this->id) {
            $toValidate['password'] = $length6;
        }

        $valid = true;
        foreach($toValidate as $field => $validator) {
            $valid = $validator->isValid($this->$field);
            if(!$valid) {
                $errors = $validator->getErrors();
                if(in_array('duplicate', $errors)) {
                    $this->duplicateField = $field;
                }
                else {
                    $this->notValidField = $field;
                }
                break;
            }
        }

        return $valid;
    }
    
    /**
     * Try to login using provided credentials
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function login($username, $password)
    {
        if(empty($username)) {
            return false;
        }
        $user = $this->getTable()->findOneBy('username', $username);
        if(!$user) {
            return false;
        }

        $check = $user->password == $password;
        if($check) {
            $data = $user->toArray();
            $this->fromArray($data);
            $this->assignIdentifier($user->identifier());
        }

        return $check;
    }

    /**
     * Save record information
     *
     * @param Doctrine_Connection $conn
     * @return void
     */
    public function save(Doctrine_Connection $conn = null)
    {
        if(array_key_exists('password', $this->getModified())) {
            $this->password = md5($this->password);
        }
        if(!$this->id) {
            $this->data_inregistrarii = new Doctrine_Expression('NOW()');
        }

        return parent::save($conn);
    }
}