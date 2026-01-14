<?php

declare(strict_types=1);

namespace Marshmallow\NovaFontAwesome\Services;

/**
 * Converts legacy Font Awesome class formats to modern FA6/FA7 format.
 *
 * Legacy formats (FA5 and earlier):
 * - fas fa-home → fa-solid fa-home
 * - far fa-user → fa-regular fa-user
 * - fab fa-github → fa-brands fa-github
 * - fal fa-star → fa-light fa-star
 * - fat fa-circle → fa-thin fa-circle
 * - fad fa-house → fa-duotone fa-solid fa-house
 *
 * Modern formats (kept as-is):
 * - fa-solid fa-home (FA6)
 * - fa-classic fa-solid fa-home (FA7 with family)
 */
class IconClassConverter
{
    /**
     * Legacy shorthand to modern class mapping.
     *
     * @var array<string, array{family: string, style: string}>
     */
    protected array $legacyShorthandMap = [
        'fas' => ['family' => 'classic', 'style' => 'solid'],
        'far' => ['family' => 'classic', 'style' => 'regular'],
        'fal' => ['family' => 'classic', 'style' => 'light'],
        'fat' => ['family' => 'classic', 'style' => 'thin'],
        'fad' => ['family' => 'duotone', 'style' => 'solid'],
        'fab' => ['family' => 'brands', 'style' => 'brands'],
        'fass' => ['family' => 'sharp', 'style' => 'solid'],
        'fasr' => ['family' => 'sharp', 'style' => 'regular'],
        'fasl' => ['family' => 'sharp', 'style' => 'light'],
        'fast' => ['family' => 'sharp', 'style' => 'thin'],
        'fasds' => ['family' => 'sharp-duotone', 'style' => 'solid'],
    ];

    /**
     * Style class mapping (already modern format).
     *
     * @var array<string, string>
     */
    protected array $styleClasses = [
        'fa-solid' => 'solid',
        'fa-regular' => 'regular',
        'fa-light' => 'light',
        'fa-thin' => 'thin',
        'fa-brands' => 'brands',
    ];

    /**
     * Family class mapping (modern format).
     *
     * @var array<string, string>
     */
    protected array $familyClasses = [
        'fa-classic' => 'classic',
        'fa-sharp' => 'sharp',
        'fa-duotone' => 'duotone',
        'fa-sharp-duotone' => 'sharp-duotone',
    ];

    public static function make(): self
    {
        return new self;
    }

    /**
     * Convert any Font Awesome class string to modern FA6/FA7 format.
     *
     * @param string|null $classString The icon class string to convert
     *
     * @return string|null The converted class string, or null if empty
     */
    public function convert(?string $classString): ?string
    {
        if (empty($classString)) {
            return null;
        }

        $parsed = $this->parse($classString);

        if ($parsed['icon'] === null) {
            return null;
        }

        return $this->build(
            $parsed['family'],
            $parsed['style'],
            $parsed['icon']
        );
    }

    /**
     * Check if a class string uses legacy format.
     */
    public function isLegacyFormat(?string $classString): bool
    {
        if (empty($classString)) {
            return false;
        }

        $classes = $this->toClassArray($classString);

        foreach ($classes as $class) {
            if (isset($this->legacyShorthandMap[$class])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse a class string into its components.
     *
     * @return array{family: string, style: string, icon: string|null}
     */
    public function parse(?string $classString): array
    {
        if (empty($classString)) {
            return [
                'family' => 'classic',
                'style' => 'solid',
                'icon' => null,
            ];
        }

        $classes = $this->toClassArray($classString);
        $family = 'classic';
        $style = 'solid';
        $icon = null;

        foreach ($classes as $class) {
            // Check for legacy shorthand (fas, far, etc.)
            if (isset($this->legacyShorthandMap[$class])) {
                $family = $this->legacyShorthandMap[$class]['family'];
                $style = $this->legacyShorthandMap[$class]['style'];

                continue;
            }

            // Check for modern family class (fa-classic, fa-sharp, etc.)
            if (isset($this->familyClasses[$class])) {
                $family = $this->familyClasses[$class];

                continue;
            }

            // Check for modern style class (fa-solid, fa-regular, etc.)
            if (isset($this->styleClasses[$class])) {
                $style = $this->styleClasses[$class];

                // fa-brands implies brands family
                if ($class === 'fa-brands') {
                    $family = 'brands';
                }

                continue;
            }

            // Must be the icon name (fa-home, fa-user, etc.)
            if (str_starts_with($class, 'fa-')) {
                $icon = mb_substr($class, 3); // Remove 'fa-' prefix
            }
        }

        return [
            'family' => $family,
            'style' => $style,
            'icon' => $icon,
        ];
    }

    /**
     * Build a modern FA6/FA7 class string from components.
     */
    public function build(string $family, string $style, string $icon): string
    {
        $classes = [];

        // For brands, we only need fa-brands fa-{icon}
        if ($family === 'brands' || $style === 'brands') {
            return "fa-brands fa-{$icon}";
        }

        // For duotone family, we need fa-duotone fa-{style} fa-{icon}
        if ($family === 'duotone') {
            return "fa-duotone fa-{$style} fa-{$icon}";
        }

        // For sharp family, we need fa-sharp fa-{style} fa-{icon}
        if ($family === 'sharp') {
            return "fa-sharp fa-{$style} fa-{$icon}";
        }

        // For sharp-duotone family, we need fa-sharp-duotone fa-{style} fa-{icon}
        if ($family === 'sharp-duotone') {
            return "fa-sharp-duotone fa-{$style} fa-{$icon}";
        }

        // For classic family (default), we just need fa-{style} fa-{icon}
        return "fa-{$style} fa-{$icon}";
    }

    /**
     * Build a class string including the explicit family for FA7 format.
     */
    public function buildWithFamily(string $family, string $style, string $icon): string
    {
        // For brands, we only need fa-brands fa-{icon}
        if ($family === 'brands' || $style === 'brands') {
            return "fa-brands fa-{$icon}";
        }

        // For other families, include the family class
        return "fa-{$family} fa-{$style} fa-{$icon}";
    }

    /**
     * Convert class array to lowercase trimmed array.
     *
     * @return string[]
     */
    protected function toClassArray(string $classString): array
    {
        return array_filter(
            array_map('trim', explode(' ', mb_strtolower($classString)))
        );
    }
}
