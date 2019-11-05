<?php

namespace Src\Helper;

class ConsoleHelper
{
    /**
     * @param string $message
     */
    public function info(string $message): void
    {
        $time = date('Y-m-d H:i:s');
        echo "\e[1;34;1m[{$time}] {$message}\e[0m\n";
    }

    /**
     * @param string $message
     */
    public function error(string $message): void
    {
        $time = date('Y-m-d H:i:s');
        echo "\e[0;31;1m[{$time}] {$message}\e[0m\n";
    }
}