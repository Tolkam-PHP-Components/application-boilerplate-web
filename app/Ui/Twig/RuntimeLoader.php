<?php declare(strict_types=1);

namespace Acme\App\Web\Ui\Twig;

use Psr\Container\ContainerInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class RuntimeLoader implements RuntimeLoaderInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @inheritDoc
     */
    public function load($class)
    {
        return $this->container->get($class);
    }
}
