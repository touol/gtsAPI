# Интеграция PVTables с gtsAPI

## Введение

PVTables - это компонент для MODX, предназначенный для создания редактируемых html-таблиц. Он интегрируется с gtsAPI для получения данных и выполнения операций CRUD (создание, чтение, обновление, удаление).

## Основной принцип работы

1. В базе данных создаются таблицы, которые нужно редактировать.
2. В таблице gtsAPI "Таблицы API" создается краткое описание (инструкции) нужной таблицы.
3. При вызове сниппета PVTable указывается название таблицы.
4. Затем сниппет подгружает js код PVTables и передает в него название таблицы.
5. PVTables делает запрос к gtsAPI и подгружает описание таблицы, по которому строит таблицу и делает запросы на чтение и запись в gtsAPI.

## Быстрый старт

Для самой простой таблицы в "Таблицы API" нужно:
1. Прописать имя таблицы (если имя совпадает с классом MODX таблицы)
2. Завести и выбрать пакет MODX, к которому привязана таблица
3. В properties прописать действия, доступные для таблицы

## Настройка properties

В properties можно прописать различные настройки для таблицы:

```json
{
  "actions": {
    "read": {},
    "create": {},
    "update": {},
    "delete": {},
    "subtables": {
      "table": "tableName",
      "where": {
        "field1": 1
      }
    },
    "subtabs": {
      "test": {
        "DocOrderLink": {
          "title": "Заголовок",
          "table": "DocOrderLink",
          "where": {
            "type_order_id": 1,
            "order_id": "id"
          }
        },
        "OrgsContact": {
          "title": "Контакты",
          "table": "OrgsContact",
          "where": {
            "OrgsContactLink.org_id": "loc_org_id"
          }
        }
      }
    }
  }
}
```

### Рекомендации по работе с конфигурацией таблиц

1. При добавлении таба для управления связанными данными в форме используйте тип `'table'` и укажите правильное условие `where` для фильтрации данных.
2. В конфигурации таблиц обратите внимание на синтаксис для ссылок на поля:
   - `tree_id: 'tree_id'` для получения текущего ID
   - `current_id` для связанной таблицы
   - `tree_id` для ID таблицы дерева
3. При работе с автозаполнением полей (`autocomplete`) убедитесь, что указаны правильные параметры `table` и `tpl`.
4. Для изменения подписей полей в формах используйте параметр `title_label` в конфигурации.
5. При изменении конфигурации компонентов/таблиц в файле `_build\configs\gtsapipackages.js` всегда обновляйте версию:
   - Для новых компонентов/таблиц версия должна быть 1
   - Для изменяемых - увеличивайте на 1
   - Обновление версии необходимо для корректного применения изменений в системе

## Настройка запросов

Если в таблице нужно отобразить данные из нескольких таблиц базы данных, то в properties описания таблицы нужно использовать инструкцию "query":

```json
{
  "query": {
    "class": "sraschet",
    "where": {
      "sraschet.last": 1
    },
    "select": {
      "sraschet": "sraschet.id,sraschet.family_id,sraschet.manager_id,sraschet.raschet_date,sraschet.loc_id"
    },
    "sortby": {
      "sraschet.id": "DESC"
    }
  }
}
```

## Типы полей таблиц

Сейчас заведены типы полей:
- text
- textarea
- boolean
- date
- autocomplete
- select

### Autocomplete

autocomplete позволяет выбрать/вставить в таблицу id записи из другой таблицы базы данных.

Для того чтобы другая таблица данных могла предоставлять autocomplete, в ее описании в properties надо вставить инструкцию "autocomplete":

```json
{
  "autocomplete": {
    "select": [
      "id",
      "name"
    ],
    "where": {
      "name:LIKE": "%query%"
    },
    "tpl": "{$name}",
    "limit": 0
  }
}
```

Параметры autocomplete:
- select — какие поля запрашивать в mysql
- where — в поле автокомплект можно вводить текст и поле будет искать этот текст в базе по инструкции в where
- tpl — строка отображаемая в поле в формате fenom
- limit — если в другой таблице всего 10 строк, то имеет смысл подружать их все сразу; а если там например 2000 контрагентов, то лучше поставить limit:20 и не загружать их все сразу
- default_row — в формате pdoFetch where для определения строки автокомплета по умолчанию

### Select

Поле select выдает обычный селект. Описания доступных опций селекта забиваются таблице "Селекты" в gtsAPI для того, чтобы их можно было переиспользовать в разных таблицах.

В "Опции в JSON" опции можно забить либо как строку с разделителями: "Мужчина,Женщина", либо как массив пар: [[1,"Прямоугольник"], [2,"Кругляк"]]

## Дополнительные возможности

### Сторонний action

Часто вместе со стандартным CRUD в таблице нужно какое-то кастомное действие. Например, перерасчет остатков на складе.

