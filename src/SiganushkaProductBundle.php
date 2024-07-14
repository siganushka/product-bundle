<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SiganushkaProductBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
