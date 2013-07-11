<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Block_Payment_Form_Submit extends Mage_Core_Block_Template
{
    protected function _construct()
    {
    	parent::_construct();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('inchoo_mycheckout');

        $session = Mage::getSingleton('checkout/session');

        $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

        $billingAddress = Mage::getModel('sales/order_address')
                                ->load($order->getBillingAddressId());

        $totalAmount = $helper->formatPrice($order->getGrandTotal());

        $form = new Varien_Data_Form();
        $formId = 'inchoo_mycheckout';

        $form->setAction($helper->getMycheckoutPostUrl())
             ->setId($formId)
             ->setName($formId)
             ->setMethod('POST')
             ->setUseContainer(true);
             
        $countryCode = $billingAddress->getCountry();
        $country = Mage::getModel('directory/country')->loadByCode($countryCode);
        
        $requestHash = sha1($helper->getMerchantId() . $totalAmount . $order->getIncrementId() . $helper->getSecureKey());

        $form->addField('submit_type', 'hidden', array('name'=>'submit_type', 'value'=>'cust'));
        $form->addField('trantype', 'hidden', array('name'=>'trantype', 'value'=>$helper->getPaymentAction()));
        $form->addField('purchase_amount', 'hidden', array('name'=>'purchase_amount', 'value'=>$totalAmount));
        $form->addField('purchase_currency', 'hidden', array('name'=>'purchase_currency', 'value'=>'191'));
        $form->addField('purchase_description', 'hidden', array('name'=>'purchase_description', 'value'=>$helper->getPurchaseDescription($order)));
        $form->addField('order_number', 'hidden', array('name'=>'order_number', 'value'=>$order->getIncrementId()));
        $form->addField('merchant_id', 'hidden', array('name'=>'merchant_id', 'value'=>$helper->getMerchantId()));
        $form->addField('request_hash', 'hidden', array('name'=>'request_hash', 'value'=>$requestHash));
        
        $form->addField('customer_lang', 'hidden', array('name'=>'customer_lang', 'value'=>'hr'));
        $form->addField('customer_name', 'hidden', array('name'=>'customer_name', 'value'=>$billingAddress->getFirstname()));
        $form->addField('customer_surname', 'hidden', array('name'=>'customer_surname', 'value'=>$billingAddress->getLastname()));
        $form->addField('customer_address', 'hidden', array('name'=>'customer_address', 'value'=>implode('\n', $billingAddress->getStreet())));
        $form->addField('customer_country', 'hidden', array('name'=>'customer_country', 'value'=>$country->getName()));
        $form->addField('customer_city', 'hidden', array('name'=>'customer_city', 'value'=>$billingAddress->getCity()));
        $form->addField('customer_zip', 'hidden', array('name'=>'customer_zip', 'value'=>$billingAddress->getPostcode()));
        $form->addField('customer_phone', 'hidden', array('name'=>'customer_phone', 'value'=>$billingAddress->getTelephone()));
        $form->addField('customer_email', 'hidden', array('name'=>'customer_email', 'value'=>$billingAddress->getEmail()));

        $idSuffix = Mage::helper('core')->uniqHash();

        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $helper->__('Click here if you are not redirected within 10 seconds...'),
        ));

        $id = "submit_to_inchoo_mycheckout_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);

        $html = '<html><body>';
        $html .= $this->__('You will be redirected to the PBZ MyCheckOut website in a few seconds.');
        $html .= $form->toHtml();
        $html .= '<script type="text/javascript">document.getElementById("'.$formId.'").submit();</script>';
        $html .= '</body></html>';

        return $html;
    }
}
