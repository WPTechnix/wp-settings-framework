<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Description Field Class
 */
final class DescriptionField extends AbstractField
{
    /**
     * @{inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        // This field only displays its description, which is handled by the renderer.
        // It has no input element.
        if (! empty($this->config->get('description'))) {
            echo '<div>' . wp_kses_post($this->config->get('description')) . '</div>';
        }
    }

    /**
     * @{inheritDoc}
     */
    public function sanitize(mixed $value): mixed
    {
        // No value to sanitize.
        return null;
    }

    /**
     * @{inheritDoc}
     */
    public function getDefaultValue(): mixed
    {
        return null;
    }
}
