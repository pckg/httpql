<?php namespace Pckg\HttpQL\Provider;

use Pckg\Framework\Provider;

class HttpQL extends Provider
{

    public function routes()
    {
        return [
            routeGroup([
                'controller' => \Pckg\HttpQL\Controller\HttpQL::class,
                'urlPrefix' => '/api/ql',
                'namePrefix' => 'api.ql',
            ], [
                '' => route('', 'index')->methods('POST'),
            ]),
        ];
    }

}