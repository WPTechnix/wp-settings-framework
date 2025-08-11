<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

/**
 * Handles all HTML output for the settings page.
 *
 * This class isolates the presentation logic from the business logic. It is
 * responsible for rendering the main page wrapper, navigation tabs, and the
 * settings form itself.
 *
 * @noinspection HtmlUnknownAttribute
 */
final class PageRenderer
{
    /**
     * The main settings configuration array.
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * The FieldFactory instance for creating field objects.
     *
     * @var FieldFactory
     */
    private FieldFactory $fieldFactory;

    /**
     * PageRenderer constructor.
     *
     * @param array<string, mixed> $config       The main settings configuration.
     * @param FieldFactory         $fieldFactory The factory for creating field objects.
     */
    public function __construct(array $config, FieldFactory $fieldFactory)
    {
        $this->config = $config;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Renders the entire settings page.
     *
     * This is the main callback function for `add_submenu_page`. It includes
     * a capability check and renders the page structure.
     */
    public function renderPage(): void
    {
        if (!empty($this->config['capability']) && !current_user_can($this->config['capability'])) {
            wp_die(esc_html($this->config['labels']['noPermission'] ?? 'Permission denied.'));
        }

        $activeTab = $this->getActiveTab();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($this->config['pageTitle']); ?></h1>

            <?php if (!empty($this->config['useTabs']) && !empty($this->config['tabs'])) : ?>
                <?php $this->renderTabs($activeTab); ?>
            <?php endif; ?>

            <!--suppress HtmlUnknownTarget -->
            <form method="post" action="options.php">
                <?php
                settings_fields($this->config['optionGroup']);

                if (!empty($this->config['useTabs']) && !empty($activeTab)) {
                    $this->renderSectionsForTab($activeTab);
                } else {
                    do_settings_sections($this->config['pageSlug']);
                }

                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * The WordPress callback for rendering a settings field.
     *
     * This method passes the `htmlPrefix` to the field configuration, ensuring
     * that the rendered field's HTML has the correct CSS classes.
     *
     * @param array<string, mixed> $args Arguments passed from `add_settings_field`.
     * @return void
     */
    public function renderField(array $args): void
    {
        $fieldId = $args['id'] ?? '';
        $fieldConfig = $this->config['fields'][$fieldId] ?? null;

        if (empty($fieldConfig)) {
            return; // Safety check.
        }

        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';
        $fieldConfig['htmlPrefix'] = $htmlPrefix; // Pass the prefix so that later we can use in fields.

        $options = get_option($this->config['optionName'], []);
        $value = $options[$fieldId] ?? $fieldConfig['default'] ?? null;

        try {
            $field = $this->fieldFactory->create($fieldConfig['type'], $fieldConfig);

            $conditionalAttr = '';
            if (!empty($fieldConfig['conditional'])) {
                $cond = $fieldConfig['conditional'];
                $conditionalAttr = sprintf(
                    'data-conditional="%s" data-conditional-value="%s" data-conditional-operator="%s"',
                    esc_attr($cond['field'] ?? ''),
                    esc_attr((string) ($cond['value'] ?? '')),
                    esc_attr($cond['operator'] ?? '==')
                );
            }

            printf('<div class="%s-field-container" %s>', esc_attr($htmlPrefix), $conditionalAttr);
            $field->render($value, $fieldConfig['attributes'] ?? []);

            if (!empty($fieldConfig['description']) && 'description' !== $fieldConfig['type']) {
                echo '<p class="description">' . wp_kses_post($fieldConfig['description']) . '</p>';
            }
            echo '</div>';
        } catch (\InvalidArgumentException $e) {
            echo '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    /**
     * Renders the navigation tabs for the settings page.
     *
     * @param string $activeTab The slug of the currently active tab.
     */
    private function renderTabs(string $activeTab): void
    {
        echo '<nav class="nav-tab-wrapper" style="margin-bottom: 20px;">';
        foreach ($this->config['tabs'] as $tabId => $tab) {
            $url = add_query_arg(['page' => $this->config['pageSlug'], 'tab' => $tabId]);
            $class = 'nav-tab' . ($activeTab === $tabId ? ' nav-tab-active' : '');
            printf(
                '<a href="%s" class="%s">%s %s</a>',
                esc_url($url),
                esc_attr($class),
                !empty($tab['icon']) ?
                    '<span class="dashicons ' . esc_attr($tab['icon']) . '" style="margin-right: 5px;"></span>'
                    : '',
                esc_html($tab['title'])
            );
        }
        echo '</nav>';
    }

    /**
     * Renders all settings sections associated with a specific tab.
     *
     * @param string $activeTab The slug of the currently active tab.
     */
    private function renderSectionsForTab(string $activeTab): void
    {
        global $wp_settings_sections, $wp_settings_fields;

        $page = $this->config['pageSlug'];

        if (empty($wp_settings_sections[$page])) {
            return;
        }

        foreach ((array) $wp_settings_sections[$page] as $section) {
            $sectionTab = $this->config['sections'][$section['id']]['tab'] ?? '';
            if ($sectionTab !== $activeTab) {
                continue;
            }

            if ($section['title']) {
                echo "<h2>" . esc_html($section['title']) . "</h2>\n";
            }

            if ($section['callback']) {
                call_user_func($section['callback'], $section);
            }

            if (
                !isset($wp_settings_fields) ||
                !isset($wp_settings_fields[$page]) ||
                !isset($wp_settings_fields[$page][$section['id']])
            ) {
                continue;
            }
            echo '<table class="form-table" role="presentation">';
            do_settings_fields($page, $section['id']);
            echo '</table>';
        }
    }


    /**
     * Determines the currently active tab from the URL query string.
     *
     * @return string The slug of the active tab, or an empty string if none.
     */
    private function getActiveTab(): string
    {
        if (empty($this->config['useTabs']) || empty($this->config['tabs'])) {
            return '';
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $activeTab = sanitize_text_field($_GET['tab'] ?? '');
        if (empty($activeTab) || !isset($this->config['tabs'][$activeTab])) {
            return array_key_first($this->config['tabs']) ?? '';
        }
        return $activeTab;
    }
}
