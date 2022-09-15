<?php

namespace Codestage\Authorization\Traits;

use Exception;
use Illuminate\Database\Eloquent\{Builder};

/**
 * This trait marks a model as using a composite key instead of a simple one.
 *
 * @internal
 * @method array getKeyName()
 * @method Builder newQuery()
 */
trait HasCompositePrimaryKey
{
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param Builder $query
     * @throws Exception
     * @return Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->getKeyName() as $key) {
            if (isset($this->$key)) {
                $query->where($key, '=', $this->$key);
            } else {
                throw new Exception(__METHOD__ . 'Missing part of the primary key: ' . $key);
            }
        }

        return $query;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  array  $ids Array of keys, like [column => value].
     * @param  array  $columns
     * @return mixed|static
     */
    public static function find($ids, $columns = ['*'])
    {
        $me = new self;
        $query = $me->newQuery();

        foreach ($me->getKeyName() as $key) {
            $query->where($key, '=', $ids[$key]);
        }

        return $query->first($columns);
    }
}
