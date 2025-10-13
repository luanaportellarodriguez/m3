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
        $product = null; // Inicializa a variável

        try {
            // Log 1: Parâmetros recebidos
            $sku = $this->getRequest()->getParam('sku');
            $processedImageUrl = $this->getRequest()->getParam('image_url');
            $originalImageUrl = $this->getRequest()->getParam('original_image');
            $style = $this->getRequest()->getParam('style');

            $this->logger->info('AI Cart Add - Params:', [
                'sku' => $sku,
                'processed' => $processedImageUrl,
                'original' => $originalImageUrl,
                'style' => $style
            ]);

            if (!$sku || !$processedImageUrl || !$originalImageUrl) {
                return $result->setData([
                    'success' => false,
                    'error' => 'Dados incompletos. SKU: ' . ($sku ? 'OK' : 'FALTA') . 
                              ', Processada: ' . ($processedImageUrl ? 'OK' : 'FALTA') . 
                              ', Original: ' . ($originalImageUrl ? 'OK' : 'FALTA')
                ]);
            }

            // Log 2: Tentando carregar produto
            try {
                $product = $this->productRepository->get($sku);
                $this->logger->info('AI Cart Add - Produto carregado:', [
                    'id' => $product->getId(),
                    'name' => $product->getName()
                ]);
            } catch (NoSuchEntityException $e) {
                $this->logger->error('AI Cart Add - Produto não encontrado: ' . $sku);
                return $result->setData([
                    'success' => false,
                    'error' => 'Produto não encontrado: ' . $sku
                ]);
            }

            // Verifica se o produto foi carregado
            if (!$product || !$product->getId()) {
                return $result->setData([
                    'success' => false,
                    'error' => 'Falha ao carregar o produto'
                ]);
            }

            // Log 3: Pegando custom options
            $productOptions = $product->getOptions();
            $this->logger->info('AI Cart Add - Total de options no produto: ' . count($productOptions));

            $options = [];
            foreach ($productOptions as $option) {
                $title = strtolower(trim($option->getTitle()));
                $this->logger->info('AI Cart Add - Option encontrada: ' . $option->getTitle() . ' (ID: ' . $option->getId() . ')');
                
                if (strpos($title, 'original') !== false) {
                    $options[$option->getId()] = $originalImageUrl;
                    $this->logger->info('AI Cart Add - Mapeou option ORIGINAL: ' . $option->getId());
                } elseif (strpos($title, 'processada') !== false) {
                    $options[$option->getId()] = $processedImageUrl;
                    $this->logger->info('AI Cart Add - Mapeou option PROCESSADA: ' . $option->getId());
                }
            }

            $this->logger->info('AI Cart Add - Options finais:', $options);

            if (empty($options)) {
                return $result->setData([
                    'success' => false,
                    'error' => 'Custom options não configuradas no produto. Por favor, configure as Customizable Options no admin: "Imagem Original" e "Imagem Processada"'
                ]);
            }

            // Log 4: Tentando adicionar ao carrinho
            $params = new \Magento\Framework\DataObject([
                'product' => $product->getId(),
                'qty' => 1,
                'options' => $options
            ]);

            $this->logger->info('AI Cart Add - Params para addProduct:', $params->getData());

            $this->cart->addProduct($product, $params);
            $this->cart->save();

            $quote = $this->cart->getQuote();
            $this->checkoutSession->setQuoteId($quote->getId());
            $this->checkoutSession->replaceQuote($quote);
            $this->checkoutSession->getQuote()->collectTotals()->save();

            $this->logger->info('AI Cart Add - Sucesso! Items no carrinho: ' . $quote->getItemsCount());

            return $result->setData([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho com sucesso!',
                'items_count' => $quote->getItemsCount(),
                'quote_id' => $quote->getId()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('AI Cart Add - Erro fatal: ' . $e->getMessage());
            $this->logger->error('AI Cart Add - Stack trace: ' . $e->getTraceAsString());
            
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}