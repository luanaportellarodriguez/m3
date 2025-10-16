<?php
namespace AI\ai\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\UrlInterface;

class DefaultItemPlugin
{
    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Adiciona a URL da imagem processada e modifica o link do produto
     *
     * @param DefaultItem $subject
     * @param array $result
     * @return array
     */
    public function afterGetItemData(DefaultItem $subject, array $result)
    {
        try {
            // Usa reflection para acessar o item protegido
            $reflection = new \ReflectionClass(get_class($subject));
            $property = $reflection->getProperty('item');
            $property->setAccessible(true);
            $item = $property->getValue($subject);
            
            if (!$item) {
                return $result;
            }

            $processedImageUrl = null;
            $originalImageUrl = null;

            // Procura pelas custom options
            if ($item->getOptionByCode('option_ids')) {
                $product = $item->getProduct();
                $optionIds = explode(',', $item->getOptionByCode('option_ids')->getValue());
                
                foreach ($optionIds as $optionId) {
                    $option = $item->getOptionByCode('option_' . $optionId);
                    if ($option) {
                        $optionConfig = $product->getOptionById($optionId);
                        if ($optionConfig) {
                            $title = strtolower($optionConfig->getTitle());
                            
                            if (stripos($title, 'processada') !== false) {
                                $processedImageUrl = $option->getValue();
                            } elseif (stripos($title, 'original') !== false) {
                                $originalImageUrl = $option->getValue();
                            }
                        }
                    }
                }
            }

            // Se encontrou as imagens customizadas
            if ($processedImageUrl && $originalImageUrl) {
                // Substitui a imagem do produto pela processada
                $result['product_image']['src'] = $processedImageUrl;
                
                // Modifica a URL do produto para incluir as imagens
                $result['product_url'] = $this->urlBuilder->getUrl('ai/product/view', [
                    'id' => $item->getProduct()->getId(),
                    'processed' => base64_encode($processedImageUrl),
                    'original' => base64_encode($originalImageUrl)
                ]);
            }
        } catch (\Exception $e) {
            // Silenciosamente ignora erros para não quebrar o carrinho
        }

        return $result;
    }
}