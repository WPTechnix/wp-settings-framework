<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Interfaces;

/**
 * Defines the contract for a settings field.
 */
interface FieldInterface
{
    /**
     * Render the field's HTML markup.
     *
     * This method is responsible for echoing the complete HTML for the form element.
     *
     * @param mixed                          $value      The current value of the field.
     * @param array<string, string|int|bool> $attributes Additional HTML attributes for the field.
     */
    public function render(mixed $value, array $attributes): void;

    /**
     * Sanitize the field's value before saving.
     *
     * This method ensures the input data is clean and in the correct format
     * before being persisted to the database.
     *
     * @param mixed $value The raw input value to be sanitized.
     * @return mixed The sanitized value.
     */
    public function sanitize(mixed $value): mixed;

    /**
     * Get the default value for the field.
     *
     * Provides a fallback value when no value has been saved yet.
     *
     * @return mixed The default value.
     */
    public function getDefaultValue(): mixed;
}
