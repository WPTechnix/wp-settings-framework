<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Select Field Class
 */
final class SelectField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix        = $this->config->get('htmlPrefix', 'wptechnix-settings');
        $defaultAttributes = ['class' => "{$htmlPrefix}-select2-field"];
        $mergedAttributes  = array_merge($defaultAttributes, $attributes);

        $options = $this->config->get('options', []);

        if (isset($mergedAttributes['data-placeholder'])) {
            $options = [
                '' => esc_attr((string)$mergedAttributes['data-placeholder']),
                ...$options
            ];
        }

        printf(
            '<select id="%s" name="%s" %s>',
            esc_attr($this->config->get('id')),
            esc_attr($this->config->get('name')),
            $this->buildAttributesString($mergedAttributes)
        );

        foreach ($options as $optionValue => $optionLabel) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr((string)$optionValue),
                selected((string)$value, (string)$optionValue, false),
                esc_html($optionLabel)
            );
        }
        echo '</select>';
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): string
    {
        $allowedValues = array_keys($this->config->get('options', []));
        if (in_array((string)$value, $allowedValues, true)) {
            return (string)$value;
        }

        return '';
    }
}
