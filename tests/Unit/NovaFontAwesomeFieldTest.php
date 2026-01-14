<?php

namespace Marshmallow\NovaFontAwesome\Tests\Unit;

use Marshmallow\NovaFontAwesome\NovaFontAwesome;
use Marshmallow\NovaFontAwesome\Tests\TestCase;

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

    /** @test */
    public function it_can_set_families(): void
    {
        $families = ['classic', 'brands', 'duotone'];
        $field = NovaFontAwesome::make('Icon')->families($families);

        $this->assertEquals($families, $field->meta['families']);
    }

    /** @test */
    public function it_can_set_kit_id(): void
    {
        $field = NovaFontAwesome::make('Icon')->kitId('abc123def');

        $this->assertEquals('abc123def', $field->meta['kitId']);
        $this->assertEquals('https://kit.fontawesome.com/abc123def.js', $field->meta['proCssUrl']);
    }

    /** @test */
    public function it_can_set_pro_css_url(): void
    {
        $url = 'https://example.com/fontawesome.css';
        $field = NovaFontAwesome::make('Icon')->proCssUrl($url);

        $this->assertEquals($url, $field->meta['proCssUrl']);
    }

    /** @test */
    public function it_can_enable_fuzzy_search(): void
    {
        $field = NovaFontAwesome::make('Icon')->fuzzySearch(true);

        $this->assertTrue($field->meta['fuzzySearch']);
    }

    /** @test */
    public function it_can_disable_fuzzy_search(): void
    {
        $field = NovaFontAwesome::make('Icon')->fuzzySearch(false);

        $this->assertFalse($field->meta['fuzzySearch']);
    }

    /** @test */
    public function it_can_set_fuzzy_search_threshold(): void
    {
        $field = NovaFontAwesome::make('Icon')->fuzzySearchThreshold(0.5);

        $this->assertEquals(0.5, $field->meta['fuzzySearchThreshold']);
    }

    /** @test */
    public function it_can_chain_new_methods(): void
    {
        $field = NovaFontAwesome::make('Icon')
            ->kitId('abc123')
            ->families(['classic', 'sharp'])
            ->fuzzySearch(true)
            ->fuzzySearchThreshold(0.4);

        $this->assertEquals('abc123', $field->meta['kitId']);
        $this->assertEquals(['classic', 'sharp'], $field->meta['families']);
        $this->assertTrue($field->meta['fuzzySearch']);
        $this->assertEquals(0.4, $field->meta['fuzzySearchThreshold']);
    }
}
