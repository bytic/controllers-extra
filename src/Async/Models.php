<?php

namespace ByTIC\Controllers\Behaviors\Async;

use Nip\Database\Query\Select as SelectQuery;
use Nip\Records\RecordManager;

/**
 * Class Models
 * @package ByTIC\Controllers\Behaviors\Async
 */
trait Models
{
    public function order()
    {
        $orderString = $this->getRequest()->request->get('order');
        $orderName = $this->getRequest()->request->get('name', 'item');
        parse_str($orderString, $order);

        $idFields = $order[$orderName];

        $fields = $this->getModelManager()->findByPrimary($idFields);
        if (count($fields) < 1) {
            $this->Async()->sendMessage('No fields', 'error');
        }

        foreach ($idFields as $pos => $idField) {
            $field = $fields[$idField];
            if ($field) {
                $field->pos = $pos + 1;
                $field->update();
            }
        }

        $this->Async()->sendMessage('Items reordered');
    }

    /**
     * @return RecordManager
     */
    abstract protected function getModelManager();

    public function nameAutocomplete()
    {
        $query = $this->generateNameAutocompleteQuery();
        $items = $this->getModelManager()->findByQuery($query);

        $return = [];
        foreach ($items as $user) {
            $array = $user->toArray();
            $array['value'] = $user->getName();
            $array['label'] = $user->getName();
            $return[] = $array;
        }

        $this->setResponseValues($return);
        $this->output();
    }

    /**
     * @return SelectQuery
     */
    protected function generateNameAutocompleteQuery()
    {
        $query = $this->getModelManager()->paramsToQuery();
        $names = explode(' ', $_GET['term']);
        foreach ($names as $name) {
            $condition = $this->generateNameAutocompleteQueryCondition($query, $name);
            $query->where($condition);
        }
        return $query;
    }

    /**
     * @param SelectQuery $query
     * @param string $term
     * @return mixed
     */
    protected function generateNameAutocompleteQueryCondition($query, $term)
    {
        $condition = $query->getCondition("name LIKE ? ", "%{$term}%");
        return $condition;
    }
}
