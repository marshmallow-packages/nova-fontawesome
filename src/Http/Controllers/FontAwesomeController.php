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
            $query, $version, $first, $styles, $freeOnly,
        ]));

        // Cache results
        $cacheDuration = config('nova-fontawesome.cache_duration', 3600);
        $results = Cache::remember($cacheKey, $cacheDuration, function () use ($query, $version, $first, $freeOnly) {
            return $this->queryFontAwesome($query, $version, $first, $freeOnly);
        });

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
        $icon = Cache::remember($cacheKey, $cacheDuration * 24, function () use ($name, $version) {
            return $this->getIconByName($name, $version);
        });

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
                    svg
                }
            }
        }
        GRAPHQL;

        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];

            // Add API token if configured
            if ($apiToken = config('nova-fontawesome.api_token')) {
                $headers['Authorization'] = 'Bearer ' . $apiToken;
            }

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($this->apiEndpoint, [
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
     */
    protected function getIconByName(string $name, string $version): ?array
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
                        svg
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];

            // Add API token if configured
            if ($apiToken = config('nova-fontawesome.api_token')) {
                $headers['Authorization'] = 'Bearer ' . $apiToken;
            }

            $response = Http::timeout(10)
                ->withHeaders($headers)
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

            return null;
        } catch (\Exception $e) {
            logger()->error('Font Awesome API exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
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
        $icons = Cache::remember($cacheKey, $cacheDuration * 24, function () use ($version, $first) {
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
        });

        return response()->json([
            'success' => true,
            'icons' => $icons,
        ]);
    }
}
