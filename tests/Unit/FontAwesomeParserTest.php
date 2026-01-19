<?php

namespace Marshmallow\NovaFontAwesome\Tests\Unit;

use Marshmallow\NovaFontAwesome\Tests\TestCase;
use Marshmallow\NovaFontAwesome\Http\Support\FontAwesomeParser;

class FontAwesomeParserTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $parser = FontAwesomeParser::make();

        $this->assertInstanceOf(FontAwesomeParser::class, $parser);
    }

    /** @test */
    public function it_parses_fas_shorthand(): void
    {
        $parser = FontAwesomeParser::make('fas fa-user');

        $this->assertEquals('classic', $parser->family());
        $this->assertEquals('solid', $parser->style());
        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_parses_far_shorthand(): void
    {
        $parser = FontAwesomeParser::make('far fa-user');

        $this->assertEquals('classic', $parser->family());
        $this->assertEquals('regular', $parser->style());
        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_parses_fab_shorthand(): void
    {
        $parser = FontAwesomeParser::make('fab fa-github');

        $this->assertEquals('brands', $parser->family());
        $this->assertEquals('brands', $parser->style());
        $this->assertEquals('github', $parser->icon());
    }

    /** @test */
    public function it_parses_fal_shorthand(): void
    {
        $parser = FontAwesomeParser::make('fal fa-user');

        $this->assertEquals('classic', $parser->family());
        $this->assertEquals('light', $parser->style());
        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_parses_fat_shorthand(): void
    {
        $parser = FontAwesomeParser::make('fat fa-user');

        $this->assertEquals('classic', $parser->family());
        $this->assertEquals('thin', $parser->style());
        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_parses_fad_shorthand(): void
    {
        $parser = FontAwesomeParser::make('fad fa-user');

        $this->assertEquals('duotone', $parser->family());
        $this->assertEquals('solid', $parser->style());
        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_parses_fa_solid_class(): void
    {
        $parser = FontAwesomeParser::make('fa-solid fa-user');

        $this->assertEquals('classic', $parser->family());
        $this->assertEquals('solid', $parser->style());
        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_parses_fa_regular_class(): void
    {
        $parser = FontAwesomeParser::make('fa-regular fa-heart');

        $this->assertEquals('classic', $parser->family());
        $this->assertEquals('regular', $parser->style());
        $this->assertEquals('heart', $parser->icon());
    }

    /** @test */
    public function it_parses_fa_brands_class(): void
    {
        $parser = FontAwesomeParser::make('fa-brands fa-twitter');

        $this->assertEquals('brands', $parser->family());
        $this->assertEquals('brands', $parser->style());
        $this->assertEquals('twitter', $parser->icon());
    }

    /** @test */
    public function it_returns_full_parse_array(): void
    {
        $parser = FontAwesomeParser::make('fas fa-home');
        $result = $parser->parse();

        $this->assertArrayHasKey('family', $result);
        $this->assertArrayHasKey('style', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertEquals('classic', $result['family']);
        $this->assertEquals('solid', $result['style']);
        $this->assertEquals('home', $result['icon']);
    }

    /** @test */
    public function it_converts_to_graphql_format(): void
    {
        $parser = FontAwesomeParser::make();
        $result = $parser->formatForGraphql('classic', 'solid');

        $this->assertEquals('CLASSIC', $result['family']);
        $this->assertEquals('SOLID', $result['style']);
    }

    /** @test */
    public function it_handles_duotone_graphql_format(): void
    {
        $parser = FontAwesomeParser::make();
        $result = $parser->formatForGraphql('duotone', 'solid');

        $this->assertEquals('DUOTONE', $result['family']);
        $this->assertEquals('SOLID', $result['style']);
    }

    /** @test */
    public function it_handles_sharp_graphql_format(): void
    {
        $parser = FontAwesomeParser::make();
        $result = $parser->formatForGraphql('sharp', 'solid');

        $this->assertEquals('SHARP', $result['family']);
        $this->assertEquals('SOLID', $result['style']);
    }

    /** @test */
    public function it_handles_empty_class_string(): void
    {
        $parser = FontAwesomeParser::make('');

        $this->assertNull($parser->icon());
    }

    /** @test */
    public function it_handles_icon_only_class(): void
    {
        $parser = FontAwesomeParser::make('fa-user');

        $this->assertEquals('user', $parser->icon());
    }

    /** @test */
    public function it_defaults_to_solid_style_when_not_specified(): void
    {
        $parser = FontAwesomeParser::make();
        $result = $parser->formatForGraphql('classic', null);

        $this->assertEquals('SOLID', $result['style']);
    }

    /** @test */
    public function it_defaults_to_classic_family_when_not_specified(): void
    {
        $parser = FontAwesomeParser::make();
        $result = $parser->formatForGraphql(null, 'solid');

        $this->assertEquals('CLASSIC', $result['family']);
    }
}
