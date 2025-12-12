<?php
/**
 * Методы для работы со стилями колонок таблиц
 * Добавить эти методы в класс tableAPIController
 */

/**
 * Сохранение стилей колонок на сервере
 * Доступно только для группы Administrator
 */
public function save_fields_style($rule, $request) {
    // Проверка прав доступа - только Administrator
    if (!$this->modx->user->isMember('Administrator')) {
        return $this->error('Доступ запрещен. Требуются права администратора.');
    }
    
    if (empty($request['fields_style'])) {
        return $this->error('Не переданы стили полей');
    }
    
    // Получаем объект таблицы
    if (!$gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $rule['table'], 'active' => 1])) {
        return $this->error('Таблица не найдена');
    }
    
    // Сохраняем стили как JSON
    $gtsAPITable->set('fields_style', json_encode($request['fields_style']));
    
    if ($gtsAPITable->save()) {
        return $this->success('Стили полей сохранены', [
            'fields_style' => $request['fields_style']
        ]);
    }
    
    return $this->error('Ошибка сохранения стилей');
}

/**
 * Сброс стилей колонок на сервере
 * Доступно только для группы Administrator
 */
public function reset_fields_style($rule, $request) {
    // Проверка прав доступа - только Administrator
    if (!$this->modx->user->isMember('Administrator')) {
        return $this->error('Доступ запрещен. Требуются права администратора.');
    }
    
    // Получаем объект таблицы
    if (!$gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $rule['table'], 'active' => 1])) {
        return $this->error('Таблица не найдена');
    }
    
    // Очищаем стили
    $gtsAPITable->set('fields_style', null);
    
    if ($gtsAPITable->save()) {
        return $this->success('Стили полей сброшены', [
            'fields_style' => null
        ]);
    }
    
    return $this->error('Ошибка сброса стилей');
}
