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
    }

    public function pro()
    {
        return $this->withMeta([
            'pro' => true
        ]);
    }

    public function addButtonText($text)
    {
        return $this->withMeta([
            'add_button_text' => $text
        ]);
    }

    public function defaultIcon($type, $icon)
    {
        return $this->withMeta([
            'default_icon_type' => $type,
            'default_icon' => $icon
        ]);
    }

    public function persistDefaultIcon()
    {
        return $this->withMeta([
            'enforce_default_icon' => true
        ]);
    }

    public function only($icons = [])
    {
        return $this->withMeta([
            'only' => $icons
        ]);
    }

    /**
     * Set the Font Awesome version to use.
     */
    public function version(string $version)
    {
        return $this->withMeta([
            'version' => $version
        ]);
    }

    /**
     * Set the icon styles to show.
     */
    public function styles(array $styles)
    {
        return $this->withMeta([
            'styles' => $styles
        ]);
    }

    /**
     * Only show free icons.
     */
    public function freeOnly()
    {
        return $this->withMeta([
            'freeOnly' => true
        ]);
    }

    /**
     * Include Pro icons (requires Font Awesome Pro subscription).
     */
    public function includePro()
    {
        return $this->withMeta([
            'freeOnly' => false
        ]);
    }

    /**
     * Set maximum number of search results.
     */
    public function maxResults(int $max)
    {
        return $this->withMeta([
            'maxResults' => $max
        ]);
    }

    /**
     * Set minimum search length before triggering search.
     */
    public function minSearchLength(int $min)
    {
        return $this->withMeta([
            'minSearchLength' => $min
        ]);
    }
}
