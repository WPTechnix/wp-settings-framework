<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Textarea Field Class
 */
final class TextareaField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $defaultAttributes = ['rows' => 5, 'cols' => 50, 'class' => 'large-text'];
        $mergedAttributes  = array_merge($defaultAttributes, $attributes);
        printf(
            '<textarea id="%s" name="%s" %s>%s</textarea>',
            esc_attr($this->config->get('id')),
            esc_attr($this->config->get('name')),
            $this->buildAttributesString($mergedAttributes),
            esc_textarea((string)$value)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): string
    {
        return sanitize_textarea_field((string)$value);
    }
}
