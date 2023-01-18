<?php

namespace Tests\Feature;

use App\Models\ZipCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ZipCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test.
     *
     * @return void
     */
    public function test_alive()
    {
        $response = $this->get('/api/live');

        $response->assertStatus(200)
            ->assertExactJson(['success' => true]);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function test_get_zip_code_pagination()
    {
        $response = $this->get(route('zip-codes.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function test_get_created_zip_codes()
    {
        ZipCode::factory()->create(['zip_code' => 11111, 'locality' => 'Tijuana']);
        ZipCode::factory()->count(10)->create();
        $response = $this->get(route('zip-codes.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'zip_code' => '11111',
                'locality' => 'Tijuana',
            ])
            ->assertJsonFragment([
                'total' => 11,
            ]);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function test_get_one_zip_code()
    {
        $zip_code = '222';
        $locality = 'Mexicali';
        $zip_code_attributes = [
            'zip_code' => $zip_code, 
            'locality' => $locality
        ];
        
        ZipCode::factory()->create($zip_code_attributes);

        $response = $this->get(route('zip-codes.show', [
            'zip_code' => $zip_code
        ]));

        $response->assertStatus(200)
            ->assertJsonFragment($zip_code_attributes)
            ->assertJsonStructure([
                'zip_code',
                'locality',
                'federal_entity',
                'settlements',
                'municipality'
            ]);
    }
}
