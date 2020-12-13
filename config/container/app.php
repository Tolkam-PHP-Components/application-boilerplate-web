<?php declare(strict_types=1);

use Tolkam\Application\ApplicationInterface;
use Tolkam\Configuration\Aggregator;
use Tolkam\Configuration\Configuration;
use Tolkam\Configuration\ConfigurationInterface;
use Tolkam\Configuration\Provider\ArrayProvider;
use Tolkam\Configuration\Provider\FileProvider;
use Tolkam\Utils\IP;

return [
    
    // config
    ConfigurationInterface::class => function (ApplicationInterface $app) {
        return new Configuration(new Aggregator(
            new FileProvider($app->getDirectory('config') . 'configuration.php'),
            new ArrayProvider([
                'debug' => $app->getEnvironment() === $app::ENV_DEVELOPMENT,
                'runtime' => [
                    'isLocal' => IP::checkIp($_SERVER['REMOTE_ADDR'] ?? '', '192.168.0.0/16'),
                ],
            ]),
        ));
    },
];
