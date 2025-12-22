<?php

namespace Marshmallow\NovaFontAwesome\Http\Support;

class FontAwesomeParser
{
    protected array $shorthandMap = [
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

    protected array $styleMap = [
        'fa-solid' => 'solid',
        'fa-regular' => 'regular',
        'fa-light' => 'light',
        'fa-thin' => 'thin',
        'fa-brands' => 'brands',
    ];

    protected array $familyMap = [
        'fa-sharp' => 'sharp',
        'fa-duotone' => 'duotone',
        'fa-sharp-duotone' => 'sharp-duotone',
    ];

    public function __construct(
        protected ?string $classString = null
    ) {}

    public static function make(?string $classString = null): static
    {
        return new static($classString);
    }

    public function parse(?string $classString = null): array
    {
        $classString ??= $this->classString;

        return [
            'family' => $this->family($classString),
            'style' => $this->style($classString),
            'icon' => $this->icon($classString),
        ];
    }

    public function family(?string $classString = null): string
    {
        $classes = $this->toClassArray($classString ?? $this->classString);
        $family = 'classic';
        $foundExplicitFamily = false;

        foreach ($classes as $class) {
            // Check shorthand prefixes first
            if (isset($this->shorthandMap[$class])) {
                $family = $this->shorthandMap[$class]['family'];
                $foundExplicitFamily = true;
                continue;
            }

            // Check explicit family modifiers
            if (isset($this->familyMap[$class])) {
                $family = $this->familyMap[$class];
                $foundExplicitFamily = true;
                continue;
            }

            // Brands style class implies brands family
            if ($class === 'fa-brands') {
                $family = 'brands';
                $foundExplicitFamily = true;
            }
        }

        return $family;
    }

    public function style(?string $classString = null): string
    {
        $classes = $this->toClassArray($classString ?? $this->classString);
        $style = 'solid';

        foreach ($classes as $class) {
            if (isset($this->shorthandMap[$class])) {
                $style = $this->shorthandMap[$class]['style'];
                continue;
            }

            if (isset($this->styleMap[$class])) {
                $style = $this->styleMap[$class];
            }
        }

        return $style;
    }

    public function icon(?string $classString = null): ?string
    {
        $classes = $this->toClassArray($classString ?? $this->classString);

        foreach ($classes as $class) {
            if (
                str_starts_with($class, 'fa-') &&
                !isset($this->styleMap[$class]) &&
                !isset($this->familyMap[$class])
            ) {
                return str_replace('fa-', '', $class);
            }
        }

        return null;
    }

    /**
     * Infer the family from a style class or shorthand.
     * Useful when only style is provided and you need to determine the family.
     */
    public function inferFamilyFromStyle(?string $classString = null): string
    {
        $classes = $this->toClassArray($classString ?? $this->classString);

        foreach ($classes as $class) {
            // Check shorthand prefixes that imply family
            if (isset($this->shorthandMap[$class])) {
                return $this->shorthandMap[$class]['family'];
            }

            // Check explicit family modifiers
            if (isset($this->familyMap[$class])) {
                return $this->familyMap[$class];
            }

            // Brands style implies brands family
            if ($class === 'fa-brands') {
                return 'brands';
            }
        }

        // Default to classic for standalone style classes like fa-light, fa-regular, etc.
        return 'classic';
    }

    /**
     * Infer the default style from a family.
     * - Brands family only has 'brands' style
     * - All other families default to 'solid'
     */
    public function inferStyleFromFamily(?string $family = null): string
    {
        $family ??= $this->family();
        $family = strtolower($family);

        // Brands family only has one style
        if ($family === 'brands') {
            return 'brands';
        }

        // All other families default to solid
        return 'solid';
    }

    public function toGraphqlFamily(?string $family = null): string
    {
        $family ??= $this->family();

        return strtoupper(str_replace('-', '_', $family));
    }

    public function toGraphqlStyle(?string $style = null): string
    {
        $style ??= $this->style();

        return strtoupper($style);
    }

    public function toGraphqlParams(?string $classString = null): array
    {
        $classString ??= $this->classString;

        return [
            'family' => $this->toGraphqlFamily($this->family($classString)),
            'style' => $this->toGraphqlStyle($this->style($classString)),
            'icon' => $this->icon($classString),
        ];
    }

    /**
     * Format family and style for GraphQL API.
     * If family is not provided, it will be inferred from the style.
     * If style is not provided, it will be inferred from the family.
     */
    public function formatForGraphql(?string $family = null, ?string $style = null): array
    {
        // If neither provided, use defaults
        if (!$family && !$style) {
            $family = 'classic';
            $style = 'solid';
        }
        // If only style provided, infer family
        elseif (!$family && $style) {
            $family = $this->inferFamilyFromStyle($style);
        }
        // If only family provided, infer style
        elseif ($family && !$style) {
            $style = $this->inferStyleFromFamily($family);
        }

        return [
            'family' => $this->toGraphqlFamily($family),
            'style' => $this->toGraphqlStyle($style),
        ];
    }

    protected function toClassArray(?string $classString): array
    {
        if (empty($classString)) {
            return [];
        }

        return array_map('trim', explode(' ', strtolower($classString)));
    }
}
