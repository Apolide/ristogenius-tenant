<?php

namespace Tests\Feature\Profile;

use App\Livewire\Profile\ProfileEdit;
use App\Models\TenantProfile;
use Database\Seeders\TenantProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TenantProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tenant.name', 'Ristorante Test');
        config()->set('tenant.slug', 'ristorante-test');
        config()->set('media-library.disk_name', 'public');
    }

    public function test_tenant_profile_seeder_creates_the_default_profile_from_configuration(): void
    {
        $this->seed(TenantProfileSeeder::class);

        $this->assertDatabaseHas('tenant_profiles', [
            'id' => 1,
            'name' => 'Ristorante Test',
            'company_name' => 'Ristorante Test Srl',
            'city' => 'Lecce',
            'province' => 'LE',
            'postcode' => '73100',
            'address' => 'Via Roma 1',
        ]);
    }

    public function test_profile_component_creates_a_profile_when_missing(): void
    {
        Livewire::test(ProfileEdit::class)
            ->assertSet('name', 'Ristorante Test')
            ->assertSet('company_name', null)
            ->assertSet('profileImage', null);

        $this->assertDatabaseHas('tenant_profiles', [
            'name' => 'Ristorante Test',
        ]);
    }

    public function test_profile_settings_can_be_updated(): void
    {
        TenantProfile::create(['name' => 'Ristorante Test']);

        Livewire::test(ProfileEdit::class)
            ->set('company_name', 'Nuova Societa Srl')
            ->set('city', 'Milano')
            ->set('province', 'MI')
            ->set('postcode', '20100')
            ->set('address', 'Via Test 10')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_profiles', [
            'name' => 'Ristorante Test',
            'company_name' => 'Nuova Societa Srl',
            'city' => 'Milano',
            'province' => 'MI',
            'postcode' => '20100',
            'address' => 'Via Test 10',
        ]);
    }

    public function test_logo_upload_is_stored_under_the_tenant_slug_and_loaded_from_original_file(): void
    {
        Storage::fake('public');
        TenantProfile::create(['name' => 'Ristorante Test']);

        Livewire::test(ProfileEdit::class)
            ->set('image', UploadedFile::fake()->image('logo.png', 600, 400)->size(256))
            ->call('update')
            ->assertHasNoErrors();

        $profile = TenantProfile::firstOrFail();
        $media = $profile->getFirstMedia('logo');

        $this->assertNotNull($media);
        $this->assertSame('public', $media->disk);
        $this->assertSame('logo.png', $media->file_name);
        $this->assertStringStartsWith('ristorante-test/logo/', $media->getPathRelativeToRoot());
        $this->assertStringEndsWith('/logo.png', $media->getPathRelativeToRoot());
        $this->assertSame([], $media->generated_conversions);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());

        Livewire::test(ProfileEdit::class)
            ->assertSet('profileImage', URL::to($profile->getFirstMediaUrl('logo')));
    }

    public function test_logo_upload_must_be_an_image(): void
    {
        Storage::fake('public');
        TenantProfile::create(['name' => 'Ristorante Test']);

        Livewire::test(ProfileEdit::class)
            ->set('image', UploadedFile::fake()->image('logo.gif'))
            ->call('update')
            ->assertHasErrors(['image']);

        $this->assertSame(0, TenantProfile::firstOrFail()->media()->count());
    }

    public function test_profile_validation_limits_text_fields(): void
    {
        TenantProfile::create(['name' => 'Ristorante Test']);

        Livewire::test(ProfileEdit::class)
            ->set('company_name', str_repeat('a', 256))
            ->set('postcode', str_repeat('1', 21))
            ->call('update')
            ->assertHasErrors(['company_name', 'postcode']);
    }
}
