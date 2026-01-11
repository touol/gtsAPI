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