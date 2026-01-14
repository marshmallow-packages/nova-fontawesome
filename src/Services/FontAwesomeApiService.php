<?php

namespace Marshmallow\NovaFontAwesome\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Marshmallow\NovaFontAwesome\Http\Support\FontAwesomeParser;

class FontAwesomeApiService
{
    /**
     * Font Awesome GraphQL API endpoint.
     */
    protected string $apiEndpoint = 'https://api.fontawesome.com';

    /**
     * Font Awesome token endpoint.
     */
    protected string $tokenEndpoint = 'https://api.fontawesome.com/token';

    /**
     * Font Awesome API authentication token.
     */
    protected ?string $authToken = null;

    /**
     * Default Font Awesome version.
     */
    protected string $version = '6.x';

    /**
     * Whether to restrict to free icons only.
     */
    protected bool $freeOnly = true;

    /**
     * Maximum search results to return.
     */
    protected int $maxResults = 25;

    /**
     * Cache duration in seconds.
     */
    protected int $cacheDuration = 3600;

    /**
     * Whether caching is enabled.
     */
    protected bool $cacheEnabled = true;

    /**
     * Fallback icons for when API is unavailable.
     */
    protected array $fallbackIcons = [
        ['id' => 'user', 'label' => 'User', 'unicode' => 'f007'],
        ['id' => 'home', 'label' => 'Home', 'unicode' => 'f015'],
        ['id' => 'search', 'label' => 'Search', 'unicode' => 'f002'],
        ['id' => 'heart', 'label' => 'Heart', 'unicode' => 'f004'],
        ['id' => 'star', 'label' => 'Star', 'unicode' => 'f005'],
        ['id' => 'envelope', 'label' => 'Envelope', 'unicode' => 'f0e0'],
        ['id' => 'phone', 'label' => 'Phone', 'unicode' => 'f095'],
        ['id' => 'camera', 'label' => 'Camera', 'unicode' => 'f030'],
        ['id' => 'calendar', 'label' => 'Calendar', 'unicode' => 'f133'],
        ['id' => 'clock', 'label' => 'Clock', 'unicode' => 'f017'],
        ['id' => 'download', 'label' => 'Download', 'unicode' => 'f019'],
        ['id' => 'upload', 'label' => 'Upload', 'unicode' => 'f093'],
        ['id' => 'edit', 'label' => 'Edit', 'unicode' => 'f044'],
        ['id' => 'trash', 'label' => 'Trash', 'unicode' => 'f1f8'],
        ['id' => 'save', 'label' => 'Save', 'unicode' => 'f0c7'],
        ['id' => 'copy', 'label' => 'Copy', 'unicode' => 'f0c5'],
        ['id' => 'link', 'label' => 'Link', 'unicode' => 'f0c1'],
        ['id' => 'image', 'label' => 'Image', 'unicode' => 'f03e'],
        ['id' => 'video', 'label' => 'Video', 'unicode' => 'f03d'],
        ['id' => 'music', 'label' => 'Music', 'unicode' => 'f001'],
        ['id' => 'play', 'label' => 'Play', 'unicode' => 'f04b'],
        ['id' => 'pause', 'label' => 'Pause', 'unicode' => 'f04c'],
        ['id' => 'stop', 'label' => 'Stop', 'unicode' => 'f04d'],
        ['id' => 'lock', 'label' => 'Lock', 'unicode' => 'f023'],
        ['id' => 'unlock', 'label' => 'Unlock', 'unicode' => 'f09c'],
        ['id' => 'key', 'label' => 'Key', 'unicode' => 'f084'],
        ['id' => 'shield', 'label' => 'Shield', 'unicode' => 'f132'],
        ['id' => 'exclamation-triangle', 'label' => 'Warning', 'unicode' => 'f071'],
        ['id' => 'info-circle', 'label' => 'Info', 'unicode' => 'f05a'],
        ['id' => 'question-circle', 'label' => 'Question', 'unicode' => 'f059'],
        ['id' => 'check', 'label' => 'Check', 'unicode' => 'f00c'],
        ['id' => 'times', 'label' => 'Times', 'unicode' => 'f00d'],
        ['id' => 'plus', 'label' => 'Plus', 'unicode' => 'f067'],
        ['id' => 'minus', 'label' => 'Minus', 'unicode' => 'f068'],
        ['id' => 'cog', 'label' => 'Settings', 'unicode' => 'f013'],
        ['id' => 'bell', 'label' => 'Bell', 'unicode' => 'f0f3'],
        ['id' => 'bookmark', 'label' => 'Bookmark', 'unicode' => 'f02e'],
        ['id' => 'folder', 'label' => 'Folder', 'unicode' => 'f07b'],
        ['id' => 'file', 'label' => 'File', 'unicode' => 'f15b'],
        ['id' => 'database', 'label' => 'Database', 'unicode' => 'f1c0'],
    ];

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load configuration from config file.
     */
    protected function loadConfiguration(): void
    {
        $this->cacheDuration = config('nova-fontawesome.cache_duration', 3600);
        $this->cacheEnabled = $this->cacheDuration > 0;
        $this->version = config('nova-fontawesome.version', '6.x');
        $this->freeOnly = config('nova-fontawesome.free_only', true);
        $this->maxResults = config('nova-fontawesome.max_results', 25);
    }

