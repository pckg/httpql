<?php

namespace Pckg\HttpQL\Query;

use Pckg\Database\Entity;
use Pckg\Htmlbuilder\Element\Form;
use Pckg\Htmlbuilder\Resolver\FormResolver;

/**
 * Class AbstractQuery
 * @package Pckg\HttpQL\Query
 */
abstract class AbstractQuery
{
    /**
     * @var array
     */
    protected $schemes = [];

    abstract public function mutate(array $data = [], array $query = []);

    public function map($data)
    {
        $mapped = [];
        $schemes = config('pckg.ql.schemes', []);
        foreach ($this->schemes as $scheme) {
            $definition = $schemes[$scheme] ?? null;
            if (isset($definition['from'])) {
                $from = $schemes[$definition['from']] ?? null;
            }
            if (!isset($from)) {
                return $data;
            }
            foreach ($definition['fields'] as $f => $c) {
                if (isset($c['from'])) {
                    $mapped[$c['from']] = $data[$f] ?? null;
                } else {
                    $mapped[$f] = $data[$f] ?? null;
                }
            }
        }

        return $mapped;
    }

    /**
     * Validate request.
     * @return ?Form
     */
    public function resolve()
    {
        $schemes = config('pckg.ql.schemes', []);
        $authTags = auth()->getUserDataArray()['tags'] ?? [];

        foreach ($this->schemes as $scheme) {
            /**
             * Retrieve definition.
             */
            $definition = $schemes[$scheme] ?? null;
            if (isset($definition['from'])) {
                $from = $schemes[$definition['from']] ?? null;
            }
            if (!$definition) {
                throw new \Exception('Scheme for query does not exist');
            }

            /**
             * Validate permissions.
             */
            $granted = true;
            foreach ($definition['permissions'] as $key => $settings) {
            }

            /*if (!$granted) {
                throw new \Exception('Access to the resource not granted');
            }*/

            /**
             * Create a form with defined fields.
             */
            $form = (new Form())->fromArray($definition['fields']);
            $body = post('data');

            /**
             * Now, we first need to build a new array?
             */
            $form->populateFromArray($body);

            /**
             * Make sure that does not contain any errors.
             */
            $errors = [];
            $descriptions = [];
            if (!$form->isValid($errors, $descriptions)) {
                return (new FormResolver())->ajaxErrorResponse($errors, $descriptions);
            }

            return $form;
        }

        return null;
    }

    /**
     * @param array $query
     * @param Entity $entity
     * @throws \Exception
     */
    public function applyQueryOnEntity(array $query, Entity $entity)
    {
        foreach (json_decode($query['X-Pckg-Orm-Filters'] ?? '[]', true) as $filter) {
            $key = $filter['k'] ?? null;
            $value = $filter['v'] ?? null;
            $comparator = $filter['c'] ?? null;
            if (!$key || !$value || !$comparator) {
                throw new \Exception('Invalid filter definition');
            }

            if ($comparator !== '=') {
                throw new \Exception('Comparator not supported');
            } else if (!is_scalar($value)) {
                throw new \Exception('Value not supported');
            }
            /**
             * @T00D00 - validate that field is actually defined.
             */
            $mapper = [
                'source' => 'morph_id',
                'hash' => 'slug',
            ];
            $entity->where($mapper[$key] ?? $key, $value);
        }

        return $entity;
    }
}
