# WPTechnix Settings Framework: Usage & Field Reference

Welcome to the examples directory for the WPTechnix Settings Framework. This document provides a comprehensive guide to building a settings page and a detailed reference for every available field type and its configuration options.

For installation instructions, please see the main [README.md](../README.md) in the root of the project.

## 1. Creating a Settings Page

The foundation of the framework is the `\WPTechnix\WPSettings\Settings` class. You instantiate this class to begin building your page.

### The `Settings` Class Constructor

The constructor creates your settings page object.

```php
new Settings(string $pageSlug, ?string $pageTitle = null, ?string $menuTitle = null, array $options = [])```

*   `$pageSlug` (string, **required**): A unique slug for your settings page (e.g., `my-plugin-settings`). This is used in the URL.
*   `$pageTitle` (string|null, optional): The main `<h1>` title displayed at the top of your settings page. If omitted, it defaults to "Settings".
*   `$menuTitle` (string|null, optional): The text displayed in the WordPress admin menu. If omitted, it defaults to the `$pageTitle`.
*   `$options` (array, optional): An associative array to override default page settings.

#### Constructor Options (`$options` array)

You can pass the following keys in the `$options` array:

| Key | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `parentSlug` | string | `'options-general.php'` | The slug of the parent menu to attach this page to (e.g., `'edit.php?post_type=page'`, `'tools.php'`). |
| `capability`| string | `'manage_options'` | The WordPress capability required for a user to view this page. |
| `htmlPrefix` | string | `'wptechnix-settings'` | A prefix for all custom HTML classes to prevent CSS/JS conflicts. |

### Basic Structure

Every settings page follows this basic pattern:

```php
<?php
use WPTechnix\WPSettings\Settings;

// 1. Instantiate the Settings manager
$settingsManager = new Settings('my-plugin-slug', 'My Plugin Settings');

// 2. Add at least one Section
$settingsManager->addSection('main_section', 'Main Settings');

// 3. Add Fields to your section
$settingsManager->addField('api_key', 'main_section', 'text', 'API Key');

// 4. Initialize the page to hook it into WordPress
$settingsManager->init();
```

---

## 2. Field Type Reference

This is a comprehensive guide to every field type available in the framework.

The `addField()` method has the following signature:
`addField(string $id, string $sectionId, string $type, string $label, array $args = [])`

### Common Field Arguments (`$args` array)

These arguments can be used with almost every field type:

*   `description` (string): Help text displayed below the field. Supports HTML.
*   `default` (mixed): A default value for the field if none is saved in the database.
*   `attributes` (array): An associative array of custom HTML attributes to add to the input element (see "Advanced Usage" below).
*   `conditional` (array): An array to control the field's visibility based on another field's value (see "Advanced Usage" below).

---

### Text & Input Fields

#### Field: Text (`type: 'text'`)
A standard single-line text input.
*   **Example:**
    ```php
    $settings->addField(
        'api_key',
        'main_section',
        'text',
        'API Key',
        [
            'description' => 'Enter your public API key.',
            'attributes'  => [
                'placeholder' => 'pub_xxxxxxxxxx',
                'class'       => 'regular-text code',
            ],
        ]
    );
    ```

#### Field: Email (`type: 'email'`)
A text input with HTML5 `type="email"` validation.
*   **Example:**
    ```php
    $settings->addField(
        'admin_email',
        'main_section',
        'email',
        'Admin Email',
        [
            'description' => 'The email address for notifications.',
        ]
    );
    ```

#### Field: Password (`type: 'password'`)
A text input where the value is obscured.
*   **Example:**
    ```php
    $settings->addField(
        'secret_key',
        'main_section',
        'password',
        'Secret Key',
        [
            'description' => 'Your secret key will not be shown.',
        ]
    );
    ```

#### Field: Number (`type: 'number'`)
A number input. You can use the `attributes` argument to set `min`, `max`, and `step`.
*   **Example:**
    ```php
    $settings->addField(
        'item_limit',
        'main_section',
        'number',
        'Item Limit',
        [
            'default'    => 10,
            'attributes' => [
                'min'  => 1,
                'max'  => 50,
                'step' => 1,
            ],
        ]
    );
    ```

