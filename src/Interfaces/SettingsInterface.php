<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Interfaces;

/**
 * Defines the public contract for a settings page builder.
 */
interface SettingsInterface
{
    /**
     * Sets the main title of the settings page (the `<h1>` tag).
     *
     * @param string $pageTitle The main title for the settings page.
     *
     * @return static Provides a fluent interface.
     */
    public function setPageTitle(string $pageTitle): static;

    /**
     * Sets the title displayed in the WordPress admin menu.
     *
     * @param string $menuTitle The title for the admin menu item.
     *
     * @return static Provides a fluent interface.
     */
    public function setMenuTitle(string $menuTitle): static;

    /**
     * Sets the required capability to view and save the settings page.
     *
     * @param string $capability The WordPress capability string (e.g., 'manage_options').
     *
     * @return static Provides a fluent interface.
     */
    public function setCapability(string $capability): static;

    /**
     * Sets the parent menu page slug under which this settings page will appear.
     *
     * @param string $parentSlug The slug of the parent menu (e.g., 'options-general.php', 'themes.php').
     *
     * @return static Provides a fluent interface.
     */
    public function setParentSlug(string $parentSlug): static;

    /**
     * Adds a navigation tab to the settings page.
     * This automatically enables the tabbed interface.
     *
     * @param string $id A unique identifier for the tab.
     * @param string $title The visible title of the tab.
     * @param string $icon (Optional) A Dashicons class for an icon (e.g., 'dashicons-admin-generic').
     *
     * @return static Provides a fluent interface.
     */
    public function addTab(string $id, string $title, string $icon = ''): static;

    /**
     * Adds a settings section to group related fields.
     *
     * @param string $id A unique identifier for the section.
     * @param string $title The visible title of the section (an `<h2>` tag).
     * @param string $description (Optional) A short description displayed below the section title.
     * @param string $tabId (Optional) The ID of the tab this section belongs to. Required for tabbed interfaces.
     *
     * @return static Provides a fluent interface.
     */
    public function addSection(string $id, string $title, string $description = '', string $tabId = ''): static;

    /**
     * Adds a setting field to a section.
     *
     * @param string $id A unique identifier for the field, used as the key in the options array.
     * @param string $sectionId The ID of the section this field belongs to.
     * @param string $type The type of field (e.g., 'text', 'toggle', 'select').
     * @param string $label The label displayed for the field.
     * @param array<string, mixed> $args (Optional) Additional arguments for the field, such as 'default',
     *                                   'description', 'options', 'attributes', etc.
     *
     * @return static Provides a fluent interface.
     */
    public function addField(
        string $id,
        string $sectionId,
        string $type,
        string $label,
        array $args = []
    ): static;

    /**
     * Hooks the settings framework into the appropriate WordPress actions.
     * This method must be called to activate the settings page and make it appear.
     *
     * @return void
     */
    public function init(): void;

    /**
     * Gets a saved option value from the database.
     *
     * This is the primary method for retrieving a field's current value for rendering.
     * It intelligently falls back to the field's configured 'default' value if no
     * saved value exists in the database.
     *
     * @param string $key The specific option key (field ID) to retrieve.
     * @param mixed|null $default A final fallback value if no saved option or field default is found.
     *
     * @return mixed The saved value, the field's default value, or the provided default.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Gets the main option name used in the database.
     *
     * This is the top-level key for the array stored in the `wp_options` table.
     *
     * @return string The name of the option array.
     */
    public function getOptionName(): string;
}
