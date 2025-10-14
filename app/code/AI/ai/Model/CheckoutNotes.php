<?php
namespace AI\ai\Model;

use AI\ai\Api\CheckoutNotesInterface;
use AI\ai\Api\Data\CheckoutNotesResponseInterface;
use AI\ai\Api\Data\CheckoutNotesResponseInterfaceFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

class CheckoutNotes implements CheckoutNotesInterface
{
    protected $checkoutSession;
    protected $responseFactory;
    protected $logger;
    
    public function __construct(
        CheckoutSession $checkoutSession,
        CheckoutNotesResponseInterfaceFactory $responseFactory,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }
    
    public function save($notes, $pickupPointId = null)
    {
        $response = $this->responseFactory->create();
        
        try {
            // Validação
            if (empty($notes)) {
                return $response
                    ->setSuccess(false)
                    ->setMessage('As observações não podem estar vazias');
            }
            
            // Salva na sessão do checkout
            $quote = $this->checkoutSession->getQuote();
            
            if (!$quote || !$quote->getId()) {
                return $response
                    ->setSuccess(false)
                    ->setMessage('Nenhum carrinho ativo encontrado');
            }
            
            // Salva como dados adicionais
            $additionalData = [
                'checkout_notes' => $notes,
                'pickup_point_id' => $pickupPointId
            ];
            
            $quote->setData('checkout_notes', json_encode($additionalData));
            $quote->save();
            
            $this->logger->info('Checkout notes saved:', $additionalData);
            
            return $response
                ->setSuccess(true)
                ->setMessage('Observações salvas com sucesso!')
                ->setNotes($notes)
                ->setPickupPointId($pickupPointId);
                
        } catch (\Exception $e) {
            $this->logger->error('Error saving checkout notes: ' . $e->getMessage());
            
            return $response
                ->setSuccess(false)
                ->setMessage('Erro ao salvar observações: ' . $e->getMessage());
        }
    }
    
    public function get()
    {
        $response = $this->responseFactory->create();
        
        try {
            $quote = $this->checkoutSession->getQuote();
            
            if (!$quote || !$quote->getId()) {
                return $response
                    ->setSuccess(false)
                    ->setMessage('Nenhum carrinho ativo encontrado');
            }
            
            $notesData = $quote->getData('checkout_notes');
            
            if ($notesData) {
                $data = json_decode($notesData, true);
                return $response
                    ->setSuccess(true)
                    ->setMessage('Observações recuperadas')
                    ->setNotes($data['checkout_notes'] ?? null)
                    ->setPickupPointId($data['pickup_point_id'] ?? null);
            }
            
            return $response
                ->setSuccess(true)
                ->setMessage('Nenhuma observação salva')
                ->setNotes(null)
                ->setPickupPointId(null);
                
        } catch (\Exception $e) {
            $this->logger->error('Error getting checkout notes: ' . $e->getMessage());
            
            return $response
                ->setSuccess(false)
                ->setMessage('Erro ao recuperar observações');
        }
    }
    
    public function delete()
    {
        $response = $this->responseFactory->create();
        
        try {
            $quote = $this->checkoutSession->getQuote();
            
            if (!$quote || !$quote->getId()) {
                return $response
                    ->setSuccess(false)
                    ->setMessage('Nenhum carrinho ativo encontrado');
            }
            
            $quote->setData('checkout_notes', null);
            $quote->save();
            
            $this->logger->info('Checkout notes deleted');
            
            return $response
                ->setSuccess(true)
                ->setMessage('Observações excluídas com sucesso!');
                
        } catch (\Exception $e) {
            $this->logger->error('Error deleting checkout notes: ' . $e->getMessage());
            
            return $response
                ->setSuccess(false)
                ->setMessage('Erro ao excluir observações');
        }
    }
}