import { onMounted as Oe, reactive as we, defineComponent as He, ref as d, resolveComponent as Qe, openBlock as n, createElementBlock as V, createVNode as m, unref as o, withCtx as b, Fragment as C, renderList as B, createBlock as c, normalizeClass as G, createCommentVNode as A, createTextVNode as ue, toDisplayString as j, createSlots as Je, withKeys as qe, withModifiers as We, createElementVNode as Q } from "vue";
import Ye from "primevue/datatable";
import I from "primevue/column";
import S from "primevue/button";
import Ze from "primevue/toolbar";
import pe from "primevue/dialog";
import H from "primevue/inputtext";
import Ve from "primevue/textarea";
import Z from "primevue/inputnumber";
import ke from "primevue/inputswitch";
import { FilterOperator as X, FilterMatchMode as ee } from "primevue/api";
import ge from "pvtables/gtsdate";
import ce from "pvtables/gtsautocomplete";
import Pe from "pvtables/gtsselect";
import { useNotifications as Xe } from "pvtables/notify";
import { PVTabs as el } from "pvtables/pvtabs";
import ll from "pvtables/api";
const tl = 3, al = () => {
  Oe(() => {
    document.addEventListener("keydown", (k) => {
      k.code === "KeyZ" && k.ctrlKey && y(), k.code === "KeyY" && k.ctrlKey && f();
    });
  });
  const v = we({
    undo: [],
    redo: []
  }), P = we({
    name: "",
    details: {}
  }), h = (k) => {
    v.undo.length === tl && v.undo.shift(), v.undo.push(k);
  };
  function y() {
    v.undo.length !== 0 && (P.details = v.undo.pop(), P.name = "undo", P.details.isNew, v.redo.push(P.details));
  }
  function f() {
    v.redo.length !== 0 && (P.details = v.redo.pop(), P.name = "redo", P.details.isNew, v.undo.push(P.details));
  }
  return { undo: y, redo: f, cacheAction: h, cache: v };
}, il = (v, P) => {
  let h = [];
  return v.length && v.forEach(function(y) {
    for (let f in P)
      switch (f == "id" && (y[f] = Number(y[f])), P[f].type) {
        case "boolean":
          y.hasOwnProperty(f) && (y[f] === "0" ? y[f] = !1 : y[f] = !0);
          break;
        case "number":
        case "decimal":
          y[f] = Number(y[f]);
          break;
      }
    h.push(y);
  }), h;
}, ol = { class: "card" }, sl = {
  key: 0,
  class: "p-3"
}, nl = {
  key: 1,
  class: "p-3"
}, rl = { class: "p-field" }, dl = ["for"], ul = ["id"], pl = { class: "confirmation-content" }, cl = /* @__PURE__ */ Q("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), fl = { key: 0 }, ml = { class: "confirmation-content" }, vl = /* @__PURE__ */ Q("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), yl = { key: 0 }, Ue = {
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
  setup(v, { expose: P }) {
    He({
      name: "PVTables"
    });
    const h = v, y = ll(h.table), { notify: f } = Xe(), k = d(), fe = () => {
      let t = {};
      for (let i in w)
        if (h.filters.hasOwnProperty(i))
          t[i] = h.filters[i];
        else
          switch (w[i].type) {
            default:
              t[i] = {
                operator: X.AND,
                constraints: [
                  { value: null, matchMode: ee.STARTS_WITH }
                ]
              };
          }
      for (let i in h.filters)
        t.hasOwnProperty(i) || (t[i] = h.filters[i]);
      for (let i in U)
        switch (U[i].type) {
          default:
            const u = U[i].default ? U[i].default : null;
            t[i] = {
              operator: X.AND,
              constraints: [
                { value: u, matchMode: ee.EQUALS }
              ]
            };
        }
      ye.value = JSON.parse(JSON.stringify(U)), k.value = t;
    }, xe = async (t) => {
      k.value[t.field].constraints[0].value = t.default, await D();
    }, Se = async (t) => {
      await D(t);
    }, Ce = async () => {
      fe(), await D();
    }, le = (t) => "Поиск по " + t.label, te = d(), J = d(!0), ae = d(0), me = d(0), F = d({}), q = d([{ field: "id", label: "ID" }]);
    let w = {};
    const x = d();
    let L = d([]);
    const W = d(!1), ve = d([]), ie = d({}), ye = d({});
    let U = {};
    Oe(async () => {
      J.value = !0, F.value = {
        first: te.value.first,
        rows: te.value.rows,
        sortField: null,
        sortOrder: null
        // filters: filters.value
      };
      try {
        const t = await y.options();
        if (t.data.hasOwnProperty("fields")) {
          w = t.data.fields;
          let i = [], u = [];
          for (let l in w)
            w[l].field = l, w[l].hasOwnProperty("label") || (w[l].label = l), w[l].hasOwnProperty("type") || (w[l].type = "text"), w[l].hasOwnProperty("readonly") && (w[l].readonly === !0 || w[l].readonly == 1 ? w[l].readonly = !0 : w[l].readonly = !1), u.push(w[l]), i.push(l);
          if (t.data.hasOwnProperty("filters")) {
            U = t.data.filters;
            for (let l in U)
              U[l].field = l, U[l].default = U[l].default.toString(), U[l].hasOwnProperty("label") || (U[l].label = l), U[l].hasOwnProperty("type") || (U[l].type = "text");
          }
          ve.value = i, fe();
          let e = t.data.actions;
          if (h.actions.hasOwnProperty(h.table))
            for (let l in h.actions[h.table])
              e[l] = h.actions[h.table][l];
          for (let l in e) {
            let a = { ...e[l] }, s = !0;
            switch (a.action = l, l) {
              case "update":
                a.hasOwnProperty("row") || (a.row = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-pencil"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-success"), a.hasOwnProperty("click") || (a.click = (g) => Ne(g));
                break;
              case "delete":
                a.hasOwnProperty("row") || (a.row = !0), a.hasOwnProperty("head") || (a.head = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-trash"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-danger"), a.hasOwnProperty("click") || (a.click = (g) => Me(g)), a.hasOwnProperty("head_click") || (a.head_click = () => ze()), a.hasOwnProperty("label") || (a.label = "Удалить");
                break;
              case "create":
                a.hasOwnProperty("head") || (a.head = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-plus"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-success"), a.hasOwnProperty("head_click") || (a.head_click = () => Le()), a.hasOwnProperty("label") || (a.label = "Создать");
                break;
              case "subtables":
                s = !1;
                for (let g in e[l]) {
                  let p = { action: l, ...e[l][g] };
                  p.table = g, p.hasOwnProperty("row") || (p.row = !0), p.hasOwnProperty("icon") || (p.icon = "pi pi-angle-right"), p.hasOwnProperty("class") || (p.class = "p-button-rounded p-button-success"), p.hasOwnProperty("click") || (p.click = (de) => he(de, p)), W.value = !0, L.value.push(p);
                }
                break;
              case "subtabs":
                s = !1;
                for (let g in e[l]) {
                  let p = { action: l, tabs: { ...e[l][g] } };
                  p.table = g, p.hasOwnProperty("row") || (p.row = !0), p.hasOwnProperty("icon") || (p.icon = "pi pi-angle-right"), p.hasOwnProperty("class") || (p.class = "p-button-rounded p-button-success"), p.hasOwnProperty("click") || (p.click = (de) => he(de, p)), W.value = !0, L.value.push(p);
                }
                break;
            }
            s && (a.hasOwnProperty("row") && (W.value = !0), L.value.push(a));
          }
          t.data.selects && (ie.value = t.data.selects), q.value = u;
        }
        await D();
      } catch (t) {
        f("error", { detail: t.message }, !0);
      }
    });
    const M = d({}), N = d({}), E = d({}), oe = (t) => {
      if (!t || t == h.table)
        D();
      else if (t && E.value)
        for (let i in E.value)
          E.value[i].refresh(t);
    };
    P({ refresh: oe });
    const R = d({}), be = async (t) => {
      M.value = { ...t };
    }, he = async (t, i) => {
      let u = { ...M.value };
      if (u.hasOwnProperty(t.id))
        if (N.value[t.id].table == i.table) {
          delete u[t.id], await be(u);
          return;
        } else
          delete u[t.id], await be(u), u[t.id] = !0;
      else
        u[t.id] = !0;
      if (N.value[t.id] = i, i.action == "subtables") {
        if (i.hasOwnProperty("where")) {
          let e = {};
          for (let l in i.where)
            e[l] = {
              operator: X.AND,
              constraints: [
                {
                  value: t[i.where[l]],
                  matchMode: ee.EQUALS
                }
              ]
            };
          R.value[t.id] = e;
        }
      } else if (i.action == "subtabs") {
        for (let e in i.tabs)
          if (i.tabs[e].hasOwnProperty("where")) {
            let l = {};
            for (let a in i.tabs[e].where)
              l[a] = {
                operator: X.AND,
                constraints: [
                  {
                    value: t[i.tabs[e].where[a]] ? t[i.tabs[e].where[a]] : i.tabs[e].where[a],
                    matchMode: ee.EQUALS
                  }
                ]
              };
            R.value.hasOwnProperty(t.id) || (R.value[t.id] = {}), R.value[t.id][e] = l;
          }
      }
      M.value = { ...u };
    }, se = d({}), Y = d({}), D = async (t) => {
      J.value = !0, F.value = {
        ...F.value,
        first: (t == null ? void 0 : t.first) || me.value
      };
      let i = {};
      for (let e in k.value)
        k.value[e].constraints[0].value !== null && (i[e] = k.value[e]);
      let u = {
        limit: F.value.rows,
        setTotal: 1,
        offset: F.value.first,
        // sortField:lazyParams.value.sortField,
        // sortOrder:lazyParams.value.sortOrder,
        multiSortMeta: F.value.multiSortMeta,
        filters: i
      };
      try {
        const e = await y.read(u);
        if (x.value = il(e.data.rows, w), e.data.autocomplete)
          for (let l in e.data.autocomplete)
            se.value[l] = e.data.autocomplete[l];
        e.data.row_setting && (Y.value = e.data.row_setting), ae.value = e.data.total, J.value = !1;
      } catch (e) {
        f("error", { detail: e.message });
      }
    }, { cacheAction: Fe, cache: bl } = al(), z = async (t) => {
      let { data: i, newValue: u, field: e } = t;
      const l = {
        id: i.id,
        [e]: u
      };
      Fe({ type: "update", payload: l });
      try {
        (await y.update(l)).success && (i[e] = u);
      } catch (a) {
        f("error", { detail: a.message }, !0);
      }
    }, De = async (t) => {
      F.value = t, await D(t);
    }, _e = async (t) => {
      F.value = t, await D(t);
    }, Te = (t) => parseFloat(t).toFixed(2).toString().replace(".", ","), r = d({}), ne = d(!1), _ = d(!1), Ne = (t) => {
      r.value = { ...t }, _.value = !0;
    }, Re = () => {
      _.value = !1, ne.value = !1;
    }, Ae = async () => {
      if (ne.value = !0, r.value.id)
        try {
          await y.update(r.value), x.value[Ie(Number(r.value.id))] = r.value, _.value = !1, r.value = {};
        } catch (t) {
          f("error", { detail: t.message });
        }
      else
        try {
          await y.create(r.value), oe(), _.value = !1, r.value = {};
        } catch (t) {
          f("error", { detail: t.message });
        }
    }, Ie = (t) => {
      let i = -1;
      for (let u = 0; u < x.value.length; u++)
        if (x.value[u].id === t) {
          i = u;
          break;
        }
      return i;
    }, Le = () => {
      r.value = {}, ne.value = !1, _.value = !0;
    }, $ = d(!1), K = d(!1), Me = (t) => {
      r.value = t, $.value = !0;
    }, Ee = async () => {
      try {
        await y.delete({ ids: r.value.id }), x.value = x.value.filter(
          (t) => t.id !== r.value.id
        ), $.value = !1, r.value = {};
      } catch (t) {
        f("error", { detail: t.message });
      }
    }, ze = () => {
      O.value && O.value.length && (K.value = !0);
    }, $e = async () => {
      const t = O.value.map((i) => i.id).join(",");
      try {
        await y.delete({ ids: t }), x.value = x.value.filter(
          (i) => !O.value.includes(i)
        ), K.value = !1, O.value = null;
      } catch (i) {
        f("error", { detail: i.message });
      }
    }, O = d(), T = d(!1), Ke = (t) => {
      T.value = t.checked, T.value ? (T.value = !0, O.value = x.value) : (T.value = !1, O.value = []);
    }, Be = () => {
      T.value = O.value.length === ae.value;
    }, Ge = () => {
      T.value = !1;
    }, re = (t) => t.readonly ? "readonly " + t.type : t.type, je = (t) => {
      if (Y.value[t.id] && Y.value[t.id].class)
        return Y.value[t.id].class;
    };
    return (t, i) => {
      const u = Qe("PVTables", !0);
      return n(), V("div", ol, [
        m(o(Ze), { class: "p-mb-4" }, {
          start: b(() => [
            (n(!0), V(C, null, B(o(L).filter((e) => e.head), (e) => (n(), c(o(S), {
              icon: e.icon,
              label: e.label,
              class: G(e.class),
              onClick: (l) => e.head_click(l, v.table, k.value, O.value)
            }, null, 8, ["icon", "label", "class", "onClick"]))), 256))
          ]),
          center: b(() => [
            (n(!0), V(C, null, B(ye.value, (e) => (n(), V(C, {
              key: e.field
            }, [
              e.type == "autocomplete" ? (n(), c(o(ce), {
                key: 0,
                table: e.table,
                id: e.default,
                "onUpdate:id": (l) => e.default = l,
                options: e.rows,
                onSetValue: (l) => xe(e)
              }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue"])) : A("", !0)
            ], 64))), 128))
          ]),
          end: b(() => [
            m(o(S), {
              icon: "pi pi-refresh",
              class: "p-button-rounded p-button-success",
              onClick: i[0] || (i[0] = (e) => oe())
            }),
            m(o(S), {
              type: "button",
              icon: "pi pi-filter-slash",
              onClick: i[1] || (i[1] = (e) => Ce())
            })
          ]),
          _: 1
        }),
        m(o(Ye), {
          value: x.value,
          lazy: "",
          paginator: "",
          first: me.value,
          rows: 10,
          rowsPerPageOptions: [10, 60, 30, 10],
          paginatorTemplate: "RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink",
          currentPageReportTemplate: "{first} to {last} of {totalRecords}",
          ref_key: "dt",
          ref: te,
          dataKey: "id",
          totalRecords: ae.value,
          loading: J.value,
          onPage: i[3] || (i[3] = (e) => De(e)),
          onSort: i[4] || (i[4] = (e) => _e(e)),
          sortMode: "multiple",
          editMode: "cell",
          onCellEditComplete: z,
          selection: O.value,
          "onUpdate:selection": i[5] || (i[5] = (e) => O.value = e),
          selectAll: T.value,
          onSelectAllChange: Ke,
          onRowSelect: Be,
          onRowUnselect: Ge,
          filters: k.value,
          "onUpdate:filters": i[6] || (i[6] = (e) => k.value = e),
          filterDisplay: "menu",
          globalFilterFields: ve.value,
          onFilter: i[7] || (i[7] = (e) => Se(e)),
          expandedRows: M.value,
          "onUpdate:expandedRows": i[8] || (i[8] = (e) => M.value = e),
          showGridlines: "",
          scrollable: "",
          scrollHeight: "45rem",
          resizableColumns: "",
          columnResizeMode: "expand",
          size: "small",
          rowClass: je
        }, {
          expansion: b((e) => [
            N.value[e.data.id].action == "subtables" ? (n(), V("div", sl, [
              m(u, {
                table: N.value[e.data.id].table,
                actions: v.actions,
                filters: R.value[e.data.id],
                ref: (l) => {
                  l && (E.value[e.data.id] = l);
                }
              }, null, 8, ["table", "actions", "filters"])
            ])) : A("", !0),
            N.value[e.data.id].action == "subtabs" ? (n(), V("div", nl, [
              m(o(el), {
                tabs: N.value[e.data.id].tabs,
                actions: v.actions,
                filters: R.value[e.data.id],
                ref: (l) => {
                  l && (E.value[e.data.id] = l);
                }
              }, null, 8, ["tabs", "actions", "filters"])
            ])) : A("", !0)
          ]),
          default: b(() => [
            m(o(I), {
              selectionMode: "multiple",
              headerStyle: "width: 3rem"
            }),
            (n(!0), V(C, null, B(q.value.filter((e) => e.modal_only != !0), (e) => (n(), V(C, {
              key: e.field
            }, [
              e.field == "id" ? (n(), c(o(I), {
                key: 0,
                field: "id",
                header: "id",
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => [
                  ue(j(l[a]), 1)
                ]),
                _: 1
              })) : e.type == "autocomplete" ? (n(), c(o(I), {
                key: 1,
                field: e.field,
                header: e.label,
                class: G(re(e)),
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => {
                  var s;
                  return [
                    m(o(ce), {
                      table: e.table,
                      id: l[a],
                      "onUpdate:id": (g) => l[a] = g,
                      options: (s = se.value[a]) == null ? void 0 : s.rows,
                      onSetValue: (g) => z({ data: l, field: a, newValue: l[a] }),
                      disabled: e.readonly
                    }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: b(({ filterModel: l }) => [
                  m(o(H), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: le(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : e.type == "select" ? (n(), c(o(I), {
                key: 2,
                field: e.field,
                header: e.label,
                class: G(re(e)),
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => {
                  var s;
                  return [
                    m(o(Pe), {
                      id: l[a],
                      "onUpdate:id": (g) => l[a] = g,
                      options: (s = ie.value[a]) == null ? void 0 : s.rows,
                      onSetValue: (g) => z({ data: l, field: a, newValue: l[a] }),
                      disabled: e.readonly
                    }, null, 8, ["id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: b(({ filterModel: l }) => [
                  m(o(H), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: le(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : (n(), c(o(I), {
                key: 3,
                field: e.field,
                header: e.label,
                class: G(re(e)),
                sortable: ""
              }, Je({
                body: b(({ data: l, field: a }) => [
                  e.type == "decimal" ? (n(), V(C, { key: 0 }, [
                    ue(j(Te(l[a])), 1)
                  ], 64)) : e.type == "boolean" ? (n(), c(o(ke), {
                    key: 1,
                    modelValue: l[a],
                    "onUpdate:modelValue": (s) => l[a] = s,
                    onKeydown: i[2] || (i[2] = qe(We(() => {
                    }, ["stop"]), ["tab"])),
                    onChange: (s) => z({ data: l, field: a, newValue: l[a] }),
                    disabled: e.readonly
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "onChange", "disabled"])) : e.type === "date" ? (n(), c(o(ge), {
                    key: 2,
                    "model-value": l[a],
                    "onUpdate:modelValue": (s) => z({ data: l, field: a, newValue: s }),
                    disabled: e.readonly
                  }, null, 8, ["model-value", "onUpdate:modelValue", "disabled"])) : (n(), V(C, { key: 3 }, [
                    ue(j(l[a]), 1)
                  ], 64))
                ]),
                filter: b(({ filterModel: l }) => [
                  m(o(H), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: le(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, [
                !["boolean", "date"].includes(e.type) && !e.readonly ? {
                  name: "editor",
                  fn: b(({ data: l, field: a }) => [
                    e.type == "textarea" ? (n(), c(o(Ve), {
                      key: 0,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s,
                      rows: "1"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "number" ? (n(), c(o(Z), {
                      key: 1,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "decimal" ? (n(), c(o(Z), {
                      key: 2,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s,
                      minFractionDigits: e.FractionDigits,
                      maxFractionDigits: e.FractionDigits
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits"])) : (n(), c(o(H), {
                      key: 3,
                      modelValue: l[a],
                      "onUpdate:modelValue": (s) => l[a] = s
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]))
                  ]),
                  key: "0"
                } : void 0
              ]), 1032, ["field", "header", "class"]))
            ], 64))), 128)),
            W.value ? (n(), c(o(I), {
              key: 0,
              exportable: !1,
              style: { "white-space": "nowrap" }
            }, {
              body: b((e) => [
                (n(!0), V(C, null, B(o(L).filter((l) => l.row), (l) => (n(), c(o(S), {
                  icon: l.icon,
                  class: G(l.class),
                  onClick: (a) => l.click(e.data, q.value)
                }, null, 8, ["icon", "class", "onClick"]))), 256))
              ]),
              _: 1
            })) : A("", !0)
          ]),
          _: 1
        }, 8, ["value", "first", "totalRecords", "loading", "selection", "selectAll", "filters", "globalFilterFields", "expandedRows"]),
        m(o(pe), {
          visible: _.value,
          "onUpdate:visible": i[9] || (i[9] = (e) => _.value = e),
          style: { width: "450px" },
          header: "Редактировать",
          modal: !0,
          class: "p-fluid"
        }, {
          footer: b(() => [
            m(o(S), {
              label: "Отмена",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: Re
            }),
            m(o(S), {
              label: "Сохранить",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Ae
            })
          ]),
          default: b(() => [
            (n(!0), V(C, null, B(q.value.filter((e) => e.table_only != !0), (e) => {
              var l, a;
              return n(), V("div", rl, [
                Q("label", {
                  for: e.field
                }, j(e.label), 9, dl),
                e.field == "id" ? (n(), V("p", {
                  key: 0,
                  id: e.field
                }, j(r.value[e.field]), 9, ul)) : e.type == "textarea" ? (n(), c(o(Ve), {
                  key: 1,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  modelModifiers: { trim: !0 },
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "number" ? (n(), c(o(Z), {
                  key: 2,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "autocomplete" ? (n(), c(o(ce), {
                  key: 3,
                  id: r.value[e.field],
                  "onUpdate:id": (s) => r.value[e.field] = s,
                  table: e.table,
                  options: (l = se.value[e.field]) == null ? void 0 : l.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "table", "options", "disabled"])) : e.type == "select" ? (n(), c(o(Pe), {
                  key: 4,
                  id: r.value[e.field],
                  "onUpdate:id": (s) => r.value[e.field] = s,
                  options: (a = ie.value[e.field]) == null ? void 0 : a.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "options", "disabled"])) : e.type == "decimal" ? (n(), c(o(Z), {
                  key: 5,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  minFractionDigits: e.FractionDigits,
                  maxFractionDigits: e.FractionDigits,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits", "disabled"])) : e.type == "boolean" ? (n(), c(o(ke), {
                  key: 6,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type === "date" ? (n(), c(o(ge), {
                  key: 7,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (s) => r.value[e.field] = s,
                  disabled: e.readonly
                }, null, 8, ["modelValue", "onUpdate:modelValue", "disabled"])) : (n(), c(o(H), {
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
        m(o(pe), {
          visible: $.value,
          "onUpdate:visible": i[11] || (i[11] = (e) => $.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: b(() => [
            m(o(S), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: i[10] || (i[10] = (e) => $.value = !1)
            }),
            m(o(S), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Ee
            })
          ]),
          default: b(() => [
            Q("div", pl, [
              cl,
              r.value ? (n(), V("span", fl, "Вы хотите удалить эту запись?")) : A("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"]),
        m(o(pe), {
          visible: K.value,
          "onUpdate:visible": i[13] || (i[13] = (e) => K.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: b(() => [
            m(o(S), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: i[12] || (i[12] = (e) => K.value = !1)
            }),
            m(o(S), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: $e
            })
          ]),
          default: b(() => [
            Q("div", ml, [
              vl,
              r.value ? (n(), V("span", yl, "Вы хотите удалить отмеченные записи?")) : A("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"])
      ]);
    };
  }
}, Al = {
  install: (v, P) => {
    v.component(Ue.name, Ue);
  }
};
export {
  Ue as PVTables,
  Al as default
};
