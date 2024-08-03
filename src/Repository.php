<?php

namespace Heca73\LaravelRepository;

use Heca73\LaravelRepository\Interfaces\RepositoryInterface;
use Heca73\LaravelRepository\Traits\RepositoryAction;
use Heca73\LaravelRepository\Traits\RepositoryBuilder;

class Repository implements RepositoryInterface
{
    use RepositoryBuilder, RepositoryAction;
}