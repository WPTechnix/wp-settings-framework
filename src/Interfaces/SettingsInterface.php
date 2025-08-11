<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Interfaces;

/**
 * Defines the contract for a settings page builder.
 */
interface SettingsInterface
{
    /**
     * Sets or overrides the main page title.
     *
     * @param string $pageTitle The new title for the settings page.
     * @return static
     */
    public function setPageTitle(string $pageTitle): static;

    /**
     * Sets or overrides the menu title.
     *
     * @param string $menuTitle The new title for the admin menu item.
     * @return static
     */
    public function setMenuTitle(string $menuTitle): static;

    /**
     * Adds a tab for organizing settings sections.
     *
     * @param string $id    The unique identifier for the tab.
     * @param string $title The text to display on the tab.
     * @param string $icon  Optional. A Dashicon class to display next to the title.
     * @return static
     */
    public function addTab(string $id, string $title, string $icon = ''): static;

    /**
     * Adds a settings section to the page.
     *
     * @param string $id          The unique identifier for the section.
     * @param string $title       The title displayed for the section.
     * @param string $description Optional. A description displayed below the section title.
     * @param string $tabId       Optional. The ID of the tab this section should appear under.
     * @return static
     */
    public function addSection(string $id, string $title, string $description = '', string $tabId = ''): static;

    /**
     * Adds a field to a section.
     *
     * @param string               $id        The unique identifier for the field.
     * @param string               $sectionId The ID of the section this field belongs to.
     * @param string               $type      The field type (e.g., 'text', 'toggle', 'code').
     * @param string               $label     The label displayed for the field.
     * @param array<string, mixed> $args      Optional. An array of additional arguments.
     * @return static
     */
    public function addField(string $id, string $sectionId, string $type, string $label, array $args = []): static;

    /**
     * Initializes the settings page and hooks all components into WordPress.
     *
     * This method must be called after all configuration is complete.
     */
    public function init(): void;

    /**
     * Gets the WordPress option name where settings are stored.
     *
     * @return string The option name.
     */
    public function getOptionName(): string;

    /**
     * Retrieves a setting's value for this settings page.
     *
     * @param string $key     The unique key of the setting to retrieve.
     * @param mixed  $default A fallback value to return if the setting is not found.
     * @return mixed The stored setting value, or the default if not found.
     */
    public function get(string $key, mixed $default = null): mixed;
}
