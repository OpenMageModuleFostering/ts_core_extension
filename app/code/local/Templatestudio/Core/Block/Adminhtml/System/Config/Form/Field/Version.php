<?php

/**
 * @author TemplateStudio UK
 */

class Templatestudio_Core_Block_Adminhtml_System_Config_Form_Field_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Get element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setReadonly(TRUE, TRUE)
            ->setValue($this->_getVersion());

        if ('note' === $element->getType())
        {
            $element->setText(
                $element->getValue()
            );
        }
        
        return parent::_getElementHtml($element);
    }
    
    protected function _getVersion()
    {
        return Mage::getConfig()
            ->getModuleConfig($this->getModuleName())
            ->version
            ->__toString();
    }
}