```json
{
  "loadModels": "skladmake",
  "actions": {
    "skladmake/pereraschet_sklad": {
      "groups": "ceh_boss,Administrator",
      "head": true,
      "icon": "pi pi-trash",
      "class": "p-button-rounded p-button-danger"
    }
  }
}
```

Параметры action:
- "head":true — вывести кнопку в топ таблицы
- "row":true — вывести кнопку в строке таблицы

В gtsAPI такие действия перенаправляются в сервисный файл компонента. В смысле вызывается функция handleRequest из файла "/core/components/skladmake/model/skladmake.class.php".

### Триггеры

В gtsAPI при каждом запросе подгружется класс сервисного файла пакета. Например, "/core/components/package/model/package.class.php". Если такой файл есть. Это либо пакет к которому привязана таблица, либо имена пакетов можно задать в "loadModels".

#### Регистрация триггеров

Триггеры должны быть определены в PHP-файле класса компонента (например, в `core/components/orgstructure/model/orgstructure.class.php`), а не в конфигурационном файле.

Для регистрации триггеров используйте метод `regTriggers()`, который должен возвращать массив с описанием триггеров:

```php
public function regTriggers()
{
    return [
        'DocOrderLink'=>[
            'gtsfunction'=>'triggerDocOrderLink',
        ],
    ];
}
```

То тогда когда gtsAPI выполняет какие-то стандартные действия с таблицей класса 'DocOrderLink' gtsAPI вызывает функцию 'triggerDocOrderLink' до и после выполнения этих действий.

#### Пример триггера

```php
public function triggerDocOrderLink(&$params)
{
    if($params['type'] == 'after' and $params['method'] == 'read'){
        // при запросе к таблице запрещаем чтение всем кроме администратора
        if($this->modx->user->id != 1) return $this->error("Доступ только администратору");
    }
    if($params['type'] == 'after' and $params['method'] == 'update'){
        // при изменение строки таблицы записываем в нее дату изменения
        $params['object']->date_update = date('Y-m-d');
        $params['object']->save();
    }
    return $this->success('');
}
```

#### Рекомендации по работе с триггерами

1. Триггеры должны всегда возвращать `$this->success()` или `$this->error()`.
2. Если в триггере ничего не меняется, возвращайте просто `$this->success()` без параметров.
3. Для метода read и контроллера tree.class.php:
   - Тип триггера должен быть `after`, а не `before`, чтобы он срабатывал после загрузки данных.
   - Проверяйте тип и метод в начале триггера: `if ($params['method'] != 'read' || $params['type'] != 'after') { return $this->success(); }`
   - Триггер должен возвращать отфильтрованные данные в формате: `return $this->success('', ['out' => ['rows' => $filteredRows, 'slTree' => []]])`.
4. При работе с большими объемами данных используйте pdoFetch вместо getCollection для оптимизации использования памяти.

### Внешний action

Внешний action используется тогда, когда при нажатии на кнопку нужно показать юзеру какой-то диалог. Тогда пишем компонент vue.

Пример использования PVTables в компоненте Vue:

```vue
<template>
  <PVTables table="sraschet"
    :actions="actions"
    ref="childComponentRef"
  />
  <Toast/>
</template>

<script setup>
import { PVTables } from 'pvtables/pvtables';
import { ref } from 'vue';
import Toast from 'primevue/toast';

import apiCtor from 'pvtables/api';
import { useNotifications } from "pvtables/notify";

const childComponentRef = ref()
const createDocDialog = ref(false)
const updateDocDialog = ref(false)

const api = apiCtor('DocOrderLink')

const { notify } = useNotifications();

const actions = ref({
  DocOrderLink:{
    create:{
      head:true,
      icon:"pi pi-plus",
      class:"p-button-rounded p-button-success",
      head_click: async (event,table,filters,selectedlineItems) => {
        // Код для обработки клика по кнопке создания
      }
    },
    update:{
      row:true,
      icon:"pi pi-pencil",
      class:"p-button-rounded p-button-success",
      click: async (data, columns,table,filters) => {
        // Код для обработки клика по кнопке обновления
      }
    }
  },
})
</script>
```

Пример кода для записи в таблицы:

```javascript
import apiCtor from 'pvtables/api';
const api = apiCtor('DocOrderLink')
try {
  await api.create(DocLink.value)
} catch (error) {
  notify('error', { detail: error.message });
}
```

## Безопасность

Для запросов в базу предусмотрены настройки безопасности. В "Таблицы API" можно разрешить доступ только аутентифицированным пользователям, группам пользователей или пользователям с определенными разрешениями MODX. Эти разрешения можно прописать отдельно для каждого действия в properties:

```json
{
  "actions": {
    "create": {
      "authenticated": 1,
      "groups": "ceh_boss,Administrator",
      "permitions": "list"
    }
  }
}
```
