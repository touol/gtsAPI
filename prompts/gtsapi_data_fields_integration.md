# Инструкция по изменению gtsAPI для поддержки data_fields в методе delete

## Изменения в методе delete gtsAPI

Для поддержки новой функциональности с `data_fields` необходимо модифицировать метод `delete` в gtsAPI следующим образом:

### 1. Обработка входящих параметров

В методе `delete` добавить обработку нового параметра `data_fields_values`:

```php
public function delete($data) {
    // Существующий код для получения ids
    $ids = $data['ids'];
    
    // Новый код для обработки data_fields_values
    $dataFieldsValues = isset($data['data_fields_values']) ? $data['data_fields_values'] : null;
    
    // Если есть data_fields_values, можно использовать эти данные
    // для дополнительной логики перед удалением
    if ($dataFieldsValues) {
        // $dataFieldsValues содержит массив объектов с значениями полей
        // для каждой удаляемой строки
        foreach ($dataFieldsValues as $rowData) {
            // Здесь можно добавить логику обработки данных строки
            // например, логирование, проверки, связанные операции и т.д.
        }
    }
    
    // Существующий код удаления
    // ...
}
```



## Структура передаваемых данных

### data_fields_values для одной строки (defRowAction, deleteLineItem):
```javascript
data_fields_values: [
    {
        "field1": "value1",
        "field2": "value2",
        "field3": "value3"
    }
]
```

### data_fields_values для множественных строк (defHeadAction, deleteSelectedLineItems):
```javascript
data_fields_values: [
    {
        "field1": "value1",
        "field2": "value2",
        "field3": "value3"
    },
    {
        "field1": "value4",
        "field2": "value5",
        "field3": "value6"
    }
]
```

### Настройки в response.data.options:
```javascript
{
    "data_fields": ["field1", "field2", "field3"],
    "hide_id": 1
}
