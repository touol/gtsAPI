import { onMounted as Oe, reactive as we, defineComponent as je, ref as d, resolveComponent as Qe, openBlock as s, createElementBlock as w, createVNode as v, unref as o, withCtx as b, Fragment as C, renderList as B, createBlock as c, normalizeClass as H, createCommentVNode as R, createTextVNode as ue, toDisplayString as G, createSlots as Je, withKeys as qe, withModifiers as We, createElementVNode as Q } from "vue";
import Ye from "primevue/datatable";
import A from "primevue/column";
import S from "primevue/button";
import Ze from "primevue/toolbar";
import pe from "primevue/dialog";
import j from "primevue/inputtext";
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
    document.addEventListener("keydown", (V) => {
      V.code === "KeyZ" && V.ctrlKey && y(), V.code === "KeyY" && V.ctrlKey && m();
    });
  });
  const f = we({
    undo: [],
    redo: []
  }), P = we({
    name: "",
    details: {}
  }), h = (V) => {
    f.undo.length === tl && f.undo.shift(), f.undo.push(V);
  };
  function y() {
    f.undo.length !== 0 && (P.details = f.undo.pop(), P.name = "undo", P.details.isNew, f.redo.push(P.details));
  }
  function m() {
    f.redo.length !== 0 && (P.details = f.redo.pop(), P.name = "redo", P.details.isNew, f.undo.push(P.details));
  }
  return { undo: y, redo: m, cacheAction: h, cache: f };
}, il = (f, P) => {
  let h = [];
  return f.length && f.forEach(function(y) {
    for (let m in P)
      switch (m == "id" && (y[m] = Number(y[m])), P[m].type) {
        case "boolean":
          y.hasOwnProperty(m) && (y[m] === "0" ? y[m] = !1 : y[m] = !0);
          break;
        case "number":
        case "decimal":
          y[m] = Number(y[m]);
          break;
      }
    h.push(y);
  }), h;
}, ol = { class: "card" }, sl = ["innerHTML"], nl = {
  key: 0,
  class: "p-3"
}, rl = {
  key: 1,
  class: "p-3"
}, dl = { class: "p-field" }, ul = ["for"], pl = ["id"], cl = { class: "confirmation-content" }, fl = /* @__PURE__ */ Q("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), ml = { key: 0 }, vl = { class: "confirmation-content" }, yl = /* @__PURE__ */ Q("i", {
  class: "pi pi-exclamation-triangle p-mr-3",
  style: { "font-size": "2rem" }
}, null, -1), bl = { key: 0 }, Ue = {
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
  setup(f, { expose: P }) {
    je({
      name: "PVTables"
    });
    const h = f, y = ll(h.table), { notify: m } = Xe(), V = d(), fe = () => {
      let t = {};
      for (let i in k)
        if (h.filters.hasOwnProperty(i))
          t[i] = h.filters[i];
        else
          switch (k[i].type) {
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
      ye.value = JSON.parse(JSON.stringify(U)), V.value = t;
    }, xe = async (t) => {
      V.value[t.field].constraints[0].value = t.default, await D();
    }, Se = async (t) => {
      await D(t);
    }, Ce = async () => {
      fe(), await D();
    }, le = (t) => "Поиск по " + t.label, te = d(), J = d(!0), ae = d(0), me = d(0), F = d({}), q = d([{ field: "id", label: "ID" }]);
    let k = {};
    const x = d();
    let I = d([]);
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
          k = t.data.fields;
          let i = [], u = [];
          for (let l in k)
            k[l].field = l, k[l].hasOwnProperty("label") || (k[l].label = l), k[l].hasOwnProperty("type") || (k[l].type = "text"), k[l].hasOwnProperty("readonly") && (k[l].readonly === !0 || k[l].readonly == 1 ? k[l].readonly = !0 : k[l].readonly = !1), u.push(k[l]), i.push(l);
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
            let a = { ...e[l] }, n = !0;
            switch (a.action = l, l) {
              case "update":
                a.hasOwnProperty("row") || (a.row = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-pencil"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-success"), a.hasOwnProperty("click") || (a.click = (g) => Le(g));
                break;
              case "delete":
                a.hasOwnProperty("row") || (a.row = !0), a.hasOwnProperty("head") || (a.head = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-trash"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-danger"), a.hasOwnProperty("click") || (a.click = (g) => Me(g)), a.hasOwnProperty("head_click") || (a.head_click = () => ze()), a.hasOwnProperty("label") || (a.label = "Удалить");
                break;
              case "create":
                a.hasOwnProperty("head") || (a.head = !0), a.hasOwnProperty("icon") || (a.icon = "pi pi-plus"), a.hasOwnProperty("class") || (a.class = "p-button-rounded p-button-success"), a.hasOwnProperty("head_click") || (a.head_click = () => Ie()), a.hasOwnProperty("label") || (a.label = "Создать");
                break;
              case "subtables":
                n = !1;
                for (let g in e[l]) {
                  let p = { action: l, ...e[l][g] };
                  p.table = g, p.hasOwnProperty("row") || (p.row = !0), p.hasOwnProperty("icon") || (p.icon = "pi pi-angle-right"), p.hasOwnProperty("class") || (p.class = "p-button-rounded p-button-success"), p.hasOwnProperty("click") || (p.click = (de) => he(de, p)), W.value = !0, I.value.push(p);
                }
                break;
              case "subtabs":
                n = !1;
                for (let g in e[l]) {
                  let p = { action: l, tabs: { ...e[l][g] } };
                  p.table = g, p.hasOwnProperty("row") || (p.row = !0), p.hasOwnProperty("icon") || (p.icon = "pi pi-angle-right"), p.hasOwnProperty("class") || (p.class = "p-button-rounded p-button-success"), p.hasOwnProperty("click") || (p.click = (de) => he(de, p)), W.value = !0, I.value.push(p);
                }
                break;
            }
            n && (a.hasOwnProperty("row") && (W.value = !0), I.value.push(a));
          }
          t.data.selects && (ie.value = t.data.selects), q.value = u;
        }
        await D();
      } catch (t) {
        m("error", { detail: t.message }, !0);
      }
    });
    const M = d({}), L = d({}), E = d({}), oe = (t) => {
      if (!t || t == h.table)
        D();
      else if (t && E.value)
        for (let i in E.value)
          E.value[i].refresh(t);
    };
    P({ refresh: oe });
    const N = d({}), be = async (t) => {
      M.value = { ...t };
    }, he = async (t, i) => {
      let u = { ...M.value };
      if (u.hasOwnProperty(t.id))
        if (L.value[t.id].table == i.table) {
          delete u[t.id], await be(u);
          return;
        } else
          delete u[t.id], await be(u), u[t.id] = !0;
      else
        u[t.id] = !0;
      if (L.value[t.id] = i, i.action == "subtables") {
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
          N.value[t.id] = e;
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
            N.value.hasOwnProperty(t.id) || (N.value[t.id] = {}), N.value[t.id][e] = l;
          }
      }
      M.value = { ...u };
    }, se = d({}), Y = d({}), D = async (t) => {
      J.value = !0, F.value = {
        ...F.value,
        first: (t == null ? void 0 : t.first) || me.value
      };
      let i = {};
      for (let e in V.value)
        V.value[e].constraints[0].value !== null && (i[e] = V.value[e]);
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
        if (x.value = il(e.data.rows, k), e.data.autocomplete)
          for (let l in e.data.autocomplete)
            se.value[l] = e.data.autocomplete[l];
        e.data.row_setting && (Y.value = e.data.row_setting), ae.value = e.data.total, J.value = !1;
      } catch (e) {
        m("error", { detail: e.message });
      }
    }, { cacheAction: Fe, cache: hl } = al(), z = async (t) => {
      let { data: i, newValue: u, field: e } = t;
      const l = {
        id: i.id,
        [e]: u
      };
      Fe({ type: "update", payload: l });
      try {
        (await y.update(l)).success && (i[e] = u);
      } catch (a) {
        m("error", { detail: a.message }, !0);
      }
    }, De = async (t) => {
      F.value = t, await D(t);
    }, _e = async (t) => {
      F.value = t, await D(t);
    }, Te = (t) => parseFloat(t).toFixed(2).toString().replace(".", ","), r = d({}), ne = d(!1), _ = d(!1), Le = (t) => {
      r.value = { ...t }, _.value = !0;
    }, Ne = () => {
      _.value = !1, ne.value = !1;
    }, Re = async () => {
      if (ne.value = !0, r.value.id)
        try {
          await y.update(r.value), x.value[Ae(Number(r.value.id))] = r.value, _.value = !1, r.value = {};
        } catch (t) {
          m("error", { detail: t.message });
        }
      else
        try {
          await y.create(r.value), oe(), _.value = !1, r.value = {};
        } catch (t) {
          m("error", { detail: t.message });
        }
    }, Ae = (t) => {
      let i = -1;
      for (let u = 0; u < x.value.length; u++)
        if (x.value[u].id === t) {
          i = u;
          break;
        }
      return i;
    }, Ie = () => {
      r.value = {}, ne.value = !1, _.value = !0;
    }, $ = d(!1), K = d(!1), Me = (t) => {
      r.value = t, $.value = !0;
    }, Ee = async () => {
      try {
        await y.delete({ ids: r.value.id }), x.value = x.value.filter(
          (t) => t.id !== r.value.id
        ), $.value = !1, r.value = {};
      } catch (t) {
        m("error", { detail: t.message });
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
        m("error", { detail: i.message });
      }
    }, O = d(), T = d(!1), Ke = (t) => {
      T.value = t.checked, T.value ? (T.value = !0, O.value = x.value) : (T.value = !1, O.value = []);
    }, Be = () => {
      T.value = O.value.length === ae.value;
    }, He = () => {
      T.value = !1;
    }, re = (t) => t.readonly ? "readonly " + t.type : t.type, Ge = (t) => {
      if (Y.value[t.id] && Y.value[t.id].class)
        return Y.value[t.id].class;
    };
    return (t, i) => {
      const u = Qe("PVTables", !0);
      return s(), w("div", ol, [
        v(o(Ze), { class: "p-mb-4" }, {
          start: b(() => [
            (s(!0), w(C, null, B(o(I).filter((e) => e.head), (e) => (s(), c(o(S), {
              icon: e.icon,
              label: e.label,
              class: H(e.class),
              onClick: (l) => e.head_click(l, f.table, V.value, O.value)
            }, null, 8, ["icon", "label", "class", "onClick"]))), 256))
          ]),
          center: b(() => [
            (s(!0), w(C, null, B(ye.value, (e) => (s(), w(C, {
              key: e.field
            }, [
              e.type == "autocomplete" ? (s(), c(o(ce), {
                key: 0,
                table: e.table,
                id: e.default,
                "onUpdate:id": (l) => e.default = l,
                options: e.rows,
                onSetValue: (l) => xe(e)
              }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue"])) : R("", !0)
            ], 64))), 128))
          ]),
          end: b(() => [
            v(o(S), {
              icon: "pi pi-refresh",
              class: "p-button-rounded p-button-success",
              onClick: i[0] || (i[0] = (e) => oe())
            }),
            v(o(S), {
              type: "button",
              icon: "pi pi-filter-slash",
              onClick: i[1] || (i[1] = (e) => Ce())
            })
          ]),
          _: 1
        }),
        v(o(Ye), {
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
          onRowUnselect: He,
          filters: V.value,
          "onUpdate:filters": i[6] || (i[6] = (e) => V.value = e),
          filterDisplay: "menu",
          globalFilterFields: ve.value,
          onFilter: i[7] || (i[7] = (e) => Se(e)),
          expandedRows: M.value,
          "onUpdate:expandedRows": i[8] || (i[8] = (e) => M.value = e),
          showGridlines: "",
          scrollable: "",
          scrollHeight: "85vh",
          resizableColumns: "",
          columnResizeMode: "expand",
          size: "small",
          rowClass: Ge
        }, {
          expansion: b((e) => [
            L.value[e.data.id].action == "subtables" ? (s(), w("div", nl, [
              v(u, {
                table: L.value[e.data.id].table,
                actions: f.actions,
                filters: N.value[e.data.id],
                ref: (l) => {
                  l && (E.value[e.data.id] = l);
                }
              }, null, 8, ["table", "actions", "filters"])
            ])) : R("", !0),
            L.value[e.data.id].action == "subtabs" ? (s(), w("div", rl, [
              v(o(el), {
                tabs: L.value[e.data.id].tabs,
                actions: f.actions,
                filters: N.value[e.data.id],
                ref: (l) => {
                  l && (E.value[e.data.id] = l);
                }
              }, null, 8, ["tabs", "actions", "filters"])
            ])) : R("", !0)
          ]),
          default: b(() => [
            v(o(A), {
              selectionMode: "multiple",
              headerStyle: "width: 3rem"
            }),
            (s(!0), w(C, null, B(q.value.filter((e) => e.modal_only != !0), (e) => (s(), w(C, {
              key: e.field
            }, [
              e.field == "id" ? (s(), c(o(A), {
                key: 0,
                field: "id",
                header: "id",
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => [
                  ue(G(l[a]), 1)
                ]),
                _: 1
              })) : e.type == "autocomplete" ? (s(), c(o(A), {
                key: 1,
                field: e.field,
                header: e.label,
                class: H(re(e)),
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => {
                  var n;
                  return [
                    v(o(ce), {
                      table: e.table,
                      id: l[a],
                      "onUpdate:id": (g) => l[a] = g,
                      options: (n = se.value[a]) == null ? void 0 : n.rows,
                      onSetValue: (g) => z({ data: l, field: a, newValue: l[a] }),
                      disabled: e.readonly
                    }, null, 8, ["table", "id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: b(({ filterModel: l }) => [
                  v(o(j), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: le(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : e.type == "select" ? (s(), c(o(A), {
                key: 2,
                field: e.field,
                header: e.label,
                class: H(re(e)),
                sortable: ""
              }, {
                body: b(({ data: l, field: a }) => {
                  var n;
                  return [
                    v(o(Pe), {
                      id: l[a],
                      "onUpdate:id": (g) => l[a] = g,
                      options: (n = ie.value[a]) == null ? void 0 : n.rows,
                      onSetValue: (g) => z({ data: l, field: a, newValue: l[a] }),
                      disabled: e.readonly
                    }, null, 8, ["id", "onUpdate:id", "options", "onSetValue", "disabled"])
                  ];
                }),
                filter: b(({ filterModel: l }) => [
                  v(o(j), {
                    modelValue: l.value,
                    "onUpdate:modelValue": (a) => l.value = a,
                    type: "text",
                    class: "p-column-filter",
                    placeholder: le(e)
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder"])
                ]),
                _: 2
              }, 1032, ["field", "header", "class"])) : (s(), c(o(A), {
                key: 3,
                field: e.field,
                header: e.label,
                class: H(re(e)),
                sortable: ""
              }, Je({
                body: b(({ data: l, field: a }) => [
                  e.type == "decimal" ? (s(), w(C, { key: 0 }, [
                    ue(G(Te(l[a])), 1)
                  ], 64)) : e.type == "boolean" ? (s(), c(o(ke), {
                    key: 1,
                    modelValue: l[a],
                    "onUpdate:modelValue": (n) => l[a] = n,
                    onKeydown: i[2] || (i[2] = qe(We(() => {
                    }, ["stop"]), ["tab"])),
                    onChange: (n) => z({ data: l, field: a, newValue: l[a] }),
                    disabled: e.readonly
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "onChange", "disabled"])) : e.type === "date" ? (s(), c(o(ge), {
                    key: 2,
                    "model-value": l[a],
                    "onUpdate:modelValue": (n) => z({ data: l, field: a, newValue: n }),
                    disabled: e.readonly
                  }, null, 8, ["model-value", "onUpdate:modelValue", "disabled"])) : e.type == "html" ? (s(), w("span", {
                    key: 3,
                    innerHTML: l[a]
                  }, null, 8, sl)) : (s(), w(C, { key: 4 }, [
                    ue(G(l[a]), 1)
                  ], 64))
                ]),
                filter: b(({ filterModel: l }) => [
                  v(o(j), {
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
                    e.type == "textarea" ? (s(), c(o(Ve), {
                      key: 0,
                      modelValue: l[a],
                      "onUpdate:modelValue": (n) => l[a] = n,
                      rows: "1"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "number" ? (s(), c(o(Z), {
                      key: 1,
                      modelValue: l[a],
                      "onUpdate:modelValue": (n) => l[a] = n
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])) : e.type == "decimal" ? (s(), c(o(Z), {
                      key: 2,
                      modelValue: l[a],
                      "onUpdate:modelValue": (n) => l[a] = n,
                      minFractionDigits: e.FractionDigits,
                      maxFractionDigits: e.FractionDigits
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits"])) : (s(), c(o(j), {
                      key: 3,
                      modelValue: l[a],
                      "onUpdate:modelValue": (n) => l[a] = n
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]))
                  ]),
                  key: "0"
                } : void 0
              ]), 1032, ["field", "header", "class"]))
            ], 64))), 128)),
            W.value ? (s(), c(o(A), {
              key: 0,
              exportable: !1,
              style: { "white-space": "nowrap" }
            }, {
              body: b((e) => [
                (s(!0), w(C, null, B(o(I).filter((l) => l.row), (l) => (s(), c(o(S), {
                  icon: l.icon,
                  class: H(l.class),
                  onClick: (a) => l.click(e.data, q.value, f.table, V.value)
                }, null, 8, ["icon", "class", "onClick"]))), 256))
              ]),
              _: 1
            })) : R("", !0)
          ]),
          _: 1
        }, 8, ["value", "first", "totalRecords", "loading", "selection", "selectAll", "filters", "globalFilterFields", "expandedRows"]),
        v(o(pe), {
          visible: _.value,
          "onUpdate:visible": i[9] || (i[9] = (e) => _.value = e),
          style: { width: "450px" },
          header: "Редактировать",
          modal: !0,
          class: "p-fluid"
        }, {
          footer: b(() => [
            v(o(S), {
              label: "Отмена",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: Ne
            }),
            v(o(S), {
              label: "Сохранить",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Re
            })
          ]),
          default: b(() => [
            (s(!0), w(C, null, B(q.value.filter((e) => e.table_only != !0), (e) => {
              var l, a;
              return s(), w("div", dl, [
                Q("label", {
                  for: e.field
                }, G(e.label), 9, ul),
                e.field == "id" ? (s(), w("p", {
                  key: 0,
                  id: e.field
                }, G(r.value[e.field]), 9, pl)) : e.type == "textarea" ? (s(), c(o(Ve), {
                  key: 1,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (n) => r.value[e.field] = n,
                  modelModifiers: { trim: !0 },
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "number" ? (s(), c(o(Z), {
                  key: 2,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (n) => r.value[e.field] = n,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type == "autocomplete" ? (s(), c(o(ce), {
                  key: 3,
                  id: r.value[e.field],
                  "onUpdate:id": (n) => r.value[e.field] = n,
                  table: e.table,
                  options: (l = se.value[e.field]) == null ? void 0 : l.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "table", "options", "disabled"])) : e.type == "select" ? (s(), c(o(Pe), {
                  key: 4,
                  id: r.value[e.field],
                  "onUpdate:id": (n) => r.value[e.field] = n,
                  options: (a = ie.value[e.field]) == null ? void 0 : a.rows,
                  disabled: e.readonly
                }, null, 8, ["id", "onUpdate:id", "options", "disabled"])) : e.type == "decimal" ? (s(), c(o(Z), {
                  key: 5,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (n) => r.value[e.field] = n,
                  minFractionDigits: e.FractionDigits,
                  maxFractionDigits: e.FractionDigits,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "minFractionDigits", "maxFractionDigits", "disabled"])) : e.type == "boolean" ? (s(), c(o(ke), {
                  key: 6,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (n) => r.value[e.field] = n,
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"])) : e.type === "date" ? (s(), c(o(ge), {
                  key: 7,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (n) => r.value[e.field] = n,
                  disabled: e.readonly
                }, null, 8, ["modelValue", "onUpdate:modelValue", "disabled"])) : (s(), c(o(j), {
                  key: 8,
                  id: e.field,
                  modelValue: r.value[e.field],
                  "onUpdate:modelValue": (n) => r.value[e.field] = n,
                  modelModifiers: { trim: !0 },
                  disabled: e.readonly
                }, null, 8, ["id", "modelValue", "onUpdate:modelValue", "disabled"]))
              ]);
            }), 256))
          ]),
          _: 1
        }, 8, ["visible"]),
        v(o(pe), {
          visible: $.value,
          "onUpdate:visible": i[11] || (i[11] = (e) => $.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: b(() => [
            v(o(S), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: i[10] || (i[10] = (e) => $.value = !1)
            }),
            v(o(S), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: Ee
            })
          ]),
          default: b(() => [
            Q("div", cl, [
              fl,
              r.value ? (s(), w("span", ml, "Вы хотите удалить эту запись?")) : R("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"]),
        v(o(pe), {
          visible: K.value,
          "onUpdate:visible": i[13] || (i[13] = (e) => K.value = e),
          style: { width: "450px" },
          header: "Confirm",
          modal: !0
        }, {
          footer: b(() => [
            v(o(S), {
              label: "Нет",
              icon: "pi pi-times",
              class: "p-button-text",
              onClick: i[12] || (i[12] = (e) => K.value = !1)
            }),
            v(o(S), {
              label: "Да",
              icon: "pi pi-check",
              class: "p-button-text",
              onClick: $e
            })
          ]),
          default: b(() => [
            Q("div", vl, [
              yl,
              r.value ? (s(), w("span", bl, "Вы хотите удалить отмеченные записи?")) : R("", !0)
            ])
          ]),
          _: 1
        }, 8, ["visible"])
      ]);
    };
  }
}, Al = {
  install: (f, P) => {
    f.component(Ue.name, Ue);
  }
};
export {
  Ue as PVTables,
  Al as default
};
