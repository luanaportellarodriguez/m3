<?php
namespace AI\ai\Controller\Image;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;

class Upload extends Action implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $filesystem;
    protected $urlBuilder;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Filesystem $filesystem,
        UrlInterface $urlBuilder
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->filesystem = $filesystem;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            // Pega a imagem em base64
            $imageData = $this->getRequest()->getParam('image_data');
            
            if (!$imageData) {
                throw new \Exception('Nenhuma imagem enviada');
            }

            // Remove o prefixo data:image/...;base64,
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
            } else {
                throw new \Exception('Formato de imagem inválido');
            }

            // Decodifica
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                throw new \Exception('Falha ao decodificar a imagem');
            }

            // Gera nome único
            $filename = 'original_' . uniqid() . '.' . $type;

            // Salva no diretório
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $targetPath = 'customer_uploads/originals/';
            
            // Cria diretório se não existir
            if (!$mediaDirectory->isDirectory($targetPath)) {
                $mediaDirectory->create($targetPath);
            }

            $mediaDirectory->writeFile($targetPath . $filename, $imageData);

            // Retorna a URL
            $imageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) 
                      . $targetPath . $filename;

            return $result->setData([
                'success' => true,
                'url' => $imageUrl,
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}