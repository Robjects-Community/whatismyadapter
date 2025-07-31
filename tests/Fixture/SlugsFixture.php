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

            // Slug records for Product One (multiple records)
            [
                'id' => '1e6c7b8-prod-2222-aaaa-bb33d4319f75',  // can be any unique ID 
                'model' => 'Products',
                'foreign_key' => 'prod-001-usb-c-cable', // Using the fixture ID directly - i.e. product primary key is referenced by using foreign_key 
                'slug' => 'product-one',
                'created' => '2024-09-27 09:58:35',
            ],
            [
                'id' => 'p2f7d8c9-2222-3333-bbbb-cc44e5420g86',
                'model' => 'Products',
                'foreign_key' => 'prod-001-aaaa-bbbb-cccc-ddddeeeeffff',
                'slug' => 'product-one-updated',
                'created' => '2024-10-01 10:00:00',
            ],
            [
                'id' => 'p3g8e9d0-3333-4444-cccc-dd55f6531h97',
                'model' => 'Products',
                'foreign_key' => 'prod-001-aaaa-bbbb-cccc-ddddeeeeffff',
                'slug' => 'product-one-final',
                'created' => '2024-10-05 11:00:00',
            ],
            // Slug record for Product Two (single record)
            [
                'id' => 'p4h9f0e1-4444-5555-dddd-ee66g7642i08',
                'model' => 'Products',
                'foreign_key' => 'prod-002-bbbb-cccc-dddd-eeeeffff0000',
                'slug' => 'product-two',
                'created' => '2024-09-27 09:59:35',
            ],
            // Slug records for Product Three (multiple records)
            [
                'id' => 'p5i0g1f2-5555-6666-eeee-ff77h8753j19',
                'model' => 'Products',
                'foreign_key' => 'prod-003-cccc-dddd-eeee-ffff11112222',
                'slug' => 'product-three',
                'created' => '2024-09-27 10:00:35',
            ],
            [
                'id' => 'p6j1h2g3-6666-7777-ffff-gg88i9864k20',
                'model' => 'Products',
                'foreign_key' => 'prod-003-cccc-dddd-eeee-ffff11112222',
                'slug' => 'product-three-revised',
                'created' => '2024-10-10 12:00:00',
            ],
            // Slug record for Product Four (single record)
            [
                'id' => 'p7k2i3h4-7777-8888-gggg-hh99j0975l31',
                'model' => 'Products',
                'foreign_key' => 'prod-004-dddd-eeee-ffff-gggghhhhiiii',
                'slug' => 'product-four',
                'created' => '2024-09-27 10:01:35',
            ],
            // Slug record for Product Six (single record)
            [
                'id' => 'p9m4k5j6-8888-9999-hhhh-ii11l2197n53',
                'model' => 'Products',
                'foreign_key' => 'prod-006-eeee-ffff-gggg-hhhhiiiijjjj',
                'slug' => 'product-six',
                'created' => '2023-09-27 10:02:35',
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
