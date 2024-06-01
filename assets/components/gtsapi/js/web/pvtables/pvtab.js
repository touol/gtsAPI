import { ref as a, openBlock as e, createElementBlock as o, Fragment as i, createVNode as n, unref as c, createApp as p } from "vue";
import { PVTabs as l } from "pvtables/pvtabs";
import m from "primevue/toast";
import f from "primevue/config";
import u from "primevue/toastservice";
const _ = { key: 0 }, b = { key: 1 }, T = {
  __name: "PVTab",
  setup(V) {
    console.log("PVTabsConfigs", PVTabsConfigs);
    const s = a(PVTabsConfigs), r = a(!1);
    return r.value = !!s, (P, g) => (e(), o(i, null, [
      r.value ? (e(), o("div", _, [
        n(c(l), {
          tabs: s.value,
          actions: {},
          filters: {}
        }, null, 8, ["tabs"])
      ])) : (e(), o("p", b, "Табы не заданы!")),
      n(c(m))
    ], 64));
  }
}, t = p(T);
t.use(f, { ripple: !0 });
t.use(u);
t.mount("#pvtab");
