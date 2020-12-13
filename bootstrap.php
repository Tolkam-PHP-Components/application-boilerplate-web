<?php /** @noinspection PhpIncludeInspection */
declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tolkam\Application\ApplicationInterface;
use Tolkam\Application\Cli\CliApplication;
use Tolkam\Application\Http\HttpApplication;
use Tolkam\ThrowableHandler\ThrowableHandler;
use function DI\create;

// composer autoloader
require __DIR__ . '/vendor/autoload.php';

// setup php environment
error_reporting(E_ALL);

date_default_timezone_set('UTC');

ini_set('precision', '14');
ini_set('serialize_precision', '14');

// setup app environment
$isCli = PHP_SAPI === 'cli';
Dotenv::createImmutable(__DIR__)->load();

// mock some server values in CLI
if ($isCli) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['HTTP_HOST'] ??= getenv('APP_HOST') ?: '';
}

$environment = getenv('APP_ENV') ?: ApplicationInterface::ENV_PRODUCTION;
$isDevelopment = $environment === ApplicationInterface::ENV_DEVELOPMENT;

// handle uncaught errors
$throwableHandler = new ThrowableHandler;
$throwableHandler->catchAll();
if ($isDevelopment) {
    $throwableHandler->exposeErrors();
}

// create app
$app = !$isCli
    ? new HttpApplication
    : new CliApplication;

$app->setEnvironment($environment);

// register and create directories
$app->registerDirectories([
    'root' => __DIR__,
    
    'var' => '@root/var/' . $app->getEnvironment(),
    'cache' => '@var/cache',
    
    'config' => '@root/config',
    
    'resources' => '@root/resources',
    
    'bin' => '@root/bin',
    'public' => '@root/public',
]);

$app->createDirectories(['config', 'cache', 'log']);

// create di container
$containerBuilder = new ContainerBuilder;
if (!$isDevelopment) {
    $containerBuilder->enableCompilation($app->getDirectory('cache'));
}

// add definitions
$definitions = $app->getDirectory('config') . 'container.php';
$definitionsEnv = $app->getDirectory('config') . 'container-' . $app->getEnvironment() . '.php';

if (file_exists($definitions)) {
    // use closure to not leak definition files variables
    (function () use ($definitions, $containerBuilder) {
        foreach (require_once($definitions) as $definition) {
            $containerBuilder->addDefinitions($definition);
        }
    })();
}
if (file_exists($definitionsEnv)) {
    (function () use ($definitionsEnv, $containerBuilder) {
        foreach (require_once($definitionsEnv) as $definition) {
            $containerBuilder->addDefinitions($definition);
        }
    })();
}

// register defaults and the app itself
$containerBuilder->addDefinitions([
    StreamFactoryInterface::class => create(StreamFactory::class),
    RequestFactoryInterface::class => create(RequestFactory::class),
    ResponseFactoryInterface::class => create(ResponseFactory::class),
    ServerRequestInterface::class => function () {
        return ServerRequestFactory::fromGlobals();
    },
    ApplicationInterface::class => $app,
]);
