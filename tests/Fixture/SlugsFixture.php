<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SlugsFixture
 */
class SlugsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            // Slug records for Article One (multiple records)
            [
                'id' => '1e6c7b88-283d-43df-bfa3-fa33d4319f75',
                'model' => 'Articles',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug' => 'article-one',
                'created' => '2024-09-27 07:58:35',
            ],
            [
                'id' => '2f7d8c99-394e-54ef-cfa4-gb44e5420g86',
                'model' => 'Articles',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug' => 'article-one-updated',
                'created' => '2024-10-01 08:00:00',
            ],
            [
                'id' => '3g8e9d00-4a5f-65fg-dgb5-hc55f6531h97',
                'model' => 'Articles',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug' => 'article-one-final',
                'created' => '2024-10-05 09:00:00',
            ],
            // Slug record for Article Two (single record)
            [
                'id' => '4h9f0e11-5b6g-76gh-ehc6-id66g7642i08',
                'model' => 'Articles',
                'foreign_key' => 'ij2349f8-h707-001i-55jj-jk59l2k3kkg7',
                'slug' => 'article-two',
                'created' => '2024-09-27 07:59:35',
            ],
            // Slug records for Article Three (multiple records)
            [
                'id' => '5i0g1f22-6c7h-87hi-fid7-je77h8753j19',
                'model' => 'Articles',
                'foreign_key' => '42655115-cb43-4ba5-bae7-292443b9ce21',
                'slug' => 'article-three',
                'created' => '2024-09-27 08:00:35',
            ],
            [
                'id' => '6j1h2g33-7d8i-98ij-gje8-kf88i9864k20',
                'model' => 'Articles',
                'foreign_key' => '42655115-cb43-4ba5-bae7-292443b9ce21',
                'slug' => 'article-three-revised',
                'created' => '2024-10-10 10:00:00',
            ],
            // Slug record for Article Four (single record)
            [
                'id' => '7k2i3h44-8e9j-09jk-hkf9-lg99j0975l31',
                'model' => 'Articles',
                'foreign_key' => 'kl4561h0-j909-223k-77ll-lm71n4m5mmi9',
                'slug' => 'article-four',
                'created' => '2024-09-27 08:01:35',
            ],
            // Slug record for Article Six (single record)
            [
                'id' => '9m4k5j66-0g1l-21lm-jmh1-ni11l2197n53',
                'model' => 'Articles',
                'foreign_key' => '224310b4-96ad-4d58-a0a9-af6dc7253c4f',
                'slug' => 'article-six',
                'created' => '2023-09-27 08:02:35',
            ],
            // Add some Tag slugs for variety
            [
                'id' => 'aa4k5j66-0g1l-21lm-jmh1-ni11l2197n54',
                'model' => 'Tags',
                'foreign_key' => '334310b4-96ad-4d58-a0a9-af6dc7253c5e',
                'slug' => 'technology',
                'created' => '2024-01-27 08:02:35',
            ],
            [
                'id' => 'bb4k5j66-0g1l-21lm-jmh1-ni11l2197n55',
                'model' => 'Tags',
                'foreign_key' => '444310b4-96ad-4d58-a0a9-af6dc7253c6f',
                'slug' => 'programming',
                'created' => '2024-01-27 08:03:35',
            ],
        ];
        parent::init();
    }
}
