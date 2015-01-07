<?php

/*
 * This file is part of the puli/repository-manager package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\RepositoryManager\Tests\Config;

use PHPUnit_Framework_TestCase;
use Puli\RepositoryManager\Config\Config;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testGetRaw()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'puli-dir');

        $this->assertSame('puli-dir', $config->getRaw(Config::PULI_DIR));
    }

    public function testGetRawReturnsNullIfNotSet()
    {
        $config = new Config();

        $this->assertNull($config->getRaw(Config::PULI_DIR));
    }

    public function testGetRawWithCustomDefault()
    {
        $config = new Config();

        $this->assertSame('my-default', $config->getRaw(Config::PULI_DIR, 'my-default'));
    }

    public function testGetRawReturnsFallbackIfSet()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $config = new Config($baseConfig);

        $this->assertSame('my-puli-dir', $config->getRaw(Config::PULI_DIR));
    }

    public function testGetRawPassesCustomDefaultToFallbackConfig()
    {
        $baseConfig = new Config();
        $config = new Config($baseConfig);

        $this->assertSame('my-default', $config->getRaw(Config::PULI_DIR, 'my-default'));
    }

    public function testGetRawDoesNotReturnFallbackIfDisabled()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $config = new Config($baseConfig);

        $this->assertNull($config->getRaw(Config::PULI_DIR, null, false));
    }

    public function testGetRawDoesNotReplacePlaceholder()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');
        $config->set(Config::FACTORY_FILE, '{$puli-dir}/ServiceRegistry.php');

        $this->assertSame('{$puli-dir}/ServiceRegistry.php', $config->getRaw(Config::FACTORY_FILE));
    }

    /**
     * @expectedException \Puli\RepositoryManager\Config\NoSuchConfigKeyException
     * @expectedExceptionMessage foo
     */
    public function testGetRawFailsIfInvalidKey()
    {
        $config = new Config();
        $config->getRaw('foo');
    }

    public function testGetRawCompositeKey()
    {
        $config = new Config();
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY_PATH, 'my-path');
        $config->set(Config::REPOSITORY_STORE_TYPE, 'my-store-type');

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
            'store' => array(
                'type' => 'my-store-type',
            ),
        ), $config->getRaw(Config::REPOSITORY));
    }

    public function testGetRawCompositeKeyReturnsArrayIfNotSet()
    {
        $config = new Config();

        $this->assertSame(array(), $config->getRaw(Config::REPOSITORY));
    }

    public function testGetRawCompositeKeyWithCustomDefault()
    {
        $default = array('type' => 'my-type');

        $config = new Config();
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
        ), $config->getRaw(Config::REPOSITORY, $default));
    }

    public function testGetRawCompositeKeyIncludesFallbackKeys()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::REPOSITORY_TYPE, 'fallback-type');
        $baseConfig->set(Config::REPOSITORY_PATH, 'fallback-path');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'type' => 'fallback-type',
            'path' => 'my-path',
        ), $config->getRaw(Config::REPOSITORY));
    }

    public function testGetRawCompositeKeyIncludesFallbackKeysOfMultipleLevels()
    {
        $baseBaseConfig = new Config();
        $baseBaseConfig->set(Config::REPOSITORY_STORE_TYPE, 'my-store');

        $baseConfig = new Config($baseBaseConfig);
        $baseConfig->set(Config::REPOSITORY_TYPE, 'fallback-type');
        $baseConfig->set(Config::REPOSITORY_PATH, 'fallback-path');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'store' => array(
                'type' => 'my-store',
            ),
            'type' => 'fallback-type',
            'path' => 'my-path',
        ), $config->getRaw(Config::REPOSITORY));
    }

    public function testGetRawCompositeKeyPassesDefaultToFallback()
    {
        $default = array('type' => 'my-type');

        $baseConfig = new Config();
        $baseConfig->set(Config::REPOSITORY_PATH, 'my-path');
        $config = new Config($baseConfig);

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
        ), $config->getRaw(Config::REPOSITORY, $default));
    }

    public function testGetRawCompositeKeyDoesNotIncludeFallbackKeysIfDisabled()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::REPOSITORY_TYPE, 'fallback-type');
        $baseConfig->set(Config::REPOSITORY_PATH, 'fallback-path');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'path' => 'my-path',
        ), $config->getRaw(Config::REPOSITORY, null, false));
    }

    public function testGetRawCompositeKeyDoesNotReplacePlaceholders()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'puli-dir');
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            'path' => '{$puli-dir}/my-path',
        ), $config->getRaw(Config::REPOSITORY));
    }

    public function testGet()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');

        $this->assertSame('my-puli-dir', $config->get(Config::PULI_DIR));
    }

    public function testGetReturnsNullIfNotSet()
    {
        $config = new Config();

        $this->assertNull($config->get(Config::PULI_DIR));
    }

    public function testGetWithFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'fallback');
        $config = new Config($baseConfig);
        $config->set(Config::PULI_DIR, 'my-puli-dir');

        $this->assertSame('my-puli-dir', $config->get(Config::PULI_DIR));
    }

    public function testGetReturnsFallbackIfSet()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $config = new Config($baseConfig);

        $this->assertSame('my-puli-dir', $config->get(Config::PULI_DIR));
    }

    public function testGetDoesNotReturnFallbackIfDisabled()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $config = new Config($baseConfig);

        $this->assertNull($config->get(Config::PULI_DIR, null, false));
    }

    public function testGetWithCustomDefaultValue()
    {
        $config = new Config();

        $this->assertSame('my-default', $config->get(Config::PULI_DIR, 'my-default'));
    }

    public function testGetReplacesPlaceholder()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');
        $config->set(Config::FACTORY_FILE, '{$puli-dir}/ServiceRegistry.php');

        $this->assertSame('my-puli-dir/ServiceRegistry.php', $config->get(Config::FACTORY_FILE));
    }

    public function testGetReplacesPlaceholderDefinedInDefaultConfig()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::FACTORY_FILE, '{$puli-dir}/ServiceRegistry.php');
        $config = new Config($baseConfig);
        $config->set(Config::PULI_DIR, 'my-puli-dir');

        $this->assertSame('my-puli-dir/ServiceRegistry.php', $config->get(Config::FACTORY_FILE));
    }

    public function testGetReplacesPlaceholderSetInDefaultConfig()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $config = new Config($baseConfig);
        $config->set(Config::FACTORY_FILE, '{$puli-dir}/ServiceRegistry.php');

        $this->assertSame('my-puli-dir/ServiceRegistry.php', $config->get(Config::FACTORY_FILE));
    }

    public function testGetDoesNotUseFallbackPlaceholderIfFallbackDisabled()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $config = new Config($baseConfig);
        $config->set(Config::FACTORY_FILE, '{$puli-dir}/ServiceRegistry.php');

        $this->assertSame('/ServiceRegistry.php', $config->get(Config::FACTORY_FILE, null, false));
    }

    /**
     * @expectedException \Puli\RepositoryManager\Config\NoSuchConfigKeyException
     * @expectedExceptionMessage foo
     */
    public function testGetFailsIfInvalidKey()
    {
        $config = new Config();
        $config->get('foo');
    }

    public function testGetCompositeKey()
    {
        $config = new Config();
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY_PATH, 'my-path');
        $config->set(Config::REPOSITORY_STORE_TYPE, 'my-store-type');

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
            'store' => array(
                'type' => 'my-store-type',
            ),
        ), $config->get(Config::REPOSITORY));
    }

    public function testGetCompositeKeyReturnsArrayIfNotSet()
    {
        $config = new Config();

        $this->assertSame(array(), $config->get(Config::REPOSITORY));
    }

    public function testGetCompositeKeyWithCustomDefault()
    {
        $default = array('type' => 'my-type');

        $config = new Config();
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
        ), $config->get(Config::REPOSITORY, $default));
    }

    public function testGetCompositeKeyIncludesFallbackKeys()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
        ), $config->get(Config::REPOSITORY));
    }

    public function testGetCompositeKeyDoesNotIncludeFallbackKeysIfDisabled()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, 'my-path');

        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
        ), $config->get(Config::REPOSITORY));
    }

    public function testGetCompositeKeyReplacesPlaceholders()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'puli-dir');
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            'path' => 'puli-dir/my-path',
        ), $config->get(Config::REPOSITORY));
    }

    public function testGetCompositeKeyUsesFallbackPlaceholders()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'puli-dir');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            'path' => 'puli-dir/my-path',
        ), $config->get(Config::REPOSITORY));
    }

    public function testGetCompositeKeyDoesNotUseFallbackPlaceholdersIfDisabled()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'puli-dir');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            'path' => '/my-path',
        ), $config->get(Config::REPOSITORY, null, false));
    }

    public function testSetCompositeKey()
    {
        $config = new Config();
        $config->set(Config::REPOSITORY, array(
            'type' => 'my-type',
            'path' => 'my-path',
            'store' => array(
                'type' => 'my-store-type',
            ),
        ));

        $this->assertSame('my-type', $config->get(Config::REPOSITORY_TYPE));
        $this->assertSame('my-path', $config->get(Config::REPOSITORY_PATH));
        $this->assertSame('my-store-type', $config->get(Config::REPOSITORY_STORE_TYPE));
        $this->assertSame(array(
            'type' => 'my-type',
            'path' => 'my-path',
            'store' => array(
                'type' => 'my-store-type',
            ),
        ), $config->get(Config::REPOSITORY));
    }

    public function testSetCompositeKeyRemovesPreviouslySetKeys()
    {
        $config = new Config();
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY, array(
            'path' => 'my-path',
        ));

        $this->assertSame(array(
            'path' => 'my-path',
        ), $config->get(Config::REPOSITORY));
    }

    /**
     * @expectedException \Puli\RepositoryManager\Config\NoSuchConfigKeyException
     * @expectedExceptionMessage foo
     */
    public function testSetFailsIfInvalidKey()
    {
        $config = new Config();
        $config->set('foo', 'bar');
    }

    /**
     * @dataProvider getNotNullKeys
     * @expectedException \Puli\RepositoryManager\InvalidConfigException
     */
    public function testSetFailsIfValueIsNull($key)
    {
        $config = new Config();
        $config->set($key, null);
    }

    /**
     * @dataProvider getStringKeys
     */
    public function testStringKeys($key)
    {
        $config = new Config();
        $config->set($key, 'string');

        $this->assertSame('string', $config->get($key));
    }

    /**
     * @dataProvider getStringKeys
     * @expectedException \Puli\RepositoryManager\InvalidConfigException
     */
    public function testSetFailsIfValueIsNotString($key)
    {
        $config = new Config();
        $config->set($key, 12345);
    }

    /**
     * @dataProvider getNonEmptyKeys
     * @expectedException \Puli\RepositoryManager\InvalidConfigException
     */
    public function testSetFailsIfValueIsEmptyString($key)
    {
        $config = new Config();
        $config->set($key, '');
    }

    /**
     * @dataProvider getBooleanKeys
     */
    public function testBooleanValues($key)
    {
        $config = new Config();
        $config->set($key, true);

        $this->assertTrue($config->get($key));
    }

    /**
     * @dataProvider getBooleanKeys
     * @expectedException \Puli\RepositoryManager\InvalidConfigException
     */
    public function testSetFailsIfValueIsNotBoolean($key)
    {
        $config = new Config();
        $config->set($key, 'true');
    }

    public function testMerge()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'puli-dir');
        $config->set(Config::FACTORY_CLASS, 'Puli\ServiceRegistry');
        $config->merge(array(
            Config::FACTORY_CLASS => 'My\ServiceRegistry',
            Config::FACTORY_FILE => 'repo-file.php',
        ));

        $this->assertSame('puli-dir', $config->get(Config::PULI_DIR));
        $this->assertSame('My\ServiceRegistry', $config->get(Config::FACTORY_CLASS));
        $this->assertSame('repo-file.php', $config->get(Config::FACTORY_FILE));
    }

    public function testRemove()
    {
        $config = new Config();
        $config->set(Config::FACTORY_FILE, 'ServiceRegistry.php');
        $config->set(Config::FACTORY_CLASS, 'Puli\ServiceRegistry');
        $config->remove(Config::FACTORY_CLASS);

        $this->assertSame('ServiceRegistry.php', $config->get(Config::FACTORY_FILE));
        $this->assertNull($config->get(Config::FACTORY_CLASS));
    }

    public function testRemoveCompositeKey()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'puli-dir');
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->remove(Config::REPOSITORY);

        $this->assertSame('puli-dir', $config->get(Config::PULI_DIR));
        $this->assertSame(array(), $config->get(Config::REPOSITORY));
        $this->assertNull($config->get(Config::REPOSITORY_TYPE));
    }

    /**
     * @expectedException \Puli\RepositoryManager\Config\NoSuchConfigKeyException
     * @expectedExceptionMessage foo
     */
    public function testRemoveFailsIfInvalidKey()
    {
        $config = new Config();
        $config->remove('foo');
    }

    public function testGetReturnsFallbackAfterRemove()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::FACTORY_CLASS, 'Fallback\ServiceRegistry');
        $config = new Config($baseConfig);
        $config->set(Config::FACTORY_FILE, 'ServiceRegistry.php');
        $config->set(Config::FACTORY_CLASS, 'Puli\ServiceRegistry');
        $config->remove(Config::FACTORY_CLASS);

        $this->assertSame('ServiceRegistry.php', $config->get(Config::FACTORY_FILE));
        $this->assertSame('Fallback\ServiceRegistry', $config->get(Config::FACTORY_CLASS));
    }

    public function testToRawArray()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY => array(
                'type' => 'my-type',
                'path' => '{$puli-dir}/my-path',
            )
        ), $config->toRawArray());
    }

    public function testToRawArrayWithFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY => array(
                'type' => 'my-type',
                'path' => '{$puli-dir}/my-path',
            )
        ), $config->toRawArray());
    }

    public function testToRawArrayWithoutFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::REPOSITORY => array(
                'path' => '{$puli-dir}/my-path',
            )
        ), $config->toRawArray(false));
    }

    public function testToFlatRawArray()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY_TYPE => 'my-type',
            Config::REPOSITORY_PATH => '{$puli-dir}/my-path',
        ), $config->toFlatRawArray());
    }

    public function testToFlatRawArrayWithFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');
        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY_TYPE => 'my-type',
            Config::REPOSITORY_PATH => '{$puli-dir}/my-path',
        ), $config->toFlatRawArray());
    }

    public function testToFlatRawArrayWithoutFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');
        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::REPOSITORY_PATH => '{$puli-dir}/my-path',
        ), $config->toFlatRawArray(false));
    }

    public function testToArray()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY => array(
                'type' => 'my-type',
                'path' => 'my-puli-dir/my-path',
            )
        ), $config->toArray());
    }

    public function testToArrayWithFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY => array(
                'type' => 'my-type',
                'path' => 'my-puli-dir/my-path',
            )
        ), $config->toArray());
    }

    public function testToArrayWithoutFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');

        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::REPOSITORY => array(
                'path' => '/my-path',
            )
        ), $config->toArray(false));
    }

    public function testToFlatArray()
    {
        $config = new Config();
        $config->set(Config::PULI_DIR, 'my-puli-dir');
        $config->set(Config::REPOSITORY_TYPE, 'my-type');
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY_TYPE => 'my-type',
            Config::REPOSITORY_PATH => 'my-puli-dir/my-path',
        ), $config->toFlatArray());
    }

    public function testToFlatArrayWithFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');
        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::REPOSITORY_TYPE => 'my-type',
            Config::REPOSITORY_PATH => 'my-puli-dir/my-path',
        ), $config->toFlatArray());
    }

    public function testToFlatArrayWithoutFallback()
    {
        $baseConfig = new Config();
        $baseConfig->set(Config::PULI_DIR, 'my-puli-dir');
        $baseConfig->set(Config::REPOSITORY_TYPE, 'my-type');
        $config = new Config($baseConfig);
        $config->set(Config::REPOSITORY_PATH, '{$puli-dir}/my-path');

        $this->assertSame(array(
            Config::REPOSITORY_PATH => '/my-path',
        ), $config->toFlatArray(false));
    }

    public function getNotNullKeys()
    {
        return array(
            array(Config::PULI_DIR),
            array(Config::FACTORY_CLASS),
            array(Config::FACTORY_FILE),
        );
    }

    public function getNonEmptyKeys()
    {
        return array(
            array(Config::PULI_DIR),
            array(Config::FACTORY_CLASS),
            array(Config::FACTORY_FILE),
        );
    }

    public function getStringKeys()
    {
        return array(
            array(Config::PULI_DIR),
            array(Config::FACTORY_CLASS),
            array(Config::FACTORY_FILE),
        );
    }

    public function getBooleanKeys()
    {
        return array(
            array(Config::FACTORY_AUTO_GENERATE),
            array(Config::REPOSITORY_SYMLINK),
            array(Config::REPOSITORY_STORE_GZIP),
            array(Config::REPOSITORY_STORE_CACHE),
            array(Config::DISCOVERY_STORE_GZIP),
            array(Config::DISCOVERY_STORE_CACHE)
        );
    }
}
