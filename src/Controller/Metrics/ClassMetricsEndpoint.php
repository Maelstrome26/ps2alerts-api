<?php

namespace Ps2alerts\Api\Controller\Metrics;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Metrics\ClassMetricsLoader;
use Symfony\Component\HttpFoundation\Request;

class ClassMetricsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\ClassMetricsLoader $loader
     */
    public function __construct(ClassMetricsLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Returns a single entry by metric
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @param  array                                    $args
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readSingleByMetric(Request $request, array $args)
    {
        $this->loader->setMetrics([
            'col'   => 'classID',
            'value' => $args['classID']
        ]);

        $return = $this->loader->readSingle($args['resultID']);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
