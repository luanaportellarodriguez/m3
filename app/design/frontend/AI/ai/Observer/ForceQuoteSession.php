<?php
namespace AI\ai\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class ForceQuoteSession implements ObserverInterface
{
    protected $checkoutSession;

    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuoteItem()->getQuote();
        
        // Força o quote_id na sessão
        $this->checkoutSession->setQuoteId($quote->getId());
        $this->checkoutSession->replaceQuote($quote);
    }
}