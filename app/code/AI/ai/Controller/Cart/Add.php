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

class Add extends Action implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $cart;
    protected $productRepository;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        try {
            $sku = $this->getRequest()->getParam('sku');
            $imageUrl = $this->getRequest()->getParam('image_url');
            $style = $this->getRequest()->getParam('style');
            
            $this->logger->info('AI Cart Add - Params:', [
                'sku' => $sku,
                'image_url' => $imageUrl,
                'style' => $style
            ]);
            
            if (!$sku || !$imageUrl) {
                return $result->setData([
                    'success' => false,
                    'error' => 'Dados incompletos'
                ]);
            }

            try {
                $product = $this->productRepository->get($sku);
                $this->logger->info('Produto encontrado: ' . $product->getId());
            } catch (NoSuchEntityException $e) {
                $this->logger->error('Produto não encontrado: ' . $sku);
                return $result->setData([
                    'success' => false,
                    'error' => 'Produto não encontrado: ' . $sku
                ]);
            }

            // Usa o cart model que é o mesmo que o Magento usa
            $params = new \Magento\Framework\DataObject([
                'product' => $product->getId(),
                'qty' => 1
            ]);
            
            $this->cart->addProduct($product, $params);
            $this->cart->save();
            
            $this->logger->info('Produto adicionado via Cart model');
            $this->logger->info('Items no carrinho: ' . $this->cart->getQuote()->getItemsCount());

            return $result->setData([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho com sucesso!',
                'items_count' => $this->cart->getQuote()->getItemsCount(),
                'quote_id' => $this->cart->getQuote()->getId()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Erro ao adicionar ao carrinho: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}