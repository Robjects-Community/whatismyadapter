<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsTranslationsFixture
 */
class ProductsTranslationsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'products_translations';

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'prod-001-usb-c-cable',
                'locale' => 'en_GB',
                'title' => 'USB-C to Lightning Cable',
                'lede' => 'Premium charging cable for Apple devices',
                'body' => 'High-quality USB-C to Lightning cable perfect for fast charging your iPhone and iPad.',
                'summary' => 'Fast charging cable for Apple devices',
                'meta_title' => 'USB-C to Lightning Cable - Fast Charging',
                'meta_description' => 'High-quality USB-C to Lightning cable for fast charging your iPhone and iPad.',
                'meta_keywords' => 'USB-C, Lightning, cable, charging, Apple',
                'facebook_description' => 'Get the best USB-C to Lightning cable for fast charging.',
                'linkedin_description' => 'Professional-grade USB-C to Lightning cable.',
                'instagram_description' => '⚡ Fast charging USB-C to Lightning cable!',
                'twitter_description' => 'Fast charging USB-C to Lightning cable.'
            ],
            [
                'id' => 'prod-001-usb-c-cable',
                'locale' => 'fr_FR',
                'title' => 'Câble USB-C vers Lightning',
                'lede' => 'Câble de charge premium pour appareils Apple',
                'body' => 'Câble USB-C vers Lightning de haute qualité parfait pour la charge rapide de votre iPhone et iPad.',
                'summary' => 'Câble de charge rapide pour appareils Apple',
                'meta_title' => 'Câble USB-C vers Lightning - Charge Rapide',
                'meta_description' => 'Câble USB-C vers Lightning de haute qualité pour la charge rapide de votre iPhone et iPad.',
                'meta_keywords' => 'USB-C, Lightning, câble, charge, Apple',
                'facebook_description' => 'Obtenez le meilleur câble USB-C vers Lightning pour la charge rapide.',
                'linkedin_description' => 'Câble USB-C vers Lightning de qualité professionnelle.',
                'instagram_description' => '⚡ Câble USB-C vers Lightning à charge rapide!',
                'twitter_description' => 'Câble USB-C vers Lightning à charge rapide.'
            ]
        ];
        parent::init();
    }
}
