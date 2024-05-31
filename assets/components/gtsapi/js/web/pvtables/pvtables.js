import { onMounted as we, reactive as ue, defineComponent as Ke, ref as u, resolveComponent as $e, openBlock as n, createElementBlock as k, createVNode as m, unref as i, withCtx as y, Fragment as D, renderList as G, createBlock as c, normalizeClass as M, createElementVNode as T, createTextVNode as te, toDisplayString as E, createSlots as Be, withKeys as Ge, withModifiers as je, createCommentVNode as ae } from "vue";
import He from "primevue/datatable";
import I from "primevue/column";
import C from "primevue/button";
import qe from "primevue/toolbar";
import oe from "primevue/dialog";
import z from "primevue/inputtext";
import pe from "primevue/textarea";
import j from "primevue/inputnumber";
import ce from "primevue/inputswitch";
import { FilterOperator as fe, FilterMatchMode as me } from "primevue/api";
import ye from "pvtables/gtsdate";
import ve from "pvtables/gtsautocomplete";
import be from "pvtables/gtsselect";
import { useNotifications as Qe } from "pvtables/notify";
import We from "pvtables/api";
const Ye = 3, Ze = () => {
  we(() => {
    document.addEventListener("keydown", (w) => {
      w.code === "KeyZ" && w.ctrlKey && f(), w.code === "KeyY" && w.ctrlKey && p();
    });
  });
  const v = ue({
    undo: [],
    redo: []
  }), h = ue({
    name: "",
    details: {}
  }), U = (w) => {
    v.undo.length === Ye && v.undo.shift(), v.undo.push(w);
  };
  function f() {
    v.undo.length !== 0 && (h.details = v.undo.pop(), h.name = "undo", h.details.isNew, v.redo.push(h.details));
  }
  function p() {
    v.redo.length !== 0 && (h.details = v.redo.pop(), h.name = "redo", h.details.isNew, v.undo.push(h.details));
  }
  return { undo: f, redo: p, cacheAction: U, cache: v };
}, Je = (v, h) => {
  let U = [];
  return v.length && v.forEach(function(f) {
    for (let p in h)
      switch (p == "id" && (f[p] = Number(f[p])), h[p].type) {
        case "boolean":
          f.hasOwnProperty(p) && (f[p] === "0" ? f[p] = !1 : f[p] = !0);
          break;
        case "number":
        case "decimal":
          f[p] = Number(f[p]);
          break;
      }
    U.push(f);
  }), U;
}, Xe = { class: "card" }, el = { class: "p-3" }, ll = { class: "p-field" }, tl = ["for"], al = ["id"], ol = { class: "confirmation-content" }, il = /* @__PURE__ */ T("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), sl = { key: 0 }, nl = { class: "confirmation-content" }, rl = /* @__PURE__ */ T("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), dl = { key: 0 }, he = {
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
  setup(v, { expose: h }) {
    Ke({
      name: "PVTables"
    });
    const U = v, f = We(U.table), { notify: p } = Qe(), w = u(), ie = () => {
      let a = {};
      for (let o in b)
        if (U.filters.hasOwnProperty(o))
          a[o] = U.filters[o];
        else
          switch (b[o].type) {
            default:
              a[o] = {
                operator: fe.AND,
                constraints: [
                  { value: null, matchMode: me.STARTS_WITH }
                ]
              };
          }
      w.value = a;
    }, Ve = async (a) => {
      x.value.filters = w.value, await F(a);
    }, ke = async () => {
      ie(), x.value.filters = w.value, await F();
    }, H = (a) => "Поиск по " + a.label, q = u(), K = u(!0), Q = u(0), se = u(0), x = u({}), $ = u([{ field: "id", label: "ID" }]);
    let b = {};
    const P = u();
    let B = u([]);
    const W = u(!1), ge = u(!1), ne = u([]), Y = u({});
    we(async () => {
      K.value = !0, x.value = {
        first: q.value.first,
        rows: q.value.rows,
        sortField: null,
        sortOrder: null
        // filters: filters.value
      };
      try {
        const a = await f.options();
        if (a.data.hasOwnProperty("fields")) {
          b = a.data.fields;
          let o = [], d = [];
          for (let t in b)
            b[t].field = t, b[t].hasOwnProperty("label") || (b[t].label = t), b[t].hasOwnProperty("type") || (b[t].type = "text"), b[t].hasOwnProperty("readonly") && (b[t].readonly === !0 || b[t].readonly == 1 ? b[t].readonly = !0 : b[t].readonly = !1), d.push(b[t]), o.push(t);
          ne.value = o, ie();
          let e = a.data.actions;
          for (let t in U.actions)
            e[t] = U.actions[t];
          for (let t in e) {
            let l = { ...e[t] }, s = !0;
            switch (l.action = t, t) {
              case "update":
                l.hasOwnProperty("row") || (l.row = !0), l.hasOwnProperty("icon") || (l.icon = "pi pi-pencil"), l.hasOwnProperty("class") || (l.class = "p-button-rounded p-button-success"), l.hasOwnProperty("click") || (l.click = (V) => Se(V));
                break;
              case "delete":
                l.hasOwnProperty("row") || (l.row = !0), l.hasOwnProperty("head") || (l.head = !0), l.hasOwnProperty("icon") || (l.icon = "pi pi-trash"), l.hasOwnProperty("class") || (l.class = "p-button-rounded p-button-danger"), l.hasOwnProperty("click") || (l.click = (V) => Te(V)), l.hasOwnProperty("head_click") || (l.head_click = () => Ne()), l.hasOwnProperty("label") || (l.label = "Удалить");
                break;
              case "create":
                l.hasOwnProperty("head") || (l.head = !0), l.hasOwnProperty("icon") || (l.icon = "pi pi-plus"), l.hasOwnProperty("class") || (l.class = "p-button-rounded p-button-success"), l.hasOwnProperty("head_click") || (l.head_click = () => Ie()), l.hasOwnProperty("label") || (l.label = "Создать");
                break;
              case "subtables":
                s = !1;
                for (let V in e[t]) {
                  let g = { ...e[t][V] };
                  g.table = V, g.hasOwnProperty("row") || (g.row = !0), g.hasOwnProperty("icon") || (g.icon = "pi pi-angle-right"), g.hasOwnProperty("class") || (g.class = "p-button-rounded p-button-success"), g.hasOwnProperty("click") || (g.click = (ze) => Ue(ze, g)), W.value = !0, B.value.push(g);
                }
                break;
            }
            s && (l.hasOwnProperty("row") && (W.value = !0), l.hasOwnProperty("row") && (ge.value = !0), B.value.push(l));
          }
          a.data.selects && (Y.value = a.data.selects), $.value = d;
        }
        await F();
      } catch (a) {
        p("error", { detail: a.message }, !0);
      }
    });
    const A = u({}), Z = u({}), re = u({}), de = async (a) => {
      A.value = { ...a };
    }, Ue = async (a, o) => {
      let d = { ...A.value };
      if (d.hasOwnProperty(a.id))
        if (Z.value[a.id] == o.table) {
          delete d[a.id], await de(d);
          return;
        } else
          delete d[a.id], await de(d), d[a.id] = !0;
      else
        d[a.id] = !0;
      if (Z.value[a.id] = o.table, o.hasOwnProperty("where")) {
        let e = {};
        for (let t in o.where)
          e[t] = {
            operator: fe.AND,
            constraints: [
              {
                value: a[o.where[t]],
                matchMode: me.EQUALS
              }
            ]
          };
        re.value[a.id] = e;
      }
      A.value = { ...d };
    }, J = u({}), F = async (a) => {
      K.value = !0, x.value = {
        ...x.value,
        first: (a == null ? void 0 : a.first) || se.value
      };
      let o = {
        limit: x.value.rows,
        setTotal: 1,
        offset: x.value.first,
        // sortField:lazyParams.value.sortField,
        // sortOrder:lazyParams.value.sortOrder,
        multiSortMeta: x.value.multiSortMeta,
        filters: w.value
      };
      try {
        const d = await f.read(o);
        if (P.value = Je(d.data.rows, b), d.data.autocomplete)
          for (let e in d.data.autocomplete)
            J.value[e] = d.data.autocomplete[e];
        Q.value = d.data.total, K.value = !1;
      } catch (d) {
        p("error", { detail: d.message });
      }
    }, X = () => {
      F();
    };
    h({ refresh: X });
    const { cacheAction: xe, cache: ul } = Ze(), N = async (a) => {
      let { data: o, newValue: d, field: e } = a;
      const t = {
        id: o.id,
        [e]: d
      };
      xe({ type: "update", payload: t });
      try {
        (await f.update(t)).success && (o[e] = d);
      } catch (l) {
        p("error", { detail: l.message }, !0);
      }
    }, Pe = async (a) => {
      x.value = a, await F(a);
    }, Oe = async (a) => {
      x.value = a, await F(a);
    }, Ce = (a) => a.toString().replace(".", ","), r = u({}), ee = u(!1), S = u(!1), Se = (a) => {
      r.value = { ...a }, S.value = !0;
    }, _e = () => {
      S.value = !1, ee.value = !1;
    }, De = async () => {
      if (ee.value = !0, r.value.id)
        try {
          await f.update(r.value), P.value[Fe(Number(r.value.id))] = r.value, S.value = !1, r.value = {};
        } catch (a) {
          p("error", { detail: a.message });
        }
      else
        try {
          await f.create(r.value), X(), S.value = !1, r.value = {};
        } catch (a) {
          p("error", { detail: a.message });
        }
    }, Fe = (a) => {
      let o = -1;
      for (let d = 0; d < P.value.length; d++)
        if (P.value[d].id === a) {
          o = d;
          break;
        }
      return o;
    }, Ie = () => {
      r.value = {}, ee.value = !1, S.value = !0;
    }, R = u(!1), L = u(!1), Te = (a) => {
      r.value = a, R.value = !0;
    }, Ae = async () => {
      try {
        await f.delete({ ids: r.value.id }), P.value = P.value.filter(
          (a) => a.id !== r.value.id
        ), R.value = !1, r.value = {};
      } catch (a) {
        p("error", { detail: a.message });
      }
    }, Ne = () => {
      O.value && O.value.length && (L.value = !0);
    }, Re = async () => {
      const a = O.value.map((o) => o.id).join(",");
      try {
        await f.delete({ ids: a }), P.value = P.value.filter(
          (o) => !O.value.includes(o)
        ), L.value = !1, O.value = null;
      } catch (o) {
        p("error", { detail: o.message });
      }
    }, O = u(), _ = u(!1), Le = (a) => {
      _.value = a.checked, _.value ? (_.value = !0, O.value = P.value) : (_.value = !1, O.value = []);
    }, Me = () => {
      _.value = O.value.length === Q.value;
    }, Ee = () => {
      _.value = !1;
    }, le = (a) => {
      if (a.readonly)
        return "readonly";
    };
    return (a, o) => {
      const d = $e("PVTables", !0);
      return n(), k("div", Xe, [
        m(i(qe), { class: "p-mb-4" }, {
          start: y(() => [
            (n(!0), k(D, null, G(i(B).filter((e) => e.head), (e) => (n(), c(i(C), {
              icon: e.icon,
              label: e.label,
              class: M(e.class),
              onClick: e.head_click
            }, null, 8, ["icon", "label", "class", "onClick"]))), 256))
          ]),
          end: y(() => [
            m(i(C), {
              icon: "pi pi-refresh",
              class: "p-button-rounded p-button-success",
              onClick: X
            }),
            m(i(C), {
              type: "button",
              icon: "pi pi-filter-slash",
              onClick: o[0] || (o[0] = (e) => ke())
            })
          ]),
          _: 1
        }),
        m(i(He), {
          value: P.value,
          lazy: "",
          paginator: "",
          first: se.value,
          rows: 10,
          rowsPerPageOptions: [10, 60, 30, 10],
          ref_key: "dt",
          ref: q,
          dataKey: "id",
          totalRecords: Q.value,
          loading: K.value,
          onPage: o[2] || (o[2] = (e) => Pe(e)),
          onSort: o[3] || (o[3] = (e) => Oe(e)),
          sortMode: "multiple",
          editMode: "cell",
          onCellEditComplete: N,
          selection: O.value,
          "onUpdate:selection": o[4] || (o[4] = (e) => O.value = e),
          selectAll: _.value,
          onSelectAllChange: Le,
          onRowSelect: Me,
          onRowUnselect: Ee,
          filters: w.value,
          "onUpdate:filters": o[5] || (o[5] = (e) => w.value = e),
          filterDisplay: "menu",
          globalFilterFields: ne.value,
          onFilter: o[6] || (o[6] = (e) => Ve(e)),
          expandedRows: A.value,
          "onUpdate:expandedRows": o[7] || (o[7] = (e) => A.value = e),
          showGridlines: "",
          scrollable: "",
          scrollHeight: "50rem",
          resizableColumns: "",
          columnResizeMode: "expand"
        }, {
          expansion: y((e) => [
            T("div", el, [
              m(d, {
                table: Z.value[e.data.id],
                filters: re.value[e.data.id]
              }, null, 8, ["table", "filters"])
            ])
          ]),
          default: y(() => [
            m(i(I), {
              selectionMode: "multiple",
              headerStyle: "width: 3rem"
            }),
            (n(!0), k(D, null, G($.value.filter((e) => e.modal_only != !0), (e) => (n(), k(D, {
              key: e.field
            }, [
              e.field == "id" ? (n(), c(i(I), {
                key: 0,
                field: "id",
                header: "id",
                style: { padding: "1rem 10px 1rem 10px" },
                sortable: ""
              }, {
                body: y(({ data: t, field: l }) => [
                  te(E(t[l]), 1)
                ]),
                _: 1
              })) : e.type == "autocomplete" ? (n(), c(i(I), {
                key: 1,
                field: e.field,
                header: e.label,
                class: M(le(e)),
                style: { "min-width": "350px" },
                sortable: ""
              }, {
                body: y(({ data: t, field: l }) => {
                  var s;
                  return [
                    m(i(ve), {
                      table: e.table,
                      id: t[l],
                      "onUpdate:id": (V) => t[l] = V,
                      options: (s = J.value[l]) == null ? void 0 : s.rows,
                      onSetValue: (V) => N({ data: t, field: l, newValue: t[l] }),
                      disabled: e.readonly
                    }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: y(({ filterModel: t }) => [
                  m(i(z), {
                    modelValue: t.value,
                    "onUpdate:modelValue": (l) => t.value = l,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: H(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : e.type == "select" ? (n(), c(i(I), {
                key: 2,
                field: e.field,
                header: e.label,
                class: M(le(e)),
                style: { "min-width": "350px" },
                sortable: ""
              }, {
                body: y(({ data: t, field: l }) => {
                  var s;
                  return [
                    m(i(be), {
                      id: t[l],
                      "onUpdate:id": (V) => t[l] = V,
                      options: (s = Y.value[l]) == null ? void 0 : s.rows,
                      onSetValue: (V) => N({ data: t, field: l, newValue: t[l] }),
                      disabled: e.readonly
                    }, null, 8, ["id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: y(({ filterModel: t }) => [
                  m(i(z), {
                    modelValue: t.value,
                    "onUpdate:modelValue": (l) => t.value = l,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: H(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : (n(), c(i(I), {
                key: 3,
                field: e.field,
                header: e.label,
                class: M(le(e)),
                style: { "min-width": "350px" },
                sortable: ""
              }, Be({
                body: y(({ data: t, field: l }) => [
                  e.type == "decimal" ? (n(), k(D, { key: 0 }, [
                    te(E(Ce(t[l])), 1)
                  ], 64)) : e.type == "boolean" ? (n(), c(i(ce), {
                    key: 1,
                    modelValue: t[l],
                    "onUpdate:modelValue": (s) => t[l] = s,
                    onKeydown: o[1] || (o[1] = Ge(je(() => {
                    }, ["stop"]), ["tab"])),
                    onChange: (s) => N({ data: t, field: l, newValue: t[l] }),
                    disabled: e.readonly
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "onChange", "disabled"])) : e.type === "date" ? (n(), c(i(ye), {
                    key: 2,
                    "model-value": t[l],
                    "onUpdate:modelValue": (s) => N({ data: t, field: l, newValue: s }),
                    disabled: e.readonly
                  }, null, 8, ["model-value", "onUpdate:modelValue", "disabled"])) : (n(), k(D, { key: 3 }, [
                    te(E(t[l]), 1)
                  ], 64))
                ]),
                filter: y(({ filterModel: t }) => [
                  m(i(z), {
                    modelValue: t.value,
                    "onUpdate:modelValue": (l) => t.value = l,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: H(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, [
                !["boolean", "date"].includes(e.type) && !e.readonly ? {
                  name: "editor",
                  fn: y(({ data: t, field: l }) => [
                    e.type == "textarea" ? (n(), c(i(pe), {
                      key: 0,
                      modelValue: t[l],
                      "onUpdate:modelValue": (s) => t[l] = s,
                      rows: "1"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "number" ? (n(), c(i(j), {
                      key: 1,
                      modelValue: t[l],
                      "onUpdate:modelValue": (s) => t[l] = s
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "decimal" ? (n(), c(i(j), {
                      key: 2,
                      modelValue: t[l],
                      "onUpdate:modelValue": (s) => t[l] = s,
                      minFractionDigits: e.FractionDigits,
                      maxFractionDigits: e.FractionDigits
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits"])) : (n(), c(i(z), {
                      key: 3,
                      modelValue: t[l],
                      "onUpdate:modelValue": (s) => t[l] = s
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]))
                  ]),
                  key: "0"
                } : void 0
              ]), 1032, ["field", "header", "class"]))
            ], 64))), 128)),
            W.value ? (n(), c(i(I), {
              key: 0,
              exportable: !1,
              style: { "white-space": "nowrap" }
            }, {
              body: y((e) => [
                (n(!0), k(D, null, G(i(B).filter((t) => t.row), (t) => (n(), c(i(C), {
                  icon: t.icon,
                  class: M(t.class),
                  onClick: (l) => t.click(e.data, $.value)
                }, null, 8, ["icon", "class", "onClick"]))), 256))
              ]),
              _: 1
            })) : ae("", !0)
          ]),
          _: 1
        }, 8, ["value", "first", "totalRecords", "loading", "selection", "selectAll", "filters", "globalFilterFields", "expandedRows"]),
        m(i(oe), {
          visible: S.value,
          "onUpdate:visible": o[8] || (o[8] = (e) => S.value = e),
          style: { width: "450px" },
          header: "Редактировать",
          modal: !0,
          class: "p-fluid"
        }, {
          footer: y(() => [
            m(i(C), {
              label: "Отмена",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: _e
            }),
            m(i(C), {
              label: "Сохранить",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: De
            })
          ]),
          default: y(() => [
            (n(!0), k(D, null, G($.value.filter((e) => e.table_only != !0), (e) => {
              var t, l;
              return n(), k("div", ll, [
                T("label", {
                  for: e.field
                }, E(e.label), 9, tl),
                e.field == "id" ? (n(), k("p", {
                  key: 0,
                  id: e.field
                }, E(r.value[e.field]), 9, al)) : e.type == "textarea" ? (n(), c(i(pe), {
                  key: 1,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  modelModifiers: { trim: !0 },
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "number" ? (n(), c(i(j), {
                  key: 2,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "autocomplete" ? (n(), c(i(ve), {
                  key: 3,
                  id: r.value[e.field],
                  "onUpdate:id": (s) => r.value[e.field] = s,
                  table: e.table,
                  options: (t = J.value[e.field]) == null ? void 0 : t.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "table", "options", "disabled"])) : e.type == "select" ? (n(), c(i(be), {
                  key: 4,
                  id: r.value[e.field],
                  "onUpdate:id": (s) => r.value[e.field] = s,
                  options: (l = Y.value[e.field]) == null ? void 0 : l.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "options", "disabled"])) : e.type == "decimal" ? (n(), c(i(j), {
                  key: 5,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  minFractionDigits: e.FractionDigits,
                  maxFractionDigits: e.FractionDigits,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits", "disabled"])) : e.type == "boolean" ? (n(), c(i(ce), {
                  key: 6,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type === "date" ? (n(), c(i(ye), {
                  key: 7,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["modelValue", "onUpdate:modelValue", "disabled"])) : (n(), c(i(z), {
                  key: 8,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  modelModifiers: { trim: !0 },
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"]))
              ]);
            }), 256))
          ]),
          _: 1
        }, 8, ["visible"]),
        m(i(oe), {
          visible: R.value,
          "onUpdate:visible": o[10] || (o[10] = (e) => R.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: y(() => [
            m(i(C), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: o[9] || (o[9] = (e) => R.value = !1)
            }),
            m(i(C), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Ae
            })
          ]),
          default: y(() => [
            T("div", ol, [
              il,
              r.value ? (n(), k("span", sl, "Вы хотите удалить эту запись?")) : ae("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"]),
        m(i(oe), {
          visible: L.value,
          "onUpdate:visible": o[12] || (o[12] = (e) => L.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: y(() => [
            m(i(C), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: o[11] || (o[11] = (e) => L.value = !1)
            }),
            m(i(C), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Re
            })
          ]),
          default: y(() => [
            T("div", nl, [
              rl,
              r.value ? (n(), k("span", dl, "Вы хотите удалить отмеченные записи?")) : ae("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"])
      ]);
    };
  }
}, Cl = {
  install: (v, h) => {
    v.component(he.name, he);
  }
};
export {
  he as PVTables,
  Cl as default
};
