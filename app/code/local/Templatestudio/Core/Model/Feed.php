<?php
/**
 * @author Templatestudio UK
 */

class Templatestudio_Core_Model_Feed extends Mage_AdminNotification_Model_Feed
{    
    const XML_PATH_NOTIFY = 'templatestudio/feed/notify';

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl))
        {
            $feedUrl = Mage::helper('tscore')->getNotificationFeedUrl();
            if ( ! empty($feedUrl))
            {
                $feedUrl = preg_replace('#^https?:\/\/#i', '', $feedUrl);
                $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://') . $feedUrl;
            }
        }

        return $this->_feedUrl;
    }

    public function observe()
    {
        if (TRUE === Mage::getStoreConfigFlag(self::XML_PATH_NOTIFY))
        {
            $this->checkUpdate();         
        }
    }
}