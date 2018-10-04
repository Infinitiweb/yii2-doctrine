<?php

namespace Infinitiweb\YiiDoctrine;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\{
    ApcCache as DoctrineApcCache,
    ApcuCache as DoctrineApcuCache,
    ArrayCache as DoctrineArrayCache,
    CacheProvider as DoctrineCacheProvider,
    FilesystemCache as DoctrineFilesystemCache,
    MemcacheCache as DoctrineMemcacheCache,
    MemcachedCache as DoctrineMemcachedCache
};
use Doctrine\DBAL\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use yii\base\Component as BaseComponent;
use yii\base\InvalidConfigException;
use yii\caching\{
    ApcCache as YiiApcCache,
    FileCache as YiiFileCache,
    MemCache as YiiMemCache
};

/**
 * Class DoctrineComponent
 * @package Infinitiweb\YiiDoctrine\components
 *
 * @property string $driver
 * @property string $hostname
 * @property string $username
 * @property string $password
 * @property string $database
 * @property-write string $isDevMode
 * @property array $entityPath
 * @property string $proxyPath
 * @property-read EntityManagerInterface $entityManager
 * @property-read Connection $connection
 */
class Component extends BaseComponent
{
    /** @var array */
    private $dbal = [];
    /** @var bool */
    private $isDevMode;
    /** @var array */
    private $entityPath;
    /** @var string */
    private $proxyPath;
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var null|string */
    private $namespace = 'app\migrations';
    /** @var string */
    private $tableName = 'migrations';
    /** @var string */
    private $columnName = 'version';
    /** @var string */
    private $migrationsPath = '@app/migrations';
    /** @var array */
    private $migrations = [];
    /** @var string */
    private $customTemplate;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        AnnotationRegistry::registerAutoloadNamespace('Doctrine\ORM\Mapping', \Yii::getAlias('@vendor'));

        $config = $this->createConfig();
        $annotationDriver = $config->newDefaultAnnotationDriver($this->getEntityPath(), false);
        $config->setMetadataDriverImpl($annotationDriver);

        $this->entityManager = EntityManager::create($this->getDbal(), $config);
    }

    public function setDbal(array $dbal): void
    {
        $this->dbal = $dbal;
    }

    /**
     * @return array
     */
    public function getDbal(): array
    {
        return $this->dbal;
    }

    /**
     * @param bool $isDevMode
     */
    public function setIsDevMode(bool $isDevMode): void
    {
        $this->isDevMode = $isDevMode;
    }

    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return $this->isDevMode ?? YII_ENV_DEV;
    }

    /**
     * @param array|string $path
     */
    public function setEntityPath($path): void
    {
        $this->entityPath = array_map([\Yii::class, 'getAlias'], (array) $path);
    }

    /**
     * @return array
     */
    public function getEntityPath(): array
    {
        return $this->entityPath ?? [\Yii::getAlias('@app/entity')];
    }

    /**
     * @param string $path
     */
    public function setProxyPath(string $path): void
    {
        $this->proxyPath = \Yii::getAlias($path);
    }

    /**
     * @return string
     */
    public function getProxyPath(): string
    {
        return $this->proxyPath ?? \Yii::getAlias('@runtime/doctrine-proxies');
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setColumnName(string $columnName): void
    {
        $this->columnName = $columnName;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @param string $path
     */
    public function setMigrationsPath(string $path): void
    {
        $this->migrationsPath = $path;
    }

    /**
     * @return string
     */
    public function getMigrationsPath(): string
    {
        return \Yii::getAlias($this->migrationsPath);
    }

    /**
     * @param array $migrations
     */
    public function setMigrations(array $migrations): void
    {
        $this->migrations = $migrations;
    }

    /**
     * @return array
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }

    /**
     * @param string $template
     */
    public function setCustomTemplate(string $template): void
    {
        $this->customTemplate = $template;
    }

    /**
     * @return string
     */
    public function getCustomTemplate(): ?string
    {
        return $this->customTemplate;
    }

    /**
     * @return MigrationsConfiguration
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function getMigrationsConfiguration(): MigrationsConfiguration
    {
        $configuration = new MigrationsConfiguration($this->getConnection());

        $configuration->setMigrationsNamespace($this->getNamespace());
        $configuration->setCustomTemplate($this->getCustomTemplate());
        $configuration->setMigrationsTableName($this->getTableName());
        $configuration->setMigrationsColumnName($this->getColumnName());
        $configuration->setMigrationsDirectory($this->getMigrationsPath());

        $configuration->setMigrationsAreOrganizedByYearAndMonth();

        return $configuration;
    }

    /**
     * @return Configuration
     * @throws \yii\base\InvalidConfigException
     */
    private function createConfig(): Configuration
    {
        $cache = $this->createDoctrineCache();
        $config = new Configuration();

        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setResultCacheImpl($cache);
        $config->setProxyDir($this->getProxyPath());
        $config->setProxyNamespace('DoctrineProxies');
        $config->setAutoGenerateProxyClasses($this->isDev());

        return $config;
    }

    /**
     * @return DoctrineCacheProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function createDoctrineCache(): DoctrineCacheProvider
    {
        $yiiCacheDriver = \Yii::$app->getCache();

        if ($yiiCacheDriver instanceof YiiApcCache) {
            $cache = $yiiCacheDriver->useApcu
                ? new DoctrineApcuCache()
                : new DoctrineApcCache();
        } else if ($yiiCacheDriver instanceof YiiFileCache) {
            $cache = new DoctrineFilesystemCache($yiiCacheDriver->cachePath);
        } else if ($yiiCacheDriver instanceof YiiMemCache) {
            $memcacheDriver = $yiiCacheDriver->getMemcache();

            if ($memcacheDriver instanceof \Memcache) {
                $cache = new DoctrineMemcacheCache();
                $cache->setMemcache($memcacheDriver);
            } else if ($memcacheDriver instanceof \Memcached) {
                $cache = new DoctrineMemcachedCache();
                $cache->setMemcached($memcacheDriver);
            }
        } else {
            $cache = new DoctrineArrayCache();
        }

        $cache->setNamespace(sprintf('Doctrine_%s', md5($this->getProxyPath())));

        return $cache;
    }
}