<?php

/**
 * Trait для работы с древовидными структурами
 * Содержит методы построения и обработки деревьев
 */
trait TableTreeTrait
{
    /**
     * Получение дерева slTree
     */
    public function getslTree($slTreeSettings, $rows, $parents)
    {
        foreach ($rows as &$row) {
            $row['title'] = $this->pdoTools->getChunk("@INLINE " . $slTreeSettings['title'], $row);
            $isLeaf = true;
            foreach ($slTreeSettings['isLeaf'] as $field => $v) {
                if ($row[$field] != $v) $isLeaf = false;
            }
            if ($isLeaf) $row['isLeaf'] = true;
            $row[$slTreeSettings['idField']] = (int)$row[$slTreeSettings['idField']];
            $row[$slTreeSettings['parentIdField']] = (int)$row[$slTreeSettings['parentIdField']];
        }
        $tree0 = $this->buildTree($rows, $slTreeSettings['idField'], $slTreeSettings['parentIdField'], [(int)$parents]);
        $tree = [];
        $tree[] = $this->prepareTree($tree0[(int)$parents]);
        return $tree;
    }

    /**
     * Подготовка узла дерева
     */
    public function prepareTree($node0)
    {
        $node = [];
        if (!empty($node0['children'])) {
            $children = $node0['children'];
            usort($children, function ($item1, $item2) {
                return $item1['menuindex'] >= $item2['menuindex'];
            });
            unset($node0['children']);
        }
        
        $node = [
            'title' => $node0['title'],
            'data' => $node0
        ];
        if ($node0['isLeaf']) {
            $node['isLeaf'] = true;
        } else {
            $node['isExpanded'] = false;
        }
        if (isset($children)) {
            foreach ($children as $child) {
                $node['children'][] = $this->prepareTree($child);
            }
        }
        return $node;
    }

    /**
     * Построение иерархического дерева из массива
     *
     * @param array $tmp Массив с данными
     * @param string $id Имя первичного ключа
     * @param string $parent Имя родительского ключа
     * @param array $roots Разрешенные корни узлов
     *
     * @return array
     */
    public function buildTree($tmp = array(), $id = 'id', $parent = 'parent', array $roots = array())
    {
        if (empty($id)) {
            $id = 'id';
        }
        if (empty($parent)) {
            $parent = 'parent';
        }

        if (count($tmp) == 1) {
            $row = current($tmp);
            $tree = array(
                $row[$parent] => array(
                    'children' => array(
                        $row[$id] => $row,
                    ),
                ),
            );
        } else {
            $rows = $tree = array();
            foreach ($tmp as $v) {
                $rows[$v[$id]] = $v;
            }

            foreach ($rows as $id => &$row) {
                if (empty($row[$parent]) || (!isset($rows[$row[$parent]]) && in_array($id, $roots))) {
                    $tree[$id] = &$row;
                } else {
                    $rows[$row[$parent]]['children'][$id] = &$row;
                }
            }
        }

        return $tree;
    }

    /**
     * Изменить порядок строк через drag-and-drop.
     * Принимает $request['order'] — массив ID в новом порядке.
     * Назначает sortfield = 10, 20, 30... по этому порядку.
     * Настройка: row_drag: { sortfield: "sortfield", parentsort: "parent_field" }
     */
    public function sortableReorder($rule, $request)
    {
        $rowDragConfig = isset($rule['properties']['row_drag']) && is_array($rule['properties']['row_drag'])
            ? $rule['properties']['row_drag'] : null;
        if (!$rowDragConfig) return $this->error('row_drag не настроен или не является объектом');

        $sfField = isset($rowDragConfig['sortfield']) ? $rowDragConfig['sortfield'] : 'sortfield';
        $order   = isset($request['order']) ? $request['order'] : [];
        if (empty($order) || !is_array($order)) return $this->error('Не передан order');

        $sf = 10;
        foreach ($order as $id) {
            $id = (int)$id;
            if (!$id) continue;
            if ($obj = $this->modx->getObject($rule['class'], $id)) {
                $obj->set($sfField, $sf);
                $obj->save();
                $sf += 10;
            }
        }
        return $this->success('Порядок сохранён');
    }

    /**
     * Вставить пустую строку выше строки с указанным ID.
     * Сдвигает строки с sortfield >= target на +10, создаёт новую строку на их месте.
     * Принимает $request['target_id'] (или $request['id']).
     */
    public function sortableInsertAbove($rule, $request)
    {
        $rowDragConfig = isset($rule['properties']['row_drag']) && is_array($rule['properties']['row_drag'])
            ? $rule['properties']['row_drag'] : null;
        if (!$rowDragConfig) return $this->error('row_drag не настроен или не является объектом');

        $sfField  = isset($rowDragConfig['sortfield'])  ? $rowDragConfig['sortfield']  : 'sortfield';
        $psField  = isset($rowDragConfig['parentsort']) ? $rowDragConfig['parentsort'] : null;

        $targetId = (int)(isset($request['target_id']) ? $request['target_id'] : (isset($request['id']) ? $request['id'] : 0));
        if (!$targetId) return $this->error('Не указан target_id');

        $target = $this->modx->getObject($rule['class'], $targetId);
        if (!$target) return $this->error('Строка не найдена');

        $targetSortfield = (int)$target->get($sfField);
        $parentValue     = $psField ? $target->get($psField) : null;

        // Сдвигаем строки с sortfield >= targetSortfield в той же группе
        $table       = $this->modx->getTableName($rule['class']);
        $whereParent = ($psField && $parentValue !== null)
            ? "AND `{$psField}` = " . (int)$parentValue : '';
        $this->modx->exec(
            "UPDATE {$table} SET `{$sfField}` = `{$sfField}` + 10
             WHERE `{$sfField}` >= {$targetSortfield} {$whereParent}"
        );

        // Создаём новую строку с нужным sortfield и parentsort
        $newObj = $this->modx->newObject($rule['class']);
        if (!$newObj) return $this->error('Ошибка создания объекта');
        if ($psField && $parentValue !== null) {
            $newObj->set($psField, $parentValue);
        }
        $newObj->set($sfField, $targetSortfield);
        $newObj->save();

        return $this->success('', ['id' => $newObj->get('id'), $sfField => $targetSortfield]);
    }

    /**
     * Установка строки выбора для группировки
     */
    public function setSelectRow($rule, $rows0 = null)
    {
        $select_row = [];
        foreach ($rule['properties']['group']['select'] as $field => $v) {
            switch ($v['type_aggs']) {
                case 'count':
                    $select_row[$field] = 0;
                    break;
                case 'sum':
                    $select_row[$field] = 0;
                    break;
                case 'max':
                    if ($rows0 !== null && !empty($rows0)) {
                        $select_row[$field] = $rows0[0][$field];
                    } else {
                        $select_row[$field] = 0;
                    }
                    break;
                case 'min':
                    if ($rows0 !== null && !empty($rows0)) {
                        $select_row[$field] = $rows0[0][$field];
                    } else {
                        $select_row[$field] = 0;
                    }
                    break;
                default:
                    $select_row[$field] = $v;
            }
        }
        return $select_row;
    }
}