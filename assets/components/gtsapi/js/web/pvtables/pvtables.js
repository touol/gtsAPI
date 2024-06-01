import { onMounted as ge, reactive as ye, defineComponent as $e, ref as u, resolveComponent as Ke, openBlock as n, createElementBlock as g, createVNode as f, unref as o, withCtx as b, Fragment as F, renderList as W, createBlock as m, normalizeClass as $, createCommentVNode as K, createTextVNode as se, toDisplayString as B, createSlots as Be, withKeys as Ge, withModifiers as je, createElementVNode as j } from "vue";
import He from "primevue/datatable";
import T from "primevue/column";
import C from "primevue/button";
import Qe from "primevue/toolbar";
import ne from "primevue/dialog";
import G from "primevue/inputtext";
import ve from "primevue/textarea";
import Y from "primevue/inputnumber";
import be from "primevue/inputswitch";
import { FilterOperator as re, FilterMatchMode as de } from "primevue/api";
import he from "pvtables/gtsdate";
import we from "pvtables/gtsautocomplete";
import Ve from "pvtables/gtsselect";
import { useNotifications as qe } from "pvtables/notify";
import We from "pvtables/pvtabs";
import Ye from "pvtables/api";
const Ze = 3, Je = () => {
  ge(() => {
    document.addEventListener("keydown", (U) => {
      U.code === "KeyZ" && U.ctrlKey && y(), U.code === "KeyY" && U.ctrlKey && c();
    });
  });
  const v = ye({
    undo: [],
    redo: []
  }), k = ye({
    name: "",
    details: {}
  }), w = (U) => {
    v.undo.length === Ze && v.undo.shift(), v.undo.push(U);
  };
  function y() {
    v.undo.length !== 0 && (k.details = v.undo.pop(), k.name = "undo", k.details.isNew, v.redo.push(k.details));
  }
  function c() {
    v.redo.length !== 0 && (k.details = v.redo.pop(), k.name = "redo", k.details.isNew, v.undo.push(k.details));
  }
  return { undo: y, redo: c, cacheAction: w, cache: v };
}, Xe = (v, k) => {
  let w = [];
  return v.length && v.forEach(function(y) {
    for (let c in k)
      switch (c == "id" && (y[c] = Number(y[c])), k[c].type) {
        case "boolean":
          y.hasOwnProperty(c) && (y[c] === "0" ? y[c] = !1 : y[c] = !0);
          break;
        case "number":
        case "decimal":
          y[c] = Number(y[c]);
          break;
      }
    w.push(y);
  }), w;
}, el = { class: "card" }, ll = {
  key: 0,
  class: "p-3"
}, al = {
  key: 1,
  class: "p-3"
}, tl = { class: "p-field" }, il = ["for"], ol = ["id"], sl = { class: "confirmation-content" }, nl = /* @__PURE__ */ j("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), rl = { key: 0 }, dl = { class: "confirmation-content" }, ul = /* @__PURE__ */ j("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), pl = { key: 0 }, ke = {
  __name: "PVTables",
  props: {
    table: {
      type: String,
      required: !0
    },
    actions: {
      type: Object,
      default: {}
    },
    reload: {
      type: Boolean
    },
    filters: {
      type: Object,
      default: {}
    }
  },
  setup(v, { expose: k }) {
    $e({
      name: "PVTables"
    });
    const w = v, y = Ye(w.table), { notify: c } = qe(), U = u(), ue = () => {
      let t = {};
      for (let i in h)
        if (w.filters.hasOwnProperty(i))
          t[i] = w.filters[i];
        else
          switch (h[i].type) {
            default:
              t[i] = {
                operator: re.AND,
                constraints: [
                  { value: null, matchMode: de.STARTS_WITH }
                ]
              };
          }
      U.value = t;
    }, Ue = async (t) => {
      x.value.filters = U.value, await I(t);
    }, xe = async () => {
      ue(), x.value.filters = U.value, await I();
    }, Z = (t) => "Поиск по " + t.label, J = u(), H = u(!0), X = u(0), pe = u(0), x = u({}), Q = u([{ field: "id", label: "ID" }]);
    let h = {};
    const O = u();
    let A = u([]);
    const q = u(!1), ce = u([]), ee = u({});
    ge(async () => {
      H.value = !0, x.value = {
        first: J.value.first,
        rows: J.value.rows,
        sortField: null,
        sortOrder: null
        // filters: filters.value
      };
      try {
        const t = await y.options();
        if (t.data.hasOwnProperty("fields")) {
          h = t.data.fields;
          let i = [], d = [];
          for (let l in h)
            h[l].field = l, h[l].hasOwnProperty("label") || (h[l].label = l), h[l].hasOwnProperty("type") || (h[l].type = "text"), h[l].hasOwnProperty("readonly") && (h[l].readonly === !0 || h[l].readonly == 1 ? h[l].readonly = !0 : h[l].readonly = !1), d.push(h[l]), i.push(l);
          ce.value = i, ue();
          let e = t.data.actions;
          if (w.actions.hasOwnProperty(w.table))
            for (let l in w.actions[w.table])
              e[l] = w.actions[w.table][l];
          for (let l in e) {
            let a = { ...e[l] }, s = !0;
            switch (a.action = l, l) {
              case "update":
                a.hasOwnProperty("row") || (a.row = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-pencil"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-success"), a.hasOwnProperty("click") || (a.click = (V) => De(V));
                break;
              case "delete":
                a.hasOwnProperty("row") || (a.row = !0), a.hasOwnProperty("head") || (a.head = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-trash"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-danger"), a.hasOwnProperty("click") || (a.click = (V) => Ae(V)), a.hasOwnProperty("head_click") || (a.head_click = () => Re()), a.hasOwnProperty("label") || (a.label = "Удалить");
                break;
              case "create":
                a.hasOwnProperty("head") || (a.head = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-plus"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-success"), a.hasOwnProperty("head_click") || (a.head_click = () => Te()), a.hasOwnProperty("label") || (a.label = "Создать");
                break;
              case "subtables":
                s = !1;
                for (let V in e[l]) {
                  let p = { action: l, ...e[l][V] };
                  p.table = V, p.hasOwnProperty("row") || (p.row = !0), p.hasOwnProperty("icon") || (p.icon = "pi pi-angle-right"), p.hasOwnProperty("class") || (p.class = "p-button-rounded p-button-success"), p.hasOwnProperty("click") || (p.click = (oe) => me(oe, p)), q.value = !0, A.value.push(p);
                }
                break;
              case "subtabs":
                s = !1;
                for (let V in e[l]) {
                  let p = { action: l, tabs: { ...e[l][V] } };
                  p.table = V, p.hasOwnProperty("row") || (p.row = !0), p.hasOwnProperty("icon") || (p.icon = "pi pi-angle-right"), p.hasOwnProperty("class") || (p.class = "p-button-rounded p-button-success"), p.hasOwnProperty("click") || (p.click = (oe) => me(oe, p)), q.value = !0, A.value.push(p);
                }
                break;
            }
            s && (a.hasOwnProperty("row") && (q.value = !0), A.value.push(a));
          }
          t.data.selects && (ee.value = t.data.selects), Q.value = d;
        }
        await I();
      } catch (t) {
        c("error", { detail: t.message }, !0);
      }
    });
    const N = u({}), _ = u({}), R = u({}), le = (t) => {
      if (!t || t == w.table)
        I();
      else if (t && R.value)
        for (let i in R.value)
          R.value[i].refresh(t);
    };
    k({ refresh: le });
    const L = u({}), fe = async (t) => {
      N.value = { ...t };
    }, me = async (t, i) => {
      let d = { ...N.value };
      if (d.hasOwnProperty(t.id))
        if (_.value[t.id].table == i.table) {
          delete d[t.id], await fe(d);
          return;
        } else
          delete d[t.id], await fe(d), d[t.id] = !0;
      else
        d[t.id] = !0;
      if (_.value[t.id] = i, i.action == "subtables") {
        if (i.hasOwnProperty("where")) {
          let e = {};
          for (let l in i.where)
            e[l] = {
              operator: re.AND,
              constraints: [
                {
                  value: t[i.where[l]],
                  matchMode: de.EQUALS
                }
              ]
            };
          L.value[t.id] = e;
        }
      } else if (i.action == "subtabs") {
        for (let e in i.tabs)
          if (i.tabs[e].hasOwnProperty("where")) {
            let l = {};
            for (let a in i.tabs[e].where)
              l[a] = {
                operator: re.AND,
                constraints: [
                  {
                    value: t[i.tabs[e].where[a]],
                    matchMode: de.EQUALS
                  }
                ]
              };
            L.value[t.id] = {}, L.value[t.id][e] = l;
          }
      }
      N.value = { ...d };
    }, ae = u({}), I = async (t) => {
      H.value = !0, x.value = {
        ...x.value,
        first: (t == null ? void 0 : t.first) || pe.value
      };
      let i = {
        limit: x.value.rows,
        setTotal: 1,
        offset: x.value.first,
        // sortField:lazyParams.value.sortField,
        // sortOrder:lazyParams.value.sortOrder,
        multiSortMeta: x.value.multiSortMeta,
        filters: U.value
      };
      try {
        const d = await y.read(i);
        if (O.value = Xe(d.data.rows, h), d.data.autocomplete)
          for (let e in d.data.autocomplete)
            ae.value[e] = d.data.autocomplete[e];
        X.value = d.data.total, H.value = !1;
      } catch (d) {
        c("error", { detail: d.message });
      }
    }, { cacheAction: Oe, cache: cl } = Je(), M = async (t) => {
      let { data: i, newValue: d, field: e } = t;
      const l = {
        id: i.id,
        [e]: d
      };
      Oe({ type: "update", payload: l });
      try {
        (await y.update(l)).success && (i[e] = d);
      } catch (a) {
        c("error", { detail: a.message }, !0);
      }
    }, Pe = async (t) => {
      x.value = t, await I(t);
    }, Ce = async (t) => {
      x.value = t, await I(t);
    }, Se = (t) => t.toString().replace(".", ","), r = u({}), te = u(!1), S = u(!1), De = (t) => {
      r.value = { ...t }, S.value = !0;
    }, Fe = () => {
      S.value = !1, te.value = !1;
    }, _e = async () => {
      if (te.value = !0, r.value.id)
        try {
          await y.update(r.value), O.value[Ie(Number(r.value.id))] = r.value, S.value = !1, r.value = {};
        } catch (t) {
          c("error", { detail: t.message });
        }
      else
        try {
          await y.create(r.value), le(), S.value = !1, r.value = {};
        } catch (t) {
          c("error", { detail: t.message });
        }
    }, Ie = (t) => {
      let i = -1;
      for (let d = 0; d < O.value.length; d++)
        if (O.value[d].id === t) {
          i = d;
          break;
        }
      return i;
    }, Te = () => {
      r.value = {}, te.value = !1, S.value = !0;
    }, E = u(!1), z = u(!1), Ae = (t) => {
      r.value = t, E.value = !0;
    }, Ne = async () => {
      try {
        await y.delete({ ids: r.value.id }), O.value = O.value.filter(
          (t) => t.id !== r.value.id
        ), E.value = !1, r.value = {};
      } catch (t) {
        c("error", { detail: t.message });
      }
    }, Re = () => {
      P.value && P.value.length && (z.value = !0);
    }, Le = async () => {
      const t = P.value.map((i) => i.id).join(",");
      try {
        await y.delete({ ids: t }), O.value = O.value.filter(
          (i) => !P.value.includes(i)
        ), z.value = !1, P.value = null;
      } catch (i) {
        c("error", { detail: i.message });
      }
    }, P = u(), D = u(!1), Me = (t) => {
      D.value = t.checked, D.value ? (D.value = !0, P.value = O.value) : (D.value = !1, P.value = []);
    }, Ee = () => {
      D.value = P.value.length === X.value;
    }, ze = () => {
      D.value = !1;
    }, ie = (t) => {
      if (t.readonly)
        return "readonly";
    };
    return (t, i) => {
      const d = Ke("PVTables", !0);
      return n(), g("div", el, [
        f(o(Qe), { class: "p-mb-4" }, {
          start: b(() => [
            (n(!0), g(F, null, W(o(A).filter((e) => e.head), (e) => (n(), m(o(C), {
              icon: e.icon,
              label: e.label,
              class: $(e.class),
              onClick: e.head_click
            }, null, 8, ["icon", "label", "class", "onClick"]))), 256))
          ]),
          end: b(() => [
            f(o(C), {
              icon: "pi pi-refresh",
              class: "p-button-rounded p-button-success",
              onClick: i[0] || (i[0] = (e) => le())
            }),
            f(o(C), {
              type: "button",
              icon: "pi pi-filter-slash",
              onClick: i[1] || (i[1] = (e) => xe())
            })
          ]),
          _: 1
        }),
        f(o(He), {
          value: O.value,
          lazy: "",
          paginator: "",
          first: pe.value,
          rows: 10,
          rowsPerPageOptions: [10, 60, 30, 10],
          ref_key: "dt",
          ref: J,
          dataKey: "id",
          totalRecords: X.value,
          loading: H.value,
          onPage: i[3] || (i[3] = (e) => Pe(e)),
          onSort: i[4] || (i[4] = (e) => Ce(e)),
          sortMode: "multiple",
          editMode: "cell",
          onCellEditComplete: M,
          selection: P.value,
          "onUpdate:selection": i[5] || (i[5] = (e) => P.value = e),
          selectAll: D.value,
          onSelectAllChange: Me,
          onRowSelect: Ee,
          onRowUnselect: ze,
          filters: U.value,
          "onUpdate:filters": i[6] || (i[6] = (e) => U.value = e),
          filterDisplay: "menu",
          globalFilterFields: ce.value,
          onFilter: i[7] || (i[7] = (e) => Ue(e)),
          expandedRows: N.value,
          "onUpdate:expandedRows": i[8] || (i[8] = (e) => N.value = e),
          showGridlines: "",
          scrollable: "",
          scrollHeight: "45rem",
          resizableColumns: "",
          columnResizeMode: "expand"
        }, {
          expansion: b((e) => [
            _.value[e.data.id].action == "subtables" ? (n(), g("div", ll, [
              f(d, {
                table: _.value[e.data.id].table,
                actions: v.actions,
                filters: L.value[e.data.id],
                ref: (l) => {
                  l && (R.value[e.data.id] = l);
                }
              }, null, 8, ["table", "actions", "filters"])
            ])) : K("", !0),
            _.value[e.data.id].action == "subtabs" ? (n(), g("div", al, [
              f(o(We), {
                tabs: _.value[e.data.id].tabs,
                actions: v.actions,
                filters: L.value[e.data.id],
                ref: (l) => {
                  l && (R.value[e.data.id] = l);
                }
              }, null, 8, ["tabs", "actions", "filters"])
            ])) : K("", !0)
          ]),
          default: b(() => [
            f(o(T), {
              selectionMode: "multiple",
              headerStyle: "width: 3rem"
            }),
            (n(!0), g(F, null, W(Q.value.filter((e) => e.modal_only != !0), (e) => (n(), g(F, {
              key: e.field
            }, [
              e.field == "id" ? (n(), m(o(T), {
                key: 0,
                field: "id",
                header: "id",
                style: { padding: "1rem 10px 1rem 10px" },
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => [
                  se(B(l[a]), 1)
                ]),
                _: 1
              })) : e.type == "autocomplete" ? (n(), m(o(T), {
                key: 1,
                field: e.field,
                header: e.label,
                class: $(ie(e)),
                style: { "min-width": "350px" },
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => {
                  var s;
                  return [
                    f(o(we), {
                      table: e.table,
                      id: l[a],
                      "onUpdate:id": (V) => l[a] = V,
                      options: (s = ae.value[a]) == null ? void 0 : s.rows,
                      onSetValue: (V) => M({ data: l, field: a, newValue: l[a] }),
                      disabled: e.readonly
                    }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: b(({ filterModel: l }) => [
                  f(o(G), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: Z(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : e.type == "select" ? (n(), m(o(T), {
                key: 2,
                field: e.field,
                header: e.label,
                class: $(ie(e)),
                style: { "min-width": "350px" },
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => {
                  var s;
                  return [
                    f(o(Ve), {
                      id: l[a],
                      "onUpdate:id": (V) => l[a] = V,
                      options: (s = ee.value[a]) == null ? void 0 : s.rows,
                      onSetValue: (V) => M({ data: l, field: a, newValue: l[a] }),
                      disabled: e.readonly
                    }, null, 8, ["id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: b(({ filterModel: l }) => [
                  f(o(G), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: Z(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : (n(), m(o(T), {
                key: 3,
                field: e.field,
                header: e.label,
                class: $(ie(e)),
                style: { "min-width": "350px" },
                sortable: ""
              }, Be({
                body: b(({ data: l, field: a }) => [
                  e.type == "decimal" ? (n(), g(F, { key: 0 }, [
                    se(B(Se(l[a])), 1)
                  ], 64)) : e.type == "boolean" ? (n(), m(o(be), {
                    key: 1,
                    modelValue: l[a],
                    "onUpdate:modelValue": (s) => l[a] = s,
                    onKeydown: i[2] || (i[2] = Ge(je(() => {
                    }, ["stop"]), ["tab"])),
                    onChange: (s) => M({ data: l, field: a, newValue: l[a] }),
                    disabled: e.readonly
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "onChange", "disabled"])) : e.type === "date" ? (n(), m(o(he), {
                    key: 2,
                    "model-value": l[a],
                    "onUpdate:modelValue": (s) => M({ data: l, field: a, newValue: s }),
                    disabled: e.readonly
                  }, null, 8, ["model-value", "onUpdate:modelValue", "disabled"])) : (n(), g(F, { key: 3 }, [
                    se(B(l[a]), 1)
                  ], 64))
                ]),
                filter: b(({ filterModel: l }) => [
                  f(o(G), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: Z(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, [
                !["boolean", "date"].includes(e.type) && !e.readonly ? {
                  name: "editor",
                  fn: b(({ data: l, field: a }) => [
                    e.type == "textarea" ? (n(), m(o(ve), {
                      key: 0,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s,
                      rows: "1"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "number" ? (n(), m(o(Y), {
                      key: 1,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "decimal" ? (n(), m(o(Y), {
                      key: 2,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s,
                      minFractionDigits: e.FractionDigits,
                      maxFractionDigits: e.FractionDigits
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits"])) : (n(), m(o(G), {
                      key: 3,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]))
                  ]),
                  key: "0"
                } : void 0
              ]), 1032, ["field", "header", "class"]))
            ], 64))), 128)),
            q.value ? (n(), m(o(T), {
              key: 0,
              exportable: !1,
              style: { "white-space": "nowrap" }
            }, {
              body: b((e) => [
                (n(!0), g(F, null, W(o(A).filter((l) => l.row), (l) => (n(), m(o(C), {
                  icon: l.icon,
                  class: $(l.class),
                  onClick: (a) => l.click(e.data, Q.value)
                }, null, 8, ["icon", "class", "onClick"]))), 256))
              ]),
              _: 1
            })) : K("", !0)
          ]),
          _: 1
        }, 8, ["value", "first", "totalRecords", "loading", "selection", "selectAll", "filters", "globalFilterFields", "expandedRows"]),
        f(o(ne), {
          visible: S.value,
          "onUpdate:visible": i[9] || (i[9] = (e) => S.value = e),
          style: { width: "450px" },
          header: "Редактировать",
          modal: !0,
          class: "p-fluid"
        }, {
          footer: b(() => [
            f(o(C), {
              label: "Отмена",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: Fe
            }),
            f(o(C), {
              label: "Сохранить",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: _e
            })
          ]),
          default: b(() => [
            (n(!0), g(F, null, W(Q.value.filter((e) => e.table_only != !0), (e) => {
              var l, a;
              return n(), g("div", tl, [
                j("label", {
                  for: e.field
                }, B(e.label), 9, il),
                e.field == "id" ? (n(), g("p", {
                  key: 0,
                  id: e.field
                }, B(r.value[e.field]), 9, ol)) : e.type == "textarea" ? (n(), m(o(ve), {
                  key: 1,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  modelModifiers: { trim: !0 },
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "number" ? (n(), m(o(Y), {
                  key: 2,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "autocomplete" ? (n(), m(o(we), {
                  key: 3,
                  id: r.value[e.field],
                  "onUpdate:id": (s) => r.value[e.field] = s,
                  table: e.table,
                  options: (l = ae.value[e.field]) == null ? void 0 : l.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "table", "options", "disabled"])) : e.type == "select" ? (n(), m(o(Ve), {
                  key: 4,
                  id: r.value[e.field],
                  "onUpdate:id": (s) => r.value[e.field] = s,
                  options: (a = ee.value[e.field]) == null ? void 0 : a.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "options", "disabled"])) : e.type == "decimal" ? (n(), m(o(Y), {
                  key: 5,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  minFractionDigits: e.FractionDigits,
                  maxFractionDigits: e.FractionDigits,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits", "disabled"])) : e.type == "boolean" ? (n(), m(o(be), {
                  key: 6,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type === "date" ? (n(), m(o(he), {
                  key: 7,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["modelValue", "onUpdate:modelValue", "disabled"])) : (n(), m(o(G), {
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
        f(o(ne), {
          visible: E.value,
          "onUpdate:visible": i[11] || (i[11] = (e) => E.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: b(() => [
            f(o(C), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: i[10] || (i[10] = (e) => E.value = !1)
            }),
            f(o(C), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Ne
            })
          ]),
          default: b(() => [
            j("div", sl, [
              nl,
              r.value ? (n(), g("span", rl, "Вы хотите удалить эту запись?")) : K("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"]),
        f(o(ne), {
          visible: z.value,
          "onUpdate:visible": i[13] || (i[13] = (e) => z.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: b(() => [
            f(o(C), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: i[12] || (i[12] = (e) => z.value = !1)
            }),
            f(o(C), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Le
            })
          ]),
          default: b(() => [
            j("div", dl, [
              ul,
              r.value ? (n(), g("span", pl, "Вы хотите удалить отмеченные записи?")) : K("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"])
      ]);
    };
  }
}, Fl = {
  install: (v, k) => {
    v.component(ke.name, ke);
  }
};
export {
  ke as PVTables,
  Fl as default
};
