# yii2-doctrine
Yii 2 component wrapper to communicate with Doctrine 2.

## Installation
You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

    composer require infinitiweb/yii2-doctrine
    
## Usage ##
For connecting doctrine components insert in you **config** file
 ```php
'components' => [
        'doctrine'  => [
             'class'    => Infinitiweb\YiiDoctrine\Component::class,
             'dbal' => [
                'url' => 'mysql://user:secret@localhost/mydb'
             ],
             'entityPath' => [
                 '@backend/entity',
                 '@frontend/entity',
                 '@console/entity',
                 '@common/entity',
             ]
         ]
]
 ```
 
**Here are details about what each configuration option does:**

|Name|Required|Default|Description|
|---|---|---|---|
|dbal|yes|[]|The DBAL configuration [See for more details](https://www.doctrine-project.org/projects/doctrine-dbal/en/2.8/reference/configuration.html).|
|isDevMode|no|YII_DEV_ENV|Hostname of the database to connect to.|
|entityPath|no|[\Yii::getAlias('@app/entity')]|Paths with you entity.|
|proxyPath|no|\Yii::getAlias('@runtime/doctrine-proxies')|Set directory where Doctrine generates any proxy classes. For a detailed explanation on proxy classes and how they are used in Doctrine, refer to the "Proxy Objects" section further down.| 
|namespace|no|app\migrations|The PHP namespace your migration classes are located under.|
|tableName|no|migrations|The name of the table to track executed migrations in.|
|columnName|no|versions|The name of the column which stores the version name.|
|migrationsPath|no|\Yii::getAlias('@app/migrations')|The path to a directory where to look for migration classes.|
|migrations|no|[]|[Manually specify the array of migration versions instead of finding migrations.](https://www.doctrine-project.org/projects/doctrine-migrations/en/latest/reference/configuration.html#manually-providing-migrations)|
|customTemplate|no|null|The path to a custom template file for migration|


For using doctrine console add to you **config** file 
```PHP
'controllerMap' => [
        ....
        'doctrine' => [
            'class' => Infinitiweb\YiiDoctrine\Controller::class,
        ]
    ]
]
```
and call **./yii doctrine**
