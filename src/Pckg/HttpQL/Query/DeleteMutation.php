<?php

namespace Pckg\HttpQL\Query;

trait DeleteMutation
{

    /**
     * @param array $data
     * @param array $query
     * @throws \Exception
     */
    public function mutate(array $data = [], array $query = [])
    {
        /**
         * Manually apply filter.
         */
        $entity = $this->getEntity();
        $entity = $this->applyQueryOnEntity($query, $entity);

        /**
         * Retrieve record before update.
         */
        $record = $entity->oneOrFail();

        /**
         * Now execute mutation.
         */
        return $record->delete();
    }
}
