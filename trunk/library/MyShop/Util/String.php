<?php
/**
 * String utilities
 *
 * @author Rares Vlasceanu
 * @package MyShop
 * @subpackage Util
 * @version 1.0
 */
class MyShop_Util_String
{
    protected static $_instances;
    private $_string;

    /**
     * Get instance
     *
     * @param string $string
     * @return MyShop_Util_String
     */
    public static function getInstance($string)
    {
        if(!isset(self::$_instances[$string]) || !self::$_instances[$string] instanceof self) {
            self::$_instances[$string] = new self($string);
        }

        return self::$_instances[$string];
    }

    /**
     * Class constructor
     *
     * @param string $string
     * @access protected
     */
    protected function  __construct($string)
    {
        $this->_string = $string;
    }

    /**
     * Escape url
     *
     * @return MyShop_Util_String
     */
    public function escapeUrl()
    {
        $this->removeDiacritics();

        $pattern = '/[^a-zA-Z0-9]/';
        $this->_string = preg_replace($pattern, ' ', strtolower($this->_string));
        $this->_string = str_replace(array('  ', ' '), array('', '-'), $this->_string);

        return $this;
    }

    /**
     * Remove string diacritics
     * 
     * @return MyShop_Util_String
     */
    public function removeDiacritics()
    {
        $diacritics = array(
            'ă' => 'a',
            'Ă' => 'A',
            'î' => 'i',
            'Î' => 'I',
            'â' => 'a',
            'Â' => 'A',
            'ş' => 's',
            'Ş' => 'S',
            'ţ' => 't',
            'Ţ' => 'T'
        );
        $this->_string = str_replace(array_keys($diacritics), array_values($diacritics), $this->_string);

        return $this;
    }

    /**
     * ToString Magic method
     *
     * @return string
     */
    public function  __toString()
    {
        return $this->_string;
    }
}