    /**
     * Configure the service with request parameters.
     */
    public function configure(array $options = []): self
    {
        if (isset($options['version'])) {
            $this->version = $options['version'];
        }
        if (isset($options['freeOnly'])) {
            $this->freeOnly = (bool) $options['freeOnly'];
        }
        if (isset($options['maxResults'])) {
            $this->maxResults = (int) $options['maxResults'];
        }

        $this->authToken = $this->getAuthToken();

        return $this;
    }

    /**
     * Get or refresh Font Awesome API token.
     */
    protected function getAuthToken(): ?string
    {
        $apiToken = config('nova-fontawesome.api_token');

        if (! $apiToken) {
            return null;
        }

        $scope = $this->freeOnly ? 'svg_icons_free' : 'svg_icons_pro';
        $cacheKey = 'fa_auth_token_' . md5($apiToken . $scope);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withToken($apiToken)
                ->post($this->tokenEndpoint, ['scope' => $scope]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'] ?? null;
                $ttl = $data['expires_in'] ?? 3600;

                if ($accessToken && $this->cacheEnabled) {
                    Cache::put($cacheKey, $accessToken, now()->addSeconds($ttl - 100));
                }

                return $accessToken;
            }

            $this->logError('Font Awesome token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'scope' => $scope,
            ]);

