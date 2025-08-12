<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\SettingsInterface;

/**
 * A fluent builder for creating and managing WordPress admin settings pages.
 *
 * This class provides a comprehensive API to register settings pages, tabs,
 * sections, and fields, while handling rendering, sanitization, and asset management.
 *
 * @phpstan-type SettingsConfig array{
 *      optionName: string,
 *      optionGroup: string,
 *      pageSlug: string,
 *      parentSlug: string,
 *      capability: string,
 *      pageTitle: string,
 *      menuTitle: string,
 *      useTabs: bool,
 *      htmlPrefix: string,
 *      tabs: array<string, array{title: string, icon: string}>,
 *      sections: array<string, array{title: string, description: string, tab: string}>,
 *      fields: array<string, array<string, mixed>>,
 *      assetPackages: array<string, mixed>,
 *      labels: array{
 *          noPermission: string,
 *          addMediaTitle: string,
 *          selectMediaText: string,
 *          removeMediaText: string,
 *          validationError: string
 *      }
 *  }
 */
class Settings implements SettingsInterface
{
    /**
     * The settings configuration array.
     *
     * @var array
     * @phpstan-var SettingsConfig
     */
    private array $config;

    /**
     * The FieldFactory instance.
     *
     * @var FieldFactory
     */
    private FieldFactory $fieldFactory;

    /**
     * The AssetManager instance.
     *
     * @var AssetManager
     */
    private AssetManager $assetManager;

    /**
     * A cached copy of the settings options array from the database.
     *
     * @var array<string, mixed>|null
     */
    private ?array $options = null;

    /**
     * Settings constructor.
     *
     * @param string $optionName The name of the option to be stored in the wp_options table.
     * @param string $pageSlug   The unique slug for the settings page URL.
     * @param array<string, mixed> $options Optional configuration overrides. Allows customization of titles,
     *                                      labels, and other framework behaviors. The developer using the
     *                                      framework is responsible for passing pre-translated strings here.
     */
    public function __construct(
        string $optionName,
        string $pageSlug,
        array $options = []
    ) {
        if (empty($optionName)) {
            throw new InvalidArgumentException('Option name cannot be empty.');
        }
        if (empty($pageSlug)) {
            throw new InvalidArgumentException('Page slug cannot be empty.');
        }

        /** @phpstan-var SettingsConfig $defaults */

        $defaults = [
            'optionName'  => $optionName,
            'optionGroup' => $optionName . '_group',
            'pageSlug'    => $pageSlug,
            'parentSlug'  => 'options-general.php',
            'capability'  => 'manage_options',
            'pageTitle'   => 'Settings',
            'menuTitle'   => 'Settings',
            'useTabs'     => false,
            'htmlPrefix'  => 'wptechnix-settings',
            'tabs'        => [],
            'sections'    => [],
            'fields'      => [],
            'assetPackages' => [],
            'labels'      => [
                'noPermission'    => 'You do not have permission to access this page.',
                'addMediaTitle'   => __('Add media', 'default'),
                'selectMediaText' => __('Select', 'default'),
                'removeMediaText' => __('Remove', 'default'),
                /* translators: %s: Field label */
                'validationError' => 'Invalid value for %s. The setting has been reverted to its default value.',
            ],
        ];

        /** @phpstan-var SettingsConfig $finalConfig */
        $finalConfig = array_replace_recursive($defaults, $options);

        $this->config = $finalConfig;

        $this->fieldFactory = new FieldFactory();

        $this->assetManager = new AssetManager($this->config);
    }

    /**
     * {@inheritDoc}
     */
    public function setPageTitle(string $pageTitle): static
    {
        $this->config['pageTitle'] = $pageTitle;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMenuTitle(string $menuTitle): static
    {
        $this->config['menuTitle'] = $menuTitle;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCapability(string $capability): static
    {
        $this->config['capability'] = $capability;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParentSlug(string $parentSlug): static
    {
        $this->config['parentSlug'] = $parentSlug;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addTab(string $id, string $title, string $icon = ''): static
    {
        $this->config['tabs'][$id] = [
            'title' => $title,
            'icon' => $icon
        ];
        $this->config['useTabs'] = true;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addSection(string $id, string $title, string $description = '', string $tabId = ''): static
    {
        $this->config['sections'][$id] = [
            'title' => $title,
            'description' => $description,
            'tab' => $tabId
        ];
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addField(
        string $id,
        string $sectionId,
        string $type,
        string $label,
        array $args = []
    ): static {
        if (!isset($this->config['sections'][$sectionId])) {
            throw new InvalidArgumentException("Section '{$sectionId}' must be added before adding fields to it.");
        }
        if (!in_array($type, $this->fieldFactory->getSupportedTypes(), true)) {
            throw new InvalidArgumentException("Field type '{$type}' is not supported.");
        }

        $this->config['fields'][$id] = array_merge(
            [
                'id'          => $id,
                'name'        => $this->getOptionName() . '[' . $id . ']',
                'section'     => $sectionId,
                'type'        => $type,
                'label'       => $label,
                'description' => '',
                'labels'      => $this->config['labels'], // Pass labels to fields.
            ],
            $args
        );
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        $this->assetManager->init();

        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Registers the settings page with the WordPress admin menu.
     *
     * @internal This method is intended for internal use by the class.
     */
    public function registerPage(): void
    {
        $renderer = new PageRenderer($this->config, $this->fieldFactory);

        add_submenu_page(
            $this->config['parentSlug'],
            $this->config['pageTitle'],
            $this->config['menuTitle'],
            $this->config['capability'],
            $this->config['pageSlug'],
            [$renderer, 'renderPage']
        );
    }

    /**
     * Registers the settings, sections, and fields with the WordPress Settings API.
     *
     * @internal This method is intended for internal use by the class.
     */
    public function registerSettings(): void
    {
        $sanitizer = new Sanitizer($this->config, $this->fieldFactory);
        $renderer = new PageRenderer($this->config, $this->fieldFactory);

        register_setting(
            $this->config['optionGroup'],
            $this->config['optionName'],
            ['sanitize_callback' => [$sanitizer, 'sanitize']]
        );

        foreach ($this->config['sections'] as $id => $section) {
            add_settings_section(
                $id,
                $section['title'],
                !empty($section['description'])
                    ? fn() => print('<p class="section-description">' . wp_kses_post($section['description']) . '</p>')
                    : '__return_null',
                $this->config['pageSlug']
            );
        }

        foreach ($this->config['fields'] as $id => $field) {
            add_settings_field(
                $id,
                $field['label'],
                [$renderer, 'renderField'],
                $this->config['pageSlug'],
                $field['section'],
                ['id' => $id, 'label_for' => $id]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionName(): string
    {
        return $this->config['optionName'];
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->options === null) {
            $optionsFromDb = get_option($this->getOptionName(), []);
            $this->options = is_array($optionsFromDb) ? $optionsFromDb : [];
        }

        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        if (isset($this->config['fields'][$key]['default'])) {
            return $this->config['fields'][$key]['default'];
        }

        return $default;
    }
}
