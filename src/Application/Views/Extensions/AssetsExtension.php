<?php

namespace App\Application\Views\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetsExtension extends AbstractExtension
{
    private ?string $serverPath;

    public function __construct(?string $serverPath)
    {
        $this->serverPath = $serverPath ?? '';
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('assets', function (string $filePath): string {
                if (!is_string($filePath)) {
                    return "";
                }

                return $this->serverPath . '/public/assets/' . $filePath;
            }),
        ];
    }
}