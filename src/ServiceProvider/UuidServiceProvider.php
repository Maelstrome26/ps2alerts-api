<?php

namespace Ps2alerts\Api\ServiceProvider;

use League\Container\ServiceProvider;
use Ramsey\Uuid\Uuid;

class UuidServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Ramsey\Uuid\Uuid',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->add('Ramsey\Uuid\Uuid', function () {
            $uuid = Uuid::Uuid4();
            return $uuid;
        });
    }
}
