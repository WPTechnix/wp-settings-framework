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
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';
        $defaultAttributes = ['class' => "{$htmlPrefix}-select2-field"];
        $mergedAttributes = array_merge($defaultAttributes, $attributes);

        printf(
            '<select id="%s" name="%s" %s>',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            $this->buildAttributesString($mergedAttributes)
        );

        $options = $this->config['options'] ?? [];
        foreach ($options as $optionValue => $optionLabel) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr((string) $optionValue),
                selected((string) $value, (string) $optionValue, false),
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
        $allowedValues = array_keys($this->config['options'] ?? []);
        if (in_array((string) $value, $allowedValues, true)) {
            return (string) $value;
        }
        return '';
    }
}
