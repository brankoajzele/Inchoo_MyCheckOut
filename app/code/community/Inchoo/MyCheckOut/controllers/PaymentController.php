<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_PaymentController extends Mage_Core_Controller_Front_Action
{
    private $_orderNumber;
    private $_responseRandomNumber;
    private $_responseHash;
    
    protected function _construct()
    {
        $this->_orderNumber = $this->getRequest()->getParam('order_number');
        $this->_responseRandomNumber = $this->getRequest()->getParam('response_random_number');
        $this->_responseHash = $this->getRequest()->getParam('response_hash');        
    }    
    
    /**
     * When a customer chooses "PBZ MyCheckOut (Redirect)" on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('inchoo_mycheckout/payment_form_submit')->toHtml());
    }

    /**
     * When a customer cancel payment from "PBZ MyCheckOut (Redirect)".
     */
    public function cancelAction()
    {
        $helper = Mage::helper('inchoo_mycheckout');
        
        /* Validate PBZ MyCheckOut response */
        if ($helper->validateResponseHash($this->_orderNumber, $this->_responseRandomNumber, $this->_responseHash) === false) {
            Mage::getSingleton('core/session')
                ->addNotice($helper->__('Invalid response from payment gateway, response_hash value is faulty.'));
            
            $this->_redirect('no-route');
            return;                        
        }

        $session = Mage::getSingleton('checkout/session');
        
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                    $order->cancel()->save();
                
                    $params = $this->getRequest()->getParams();
                    $paramsString = '';
                    
                    foreach ($params as $k => $v) {
                        $paramsString .= sprintf('%s: %s, ', htmlentities($k), htmlentities($v));
                    }                    
                    
                    $comment = $helper->__('Order intentionally canceled by customer through PBZ MyCheckOut system. Transaction info => %s', $paramsString);
                    
                    $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                                        ->setOrderFilter($order)
                                        ->setOrder('created_at', 'desc')
                                        ->addFieldToFilter('entity_name', Mage_Sales_Model_Order::HISTORY_ENTITY_NAME)
                                        ->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_CANCELED)
                                        ->setPageSize(1)
                                        ->getFirstItem();
                    
                    if ($historyItem) {
                        $historyItem->setComment($comment);
                        $historyItem->setIsCustomerNotified(0);
                        $historyItem->save();
                    }  
                    
                    try {
                        $order->setStatus($helper->getCanceledOrderStatus());
                        $order->save();
                        
                        Mage::dispatchEvent('inchoo_mycheckout_payment_cancel', array('order'=>$order));
                        
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                
                Mage::getSingleton('core/session')
                    ->addNotice($helper->__('Order %s has been successfully canceled!', $order->getIncrementId()));
            }
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('sales/order/history');
        } else {
            $this->_redirect('checkout/cart');
        }
    }

    public function  successAction()
    {
        $helper = Mage::helper('inchoo_mycheckout');
        
        /* Validate PBZ MyCheckOut response */
        if ($helper->validateResponseHash($this->_orderNumber, $this->_responseRandomNumber, $this->_responseHash) === false) {
            Mage::getSingleton('core/session')
                ->addNotice($helper->__('Invalid response from payment gateway, response_hash value is faulty.'));
            
            $this->_redirect('no-route');
            return;                        
        }
        
        $session = Mage::getSingleton('checkout/session');
        
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

            if ($order && $order->getIncrementId() == $session->getLastRealOrderId()) {
                
                $session->unsLastRealOrderId();

                $response_result = $this->getRequest()->getParam('response_result');
                
                if($response_result !== Inchoo_MyCheckOut_Model_Payment::RESPONSE_RESULT_OK) {

                    $knownResponseResults = Inchoo_MyCheckOut_Model_Payment::getKnownResponseResults();

                    if (in_array($response_result, $knownResponseResults)) {
                        Mage::getSingleton('core/session')->addNotice($helper->__('Faulty response result: %s.', $knownResponseResults[$response_result]));
                    } else {
                        Mage::getSingleton('core/session')->addNotice($helper->__('Faulty response result: %s.', $response_result));                            
                    }

                    $this->_redirect('no-route');
                    return;
                }
    
                if ($helper->getPaymentAction() === Inchoo_MyCheckOut_Model_System_Config_Source_Payment_Action::TYPE_AUTHORIZE_ONLY) {
                    $comment = $helper->__('PBZ MyCheckOut system successfully authorized transaction.');
                } else {
                    $comment = $helper->__('PBZ MyCheckOut system successfully authorized and captured transaction.');
                }

                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                $order->setStatus($helper->getPayedOrderStatus());
                $order->addStatusToHistory($helper->getPayedOrderStatus(), $comment, true);

                $order->save();

                if ($order->getId()) {
                    $order->sendNewOrderEmail();
                }
                
                Mage::dispatchEvent('inchoo_mycheckout_payment_success', array('order'=>$order));
                    
                $this->_redirect('checkout/onepage/success', array('_secure'=>true));
                return;
            }
        }
        
        Mage::getSingleton('core/session')->addError($helper->__('Order information could not be found. Cookie/Session destroyed?'));
        $this->_redirect('no-route');
        return;
    }
}
