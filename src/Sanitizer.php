<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\ConfigInterface;

/**
 * Sanitizes settings before they are saved to the database.
 *
 * This class is state-aware. When sanitizing a form submission (especially
 * from a tabbed interface), it merges the newly submitted values with the
 * existing saved values to prevent data loss from other tabs.
 */
final class Sanitizer
{
    /**
     * Sanitizer constructor.
     *
     * @param ConfigInterface $config The shared configuration object.
     * @param FieldFactory $fieldFactory The factory for creating field objects.
     */
    public function __construct(
        protected ConfigInterface $config,
        protected FieldFactory $fieldFactory
    ) {
    }

    /**
     * Sanitizes the settings array by merging new input with existing options.
     *
     * This is the main callback for the 'sanitize_callback' argument in
     * `register_setting`. It processes the raw input from the $_POST array.
     *
     * @param mixed $input The raw input from the form submission (from `$_POST`).
     *
     * @return array<string, mixed> The complete, sanitized settings array ready for saving.
     */
    public function sanitize(mixed $input): array
    {
        // 1. Fetch all previously saved options from the database.
        // This is the base we'll be merging the new values into.
        $optionName = $this->config->get('optionName');
        $oldOptions = get_option($optionName, []);
        $oldOptions = is_array($oldOptions) ? $oldOptions : [];

        // If the submitted data isn't an array, it's invalid. Return the old
        // options to prevent data loss and show an error.
        if (! is_array($input)) {
            add_settings_error(
                $this->config->get('optionGroup'),
                'invalid_input_type',
                'Settings data received was not in the expected format. No changes were saved.',
                'error'
            );

            return $oldOptions;
        }

        // 2. Determine which fields we need to process from this submission.
        $fieldsToProcess = $this->getFieldsToProcess();
        $newValues       = [];

        foreach ($fieldsToProcess as $fieldId => $fieldConfig) {
            $rawValue  = $input[$fieldId] ?? null;
            $fieldType = $fieldConfig['type'] ?? 'text';

            if ('description' === $fieldType) {
                continue; // Description-only fields are not saved.
            }

            try {
                $field        = $this->fieldFactory->create($fieldType, $fieldConfig);
                $defaultValue = $field->getDefaultValue();

                // If a value is not submitted (e.g., an unchecked checkbox),
                // it will be null. We should process it to get its "off" state (e.g., '0' or '').
                $sanitizedValue = $field->sanitize($rawValue);

                // Apply custom validation if it exists.
                if (isset($fieldConfig['validate_callback']) && is_callable($fieldConfig['validate_callback'])) {
                    if (! call_user_func($fieldConfig['validate_callback'], $sanitizedValue)) {
                        $sanitizedValue = $defaultValue; // Revert on validation fail.

                        $errorMessageTemplate = $this->config->get(
                            'labels.validationError',
                            'Invalid value for %s. Reverted to default.'
                        );
                        $errorMessage         = sprintf($errorMessageTemplate, $fieldConfig['label'] ?? $fieldId);

                        add_settings_error(
                            $this->config->get('optionGroup'),
                            'validation_error_' . $fieldId,
                            $errorMessage,
                            'error'
                        );
                    }
                }

                // Store the processed value.
                $newValues[$fieldId] = $sanitizedValue;
            } catch (InvalidArgumentException) {
                // Failsafe if field type is somehow invalid.
                $newValues[$fieldId] = $fieldConfig['default'] ?? '';
            }
        }

        // 3. Merge the newly sanitized values into the old options and return.
        // This preserves all settings from other tabs.
        return array_merge($oldOptions, $newValues);
    }

    /**
     * Determines which fields should be processed in the current request.
     *
     * If tabs are enabled, it returns only the fields for the active tab.
     * Otherwise, it returns all registered fields.
     *
     * @return array<string, mixed> An array of field configurations to process.
     */
    private function getFieldsToProcess(): array
    {
        $allFields = $this->config->get('fields', []);

        // If not using tabs, process all fields.
        if (empty($this->config->get('useTabs'))) {
            return $allFields;
        }

        $activeTab = $this->config->get('activeTab');

        if (empty($activeTab)) {
            // Failsafe: if tabs are on but no tab is active, process nothing
            // to be safe.
            return [];
        }

        // Filter all fields to get only those belonging to the active tab.
        return array_filter($allFields, function ($field) use ($activeTab) {
            $sectionId = $field['section'] ?? null;
            if (empty($sectionId)) {
                return false;
            }
            // A field belongs to a tab via its section's 'tab' property.
            $sectionTab = $this->config->get("sections.{$sectionId}.tab");

            return $sectionTab === $activeTab;
        });
    }
}
