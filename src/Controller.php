<?php

namespace Infinitiweb\YiiDoctrine;

use Doctrine\DBAL\Migrations\Tools\Console\ConsoleRunner as MigrationsConsole;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner as ORMConsole;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use yii\console\Controller as BaseController;
use yii\di\Instance;

/**
 * Class DoctrineController
 * @package Infinitiweb\YiiDoctrine\console
 */
class Controller extends BaseController
{
    /** @var string|Component */
    public $doctrine = 'doctrine';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->doctrine = Instance::ensure($this->doctrine, Component::class);
    }

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'doctrine'
        ]);
    }

    /**
     * @param array $args
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function actionIndex(array $args = [])
    {
        $application = new Application('Doctrine application');
        $application->setCatchExceptions(true);
        $application->setHelperSet($this->getHelperSet());


        ORMConsole::addCommands($application);
        MigrationsConsole::addCommands($application);

        $input = new ArrayInput($args);

        $application->run($input);
    }

    /**
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    private function getHelperSet(): HelperSet
    {
        $helperSet = new HelperSet();

        $helperSet->set(new ConfigurationHelper(
            $this->doctrine->getConnection(),
            $this->doctrine->getMigrationsConfiguration()
        ));
        $helperSet->set(new ConnectionHelper($this->doctrine->getConnection()));
        $helperSet->set(new EntityManagerHelper($this->doctrine->getEntityManager()));
        $helperSet->set($helperSet->get('entityManager'), 'em');
        $helperSet->set(new QuestionHelper());

        return $helperSet;
    }
}