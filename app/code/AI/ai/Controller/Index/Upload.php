<?php
namespace AI\ai\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload extends Action implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $filesystem;
    protected $urlBuilder;
    protected $uploaderFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        UploaderFactory $uploaderFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->filesystem = $filesystem;
        $this->urlBuilder = $urlBuilder;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $style = $this->getRequest()->getParam('style');
            if (!$style) {
                throw new \Exception('Nenhum estilo selecionado');
            }

            // Upload do arquivo normalmente
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('customer_uploads');

            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $uploadResult = $uploader->save($destinationPath);

            // mock quesempre retorna a mesma imagem
            $mockImageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'mock/dog-small.png';

            return $result->setData([
                'success' => true,
                'filename' => $uploadResult['file'],
                'url' => $mockImageUrl,
                'style' => $style
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
