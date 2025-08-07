<?php
declare(strict_types=1);

// src/Model/Entity/Adapter.php
namespace App\Model\Entity;

class Adapter extends Product
{
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];

    // Adapter-specific validation
    protected function _getCompatibilityScore(): float
    {
        $score = 0;
        if ($this->supports_usb_pd) {
            $score += 2;
        }
        if ($this->supports_thunderbolt) {
            $score += 3;
        }
        if ($this->supports_displayport) {
            $score += 2;
        }

        return $score;
    }
}
