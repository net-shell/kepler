<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleDocuments = [
            [
                'title' => 'Refund Policy',
                'body' => 'Items can be returned within 30 days for a full refund. Original packaging required. Refunds are processed within 5-7 business days after we receive the returned item.',
                'tags' => ['refund', 'returns', 'policy', 'customer-service'],
                'metadata' => ['category' => 'policy', 'priority' => 'high']
            ],
            [
                'title' => 'Privacy Statement',
                'body' => 'We never share your personal data with third parties. Your information is encrypted and secure. We comply with GDPR and CCPA regulations.',
                'tags' => ['privacy', 'security', 'data-protection', 'legal'],
                'metadata' => ['category' => 'legal', 'priority' => 'high']
            ],
            [
                'title' => 'Shipping Information',
                'body' => 'Worldwide delivery with tracking number. Express shipping available for urgent orders. Standard shipping takes 5-7 business days. Free shipping on orders over $50.',
                'tags' => ['shipping', 'delivery', 'international', 'logistics'],
                'metadata' => ['category' => 'shipping', 'priority' => 'medium']
            ],
            [
                'title' => 'AI Search Project',
                'body' => 'An open-source system for semantic search on structured data using TF-IDF and cosine similarity. Built with Python, scikit-learn, and modern web technologies.',
                'tags' => ['AI', 'search', 'open-source', 'machine-learning', 'technology'],
                'metadata' => ['category' => 'technology', 'priority' => 'medium']
            ],
            [
                'title' => 'Customer Support',
                'body' => '24/7 customer support available via email, chat, and phone. Average response time: 2 hours. We strive to resolve all issues within 24 hours.',
                'tags' => ['support', 'help', 'customer-service', 'contact'],
                'metadata' => ['category' => 'support', 'priority' => 'high']
            ],
            [
                'title' => 'Product Warranty',
                'body' => 'All products come with a 1-year manufacturer warranty. Extended warranty options available at checkout. Warranty covers defects in materials and workmanship.',
                'tags' => ['warranty', 'guarantee', 'protection', 'products'],
                'metadata' => ['category' => 'policy', 'priority' => 'medium']
            ],
            [
                'title' => 'Payment Methods',
                'body' => 'We accept all major credit cards, PayPal, Apple Pay, and Google Pay. All transactions are secure and encrypted. We do not store your credit card information.',
                'tags' => ['payment', 'billing', 'security', 'finance'],
                'metadata' => ['category' => 'finance', 'priority' => 'high']
            ],
            [
                'title' => 'Account Management',
                'body' => 'Create an account to track orders, save favorites, and manage your profile. You can update your information anytime. We protect your account with two-factor authentication.',
                'tags' => ['account', 'profile', 'user', 'security'],
                'metadata' => ['category' => 'user', 'priority' => 'medium']
            ],
            [
                'title' => 'Technical Documentation',
                'body' => 'Comprehensive API documentation for developers. Includes code examples in multiple languages. RESTful API with JSON responses. Rate limiting: 1000 requests per hour.',
                'tags' => ['documentation', 'API', 'developer', 'technical'],
                'metadata' => ['category' => 'technology', 'priority' => 'low']
            ],
            [
                'title' => 'Environmental Policy',
                'body' => 'We are committed to sustainability. All packaging is recyclable. We offset our carbon footprint through reforestation programs. Eco-friendly products available.',
                'tags' => ['environment', 'sustainability', 'eco-friendly', 'green'],
                'metadata' => ['category' => 'policy', 'priority' => 'medium']
            ],
        ];

        foreach ($sampleDocuments as $docData) {
            Document::create($docData);
        }

        $this->command->info('Successfully seeded ' . count($sampleDocuments) . ' documents!');
    }
}
