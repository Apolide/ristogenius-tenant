<?php

namespace Tests\Feature\Localization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_test/locale', fn () => response()->json([
            'locale' => app()->getLocale(),
            'title' => __('personnel.title'),
        ]));
    }

    public function test_authenticated_user_language_is_used_for_web_requests(): void
    {
        $user = User::factory()->create(['lang' => 'de']);

        $this->actingAs($user)->get('/_test/locale')
            ->assertOk()
            ->assertJson(['locale' => 'de', 'title' => 'Personal']);
    }

    public function test_unsupported_or_missing_language_uses_safe_fallback(): void
    {
        $user = User::factory()->create(['lang' => 'fr']);

        $this->actingAs($user)->get('/_test/locale')
            ->assertOk()
            ->assertJson(['locale' => 'it', 'title' => 'Personale']);
    }
}
