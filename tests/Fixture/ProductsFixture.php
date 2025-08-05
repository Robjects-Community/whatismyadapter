<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsFixture
 */
class ProductsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
<<<<<<< HEAD
                'id' => '99df2e7d-444a-43e4-aa1f-800968fe40a7',
                'user_id' => 'e9ddab29-6979-4b66-8cbe-8db2ecaf0dc4',
                'article_id' => '7798da0a-6dbd-4970-a680-037cd966e176',
=======
                // Example product record
                'id' => '0cab0d79-877c-4e97-81c3-472cefa099a5',
                'user_id' => 'e1e68d5b-4607-473f-afbb-0df146d1980f',
                'article_id' => 'b588654f-876b-464f-b8ca-e0eee1d9a766',
>>>>>>> 9e559aff79ca5e9c3e9210dbf026c66ee4a81e34
                'title' => 'Lorem ipsum dolor sit amet',
                'slug' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'manufacturer' => 'Lorem ipsum dolor sit amet',
                'model_number' => 'Lorem ipsum dolor sit amet',
                'price' => 1.5,
                'currency' => '',
                'image' => 'Lorem ipsum dolor sit amet',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'is_published' => 1,
                'featured' => 1,
                'verification_status' => 'Lorem ipsum dolor ',
                'reliability_score' => 1.5,
                'view_count' => 1,
<<<<<<< HEAD
                'created' => '2025-08-05 17:58:00',
                'modified' => '2025-08-05 17:58:00',
=======
                'created' => '2025-08-04 18:34:21',
                'modified' => '2025-08-04 18:34:21',
>>>>>>> 9e559aff79ca5e9c3e9210dbf026c66ee4a81e34
            ],
        ];
        parent::init();
    }
}
