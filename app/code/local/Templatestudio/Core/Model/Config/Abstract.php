<?php
/**
 * @author TemplateStudio UK
 */
 
abstract class Templatestudio_Core_Model_Config_Abstract extends Mage_Core_Model_Config_Base
{
    /**
     * JS Config Options
     *
     * @var null|array
     */
    protected $_jsConfig;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct($this->_getConfigNode());
    }

    /**
     * Get config node
     *
     * @return Varien_Simplexml_Element
     */
    abstract protected function _getConfigNode();

    /**
     * Get helper
     *
     * @return Templatestudio_Core_Helper_Abstract
     */
    abstract protected function _getHelper();

    /**
     * Get config node
     *
     * @return Varien_Simplexml_Element
     */
    public function getJsConfigNode()
    {
        return $this->getNode('jsconfig');
    }

    /**
     * Retrieve js config
     * 
     * @param mixed $store Optional
     * @return Varien_Object
     */
    public function getJsConfig($store = NULL)
    {
        $store = Mage::app()->getStore($store);
        
        if (is_null($this->_jsConfig) OR ! array_key_exists($store->getId(), $this->_jsConfig))
        {
            if ( ! is_array($this->_jsConfig))
            {
                $this->_jsConfig = array();
            }

            $this->_jsConfig[$store->getId()] = new Varien_Object(
                $this->_getJsConfig($this->getJsConfigNode()->children(), $store)
            );
        }

        return $this->_jsConfig[$store->getId()];
    }

    /**
     * Retrieve js theme config
     *
     * @param Varien_Simplexml_Element $node
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    protected function _getJsConfig(Varien_Simplexml_Element $node, Mage_Core_Model_Store $store)
    {
        $jsConfig = array();
        foreach($node as $key => $config)
        {
            $ifconfig = $config->getAttribute('ifconfig');
            $ifvalue = $config->getAttribute('ifvalue');
            if ( ! empty($ifconfig))
            {
                if ( ! is_null($ifvalue))
                {
                    $ifseparator = $config->getAttribute('ifseparator');

                    if ( ! empty($ifseparator))
                    {
                        $values = explode($ifseparator, Mage::getStoreConfig($ifconfig, $store));
                        
                        if ( ! empty($values) AND ! in_array($ifvalue, $values))
                        {
                            continue;
                        }
                    }
                    elseif ($ifvalue != Mage::getStoreConfig($ifconfig, $store))
                    {
                        continue;
                    }
                }
                elseif (FALSE === Mage::getStoreConfigFlag($ifconfig, $store))
                {
                    continue;
                }
            }

            if (FALSE === $config->hasChildren())
            {
                $path = $config->__toString();
                $type = $config->getAttribute('type');

                $path = trim(preg_replace(array('/[^A-Za-z0-9 _\/]/', '/\/+/', '/_+/'), array('_', '/', '_'), $path), ' /_');
                if (empty($path))
                {
                    continue;
                }

                if (is_a($this->_getHelper(), 'Templatestudio_Core_Helper_Abstract'))
                {
                    $value = $this->_getHelper()->getConfigData($path, $store);
                }
                else
                {
                    $value = Mage::getStoreConfig($path, $store);
                }

                if ( ! empty($type) AND in_array($type, $this->_getDataTypes()))
                {
                    settype($value, $type);
                }

                $value = $this->_prepareJsConfig($key, $value, $node, $store);

                if ( ! is_null($value))
                {
                    $jsConfig[$key] = $value;
                }
            }
            else
            {
                $jsConfig[$key] = $this->_getJsConfig($config, $store);
            }
        }
        
        return $jsConfig;
    }

    /**
     * Prepare JS Config
     *
     * @param string $key
     * @param mixed $value
     * @param Varien_Simplexml_Element $node
     * @param Mage_Core_Model_Store $store
     * @return mixed
     */
    protected function _prepareJsConfig($key, $value, $node, $store)
    {
        return $value;
    }

    /**
     * Retrieve data types
     *
     * @return array
     */
    final protected function _getDataTypes()
    {
        $dataTypes = array('boolean', 'integer', 'double', 'string', 'array', 'object');
    
        if (version_compare(PHP_VERSION, '4.2.0', 'ge'))
        {
            $dataTypes = array_merge($dataTypes, array('bool', 'int', 'float', 'null'));
        }
    
        return $dataTypes;
    }
}