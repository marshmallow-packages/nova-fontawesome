<?php

declare(strict_types=1);

namespace Marshmallow\NovaFontAwesome\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Marshmallow\NovaFontAwesome\Services\IconClassConverter;
use Marshmallow\NovaFontAwesome\Services\FontAwesomeApiService;

class FontAwesomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected FontAwesomeApiService $apiService,
        protected IconClassConverter $iconClassConverter
    ) {}

    /**
     * Configure the API service from request parameters.
     */
    protected function configureServiceFromRequest(Request $request): void
    {
        $freeOnly = $request->input('freeOnly', config('nova-fontawesome.free_only', true));

        // Convert string boolean to actual boolean
        if (is_string($freeOnly)) {
            $freeOnly = filter_var($freeOnly, FILTER_VALIDATE_BOOLEAN);
        }

        $this->apiService->configure([
            'version' => $request->input('version', config('nova-fontawesome.version', '6.x')),
            'freeOnly' => $freeOnly,
            'maxResults' => $request->input('first', config('nova-fontawesome.max_results', 100)),
        ]);
    }

    /**
     * Search icons via the Font Awesome GraphQL API.
     *
     * Supports pagination via cursor parameter.
     * Returns: { icons: [], hasMore: bool, cursor: string|null, total: int }
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:100',
            'version' => 'nullable|string',
            'first' => 'nullable|integer|min:1|max:100',
            'family' => 'nullable|string',
            'style' => 'nullable|string',
            'styles' => 'nullable|array',
            'freeOnly' => 'nullable',
            'cursor' => 'nullable|string',
        ]);

        $this->configureServiceFromRequest($request);

        $query = $request->input('query');
        $family = $request->input('family');
        $style = $request->input('style');
        $styles = $request->input('styles', []);
        $cursor = $request->input('cursor');

        $result = $this->apiService->search($query, $family, $style, $cursor);

        // Check for API error
        if (isset($result['error']) && $result['error']) {
            return response()->json([
                'error' => true,
                'message' => $result['message'] ?? 'Font Awesome API is currently unavailable.',
                'icons' => [],
                'hasMore' => false,
                'cursor' => null,
                'total' => 0,
            ]);
        }

        $icons = $result['icons'] ?? [];

        // Get configured families and styles
        $configFamilies = config('nova-fontawesome.families', []);
        $configStyles = config('nova-fontawesome.styles', []);

        // Filter icons by configured families and styles
        if (!empty($configFamilies) || !empty($configStyles)) {
            $icons = array_filter($icons, function ($icon) use ($configFamilies, $configStyles) {
                $selectedStyle = $icon['_selectedStyle'] ?? null;

                if ($selectedStyle) {
                    $familyMatch = empty($configFamilies) || in_array($selectedStyle['family'] ?? 'classic', $configFamilies);
                    $styleMatch = empty($configStyles) || in_array($selectedStyle['style'] ?? 'solid', $configStyles);

                    return $familyMatch && $styleMatch;
                }

                // Fallback: check familyStylesByLicense
                foreach ($icon['familyStylesByLicense'] ?? [] as $license => $licenseStyles) {
                    foreach ($licenseStyles as $styleData) {
                        $familyMatch = empty($configFamilies) || in_array($styleData['family'] ?? 'classic', $configFamilies);
                        $styleMatch = empty($configStyles) || in_array($styleData['style'] ?? 'solid', $configStyles);

                        if ($familyMatch && $styleMatch) {
                            return true;
                        }
                    }
                }

                return false;
            });
            $icons = array_values($icons);
        }

        // Filter by styles if specified in request (additional filtering)
        if (!empty($styles)) {
            $icons = array_filter($icons, function ($icon) use ($styles) {
                foreach ($icon['familyStylesByLicense'] ?? [] as $license => $licenseStyles) {
                    foreach ($licenseStyles as $styleData) {
                        if (in_array($styleData['style'], $styles)) {
                            return true;
                        }
                    }
                }

                return false;
            });
            $icons = array_values($icons);
        }

        return response()->json([
            'icons' => $icons,
            'hasMore' => $result['hasMore'] ?? false,
            'cursor' => $result['cursor'] ?? null,
            'total' => $result['total'] ?? count($icons),
            'fallback' => $result['fallback'] ?? false,
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
                'error' => true,
                'message' => 'Icon not found',
            ], 404);
        }

        return response()->json([
            'icon' => $icon,
        ]);
    }

    /**
     * Get available FontAwesome metadata (families and styles).
     */
    public function metadata(Request $request): JsonResponse
    {
        $this->configureServiceFromRequest($request);

        $metadata = $this->apiService->getMetadata();

        // Filter metadata based on config
        $configFamilies = config('nova-fontawesome.families', []);
        $configStyles = config('nova-fontawesome.styles', []);

        if (!empty($configFamilies) && !empty($metadata['families'])) {
            $metadata['families'] = array_values(array_filter(
                $metadata['families'],
                fn ($family) => in_array($family['id'], $configFamilies)
            ));
        }

        if (!empty($configStyles) && !empty($metadata['styles'])) {
            $metadata['styles'] = array_values(array_filter(
                $metadata['styles'],
                fn ($style) => in_array($style['id'], $configStyles)
            ));
        }

        return response()->json([
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get CSS configuration for frontend.
     */
    public function config(): JsonResponse
    {
        $cssConfig = config('nova-fontawesome.css', []);
        $strategy = $cssConfig['strategy'] ?? 'self-hosted';

        $response = [
            'strategy' => $strategy,
        ];

        switch ($strategy) {
            case 'self-hosted':
                $response['cssPath'] = $cssConfig['path'] ?? '/vendor/fontawesome/css/all.min.css';
                break;

            case 'kit':
                $response['kitId'] = $cssConfig['kit_id'] ?? null;
                break;

            case 'cdn':
                $version = $cssConfig['cdn_version'] ?? '6.5.1';
                $response['cdnUrl'] = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/{$version}/css/all.min.css";
                break;
        }

        return response()->json($response);
    }

    /**
     * Convert legacy icon class format to modern FA6/FA7 format.
     */
    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'class' => 'required|string',
        ]);

        $classString = $request->input('class');
        $converted = $this->iconClassConverter->convert($classString);
        $isLegacy = $this->iconClassConverter->isLegacyFormat($classString);

        return response()->json([
            'original' => $classString,
            'converted' => $converted,
            'isLegacy' => $isLegacy,
            'parsed' => $this->iconClassConverter->parse($classString),
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
            'status' => $diagnostics['status'],
            'diagnostics' => $diagnostics,
            'configuration' => $this->apiService->getConfiguration(),
            'cssConfig' => config('nova-fontawesome.css'),
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
            'icons' => $icons,
            'hasMore' => false,
            'cursor' => null,
            'total' => count($icons),
            'fallback' => true,
        ]);
    }
}
