<?php

namespace Ps2alerts\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\QueryObjects\QueryObject;

class EndpointBaseController
{
    protected $repository;
}
