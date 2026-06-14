<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LegalArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LegalWorkbenchSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_systems_and_articles()
    {
        // 1. Create a mock expert user
        $expert = User::create([
            'name' => 'Expert Test User',
            'email' => 'expert.test.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'expert',
            'is_active' => true,
        ]);

        // 2. Create a mock legal article
        $article = LegalArticle::create([
            'legislation_id' => 'test-law-123',
            'legislation_title' => 'نظام تجريبي للبحث المتقدم',
            'article_title' => 'المادة الثالثة والثلاثون',
            'content' => 'نص المادة التجريبية 33 للتحقق من صحة البحث.',
            'reference_id' => 'art33',
        ]);

        // 3. Act as the expert and hit the search systems endpoint
        $response = $this->actingAs($expert)
            ->get(route('dashboard.expert.legal_workbench.search_systems', ['q' => 'المتقدم']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['نظام تجريبي للبحث المتقدم']);

        // 4. Hit the search articles endpoint by text query
        $response2 = $this->actingAs($expert)
            ->get(route('dashboard.expert.legal_workbench.search_articles', [
                'system' => 'نظام تجريبي للبحث المتقدم',
                'q' => 'الثالثة'
            ]));

        $response2->assertStatus(200);
        $this->assertTrue(collect($response2->json())->contains('id', $article->id));

        // 5. Hit the search articles endpoint by digit query (33)
        $response3 = $this->actingAs($expert)
            ->get(route('dashboard.expert.legal_workbench.search_articles', [
                'system' => 'نظام تجريبي للبحث المتقدم',
                'q' => '33'
            ]));

        $response3->assertStatus(200);
        $this->assertTrue(collect($response3->json())->contains('id', $article->id));

        // Clean up
        $article->delete();
        $expert->delete();
    }
}
