<?php

/**
 * @author TemplateStudio UK
 */

abstract class Templatestudio_Core_Block_Adminhtml_System_Config_Form_Field_Array_Abstract extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
    
        if ( ! empty($html))
        {
            if (is_null($this->_frameOpenTag))
            {
                $this->setFrameTags('div');
            }
    
            $html = '<' . $this->_frameOpenTag . ' id="' . $this->getElement()->getId() . '">'
                . $html . '</' . $this->_frameCloseTag . '>';
        }
    
        return $html;
    }
}