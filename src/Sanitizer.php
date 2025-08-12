<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;

/**
 * Sanitizes the settings array before it is saved to the database.
 */
final class Sanitizer
{
    /**
     * The full settings configuration array.
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
     * Sanitizer constructor.
     *
     * @param array<string, mixed> $config       The settings configuration.
     * @param FieldFactory         $fieldFactory The factory for creating field objects.
     */
    public function __construct(array $config, FieldFactory $fieldFactory)
    {
        $this->config = $config;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Sanitizes the entire settings array.
     *
     * This is the main callback for the 'sanitize_callback' argument in
     * `register_setting`. It processes the raw input from the $_POST array.
     *
     * @param mixed $input The raw input from the form submission. Expects an array.
     * @return array<string, mixed> The sanitized settings array ready for saving.
     */
    public function sanitize(mixed $input): array
    {
        if (!is_array($input)) {
            add_settings_error(
                $this->config['optionGroup'],
                'invalid_input_type',
                'Settings data received was not in the expected format.',
                'error'
            );
            return [];
        }

        $sanitized = [];
        $fields = $this->config['fields'] ?? [];

        foreach ($fields as $fieldId => $fieldConfig) {
            $rawValue = $input[$fieldId] ?? null;

            $fieldType = $fieldConfig['type'] ?? 'text';

            if ('description' === $fieldType) {
                continue;
            }

            try {
                $field = $this->fieldFactory->create($fieldType, $fieldConfig);
                $defaultValue = $field->getDefaultValue();

                // If value is not submitted (e.g., unchecked checkbox), use default.
                if (null === $rawValue) {
                    $sanitized[$fieldId] = $defaultValue;
                    continue;
                }

                // Apply the field's specific sanitization method.
                $sanitizedValue = $field->sanitize($rawValue);

                // Apply custom validation callback if it exists.
                if (isset($fieldConfig['validate_callback']) && is_callable($fieldConfig['validate_callback'])) {
                    if (!call_user_func($fieldConfig['validate_callback'], $sanitizedValue)) {
                        $sanitizedValue = $defaultValue; // Revert on validation fail.

                        // Use the configurable label for the error message.
                        $errorMessageTemplate = $this->config['labels']['validationError'] ??
                                    'Invalid value for %s. The setting has been reverted to its default value.';
                        $errorMessage = sprintf($errorMessageTemplate, $fieldConfig['label'] ?? '');

                        add_settings_error(
                            $this->config['optionGroup'],
                            'validation_error_' . $fieldId,
                            $errorMessage,
                            'error'
                        );
                    }
                }

                $sanitized[$fieldId] = $sanitizedValue;
            } catch (InvalidArgumentException) {
                // This should not happen if types are validated on creation, but as a safeguard:
                $sanitized[$fieldId] = $fieldConfig['default'] ?? '';
            }
        }

        return $sanitized;
    }
}
