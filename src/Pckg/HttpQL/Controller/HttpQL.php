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
            return request()->mock(function (\Pckg\Framework\Request $mockRequest, \Pckg\Framework\Request $originalRequest) use ($actionConfig) {
                $mockRequest->setPost($originalRequest->post('data', []));

                /**
                 * Fake router?
                 */
                return router()->mock(function (Router $mockRouter, Router $originalRouter) use ($actionConfig, $mockRequest) {
                    $resolved = [];
                    if (isset($actionConfig['resolvers'])) {
                        foreach ($actionConfig['resolvers'] as $key => $resolver) {
                            $mockRouter->setData([
                                'data' => [
                                    $key => $mockRequest->post('id'),
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
