<?php

namespace Src\Engine;

use Twig_SimpleFilter;

class TwigEngine extends BaseEngine
{
    /**
     * @var \Twig\Environment
     */
    private $engine;

    /**
     * TwigEngine constructor.
     * @param string $path
     * @throws \Twig\Error\LoaderError
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        $loader = new \Twig\Loader\FilesystemLoader($path);
        $loader->addPath(__DIR__ . '/../views/base','core_templates');
        $this->engine = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $this->engine->addFilter(new Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
            $response = array();
            foreach ($stdClassObject as $key => $value) {
                $response[$key] = $value;
            }
            return $response;
        }));
    }

    /**
     * @param string $view
     * @return BaseEngine
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function load(string $view): BaseEngine
    {
        $this->view = $this->engine->load($view);
        return $this;
    }

    /**
     * @param string $view
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $view = null, array $context = [])
    {
        if (!empty($this->view)) {
            return $this->view->render($context);
        }

        if (empty($view)) {
            return false;
        }

        $view = $this->load($view);
        return $view->render($context);
    }

    /**
     * @param string $block
     * @param string|null $view
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function hasBlock(string $block, string $view = null)
    {
        if (!empty($this->view)) {
            return $this->view->hasBlock($block);
        }

        if (empty($view)) {
            return false;
        }

        $view = $this->load($view);
        return $view->hasBlock($block);
    }

    /**
     * @param string $block
     * @param string|null $view
     * @return bool|string
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBlock(string $block, array $context = [], string $view = null)
    {
        if (!empty($this->view)) {
            return $this->view->renderBlock($block, $context);
        }

        if (empty($view)) {
            return false;
        }

        $view = $this->load($view);
        return $view->renderBlock($block, $context);
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFullFilename(string $filename): string
    {
        return "{$filename}.twig";
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return 'twig';
    }
}