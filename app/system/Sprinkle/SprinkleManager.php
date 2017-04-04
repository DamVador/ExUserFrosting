<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Sprinkle;

use Illuminate\Support\Str;
use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\Event\EventDispatcher;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use UserFrosting\Support\Exception\FileNotFoundException;

/**
 * Sprinkle manager class.
 *
 * Loads a series of sprinkles, running their bootstrapping code and including their routes.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SprinkleManager
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var Sprinkle[] An array of sprinkles.
     */
    protected $sprinkles = [];

    /**
     * @var string The full absolute base path to the sprinkles directory.
     */
    protected $sprinklesPath;

    /**
     * Create a new SprinkleManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->sprinklesPath = \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\SPRINKLES_DIR_NAME . \UserFrosting\DS;
    }

    /**
     * Takes the name of a Sprinkle, and creates an instance of the initializer object (if defined).
     *
     * Creates an object of a subclass of UserFrosting\System\Sprinkle\Sprinkle if defined for the sprinkle (converting to StudlyCase).
     * Otherwise, returns null.
     * @param $name The name of the Sprinkle to initialize.
     */
    public function bootSprinkle($name)
    {
        $className = Str::studly($name);
        $fullClassName = "\\UserFrosting\\Sprinkle\\$className\\$className";

        // Check that class exists.  If not, set to null
        if (class_exists($fullClassName)) {
            $sprinkle = new $fullClassName($this->ci);
            return $sprinkle;
        } else {
            return null;
        }
    }

    public function initFromSchema($schemaPath)
    {
        $baseSprinkleNames = $this->loadSchema($schemaPath);

        foreach ($baseSprinkleNames as $sprinkleName) {
            $sprinkle = $this->bootSprinkle($sprinkleName);

            if ($sprinkle) {
                // Subscribe the sprinkle to the event dispatcher
                $this->ci->eventDispatcher->addSubscriber($sprinkle);
            }

            $this->sprinkles[$sprinkleName] = $sprinkle;
        }
    }

    /**
     * Register services for a specified Sprinkle.
     */
    public function registerServices($name)
    {
        $className = Str::studly($name);
        $fullClassName = "\\UserFrosting\\Sprinkle\\$className\\ServicesProvider\\ServicesProvider";

        // Check that class exists, and register services
        if (class_exists($fullClassName)) {
            // Register core services
            $serviceProvider = new $fullClassName();
            $serviceProvider->register($this->ci);
        }
    }

    public function registerAllServices()
    {
        foreach ($this->getSprinkleNames() as $sprinkleName) {
            $this->registerServices($sprinkleName);
        }
    }

    /**
     * Adds assets for a specified Sprinkle to the assets (assets://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's assets (if found).
     */
    public function addAssets($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\ASSET_DIR_NAME;

        $this->ci->locator->addPath('assets', '', $path);

        return $this->ci->locator->findResource('assets://', true, false);
    }

    /**
     * Adds config for a specified Sprinkle to the config (config://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's config (if found).
     */
    public function addConfig($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\CONFIG_DIR_NAME;

        $this->ci->locator->addPath('config', '', $path);

        return $this->ci->locator->findResource('config://', true, false);
    }

    /**
     * Adds extras for a specified Sprinkle to the locale (extra://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's extras (if found).
     */
    public function addExtras($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\EXTRA_DIR_NAME;

        $this->ci->locator->addPath('extra', '', $path);

        return $this->ci->locator->findResource('extra://', true, false);
    }

    /**
     * Adds locales for a specified Sprinkle to the locale (locale://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's locales (if found).
     */
    public function addLocale($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\LOCALE_DIR_NAME;

        $this->ci->locator->addPath('locale', '', $path);

        return $this->ci->locator->findResource('locale://', true, false);
    }

    /**
     * Adds paths to routes for a specified Sprinkle to the routes (routes://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's routes (if found).
     */
    public function addRoutes($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\ROUTE_DIR_NAME;

        $this->ci->locator->addPath('routes', '', $path);

        return $this->ci->locator->findResource('routes://', true, false);
    }

    /**
     * Adds Fortress schema for a specified Sprinkle to the schema (schema://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's schema (if found).
     */
    public function addSchema($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\SCHEMA_DIR_NAME;

        $this->ci->locator->addPath('schema', '', $path);

        return $this->ci->locator->findResource('schema://', true, false);
    }

    /**
     * Adds templates for a specified Sprinkle to the templates (templates://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's templates (if found).
     */
    public function addTemplates($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\TEMPLATE_DIR_NAME;

        $this->ci->locator->addPath('templates', '', $path);

        return $this->ci->locator->findResource('templates://', true, false);
    }

    /**
     * Register resource streams for all base sprinkles.
     */
    public function addResources()
    {
        // For each sprinkle (other than Core), register its resources and then run its initializer
        foreach ($this->sprinkles as $sprinkleName => $sprinkle) {
            $this->addConfig($sprinkleName);
            $this->addAssets($sprinkleName);
            $this->addExtras($sprinkleName);
            $this->addLocale($sprinkleName);
            $this->addRoutes($sprinkleName);
            $this->addSchema($sprinkleName);
            $this->addTemplates($sprinkleName);
        }

        /* This would allow a stream to subnavigate to a specific sprinkle (e.g. "templates://core/")
           Not sure if we need this.
           $locator->addPath('templates', '$name', $sprinklesDirFragment . '/' . \UserFrosting\TEMPLATE_DIR_NAME);
         */
    }

    /**
     * Sets the list of sprinkles.
     *
     * @param Sprinkle[] $sprinkles An array of Sprinkle classes.
     */
    public function setSprinkles($sprinkles)
    {
        $this->sprinkles = $sprinkles;
        return $this;
    }

    /**
     * Returns a list of available sprinkles.
     *
     * @return Sprinkle[]
     */
    public function getSprinkles()
    {
        return $this->sprinkles;
    }

    /**
     * Returns a list of available sprinkle names.
     *
     * @return string[]
     */
    public function getSprinkleNames()
    {
        return array_keys($this->sprinkles);
    }

    /**
     * Return if a Sprinkle is available
     * Can be used by other Sprinkles to test if their dependencies are met
     *
     * @param $name The name of the Sprinkle
     */
    public function isAvailable($name)
    {
        return in_array($name, $this->getSprinkleNames());
    }

    /**
     * Load list of Sprinkles from a JSON schema file (e.g. sprinkles.json).
     *
     *
     */
    protected function loadSchema($schemaPath)
    {
        $sprinklesFile = file_get_contents($schemaPath);
    
        if ($sprinklesFile === false) {
            $errorMessage = "Error: Unable to determine Sprinkle load order.";
            throw new FileNotFoundException($errorMessage);
        }

        return json_decode($sprinklesFile)->base;
    }
}
