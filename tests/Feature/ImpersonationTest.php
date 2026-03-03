<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_superadmin_can_impersonate_user(): void
    {
        $superadmin = \App\Models\User::factory()->create(['role' => 'superadmin', 'is_active' => true]);
        $targetUser = \App\Models\User::factory()->create(['role' => 'company', 'is_active' => true]);

        $response = $this->actingAs($superadmin)->post(route('admin.impersonate.start', $targetUser->id));
        
        $response->assertRedirect();
        $this->assertEquals($targetUser->id, auth()->id());
        $this->assertEquals($superadmin->id, session('impersonated_by'));
    }

    public function test_impersonation_can_be_stopped(): void
    {
        $superadmin = \App\Models\User::factory()->create(['role' => 'superadmin', 'is_active' => true]);
        $targetUser = \App\Models\User::factory()->create(['role' => 'company', 'is_active' => true]);

        $this->actingAs($superadmin)->post(route('admin.impersonate.start', $targetUser->id));
        
        $response = $this->post(route('impersonate.stop'));
        
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertEquals($superadmin->id, auth()->id());
        $this->assertFalse(session()->has('impersonated_by'));
    }

    public function test_sensitive_routes_blocked_during_impersonation(): void
    {
        $superadmin = \App\Models\User::factory()->create(['role' => 'superadmin', 'is_active' => true]);
        $targetUser = \App\Models\User::factory()->create(['role' => 'company', 'is_active' => true]);

        $this->actingAs($superadmin)->post(route('admin.impersonate.start', $targetUser->id));
        
        $response = $this->post(route('dashboard.settings.update'));
        
        $response->assertStatus(403);
    }
}
