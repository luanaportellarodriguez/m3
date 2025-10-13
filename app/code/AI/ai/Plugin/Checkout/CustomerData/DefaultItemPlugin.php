<?php
namespace AI\ai\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\DefaultItem;

class DefaultItemPlugin
{
    /**
     * Adiciona a URL da imagem processada aos dados do item
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

            // Procura pela custom option "Imagem Processada"
            if ($item->getOptionByCode('option_ids')) {
                $product = $item->getProduct();
                $optionIds = explode(',', $item->getOptionByCode('option_ids')->getValue());
                
                foreach ($optionIds as $optionId) {
                    $option = $item->getOptionByCode('option_' . $optionId);
                    if ($option) {
                        $optionConfig = $product->getOptionById($optionId);
                        if ($optionConfig && stripos($optionConfig->getTitle(), 'processada') !== false) {
                            // Substitui a imagem do produto pela processada
                            $result['product_image']['src'] = $option->getValue();
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silenciosamente ignora erros para não quebrar o carrinho
        }

        return $result;
    }
}