<?php
namespace Ibrows\RestBundle\Tests\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @var string
     */
    private $rootConfig;

    /**
     * @param string $rootConfig
     * @param bool   $environment
     * @param        $debug
     */
    public function __construct($rootConfig, $environment, $debug)
    {
        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($rootConfig) && !is_file($rootConfig = __DIR__ . '/' . $rootConfig)) {
            throw new \InvalidArgumentException(sprintf('The root config "%s" does not exist.', $rootConfig));
        }
        $this->rootConfig = $rootConfig;

        parent::__construct($environment, $debug);
    }

    /**
     * @return array
     */
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new \Ibrows\JsonPatch\IbrowsJsonPatchBundle(),
            new \Ibrows\RestBundle\IbrowsRestBundle(),
        ];
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/' . Kernel::VERSION . '/cache/' . $this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir() . '/' . Kernel::VERSION . '/logs';
    }

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->rootConfig);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array($this->rootConfig, $this->getEnvironment(), $this->isDebug()));
    }

    /**
     * @param string $str
     */
    public function unserialize($str)
    {
        $a = unserialize($str);
        $this->__construct($a[0], $a[1], $a[2], $a[3]);
    }
}
