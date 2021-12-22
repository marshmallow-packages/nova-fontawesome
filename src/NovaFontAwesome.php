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
}
