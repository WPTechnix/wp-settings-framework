<?php

declare(strict_types=1);

use WPTechnix\WPSettings\Settings;

// Please make sure you require the composer autoload.
// require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';

add_action('plugins_loaded', 'wptechnix_settings_demo_without_tabs');

/**
 * Initializes the settings page demo without tabs.
 */
function wptechnix_settings_demo_without_tabs(): void
{
    // 1. Create a new Settings instance.
    $settings = new Settings(
        'wptechnix_options_simple',      // Unique option name for the database
        'wptechnix-demo-simple',         // Unique page slug
        [
            'pageTitle' => 'Settings Demo (Simple)',    // Page Title
            'menuTitle' => 'Settings Demo (Simple)'     // Menu Title
        ]
    );

    // 2. Add sections to organize fields.
    $settings
        ->addSection(
            'basic_fields_section',
            'Basic Input Fields',
            'A showcase of standard text, choice, and UI fields.'
        )
        ->addSection(
            'advanced_fields_section',
            'Advanced & Conditional Fields',
            'A showcase of advanced fields and conditional logic.'
        );


    // 3. Add all field types to the sections.
    $settings
        // --- Fields for the "Basic Inputs" Section ---
        ->addField(
            'demo_text',
            'basic_fields_section',
            'text',
            'Text Field',
            ['description' => 'A standard single-line text input.', 'attributes' => ['placeholder' => 'Enter some text...']]
        )
        ->addField(
            'demo_textarea',
            'basic_fields_section',
            'textarea',
            'Textarea Field',
            ['description' => 'A multi-line text input area.', 'attributes' => ['rows' => 4]]
        )
        ->addField(
            'demo_toggle',
            'basic_fields_section',
            'toggle',
            'Toggle Switch',
            ['description' => 'A modern on/off toggle switch.', 'default' => true]
        )
        ->addField(
            'demo_select',
            'basic_fields_section',
            'select',
            'Select Dropdown',
            ['options' => ['option_1' => 'Option One', 'option_2' => 'Option Two', 'option_3' => 'Option Three']]
        )
        ->addField(
            'demo_radio',
            'basic_fields_section',
            'radio',
            'Radio Buttons',
            ['options' => ['yes' => 'Yes', 'no' => 'No', 'maybe' => 'Maybe'], 'default' => 'yes']
        )
        ->addField(
            'demo_color',
            'basic_fields_section',
            'color',
            'Color Picker',
            ['description' => 'A field for selecting a hex color value.', 'default' => '#52ACCC']
        )
        ->addField(
            'demo_date',
            'basic_fields_section',
            'date',
            'Date Picker',
            ['description' => 'A field for selecting a calendar date.']
        )

        // --- Fields for the "Advanced & Conditional" Section ---
        ->addField(
            'demo_media',
            'advanced_fields_section',
            'media',
            'Media Uploader',
            ['description' => 'Upload an image or file using the WordPress Media Library.']
        )
        ->addField(
            'demo_code_html',
            'advanced_fields_section',
            'code',
            'Code Editor (HTML)',
            [
                'description' => 'A code editor with HTML syntax highlighting.',
                'language' => 'html',
            ]
        )
        ->addField(
            'demo_code_css',
            'advanced_fields_section',
            'code',
            'Code Editor (CSS)',
            [
                'description' => 'A code editor with CSS syntax highlighting.',
                'language' => 'css',
            ]
        )
        ->addField(
            'demo_code_js',
            'advanced_fields_section',
            'code',
            'Code Editor (JS)',
            [
                'description' => 'A code editor with JavaScript syntax highlighting.',
                'language' => 'javascript',
            ]
        )
        ->addField( // --- Start Conditional Logic Demo ---
            'demo_enable_advanced', // This is the CONTROLLING field
            'advanced_fields_section',
            'toggle',
            'Enable Advanced Options',
            ['description' => 'Turn this on to reveal hidden advanced fields below.', 'default' => false]
        )
        ->addField(
            'demo_conditional_api_key', // This is the CONDITIONAL field
            'advanced_fields_section',
            'text',
            'Conditional API Key',
            [
                'description' => 'This field is only visible when the toggle above is ON.',
                'conditional' => [
                    'field' => 'demo_enable_advanced', // The ID of the controlling field
                    'value' => '1',                    // The value to check for (1 for 'on')
                    'operator' => '==',                // The comparison operator
                ]
            ]
        )
        ->addField(
            'demo_conditional_mode', // This is another CONDITIONAL field
            'advanced_fields_section',
            'buttongroup',
            'Conditional Mode',
            [
                'description' => 'This button group is also only visible when the toggle is ON.',
                'options' => ['live' => 'Live', 'test' => 'Test'],
                'default' => 'test',
                'conditional' => [
                    'field' => 'demo_enable_advanced',
                    'value' => '1',
                ]
            ]
        ); // --- End Conditional Logic Demo ---


    // 4. Initialize the settings page.
    $settings->init();
}
