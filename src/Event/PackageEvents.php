<?php

/*
 * This file is part of the Puli PackageManager package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\PackageManager\Event;

/**
 * Contains the events triggered by the package manager.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PackageEvents
{
    /**
     * Dispatched when a package JSON file was loaded.
     */
    const PACKAGE_JSON_LOADED = 'package-json-loaded';

    /**
     * Dispatched when package JSON data was generated.
     */
    const PACKAGE_JSON_GENERATED = 'package-json-generated';

    private final function __construct()
    {
    }
}