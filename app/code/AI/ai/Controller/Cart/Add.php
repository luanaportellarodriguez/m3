<?php
namespace AI\ai\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class Add extends Action implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $cart;
    protected $productRepository;
    protected $logger;
    protected $checkoutSession;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $sku = $this->getRequest()->getParam('sku');
            $imageUrl = $this->getRequest()->getParam('image_url');
            $style = $this->getRequest()->getParam('style');

            if (!$sku || !$imageUrl) {
                return $result->setData([
                    'success' => false,
                    'error' => 'Dados incompletos'
                ]);
            }

            try {
                $product = $this->productRepository->get($sku);
            } catch (NoSuchEntityException $e) {
                return $result->setData([
                    'success' => false,
                    'error' => 'Produto não encontrado: ' . $sku
                ]);
            }

            $params = new \Magento\Framework\DataObject([
                'product' => $product->getId(),
                'qty' => 1
            ]);

            $this->cart->addProduct($product, $params);
            $this->cart->save();

            $quote = $this->cart->getQuote();
            $this->checkoutSession->setQuoteId($quote->getId());
            $this->checkoutSession->replaceQuote($quote);

            $this->checkoutSession->getQuote()->collectTotals()->save();

            return $result->setData([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho com sucesso!',
                'items_count' => $quote->getItemsCount(),
                'quote_id' => $quote->getId()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar ao carrinho: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
