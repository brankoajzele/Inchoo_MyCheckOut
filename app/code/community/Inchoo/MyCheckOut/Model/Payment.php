<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'inchoo_mycheckout';

    protected $_isGateway                   = false; /* Seems like its used for capturing of funds, which we cannot do with redirect gateway. */
    protected $_canAuthorize                = true; /* Required to be true or else we wont be able to go trough checkout. */
    protected $_canUseInternal              = false; /* This method cannot be used from Magento admin area. */
    protected $_isInitializeNeeded          = true; /* If set to true, it calls initialize() instead of authorize() or capture(). */

    protected $_formBlockType = 'inchoo_mycheckout/payment_form';
    protected $_infoBlockType = 'inchoo_mycheckout/payment_info';
    
    const RESPONSE_RESULT_OK = '000';
    
    protected static $_responseResults = array(
        '000' => 'Odobreno/Prihvaćeno',
        '100' => 'Odbijen',
        '101' => 'Istekla kartica',
        '104' => 'Ograničena kartica',
        '106' => 'Pokušaji unosa PIN-a',
        '107' => 'Refferal *',
        '109' => 'Ne važeća uspostava servisa',
        '111' => 'Kartica nije prisutna',
        '115' => 'Zahtijevana funkcija nije podržana',
        '117' => 'Krivi PIN',
        '121' => 'Prekoračeni limit',
        '400' => 'Poništenje prihvaćeno',
        '903' => 'Ponovno unijeti transakciju ***',
        '909' => 'Tehnička greška – nije moguće procesirati zahtjev**',
        '912' => 'Veza prema hostu nije uspostavljena **',
        '930' => 'Transakcija nije pronađena ****',
        '931' => 'Transakcija poništena ****',
    );
    
    public static function getKnownResponseResults()
    {
        return self::$_responseResults;
    }
    
    /**
     * This function works with Mage_Checkout_Model_Type_Onepage -> saveOrder().
     *
     * Check the line that says:
     * $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
     *
     * Before the redirect to 3rd party payment system, order info is set into session:
     * $this->_checkoutSession->setLastOrderId($order->getId())
     *      ->setRedirectUrl($redirectUrl)
     *      ->setLastRealOrderId($order->getIncrementId());
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pbzmycheckout/payment/redirect', array('_secure' => true));
    }
    
//    /**
//     * Method that will be executed instead of authorize or capture
//     * if flag isInitializeNeeded set to true
//     *
//     * @param string $paymentAction
//     * @param object $stateObject
//     *
//     * @return Mage_Payment_Model_Abstract
//     */    
//    public function initialize($paymentAction, $stateObject)
//    {
//        
//        /**
//         * This is just so that we do not get default 
//         * "Customer Notification Not Applicable " 
//         * message under order comments history.
//         */
//        $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
//        $stateObject->setStatus(Mage::helper('inchoo_mycheckout')->getPendingOrderStatus());
//        $stateObject->setIsNotified(false);
//        
//        Mage::log($stateObject->debug(), null, 'initialize.log', true);
//        
//        return $this;
//    }     
}
