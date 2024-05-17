import { onMounted as ve, reactive as ne, defineComponent as Le, ref as u, resolveComponent as Ee, openBlock as n, createElementBlock as w, createVNode as v, unref as i, withCtx as y, Fragment as _, renderList as B, createBlock as c, normalizeClass as se, createElementVNode as I, createTextVNode as Y, toDisplayString as M, createSlots as Ke, withKeys as ze, withModifiers as Be, createCommentVNode as Z } from "vue";
import $e from "primevue/datatable";
import L from "primevue/column";
import O from "primevue/button";
import je from "primevue/toolbar";
import J from "primevue/dialog";
import X from "primevue/inputtext";
import re from "primevue/textarea";
import $ from "primevue/inputnumber";
import ue from "primevue/inputswitch";
import { FilterOperator as de, FilterMatchMode as pe } from "primevue/api";
import ce from "pvtables/gtsdate";
import fe from "pvtables/gtsautocomplete";
import { useNotifications as Ge } from "pvtables/notify";
import He from "pvtables/api";
const qe = 3, Qe = () => {
  ve(() => {
    document.addEventListener("keydown", (h) => {
      h.code === "KeyZ" && h.ctrlKey && p(), h.code === "KeyY" && h.ctrlKey && d();
    });
  });
  const m = ne({
    undo: [],
    redo: []
  }), b = ne({
    name: "",
    details: {}
  }), g = (h) => {
    m.undo.length === qe && m.undo.shift(), m.undo.push(h);
  };
  function p() {
    m.undo.length !== 0 && (b.details = m.undo.pop(), b.name = "undo", b.details.isNew, m.redo.push(b.details));
  }
  function d() {
    m.redo.length !== 0 && (b.details = m.redo.pop(), b.name = "redo", b.details.isNew, m.undo.push(b.details));
  }
  return { undo: p, redo: d, cacheAction: g, cache: m };
}, We = (m, b) => {
  let g = [];
  return m.length && m.forEach(function(p) {
    for (let d in b)
      switch (d == "id" && (p[d] = Number(p[d])), b[d].type) {
        case "boolean":
          p.hasOwnProperty(d) && (p[d] === "0" ? p[d] = !1 : p[d] = !0);
          break;
        case "number":
        case "decimal":
          p[d] = Number(p[d]);
          break;
      }
    g.push(p);
  }), g;
}, Ye = { class: "card" }, Ze = { class: "p-3" }, Je = { class: "p-field" }, Xe = ["for"], el = ["id"], ll = { class: "confirmation-content" }, tl = /* @__PURE__ */ I("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), al = { key: 0 }, ol = { class: "confirmation-content" }, il = /* @__PURE__ */ I("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), nl = { key: 0 }, me = {
  __name: "PVTables",
  props: {
    table: {
      type: String,
      required: !0
    },
    actions: {
      type: Object
    },
    reload: {
      type: Boolean
    },
    filters: {
      type: Object,
      default: {}
    }
  },
  setup(m, { expose: b }) {
    Le({
      name: "PVTables"
    });
    const g = m, p = He(g.table), { notify: d } = Ge(), h = u(), ee = () => {
      let a = {};
      for (let o in V)
        if (g.filters.hasOwnProperty(o))
          a[o] = g.filters[o];
        else
          switch (V[o].type) {
            default:
              a[o] = {
                operator: de.AND,
                constraints: [
                  { value: null, matchMode: pe.STARTS_WITH }
                ]
              };
          }
      h.value = a;
    }, ye = async (a) => {
      x.value.filters = h.value, await S(a);
    }, be = async () => {
      ee(), x.value.filters = h.value, await S();
    }, he = (a) => "Поиск по " + a.label, j = u(), T = u(!0), G = u(0), le = u(0), x = u({}), E = u([{ field: "id", label: "ID" }]);
    let V = {};
    const P = u();
    let K = u([]);
    const H = u(!1), we = u(!1), te = u([]);
    ve(async () => {
      T.value = !0, x.value = {
        first: j.value.first,
        rows: j.value.rows,
        sortField: null,
        sortOrder: null
        // filters: filters.value
      };
      try {
        const a = await p.options();
        if (a.data.hasOwnProperty("fields")) {
          V = a.data.fields;
          let o = [], r = [];
          for (let t in V)
            V[t].field = t, V[t].hasOwnProperty("label") || (V[t].label = t), V[t].hasOwnProperty("type") || (V[t].type = "text"), r.push(V[t]), o.push(t);
          te.value = o, ee();
          let e = a.data.actions;
          for (let t in g.actions)
            e[t] = g.actions[t];
          for (let t in e) {
            let l = { ...e[t] }, f = !0;
            switch (l.action = t, t) {
              case "update":
                l.hasOwnProperty("row") || (l.row = !0), l.hasOwnProperty("icon") || (l.icon = "pi pi-pencil"), l.hasOwnProperty("class") || (l.class = "p-button-rounded p-button-success"), l.hasOwnProperty("click") || (l.click = (C) => Ue(C));
                break;
              case "delete":
                l.hasOwnProperty("row") || (l.row = !0), l.hasOwnProperty("head") || (l.head = !0), l.hasOwnProperty("icon") || (l.icon = "pi pi-trash"), l.hasOwnProperty("class") || (l.class = "p-button-rounded p-button-danger"), l.hasOwnProperty("click") || (l.click = (C) => _e(C)), l.hasOwnProperty("head_click") || (l.head_click = () => Ie()), l.hasOwnProperty("label") || (l.label = "Удалить");
                break;
              case "create":
                l.hasOwnProperty("head") || (l.head = !0), l.hasOwnProperty("icon") || (l.icon = "pi pi-plus"), l.hasOwnProperty("class") || (l.class = "p-button-rounded p-button-success"), l.hasOwnProperty("head_click") || (l.head_click = () => Fe()), l.hasOwnProperty("label") || (l.label = "Создать");
                break;
              case "subtables":
                f = !1;
                for (let C in e[t]) {
                  let k = { ...e[t][C] };
                  k.table = C, k.hasOwnProperty("row") || (k.row = !0), k.hasOwnProperty("icon") || (k.icon = "pi pi-angle-right"), k.hasOwnProperty("class") || (k.class = "p-button-rounded p-button-success"), k.hasOwnProperty("click") || (k.click = (Me) => Ve(Me, k)), H.value = !0, K.value.push(k);
                }
                break;
            }
            f && (l.hasOwnProperty("row") && (H.value = !0), l.hasOwnProperty("row") && (we.value = !0), K.value.push(l));
          }
          E.value = r;
        }
        await S();
      } catch (a) {
        d("error", { detail: a.message }, !0);
      }
    });
    const A = u({}), q = u({}), ae = u({}), oe = async (a) => {
      A.value = { ...a };
    }, Ve = async (a, o) => {
      let r = { ...A.value };
      if (r.hasOwnProperty(a.id))
        if (q.value[a.id] == o.table) {
          delete r[a.id], await oe(r);
          return;
        } else
          delete r[a.id], await oe(r), r[a.id] = !0;
      else
        r[a.id] = !0;
      if (q.value[a.id] = o.table, o.hasOwnProperty("where")) {
        let e = {};
        for (let t in o.where)
          e[t] = {
            operator: de.AND,
            constraints: [
              {
                value: a[o.where[t]],
                matchMode: pe.EQUALS
              }
            ]
          };
        ae.value[a.id] = e;
      }
      A.value = { ...r };
    }, Q = u({}), S = async (a) => {
      T.value = !0, x.value = {
        ...x.value,
        first: (a == null ? void 0 : a.first) || le.value
      };
      let o = {
        limit: x.value.rows,
        setTotal: 1,
        offset: x.value.first,
        // sortField:lazyParams.value.sortField,
        // sortOrder:lazyParams.value.sortOrder,
        multiSortMeta: x.value.multiSortMeta,
        filters: h.value
      };
      try {
        const r = await p.read(o);
        P.value = We(r.data.rows, V), Q.value = r.data.autocomplete, G.value = r.data.total, T.value = !1;
      } catch (r) {
        d("error", { detail: r.message });
      }
    }, ie = () => {
      S();
    };
    b({ refresh: ie });
    const { cacheAction: ke, cache: sl } = Qe(), z = async (a) => {
      let { data: o, newValue: r, field: e } = a;
      const t = {
        id: o.id,
        [e]: r
      };
      ke({ type: "update", payload: t });
      try {
        (await p.update(t)).success && (o[e] = r);
      } catch (l) {
        a.preventDefault(), d("error", { detail: l.message }, !0);
      }
    }, ge = async (a) => {
      x.value = a, await S(a);
    }, xe = async (a) => {
      x.value = a, await S(a);
    }, Pe = (a) => a.toString().replace(".", ","), s = u({}), W = u(!1), D = u(!1), Ue = (a) => {
      s.value = { ...a }, D.value = !0;
    }, Oe = () => {
      D.value = !1, W.value = !1;
    }, Ce = async () => {
      if (W.value = !0, s.value.id)
        try {
          await p.update(s.value), P.value[De(Number(s.value.id))] = s.value, D.value = !1, s.value = {};
        } catch (a) {
          d("error", { detail: a.message });
        }
      else
        try {
          await p.create(), T.value = !0, D.value = !1, s.value = {};
        } catch (a) {
          d("error", { detail: a.message });
        }
    }, De = (a) => {
      let o = -1;
      for (let r = 0; r < P.value.length; r++)
        if (P.value[r].id === a) {
          o = r;
          break;
        }
      return o;
    }, Fe = () => {
      s.value = {}, W.value = !1, D.value = !0;
    }, N = u(!1), R = u(!1), _e = (a) => {
      s.value = a, N.value = !0;
    }, Se = async () => {
      try {
        await p.delete({ ids: s.value.id }), P.value = P.value.filter(
          (a) => a.id !== s.value.id
        ), N.value = !1, s.value = {};
      } catch (a) {
        d("error", { detail: a.message });
      }
    }, Ie = () => {
      U.value && U.value.length && (R.value = !0);
    }, Te = async () => {
      const a = U.value.map((o) => o.id).join(",");
      try {
        await p.delete({ ids: a }), P.value = P.value.filter(
          (o) => !U.value.includes(o)
        ), R.value = !1, U.value = null;
      } catch (o) {
        d("error", { detail: o.message });
      }
    }, U = u(), F = u(!1), Ae = (a) => {
      F.value = a.checked, F.value ? (F.value = !0, U.value = P.value) : (F.value = !1, U.value = []);
    }, Ne = () => {
      F.value = U.value.length === G.value;
    }, Re = () => {
      F.value = !1;
    };
    return (a, o) => {
      const r = Ee("PVTables", !0);
      return n(), w("div", Ye, [
        v(i(je), { class: "p-mb-4" }, {
          start: y(() => [
            (n(!0), w(_, null, B(i(K).filter((e) => e.head), (e) => (n(), c(i(O), {
              icon: e.icon,
              label: e.label,
              class: se(e.class),
              onClick: e.head_click
            }, null, 8, ["icon", "label", "class", "onClick"]))), 256))
          ]),
          end: y(() => [
            v(i(O), {
              icon: "pi pi-refresh",
              class: "p-button-rounded p-button-success",
              onClick: ie
            }),
            v(i(O), {
              type: "button",
              icon: "pi pi-filter-slash",
              onClick: o[0] || (o[0] = (e) => be())
            })
          ]),
          _: 1
        }),
        v(i($e), {
          value: P.value,
          lazy: "",
          paginator: "",
          first: le.value,
          rows: 10,
          rowsPerPageOptions: [10, 60, 30, 10],
          ref_key: "dt",
          ref: j,
          dataKey: "id",
          totalRecords: G.value,
          loading: T.value,
          onPage: o[2] || (o[2] = (e) => ge(e)),
          onSort: o[3] || (o[3] = (e) => xe(e)),
          sortMode: "multiple",
          editMode: "cell",
          onCellEditComplete: z,
          selection: U.value,
          "onUpdate:selection": o[4] || (o[4] = (e) => U.value = e),
          selectAll: F.value,
          onSelectAllChange: Ae,
          onRowSelect: Ne,
          onRowUnselect: Re,
          filters: h.value,
          "onUpdate:filters": o[5] || (o[5] = (e) => h.value = e),
          filterDisplay: "menu",
          globalFilterFields: te.value,
          onFilter: o[6] || (o[6] = (e) => ye(e)),
          expandedRows: A.value,
          "onUpdate:expandedRows": o[7] || (o[7] = (e) => A.value = e),
          showGridlines: ""
        }, {
          expansion: y((e) => [
            I("div", Ze, [
              v(r, {
                table: q.value[e.data.id],
                filters: ae.value[e.data.id]
              }, null, 8, ["table", "filters"])
            ])
          ]),
          default: y(() => [
            v(i(L), {
              selectionMode: "multiple",
              headerStyle: "width: 3rem"
            }),
            (n(!0), w(_, null, B(E.value.filter((e) => e.modal_only != !0), (e) => (n(), w(_, {
              key: e.field
            }, [
              e.field == "id" ? (n(), c(i(L), {
                key: 0,
                field: "id",
                header: "id",
                style: { padding: "1rem 10px 1rem 10px" },
                sortable: ""
              }, {
                body: y(({ data: t, field: l }) => [
                  Y(M(t[l]), 1)
                ]),
                _: 1
              })) : e.type == "autocomplete" ? (n(), c(i(L), {
                key: 1,
                field: e.field,
                header: e.label,
                style: { "min-width": "350px" }
              }, {
                body: y(({ data: t, field: l }) => {
                  var f;
                  return [
                    v(i(fe), {
                      table: e.table,
                      id: t[l],
                      "onUpdate:id": (C) => t[l] = C,
                      options: (f = Q.value[l]) == null ? void 0 : f.rows,
                      onSetValue: (C) => z({ data: t, field: l, newValue: t[l] })
                    }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue"])
                  ];
                }),
                _: 2
              }, 1032, ["field", "header"])) : (n(), c(i(L), {
                key: 2,
                field: e.field,
                header: e.label,
                style: { "min-width": "12rem" },
                sortable: ""
              }, Ke({
                body: y(({ data: t, field: l }) => [
                  e.type == "decimal" ? (n(), w(_, { key: 0 }, [
                    Y(M(Pe(t[l])), 1)
                  ], 64)) : e.type == "boolean" ? (n(), c(i(ue), {
                    key: 1,
                    modelValue: t[l],
                    "onUpdate:modelValue": (f) => t[l] = f,
                    onKeydown: o[1] || (o[1] = ze(Be(() => {
                    }, ["stop"]), ["tab"])),
                    onChange: (f) => z({ data: t, field: l, newValue: t[l] })
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "onChange"])) : e.type === "date" ? (n(), c(i(ce), {
                    key: 2,
                    "model-value": t[l],
                    "onUpdate:modelValue": (f) => z({ data: t, field: l, newValue: f })
                  }, null, 8, ["model-value", "onUpdate:modelValue"])) : (n(), w(_, { key: 3 }, [
                    Y(M(t[l]), 1)
                  ], 64))
                ]),
                filter: y(({ filterModel: t }) => [
                  v(i(X), {
                    modelValue: t.value,
                    "onUpdate:modelValue": (l) => t.value = l,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: he(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, [
                ["boolean", "date"].includes(e.type) ? void 0 : {
                  name: "editor",
                  fn: y(({ data: t, field: l }) => [
                    e.type == "textarea" ? (n(), c(i(re), {
                      key: 0,
                      modelValue: t[l],
                      "onUpdate:modelValue": (f) => t[l] = f,
                      rows: "1"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "number" ? (n(), c(i($), {
                      key: 1,
                      modelValue: t[l],
                      "onUpdate:modelValue": (f) => t[l] = f
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "decimal" ? (n(), c(i($), {
                      key: 2,
                      modelValue: t[l],
                      "onUpdate:modelValue": (f) => t[l] = f,
                      minFractionDigits: e.FractionDigits,
                      maxFractionDigits: e.FractionDigits
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits"])) : (n(), c(i(X), {
                      key: 3,
                      modelValue: t[l],
                      "onUpdate:modelValue": (f) => t[l] = f
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]))
                  ]),
                  key: "0"
                }
              ]), 1032, ["field", "header"]))
            ], 64))), 128)),
            H.value ? (n(), c(i(L), {
              key: 0,
              exportable: !1,
              style: { "white-space": "nowrap" }
            }, {
              body: y((e) => [
                (n(!0), w(_, null, B(i(K).filter((t) => t.row), (t) => (n(), c(i(O), {
                  icon: t.icon,
                  class: se(t.class),
                  onClick: (l) => t.click(e.data, E.value)
                }, null, 8, ["icon", "class", "onClick"]))), 256))
              ]),
              _: 1
            })) : Z("", !0)
          ]),
          _: 1
        }, 8, ["value", "first", "totalRecords", "loading", "selection", "selectAll", "filters", "globalFilterFields", "expandedRows"]),
        v(i(J), {
          visible: D.value,
          "onUpdate:visible": o[8] || (o[8] = (e) => D.value = e),
          style: { width: "450px" },
          header: "Редактировать",
          modal: !0,
          class: "p-fluid"
        }, {
          footer: y(() => [
            v(i(O), {
              label: "Отмена",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: Oe
            }),
            v(i(O), {
              label: "Сохранить",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Ce
            })
          ]),
          default: y(() => [
            (n(!0), w(_, null, B(E.value.filter((e) => e.table_only != !0), (e) => {
              var t;
              return n(), w("div", Je, [
                I("label", {
                  for: e.field
                }, M(e.label), 9, Xe),
                e.field == "id" ? (n(), w("p", {
                  key: 0,
                  id: e.field
                }, M(s.value[e.field]), 9, el)) : e.type == "textarea" ? (n(), c(i(re), {
                  key: 1,
                  id: e.field,
                  modelValue: s.value[e.field],
                  "onUpdate:modelValue": (l) => s.value[e.field] = l,
                  modelModifiers: { trim: !0 }
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue"])) : e.type == "number" ? (n(), c(i($), {
                  key: 2,
                  id: e.field,
                  modelValue: s.value[e.field],
                  "onUpdate:modelValue": (l) => s.value[e.field] = l
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue"])) : e.type == "autocomplete" ? (n(), c(i(fe), {
                  key: 3,
                  id: s.value[e.field],
                  "onUpdate:id": (l) => s.value[e.field] = l,
                  table: e.table,
                  options: (t = Q.value[e.field]) == null ? void 0 : t.rows
                }, null, 8, ["id", "onUpdate:id", "table", "options"])) : e.type == "decimal" ? (n(), c(i($), {
                  key: 4,
                  id: e.field,
                  modelValue: s.value[e.field],
                  "onUpdate:modelValue": (l) => s.value[e.field] = l,
                  minFractionDigits: e.FractionDigits,
                  maxFractionDigits: e.FractionDigits
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits"])) : e.type == "boolean" ? (n(), c(i(ue), {
                  key: 5,
                  id: e.field,
                  modelValue: s.value[e.field],
                  "onUpdate:modelValue": (l) => s.value[e.field] = l
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue"])) : e.type === "date" ? (n(), c(i(ce), {
                  key: 6,
                  modelValue: s.value[e.field],
                  "onUpdate:modelValue": (l) => s.value[e.field] = l
                }, null, 8, ["modelValue", "onUpdate:modelValue"])) : (n(), c(i(X), {
                  key: 7,
                  id: e.field,
                  modelValue: s.value[e.field],
                  "onUpdate:modelValue": (l) => s.value[e.field] = l,
                  modelModifiers: { trim: !0 }
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue"]))
              ]);
            }), 256))
          ]),
          _: 1
        }, 8, ["visible"]),
        v(i(J), {
          visible: N.value,
          "onUpdate:visible": o[10] || (o[10] = (e) => N.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: y(() => [
            v(i(O), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: o[9] || (o[9] = (e) => N.value = !1)
            }),
            v(i(O), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Se
            })
          ]),
          default: y(() => [
            I("div", ll, [
              tl,
              s.value ? (n(), w("span", al, "Вы хотите удалить эту запись?")) : Z("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"]),
        v(i(J), {
          visible: R.value,
          "onUpdate:visible": o[12] || (o[12] = (e) => R.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: y(() => [
            v(i(O), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: o[11] || (o[11] = (e) => R.value = !1)
            }),
            v(i(O), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Te
            })
          ]),
          default: y(() => [
            I("div", ol, [
              il,
              s.value ? (n(), w("span", nl, "Вы хотите удалить отмеченные записи?")) : Z("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"])
      ]);
    };
  }
}, xl = {
  install: (m, b) => {
    m.component(me.name, me);
  }
};
export {
  me as PVTables,
  xl as default
};
