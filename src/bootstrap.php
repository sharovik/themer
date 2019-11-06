<?php

use Src\Helper\ConsoleHelper;
use Src\Helper\NavigationHelper;

include_once __DIR__ . "/../vendor/autoload.php";

$container = new \Pimple\Container();

$options = getopt('', ['path::', 'engine::',]);

$container['path'] = function ($c) use ($options) {
    $path = dirname(__DIR__);
    if (!empty($options['path']) && file_exists($options['path'])) {
        $path = $options['path'];
    }

    return $path;
};

$container['config'] = function ($c) {
    $pathToConfig = $c['path'] . DIRECTORY_SEPARATOR . 'themer.config.json';
    if (file_exists($pathToConfig)) {
        $config = json_decode(file_get_contents($pathToConfig), true);
    } else {
        $config = [
            "themeName" => "Demo theme",
            "author" => "John Doe",
            "authorUrl" => "http://john-doe.doe",
            "authorEmail" => "john@doe.doe",
            "themeVersion" => "1.0.0",
            "themeAlias" => "demo-theme",
            "engine" => "twig",
        ];
    }

    return $config;
};

$container['selected_engine'] = function ($c) use ($options) {
    $availableEngines = [
        'twig',
    ];

    $engine = 'twig';

    if (!empty($c['config']['engine'])) {
        $engine = $c['config']['engine'];
    }

    if (!empty($options['engine']) && in_array($options['engine'], $availableEngines)) {
        $engine = $options['engine'];
    }

    return $engine;
};

$container['engine'] = function ($c) use ($options) {
    $engine = $c['selected_engine'];

    $engineClass = 'Src\\Engine\\'.ucfirst($engine).'Engine';
    
    return new $engineClass($c['path']);
};

$container['engine_extra_files'] = function ($c) {
    $engine = 'twig';

    $engineClass = 'Src\\Engine\\'.ucfirst($engine).'Engine';

    return new $engineClass(__DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $c['selected_compiler']);
};

$container['selected_compiler'] = function ($c) use ($options) {
    $compiler = 'wordpress';
    $availableCompilers = [
        'wordpress',
    ];

    if (!empty($options['compiler']) && in_array($options['compiler'], $availableCompilers)) {
        $compiler = $options['compiler'];
    }

    return $compiler;
};

$container['compiler'] = function ($c) use ($options) {
    $compiler = $c['selected_compiler'];

    $compilerClass = 'Src\\Compiler\\'.ucfirst($compiler).'Compiler';

    return new $compilerClass($c['engine'], $c);
};

$container['console'] = function ($c) {
    return new ConsoleHelper();
};

$container['navigation'] = function ($c) {
    return new NavigationHelper();
};