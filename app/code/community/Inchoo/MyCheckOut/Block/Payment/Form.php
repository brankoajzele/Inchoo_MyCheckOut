<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MyCheckOut
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MyCheckOut_Block_Payment_Form extends Mage_Payment_Block_Form
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('inchoo/mycheckout/payment/form/mycheckout.phtml');
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        $helper = Mage::helper('inchoo_mycheckout');

        if (is_null($this->_instructions)) {
            $this->_instructions = $helper->getPaymentStepInformation();
        }
        return $this->_instructions;
    }
}
