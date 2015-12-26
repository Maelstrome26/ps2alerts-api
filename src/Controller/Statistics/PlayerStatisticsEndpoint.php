<?php

namespace Ps2alerts\Api\Controller\Statistics;

use League\Route\Http\JsonResponse as Response;
use Ps2alerts\Api\Controller\EndpointBaseController;
use Ps2alerts\Api\Loader\Statistics\PlayerStatisticsLoader;
use Symfony\Component\HttpFoundation\Request;

class PlayerStatisticsEndpoint extends EndpointBaseController
{
    /**
     * Construct
     *
     * @param \Ps2alerts\Api\Loader\Metrics\PlayerStatisticsLoader $loader
     */
    public function __construct(PlayerStatisticsLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Gets top players
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \League\Route\Http\JsonResponse
     */
    public function readLeaderboard(Request $request)
    {
        $post = $request->request->all();

        $return = $this->loader->readLeaderboard($post);

        if (empty($return)) {
            return new Response\NoContent();
        }

        return new Response\Ok($return);
    }
}