            return null;
        } catch (Exception $e) {
            $this->logError('Font Awesome token exchange exception', [
                'message' => $e->getMessage(),
                'scope' => $scope,
            ]);

            return null;
        }
    }

    /**
     * Search for icons.
     */
    public function search(string $query, ?string $family = null, ?string $style = null): array
    {
        $cacheKey = $this->getCacheKey('search', [
            'query' => $query,
            'version' => $this->version,
            'maxResults' => $this->maxResults,
            'family' => $family,
            'style' => $style,
            'freeOnly' => $this->freeOnly,
        ]);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $results = $this->executeSearchQuery($query, $family, $style);

            if ($this->cacheEnabled && ! empty($results)) {
                Cache::put($cacheKey, $results, $this->cacheDuration);
            }

            return $results;
        } catch (Exception $e) {
            $this->logError('Font Awesome search failed, using fallback', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackIcons($query);
        }
    }

    /**
     * Execute the GraphQL search query.
     */
    protected function executeSearchQuery(string $query, ?string $family = null, ?string $style = null): array
    {
        [$graphqlQuery, $variables] = $this->buildSearchQuery($query, $family, $style);

        $http = Http::timeout(10);
        if ($this->authToken) {
            $http = $http->withToken($this->authToken);
        }

        $response = $http->post($this->apiEndpoint, [
            'query' => $graphqlQuery,
            'variables' => $variables,
        ]);

        if (! $response->successful()) {
            throw new Exception('GraphQL request failed: ' . $response->status());
        }

        $data = $response->json();

        if (isset($data['errors'])) {
            throw new Exception('GraphQL errors: ' . json_encode($data['errors']));
        }

        $icons = $data['data']['search'] ?? [];

        if ($this->freeOnly) {
            $icons = array_values(array_filter($icons, function ($icon) {
                return ! empty($icon['familyStylesByLicense']['free']);
            }));
        }

        // Expand icons to show each style variation as separate entry (like FA website)
        $icons = $this->expandIconsByStyle($icons, $family, $style);

        return $icons;
    }

    /**
     * Expand icons to create separate entries for each style variation.
     * This matches the Font Awesome website behavior where the same icon
     * appears multiple times (once for each available style: solid, regular, duotone, etc.).
     */
    protected function expandIconsByStyle(array $icons, ?string $family = null, ?string $style = null): array
    {
        $expanded = [];

        foreach ($icons as $icon) {
            $availableStyles = [];

            // Get styles from free license
            $freeStyles = $icon['familyStylesByLicense']['free'] ?? [];
            foreach ($freeStyles as $styleData) {
                $availableStyles[] = $styleData;
            }

            // Include pro styles if not free-only
            if (! $this->freeOnly) {
                $proStyles = $icon['familyStylesByLicense']['pro'] ?? [];
                foreach ($proStyles as $styleData) {
                    // Avoid duplicates
                    $key = ($styleData['family'] ?? 'classic') . '-' . ($styleData['style'] ?? 'solid');
                    $exists = false;
                    foreach ($availableStyles as $existing) {
                        $existingKey = ($existing['family'] ?? 'classic') . '-' . ($existing['style'] ?? 'solid');
                        if ($existingKey === $key) {
                            $exists = true;
                            break;
                        }
                    }
                    if (! $exists) {
                        $availableStyles[] = $styleData;
                    }
                }
            }

            // Filter by family/style if specified
            if ($family || $style) {
                $availableStyles = array_filter($availableStyles, function ($s) use ($family, $style) {
                    $familyMatch = ! $family || ($s['family'] ?? 'classic') === $family;
                    $styleMatch = ! $style || ($s['style'] ?? 'solid') === $style;

                    return $familyMatch && $styleMatch;
                });
            }

            // If no styles available after filtering, skip this icon
            if (empty($availableStyles)) {
                continue;
            }

            // Create separate entry for each style variation
            foreach ($availableStyles as $styleData) {
                $expandedIcon = $icon;
                // Add the specific style this entry represents
                $expandedIcon['_selectedStyle'] = [
                    'family' => $styleData['family'] ?? 'classic',
                    'style' => $styleData['style'] ?? 'solid',
                ];
                // Create unique ID for this style variation
                $expandedIcon['_uniqueId'] = $icon['id'] . '-' . ($styleData['family'] ?? 'classic') . '-' . ($styleData['style'] ?? 'solid');
                $expanded[] = $expandedIcon;
            }
        }

        return $expanded;
    }

    /**
     * Build the GraphQL search query.
     */
    protected function buildSearchQuery(string $query, ?string $family = null, ?string $style = null): array
    {
        $variables = [
            'version' => $this->version,
            'query' => $query,
            'first' => $this->maxResults,
        ];

        $withFilters = $family && $style;

        if ($withFilters) {
            $formatted = FontAwesomeParser::make()->formatForGraphql($family, $style);
            $variables['family'] = $formatted['family'];
            $variables['style'] = $formatted['style'];

            $graphqlQuery = <<<'GRAPHQL'
            query SearchIcons($version: String!, $query: String!, $first: Int, $family: Family!, $style: Style!) {
                search(version: $version, query: $query, first: $first) {
                    id
                    label
                    unicode
                    familyStylesByLicense {
                        free { family style }
                        pro { family style }
                    }
                    svgs(filter: { familyStyles: [{ family: $family, style: $style }] }) {
                        familyStyle { family style }
                        height
                        width
                        pathData
                    }
                }
            }
            GRAPHQL;
        } else {
            $graphqlQuery = <<<'GRAPHQL'
            query SearchIcons($version: String!, $query: String!, $first: Int) {
                search(version: $version, query: $query, first: $first) {
                    id
                    label
                    unicode
                    familyStylesByLicense {
                        free { family style }
                        pro { family style }
                    }
                    svgs {
                        familyStyle { family style }
                        height
                        width
                        pathData
                    }
                }
            }
            GRAPHQL;
        }

        return [$graphqlQuery, $variables];
    }

    /**
     * Get a specific icon by name.
     */
    public function getIcon(string $name, ?string $family = null, ?string $style = null): ?array
    {
        $cacheKey = $this->getCacheKey('icon', [
            'name' => $name,
            'version' => $this->version,
            'family' => $family,
            'style' => $style,
        ]);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $icon = $this->executeIconQuery($name, $family, $style);

            if (! $icon) {
                // Fallback to search
                $results = $this->search($name, $family, $style);
                foreach ($results as $result) {
                    if ($result['id'] === $name) {
                        $icon = $result;
                        break;
                    }
                }
                $icon = $icon ?? ($results[0] ?? null);
            }

            if ($icon && $this->cacheEnabled) {
                Cache::put($cacheKey, $icon, $this->cacheDuration * 24);
            }

            return $icon;
        } catch (Exception $e) {
            $this->logError('Font Awesome get icon failed', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return $this->findFallbackIcon($name);
        }
    }

    /**
     * Execute the GraphQL icon query.
     */
    protected function executeIconQuery(string $name, ?string $family = null, ?string $style = null): ?array
    {
        $hasFilter = $family || $style;
        $variables = [
            'version' => $this->version,
            'name' => $name,
        ];

        if ($hasFilter) {
            $formatted = FontAwesomeParser::make()->formatForGraphql($family, $style);
            $variables['family'] = $formatted['family'];
            $variables['style'] = $formatted['style'];

            $graphqlQuery = <<<'GRAPHQL'
            query GetIcon($version: String!, $name: String!, $family: Family!, $style: Style!) {
                release(version: $version) {
                    icon(name: $name) {
                        id
                        label
                        unicode
                        familyStylesByLicense {
                            free { family style }
                            pro { family style }
                        }
                        svgs(filter: { familyStyles: [{ family: $family, style: $style }] }) {
                            familyStyle { family style }
                            height
                            width
                            pathData
                        }
                    }
                }
            }
            GRAPHQL;
        } else {
            $graphqlQuery = <<<'GRAPHQL'
            query GetIcon($version: String!, $name: String!) {
                release(version: $version) {
                    icon(name: $name) {
                        id
                        label
                        unicode
                        familyStylesByLicense {
                            free { family style }
                            pro { family style }
                        }
                        svgs {
                            familyStyle { family style }
                            height
                            width
                            pathData
                        }
                    }
                }
            }
            GRAPHQL;
        }

        $http = Http::timeout(10);
        if ($this->authToken) {
            $http = $http->withToken($this->authToken);
        }

        $response = $http->post($this->apiEndpoint, [
            'query' => $graphqlQuery,
            'variables' => $variables,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return $data['data']['release']['icon'] ?? null;
    }

    /**
     * Get popular icons.
     */
    public function getPopularIcons(): array
    {
        $cacheKey = $this->getCacheKey('popular', [
            'version' => $this->version,
            'maxResults' => $this->maxResults,
            'freeOnly' => $this->freeOnly,
        ]);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $commonSearches = ['arrow', 'user', 'home', 'settings', 'search', 'check', 'close', 'menu'];
            $allIcons = [];

            foreach ($commonSearches as $term) {
                $results = $this->search($term);
                $allIcons = array_merge($allIcons, array_slice($results, 0, 5));
                if (count($allIcons) >= $this->maxResults) {
                    break;
                }
            }

            // Remove duplicates
            $unique = [];
            foreach ($allIcons as $icon) {
                if (! isset($unique[$icon['id']])) {
                    $unique[$icon['id']] = $icon;
                }
            }

            $icons = array_slice(array_values($unique), 0, $this->maxResults);

            if ($this->cacheEnabled && ! empty($icons)) {
                Cache::put($cacheKey, $icons, $this->cacheDuration * 24);
            }

            return $icons;
        } catch (Exception $e) {
            $this->logError('Font Awesome popular icons failed', [
                'error' => $e->getMessage(),
            ]);

            return array_slice($this->fallbackIcons, 0, $this->maxResults);
        }
    }

    /**
     * Get Font Awesome metadata (families and styles).
     */
    public function getMetadata(): array
    {
        $cacheKey = $this->getCacheKey('metadata', [
            'version' => $this->version,
            'freeOnly' => $this->freeOnly,
        ]);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $graphqlQuery = <<<'GRAPHQL'
            query GetMetadata($version: String!) {
                release(version: $version) {
                    version
                    families { id label }
                    styles { id label }
                }
            }
            GRAPHQL;

            $http = Http::timeout(10);
            if ($this->authToken) {
                $http = $http->withToken($this->authToken);
            }

            $response = $http->post($this->apiEndpoint, [
                'query' => $graphqlQuery,
                'variables' => ['version' => $this->version],
            ]);

            if (! $response->successful()) {
                throw new Exception('Metadata request failed');
            }

            $data = $response->json();
            $release = $data['data']['release'] ?? null;

            if (! $release) {
                return $this->getDefaultMetadata();
            }

            $families = $release['families'] ?? [];
            $styles = $release['styles'] ?? [];

            // Filter by configured families and styles from config
            $configuredFamilies = config('nova-fontawesome.families', ['classic', 'brands']);
            $configuredStyles = config('nova-fontawesome.styles', ['solid', 'regular', 'brands']);

            $families = array_values(array_filter($families, fn ($f) => in_array(strtolower($f['id']), $configuredFamilies)));
            $styles = array_values(array_filter($styles, fn ($s) => in_array(strtolower($s['id']), $configuredStyles)));

            $metadata = [
                'families' => $families,
                'styles' => $styles,
            ];

            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $metadata, $this->cacheDuration * 24);
            }

            return $metadata;
        } catch (Exception $e) {
            $this->logError('Font Awesome metadata failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultMetadata();
        }
    }

    /**
     * Get default metadata when API is unavailable.
     */
    protected function getDefaultMetadata(): array
    {
        $configuredFamilies = config('nova-fontawesome.families', ['classic', 'brands']);
        $configuredStyles = config('nova-fontawesome.styles', ['solid', 'regular', 'brands']);

        $familyLabels = [
            'classic' => 'Classic',
            'brands' => 'Brands',
            'duotone' => 'Duotone',
            'sharp' => 'Sharp',
            'sharp-duotone' => 'Sharp Duotone',
        ];

        $styleLabels = [
            'solid' => 'Solid',
            'regular' => 'Regular',
            'light' => 'Light',
            'thin' => 'Thin',
            'duotone' => 'Duotone',
            'brands' => 'Brands',
        ];

        $families = array_map(fn ($id) => [
            'id' => $id,
            'label' => $familyLabels[$id] ?? ucfirst($id),
        ], $configuredFamilies);

        $styles = array_map(fn ($id) => [
            'id' => $id,
            'label' => $styleLabels[$id] ?? ucfirst($id),
        ], $configuredStyles);

        return [
            'families' => $families,
            'styles' => $styles,
        ];
    }

    /**
     * Run diagnostic tests on the API connection.
     */
    public function runDiagnostics(): array
    {
        $results = [
            'timestamp' => now()->toIso8601String(),
            'version' => $this->version,
            'freeOnly' => $this->freeOnly,
            'cacheEnabled' => $this->cacheEnabled,
            'cacheDuration' => $this->cacheDuration,
            'hasApiToken' => ! empty(config('nova-fontawesome.api_token')),
            'tests' => [],
        ];

        // Test 1: API Connectivity
        $results['tests']['api_connectivity'] = $this->testApiConnectivity();

        // Test 2: Token Exchange (if API token configured)
        if ($results['hasApiToken']) {
            $results['tests']['token_exchange'] = $this->testTokenExchange();
        }

        // Test 3: Search Query
        $results['tests']['search_query'] = $this->testSearchQuery();

        // Test 4: Cache
        $results['tests']['cache'] = $this->testCache();

        // Overall status
        $results['status'] = collect($results['tests'])->every(fn ($t) => $t['success']) ? 'healthy' : 'degraded';

        return $results;
    }

    /**
     * Test API connectivity.
     */
    protected function testApiConnectivity(): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(5)->get($this->apiEndpoint);
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'success' => true,
                'message' => 'API is reachable',
                'duration_ms' => $duration,
                'status_code' => $response->status(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'API unreachable: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }

    /**
     * Test token exchange.
     */
    protected function testTokenExchange(): array
    {
        $start = microtime(true);

        try {
            // Clear cached token for fresh test
            $apiToken = config('nova-fontawesome.api_token');
            $scope = $this->freeOnly ? 'svg_icons_free' : 'svg_icons_pro';

            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withToken($apiToken)
                ->post($this->tokenEndpoint, ['scope' => $scope]);

            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => 'Token exchange successful',
                    'duration_ms' => $duration,
                    'expires_in' => $data['expires_in'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'Token exchange failed: ' . $response->status(),
                'duration_ms' => $duration,
                'response' => $response->body(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Token exchange error: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }

    /**
     * Test search query.
     */
    protected function testSearchQuery(): array
    {
        $start = microtime(true);

        try {
            $results = $this->search('user');
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'success' => count($results) > 0,
                'message' => count($results) > 0 ? 'Search returned ' . count($results) . ' results' : 'Search returned no results',
                'duration_ms' => $duration,
                'result_count' => count($results),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }

    /**
     * Test cache functionality.
     */
    protected function testCache(): array
    {
        if (! $this->cacheEnabled) {
            return [
                'success' => true,
                'message' => 'Cache is disabled',
                'enabled' => false,
            ];
        }

        $start = microtime(true);
        $testKey = 'fa_cache_test_' . time();
        $testValue = ['test' => true, 'timestamp' => now()->toIso8601String()];

        try {
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'success' => $retrieved === $testValue,
                'message' => $retrieved === $testValue ? 'Cache is working' : 'Cache read/write mismatch',
                'duration_ms' => $duration,
                'enabled' => true,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Cache error: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
                'enabled' => true,
            ];
        }
    }

    /**
     * Get fallback icons matching a query.
     */
    public function getFallbackIcons(string $query = ''): array
    {
        if (empty($query)) {
            return $this->formatFallbackIcons(array_slice($this->fallbackIcons, 0, $this->maxResults));
        }

        $query = strtolower($query);
        $matched = array_filter($this->fallbackIcons, function ($icon) use ($query) {
            return $this->fuzzyMatch($query, $icon['id']) || $this->fuzzyMatch($query, $icon['label']);
        });

        return $this->formatFallbackIcons(array_slice(array_values($matched), 0, $this->maxResults));
    }

    /**
     * Find a specific fallback icon by name.
     */
    protected function findFallbackIcon(string $name): ?array
    {
        foreach ($this->fallbackIcons as $icon) {
            if ($icon['id'] === $name) {
                return $this->formatFallbackIcon($icon);
            }
        }

        return null;
    }

    /**
     * Format fallback icons to match API response structure.
     */
    protected function formatFallbackIcons(array $icons): array
    {
        return array_map(fn ($icon) => $this->formatFallbackIcon($icon), $icons);
    }

    /**
     * Format a single fallback icon.
     */
    protected function formatFallbackIcon(array $icon): array
    {
        return [
            'id' => $icon['id'],
            'label' => $icon['label'],
            'unicode' => $icon['unicode'],
            'familyStylesByLicense' => [
                'free' => [
                    ['family' => 'classic', 'style' => 'solid'],
                    ['family' => 'classic', 'style' => 'regular'],
                ],
                'pro' => [],
            ],
            'svgs' => [],
            '_fallback' => true,
        ];
    }

    /**
     * Perform fuzzy matching between query and target.
     */
    protected function fuzzyMatch(string $query, string $target): bool
    {
        $query = strtolower($query);
        $target = strtolower($target);

        // Exact or substring match
        if (str_contains($target, $query)) {
            return true;
        }

        // Levenshtein distance (allow ~30% character difference)
        $distance = levenshtein($query, $target);
        $maxDistance = max(1, strlen($query) * 0.3);

        return $distance <= $maxDistance;
    }

    /**
     * Generate a cache key.
     */
    protected function getCacheKey(string $type, array $params): string
    {
        return 'fa_' . $type . '_' . md5(json_encode($params));
    }

    /**
     * Log an error message.
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error('FontAwesome: ' . $message, $context);
    }

    /**
     * Get current configuration.
     */
    public function getConfiguration(): array
    {
        return [
            'version' => $this->version,
            'freeOnly' => $this->freeOnly,
            'maxResults' => $this->maxResults,
            'cacheDuration' => $this->cacheDuration,
            'cacheEnabled' => $this->cacheEnabled,
            'hasAuthToken' => ! empty($this->authToken),
        ];
    }
}
