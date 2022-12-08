<?php

namespace Pckg\HttpQL\Entity;

trait QlExtension
{
    public function applyQlExtension()
    {
        $filters = request()->header('X-Pckg-Orm-Filters');
        if (!$filters) {
            return $this;
        }
        $filters = json_decode($filters, true);
        if (!isset($filters[0]['k']) || !isset($filters[0]['v'])) {
            return $this;
        }

        return $this->where($filters[0]['k'], $filters[0]['v']);
    }
}
