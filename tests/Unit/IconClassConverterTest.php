<?php

declare(strict_types=1);

namespace Marshmallow\NovaFontAwesome\Tests\Unit;

use Marshmallow\NovaFontAwesome\Tests\TestCase;
use Marshmallow\NovaFontAwesome\Services\IconClassConverter;

class IconClassConverterTest extends TestCase
{
    protected IconClassConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = IconClassConverter::make();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(IconClassConverter::class, $this->converter);
    }

    /** @test */
    public function it_converts_fas_shorthand_to_modern_format(): void
    {
        $result = $this->converter->convert('fas fa-home');

        $this->assertEquals('fa-solid fa-home', $result);
    }

    /** @test */
    public function it_converts_far_shorthand_to_modern_format(): void
    {
        $result = $this->converter->convert('far fa-user');

        $this->assertEquals('fa-regular fa-user', $result);
    }

    /** @test */
    public function it_converts_fab_shorthand_to_modern_format(): void
    {
        $result = $this->converter->convert('fab fa-github');

        $this->assertEquals('fa-brands fa-github', $result);
    }

    /** @test */
    public function it_converts_fal_shorthand_to_modern_format(): void
    {
        $result = $this->converter->convert('fal fa-star');

        $this->assertEquals('fa-light fa-star', $result);
    }

    /** @test */
    public function it_converts_fat_shorthand_to_modern_format(): void
    {
        $result = $this->converter->convert('fat fa-circle');

        $this->assertEquals('fa-thin fa-circle', $result);
    }

    /** @test */
    public function it_converts_fad_shorthand_to_modern_format(): void
    {
        $result = $this->converter->convert('fad fa-house');

        $this->assertEquals('fa-duotone fa-solid fa-house', $result);
    }

    /** @test */
    public function it_keeps_modern_fa_solid_format_unchanged(): void
    {
        $result = $this->converter->convert('fa-solid fa-home');

        $this->assertEquals('fa-solid fa-home', $result);
    }

    /** @test */
    public function it_keeps_modern_fa_regular_format_unchanged(): void
    {
        $result = $this->converter->convert('fa-regular fa-user');

        $this->assertEquals('fa-regular fa-user', $result);
    }

    /** @test */
    public function it_keeps_fa_brands_format_unchanged(): void
    {
        $result = $this->converter->convert('fa-brands fa-github');

        $this->assertEquals('fa-brands fa-github', $result);
    }

    /** @test */
    public function it_handles_fa7_format_with_family(): void
    {
        // FA7 format includes explicit family class
        $result = $this->converter->convert('fa-classic fa-solid fa-home');

        // Should normalize to standard format
        $this->assertEquals('fa-solid fa-home', $result);
    }

    /** @test */
    public function it_handles_sharp_family(): void
    {
        $result = $this->converter->convert('fa-sharp fa-solid fa-home');

        $this->assertEquals('fa-sharp fa-solid fa-home', $result);
    }

    /** @test */
    public function it_handles_sharp_duotone_family(): void
    {
        $result = $this->converter->convert('fa-sharp-duotone fa-solid fa-home');

        $this->assertEquals('fa-sharp-duotone fa-solid fa-home', $result);
    }

    /** @test */
    public function it_detects_legacy_format(): void
    {
        $this->assertTrue($this->converter->isLegacyFormat('fas fa-home'));
        $this->assertTrue($this->converter->isLegacyFormat('far fa-user'));
        $this->assertTrue($this->converter->isLegacyFormat('fab fa-github'));
        $this->assertTrue($this->converter->isLegacyFormat('fal fa-star'));
        $this->assertTrue($this->converter->isLegacyFormat('fat fa-circle'));
        $this->assertTrue($this->converter->isLegacyFormat('fad fa-house'));
    }

    /** @test */
    public function it_detects_modern_format_as_not_legacy(): void
    {
        $this->assertFalse($this->converter->isLegacyFormat('fa-solid fa-home'));
        $this->assertFalse($this->converter->isLegacyFormat('fa-regular fa-user'));
        $this->assertFalse($this->converter->isLegacyFormat('fa-brands fa-github'));
        $this->assertFalse($this->converter->isLegacyFormat('fa-light fa-star'));
    }

    /** @test */
    public function it_parses_legacy_class_string(): void
    {
        $result = $this->converter->parse('fas fa-home');

        $this->assertEquals('classic', $result['family']);
        $this->assertEquals('solid', $result['style']);
        $this->assertEquals('home', $result['icon']);
    }

    /** @test */
    public function it_parses_modern_class_string(): void
    {
        $result = $this->converter->parse('fa-solid fa-user');

        $this->assertEquals('classic', $result['family']);
        $this->assertEquals('solid', $result['style']);
        $this->assertEquals('user', $result['icon']);
    }

    /** @test */
    public function it_parses_duotone_shorthand(): void
    {
        $result = $this->converter->parse('fad fa-house');

        $this->assertEquals('duotone', $result['family']);
        $this->assertEquals('solid', $result['style']);
        $this->assertEquals('house', $result['icon']);
    }

    /** @test */
    public function it_parses_sharp_shorthand(): void
    {
        $result = $this->converter->parse('fass fa-home');

        $this->assertEquals('sharp', $result['family']);
        $this->assertEquals('solid', $result['style']);
        $this->assertEquals('home', $result['icon']);
    }

    /** @test */
    public function it_builds_classic_solid_class(): void
    {
        $result = $this->converter->build('classic', 'solid', 'home');

        $this->assertEquals('fa-solid fa-home', $result);
    }

    /** @test */
    public function it_builds_classic_regular_class(): void
    {
        $result = $this->converter->build('classic', 'regular', 'user');

        $this->assertEquals('fa-regular fa-user', $result);
    }

    /** @test */
    public function it_builds_brands_class(): void
    {
        $result = $this->converter->build('brands', 'brands', 'github');

        $this->assertEquals('fa-brands fa-github', $result);
    }

    /** @test */
    public function it_builds_duotone_class(): void
    {
        $result = $this->converter->build('duotone', 'solid', 'house');

        $this->assertEquals('fa-duotone fa-solid fa-house', $result);
    }

    /** @test */
    public function it_builds_sharp_class(): void
    {
        $result = $this->converter->build('sharp', 'solid', 'home');

        $this->assertEquals('fa-sharp fa-solid fa-home', $result);
    }

    /** @test */
    public function it_builds_sharp_duotone_class(): void
    {
        $result = $this->converter->build('sharp-duotone', 'solid', 'home');

        $this->assertEquals('fa-sharp-duotone fa-solid fa-home', $result);
    }

    /** @test */
    public function it_handles_empty_class_string(): void
    {
        $result = $this->converter->convert('');

        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_null_class_string(): void
    {
        $result = $this->converter->convert(null);

        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_case_insensitive_input(): void
    {
        $result = $this->converter->convert('FAS FA-HOME');

        $this->assertEquals('fa-solid fa-home', $result);
    }

    /** @test */
    public function it_handles_extra_whitespace(): void
    {
        $result = $this->converter->convert('  fas   fa-home  ');

        $this->assertEquals('fa-solid fa-home', $result);
    }

    /** @test */
    public function it_builds_class_with_explicit_family(): void
    {
        $result = $this->converter->buildWithFamily('classic', 'solid', 'home');

        $this->assertEquals('fa-classic fa-solid fa-home', $result);
    }

    /** @test */
    public function it_builds_brands_with_explicit_family_same_as_regular(): void
    {
        // Brands should always be just fa-brands fa-{icon}
        $result = $this->converter->buildWithFamily('brands', 'brands', 'github');

        $this->assertEquals('fa-brands fa-github', $result);
    }
}
