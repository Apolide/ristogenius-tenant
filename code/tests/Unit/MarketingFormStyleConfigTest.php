<?php

namespace Tests\Unit;

use Tests\TestCase;

class MarketingFormStyleConfigTest extends TestCase
{
    public function test_default_marketing_form_style_configuration_is_complete(): void
    {
        $style = config('marketing_form_style');

        foreach ([
            'page_background', 'form_background', 'text', 'heading', 'label', 'link',
            'input_background', 'input_text', 'input_border', 'control_accent',
            'button_background', 'button_text', 'header_background', 'header_text',
            'footer_background', 'footer_text',
        ] as $key) {
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $style['colors'][$key]);
        }

        $this->assertNotSame('', $style['layout']['content_max_width']);
        $this->assertNotSame('', $style['layout']['form_radius']);
        $this->assertGreaterThanOrEqual(0, $style['background']['overlay']);
        $this->assertLessThanOrEqual(100, $style['background']['overlay']);
        $this->assertGreaterThan(0, $style['image']['min_width']);
        $this->assertGreaterThan(0, $style['image']['min_height']);
        $this->assertGreaterThan(0, $style['image']['max_size_kb']);
    }
}
