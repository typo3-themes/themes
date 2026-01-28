<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Frontend;

final class Utility
{
    public function firstValue(array $content = [], array $conf = []): string
    {
        if (is_array($content)) {

            $content = implode('#', $content);
        }
        return (string)$content;
    }
}
