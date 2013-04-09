<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_PaymentController extends Mage_Core_Controller_Front_Action
{
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
        
        $session = Mage::getSingleton('checkout/session');
        
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

            if ($order && $order->getIncrementId() == $session->getLastRealOrderId()) {
                
//                $allowedOrderStates = Mage::getModel('inchoo_mycheckout/system_config_source_order_status')->getAvailableStates();
//                
//                if (in_array($order->getState(), $allowedOrderStates)) {
                    $session->unsLastRealOrderId();
                    
                /**

                    params array(11) {
                      ["response_result"] => string(3) "000"
                      ["response_random_number"] => string(9) "974539404"
                      ["order_number"] => string(10) "1365421223"
                      ["response_appcode"] => string(6) "267000"
                      ["response_message"] => string(19) "ODOBREN AMEX 267000"
                      ["response_systan"] => string(6) "733767"
                      ["response_hash"] => string(40) "f5f8a3ba30ec266dc4443efc34877da3bfecff6e"
                      ["masked_pan"] => string(15) "377500*****1005"
                      ["card_type"] => string(4) "Amex"
                      ["aPauseTimer"] => string(5) "false"
                      ["aSubmitForm"] => string(4) "true"
                    }

                 */                    
                    
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
                    
                    $params = $this->getRequest()->getParams();
                    $paramsString = '';
                    
                    foreach ($params as $k => $v) {
                        $paramsString .= sprintf('%s: %s, ', htmlentities($k), htmlentities($v));
                    }                    
                    
//                    $shopID = $helper->getShopId();
//
//                    $shoppingCartID = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']:'');
//                    $shoppingCartID = str_replace('.', '', $shoppingCartID);
//                    $shoppingCartID .= $order->getData('increment_id');
//                    
//                    $totalAmount = $helper->formatPrice($order->getData('grand_total'));
//                    $signature = $helper->encodeResponse($shopID, $shoppingCartID, $totalAmount, $tid);
//
//                    if(strcmp(strtoupper($sig), strtoupper($signature)) != 0) {
//                        Mage::getSingleton('core/session')->addError($helper->__('PayWay transaction signature mismatch.'));
//                        $this->_redirect('no-route');
//                        return;
//                    }
                    
                    if ($helper->getTrantype() === Inchoo_MyCheckOut_Model_System_Config_Source_Trantype::TYPE_AUTHORIZE_ONLY) {
                        $comment = $helper->__('PBZ MyCheckOut system successfully authorized transaction. Transaction info => %s', $paramsString);
                    } else {
                        $comment = $helper->__('PBZ MyCheckOut system successfully authorized and captured transaction. Transaction info => %s', $paramsString);
                    }
                    
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $order->setStatus($helper->getOrderStatusConfig());
                    /* Mage_Sales_Model_Order -> addStatusToHistory($status, $comment = '', $isCustomerNotified = false) */
                    $order->addStatusToHistory($helper->getPayedOrderStatus(), $comment, true);
                    
                    $order->save();
                    
                    if ($order->getId()) {
                        $order->sendNewOrderEmail();
                    }
                    
                    
//                    if($helper->getAutomaticallyInvoicePayedOrder()) {
//                        try {
//                            if ($order->canInvoice()) {
//                                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
//                                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
//                                $invoice->register();
//                                $invoice->getOrder()->setCustomerNoteNotify(false);
//                                $invoice->getOrder()->setIsInProcess(true);
//                                $order->addStatusHistoryComment('Automatically invoiced as per config option setup.', false);
//
//                                $transactionSave = Mage::getModel('core/resource_transaction')
//                                        ->addObject($invoice)
//                                        ->addObject($invoice->getOrder());
//
//                                $transactionSave->save();
//
//                                if ($helper->getAutomaticallyShipInvoicedOrder()) {
//                                    $shipment = $order->prepareShipment();
//                                    $shipment->register();
//                                    $order->setIsInProcess(true);
//                                    $order->addStatusHistoryComment('Automatically shipped as per config option setup.', false);
//
//                                    $transactionSave = Mage::getModel('core/resource_transaction')
//                                            ->addObject($shipment)
//                                            ->addObject($shipment->getOrder())
//                                            ->save();          
//                                }
//                            }
//                        } catch (Exception $e) {
//                            $order->addStatusHistoryComment('Inchoo_Invoicer: Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: ' . $e->getMessage(), false);
//                            $order->save();
//                        }
//                    }                    
                    
                    $this->_redirect('checkout/onepage/success', array('_secure'=>true));
                    return;
//                } else {
//                    Mage::getSingleton('core/session')->addError($helper->__('Current order state does not allow this action.'));
//                    $this->_redirect('no-route');
//                    return;                    
//                }
            }
        }
        
        Mage::getSingleton('core/session')->addError($helper->__('Order information could not be found. Either cookie/session was destroyed or you accessed this link directly.'));    
        $this->_redirect('no-route');
        return;
    }
}