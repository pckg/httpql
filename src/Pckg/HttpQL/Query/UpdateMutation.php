<?php

namespace Pckg\HttpQL\Query;

trait UpdateMutation
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
         * And map data first?
         */
        $mapped = $this->map($data);
        return $record->setAndSave($mapped);
    }
}
