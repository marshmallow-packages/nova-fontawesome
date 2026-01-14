<?php

namespace Marshmallow\NovaFontAwesome;

use Laravel\Nova\Fields\Field;

class NovaFontAwesome extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-fontawesome';

    public function __construct(...$arguments)
    {
        parent::__construct(...$arguments);

        if (config('nova-fontawesome.pro')) {
            $this->pro();
        }

        // Apply free_only config setting
        $freeOnly = config('nova-fontawesome.free_only', true);
        $this->withMeta(['freeOnly' => $freeOnly]);

        // Load Pro CSS configuration from config
        $this->loadProCssConfig();
    }

    /**
     * Load Pro CSS configuration from config file.
     */
    protected function loadProCssConfig(): void
    {
        $proCss = config('nova-fontawesome.pro_css', []);

        if (! empty($proCss['kit_id'])) {
            $this->kitId($proCss['kit_id']);
        } elseif (! empty($proCss['css_url'])) {
            $this->proCssUrl($proCss['css_url']);
        } elseif (! empty($proCss['local_css'])) {
            $this->proCssUrl(asset($proCss['local_css']));
        }
    }

    public function pro()
    {
        return $this->withMeta([
            'pro' => true,
        ]);
    }

    public function addButtonText($text)
    {
        return $this->withMeta([
            'add_button_text' => $text,
        ]);
    }

    public function defaultIcon($type, $icon)
    {
        return $this->withMeta([
            'default_icon_type' => $type,
            'default_icon' => $icon,
        ]);
    }

    public function persistDefaultIcon()
    {
        return $this->withMeta([
            'enforce_default_icon' => true,
        ]);
    }

    public function only($icons = [])
    {
        return $this->withMeta([
            'only' => $icons,
        ]);
    }

    /**
     * Set the Font Awesome version to use.
     */
    public function version(string $version)
    {
        return $this->withMeta([
            'version' => $version,
        ]);
    }

    /**
     * Set the icon styles to show.
     */
    public function styles(array $styles)
    {
        return $this->withMeta([
            'styles' => $styles,
        ]);
    }

    /**
     * Set the icon families available for selection.
     * Options: classic, brands, duotone, sharp, sharp-duotone
     */
    public function families(array $families)
    {
        return $this->withMeta([
            'families' => $families,
        ]);
    }

    /**
     * Only show free icons.
     */
    public function freeOnly()
    {
        return $this->withMeta([
            'freeOnly' => true,
        ]);
    }

    /**
     * Include Pro icons (requires Font Awesome Pro subscription).
     */
    public function includePro()
    {
        return $this->withMeta([
            'freeOnly' => false,
        ]);
    }

    /**
     * Set maximum number of search results.
     */
    public function maxResults(int $max)
    {
        return $this->withMeta([
            'maxResults' => $max,
        ]);
    }

    /**
     * Set minimum search length before triggering search.
     */
    public function minSearchLength(int $min)
    {
        return $this->withMeta([
            'minSearchLength' => $min,
        ]);
    }

    /**
     * Set the Font Awesome Kit ID for loading Pro CSS.
     * Get your Kit ID from https://fontawesome.com/kits
     */
    public function kitId(string $kitId)
    {
        return $this->withMeta([
            'kitId' => $kitId,
            'proCssUrl' => "https://kit.fontawesome.com/{$kitId}.js",
        ]);
    }

    /**
     * Set a custom Pro CSS URL.
     */
    public function proCssUrl(string $url)
    {
        return $this->withMeta([
            'proCssUrl' => $url,
        ]);
    }

    /**
     * Enable or disable client-side fuzzy search.
     */
    public function fuzzySearch(bool $enabled = true)
    {
        return $this->withMeta([
            'fuzzySearch' => $enabled,
        ]);
    }

    /**
     * Set the fuzzy search threshold (0-1, lower = stricter).
     */
    public function fuzzySearchThreshold(float $threshold)
    {
        return $this->withMeta([
            'fuzzySearchThreshold' => $threshold,
        ]);
    }
}
