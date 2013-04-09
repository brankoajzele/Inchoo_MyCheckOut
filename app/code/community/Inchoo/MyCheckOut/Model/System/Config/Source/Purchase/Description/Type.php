<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Model_System_Config_Source_Purchase_Description_Type extends Mage_Core_Helper_Abstract
{
    const TYPE_STORE = 'store';
    const TYPE_CART_ITEM_NAME = 'cart_item_name';
    const TYPE_CART_ITEM_SKU = 'cart_item_sku';
    
    public function toOptionArray()
    {
        return array(
            
            array(
                'value' => self::TYPE_STORE,
                'label' => Mage::helper('inchoo_mycheckout')->__('Store Name')
            ),
            
            array(
                'value' => self::TYPE_CART_ITEM_NAME,
                'label' => Mage::helper('inchoo_mycheckout')->__('Cart Item Name(s) (comma-separated values)')
            ),
            
            array(
                'value' => self::TYPE_CART_ITEM_SKU,
                'label' => Mage::helper('inchoo_mycheckout')->__('Cart Item SKU(s) (comma-separated values)')
            ),            
            
        );
    }    
}
