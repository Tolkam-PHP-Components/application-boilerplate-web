<?php /** @noinspection PhpIncludeInspection */
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Tolkam\Application\ApplicationInterface;
use Tolkam\Application\Http\Emitter\SapiEmitter;
use Tolkam\Routing\Resolver\ContainerResolver;
use Tolkam\Routing\Resolver\Handler\CallableResolver;
use Tolkam\Routing\RouterContainer;
use Tolkam\Routing\RoutingMiddleware;
use Tolkam\Routing\Runner\Handler\InvokableRunner;
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
    
    // // assets manager
    // AssetManager::class => function (
    //     ApplicationInterface $app,
    //     ConfigurationInterface $config
    // ) {
    //     $templateName = $config->get('ui.template.name');
    //     $publicAssetsUri = '/assets/' . $templateName;
    //     $manifestPath = $app->getDirectory('templates', [$templateName, 'assets/build']);
    //
    //     return new AssetManager(
    //         new UriGroup(
    //             $publicAssetsUri,
    //             new ManifestVersionStrategy($manifestPath . 'manifest.json')
    //         )
    //     );
    // },
    //
    // // html renderer
    // RendererInterface::class => function (
    //     ApplicationInterface $app,
    //     ContainerInterface $container,
    //     ConfigurationInterface $config
    // ) {
    //     $debug = $config->get('debug', false);
    //
    //     $twigOptions = [
    //         'auto_reload' => true,
    //         'debug' => $debug,
    //         'strict_variables' => $debug,
    //         'cache' => new FilesystemCache(
    //             $app->getDirectory('cache', ['twig']),
    //             FilesystemCache::FORCE_BYTECODE_INVALIDATION
    //         ),
    //     ];
    //
    //     $renderer = new TwigRenderer(null, null, $twigOptions);
    //
    //     // custom twig extensions
    //     $twig = $renderer->getEnvironment();
    //
    //     // make twig autoload extensions dependencies from the container
    //     $twig->addRuntimeLoader(new Twig\RuntimeLoader($container));
    //
    //     // extensions
    //     $twig->addExtension(new DebugExtension);
    //     $twig->addExtension(new AssetExtension($container->get(AssetManager::class)));
    //
    //     // paths
    //     $viewsDir = $app->getDirectory('templates', [$config->get('ui.template.name'), 'views']);
    //     $renderer->addPath($viewsDir);
    //
    //     return $renderer;
    // },
];
