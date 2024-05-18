<?php

return [
    /*'combo_boolean' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'gtsapi_main',
    ],*/
    'frontend_js' => [
        'xtype' => 'textfield',
        'value' => '[[+jsUrl]]web/default.js',
        'area' => 'gtsapi_main',
    ],
    'day_exp' => [
      'xtype' => 'textfield',
      'value' => '30',
      'area' => 'gtsapi_main',
    ],
    'only_jwt' => [
      'xtype' => 'combo-boolean',
      'value' => 0,
      'area' => 'gtsapi_main',
    ],
    'load_vue' => [
      'xtype' => 'combo-boolean',
      'value' => 1,
      'area' => 'gtsapi_main',
    ],
    'debug_mode' => [
      'xtype' => 'combo-boolean',
      'value' => 1,
      'area' => 'gtsapi_main',
    ],
    'admin' => [
      'xtype' => 'textfield',
      'value' => '{
        "loadModels": "gtsapi",
        "selects":{
          "gtsAPIPackage":{
            "type":"autocomplect",
            "pdoTools":{
              "class":"gtsAPIPackage"
            },
            "content":"{$name}"
          }
        },
        "tabs": {
          "gtsAPITable": {
            "label": "Таблицы АПИ",
            "table": {
              "class": "gtsAPITable",
              "actions": {
                "create": [],
                "update": [],
                "copy":[],
                "remove":[],
                "export_table": {
                  "action": "gtsapi/export_table",
                  "title": "Экспорт-импорт таблицы",
                  "cls": "btn btn-primary",
                  "row": [],
                  "icon": "glyphicon glyphicon-download"
                },
                "gen_fields": {
                  "action": "gtsapi/gen_fields",
                  "title": "",
                  "cls": "btn btn-primary",
                  "row": {"title":"Сгенирировать поля"},
                  "icon": "glyphicon glyphicon-menu-hamburger"
                }
              },
              "pdoTools": {
                "class": "gtsAPITable"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {},
                "package_id": {
                  "label":"Пакет",
                  "edit":{
                    "type":"select",
                    "select":"gtsAPIPackage"
                  },
                  "filter": 1
                },
                "table": {
                  "label": "Имя таблицы",
                  "filter": 1
                },
                "class": {
                  "label": "Класс таблицы",
                  "filter": 1
                },
                "autocomplete_field": {
                  "label": "Имя поля автокомплект",
                  "filter": 1
                },
                "authenticated": {
                  "label": "Доступ только для авторизированных",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 1,
                  "filter": 1
                },
                "groups": {
                  "label": "Ограничение только для групп пользователей (имена груп через запятую)",
                  "edit": {
                    "type": "textarea"
                  }
                },
                "permitions": {
                  "label": "Разрешения (имена разрешений MODX через запятую)",
                  "edit": {
                    "type": "textarea"
                  }
                },
                "properties": {
                  "label": "Свойства",
                  "edit": {
                    "type": "textarea",
                    "editor": "ace",
                    "editor_mode": "json",
                    "skip_sanitize": 1
                  }
                },
                "version": {
                  "label": "Версия",
                  "edit": {
                    "type": "text"
                  },
                  "default": 0,
                  "filter": 1
                },
                "active": {
                  "label": "Активно",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 1,
                  "filter": 1
                }
              }
            }
          },
          "gtsAPIPackage": {
            "label": "Пакеты MODX",
            "table": {
              "class": "gtsAPIPackage",
              "actions": {
                "create": [],
                "update": [],
                "remove":[]
              },
              "pdoTools": {
                "class": "gtsAPIPackage"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {},
                "name": {
                  "label": "Имя",
                  "filter": 1
                }
              }
            }
          },
          "gtsAPISelect": {
            "label": "Селекты",
            "table": {
              "class": "gtsAPISelect",
              "actions": {
                "create": [],
                "update": [],
                "copy":[],
                "export_select": {
                  "action": "gtsapi/export_select",
                  "title": "Экспорт-импорт селекта",
                  "cls": "btn btn-primary",
                  "row": [],
                  "icon": "glyphicon glyphicon-download"
                },
                "remove":[]
              },
              "pdoTools": {
                "class": "gtsAPISelect"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {},
                "field": {
                  "label": "Имя поля",
                  "filter": 1
                },
                "rows": {
                  "label": "Опции в JSON",
                  "edit": {
                    "type": "textarea",
                    "editor": "ace",
                    "editor_mode": "json",
                    "skip_sanitize": 1
                  }
                },
                "active": {
                  "label": "Активно",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 1,
                  "filter": 1
                }
              }
            }
          },
          "gtsAPIRule": {
            "label": "Правила АПИ",
            "table": {
              "subtables": {
                "gtsAPIAction": {
                  "class": "gtsAPIAction",
                  "sub_where": {
                    "rule_id": "id"
                  },
                  "actions": {
                    "create": [],
                    "update": [],
                    "remove": []
                  },
                  "pdoTools": {
                    "class": "gtsAPIAction"
                  },
                  "checkbox": 0,
                  "autosave": 1,
                  "row": {
                    "cols": {
                      "id": {
                        "label": "id"
                      },
                      "rule_id": {
                        "label": "Правило АПИ",
                        "filter": 1,
                        "edit": {
                          "type": "hidden"
                        }
                      },
                      "gtsaction": {
                        "label": "Действие",
                        "filter": 1
                      },
                      "authenticated": {
                        "label": "Доступ только для авторизированных",
                        "edit": {
                          "type": "checkbox"
                        },
                        "default": 1,
                        "filter": 1
                      },
                      "groups": {
                        "label": "Ограничение только для групп пользователей (имена груп через запятую)",
                        "edit": {
                          "type": "textarea"
                        }
                      },
                      "permitions": {
                        "label": "Разрешения (имена разрешений MODX через запятую)",
                        "edit": {
                          "type": "textarea"
                        }
                      },
                      "processor": {
                        "label": "Процессор MODX",
                        "edit": {
                          "type": "textarea"
                        }
                      },
                      "active": {
                        "label": "Активно",
                        "edit": {
                          "type": "checkbox"
                        },
                        "default": 1,
                        "filter": 1
                      }
                    }
                  }
                }
              },
              "class": "gtsAPIRule",
              "actions": {
                "create": [],
                "update": [],
                "copy":{
                  "child":{"subtables":["gtsAPIAction"]}
                },
                "export_rule": {
                  "action": "gtsapi/export_rule",
                  "title": "Экспорт-импорт правила",
                  "cls": "btn btn-primary",
                  "row": [],
                  "icon": "glyphicon glyphicon-download"
                },
                "gen_rules": {
                  "action": "gtsapi/gen_rules",
                  "title": "",
                  "cls": "btn btn-primary",
                  "multiple": {"title":"Сгенирировать правила"},
                  "icon": "glyphicon glyphicon-menu-hamburger"
                },
                "subtable": {
                  "subtable_name": "gtsAPIAction"
                },
                "remove":[]
              },
              "pdoTools": {
                "class": "gtsAPIRule"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {},
                "point": {
                  "label": "Точка монтирования",
                  "filter": 1
                },
                "description": {
                  "label": "Описание",
                  "edit": {
                    "type": "textarea"
                  }
                },
                "packages": {
                  "label": "Подгружаемые пакеты MODX",
                  "filter": 1
                },
                "class": {
                  "label": "Класс таблицы",
                  "filter": 1
                },
                "pdoTools": {
                  "label": "pdoTools",
                  "edit": {
                    "type": "textarea",
                    "editor": "ace",
                    "editor_mode": "xml",
                    "skip_sanitize": 1
                  }
                },
                "controller_class": {
                  "label": "Контроллер АПИ",
                  "filter": 1
                },
                "controller_path": {
                  "label": "Контроллер АПИ путь",
                  "filter": 1
                },
                "authenticated": {
                  "label": "Доступ только для авторизированных",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 1,
                  "filter": 1
                },
                "groups": {
                  "label": "Ограничение только для групп пользователей (имена груп через запятую)",
                  "edit": {
                    "type": "textarea"
                  }
                },
                "permitions": {
                  "label": "Разрешения (имена разрешений MODX через запятую)",
                  "edit": {
                    "type": "textarea"
                  }
                },
                "active": {
                  "label": "Активно",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 1,
                  "filter": 1
                }
              }
            }
          }
        }
      }',
      'area' => 'gtsapi_main',
  ],

];