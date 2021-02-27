<?php

namespace Pckg\HttpQL\Controller;

use Pckg\Concept\Reflect;
use Pckg\Framework\Router;
use Pckg\Framework\Router\Command\ResolveDependencies;
use Pckg\HttpQL\Query\AbstractQuery;

class HttpQL
{

    public function postIndexAction()
    {
        $action = post('action');
        $queries = config('pckg.ql.queries', []);
        if (!array_key_exists($action, $queries)) {
            throw new \Exception('Query is not defined');
        }
        $actionConfig = $queries[$action];

        if (is_array($actionConfig)) {
            /**
             * Fake request.
             */
            return request()->mock(function (\Pckg\Framework\Request $mockRequest, \Pckg\Framework\Request $originalRequest) use ($actionConfig, $action) {
                $mockRequest->post()->setData($originalRequest->post('data'));
                $id = $mockRequest->post('id', null);

                if (strpos($action, ':fetch') === false && strpos($action, ':delete') === false) {
                    $mockRequest->setPost($originalRequest->post('data', []));
                } else {
                    $id = json_decode($originalRequest->post('query')['X-Pckg-Orm-Filters'], true)[0]['v'] ?? null;
                    if (!$id) {
                        throw new \Exception('No ID to fetch');
                    }
                }

                /**
                 * Fake router?
                 */
                return router()->mock(function (Router $mockRouter, Router $originalRouter) use ($actionConfig, $id) {
                    $resolved = [];
                    if (isset($actionConfig['resolvers'])) {
                        foreach ($actionConfig['resolvers'] as $key => $resolver) {
                            $mockRouter->setData([
                                'data' => [
                                    $key => $id,
                                ]
                            ]);
                        }
                        (new ResolveDependencies($actionConfig['resolvers']))->execute();
                    }

                    return Reflect::method($actionConfig['controller'], $actionConfig['view'], $mockRouter->getResolves());
                });
            });
        }

        /**
         * @var $query AbstractQuery
         */
        $query = resolve($actionConfig);

        /**
         * Resolve form with validation.
         */
        $form = $query->resolve();

        /**
         * Map values for aliases.
         * @T00D00 - map fields from filters to aliases.
         */
        $data = $query->map($form->getData());

        return [
            'success' => !!$query->mutate($form->getData(), post('query')),
        ];
    }
}
