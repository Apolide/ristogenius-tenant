<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default public marketing form theme
    |--------------------------------------------------------------------------
    |
    | These values are the tenant-wide defaults. Per-form settings saved from
    | the management UI may override them, but an empty form style will always
    | fall back to this configuration.
    |
    */
    'colors' => [
        'page_background' => env('TENANT_FORM_STYLE_PAGE_BACKGROUND', '#f3f4f6'),
        'form_background' => env('TENANT_FORM_STYLE_FORM_BACKGROUND', '#ffffff'),
        'text' => env('TENANT_FORM_STYLE_TEXT_COLOR', '#374151'),
        'heading' => env('TENANT_FORM_STYLE_HEADING_COLOR', '#111827'),
        'label' => env('TENANT_FORM_STYLE_LABEL_COLOR', '#374151'),
        'link' => env('TENANT_FORM_STYLE_LINK_COLOR', '#2563eb'),
        'input_background' => env('TENANT_FORM_STYLE_INPUT_BACKGROUND', '#ffffff'),
        'input_text' => env('TENANT_FORM_STYLE_INPUT_TEXT_COLOR', '#111827'),
        'input_border' => env('TENANT_FORM_STYLE_INPUT_BORDER_COLOR', '#d1d5db'),
        'control_accent' => env('TENANT_FORM_STYLE_CONTROL_ACCENT_COLOR', '#2563eb'),
        'button_background' => env('TENANT_FORM_STYLE_BUTTON_BACKGROUND', '#2563eb'),
        'button_text' => env('TENANT_FORM_STYLE_BUTTON_TEXT_COLOR', '#ffffff'),
        'header_background' => env('TENANT_FORM_STYLE_HEADER_BACKGROUND', '#ffffff'),
        'header_text' => env('TENANT_FORM_STYLE_HEADER_TEXT_COLOR', '#111827'),
        'footer_background' => env('TENANT_FORM_STYLE_FOOTER_BACKGROUND', '#ffffff'),
        'footer_text' => env('TENANT_FORM_STYLE_FOOTER_TEXT_COLOR', '#6b7280'),
    ],

    'layout' => [
        'content_max_width' => env('TENANT_FORM_STYLE_CONTENT_MAX_WIDTH', '56rem'),
        'form_radius' => env('TENANT_FORM_STYLE_FORM_RADIUS', '0.75rem'),
        'header_height' => env('TENANT_FORM_STYLE_HEADER_HEIGHT', '4rem'),
        'footer_height' => env('TENANT_FORM_STYLE_FOOTER_HEIGHT', '3.5rem'),
    ],

    'background' => [
        'overlay' => min(100, max(0, (int) env('TENANT_FORM_STYLE_BACKGROUND_OVERLAY', 35))),
        'position' => env('TENANT_FORM_STYLE_BACKGROUND_POSITION', 'center center'),
    ],

    'image' => [
        'min_width' => max(1, (int) env('TENANT_FORM_STYLE_IMAGE_MIN_WIDTH', 1600)),
        'min_height' => max(1, (int) env('TENANT_FORM_STYLE_IMAGE_MIN_HEIGHT', 900)),
        'max_size_kb' => max(1, (int) env('TENANT_FORM_STYLE_IMAGE_MAX_SIZE_KB', 5120)),
        'recommended_width' => max(1, (int) env('TENANT_FORM_STYLE_IMAGE_RECOMMENDED_WIDTH', 1920)),
        'recommended_height' => max(1, (int) env('TENANT_FORM_STYLE_IMAGE_RECOMMENDED_HEIGHT', 1080)),
    ],
];
