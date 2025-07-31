<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductVerificationsFixture
 */
class ProductVerificationsFixture extends TestFixture
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
                'id' => 'verify-001',
                'product_id' => 'prod-001-usb-c-cable',
                'verifier_user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'verification_type' => 'ai_analysis',
                'verification_score' => 4.50,
                'verification_details' => '{"technical_accuracy": 0.95, "completeness": 0.90, "specification_consistency": 0.92}',
                'issues_found' => '[]',
                'suggestions' => '["Add more detailed compatibility information", "Include certification details"]',
                'verification_notes' => 'AI analysis shows high technical accuracy. All specifications appear consistent.',
                'is_approved' => 1,
                'created' => '2024-01-15 10:30:00'
            ],
            [
                'id' => 'verify-002',
                'product_id' => 'prod-001-usb-c-cable',
                'verifier_user_id' => '199b7544-8725-49ee-a26c-a3f32e03e423',
                'verification_type' => 'expert_review',
                'verification_score' => 4.20,
                'verification_details' => '{"technical_expertise": "high", "product_familiarity": "excellent"}',
                'issues_found' => '["Minor specification discrepancy in data transfer speed"]',
                'suggestions' => '["Verify actual data transfer speed with manufacturer", "Add USB-IF certification status"]',
                'verification_notes' => 'Expert review confirms product quality. Minor specification adjustment needed.',
                'is_approved' => 1,
                'created' => '2024-01-16 14:20:00'
            ],
            [
                'id' => 'verify-003',
                'product_id' => 'prod-002-hdmi-adapter',
                'verifier_user_id' => null,
                'verification_type' => 'automated_check',
                'verification_score' => 3.80,
                'verification_details' => '{"duplicate_check": "passed", "specification_validation": "passed", "compatibility_check": "warning"}',
                'issues_found' => '["Compatibility list may be incomplete"]',
                'suggestions' => '["Expand device compatibility list", "Add compatibility testing results"]',
                'verification_notes' => 'Automated checks mostly passed. Compatibility information needs expansion.',
                'is_approved' => 1,
                'created' => '2024-01-16 09:45:00'
            ],
            [
                'id' => 'verify-004',
                'product_id' => 'prod-002-hdmi-adapter',
                'verifier_user_id' => '299b7544-8725-49ee-a26c-a3f32e03e424',
                'verification_type' => 'peer_review',
                'verification_score' => 4.10,
                'verification_details' => '{"community_confidence": 0.85, "review_thoroughness": 0.90}',
                'issues_found' => '[]',
                'suggestions' => '["Consider adding more usage scenarios", "Include setup instructions"]',
                'verification_notes' => 'Peer review indicates good product documentation. Community has high confidence.',
                'is_approved' => 1,
                'created' => '2024-01-17 11:15:00'
            ],
            [
                'id' => 'verify-005',
                'product_id' => 'prod-003-unpublished',
                'verifier_user_id' => null,
                'verification_type' => 'ai_analysis',
                'verification_score' => 2.30,
                'verification_details' => '{"technical_accuracy": 0.60, "completeness": 0.45, "specification_consistency": 0.50}',
                'issues_found' => '["Incomplete technical specifications", "Missing manufacturer information", "No compatibility data"]',
                'suggestions' => '["Add complete technical specifications", "Provide manufacturer details", "Include compatibility information", "Add product images"]',
                'verification_notes' => 'Product submission requires significant improvement before approval.',
                'is_approved' => 0,
                'created' => '2024-01-17 12:15:00'
            ],
            [
                'id' => 'verify-006',
                'product_id' => 'prod-001-usb-c-cable',
                'verifier_user_id' => '399b7544-8725-49ee-a26c-a3f32e03e425',
                'verification_type' => 'peer_review',
                'verification_score' => 4.60,
                'verification_details' => '{"community_confidence": 0.92, "review_thoroughness": 0.95}',
                'issues_found' => '[]',
                'suggestions' => '["Product meets all quality standards"]',
                'verification_notes' => 'Excellent peer review results. Product exceeds quality expectations.',
                'is_approved' => 1,
                'created' => '2024-01-18 16:30:00'
            ]
        ];
        parent::init();
    }
}
