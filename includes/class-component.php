<?php

/**
 * Base class for plugin components.
 *
 * Stores the plugin name and version so subclasses can build versioned
 * asset handles and option keys. Previously provided by the bundled
 * Power Plugins library (pp-core.php); reimplemented here so the plugin
 * is self-contained.
 *
 * @package Fancy_Product_Page
 */

namespace Fancy_Product_Page;

defined('WPINC') || die();

class Component
{
    protected $name;
    protected $version;

    public function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_version(): string
    {
        return $this->version;
    }
}
