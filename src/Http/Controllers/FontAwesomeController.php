<?php

namespace Marshmallow\NovaFontAwesome\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Marshmallow\NovaFontAwesome\Services\FontAwesomeApiService;

class FontAwesomeController extends Controller
{
    /**
     * The FontAwesome API service.
     */
    protected FontAwesomeApiService $apiService;

    /**
     * Create a new controller instance.
     */
    public function __construct(FontAwesomeApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Configure the API service from request parameters.
     */
    protected function configureServiceFromRequest(Request $request): void
    {
        $this->apiService->configure([
            'version' => $request->input('version', config('nova-fontawesome.version', '6.x')),
            'freeOnly' => $request->input('freeOnly', config('nova-fontawesome.free_only', true)),
            'maxResults' => $request->input('first', config('nova-fontawesome.max_results', 25)),
        ]);
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

        $this->configureServiceFromRequest($request);

        $query = $request->input('query');
        $family = $request->input('family');
        $style = $request->input('style');
        $styles = $request->input('styles', []);

        $results = $this->apiService->search($query, $family, $style);

        // Filter by styles if specified
        if (!empty($styles)) {
            $results = array_filter($results, function ($icon) use ($styles) {
                foreach ($icon['familyStylesByLicense'] ?? [] as $license => $licenseStyles) {
                    foreach ($licenseStyles as $styleData) {
                        if (in_array($styleData['style'], $styles)) {
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
        $this->configureServiceFromRequest($request);

        $family = $request->input('family');
        $style = $request->input('style');

        $icon = $this->apiService->getIcon($name, $family, $style);

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
     * Get popular/featured icons (no search query).
     */
    public function popular(Request $request): JsonResponse
    {
        $this->configureServiceFromRequest($request);

        $icons = $this->apiService->getPopularIcons();

        return response()->json([
            'success' => true,
            'icons' => $icons,
        ]);
    }

    /**
     * Get available FontAwesome metadata (families and styles).
     */
    public function metadata(Request $request): JsonResponse
    {
        $this->configureServiceFromRequest($request);

        $metadata = $this->apiService->getMetadata();

        return response()->json([
            'success' => true,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Debug endpoint for troubleshooting API integration.
     */
    public function debug(Request $request): JsonResponse
    {
        $this->configureServiceFromRequest($request);

        $diagnostics = $this->apiService->runDiagnostics();

        return response()->json([
            'success' => $diagnostics['status'] === 'healthy',
            'diagnostics' => $diagnostics,
            'configuration' => $this->apiService->getConfiguration(),
        ]);
    }

    /**
     * Get fallback icons when API is unavailable.
     */
    public function fallback(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        $icons = $this->apiService->getFallbackIcons($query);

        return response()->json([
            'success' => true,
            'icons' => $icons,
            'fallback' => true,
        ]);
    }
}
