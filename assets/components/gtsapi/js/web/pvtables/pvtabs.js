import { openBlock as a, createElementBlock as l, Fragment as o, createVNode as n, unref as r, withCtx as s, renderList as f, createBlock as i } from "vue";
import { PVTables as u } from "pvtables/pvtables";
import m from "primevue/toast";
import b from "primevue/tabview";
import d from "primevue/tabpanel";
const p = {
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
  setup(e) {
    return (c, T) => (a(), l(o, null, [
      n(r(b), null, {
        default: s(() => [
          (a(!0), l(o, null, f(e.tabs, (t) => (a(), i(r(d), {
            key: t.title,
            header: t.title
          }, {
            default: s(() => [
              (a(), i(r(u), {
                table: t.table,
                actions: e.actions,
                filters: e.filters,
                reload: !1,
                key: t.table
              }, null, 8, ["table", "actions", "filters"]))
            ]),
            _: 2
          }, 1032, ["header"]))), 128))
        ]),
        _: 1
      }),
      n(r(m))
    ], 64));
  }
}, x = {
  install: (e, c) => {
    e.component("PVTabs", p);
  }
};
export {
  p as PVTabs,
  x as default
};
