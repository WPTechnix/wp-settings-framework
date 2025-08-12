<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Color Picker Field Class.
 */
final class ColorField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix        = $this->config->get('htmlPrefix', 'wptechnix-settings');
        $defaultAttributes = ['class' => "{$htmlPrefix}-color-picker"];
        $mergedAttributes  = array_merge($defaultAttributes, $attributes);

        printf(
            '<input type="text" id="%s" name="%s" value="%s" %s />',
            esc_attr($this->config->get('id')),
            esc_attr($this->config->get('name')),
            esc_attr((string)$value),
            $this->buildAttributesString($mergedAttributes)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): string
    {
        $color = sanitize_hex_color((string)$value);

        return $color ?? $this->getDefaultValue();
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValue(): string
    {
        return $this->config->get('default', '#000000');
    }
}
