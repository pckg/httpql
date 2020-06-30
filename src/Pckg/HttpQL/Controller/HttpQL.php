<?php namespace Pckg\HttpQL\Controller;

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

        /**
         * @var $query AbstractQuery
         */
        $query = resolve($queries[$action]);

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