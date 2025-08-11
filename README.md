# WPTechnix Settings Framework

A modern, object-oriented PHP framework for creating powerful and professional settings pages in WordPress. Designed with a clean, fluent API, this framework saves you time by abstracting away the complexities of the WordPress Settings API.

Build complex, tabbed settings pages with over 20 field types, conditional logic, and an enhanced user interface, all while storing your data efficiently in a single database option.

---

## Features

*   **Fluent, Chainable API:** Build your entire settings page in a clean, readable, and intuitive way.
*   **Efficient Database Storage:** All settings are saved to a single array in the `wp_options` table, reducing database clutter and improving performance.
*   **Rich & Modern Field Types:** Includes over 20 field types, with enhanced UI for color pickers, media uploaders, date/time selectors, and more.
*   **Tabbed Interface:** Easily organize your settings into clean, separate tabs with support for Dashicons.
*   **Conditional Field Logic:** Show or hide fields based on the value of another field (e.g., show "Log File Path" only when "Enable Debugging" is toggled on).
*   **Code Editor Fields:** Includes a `code` field with syntax highlighting for CSS, JavaScript, and HTML, powered by the built-in WordPress CodeMirror library.
*   **Conflict-Free Prefixing:** All custom HTML classes for CSS and JS are prefixed to prevent conflicts with other plugins and themes. This prefix is fully configurable.
*   **Composer Ready:** Fully PSR-4 compliant and ready to be included as a dependency in any modern WordPress project.

## Available Field Types

The framework includes the following field types out of the box:

| Type | Description |
| :--- | :--- |
| `text` | A standard single-line text input. |
| `email` | A text input with `type="email"` validation. |
| `password` | A text input with `type="password"`. |
| `number` | A number input with `type="number"`. |
| `url` | A text input with `type="url"` validation. |
| `textarea` | A standard multi-line text area. |
| `checkbox` | A single checkbox. |
| `toggle` | An on/off toggle switch (saves a boolean). |
| `select` | A dropdown select menu. |
| `multiselect` | A multi-select dropdown menu. |
| `radio` | A group of radio buttons. |
| `buttongroup` | A modern button group that functions like a radio input. |
| `color` | A color picker field. |
| `date` | A date picker. |
| `datetime` | A date and time picker. |
| `time` | A time picker. |
| `range` | An enhanced range slider with a value display. |
| `media` | A media uploader that uses the WordPress Media Library. |
| `code` | A code editor with syntax highlighting. |
| `description` | A read-only field used to display text, lists, or other HTML. |

## Installation

This package is intended to be used as a Composer dependency.

Install the package via the command line:
```bash
composer require wptechnix/wp-settings-framework
```
Make sure your project's `vendor/autoload.php` file is included to autoload the framework's classes.

## Getting Started

Creating a settings page is simple. In your plugin's main bootstrap file or a dedicated service class, instantiate the `\WPTechnix\WPSettings\Settings` class and use its fluent methods to build your page.

### Example Usage

Here is a complete example of how to build a tabbed settings page for a fictional "My Awesome Plugin".

```php
<?php
// In your plugin's main file or a class that runs on `plugins_loaded`.

use WPTechnix\WPSettings\Settings;
use WPTechnix\WPSettings\Contracts\SettingsInterface;

add_action('plugins_loaded', function () {

    // 1. Create a new Settings instance.
    // The only required argument is a unique slug for your page.
    $settingsManager = new Settings(
        'my-awesome-plugin',
        'My Awesome Plugin Settings', // Page Title
        'Awesome Plugin'             // Menu Title
    );

    // 2. Add tabs to organize your options.
    $settingsManager
        ->addTab('general', 'General', 'dashicons-admin-generic')
        ->addTab('advanced', 'Advanced', 'dashicons-admin-settings');

    // 3. Add sections to the tabs.
    $settingsManager
        ->addSection('api_section', 'API Credentials', 'Settings for the external API connection.', 'general')
        ->addSection('display_section', 'Display Options', 'Control the look and feel.', 'general')
        ->addSection('debugging_section', 'Debugging', 'Advanced developer settings.', 'advanced');

    // 4. Add fields to the sections.
    $settingsManager
        ->addField(
            'api_key',
            'api_section',
            'text',
            'API Key',
            ['description' => 'Enter your public API key.']
        )
        ->addField(
            'primary_color',
            'display_section',
            'color',
            'Primary Color',
            ['description' => 'Select a primary color for plugin elements.', 'default' => '#2271b1']
        )
        ->addField(
            'brand_logo',
            'display_section',
            'media',
            'Brand Logo',
            ['description' => 'Upload a logo to display in the header.']
        )
        ->addField(
            'enable_debugging', // This field will control the next one
            'debugging_section',
            'toggle',
            'Enable Debug Mode',
            ['description' => 'When enabled, advanced logging will be active.', 'default' => false]
        )
        ->addField(
            'custom_css',
            'debugging_section',
            'code', // A code editor field
            'Custom CSS',
            [
                'description' => 'Enter custom CSS to be loaded on the front-end.',
                'language'    => 'css', // Specify syntax highlighting mode
                'conditional' => [
                    'field'    => 'enable_debugging', // The ID of the controlling field
                    'value'    => '1',                // The value to check for (1 for 'on')
                    'operator' => '==',               // The comparison operator
                ],
            ]
        );

    // 5. Initialize the settings page.
    // This hooks everything into WordPress.
    $settingsManager->init();


    // You can now use this $settingsManager object to retrieve values.
    // In a DI container setup, you would bind the SettingsInterface to this instance.
    // For this example, we'll just show how to use the object directly.

    function my_plugin_get_color()
    {
        global $settingsManager; // Example of accessing the object
        return $settingsManager->get('primary_color', '#2271b1');
    }
});
```

### Retrieving Setting Values

Once your settings page is initialized, you can retrieve any value using the `get()` method on your `Settings` object.

```php
// Assuming $settingsManager is your instantiated Settings object.

// Get the API Key
$apiKey = $settingsManager->get('api_key');

// Get the primary color with a default fallback value.
$primaryColor = $settingsManager->get('primary_color', '#2271b1');

// Get the debugging status (will be a boolean true/false).
$isDebugEnabled = $settingsManager->get('enable_debugging');

if ($isDebugEnabled) {
    // Do something...
}
```

## Advanced Usage

### Conditional Fields

To make a field appear only when another field has a specific value, use the `conditional` argument.

```php
$settingsManager->addField(
    'license_key',
    'general_section',
    'text',
    'License Key',
    [
        'conditional' => [
            'field'    => 'license_type', // The ID of the field to check
            'value'    => 'pro',          // The value it must have
            'operator' => '==',           // Can be '==', '!=', 'in', or 'not in'
        ]
    ]
);
```

### Customizing the HTML Prefix

By default, all custom CSS classes are prefixed with `wptechnix-settings-` (e.g., `.wptechnix-settings-toggle`). You can provide your own prefix if you want.

```php
$settingsManager = new Settings(
    'my-plugin-slug',
    'My Plugin',
    null,
    [
        'htmlPrefix' => 'myplugin' // Classes will now be prefixed with `myplugin-`
    ]
);
```

---

## License

Licensed under the MIT License.
