<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\ConfigInterface;

/**
 * Handles all HTML output for the settings page.
 */
final class PageRenderer
{
    /**
     * PageRenderer constructor.
     *
     * @param ConfigInterface $config The shared configuration object.
     * @param FieldFactory $fieldFactory The factory for creating field objects.
     * @param Settings $settings The main Settings instance to access get().
     */
    public function __construct(
        protected ConfigInterface $config,
        protected FieldFactory $fieldFactory,
        protected Settings $settings
    ) {
    }

    /**
     * Renders the entire settings page.
     *
     * This is the main callback function for `add_submenu_page`. It includes
     * a capability check and renders the page structure.
     */
    public function renderPage(): void
    {
        if (! current_user_can($this->config->get('capability'))) {
            wp_die(
                esc_html($this->config->get('labels.noPermission', 'Permission Denied.'))
            );
        }

        ?>
        <div class="wrap">
            <h1><?php
                echo esc_html($this->config->get('pageTitle')); ?></h1>

            <?php
            settings_errors();
            ?>

            <?php
            if (false !== $this->config->get('useTabs') && $this->config->has('tabs')) : ?>
                <?php
                $this->renderTabs(); ?>
                <?php
            endif; ?>

            <!--suppress HtmlUnknownTarget -->
            <form method="post" action="options.php">
                <?php
                settings_fields($this->config->get('optionGroup'));

                if (! empty($this->config->get('useTabs'))) {
                    $activeTab = $this->config->get('activeTab');
                    printf('<input type="hidden" name="tab" value="%s" />', $activeTab);
                    $this->renderSections();
                } else {
                    do_settings_sections($this->config->get('pageSlug'));
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
     */
    public function renderField(array $args): void
    {
        $fieldId = $args['id'] ?? null;
        if (empty($fieldId)) {
            return;
        }

        $fieldConfig = $this->config->get("fields.{$fieldId}");

        if (empty($fieldConfig) || ! is_array($fieldConfig)) {
            return;
        }

        $htmlPrefix = $this->config->get('htmlPrefix');

        $fieldConfig['htmlPrefix'] = $htmlPrefix;

        try {
            $fieldObject = $this->fieldFactory->create($fieldConfig['type'], $fieldConfig);

            $value = $this->settings->get($fieldId, $fieldObject->getDefaultValue());

            $fieldAttributes = $fieldConfig['attributes'] ?? [];
            if (! is_array($fieldAttributes)) {
                $fieldAttributes = [];
            }

            $conditionalAttr = '';
            if (! empty($fieldConfig['conditional'])) {
                $cond            = $fieldConfig['conditional'];
                $conditionalAttr = sprintf(
                    'data-conditional="%s" data-conditional-value="%s" data-conditional-operator="%s"',
                    esc_attr($cond['field'] ?? ''),
                    esc_attr((string)($cond['value'] ?? '')),
                    esc_attr($cond['operator'] ?? '==')
                );
            }

            printf('<div class="%s-field-container" %s>', esc_attr($htmlPrefix), $conditionalAttr);

            $fieldObject->render($value, $fieldAttributes);

            if (! empty($fieldConfig['description']) && 'description' !== $fieldConfig['type']) {
                echo '<p class="description">' . wp_kses_post($fieldConfig['description']) . '</p>';
            }
            echo '</div>';
        } catch (InvalidArgumentException $e) {
            echo '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    /**
     * Renders the navigation tabs for the settings page.
     */
    private function renderTabs(): void
    {
        $htmlPrefix = $this->config->get('htmlPrefix');
        $activeTab  = $this->config->get('activeTab'); // Correct: Read from config.

        $classes = implode(' ', [
            'nav-tab-wrapper',
            $htmlPrefix . '-nav-tab-wrapper'
        ]);

        echo '<nav class="' . esc_attr($classes) . '">';
        foreach ($this->config->get('tabs', []) as $tabId => $tab) {
            $url   = add_query_arg(['page' => $this->config->get('pageSlug'), 'tab' => $tabId]);
            $class = 'nav-tab' . ($tabId === $activeTab ? ' nav-tab-active' : '');
            $icon  = ! empty($tab['icon']) ? '<span class="dashicons ' . esc_attr($tab['icon']) . '"></span>' : '';
            printf(
                '<a href="%s" class="%s">%s%s</a>',
                esc_url($url),
                esc_attr($class),
                $icon,
                esc_html($tab['title'])
            );
        }
        echo '</nav>';
    }


    /**
     * Renders all settings sections associated with active tab.
     */
    private function renderSections(): void
    {
        global $wp_settings_sections, $wp_settings_fields;

        $page      = $this->config->get('pageSlug');
        $activeTab = $this->config->get('activeTab'); // Correct: Read from config.

        if (empty($wp_settings_sections[$page])) {
            return;
        }

        foreach ((array)$wp_settings_sections[$page] as $section) {
            $sectionTab = $this->config->get("sections.{$section['id']}.tab", '');
            if ($sectionTab !== $activeTab) {
                continue;
            }

            if ($section['title']) {
                echo "<h2>" . esc_html($section['title']) . "</h2>\n";
            }

            if ($section['callback']) {
                call_user_func($section['callback'], $section);
            }

            if (isset($wp_settings_fields[$page][$section['id']])) {
                echo '<table class="form-table" role="presentation">';
                do_settings_fields($page, $section['id']);
                echo '</table>';
            }
        }
    }
}
