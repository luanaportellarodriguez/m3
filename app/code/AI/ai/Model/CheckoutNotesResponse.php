<?php
namespace AI\ai\Model;

use AI\ai\Api\Data\CheckoutNotesResponseInterface;

class CheckoutNotesResponse implements CheckoutNotesResponseInterface
{
    protected $success;
    protected $message;
    protected $notes;
    protected $pickupPointId;
    
    public function getSuccess()
    {
        return $this->success;
    }
    
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    
    public function getNotes()
    {
        return $this->notes;
    }
    
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }
    
    public function getPickupPointId()
    {
        return $this->pickupPointId;
    }
    
    public function setPickupPointId($pickupPointId)
    {
        $this->pickupPointId = $pickupPointId;
        return $this;
    }
}