<?php
/**
 * @author TemplateStudio UK
 */
 
abstract class Templatestudio_Core_Helper_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * Section Name
     * 
     * @var string|null
     */
    protected $_section;

    /**
     * Get store config
     * 
     * @param string $node Optional
     * @param mixed $store Optional
     * @return string|array|null
     */
    public function getConfigData($node = NULL, $store = NULL)
    {
        if (1 < substr_count($node, '/'))
        {
            return Mage::getStoreConfig($node, $store);
        }
        
        if (is_null($this->_section))
        {
            return Mage::getStoreConfig($node, $store);
        }
        
        if ( ! is_null($node))
        {
            return Mage::getStoreConfig($this->_section . '/' . $node, $store);
        }
        
        return Mage::getStoreConfig($this->_section, $store);
    }
    
    /**
     * Get store config flag
     * 
     * @param string $node
     * @param mixed $store Optional
     * @return bool
     */
    public function getConfigFlag($node, $store = NULL)
    {
        if (1 < substr_count($node, '/'))
        {
            return Mage::getStoreConfigFlag($node, $store);
        }
        
        if (is_null($this->_section))
        {
            return Mage::getStoreConfigFlag($node, $store);
        }
        
        return Mage::getStoreConfigFlag($this->_section . '/' . $node, $store);
    }
    
    /**
     * Get section
     * 
     * @return string|null
     */
    public function getSection()
    {
        return $this->_section;
    }
}