<?php

namespace Src\Compiler;

use DirectoryIterator;
use DOMDocument;
use DOMElement;
use Pimple\Container;
use Src\Engine\BaseEngine;
use Src\Helper\ConsoleHelper;

abstract class BaseCompiler implements BaseCompilerInterface
{
    protected const TYPE = 'default';

    /**
     * @var array
     */
    protected $assetsConfig = [];

    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @var array
     */
    protected $navigation = [];

    /**
     * @var BaseEngine
     */
    protected $engine;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ConsoleHelper
     */
    protected $console;

    /**
     * WordpressCompiler constructor.
     * @param BaseEngine $engine
     * @param Container $container
     */
    public function __construct(BaseEngine $engine, Container $container)
    {
        $this->engine = $engine;
        $this->container = $container;
        $this->console = $this->container['console'];
    }

    /**
     * @return string
     */
    protected function getTemplatesDir(): string
    {
        return $this->engine->getProjectPath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->engine->getTemplatesDirName();
    }

    /**
     * @return string
     */
    protected function getThemePath(): string
    {
        return $this->getTemplatesDir() . DIRECTORY_SEPARATOR . $this::TYPE;
    }

    /**
     * @return string
     */
    protected function getPreviewPath(): string
    {
        return $this->engine->getProjectPath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->engine->getPreviewDirName();
    }

    public function compile(): void
    {
        if (!$this->validateThemeFiles()) {
            return;
        }

        $this->initThemeFolders();

        //Prepare default assets configuration
        $engine = $this->engine->load($this->engine->getFullFilename('base.html'));
        $this->setAssetsConfig($engine);

        foreach (new DirectoryIterator($this->engine->getProjectPath()) as $file) {
            if ($file->isFile() && ($file->getExtension() == $this->engine->getExtension())) {
                /** @var BaseEngine $engine */
                $engine = $this->engine->load($file->getFilename());

                if ($file->getFilename() != $this->engine->getFullFilename('base.html')) {

                    $filename = $file->getFilename();
                    preg_match('/^([^.]+)/', $filename, $matches);
                    
                    if (empty($matches[0])) {
                        continue;
                    }

                    $page = $matches[0];
                    $this->console->info("Compile {$page} page");
                    $this->buildPage($engine, $page);
                }
            }
        }

        $this->saveAssetsConfig();
        $this->compileNavigation();
        $this->compileThemeExtraFiles();
    }

    abstract protected function buildPage(BaseEngine $engine, string $page): void;

    abstract protected function compileHeader(BaseEngine $engine): void;

    abstract protected function compileNavigation(): void;

    abstract protected function compileContent(BaseEngine $engine, string $page): void;

    abstract protected function compileFooter(BaseEngine $engine): void;

    abstract protected function compileThemeExtraFiles(): void;

    abstract protected function compilePreviewFile(BaseEngine $engine, string $page): void;

    /**
     * @param BaseEngine $engine
     * @param string $page
     */
    protected function setAssetsConfig(BaseEngine $engine = null, string $page = 'default'): void
    {
        if (empty($engine)) {
            $engine = clone $this->engine;
        }

        $assetsConfig = $assetsConfig[$page] = $oldConfig = [];

        $blocks = ['css', 'js',];
        foreach ($blocks as $block) {
            if ($engine->hasBlock($block)) {
                $css = $engine->renderBlock($block);
                if (!empty($css)) {
                    $dom = new DOMDocument('1.0', 'UTF-8');
                    $dom->loadHTML($css, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                    //check the css files by tag name
                    $links = $dom->getElementsByTagName('link');

                    if ($links->length > 0) {
                        /** @var DOMElement $link */
                        foreach ($links as $link) {
                            $assetsConfig[$page]['css'][basename($link->getAttribute('href'))] = $link->getAttribute('href');
                        }
                    }

                    //check the js files by tag name
                    $links = $dom->getElementsByTagName('script');

                    if ($links->length > 0) {
                        /** @var DOMElement $link */
                        foreach ($links as $link) {
                            $assetsConfig[$page]['js'][basename($link->getAttribute('src'))] = $link->getAttribute('src');
                        }
                    }
                }
            }
        }

        if ($page != 'default') {
            if (!empty($this->assetsConfig)) {
                $oldConfig = $this->assetsConfig;

                if (!empty($oldConfig)) {

                    if (!empty($oldConfig['default'])) {
                        $assetsConfig['default'] = $oldConfig['default'];
                    }

                    foreach ($assetsConfig['default'] as $type => $asset) {
                        foreach ($asset as $key => $src) {
                            if (!empty($assetsConfig[$page][$type]) && in_array($key, array_keys($assetsConfig['default'][$type]))) {
                                unset($assetsConfig[$page][$type][$key]);
                            }
                        }
                    }
                }
            }
        }

        $this->assetsConfig += $assetsConfig;
    }

    protected function saveAssetsConfig(): void
    {
        $assetsPath = $this->getTemplatesDir() . DIRECTORY_SEPARATOR . 'assets.json';
        $this->console->info("Save assets configuration in path: {$assetsPath}");
        file_put_contents($assetsPath, json_encode($this->assetsConfig));
    }

    protected function initThemeFolders(): void
    {
        $foldersToCreate = [
            $this->getTemplatesDir(),
            $this->getPreviewPath(),
            $this->getThemePath(),
        ];

        foreach ($foldersToCreate as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

        //Here we copy the assets files
        foreach (glob($this->engine->getProjectPath() . DIRECTORY_SEPARATOR . "*") as $file) {
            if (is_dir($file) && is_readable($file) && !in_array(basename($file), ['cache', '.', '..'])) {
                $destination = $this->getThemePath() . DIRECTORY_SEPARATOR . basename($file);
                self::recursiveCopy($file, $destination);

                //copy files for preview
                $destination = $this->getPreviewPath() . DIRECTORY_SEPARATOR . basename($file);
                self::recursiveCopy($file, $destination);
            }
        }
    }

    /**
     * @param string $src
     * @param string $dst
     * @return bool
     */
    protected function recursiveCopy(string $src, string $dst): bool
    {
        $dir = opendir($src);
        $result = ($dir === false ? false : true);

        if ($result !== false) {
            $result = @mkdir($dst);

            if ($result === true) {
                while (false !== ($file = readdir($dir))) {
                    if (($file != '.') && ($file != '..') && $result) {
                        if (is_dir($src . '/' . $file)) {
                            $result = self::recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                        } else {
                            $result = copy($src . '/' . $file, $dst . '/' . $file);
                        }
                    }
                }
                closedir($dir);
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function validateThemeFiles(): bool
    {
        $requiredFiles = [
            'index.html',
            'base.html',
        ];

        foreach ($requiredFiles as $filename) {
            if (!file_exists($this->engine->getProjectPath() . DIRECTORY_SEPARATOR . $this->engine->getFullFilename($filename))) {
                $this->console->error("File {$filename} doesn't exists in template directory");
                return false;
            }
        }

        return true;
    }
}