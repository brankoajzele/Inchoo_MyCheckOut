<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Block_Payment_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
    	parent::_construct();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('inchoo_mycheckout');
        
        $info = $helper->getPaymentProgressInformation();
        
        if (!empty($info)) {
            return $info;
        }
        
        return $this->htmlEscape($this->getMethod()->getTitle());
    }
}
