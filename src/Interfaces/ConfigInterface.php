<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Interfaces;

use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use Countable;

/**
 * Settings Builder Configuration interface.
 *
 * All key-based operations support dot notation for nested config access.
 *
 * @extends IteratorAggregate<string,mixed>
 * @extends ArrayAccess<string,mixed>
 */
interface ConfigInterface extends
    ArrayAccess,
    JsonSerializable,
    IteratorAggregate,
    Countable
{
    /**
     * Check if the given key exists in config.
     * Supports dot notation for nested keys.
     *
     * @param string $key The key to check (dot notation supported).
     *
     * @return bool True if exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Get the value of given config key.
     * Supports dot notation for nested keys.
     *
     * @param string $key Config key (dot notation supported).
     * @param mixed $default Default value as fallback.
     *
     * @return mixed The value of key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Get all config values.
     *
     * @return array<string,mixed>
     */
    public function getAll(): array;

    /**
     * Set the value of given config key.
     * Supports dot notation for nested keys.
     *
     * @param string $key The config key to update (dot notation supported).
     * @param mixed $value The updated config value.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Unset the given config key.
     * Supports dot notation for nested keys.
     *
     * @param string $key The config key to unset (dot notation supported).
     */
    public function unset(string $key): void;

    /**
     * Updates the whole config array.
     *
     * @param array<string,mixed> $config Settings Configuration.
     */
    public function setAll(array $config): void;

    /**
     * Merge the config recursively.
     *
     * @param array<string,mixed> $partial Settings Configuration.
     */
    public function deepMerge(array $partial): void;
}
