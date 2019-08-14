<?php

/**
 * @author Templatestudio UK
 */

class Templatestudio_Core_Model_Extension
{
    /**
     * Default check frequency
     */
    const DEFAULT_CHECK_FREQUENCY = 86400;

    /**
     * Cache ID
     */
    const CACHE_KEY = 'templatestudio_extensions';

    /**
     * Check frequency
     *
     * @return string|int|null
     */
    protected $_checkFrequency;

    /**
     * Retrieve extensions url
     *
     * @return string|null
     */
    public function getUrl()
    {
        return Mage::helper('tscore')->getExtensionUrl();
    }

    /**
     * Retrieve check frequency
     *
     * @return int
     */
    public function getCheckFrequency()
    {
        if ( ! is_null($this->_checkFrequency))
        {
            return $this->_checkFrequency;
        }

        return self::DEFAULT_CHECK_FREQUENCY;
    }

    /**
     * Retrieve check frequency
     *
     * @param int $frequency
     * @return Templatestudio_Core_Model_Extension
     */
    public function setCheckFrequency($frequency)
    {
        $this->_checkFrequency = abs(intval($frequency));
        
        return $this;
    }

    /**
     * Check feed for modification
     *
     * @return Templatestudio_Core_Model_Extension
     */
    public function checkUpdate()
    {
        if ( ! (Mage::app()->loadCache(self::CACHE_KEY))
            OR (time() - $this->getLastUpdate()) > $this->getCheckFrequency())
        {
            $data = $this->getData();

            if ( ! empty($data) AND is_a($data, 'Varien_Simplexml_Element'))
            {
                $extensions = array();
                foreach ($data->children() as $extension)
                {
                    $extensions[$extension->name->__toString()] = $extension->asCanonicalArray();
                }

                Mage::app()->saveCache(serialize($extensions), self::CACHE_KEY);
                $this->setLastUpdate();
            }
        }
            
        return $this;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return Mage::app()->loadCache('templatestudio_extensions_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return Templatestudio_Core_Model_Extension
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'templatestudio_extensions_lastcheck');
        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement
     */
    public function getData()
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout' => 2
        ));

        $curl->write(Zend_Http_Client::POST, $this->getUrl(), Zend_Http_Client::HTTP_0, array('Content-Type: multipart/form-data'), array('xmldata' => $this->_prepareXml()));
        $data = $curl->read();

        if (FALSE === $data)
        {
            return FALSE;
        }

        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);

        $curl->close();

        try
        {
            $xml = new Varien_Simplexml_Element($data);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            return FALSE;
        }

        return $xml;
    }

    /**
     * Retrieve installed extensions
     *
     * @return array
     */
    protected function _getExtensions()
    {
        return Mage::helper('tscore')->getExtensions('version');
    }

    /**
     * Prepare XML
     *
     * @return string|Varien_Simplexml_Element
     */
    protected function _prepareXml($asXML = TRUE)
    {
        $xml = new Varien_Simplexml_Element('<?xml version="1.0" encoding="UTF-8"?><customer/>');
        
        $xml->addChild('base_url', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
        $xml->addChild('server_addr', Mage::helper('core/http')->getServerAddr());
        
        $extensions = $xml->addChild('modules');
        foreach ($this->_getExtensions() as $moduleName => $version)
        {
            $extension = $extensions->addChild($moduleName);
            $extension->addChild('name', $moduleName);
            $extension->addChild('version', $version);
            $extension->addChild('enabled', Mage::helper('tscore')->isModuleOutputEnabled($moduleName));

            $config = new Varien_Object();
            $default = $extension->asCanonicalArray();
            Mage::dispatchEvent('tscore_extension_update_xml', array('module' => $moduleName, 'config' => $config, 'default' => $default));
            Mage::dispatchEvent('tscore_extension_update_xml_' . strtolower($moduleName), array('config' => $config, 'default' => $default));

            if ( ! $config->isEmpty())
            {
                $additionalData = new Varien_Simplexml_Element($config->toXml());
                $extension->extend($additionalData);
            }
        }

        if (TRUE === $asXML)
        {
            return $xml->asXML();
        }

        return $xml;
    }
}