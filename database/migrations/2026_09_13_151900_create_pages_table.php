<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('meta_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('footer_links', function (Blueprint $table) {
            $table->foreignId('page_id')->nullable()->after('url')->constrained('pages')->nullOnDelete();
        });

        $now = now();
        $defaults = [
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'meta_description' => 'Get in touch with our support team.',
                'content' => '<p>Have a question about your order or our products? We are happy to help.</p><p>Email us or call using the contact details in the website footer. Our team typically replies within 1–2 business days.</p>',
                'sort_order' => 0,
            ],
            [
                'title' => 'FAQs',
                'slug' => 'faqs',
                'meta_description' => 'Frequently asked questions about orders, shipping, and returns.',
                'content' => '<h2>Orders</h2><p><strong>How do I track my order?</strong><br>Use the Track Order page with your order number and email or phone.</p><h2>Shipping</h2><p><strong>How long does delivery take?</strong><br>Delivery times depend on your location and product availability. See Shipping Info for details.</p><h2>Returns</h2><p><strong>Can I return an item?</strong><br>Please read our Returns policy for eligibility and steps.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => 'Shipping Info',
                'slug' => 'shipping-info',
                'meta_description' => 'Shipping zones, fees, and delivery timelines.',
                'content' => '<p>We ship across our supported delivery zones. Shipping fees are calculated at checkout based on your address.</p><p>Orders are processed after confirmation. You will receive status updates as your order moves through our warehouse and delivery network.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => 'Returns',
                'slug' => 'returns',
                'meta_description' => 'Return and exchange policy.',
                'content' => '<p>If an item is unused and in original condition, you may request a return or exchange within the stated return window.</p><p>Contact us with your order number to start a return. Custom or final-sale items may not be eligible.</p>',
                'sort_order' => 3,
            ],
        ];

        foreach ($defaults as $page) {
            $pageId = DB::table('pages')->insertGetId([
                'title' => $page['title'],
                'slug' => $page['slug'],
                'content' => $page['content'],
                'meta_description' => $page['meta_description'],
                'sort_order' => $page['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('footer_links')
                ->where('label', $page['title'])
                ->update([
                    'page_id' => $pageId,
                    'url' => '/pages/'.$page['slug'],
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('footer_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('page_id');
        });

        Schema::dropIfExists('pages');
    }
};
