<?php
namespace AI\ai\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;

class Upload extends Action implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $uploaderFactory;
    protected $filesystem;
    protected $urlBuilder;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        UrlInterface $urlBuilder
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        try {
            // Recebe o arquivo
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            
            // Define diretório de destino
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('customer_uploads');
            
            // Cria o diretório se não existir
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            // Faz o upload
            $uploadResult = $uploader->save($destinationPath);
            
            // Pega o estilo selecionado
            $style = $this->getRequest()->getParam('style');
            
            // URL da imagem
            $imageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) 
                      . 'customer_uploads/' . $uploadResult['file'];
            
            // AQUI VOCÊ CHAMARIA SUA API DE IA
            // $aiGeneratedImage = $this->callAiApi($imageUrl, $style);
            
            return $result->setData([
                'success' => true,
                'filename' => $uploadResult['file'],
                'url' => $imageUrl,
                'style' => $style
                // 'ai_result' => $aiGeneratedImage // quando tiver a IA
            ]);
            
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}