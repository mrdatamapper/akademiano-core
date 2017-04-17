<?php


namespace Akademiano\Core;


use Akademiano\HttpWarp\Environment;
use Akademiano\HttpWarp\EnvironmentIncludeInterface;
use Akademiano\HttpWarp\Parts\EnvironmentIncludeTrait;
use Composer\Autoload\ClassLoader;

class SitesManager implements EnvironmentIncludeInterface
{
    use EnvironmentIncludeTrait;

    /** @var  ClassLoader */
    protected $loader;

    protected $currentSite;


    public function __construct(ClassLoader $loader, Environment $environment)
    {
        if (null !== $loader) {
            $this->setLoader($loader);
        }
        if (null !== $environment) {
            $this->setEnvironment($environment);
        }
    }


    /**
     * @return ClassLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param ClassLoader $loader
     */
    public function setLoader(ClassLoader $loader)
    {
        $this->loader = $loader;
    }

    public function getCurrentSite()
    {
        if (null === $this->currentSite) {
            $environment = $this->getEnvironment();
            $site = $environment->getServerName();
            $this->currentSite = strtolower(str_replace(["/", "\\", ".."], "", trim($site)));
        }
        return $this->currentSite;
    }

    public function getSiteDir($siteName)
    {

        $siteName = str_replace([".", "-"], "_", $siteName);
        if (substr($siteName, 0, 1) === "_") {
            $siteName = "_" . ucfirst(substr($siteName, 1));
        } else {
            $siteName = ucfirst($siteName);
        }
        $siteClass = "Sites\\" . $siteName . "\\Site";
        if (!class_exists($siteClass)) {
            return null;
        }

        $file = $this->getLoader()->findFile($siteClass);
        $dir = realpath(dirname($file));
        return $dir;
    }

    public function isCurrentSiteDirDefault()
    {
        $siteName = $this->getCurrentSiteDir();
        if ($this->getSiteDir($siteName)) {
            return false;
        } elseif ($this->getSiteDir("_default")) {
            return true;
        } else {
            return false;
        }
    }

    public function getCurrentSiteDir()
    {
        return $this->getSiteDir($this->getCurrentSite()) ?: $this->getSiteDir("_default");
    }
}
