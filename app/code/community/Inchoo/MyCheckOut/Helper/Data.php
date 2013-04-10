<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Helper_Data extends Mage_Payment_Helper_Data
{
    const XML_PATH_ACTIVE                   = 'payment/inchoo_mycheckout/active';
    const XML_PATH_TITLE                    = 'payment/inchoo_mycheckout/title';
    const XML_PATH_MERCHANT_ID              = 'payment/inchoo_mycheckout/merchant_id';
    const XML_PATH_SECURE_KEY               = 'payment/inchoo_mycheckout/secure_key';
    const XML_PATH_PAYMENT_ACTION           = 'payment/inchoo_mycheckout/payment_action';
    const XML_PATH_PURCHASE_DESC_TYPE       = 'payment/inchoo_mycheckout/purchase_description_type';
    const XML_PATH_MYCHECKOUT_POST_URL      = 'payment/inchoo_mycheckout/mycheckout_post_url';
    const XML_PATH_PAYMENT_STEP_INFO        = 'payment/inchoo_mycheckout/payment_step_information';
    const XML_PATH_PAYMENT_PROGRESS_INFO    = 'payment/inchoo_mycheckout/payment_progress_information';
    const XML_PATH_PENDING_ORDER_STATUS     = 'payment/inchoo_mycheckout/pending_order_status';
    const XML_PATH_PAYED_ORDER_STATUS       = 'payment/inchoo_mycheckout/payed_order_status';
    const XML_PATH_CANCELED_ORDER_STATUS    = 'payment/inchoo_mycheckout/canceled_order_status';
    const XML_PATH_DEBUG                    = 'payment/inchoo_mycheckout/debug';

    public function getTitle($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_TITLE, $store);
    }
    
    public function getMerchantId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_MERCHANT_ID, $store);
    }
    
    public function getSecureKey($store = null)
    {
        $key = Mage::getStoreConfig(self::XML_PATH_SECURE_KEY, $store);
        
        return Mage::helper('core')->decrypt($key);
    }
    
    public function getPaymentAction($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_ACTION, $store);
    }
    
    public function getPurchaseDescriptionType($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PURCHASE_DESC_TYPE, $store);
    }

    public function getMycheckoutPostUrl($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_MYCHECKOUT_POST_URL, $store);
    }
    
    public function getPaymentStepInformation($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_STEP_INFO, $store);
    }  
    
    public function getPaymentProgressInformation($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_PROGRESS_INFO, $store);
    }  
    
    public function getPurchaseDescription($order = null)
    {
        return Mage::app()->getStore()->getName();
    }
    
    public function getPendingOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PENDING_ORDER_STATUS, $store);
    }  
    
    public function getPayedOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYED_ORDER_STATUS, $store);
    }
    
    public function getCanceledOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CANCELED_ORDER_STATUS, $store);
    }    
    
    public function formatPrice($price)
    {
        $pricef = floatval($price);
        $result = number_format ($pricef, 2 , '.' , '');
        return $result;
    }
    
    public function validateResponseHash($orderNumber, $responseRandomNumber, $responseHash)
    {
        $hash = sha1($this->getMerchantId() . $orderNumber . $responseRandomNumber . $this->getSecureKey());
        
        if ($hash === $responseHash) {
            return true;
        }
        
        return false;
    }
}
