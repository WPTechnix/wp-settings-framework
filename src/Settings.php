<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\SettingsInterface;

/**
 * A fluent builder for creating and managing WordPress admin settings pages.
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
 *      activeTab: ?string,
 *      tabs: array<string, array{
 *          title: string,
 *          icon: string
 *      }>,
 *      sections: array<string, array{
 *          title: string,
 *          description: string,
 *          tab: string
 *      }>,
 *      fields: array<string, array<string, mixed>>,
 *      assetPackages: array<string, mixed>,
 *      labels: array<string, string>
 *  }
 */
class Settings implements SettingsInterface
{
    /**
     * The central configuration object for the settings page.
     *
     * @var Config
     */
    protected Config $config;

    /**
     * Instance of the factory responsible for creating field objects.
     *
     * @var FieldFactory
     */
    protected FieldFactory $fieldFactory;

    /**
     * Instance of the asset manager for enqueueing scripts and styles.
     *
     * @var AssetManager
     */
    protected AssetManager $assetManager;

    /**
     * Instance of the page renderer responsible for all HTML output.
     *
     * @var PageRenderer
     */
    protected PageRenderer $pageRenderer;

    /**
     * A cache for the options loaded from the database for the current request.
     * This prevents multiple `get_option()` calls on the same page load.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $savedOptions = null;

    /**
     * Settings constructor.
     *
     * Initializes the settings framework with essential parameters and default configurations.
     *
     * @param string $optionName The name of the option to be stored in the wp_options table.
     * @param string $pageSlug The unique slug for the settings page URL.
     * @param array<string, mixed> $options Optional configuration overrides.
     *
     * @throws InvalidArgumentException If optionName or pageSlug are empty.
     */
    public function __construct(string $optionName, string $pageSlug, array $options = [])
    {
        if (empty($optionName)) {
            throw new InvalidArgumentException('Option name cannot be empty.');
        }
        if (empty($pageSlug)) {
            throw new InvalidArgumentException('Page slug cannot be empty.');
        }

        /** @phpstan-var SettingsConfig $defaults */
        $defaults = [
            'optionName'    => $optionName,
            'optionGroup'   => $optionName . '_group',
            'pageSlug'      => $pageSlug,
            'parentSlug'    => 'options-general.php',
            'capability'    => 'manage_options',
            'pageTitle'     => 'Settings',
            'menuTitle'     => 'Settings',
            'useTabs'       => false,
            'htmlPrefix'    => 'wptechnix-settings',
            'tabs'          => [],
            'activeTab'     => null,
            'sections'      => [],
            'fields'        => [],
            'assetPackages' => $this->getDefaultAssetPackages(),
            'labels'        => [
                'noPermission'    => 'You do not have permission to access this page.',
                'addMediaTitle'   => __('Add media', 'default'),
                'selectMediaText' => __('Select', 'default'),
                'removeMediaText' => __('Remove', 'default'),
                /* translators: %s: Field label */
                'validationError' => 'Invalid value for %s. The setting has been reverted to its default value.',
            ],
        ];

        $this->config = new Config($defaults);
        $this->config->deepMerge($options);

        $this->fieldFactory = new FieldFactory();
        $this->assetManager = new AssetManager($this->config);
        $this->pageRenderer = new PageRenderer($this->config, $this->fieldFactory, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function setPageTitle(string $pageTitle): static
    {
        $this->config->set('pageTitle', $pageTitle);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMenuTitle(string $menuTitle): static
    {
        $this->config->set('menuTitle', $menuTitle);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCapability(string $capability): static
    {
        $this->config->set('capability', $capability);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParentSlug(string $parentSlug): static
    {
        $this->config->set('parentSlug', $parentSlug);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addTab(string $id, string $title, string $icon = ''): static
    {
        $id = sanitize_key($id);
        $this->config->set("tabs.{$id}", ['title' => $title, 'icon' => $icon]);
        $this->config->set('useTabs', true);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addSection(string $id, string $title, string $description = '', string $tabId = ''): static
    {
        $id = sanitize_key($id);
        $this->config->set("sections.{$id}", [
            'title'       => $title,
            'description' => $description,
            'tab'         => $tabId,
        ]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addField(string $id, string $sectionId, string $type, string $label, array $args = []): static
    {
        if (! $this->config->has("sections.{$sectionId}")) {
            throw new InvalidArgumentException("Section '{$sectionId}' must be added before adding fields to it.");
        }
        if (! in_array($type, $this->fieldFactory->getSupportedTypes(), true)) {
            throw new InvalidArgumentException("Field type '{$type}' is not supported.");
        }

        $fieldConfig = array_merge([
            'id'          => $id,
            'name'        => $this->getOptionName() . '[' . $id . ']',
            'section'     => $sectionId,
            'type'        => $type,
            'label'       => $label,
            'description' => '',
            'labels'      => $this->config->get('labels'),
        ], $args);

        $this->config->set("fields.{$id}", $fieldConfig);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        $this->assetManager->init();
    }

    /**
     * Registers the settings page with the WordPress admin menu.
     *
     * @internal This method is a callback for the 'admin_menu' hook and should not be called directly.
     */
    public function registerPage(): void
    {
        add_submenu_page(
            $this->config->get('parentSlug'),
            $this->config->get('pageTitle'),
            $this->config->get('menuTitle'),
            $this->config->get('capability'),
            $this->config->get('pageSlug'),
            [$this->pageRenderer, 'renderPage']
        );
    }

    /**
     * Registers the settings, sections, and fields with the WordPress Settings API.
     *
     * @internal This method is a callback for the 'admin_init' hook and should not be called directly.
     */
    public function registerSettings(): void
    {
        $this->determineAndSetActiveTab();
        $sanitizer = new Sanitizer($this->config, $this->fieldFactory);

        register_setting(
            $this->config->get('optionGroup'),
            $this->config->get('optionName'),
            ['sanitize_callback' => [$sanitizer, 'sanitize']]
        );

        foreach ($this->config->get('sections', []) as $id => $section) {
            add_settings_section(
                $id,
                $section['title'],
                ! empty($section['description'])
                    ? fn() => print('<p class="section-description">' . wp_kses_post($section['description']) . '</p>')
                    : '__return_null',
                $this->config->get('pageSlug')
            );
        }

        foreach ($this->config->get('fields', []) as $id => $field) {
            add_settings_field(
                $id,
                $field['label'],
                [$this->pageRenderer, 'renderField'],
                $this->config->get('pageSlug'),
                $field['section'],
                ['id' => $id, 'label_for' => $id]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! isset($this->savedOptions)) {
            $optionsFromDb      = get_option($this->getOptionName(), []);
            $this->savedOptions = is_array($optionsFromDb) ? $optionsFromDb : [];
        }

        if (array_key_exists($key, $this->savedOptions)) {
            return $this->savedOptions[$key];
        }

        if (null === $default) {
            $default = $this->config->get("fields.{$key}.default");
        }

        return $this->config->get($key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionName(): string
    {
        return $this->config->get('optionName');
    }

    /**
     * Determines the active tab and stores it in the config object.
     *
     * @internal This is called late in the lifecycle to ensure all tabs have been registered.
     */
    private function determineAndSetActiveTab(): void
    {
        if (empty($this->config->get('useTabs'))) {
            return;
        }

        $tabs = $this->config->get('tabs', []);
        if (empty($tabs)) {
            $this->config->set('useTabs', false);

            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $activeTab = sanitize_text_field(wp_unslash($_REQUEST['tab'] ?? ''));

        if (empty($activeTab) || ! isset($tabs[$activeTab])) {
            $activeTab = (string)array_key_first($tabs);
        }

        $this->config->set('activeTab', $activeTab);
    }

    /**
     * Provides default definitions for external asset packages.
     *
     * @return array<string, mixed> The default asset package configurations.
     * @internal
     */
    private function getDefaultAssetPackages(): array
    {
        return [
            'select2'   => [
                'handle' => 'select2',
                'script' => [
                    'src'       => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                    'deps'      => ['jquery'],
                    'version'   => '4.0.13',
                    'in_footer' => true
                ],
                'style'  => [
                    'src'     => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                    'deps'    => [],
                    'version' => '4.0.13'
                ],
            ],
            'flatpickr' => [
                'handle' => 'flatpickr',
                'script' => [
                    'src'       => 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js',
                    'deps'      => [],
                    'version'   => '4.6.13',
                    'in_footer' => true
                ],
                'style'  => [
                    'src'     => 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css',
                    'deps'    => [],
                    'version' => '4.6.13'
                ],
            ],
        ];
    }
}
