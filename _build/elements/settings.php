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
        "showLog": 1,
        "loadModels": "gtsapi,gtsshop",
        "selects": {
          "gtsAPIPackage": {
            "type": "autocomplect",
            "pdoTools": {
              "class": "gtsAPIPackage"
            },
            "content": "{$name}"
          },
          "group_field": {
            "type": "autocomplect",
            "pdoTools": {
              "class": "gtsAPIFieldGroup"
            },
            "content": "{$name}"
          },
          "field": {
            "type": "autocomplect",
            "pdoTools": {
              "class": "gtsAPIField"
            },
            "content": "{$title}"
          },
          "shop_field": {
            "type": "autocomplect",
            "pdoTools": {
              "class": "gsParam"
            },
            "content": "{$title}"
          },
          "dbtype": {
            "type": "data",
            "rows": [
              [
                "int",
                "int"
              ],
              [
                "varchar",
                "varchar"
              ],
              [
                "text",
                "text"
              ],
              [
                "decimal",
                "decimal"
              ],
              [
                "tinyint",
                "tinyint"
              ],
              [
                "date",
                "date"
              ],
              [
                "datetime",
                "datetime"
              ]
            ]
          },
          "table_type":{
            "type": "data",
            "rows": [
              [
                1,
                "Таблица"
              ],
              [
                2,
                "JSON"
              ],
              [
                3,
                "Tree"
              ]
            ]
          },
          "field_type": {
            "type": "data",
            "rows": [
              [
                "text",
                "text"
              ],
              [
                "textarea",
                "textarea"
              ],
              [
                "number",
                "number"
              ],
              [
                "decimal",
                "decimal"
              ],
              [
                "autocomplete",
                "autocomplete"
              ],
              [
                "select",
                "select"
              ],
              [
                "date",
                "date"
              ],
              [
                "datetime",
                "datetime"
              ],
              [
                "boolean",
                "boolean"
              ]
            ]
          },
          "dbindex": {
            "type": "data",
            "rows": [
              [
                "no",
                "no"
              ],
              [
                "INDEX",
                "INDEX"
              ]
            ]
          }
        },
        "tabs": {
          "gtsAPITable": {
            "label": "Таблицы АПИ",
            "table": {
              "subtables": {
                "gtsAPIUniTreeClass": {
                  "class": "gtsAPIUniTreeClass",
                  "sub_where": {
                    "table_id": "id"
                  },
                  "actions": {
                    "create": [],
                    "update": [],
                    "remove": []
                  },
                  "pdoTools": {
                    "class": "gtsAPIUniTreeClass"
                  },
                  "checkbox": 0,
                  "autosave": 1,
                  "row": {
                    "cols": {
                      "id": {
                        "label": "id"
                      },
                      "table_id": {
                        "label": "Дерево АПИ",
                        "filter": 1,
                        "edit": {
                          "type": "hidden"
                        }
                      },
                      "class": {
                        "label": "Класс таблицы",
                        "filter": 1
                      },
                      "exdended_modresource": {
                        "label": "Таблица расширение modResource",
                        "edit": {
                          "type": "checkbox"
                        },
                        "filter": 1
                      },
                      "title_template": {
                        "label": "Шаблон заголовка(fenom)",
                        "filter": 1
                      }
                    }
                  }
                }
              },
              "class": "gtsAPITable",
              "actions": {
                "create": {
                  "modal": {
                    "tabs": {
                      "main": {
                        "label": "main",
                        "fields": "id,package_id,table,class,autocomplete_field,authenticated,groups,permitions,version,active"
                      },
                      "properties": {
                        "label": "properties",
                        "fields": "type,properties"
                      }
                    }
                  }
                },
                "update": {
                  "modal": {
                    "tabs": {
                      "main": {
                        "label": "main",
                        "fields": "id,package_id,table,class,autocomplete_field,authenticated,groups,permitions,version,active"
                      },
                      "properties": {
                        "label": "properties",
                        "fields": "type,properties"
                      }
                    }
                  }
                },
                "copy": [],
                "remove": [],
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
                  "row": {
                    "title": "Сгенирировать поля"
                  },
                  "icon": "glyphicon glyphicon-menu-hamburger"
                },
                "subtable": {
                  "subtable_name": "gtsAPIUniTreeClass"
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
                  "label": "Пакет",
                  "edit": {
                    "type": "select",
                    "select": "gtsAPIPackage"
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
                "type": {
                  "label": "Тип таблицы",
                  "edit": {
                    "type": "select",
                    "select": "table_type"
                  },
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
                "remove": []
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
                "copy": [],
                "export_select": {
                  "action": "gtsapi/export_select",
                  "title": "Экспорт-импорт селекта",
                  "cls": "btn btn-primary",
                  "row": [],
                  "icon": "glyphicon glyphicon-download"
                },
                "remove": []
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
          "gtsAPIFieldTable": {
            "label": "Таблицы допполей",
            "table": {
              "class": "gtsAPIFieldTable",
              "actions": {
                "create": [],
                "update": [],
                "subtable": {
                  "subtable_name": "gtsAPIFieldGroupTableLink"
                },
                "remove": []
              },
              "subtables": {
                "gtsAPIFieldGroupTableLink": {
                  "class": "gtsAPIFieldGroupTableLink",
                  "sub_where": {
                    "table_field_id": "id"
                  },
                  "actions": {
                    "create": [],
                    "update": [],
                    "remove": []
                  },
                  "pdoTools": {
                    "class": "gtsAPIFieldGroupTableLink"
                  },
                  "checkbox": 0,
                  "autosave": 1,
                  "row": {
                    "cols": {
                      "id": {
                        "label": "id"
                      },
                      "table_field_id": {
                        "type": "hidden"
                      },
                      "group_field_id": {
                        "label": "Группа полей",
                        "filter": 1,
                        "edit": {
                          "type": "select",
                          "select": "group_field"
                        }
                      }
                    }
                  }
                }
              },
              "pdoTools": {
                "class": "gtsAPIFieldTable"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {},
                "name_table": {
                  "label": "Имя таблицы добавить допполя",
                  "filter": 1
                },
                "add_base": {
                  "label": "Добавить в базу",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 0,
                  "filter": 1
                },
                "add_table": {
                  "label": "Добавить в конфиг таблицы",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 0,
                  "filter": 1
                },
                "only_text": {
                  "label": "Сделать все поля в таблице текстовые",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 0,
                  "filter": 1
                },
                "after_field": {
                  "label": "После поля",
                  "filter": 1
                },
                "desc": {
                  "label": "Описание",
                  "edit": {
                    "type": "textarea"
                  }
                }
              }
            }
          },
          "gtsAPIFieldGroup": {
            "label": "Группы допполей",
            "table": {
              "class": "gtsAPIFieldGroup",
              "actions": {
                "create": [],
                "update": [],
                "subtable": {
                  "subtable_name": "gtsAPIFieldGroupLink"
                },
                "subtable_shop": {
                  "action": "getTable/subtable",
                  "icon": [
                    "fa fa-cog",
                    "fa fa-cog"
                  ],
                  "subtable_name": "gtsAPIFieldShopGroupLink"
                },
                "remove": []
              },
              "subtables": {
                "gtsAPIFieldGroupLink": {
                  "class": "gtsAPIFieldGroupLink",
                  "sub_where": {
                    "group_field_id": "id"
                  },
                  "actions": {
                    "create": [],
                    "update": [],
                    "remove": []
                  },
                  "pdoTools": {
                    "class": "gtsAPIFieldGroupLink"
                  },
                  "checkbox": 0,
                  "autosave": 1,
                  "row": {
                    "cols": {
                      "id": {
                        "label": "id"
                      },
                      "group_field_id": {
                        "type": "hidden"
                      },
                      "field_id": {
                        "label": "Поле",
                        "filter": 1,
                        "edit": {
                          "type": "select",
                          "select": "field"
                        }
                      }
                    }
                  }
                },
                "gtsAPIFieldShopGroupLink": {
                  "class": "gtsAPIFieldShopGroupLink",
                  "sub_where": {
                    "group_field_id": "id"
                  },
                  "actions": {
                    "create": [],
                    "update": [],
                    "remove": []
                  },
                  "pdoTools": {
                    "class": "gtsAPIFieldShopGroupLink"
                  },
                  "checkbox": 0,
                  "autosave": 1,
                  "row": {
                    "cols": {
                      "id": {
                        "label": "id"
                      },
                      "group_field_id": {
                        "type": "hidden"
                      },
                      "field_id": {
                        "label": "Поле",
                        "filter": 1,
                        "edit": {
                          "type": "select",
                          "select": "shop_field"
                        }
                      }
                    }
                  }
                }
              },
              "pdoTools": {
                "class": "gtsAPIFieldGroup"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {},
                "name": {
                  "label": "Имя группы допполей",
                  "filter": 1
                },
                "from_table": {
                  "label": "Имя таблицы допполей. Для gtsShop",
                  "default": "gtsAPIField",
                  "filter": 1
                },
                "link_group_table": {
                  "label": "Имя таблицы связи полей и группы. Для gtsShop",
                  "default": "gtsAPIFieldGroupLink",
                  "filter": 1
                },
                "all": {
                  "label": "Все поля таблицы",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 0,
                  "filter": 1
                }
              }
            }
          },
          "gtsAPIField": {
            "label": "Допполя",
            "table": {
              "class": "gtsAPIField",
              "actions": {
                "create": {
                  "modal": {
                    "tabs": {
                      "main": {
                        "label": "Основные",
                        "fields": "id,title,name,dbtype,dbprecision,dbnull,dbdefault,dbindex"
                      },
                      "setting": {
                        "label": "Настройки",
                        "fields": "rank,,default,field_type,after_field,modal_only,table_only"
                      },
                      "dop": {
                        "label": "Доп. настройки",
                        "fields": "gtsapi_config,description"
                      }
                    }
                  }
                },
                "update": {
                  "modal": {
                    "tabs": {
                      "main": {
                        "label": "Основные",
                        "fields": "id,title,name,dbtype,dbprecision,dbnull,dbdefault,dbindex"
                      },
                      "setting": {
                        "label": "Настройки",
                        "fields": "rank,default,field_type,after_field,modal_only,table_only"
                      },
                      "dop": {
                        "label": "Доп. настройки",
                        "fields": "gtsapi_config,description"
                      }
                    }
                  }
                },
                "remove": []
              },
              "pdoTools": {
                "class": "gtsAPIField"
              },
              "checkbox": 0,
              "autosave": 1,
              "row": {
                "id": [],
                "title": {
                  "label": "Название",
                  "filter": 1
                },
                "name": {
                  "filter": 1
                },
                "dbtype": {
                  "edit": {
                    "type": "select",
                    "select": "dbtype"
                  },
                  "filter": 1
                },
                "dbprecision": {
                  "filter": 1
                },
                "dbnull": {
                  "edit": {
                    "type": "checkbox"
                  },
                  "filter": 1
                },
                "dbdefault": {
                  "edit": {
                    "type": "textarea"
                  },
                  "default": "none",
                  "filter": 1
                },
                "dbindex": {
                  "edit": {
                    "type": "select",
                    "select": "dbindex"
                  },
                  "filter": 1
                },
                "rank": {
                  "filter": 1
                },
                "default": {
                  "label": "По умолчанию",
                  "filter": 1
                },
                "field_type": {
                  "edit": {
                    "type": "select",
                    "select": "field_type"
                  },
                  "modal_only": 1,
                  "filter": 1
                },
                "after_field": {
                  "label": "После поля",
                  "filter": 1
                },
                "modal_only": {
                  "label": "Только в форме",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 0,
                  "modal_only": 1,
                  "filter": 1
                },
                "table_only": {
                  "label": "Только в таблице",
                  "edit": {
                    "type": "checkbox"
                  },
                  "default": 0,
                  "modal_only": 1,
                  "filter": 1
                },
                "gtsapi_config": {
                  "label": "gtsapi_config",
                  "edit": {
                    "type": "textarea"
                  },
                  "modal_only": 1,
                  "filter": 1
                },
                "description": {
                  "label": "Описание",
                  "edit": {
                    "type": "textarea"
                  },
                  "modal_only": 1,
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
                "copy": {
                  "child": {
                    "subtables": [
                      "gtsAPIAction"
                    ]
                  }
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
                  "multiple": {
                    "title": "Сгенирировать правила"
                  },
                  "icon": "glyphicon glyphicon-menu-hamburger"
                },
                "subtable": {
                  "subtable_name": "gtsAPIAction"
                },
                "remove": []
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