<?php

namespace Tests\Feature;

use App\Models\LaptopModel;
use App\Models\PhoneModel;
use App\Models\TabletModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_brand_suggestions_use_type_specific_tables(): void
    {
        PhoneModel::create(['brand' => 'Samsung', 'model' => 'Galaxy S24']);
        LaptopModel::create(['brand' => 'Lenovo', 'model' => 'ThinkPad T14']);
        TabletModel::create(['brand' => 'Apple', 'model' => 'iPad Air']);

        $this->getJson('/api/device-brands?type=phone')
            ->assertOk()
            ->assertJsonFragment(['Samsung'])
            ->assertJsonMissing(['Lenovo']);

        $this->getJson('/api/device-brands?type=laptop')
            ->assertOk()
            ->assertJsonFragment(['Lenovo'])
            ->assertJsonMissing(['Samsung']);

        $this->getJson('/api/device-brands?type=tablet')
            ->assertOk()
            ->assertJsonFragment(['Apple'])
            ->assertJsonMissing(['Lenovo']);
    }

    public function test_device_model_suggestions_are_filtered_by_type_and_brand(): void
    {
        PhoneModel::create(['brand' => 'Apple', 'model' => 'iPhone 14']);
        LaptopModel::create(['brand' => 'Apple', 'model' => 'MacBook Air M2']);
        TabletModel::create(['brand' => 'Apple', 'model' => 'iPad Pro 11']);

        $this->getJson('/api/device-models?type=laptop&brand=Apple')
            ->assertOk()
            ->assertJsonFragment(['MacBook Air M2'])
            ->assertJsonMissing(['iPhone 14'])
            ->assertJsonMissing(['iPad Pro 11']);
    }
}
