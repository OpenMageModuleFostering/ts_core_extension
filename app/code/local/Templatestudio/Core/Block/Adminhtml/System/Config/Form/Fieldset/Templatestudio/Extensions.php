<?php

/**
 * @author Templatestudio UK
 */

class Templatestudio_Core_Block_Adminhtml_System_Config_Form_Fieldset_Templatestudio_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{    
    /**
     * Render element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $modules = $this->helper('tscore')->getExtensions();
        $modulesData = @unserialize(Mage::app()->loadCache(Templatestudio_Core_Model_Extension::CACHE_KEY));        
        $fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');

        $fieldRenderer->setForm($element->getForm());
        $fieldRenderer->setConfigData($element->getConfigData());
        
        if ( ! empty($modules))
        {
            foreach ($modules as $moduleName => $moduleConfig)
            {
                $label = ltrim(strstr($moduleName, '_'), '_');
                $version = ! empty($moduleConfig['version']) ? $moduleConfig['version'] : '&mdash;';
                $hasUpdate = FALSE;
                $warning = FALSE;
                
                if (is_array($modulesData) AND key_exists($moduleName, $modulesData))
                {
                    $module = $modulesData[$moduleName];
                
                    if ( ! empty($module['display_name']))
                    {
                        $label = $module['display_name'];
                    }
                    elseif ( ! empty($module['name']))
                    {
                        $label = $module['name'];
                    }
                
                    if ( ! empty($module['url']))
                    {
                        $label = '<a href="' . $module['url'] . '" title="' . $label . '" onclick="this.target=\'_blank\'">' . $label . '</a>';
                    }
                    elseif ($this->helper('tscore')->getDeveloperUrl())
                    {
                        $label = '<a href="' . $this->helper('tscore')->getDeveloperUrl() . '" title="' . $label . '" onclick="this.target=\'_blank\'">' . $label . '</a>';
                    }
                
                    if ( ! empty($moduleConfig['version']) AND ! empty($module['version']))
                    {
                        $hasUpdate = version_compare($moduleConfig['version'], $module['version'], 'gt');
                    }
                
                    if ( ! empty($modulConfig['platform']))
                    {
                        $warning = $this->helper('tscore')->getCurrentPlatform() !== strtolower(trim($modulConfig['platform']));
                    }
                }
                elseif ($this->helper('tscore')->getDeveloperUrl())
                {
                    $label = '<a href="' . $this->helper('tscore')->getDeveloperUrl() . '" title="' . $label . '" onclick="this.target=\'_blank\'">' . $label . '</a>';
                }
                
                if (TRUE === $warning)
                {
                    $labelNotification = '<img src="' . $this->getSkinUrl('templatestudio/images/bad.gif') . '" alt="' . $this->__('Wrong extension platform') . '" title="' . $this->__('Wrong extension platform') . '"/>';
                }
                else
                {
                    $labelNotification = '<img src="' . $this->getSkinUrl('templatestudio/images/ok.gif') . '" alt="' . $this->__('Installed') . '" title="' . $this->__('Installed') . '"/>';
                }
                
                if (TRUE === $hasUpdate)
                {
                    $labelNotification .= '<img src="' . $this->getSkinUrl('templatestudio/images/update.gif') . '" alt="' . $this->__('Update available') . '" title="' . $this->__("Update available") . '"/>';
                }
                
                $element->addField($moduleName, 'note', array(
                    'label' => $labelNotification . $label,
                    'text' => $version
                ))
                ->setRenderer($fieldRenderer);
            }
        }
        else
        {
            $element->addField('no-extensions', 'note', array(
                'label' => '',
                'text' => $this->__('There are no TemplateStudio extensions installed.')
            ))
            ->setRenderer($fieldRenderer);
        }
        
        return parent::render($element);
    }
    
    /**
     * Return header html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        $html = parent::_getHeaderHtml($element);

        $html .= '
        <a id="templatestudio-core-quote" href="' . $this->helper('tscore')->getQuoteUrl() . '" title="' . $this->__('Get in touch') . '" target="_blank">
            <img src="' . $this->getSkinUrl('templatestudio/images/templatestudio-quote.jpg') . '" alt="' . $this->__('Get in touch') . '" />
        </a>';

        return $html;
    }
}