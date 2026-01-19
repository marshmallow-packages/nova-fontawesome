<?php

namespace Marshmallow\NovaFontAwesome\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Marshmallow\NovaFontAwesome\Services\FontAwesomeApiService;
use Marshmallow\NovaFontAwesome\Tests\TestCase;

class FontAwesomeApiServiceTest extends TestCase
{
    protected FontAwesomeApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FontAwesomeApiService;
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(FontAwesomeApiService::class, $this->service);
    }

    /** @test */
    public function it_can_be_configured(): void
    {
        $this->service->configure([
            'version' => '6.5.0',
            'freeOnly' => false,
            'maxResults' => 50,
        ]);

        $config = $this->service->getConfiguration();

        $this->assertEquals('6.5.0', $config['version']);
        $this->assertFalse($config['freeOnly']);
        $this->assertEquals(50, $config['maxResults']);
    }

    /** @test */
    public function it_returns_fallback_icons_when_api_fails(): void
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $result = $this->service->search('user');

        $this->assertNotEmpty($result['icons']);
        $this->assertArrayHasKey('id', $result['icons'][0]);
    }

    /** @test */
    public function it_filters_fallback_icons_by_query(): void
    {
        $icons = $this->service->getFallbackIcons('user');

        $this->assertNotEmpty($icons);

        foreach ($icons as $icon) {
            $matchesId = str_contains(strtolower($icon['id']), 'user');
            $matchesLabel = str_contains(strtolower($icon['label']), 'user');
            $this->assertTrue($matchesId || $matchesLabel);
        }
    }

    /** @test */
    public function it_returns_all_fallback_icons_when_query_is_empty(): void
    {
        $icons = $this->service->getFallbackIcons('');

        $this->assertNotEmpty($icons);
        $this->assertLessThanOrEqual(25, count($icons)); // max_results default
    }

    /** @test */
    public function fallback_icons_have_correct_structure(): void
    {
        $icons = $this->service->getFallbackIcons('home');

        $this->assertNotEmpty($icons);

        $icon = $icons[0];
        $this->assertArrayHasKey('id', $icon);
        $this->assertArrayHasKey('label', $icon);
        $this->assertArrayHasKey('unicode', $icon);
        $this->assertArrayHasKey('familyStylesByLicense', $icon);
        $this->assertArrayHasKey('_fallback', $icon);
        $this->assertTrue($icon['_fallback']);
    }

    /** @test */
    public function it_can_run_diagnostics(): void
    {
        Http::fake([
            'https://api.fontawesome.com' => Http::response(['status' => 'ok'], 200),
            'https://api.fontawesome.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $diagnostics = $this->service->runDiagnostics();

        $this->assertArrayHasKey('timestamp', $diagnostics);
        $this->assertArrayHasKey('version', $diagnostics);
        $this->assertArrayHasKey('tests', $diagnostics);
        $this->assertArrayHasKey('status', $diagnostics);
    }

    /** @test */
    public function it_returns_default_metadata_when_api_fails(): void
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $metadata = $this->service->getMetadata();

        $this->assertArrayHasKey('families', $metadata);
        $this->assertArrayHasKey('styles', $metadata);
        $this->assertNotEmpty($metadata['families']);
        $this->assertNotEmpty($metadata['styles']);
    }

    /** @test */
    public function it_caches_search_results(): void
    {
        $searchResults = [
            ['id' => 'user', 'label' => 'User', 'unicode' => 'f007'],
        ];

        Http::fake([
            '*' => Http::sequence()
                ->push(['data' => ['search' => $searchResults]], 200)
                ->push(['data' => ['search' => []]], 200), // Second call should not be made
        ]);

        // First call
        $result1 = $this->service->search('user');

        // Second call should use cache
        $result2 = $this->service->search('user');

        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_supports_fuzzy_matching(): void
    {
        // Test that 'usr' matches 'user'
        $icons = $this->service->getFallbackIcons('usr');

        $foundUser = false;
        foreach ($icons as $icon) {
            if ($icon['id'] === 'user') {
                $foundUser = true;
                break;
            }
        }

        $this->assertTrue($foundUser, 'Fuzzy matching should find "user" when searching for "usr"');
    }

    /** @test */
    public function it_handles_successful_api_response(): void
    {
        $mockResponse = [
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
                        'svgs' => [],
                    ],
                ],
            ],
        ];

        Http::fake([
            '*' => Http::response($mockResponse, 200),
        ]);

        $result = $this->service->search('user');

        $this->assertNotEmpty($result['icons']);
        $this->assertEquals('user', $result['icons'][0]['id']);
    }

    /** @test */
    public function it_filters_pro_icons_when_free_only(): void
    {
        $mockResponse = [
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
                        'id' => 'user-astronaut',
                        'label' => 'User Astronaut',
                        'unicode' => 'f4fb',
                        'familyStylesByLicense' => [
                            'free' => [], // Pro only icon
                            'pro' => [
                                ['family' => 'classic', 'style' => 'solid'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            '*' => Http::response($mockResponse, 200),
        ]);

        $this->service->configure(['freeOnly' => true]);
        $result = $this->service->search('user');

        $this->assertCount(1, $result['icons']);
        $this->assertEquals('user', $result['icons'][0]['id']);
    }
}
