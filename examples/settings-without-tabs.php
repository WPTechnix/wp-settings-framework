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
        'wptechnix_options_no_tabs',    // Unique option name for the database
        'wptechnix-demo-no-tabs',       // Unique page slug
        [
            'pageTitle' => 'Settings Demo (No Tabs)',    // Page Title
            'menuTitle' => 'Settings Demo (No Tabs)',    // Menu Title
            'parentSlug' => 'tools.php'                 // Place this page under the "Tools" menu.
        ]
    );

    // 2. Add sections directly to the page (no tabs are needed).
    $settings->addSection('text_inputs', 'Text-Based Inputs', 'Fields for text, numbers, and passwords.')
             ->addSection(
                 'choice_inputs',
                 'Choice-Based Inputs',
                 'Fields for selecting one or more options.'
             )
             ->addSection('ui_inputs', 'Enhanced UI Inputs', 'Fields with special user interfaces.')
             ->addSection(
                 'advanced_inputs',
                 'Advanced & Special Inputs',
                 'Media, code, and other powerful fields.'
             )
             ->addSection(
                 'conditional_section',
                 'Conditional Logic Demo',
                 'Show and hide fields based on other fields\' values.'
             );

    // 3. Add fields and assign them to the correct sections.

    // --- FIELDS FOR "Text-Based Inputs" SECTION ---
    $settings
        ->addField('demo_text', 'text_inputs', 'text', 'Text Field')
        ->addField('demo_email', 'text_inputs', 'email', 'Email Field')
        ->addField('demo_password', 'text_inputs', 'password', 'Password Field')
        ->addField('demo_number', 'text_inputs', 'number', 'Number Field', ['default' => 42])
        ->addField('demo_url', 'text_inputs', 'url', 'URL Field')
        ->addField('demo_textarea', 'text_inputs', 'textarea', 'Textarea Field');

    // --- FIELDS FOR "Choice-Based Inputs" SECTION ---
    $settings
        ->addField('demo_checkbox', 'choice_inputs', 'checkbox', 'Checkbox Field')
        ->addField('demo_toggle', 'choice_inputs', 'toggle', 'Toggle Switch', ['default' => true])
        ->addField(
            'demo_select',
            'choice_inputs',
            'select',
            'Select Dropdown',
            ['options' => ['a' => 'Option A', 'b' => 'Option B'], 'placeholder' => 'Select Option']
        )
        ->addField(
            'demo_multiselect',
            'choice_inputs',
            'multiselect',
            'Multi-Select',
            ['options' => ['a' => 'Choice A', 'b' => 'Choice B', 'c' => 'Choice C'], 'placeholder' => 'Select Options']
        )
        ->addField(
            'demo_radio',
            'choice_inputs',
            'radio',
            'Radio Buttons',
            ['options' => ['yes' => 'Yes', 'no' => 'No']]
        )
        ->addField(
            'demo_buttongroup',
            'choice_inputs',
            'buttongroup',
            'Button Group',
            ['options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right']]
        );

    // --- FIELDS FOR "Enhanced UI Inputs" SECTION ---
    $settings
        ->addField('demo_color', 'ui_inputs', 'color', 'Color Picker')
        ->addField('demo_date', 'ui_inputs', 'date', 'Date Picker')
        ->addField('demo_datetime', 'ui_inputs', 'datetime', 'Date & Time Picker')
        ->addField('demo_time', 'ui_inputs', 'time', 'Time Picker')
        ->addField('demo_range', 'ui_inputs', 'range', 'Range Slider', ['default' => 75]);

    // --- FIELDS FOR "Advanced & Special Inputs" SECTION ---
    $settings
        ->addField('demo_media', 'advanced_inputs', 'media', 'Media Uploader')
        ->addField(
            'demo_code_html',
            'advanced_inputs',
            'code',
            'Code Editor (HTML)',
            [
                'description' => 'A code editor with HTML syntax highlighting.',
                'language'    => 'html',
            ]
        )
        ->addField(
            'demo_code_css',
            'advanced_inputs',
            'code',
            'Code Editor (CSS)',
            [
                'description' => 'A code editor with CSS syntax highlighting.',
                'language'    => 'css',
            ]
        )
        ->addField(
            'demo_code_js',
            'advanced_inputs',
            'code',
            'Code Editor (JS)',
            [
                'description' => 'A code editor with JavaScript syntax highlighting.',
                'language'    => 'javascript',
            ]
        )
        ->addField(
            'demo_description',
            'advanced_inputs',
            'description',
            'Description Field',
            ['description' => 'This is a read-only field used to display important information. It supports <strong>HTML</strong>.']
        );

    // --- FIELDS FOR "Conditional Logic Demo" SECTION ---
    $settings
        ->addField(
            'demo_contact_method', // The CONTROLLING field
            'conditional_section',
            'radio',
            'Preferred Contact Method',
            [
                'description' => 'Select a method to see different conditional fields appear.',
                'options'     => ['email' => 'Email', 'phone' => 'Phone Call', 'none' => 'No Contact'],
                'default'     => 'none',
            ]
        )
        ->addField(
            'demo_conditional_email', // A CONDITIONAL field
            'conditional_section',
            'email',
            'Contact Email Address',
            [
                'description' => 'This only appears if "Email" is selected.',
                'conditional' => [
                    'field' => 'demo_contact_method',
                    'value' => 'email',
                ],
            ]
        )
        ->addField(
            'demo_conditional_phone', // Another CONDITIONAL field
            'conditional_section',
            'text',
            'Contact Phone Number',
            [
                'description' => 'This only appears if "Phone Call" is selected.',
                'conditional' => [
                    'field' => 'demo_contact_method',
                    'value' => 'phone',
                ],
            ]
        );

    // 4. Initialize the settings page.
    $settings->init();
}
