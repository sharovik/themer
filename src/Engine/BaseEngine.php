<?php

namespace Src\Engine;

abstract class BaseEngine implements BaseEngineInterface
{
    protected const PREVIEW_DIR_NAME = 'preview';
    protected const THEME_DIR_NAME = 'theme';

    /**
     * @var string
     */
    protected $path;

    protected $view;

    /**
     * BaseEngine constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getProjectPath(): string
    {
        return rtrim($this->path, DIRECTORY_SEPARATOR);
    }

    public function getTemplatesDirName(): string
    {
        return self::THEME_DIR_NAME;
    }

    public function getPreviewDirName(): string
    {
        return self::PREVIEW_DIR_NAME;
    }

    abstract public function load(string $view): BaseEngine;

    abstract public function render(string $view = null, array $context = []);

    abstract public function hasBlock(string $block, string $view = null);

    abstract public function renderBlock(string $block, array $context = [], string $view = null);

    abstract public function getFullFilename(string $filename): string ;

    abstract public function getExtension(): string ;
}