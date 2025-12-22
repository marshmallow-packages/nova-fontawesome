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

        foreach ($classes as $class) {
            // Check shorthand prefixes first
            if (isset($this->shorthandMap[$class])) {
                $family = $this->shorthandMap[$class]['family'];
                continue;
            }

            // Check explicit family modifiers
            if (isset($this->familyMap[$class])) {
                $family = $this->familyMap[$class];
                continue;
            }

            // Brands style class implies brands family
            if ($class === 'fa-brands') {
                $family = 'brands';
            }

            // Classic style classes (without explicit family modifier) imply classic family
            // These are: fa-solid, fa-regular, fa-light, fa-thin
            if (isset($this->styleMap[$class]) && $class !== 'fa-brands') {
                // If we see a style class and no family modifier was found, it's classic
                $family = 'classic';
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

    public function inferFamilyFromStyle(?string $classString = null): string
    {
        $classes = $this->toClassArray($classString ?? $this->classString);

        // Check shorthand prefixes that imply family
        $familyImplyingShorthands = [
            'fas' => 'classic',
            'far' => 'classic',
            'fal' => 'classic',
            'fat' => 'classic',
            'fad' => 'duotone',
            'fab' => 'brands',
            'fass' => 'sharp',
            'fasr' => 'sharp',
            'fasl' => 'sharp',
            'fast' => 'sharp',
            'fasds' => 'sharp-duotone',
        ];

        foreach ($classes as $class) {
            if (isset($familyImplyingShorthands[$class])) {
                return $familyImplyingShorthands[$class];
            }

            // Check family modifiers
            if ($class === 'fa-sharp') {
                return 'sharp';
            }

            if ($class === 'fa-sharp-duotone') {
                return 'sharp-duotone';
            }

            if ($class === 'fa-duotone') {
                return 'duotone';
            }

            if ($class === 'fa-brands') {
                return 'brands';
            }

            // Classic styles (when no family modifier is present)
            if (in_array($class, ['fa-solid', 'fa-regular', 'fa-light', 'fa-thin'])) {
                return 'classic';
            }
        }

        // Default to classic
        return 'classic';
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

    public function formatForGraphql(string $style, ?string $family = null): array
    {
        if (!$family) {
            $family = $this->inferFamilyFromStyle($style);
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
