<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\SettingsInterface;

/**
 * A fluent builder for creating and managing WordPress admin settings pages.
 */
class Settings implements SettingsInterface
{
    /**
     * The settings configuration array.
     *
     * @var array<string, mixed>
     */
    private array $config = [
        'tabs'     => [],
        'sections' => [],
        'fields'   => [],
    ];

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
     * @param string               $pageSlug   The unique settings page slug.
     * @param string|null          $pageTitle  Optional. The title for the settings page. Defaults to "Settings".
     * @param string|null          $menuTitle  Optional. The title for the admin menu. Defaults to the page title.
     * @param array<string, mixed> $options    Optional configuration overrides.
     */
    public function __construct(
        string $pageSlug,
        ?string $pageTitle = null,
        ?string $menuTitle = null,
        array $options = []
    ) {
        if (empty($pageSlug)) {
            throw new InvalidArgumentException('Page slug cannot be empty.');
        }

        $this->fieldFactory = new FieldFactory();
        $this->assetManager = new AssetManager();

        $finalPageTitle = $pageTitle ?? __('Settings', 'default');
        $finalMenuTitle = $menuTitle ?? $finalPageTitle;

        $this->config = array_replace_recursive(
            [
                'pageSlug'    => $pageSlug,
                'pageTitle'   => $finalPageTitle,
                'menuTitle'   => $finalMenuTitle,
                'capability'  => 'manage_options',
                'parentSlug'  => 'options-general.php',
                'useTabs'     => false,
                'optionName'  => $pageSlug . '_settings',
                'optionGroup' => $pageSlug . '_settings_group',
                'htmlPrefix'  => 'wptechnix-settings', // no underscore or dash in end.
                'labels'      => [
                    'noPermission' => __('You do not have permission to access this page.', 'default'),
                    'selectMedia'  => __('Select Media', 'default'),
                    'remove'       => __('Remove', 'default'),
                ],
            ],
            $options
        );
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
    public function addTab(string $id, string $title, string $icon = ''): static
    {
        $this->config['tabs'][$id] = ['title' => $title, 'icon' => $icon];
        $this->config['useTabs'] = true;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addSection(string $id, string $title, string $description = '', string $tabId = ''): static
    {
        $this->config['sections'][$id] = ['title' => $title, 'description' => $description, 'tab' => $tabId];
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
                'name'        => $this->config['optionName'] . '[' . $id . ']',
                'section'     => $sectionId,
                'type'        => $type,
                'label'       => $label,
                'description' => '',
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
        $this->assetManager->setConfig($this->config);
        $this->assetManager->init();

        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Registers the settings page with the WordPress admin menu.
     *
     * @internal
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
     * @internal
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
        // First, check if the options have already been fetched for this request.
        if (!isset($this->options)) {
            // If not, fetch from the database once and cache the result.
            $this->options = get_option($this->getOptionName(), []);
            if (!is_array($this->options)) {
                $this->options = [];
            }
        }

        // Return the value from the cached array, or the provided default.
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        // If the key is not in the options, check for a configured default for that field.
        if (isset($this->config['fields'][$key]['default'])) {
            return $this->config['fields'][$key]['default'];
        }

        return $default;
    }
}
