<?php
namespace AI\ai\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;

class View extends Action
{
    protected $resultPageFactory;
    protected $productRepository;
    protected $registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');
        $processedImage = $this->getRequest()->getParam('processed');
        $originalImage = $this->getRequest()->getParam('original');

        try {
            $product = $this->productRepository->getById($productId);
            
            // Registra o produto
            $this->registry->register('current_product', $product);
            $this->registry->register('product', $product);
            
            // Registra as imagens customizadas
            if ($processedImage && $originalImage) {
                $this->registry->register('custom_processed_image', base64_decode($processedImage));
                $this->registry->register('custom_original_image', base64_decode($originalImage));
            }
            
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set($product->getName());
            
            return $resultPage;
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Produto não encontrado.');
            return $this->resultRedirectFactory->create()->setPath('/');
        }
    }
}