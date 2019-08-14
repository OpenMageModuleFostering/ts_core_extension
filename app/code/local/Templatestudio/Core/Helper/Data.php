<?php

/**
 * @author TemplateStudio UK
 */
 
class Templatestudio_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Developer URL
     */
    const VENDOR_URL = 'http://www.templatestudio.com/';
    
    /**
     * Vendor URLs
     */
    protected $_vendorUrl;

    /**
     * Magento platform/edition abbreviations
     *
     * @return array|null
     */
    static protected $_platforms;
    
    /**
     * Extensions list
     *
     * @return array|null
     */
    protected $_extensions;
    
    /**
     * Retrieve Developer/Vendor URL
     * 
     * @return string
     */
    public function getDeveloperUrl()
    {
        $vendorUrl = $this->getUrl('vendor');

        if (! empty($vendorUrl))
        {
            return $vendorUrl;
        }
        
        return self::VENDOR_URL;
    }
    
    /**
     * Retrieve notification feed URL
     *
     * @return string|null
     */
    public function getNotificationFeedUrl()
    {
        return $this->getUrl('notification');
    }
    
    /**
     * Retrieve extensions URL
     *
     * @return string|null
     */
    public function getExtensionUrl()
    {
        return $this->getUrl('extension');
    }
    
    /**
     * Retrieve quote URL
     *
     * @return string
     */
    public function getQuoteUrl()
    {
        $quoteUrl = $this->getUrl('quote');
        
        if (empty($quoteUrl))
        {
            $quoteUrl = self::VENDOR_URL;
        }
        
        return $quoteUrl;
    }
    
    /**
     * Retrieve vendor URLs
     *
     * @return array|null
     */
    public function getVendorUrls()
    {
        if (is_null($this->_vendorUrl))
        {
            $this->_vendorUrl = Mage::getConfig()->getNode('global/tscore/url')
                ->asCanonicalArray();
            
            if ( ! empty($this->_vendorUrl) AND is_array($this->_vendorUrl))
            {
                foreach ($this->_vendorUrl as $type => $url)
                {
                    $url = parse_url($url);
                    
                    if ( ! $url OR ! isset($url['scheme']))
                    {
                        $this->_vendorUrl[$type] = 'http://' . $this->_vendorUrl[$type];
                    }
                }
            }
        }
        
        return $this->_vendorUrl;
    }
    
    /**
     * Retrieve url
     *
     * @param string $type
     * @return null|string
     */
    public function getUrl($type)
    {
        $this->getVendorUrls();
        
        if (is_array($this->_vendorUrl) AND key_exists($type, $this->_vendorUrl))
        {
            return $this->_vendorUrl[$type];
        }
        
        return NULL;
    }
    
    /**
     * Retrieve installed extensions
     *
     * @param string|array|null $field
     * @return array
     */
    public function getExtensions($field = NULL)
    {
        if (is_null($this->_extensions))
        {
            $modules = Mage::getConfig()->getNode('modules')->asArray();
            $namespace = strstr(get_class($this), '_', TRUE);
            $coreModule = $this->_getModuleName();
            $this->_extensions = array();
    
            foreach ($modules as $moduleName => $moduleConfig)
            {
                if ($namespace === strstr($moduleName, '_', TRUE)
                    AND $moduleName !== $coreModule)
                {
                    $this->_extensions[$moduleName] = $moduleConfig;
                }
            }
        }
        
        if ( ! empty($field) AND ! is_bool($field) AND ! is_resource($field))
        {
            $extensions = array();
            
            if ( ! is_scalar($field))
            {
                $field = array_fill_keys((array) $field, NULL);
            }

            foreach ($this->_extensions as $moduleName => $moduleConfig)
            {
                if (is_string($field))
                {
                    $extensions[$moduleName] = isset($moduleConfig[$field]) ? $moduleConfig[$field] : NULL; 
                }
                else
                {
                    $extensions[$moduleName] = array_merge($field, array_intersect_key($moduleConfig, $field));
                }
            }

            return $extensions;
        }
    
        return $this->_extensions;
    }
    
    /**
     * Retrieve platform/edition abbreviations
     *
     * @return array
     */
    static public function getAvailablePlatforms()
    {
        if (is_null(self::$_platforms))
        {
            self::$_platforms = array();
            
            if (class_exists('ReflectionClass'))
            {
                $reflection = new ReflectionClass('Mage');
                foreach($reflection->getConstants() as $const => $value)
                {
                    if ('EDITION' === strtok($const, '_'))
                    {
                        $edition = ltrim(strstr($const, '_'), '_');
                        $platform = strtolower($edition[0] . $const[0]);

                        self::$_platforms[$platform] = $value;
                    }
                }
            }
        }
    
        return self::$_platforms;
    }
    
    /**
     * Get current platform abbreviation
     *
     * @return string|bool
     */
    static public function getCurrentPlatform()
    {
        return array_search(Mage::getEdition(), self::getAvailablePlatforms());
    }
}