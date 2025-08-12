<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Radio Field Class
 */
final class RadioField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $options = $this->config->get('options', []);
        foreach ($options as $optionValue => $optionLabel) {
            $radioId = $this->config->get('id') . '_' . sanitize_key($optionValue);

            $htmlPrefix = $this->config->get('htmlPrefix', 'wptechnix-settings') ?? '';

            printf(
                '<label for="%s" class="%s-radio-label">
                           <input type="radio" id="%s" name="%s" value="%s" %s %s />
                           %s
                       </label>',
                esc_attr($radioId),
                $htmlPrefix,
                esc_attr($radioId),
                esc_attr($this->config->get('name')),
                esc_attr((string)$optionValue),
                checked((string)$value, (string)$optionValue, false),
                $this->buildAttributesString($attributes),
                esc_html($optionLabel)
            );
        }
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
