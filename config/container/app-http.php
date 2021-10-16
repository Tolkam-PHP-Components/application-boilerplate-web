<?php /** @noinspection PhpIncludeInspection */
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Tolkam\Application\ApplicationInterface;
use Tolkam\Application\Http\Emitter\SapiEmitter;
use Tolkam\Routing\Resolver\ContainerResolver;
use Tolkam\Routing\Resolver\CallableResolver;
use Tolkam\Routing\RouterContainer;
use Tolkam\Routing\RoutingMiddleware;
use Tolkam\Routing\Runner\InvokableRunner;
use function DI\create;

return [
    'http.emitters' => [
        create(SapiEmitter::class),
    ],
    
    'http.middlewares' => [
        // routing
        function (ContainerInterface $container) {
            return (new RoutingMiddleware($container->get(RouterContainer::class)))
                ->addMiddlewareResolver(new CallableResolver)
                ->addMiddlewareResolver(new ContainerResolver($container))
                ->addHandlerResolver(new CallableResolver)
                ->addHandlerResolver(new ContainerResolver($container))
                ->addRunner(new InvokableRunner($container));
        },
    ],
    
    // router
    RouterContainer::class => function (
        ApplicationInterface $app,
        ContainerInterface $container
    ) {
        $routerContainer = new RouterContainer;
        $configurator = require_once $app->getDirectory('config') . 'routes.php';
        $configurator($routerContainer->getMap(), $container);
        
        $devRoutes = $app->getDirectory('config') . 'routes-development.php';
        if ($app->getEnvironment() === $app::ENV_DEVELOPMENT && is_file($devRoutes)) {
            $configurator = require_once $devRoutes;
            $configurator($routerContainer->getMap(), $container);
        }
        
        return $routerContainer;
    },
];
