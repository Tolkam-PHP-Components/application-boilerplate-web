<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;

/**
 * Http App
 */
// common bootstrap
require_once dirname(__DIR__) . '/bootstrap.php';

// register defaults
// $containerBuilder->addDefinitions([
//     StreamFactoryInterface::class => create(StreamFactory::class),
//     RequestFactoryInterface::class => create(RequestFactory::class),
//     ResponseFactoryInterface::class => create(ResponseFactory::class),
// ]);

// build container
$container = $containerBuilder->build();

// use middlewares
if ($container->has('http.middlewares')) {
    $app->addMiddlewares($container->get('http.middlewares'));
}

// use emitters
if ($container->has('http.emitters')) {
    $app->addEmitters($container->get('http.emitters'));
}

// handle request
$app->run($container->get(ServerRequestInterface::class));
