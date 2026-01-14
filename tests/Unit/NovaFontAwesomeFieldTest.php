<?php

namespace Marshmallow\NovaFontAwesome\Tests\Unit;

use Marshmallow\NovaFontAwesome\Tests\TestCase;
use Marshmallow\NovaFontAwesome\NovaFontAwesome;

class NovaFontAwesomeFieldTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $field = NovaFontAwesome::make('Icon');

        $this->assertInstanceOf(NovaFontAwesome::class, $field);
    }

    /** @test */
    public function it_has_correct_component_name(): void
    {
        $field = NovaFontAwesome::make('Icon');

        $this->assertEquals('nova-fontawesome', $field->component);
    }

    /** @test */
    public function it_can_set_pro_mode(): void
    {
        $field = NovaFontAwesome::make('Icon')->pro();

        $this->assertTrue($field->meta['pro']);
    }

    /** @test */
    public function it_can_set_add_button_text(): void
    {
        $field = NovaFontAwesome::make('Icon')->addButtonText('Select Icon');

        $this->assertEquals('Select Icon', $field->meta['add_button_text']);
    }

    /** @test */
    public function it_can_set_default_icon(): void
    {
        $field = NovaFontAwesome::make('Icon')->defaultIcon('solid', 'user');

        $this->assertEquals('solid', $field->meta['default_icon_type']);
        $this->assertEquals('user', $field->meta['default_icon']);
    }

    /** @test */
    public function it_can_set_persist_default_icon(): void
    {
        $field = NovaFontAwesome::make('Icon')->persistDefaultIcon();

        $this->assertTrue($field->meta['enforce_default_icon']);
    }

    /** @test */
    public function it_can_restrict_to_specific_icons(): void
    {
        $icons = ['user', 'home', 'star'];
        $field = NovaFontAwesome::make('Icon')->only($icons);

        $this->assertEquals($icons, $field->meta['only']);
    }

    /** @test */
    public function it_can_set_version(): void
    {
        $field = NovaFontAwesome::make('Icon')->version('6.5.0');

        $this->assertEquals('6.5.0', $field->meta['version']);
    }

    /** @test */
    public function it_can_set_styles(): void
    {
        $styles = ['solid', 'regular'];
        $field = NovaFontAwesome::make('Icon')->styles($styles);

        $this->assertEquals($styles, $field->meta['styles']);
    }

    /** @test */
    public function it_can_set_free_only(): void
    {
        $field = NovaFontAwesome::make('Icon')->freeOnly();

        $this->assertTrue($field->meta['freeOnly']);
    }

    /** @test */
    public function it_can_include_pro(): void
    {
        $field = NovaFontAwesome::make('Icon')->includePro();

        $this->assertFalse($field->meta['freeOnly']);
    }

    /** @test */
    public function it_can_set_max_results(): void
    {
        $field = NovaFontAwesome::make('Icon')->maxResults(100);

        $this->assertEquals(100, $field->meta['maxResults']);
    }

    /** @test */
    public function it_can_set_min_search_length(): void
    {
        $field = NovaFontAwesome::make('Icon')->minSearchLength(3);

        $this->assertEquals(3, $field->meta['minSearchLength']);
    }

    /** @test */
    public function it_can_chain_multiple_methods(): void
    {
        $field = NovaFontAwesome::make('Icon')
            ->pro()
            ->version('6.5.0')
            ->styles(['solid', 'regular'])
            ->maxResults(50);

        $this->assertTrue($field->meta['pro']);
        $this->assertEquals('6.5.0', $field->meta['version']);
        $this->assertEquals(['solid', 'regular'], $field->meta['styles']);
        $this->assertEquals(50, $field->meta['maxResults']);
    }

    /** @test */
    public function it_uses_attribute_name_correctly(): void
    {
        $field = NovaFontAwesome::make('Icon', 'icon_class');

        $this->assertEquals('icon_class', $field->attribute);
    }

    /** @test */
    public function it_can_be_nullable(): void
    {
        $field = NovaFontAwesome::make('Icon')->nullable();

        $this->assertTrue($field->nullable);
    }
}
