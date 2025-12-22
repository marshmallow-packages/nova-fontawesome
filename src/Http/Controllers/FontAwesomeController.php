<?php

namespace Marshmallow\NovaFontAwesome\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Marshmallow\NovaFontAwesome\Http\Support\FontAwesomeParser;

class FontAwesomeController extends Controller
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
    protected string $authToken = '';

    /**
     * Default Font Awesome version.
     */
    protected string $faVersion = '6.x';

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

    protected function setDefaultFromRequest(Request $request): void
    {
        $this->cacheDuration = config('nova-fontawesome.cache_duration', 3600);
        $this->cacheEnabled = $this->cacheDuration > 0;

        $this->faVersion = $request->input('version', config('nova-fontawesome.version', '6.x'));
        $this->freeOnly = $request->input('freeOnly', config('nova-fontawesome.free_only', true));
        $this->maxResults = $request->input('first', config('nova-fontawesome.max_results', 25));

        $this->setAuthToken();
    }

    protected function setAuthToken(): void
    {
        $this->authToken = $this->authToken ?: $this->getAuthToken();
    }

    /**
     * Get or refresh Font Awesome API token.
     * Tokens are cached for their TTL duration.
     */
    protected function getAuthToken(): ?string
    {
        $apiToken = config('nova-fontawesome.api_token');

        if (!$apiToken) {
            return null;
        }

        $scope = $this->freeOnly ? 'svg_icons_free' : 'svg_icons_pro';

        // Check cache first - include scope in cache key
        $cacheKey = 'fa_auth_token_' . md5($apiToken . $scope);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->withToken($apiToken)
                ->post($this->tokenEndpoint, [
                    'scope' => $scope,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Token endpoint returns access_token and ttl
                $accessToken = $data['access_token'] ?? null;
                $ttl = $data['expires_in'] ?? 3600; // Default to 24 hours

                if ($accessToken) {
                    // Cache with actual TTL from response
                    Cache::put(
                        $cacheKey,
                        $accessToken,
                        now()->addSeconds(($ttl - 100))
                    );

                    return $accessToken;
                }
            }

            logger()->error('Font Awesome token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'scope' => $scope,
            ]);

            return null;
        } catch (\Exception $e) {
            logger()->error('Font Awesome token exchange exception', [
                'message' => $e->getMessage(),
                'scope' => $scope,
            ]);

            return null;
        }
    }

    /**
     * Search icons via the Font Awesome GraphQL API.
     */
    public function search(Request $request): JsonResponse
    {
        $this->setDefaultFromRequest($request);

        $request->validate([
            'query' => 'required|string|min:1|max:100',
            'version' => 'nullable|string',
            'first' => 'nullable|integer|min:1|max:100',
            'styles' => 'nullable|array',
            'freeOnly' => 'nullable|boolean',
        ]);

        $query = $request->input('query');
        $styles = $request->input('styles', []);

        // Create cache key for this search
        $cacheKey = 'fa_search_' . md5(json_encode([
            $query,
            $this->faVersion,
            $this->maxResults,
            $styles,
            $this->freeOnly,
        ]));

        if ($this->cacheEnabled) {
            $results = Cache::remember($cacheKey, $this->cacheDuration, function () use ($query) {
                return $this->queryFontAwesome(query: $query);
            });
        } else {
            $results = $this->queryFontAwesome(query: $query);
        }

        // Filter by styles if specified
        if (!empty($styles)) {
            $results = array_filter($results, function ($icon) use ($styles) {
                foreach ($icon['familyStylesByLicense'] ?? [] as $license => $licenseStyles) {
                    foreach ($licenseStyles as $style) {
                        if (in_array($style['style'], $styles)) {
                            return true;
                        }
                    }
                }
                return false;
            });
            $results = array_values($results);
        }

        return response()->json([
            'success' => true,
            'icons' => $results,
        ]);
    }

    /**
     * Get a specific icon by name.
     */
    public function icon(Request $request, string $name): JsonResponse
    {
        $this->setDefaultFromRequest($request);

        $cacheKey = 'fa_icon_' . md5($name . $this->faVersion);

        $family = $request->input('family', null);
        $style = $request->input('style', null);

        $icon = null;

        if ($this->cacheEnabled) {
            $icon = Cache::get($cacheKey);
        }

        if (!$this->cacheEnabled || $icon === null) {
            $icon = $this->getIconByName(
                name: $name,
                family: $family,
                style: $style,
            );
        }

        if ($this->cacheEnabled && $icon === null) {
            $cacheDuration = $this->cacheDuration * 24;
            Cache::put($cacheKey, $icon, $cacheDuration);
        }

        if (!$icon) {
            return response()->json([
                'success' => false,
                'message' => 'Icon not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'icon' => $icon,
        ]);
    }

    /**
     * Query the Font Awesome GraphQL API.
     */
    protected function queryFontAwesome(
        string $query,
        ?int $first = null,
        ?string $family = null,
        ?string $style = null,
    ): array {
        if (!$first) {
            $first = $this->maxResults;
        }

        $queryVariables = [
            'version' => $this->faVersion,
            'query' => $query,
            'first' => $first,
        ];

        if ($family) {
            $queryVariables['family'] = Str::upper($family);
        }
        if ($style) {
            $queryVariables['style'] = Str::upper($style);
        }

        $graphqlQuery = <<<GRAPHQL
        query SearchIcons(\$version: String!, \$query: String!, \$first: Int, \$family: Family!, \$style: Style!) {
            search(version: \$version, query: \$query, first: \$first) {
                id
                label
                unicode
                familyStylesByLicense {
                    free {
                        family
                        style
                    }
                    pro {
                        family
                        style
                    }
                }
                svgs(filter: { familyStyles: [{ family: \$family, style: \$style }] }) {
                    familyStyle {
                        family
                        style
                    }
                    height
                    width
                    pathData
                }
            }
        }
        GRAPHQL;

        try {
            $http = Http::timeout(10);

            // Add authentication token if available
            if ($this->authToken) {
                $http->withToken($this->authToken);
            }

            $response = $http->post($this->apiEndpoint, [
                'query' => $graphqlQuery,
                'variables' => $queryVariables,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $icons = $data['data']['search'] ?? [];

                // Filter for free only if requested
                if ($this->freeOnly) {
                    $icons = array_filter($icons, function ($icon) {
                        return !empty($icon['familyStylesByLicense']['free']);
                    });
                    $icons = array_values($icons);
                }

                return $icons;
            }

            logger()->error('Font Awesome API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            logger()->error('Font Awesome API exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get a single icon by name.
     * Uses authenticated release.icon endpoint if token is available,
     * otherwise falls back to public search endpoint.
     */
    protected function getIconByName(
        string $name,
        ?string $family = null,
        ?string $style = null,
    ): ?array {

        // If we have an auth token, use the authenticated release.icon endpoint

        $result = $this->queryIconByName($name, $family, $style);
        if ($result) {
            return $result;
        }

        // Otherwise, use the public search endpoint
        $results = $this->queryFontAwesome(
            query: $name,
            family: $family,
            style: $style,
        );

        // Find exact match by id
        foreach ($results as $icon) {
            if ($icon['id'] === $name) {
                return $icon;
            }
        }

        // If no exact match, return first result if available
        return $results[0] ?? null;
    }

    /**
     * Get a single icon by name using authenticated release.icon endpoint.
     */
    protected function queryIconByName(
        string $name,
        ?string $family,
        ?string $style
    ): ?array {
        $queryVariables = [
            'version' => $this->faVersion,
            'name' => $name,
        ];

        if ($style) {
            $formatted = FontAwesomeParser::make()->formatForGraphql($style, $family);
            $queryVariables['family'] = $formatted['family'];
            $queryVariables['style'] = $formatted['style'];
        }

        $graphqlQuery = <<<GRAPHQL
        query GetIcon(\$version: String!, \$name: String!, \$family: Family!, \$style: Style!) {
            release(version: \$version) {
                icon(name: \$name) {
                    id
                    label
                    unicode
                    familyStylesByLicense {
                        free {
                            family
                            style
                        }
                        pro {
                            family
                            style
                            }
                    }
                    svgs(filter: { familyStyles: [{ family: \$family, style: \$style }] }) {
                        familyStyle {
                            family
                            style
                        }
                        height
                        width
                        pathData
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $request = Http::timeout(10);

            if ($this->authToken) {
                $request->withToken($this->authToken);
            }

            $response = $request->post($this->apiEndpoint, [
                'query' => $graphqlQuery,
                'variables' => $queryVariables,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['release']['icon'] ?? null;
            }

            logger()->error('Font Awesome API error (authenticated)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            logger()->error('Font Awesome API exception (authenticated)', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get popular icons helper method.
     */
    protected function getPopularIcons(): array
    {
        // Search for common icons users typically want
        $commonSearches = ['arrow', 'user', 'home', 'settings', 'search', 'check', 'close', 'menu'];
        $allIcons = [];

        foreach ($commonSearches as $term) {
            $results = $this->queryFontAwesome($term, 5, true);
            $allIcons = array_merge($allIcons, $results);
            if (count($allIcons) >= $this->maxResults) {
                break;
            }
        }

        // Remove duplicates by id
        $unique = [];
        foreach ($allIcons as $icon) {
            if (!isset($unique[$icon['id']])) {
                $unique[$icon['id']] = $icon;
            }
        }

        return array_slice(array_values($unique), 0, $this->maxResults);
    }

    /**
     * Get popular/featured icons (no search query).
     */
    public function popular(Request $request): JsonResponse
    {
        $this->setDefaultFromRequest($request);

        $cacheKey = 'fa_popular_' . md5($this->faVersion . $this->maxResults);

        if ($this->cacheEnabled) {
            $icons = Cache::remember($cacheKey, $this->cacheDuration * 24, function () {
                return $this->getPopularIcons();
            });
        } else {
            $icons = $this->getPopularIcons();
        }

        return response()->json([
            'success' => true,
            'icons' => $icons,
        ]);
    }
}
