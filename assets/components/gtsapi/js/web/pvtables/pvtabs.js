import { ref as p, openBlock as l, createElementBlock as c, Fragment as u, createVNode as m, unref as n, withCtx as d, renderList as y, createBlock as k } from "vue";
import { PVTables as b } from "pvtables/pvtables";
import h from "primevue/toast";
import T from "primevue/tabview";
import V from "primevue/tabpanel";
const x = {
  __name: "PVTabs",
  props: {
    tabs: {
      type: Object,
      required: !0
    },
    actions: {
      type: Object,
      default: {}
    },
    filters: {
      type: Object,
      default: {}
    }
  },
  setup(t, { expose: f }) {
    const s = t, o = p({});
    for (let e in s.tabs)
      s.tabs[e].key = e;
    return f({ refresh: (e) => {
      if (e) {
        o.value[e].refresh(e);
        for (let a in s.tabs)
          o.value[a].refresh(e);
      } else
        for (let a in s.tabs)
          o.value[a].refresh();
    } }), (e, a) => (l(), c(u, null, [
      m(n(T), null, {
        default: d(() => [
          (l(!0), c(u, null, y(t.tabs, (r) => (l(), k(n(V), {
            key: r.key,
            header: r.title
          }, {
            default: d(() => [
              (l(), k(n(b), {
                table: r.table,
                actions: t.actions,
                filters: t.filters[r.key],
                reload: !1,
                key: r.key,
                ref_for: !0,
                ref: (i) => {
                  i && (o.value[r.key] = i);
                }
              }, null, 8, ["table", "actions", "filters"]))
            ]),
            _: 2
          }, 1032, ["header"]))), 128))
        ]),
        _: 1
      }),
      m(n(h))
    ], 64));
  }
}, w = {
  install: (t, f) => {
    t.component("PVTabs", x);
  }
};
export {
  x as PVTabs,
  w as default
};
