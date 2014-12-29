<?php

/*
 * This file is part of the puli/repository-manager package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\RepositoryManager\Package\PackageFile\Reader;

use Puli\RepositoryManager\Binding\BindingParameterDescriptor;
use Puli\RepositoryManager\Config\Config;
use Puli\RepositoryManager\FileNotFoundException;
use Puli\RepositoryManager\InvalidConfigException;
use Puli\RepositoryManager\Package\InstallInfo;
use Puli\RepositoryManager\Package\PackageFile\PackageFile;
use Puli\RepositoryManager\Package\PackageFile\RootPackageFile;
use Puli\RepositoryManager\Package\ResourceMapping;
use Puli\RepositoryManager\Binding\BindingTypeDescriptor;
use Puli\RepositoryManager\Binding\BindingDescriptor;
use Webmozart\Json\DecodingFailedException;
use Webmozart\Json\JsonDecoder;
use Webmozart\Json\ValidationFailedException;

/**
 * Reads JSON package files.
 *
 * The data in the JSON file is validated against the schema
 * `res/schema/package-schema.json`.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PackageJsonReader implements PackageFileReader
{
    /**
     * Reads a JSON package file.
     *
     * The data in the JSON file is validated against the schema
     * `res/schema/package-schema.json`.
     *
     * @param string $path The path to the JSON file.
     *
     * @return PackageFile The package file.
     *
     * @throws FileNotFoundException If the JSON file was not found.
     * @throws InvalidConfigException If the JSON file is invalid.
     */
    public function readPackageFile($path)
    {
        $packageFile = new PackageFile(null, $path);

        $jsonData = $this->decodeFile($path);

        $this->populateConfig($packageFile, $jsonData);

        return $packageFile;
    }

    /**
     * Reads a JSON root package file.
     *
     * The data in the JSON file is validated against the schema
     * `res/schema/package-schema.json`.
     *
     * @param string $path       The path to the JSON file.
     * @param Config $baseConfig The configuration that the package will inherit
     *                           its configuration values from.
     *
     * @return RootPackageFile The package file.
     *
     * @throws FileNotFoundException If the JSON file was not found.
     * @throws InvalidConfigException If the JSON file is invalid.
     */
    public function readRootPackageFile($path, Config $baseConfig = null)
    {
        $packageFile = new RootPackageFile(null, $path, $baseConfig);

        $jsonData = $this->decodeFile($path);

        $this->populateConfig($packageFile, $jsonData);
        $this->populateRootConfig($packageFile, $jsonData);

        return $packageFile;
    }

    private function populateConfig(PackageFile $packageFile, \stdClass $jsonData)
    {
        if (isset($jsonData->name)) {
            $packageFile->setPackageName($jsonData->name);
        }

        if (isset($jsonData->resources)) {
            foreach ($jsonData->resources as $path => $relativePaths) {
                $packageFile->addResourceMapping(new ResourceMapping($path, (array) $relativePaths));
            }
        }

        if (isset($jsonData->bindings)) {
            foreach ($jsonData->bindings as $bindingData) {
                $packageFile->addBindingDescriptor(new BindingDescriptor(
                    $bindingData->selector,
                    $bindingData->type,
                    isset($bindingData->parameters) ? (array) $bindingData->parameters : array()
                ));
            }
        }

        if (isset($jsonData->{'binding-types'})) {
            foreach ((array) $jsonData->{'binding-types'} as $typeName => $data) {
                $parameters = array();

                if (isset($data->parameters)) {
                    foreach ((array) $data->parameters as $paramName => $paramData) {
                        $parameters[] = new BindingParameterDescriptor(
                            $paramName,
                            isset($paramData->required) ? $paramData->required : false,
                            isset($paramData->default) ? $paramData->default : null,
                            isset($paramData->description) ? $paramData->description : null
                        );
                    }
                }

                $packageFile->addTypeDescriptor(new BindingTypeDescriptor(
                    $typeName,
                    isset($data->description) ? $data->description : null,
                    $parameters
                ));
            }
        }

        if (isset($jsonData->override)) {
            $packageFile->setOverriddenPackages((array) $jsonData->override);
        }
    }

    private function populateRootConfig(RootPackageFile $packageFile, \stdClass $jsonData)
    {
        if (isset($jsonData->{'package-order'})) {
            $packageFile->setPackageOrder((array) $jsonData->{'package-order'});
        }

        if (isset($jsonData->plugins)) {
            $packageFile->setPluginClasses($jsonData->plugins);
        }

        if (isset($jsonData->config)) {
            $config = $packageFile->getConfig();

            foreach ($this->objectsToArrays($jsonData->config) as $key => $value) {
                $config->set($key, $value);
            }
        }

        if (isset($jsonData->packages)) {
            foreach ($jsonData->packages as $packageName => $packageData) {
                $installInfo = new InstallInfo($packageName, $packageData->{'install-path'});

                if (isset($packageData->installer)) {
                    $installInfo->setInstaller($packageData->installer);
                }

                $packageFile->addInstallInfo($installInfo);
            }
        }
    }

    private function decodeFile($path)
    {
        $decoder = new JsonDecoder();
        $schema = realpath(__DIR__.'/../../../../res/schema/package-schema.json');

        if (!file_exists($path)) {
            throw new FileNotFoundException(sprintf(
                'The file %s does not exist.',
                $path
            ));
        }

        try {
            $jsonData = $decoder->decodeFile($path, $schema);
        } catch (DecodingFailedException $e) {
            throw new InvalidConfigException(sprintf(
                "The configuration in %s could not be decoded:\n%s",
                $path,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (ValidationFailedException $e) {
            throw new InvalidConfigException(sprintf(
                "The configuration in %s is invalid:\n%s",
                $path,
                $e->getErrorsAsString()
            ), $e->getCode(), $e);
        }

        return $jsonData;
    }

    private function objectsToArrays($data)
    {
        $data = (array) $data;

        foreach ($data as $key => $value) {
            $data[$key] = is_object($value) ? $this->objectsToArrays($value) : $value;
        }

        return $data;
    }
}