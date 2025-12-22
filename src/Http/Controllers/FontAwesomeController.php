<?php

namespace Marshmallow\NovaFontAwesome\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
     * Get or refresh Font Awesome API token.
     * Tokens are cached for their TTL duration.
     */
    protected function getAuthToken(): ?string
    {
        $apiToken = config('nova-fontawesome.api_token');

        if (!$apiToken) {
            return null;
        }

        $freeOnly = config('nova-fontawesome.free_only', true);
        $scope = $freeOnly ? 'svg_icons_free' : 'svg_icons_pro';

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
                $ttl = $data['ttl'] ?? 86400; // Default to 24 hours

                if ($accessToken) {
                    // Cache with actual TTL from response
                    Cache::put(
                        $cacheKey,
                        $accessToken,
                        now()->addSeconds($ttl)
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
        $request->validate([
            'query' => 'required|string|min:1|max:100',
            'version' => 'nullable|string',
            'first' => 'nullable|integer|min:1|max:100',
            'styles' => 'nullable|array',
            'freeOnly' => 'nullable|boolean',
        ]);

        $query = $request->input('query');
        $version = $request->input('version', config('nova-fontawesome.version', '6.x'));
        $first = $request->input('first', config('nova-fontawesome.max_results', 50));
        $styles = $request->input('styles', []);
        $freeOnly = $request->input('freeOnly', config('nova-fontawesome.free_only', true));

        // Create cache key for this search
        $cacheKey = 'fa_search_' . md5(json_encode([
            $query,
            $version,
            $first,
            $styles,
            $freeOnly,
        ]));

        // Cache results (if caching is enabled)
        $cacheDuration = config('nova-fontawesome.cache_duration', 3600);

        if ($cacheDuration > 0) {
            $results = Cache::remember($cacheKey, $cacheDuration, function () use ($query, $version, $first, $freeOnly) {
                return $this->queryFontAwesome($query, $version, $first, $freeOnly);
            });
        } else {
            $results = $this->queryFontAwesome($query, $version, $first, $freeOnly);
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
        $version = $request->input('version', config('nova-fontawesome.version', '6.x'));

        $cacheKey = 'fa_icon_' . md5($name . $version);
        $cacheDuration = config('nova-fontawesome.cache_duration', 3600);

        if ($cacheDuration > 0) {
            $icon = Cache::remember($cacheKey, $cacheDuration * 24, function () use ($name, $version) {
                return $this->getIconByName($name, $version);
            });
        } else {
            $icon = $this->getIconByName($name, $version);
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
    protected function queryFontAwesome(string $query, string $version, int $first, bool $freeOnly): array
    {
        $graphqlQuery = <<<GRAPHQL
        query SearchIcons(\$version: String!, \$query: String!, \$first: Int) {
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
                svgs {
                    familyStyle {
                        family
                        style
                    }
                    pathData
                }
            }
        }
        GRAPHQL;

        try {
            $http = Http::timeout(10);

            // Add authentication token if available
            if ($authToken = $this->getAuthToken()) {
                $http = $http->withToken($authToken);
            }

            $response = $http->post($this->apiEndpoint, [
                'query' => $graphqlQuery,
                'variables' => [
                    'version' => $version,
                    'query' => $query,
                    'first' => $first,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $icons = $data['data']['search'] ?? [];

                // Filter for free only if requested
                if ($freeOnly) {
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
    protected function getIconByName(string $name, string $version): ?array
    {
        $authToken = $this->getAuthToken();

        // If we have an auth token, use the authenticated release.icon endpoint
        if ($authToken) {
            return $this->getIconByNameAuthenticated($name, $version, $authToken);
        }

        // Otherwise, use the public search endpoint
        $results = $this->queryFontAwesome($name, $version, 10, false);

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
    protected function getIconByNameAuthenticated(string $name, string $version, string $authToken): ?array
    {
        $graphqlQuery = <<<GRAPHQL
        query GetIcon(\$version: String!, \$name: String!) {
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
                    svgs {
                        familyStyle {
                            family
                            style
                        }
                        pathData
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $response = Http::timeout(10)
                ->withToken($authToken)
                ->post($this->apiEndpoint, [
                    'query' => $graphqlQuery,
                    'variables' => [
                        'version' => $version,
                        'name' => $name,
                    ],
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
    protected function getPopularIcons(string $version, int $first): array
    {
        // Search for common icons users typically want
        $commonSearches = ['arrow', 'user', 'home', 'settings', 'search', 'check', 'close', 'menu'];
        $allIcons = [];

        foreach ($commonSearches as $term) {
            $results = $this->queryFontAwesome($term, $version, 5, true);
            $allIcons = array_merge($allIcons, $results);
            if (count($allIcons) >= $first) {
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

        return array_slice(array_values($unique), 0, $first);
    }

    /**
     * Get popular/featured icons (no search query).
     */
    public function popular(Request $request): JsonResponse
    {
        $version = $request->input('version', config('nova-fontawesome.version', '6.x'));
        $first = $request->input('first', 20);

        $cacheKey = 'fa_popular_' . md5($version . $first);
        $cacheDuration = config('nova-fontawesome.cache_duration', 3600);

        if ($cacheDuration > 0) {
            $icons = Cache::remember($cacheKey, $cacheDuration * 24, function () use ($version, $first) {
                return $this->getPopularIcons($version, $first);
            });
        } else {
            $icons = $this->getPopularIcons($version, $first);
        }

        return response()->json([
            'success' => true,
            'icons' => $icons,
        ]);
    }
}
