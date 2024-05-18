import { mergeModels as v, useModel as b, ref as r, watchEffect as x, openBlock as B, createBlock as M, unref as s, withKeys as g, withModifiers as S, withCtx as A, createVNode as y } from "vue";
import K from "primevue/autocomplete";
import U from "primevue/inputgroup";
import "axios";
import h from "primevue/inputtext";
import { useNotifications as k } from "pvtables/notify";
import E from "pvtables/api";
const T = {
  __name: "gtsAutoComplete",
  props: /* @__PURE__ */ v({
    table: {
      type: String,
      required: !0
    },
    options: {
      type: Object,
      default: () => []
    }
  }, {
    id: {
      type: String,
      default: ""
    },
    idModifiers: {}
  }),
  emits: /* @__PURE__ */ v(["update:id", "set-value"], ["update:id"]),
  setup(u, { emit: w }) {
    const a = b(u, "id"), i = u, d = E(i.table), p = w, { notify: n } = k(), l = r({});
    x(() => {
      const [t] = i.options.filter((e) => a.value === e.id);
      t ? l.value = t : l.value = {};
    });
    const m = r(""), c = r([]), I = async ({ query: t }) => {
      try {
        const e = await d.autocomplete({ query: t });
        c.value = e.data.rows;
      } catch (e) {
        n("error", { detail: e.message });
      }
    };
    async function V(t) {
      return (await d.autocomplete({ id: t })).data.rows[0] || null;
    }
    const f = async (t) => {
      const e = t.target.value;
      if (e === "" || e === "0") {
        a.value = e, l.value = {};
        return;
      }
      try {
        const o = await V(t.target.value);
        if (!o) {
          n("error", { detail: "Отсутствует такой ID" }), a.value = m.value;
          return;
        }
        l.value = o, a.value = e;
      } catch (o) {
        n("error", { detail: o.message });
      }
      p("set-value");
    }, C = (t) => {
      a.value = t.value.id, p("set-value");
    };
    return (t, e) => (B(), M(s(U), {
      onKeydown: e[3] || (e[3] = g(S(() => {
      }, ["stop"]), ["tab"]))
    }, {
      default: A(() => [
        y(s(h), {
          modelValue: a.value,
          "onUpdate:modelValue": e[0] || (e[0] = (o) => a.value = o),
          onBlur: f,
          onKeydown: g(f, ["enter"]),
          onFocus: e[1] || (e[1] = (o) => m.value = a.value),
          class: "gts-ac__id-field"
        }, null, 8, ["modelValue"]),
        y(s(K), {
          modelValue: l.value,
          "onUpdate:modelValue": e[2] || (e[2] = (o) => l.value = o),
          dropdown: "",
          "option-label": "content",
          suggestions: c.value,
          class: "gts-ac__search-field",
          onComplete: I,
          onItemSelect: C
        }, null, 8, ["modelValue", "suggestions"])
      ]),
      _: 1
    }));
  }
};
export {
  T as default
};
