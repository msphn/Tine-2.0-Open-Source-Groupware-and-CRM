<?php
/**
 * @package     Tinebase
 * @subpackage  Config
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2007-2016 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 */

/**
 * Zend_Config like access to array data
 * 
 * @package     Tinebase
 * @subpackage  Config
 */
class Tinebase_Config_Struct extends ArrayObject
{
    protected $_struct;
    protected $_appName;

    /** TODO struct should be mandatory at some point */
    public function __construct($data = array(), $struct = null, $appName = null)
    {
        parent::__construct($data);

        $this->_struct = $struct;
        $this->_appName = $appName;
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param  string $_name
     * @param  mixed $_default may not be NULL, if NULL given, it will be ignored, maybe you will get NULL, maybe not!
     * @return mixed
     * @throws Tinebase_Exception_InvalidArgument
     */
    public function get($_name, $_default = null)
    {
        /** TODO struct should be mandatory at some point, remove null check */
        if (null !== $this->_struct && !isset($this->_struct[$_name])) {
            throw new Tinebase_Exception_InvalidArgument($_name . ' is not a valid config key for this config struct: ' . print_r($this->_struct, true));
        }

        if (!$this->offsetExists($_name)) {
            /** TODO struct should be mandatory at some point, remove null check */
            if (null === $_default && null !== $this->_struct && isset($this->_struct[$_name]['default'])) {

                return Tinebase_Config_Abstract::rawToConfig($this->_struct[$_name]['default'], $this->_struct[$_name], $this->_appName);
            }
            return Tinebase_Config_Abstract::rawToConfig($_default, (null!==$this->_struct?$this->_struct[$_name]:null), $this->_appName);
        }

        $return = $this[$_name];

        // in case we lazy loaded already
        if (is_object($return)) {
            return $return;
        }

        /** TODO struct should be mandatory at some point, remove null check */
        if (null !== $this->_struct) {

            // type convert
            $return = Tinebase_Config_Abstract::rawToConfig($return, $this->_struct[$_name], $this->_appName);
            $this[$_name] = $return;

        /** TODO struct should be mandatory at some point, remove this elseif */
        } elseif (is_array($return)) {
            $return = new Tinebase_Config_Struct($return, (null !== $this->_struct && isset($this->_struct[$_name]['content']) ?
                $this->_struct[$_name]['content'] : null));
            $this[$_name] = $return;
        }

        return $return;
    }
    
    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = (array) $this;

        if (null !== $this->_struct) {
            foreach ($this->_struct as $key => $value) {
                if (isset($array[$key])) {
                    if (is_object($array[$key]) && method_exists($array[$key], 'toArray')) {
                        $array[$key] = $array[$key]->toArray();
                    }
                } elseif(isset($this->_struct[$key]['default'])) {
                    $array[$key] = Tinebase_Config_Abstract::rawToConfig($this->_struct[$key]['default'], $this->_struct[$key], $this->_appName);
                }
            }
        } else {
            foreach ($array as $key => $value) {
                if (is_object($value) && method_exists($value, 'toArray')) {
                    $array[$key] = $value->toArray();
                }
            }
        }

        return $array;
    }
    
    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $_name
     * @return mixed
     */
    public function __get($_name)
    {
        return $this->get($_name);
    }
    
    /**
     * Support isset() overloading on PHP 5.1
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this[$name]) || (null !== $this->_struct && isset($this->_struct[$name]) && isset($this->_struct[$name]['default']))) {
            return true;
        }
        return false;
    }
}