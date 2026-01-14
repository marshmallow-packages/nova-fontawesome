<?php

namespace Marshmallow\NovaFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Marshmallow\NovaFontAwesome\Tests\TestCase;

class FontAwesomeControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock all HTTP requests to Font Awesome API
        Http::fake([
            'https://api.fontawesome.com' => Http::response([
                'data' => [
                    'search' => [
                        [
                            'id' => 'user',
                            'label' => 'User',
                            'unicode' => 'f007',
                            'familyStylesByLicense' => [
                                'free' => [['family' => 'classic', 'style' => 'solid']],
                                'pro' => [],
                            ],
                            'svgs' => [],
                        ],
                    ],
                ],
            ], 200),
            'https://api.fontawesome.com/*' => Http::response([
                'data' => [
                    'release' => [
                        'version' => '6.x',
                        'families' => [
                            ['id' => 'classic', 'label' => 'Classic'],
                            ['id' => 'brands', 'label' => 'Brands'],
                        ],
                        'styles' => [
                            ['id' => 'solid', 'label' => 'Solid'],
                            ['id' => 'regular', 'label' => 'Regular'],
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    /** @test */
    public function it_can_search_icons(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/search?query=user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'icons',
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_validates_search_query(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/search');

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_can_get_popular_icons(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/popular');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'icons',
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_get_metadata(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/metadata');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'metadata' => [
                    'families',
                    'styles',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_get_icon_by_name(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'release' => [
                        'icon' => [
                            'id' => 'user',
                            'label' => 'User',
                            'unicode' => 'f007',
                            'familyStylesByLicense' => [
                                'free' => [['family' => 'classic', 'style' => 'solid']],
                                'pro' => [],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/nova-vendor/nova-fontawesome/icon/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'icon',
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_404_for_unknown_icon(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'release' => [
                        'icon' => null,
                    ],
                    'search' => [],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/nova-vendor/nova-fontawesome/icon/nonexistent-icon-12345');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_can_access_debug_endpoint(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/debug');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'diagnostics' => [
                    'timestamp',
                    'version',
                    'tests',
                    'status',
                ],
                'configuration',
            ]);
    }

    /** @test */
    public function it_can_get_fallback_icons(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/fallback?query=user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'icons',
                'fallback',
            ])
            ->assertJson([
                'success' => true,
                'fallback' => true,
            ]);
    }

    /** @test */
    public function it_accepts_version_parameter(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/search?query=user&version=6.5.0');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_accepts_first_parameter_for_max_results(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/search?query=user&first=10');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_accepts_styles_parameter(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'search' => [
                        [
                            'id' => 'user',
                            'label' => 'User',
                            'unicode' => 'f007',
                            'familyStylesByLicense' => [
                                'free' => [
                                    ['family' => 'classic', 'style' => 'solid'],
                                    ['family' => 'classic', 'style' => 'regular'],
                                ],
                                'pro' => [],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/nova-vendor/nova-fontawesome/search?query=user&styles[]=solid');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_accepts_free_only_parameter(): void
    {
        $response = $this->getJson('/nova-vendor/nova-fontawesome/search?query=user&freeOnly=true');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_results_by_styles(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'search' => [
                        [
                            'id' => 'user',
                            'label' => 'User',
                            'unicode' => 'f007',
                            'familyStylesByLicense' => [
                                'free' => [
                                    ['family' => 'classic', 'style' => 'solid'],
                                ],
                                'pro' => [],
                            ],
                        ],
                        [
                            'id' => 'heart',
                            'label' => 'Heart',
                            'unicode' => 'f004',
                            'familyStylesByLicense' => [
                                'free' => [
                                    ['family' => 'classic', 'style' => 'regular'],
                                ],
                                'pro' => [],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/nova-vendor/nova-fontawesome/search?query=test&styles[]=solid');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $icons = $response->json('icons');
        $this->assertCount(1, $icons);
        $this->assertEquals('user', $icons[0]['id']);
    }
}
