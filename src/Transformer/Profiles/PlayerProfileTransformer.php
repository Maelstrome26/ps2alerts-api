<?php

namespace Ps2alerts\Api\Transformer\Profiles;

use League\Fractal\TransformerAbstract;
use Ps2alerts\Api\Contract\HttpClientAwareInterface;
use Ps2alerts\Api\Contract\HttpClientAwareTrait;
use Ps2alerts\Api\Repository\Metrics\OutfitTotalRepository;
use Ps2alerts\Api\Repository\Metrics\PlayerRepository;
use Ps2alerts\Api\Transformer\Profiles\OutfitProfileTransformer;
use Ps2alerts\Api\Transformer\Profiles\PlayerCensusTransformer;
use Ps2alerts\Api\Transformer\Profiles\PlayerInvolvementTransformer;
use Ps2alerts\Api\Transformer\Profiles\PlayerMetricsTransformer;

class PlayerProfileTransformer extends TransformerAbstract implements HttpClientAwareInterface
{
    use HttpClientAwareTrait;

    /**
     * List of available includes to this resource
     *
     * @var array
     */
    protected $availableIncludes = [
        'census',
        'involvement',
        'metrics',
        'outfit',
        'vehicles',
        'weapons'
    ];

    protected $playerRepo;

    public function __construct(
        OutfitTotalRepository $outfitTotalsRepo,
        PlayerRepository $playerRepo
    ) {
        $this->outfitTotalsRepo = $outfitTotalsRepo;
        $this->playerRepo = $playerRepo;
    }

    /**
     * The transform method required by Fractal to parse the data and return proper typing and fields.
     *
     * @param  array $data Data to transform
     *
     * @return array
     */
    public function transform($data)
    {
        return [
            'id'      => (string) $data['playerID'], // Bigint
            'name'    => (string) $data['playerName'],
            'outfit'  => (string) $data['playerOutfit'], // Bigint
            'faction' => (int) $data['playerFaction'],
            'server'  => (int) $data['playerServer']
        ];
    }

    public function includeCensus($data)
    {
        $client = $this->getHttpClientDriver();

        $response = $client->get(
            "https://census.daybreakgames.com/s:planetside2alertstats/get/ps2:v2/character/{$data['playerID']}"
        );

        $json = json_decode($response->getBody()->getContents(), true);

        $character = $json['character_list'][0];
        var_dump($character);
        return $this->item($character, new PlayerCensusTransformer);
    }

    /**
     * Get Alert involvement & metrics
     *
     * @param  array $data
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeInvolvement($data)
    {
        $data = $this->playerRepo->readAllById($data['playerID'], 'playerID');
        return $this->collection($data, new PlayerInvolvementTransformer);
    }

    /**
     * Gets Metrics for a player
     *
     * @param  array $player
     *
     * @return array
     */
    public function includeMetrics($data)
    {
        $metrics = [
            'kills'     => 0,
            'deaths'    => 0,
            'teamkills' => 0,
            'suicides'  => 0,
            'headshots' => 0
        ];

        $alerts = $this->playerRepo->readAllById($data['playerID'], 'playerID');
        $count = count($alerts);
        $metrics['involvement'] = $count;

        // Calculate metrics
        for ($i = 0; $i < $count; $i++) {
            $metrics['kills']     = $metrics['kills'] + $alerts[$i]['playerKills'];
            $metrics['deaths']    = $metrics['deaths'] + $alerts[$i]['playerDeaths'];
            $metrics['teamkills'] = $metrics['teamkills'] + $alerts[$i]['playerTeamKills'];
            $metrics['suicides']  = $metrics['suicides'] + $alerts[$i]['playerSuicides'];
            $metrics['headshots'] = $metrics['headshots'] + $alerts[$i]['headshots'];
        }

        return $this->item($metrics, new PlayerMetricsTransformer);
    }

    /**
     * Get Outfit info and metrics
     *
     * @param  array $data
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeOutfit($data)
    {
        $data = $this->outfitTotalsRepo->readSingleById($data['playerOutfit'], 'primary');
        return $this->item($data, new OutfitProfileTransformer);
    }

    /**
     * Get Outfit info and metrics
     *
     * @param  array $data
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeVehicles($data)
    {
        // @todo: FINISH
    }

    /**
     * Get Outfit info and metrics
     *
     * @param  array $data
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeWeapons($data)
    {
        // @todo: FINISH
    }
}
