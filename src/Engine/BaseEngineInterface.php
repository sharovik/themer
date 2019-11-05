<?php

namespace Src\Engine;

interface BaseEngineInterface
{
    public function load(string $view);

    public function hasBlock(string $block);

    public function renderBlock(string $block);

    public function render(string $view);

    public function getFullFilename(string $filename): string ;

    public function getExtension(): string ;
}