#### Field: URL (`type: 'url'`)
A text input with HTML5 `type="url"` validation.
*   **Example:**
    ```php
    $settings->addField(
        'website_url',
        'main_section',
        'url',
        'Website URL',
        [
            'attributes' => [
                'placeholder' => 'https://example.com',
            ],
        ]
    );
    ```

#### Field: Textarea (`type: 'textarea'`)
A standard multi-line text area. Use `attributes` to control `rows` and `cols`.
*   **Example:**
    ```php
    $settings->addField(
        'custom_header_text',
        'main_section',
        'textarea',
        'Header Text',
        [
            'attributes' => [
                'rows'        => 4,
                'placeholder' => 'Enter a welcome message...',
            ],
        ]
    );
    ```

---

### Choice & Selection Fields

These fields use a special `options` argument.

*   `options` (array): An associative array where the `key` is the value that gets saved, and the `value` is the display label. `['saved_value' => 'Displayed Label']`

#### Field: Checkbox (`type: 'checkbox'`)
A single checkbox. Saves `true` if checked, `false` if not.
*   **Example:**
    ```php
    $settings->addField(
        'enable_tracking',
        'main_section',
        'checkbox',
        'Enable Tracking',
        [
            'description' => 'Allow usage data to be collected.',
        ]
    );
    ```

#### Field: Toggle (`type: 'toggle'`)
A modern on/off toggle switch. Functionally identical to a checkbox.
*   **Example:**
    ```php
    $settings->addField(
        'dark_mode',
        'main_section',
        'toggle',
        'Enable Dark Mode',
        [
            'default' => true,
        ]
    );
    ```

#### Field: Select (`type: 'select'`)
A dropdown select menu.
*   **Configuration Arguments:**
    *   `options` (array, required): The key/value pairs for the dropdown options.
*   **Example:**
    ```php
    $settings->addField(
        'font_size',
        'main_section',
        'select',
        'Font Size',
        [
            'default' => 'medium',
            'options' => [
                'small'  => 'Small',
                'medium' => 'Medium',
                'large'  => 'Large',
            ],
        ]
    );
    ```

#### Field: Multi-Select (`type: 'multiselect'`)
A dropdown that allows for multiple selections. Saves an array of values.
*   **Configuration Arguments:**
    *   `options` (array, required): The key/value pairs for the options.
*   **Example:**
    ```php
    $settings->addField(
        'post_types',
        'main_section',
        'multiselect',
        'Applicable Post Types',
        [
            'default' => ['post'],
            'options' => [
                'post'    => 'Posts',
                'page'    => 'Pages',
                'product' => 'Products',
            ],
        ]
    );
    ```

#### Field: Radio (`type: 'radio'`)
A group of radio buttons where only one option can be selected.
*   **Configuration Arguments:**
    *   `options` (array, required): The key/value pairs for the radio options.
*   **Example:**
    ```php
    $settings->addField(
        'image_alignment',
        'main_section',
        'radio',
        'Image Alignment',
        [
            'default' => 'left',
            'options' => [
                'left'   => 'Align Left',
                'center' => 'Align Center',
                'right'  => 'Align Right',
            ],
        ]
    );
    ```

#### Field: Button Group (`type: 'buttongroup'`)
A modern, styled button group that functions identically to a radio field.
*   **Configuration Arguments:**
    *   `options` (array, required): The key/value pairs for the buttons.
*   **Example:**
    ```php
    $settings->addField(
        'layout_style',
        'main_section',
        'buttongroup',
        'Layout Style',
        [
            'default' => 'grid',
            'options' => [
                'grid' => 'Grid',
                'list' => 'List',
            ],
        ]
    );
    ```

---

### Enhanced UI Fields

#### Field: Color (`type: 'color'`)
A color picker that uses the native WordPress color picker.
*   **Example:**
    ```php
    $settings->addField(
        'primary_color',
        'main_section',
        'color',
        'Primary Color',
        [
            'default' => '#2271b1',
        ]
    );
    ```

