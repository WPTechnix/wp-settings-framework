<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use WPTechnix\WPSettings\Interfaces\ConfigInterface;
use Traversable;
use ArrayIterator;

/**
 * Settings Builder Configuration class.
 *
 * Provides a configuration container that implements the
 * ConfigInterface and supports array-style access, iteration,
 * counting, and JSON serialization.
 *
 * This version includes support for dot notation to access nested data.
 *
 */
class Config implements ConfigInterface
{
    /**
     * The configuration data.
     *
     * @var array<string,mixed>
     */
    protected array $config;

    /**
     * Config constructor.
     *
     * @param array<string,mixed>|ConfigInterface $config Initial configuration data.
     */
    public function __construct(array|ConfigInterface $config = [])
    {
        $this->config = $config instanceof ConfigInterface ?
            $config->getAll() :
            $config;
    }

    /**
     * {@inheritDoc}
     *
     * Checks if a key exists using dot notation.
     * e.g., 'database.host'
     */
    public function has(string $key): bool
    {
        // If the key exists at the top level, return true immediately.
        if (array_key_exists($key, $this->config)) {
            return true;
        }

        // If no dot is present, it's a simple check.
        if (! str_contains($key, '.')) {
            return false;
        }

        $current = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return false;
            }
            $current = $current[$segment];
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // If the key exists at the top level, return it immediately.
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        // If no dot is present, we know it doesn't exist, return default.
        if (! str_contains($key, '.')) {
            return $default;
        }

        $current = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->config[$key] = $value;

            return;
        }

        $keys    = explode('.', $key);
        $current = &$this->config;

        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current[array_shift($keys)] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function unset(string $key): void
    {
        if (! str_contains($key, '.')) {
            unset($this->config[$key]);

            return;
        }

        $keys    = explode('.', $key);
        $current = &$this->config;

        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                // The path doesn't exist, so there's nothing to unset.
                return;
            }
            $current = &$current[$segment];
        }

        unset($current[array_shift($keys)]);
    }

    /**
     * {@inheritDoc}
     */
    public function setAll(array $config): void
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function deepMerge(array $partial): void
    {
        $this->config = array_replace_recursive($this->config, $partial);
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string)$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set((string)$offset, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->unset((string)$offset);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->config);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->config);
    }
}
