<?php
namespace AI\ai\Api\Data;

interface CheckoutNotesResponseInterface
{
    /**
     * Get success status
     *
     * @return bool
     */
    public function getSuccess();
    
    /**
     * Set success status
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);
    
    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();
    
    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);
    
    /**
     * Get notes
     *
     * @return string|null
     */
    public function getNotes();
    
    /**
     * Set notes
     *
     * @param string|null $notes
     * @return $this
     */
    public function setNotes($notes);
    
    /**
     * Get pickup point ID
     *
     * @return string|null
     */
    public function getPickupPointId();
    
    /**
     * Set pickup point ID
     *
     * @param string|null $pickupPointId
     * @return $this
     */
    public function setPickupPointId($pickupPointId);
}
