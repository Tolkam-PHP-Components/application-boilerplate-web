<?php declare(strict_types=1);

use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tolkam\Configuration\ConfigurationInterface;
use Tolkam\Routing\Map;

return function (Map $map, ContainerInterface $container) {
    
    // example route
    $map->route('home', '/', function (
        ServerRequestInterface $request,
        ConfigurationInterface $configuration
    ) {
        $response = (new ResponseFactory)->createResponse();
        $fooValue = $configuration->get('foo');
        
        $body = <<<BODY
            <h1>Welcome to Tolkam application!</h1>
            Configuration value of 'foo' is: $fooValue
        BODY;
        
        $response->getBody()->write($body);
        
        return $response;
    });
    
    return $map;
};
