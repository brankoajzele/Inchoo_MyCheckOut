<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Model_System_Config_Source_Trantype extends Mage_Core_Helper_Abstract
{
    const TYPE_AUTHORIZE_ONLY = 'preauth';
    const TYPE_AUTHORIZE_AND_CAPTURE = 'auth';
    
    public function toOptionArray()
    {
        return array(
            
            array(
                'value' => self::TYPE_AUTHORIZE_ONLY, 
                'label' => Mage::helper('inchoo_mycheckout')->__('Authorize Only')
            ),
            
            array(
                'value' => self::TYPE_AUTHORIZE_AND_CAPTURE, 
                'label' => Mage::helper('inchoo_mycheckout')->__('Authorize and Capture')
            ),
            
        );
    }    
}
