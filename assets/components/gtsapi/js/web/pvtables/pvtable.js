import { ref as r, openBlock as e, createElementBlock as o, Fragment as c, createBlock as s, unref as n, createVNode as i, createApp as p } from "vue";
import "primevue/button";
import { PVTables as m } from "pvtables/pvtables";
import u from "primevue/toast";
import f from "primevue/config";
import b from "primevue/toastservice";
const T = { key: 0 }, _ = { key: 1 }, V = {
  __name: "PVTable",
  setup(k) {
    console.log("PVTableConfigTable", PVTableConfigTable);
    const l = r(PVTableConfigTable), t = r(!1);
    return t.value = !!PVTableConfigTable, (v, P) => (e(), o(c, null, [
      t.value ? (e(), o("div", T, [
        (e(), s(n(m), {
          table: l.value,
          actions: {},
          filters: {},
          reload: !1,
          key: l.value
        }, null, 8, ["table"]))
      ])) : (e(), o("p", _, "Таблица не задана!")),
      i(n(u))
    ], 64));
  }
}, a = p(V);
a.use(f, { ripple: !0 });
a.use(b);
a.mount("#pvtable");