#### Field: Date (`type: 'date'`)
A date picker input.
*   **Example:**
    ```php
    $settings->addField('start_date', 'main_section', 'date', 'Campaign Start Date');
    ```

#### Field: DateTime (`type: 'datetime'`)
A date and time picker input.
*   **Example:**
    ```php
    $settings->addField('event_datetime', 'main_section', 'datetime', 'Event Date & Time');
    ```

#### Field: Time (`type: 'time'`)
A time picker input.
*   **Example:**
    ```php
    $settings->addField('closing_time', 'main_section', 'time', 'Closing Time');
    ```

#### Field: Range (`type: 'range'`)
An enhanced slider for selecting a number within a range.
*   **Example:**
    ```php
    $settings->addField(
        'opacity_level',
        'main_section',
        'range',
        'Opacity Level (%)',
        [
            'default'    => 80,
            'attributes' => [
                'min'  => 0,
                'max'  => 100,
                'step' => 5,
            ],
        ]
    );
    ```

---

### Advanced & Special Fields

#### Field: Media (`type: 'media'`)
A media uploader that integrates with the WordPress Media Library. It saves the attachment ID.
*   **Example:**
    ```php
    $settings->addField('site_logo', 'main_section', 'media', 'Site Logo');
    ```

#### Field: Code (`type: 'code'`)
A code editor with syntax highlighting, powered by CodeMirror.
*   **Configuration Arguments:**
    *   `language` (string): The syntax highlighting mode. Can be `css`, `javascript` (or `js`), or `html`. Defaults to `css`.
*   **Example:**
    ```php
    $settings->addField(
        'custom_js',
        'main_section',
        'code',
        'Footer JavaScript',
        [
            'language'    => 'javascript',
            'description' => 'This code will be added to your site footer.',
        ]
    );
    ```

#### Field: Description (`type: 'description'`)
A special read-only field used to display information. It has no input and saves no value. The content is passed via the `description` argument.
*   **Example:**
    ```php
    $settings->addField(
        'shortcode_info',
        'main_section',
        'description',
        'Shortcode',
        [
            'description' => 'To display the form, use the shortcode: <code>[my_awesome_form]</code>',
        ]
    );
    ```

---

## 3. Advanced Usage

### Custom HTML Attributes (`attributes`)

The `attributes` argument gives you direct access to the HTML input element. You can pass an associative array of any valid HTML attribute, and it will be added to the field. This is incredibly powerful for adding placeholders, data attributes, or accessibility enhancements.

**Example:**
```php
$settings->addField(
    'api_key',
    'main_section',
    'text',
    'API Key',
    [
        'attributes' => [
            'placeholder'      => 'Enter your 24-character key',
            'maxlength'        => 24,
            'required'         => true,          // Renders as the `required` attribute
            'data-api-version' => 'v3',     // Renders as `data-api-version="v3"`
        ],
    ]
);
```

### Conditional Logic (`conditional`)

The `conditional` argument makes a field appear only when another field meets a certain condition. It's an array with three keys:

*   `field` (string, required): The ID of the field to watch.
*   `value` (string, required): The value the watched field must have. For toggles/checkboxes, use `'1'` for "on".
*   `operator` (string, optional): The comparison operator. Can be `==` (default), `!=`, `in`, or `not in`.

**Example:**
```php
// The controlling field
$settings->addField(
    'shipping_method',
    'main_section',
    'radio',
    'Shipping Method',
    [
        'options' => [
            'flat'   => 'Flat Rate',
            'pickup' => 'Local Pickup',
        ],
    ]
);

// This field only shows if 'shipping_method' is 'pickup'
$settings->addField(
    'pickup_location',
    'main_section',
    'textarea',
    'Pickup Location Address',
    [
        'conditional' => [
            'field'    => 'shipping_method',
            'value'    => 'pickup',
            'operator' => '==',
        ],
    ]
);
```
