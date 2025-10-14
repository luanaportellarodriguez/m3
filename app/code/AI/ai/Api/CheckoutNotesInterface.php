<?php
namespace AI\ai\Api;

interface CheckoutNotesInterface
{
    /**
     * Save checkout notes with pickup point
     *
     * @param string $notes
     * @param string|null $pickupPointId
     * @return \AI\ai\Api\Data\CheckoutNotesResponseInterface
     */
    public function save($notes, $pickupPointId = null);
    
    /**
     * Get saved checkout notes
     *
     * @return \AI\ai\Api\Data\CheckoutNotesResponseInterface
     */
    public function get();
    
    /**
     * Delete checkout notes
     *
     * @return \AI\ai\Api\Data\CheckoutNotesResponseInterface
     */
    public function delete();
}