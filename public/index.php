<?php declare(strict_types=1);

use DI\ContainerBuilder;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tolkam\Application\Http\HttpApplication;
use function DI\create;

/**
 * Http App
 */
/* @var ContainerBuilder $containerBuilder */
/* @var HttpApplication $app */
require_once dirname(__DIR__) . '/bootstrap.php';

// register defaults
$containerBuilder->addDefinitions([
    StreamFactoryInterface::class => create(StreamFactory::class),
    RequestFactoryInterface::class => create(RequestFactory::class),
    ResponseFactoryInterface::class => create(ResponseFactory::class),
]);

// build container
$container = $containerBuilder->build();

if ($container->has('http.middlewares')) {
    $app->addMiddlewares($container->get('http.middlewares'));
}

if ($container->has('http.emitters')) {
    $app->addEmitters($container->get('http.emitters'));
}

// handle request
$app->run($container->get(ServerRequestInterface::class));
