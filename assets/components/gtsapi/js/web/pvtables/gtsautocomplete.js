import { mergeModels as v, useModel as C, ref as r, watchEffect as B, openBlock as x, createBlock as M, unref as u, withKeys as g, withModifiers as S, withCtx as A, createVNode as y } from "vue";
import K from "primevue/autocomplete";
import U from "primevue/inputgroup";
import h from "primevue/inputtext";
import { useNotifications as k } from "pvtables/notify";
import E from "pvtables/api";
const G = {
  __name: "gtsAutoComplete",
  props: /* @__PURE__ */ v({
    table: {
      type: String,
      required: !0
    },
    disabled: {
      type: Boolean,
      default: !1
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
  setup(s, { emit: b }) {
    const a = C(s, "id"), i = s, d = E(i.table), p = b, { notify: n } = k(), l = r({});
    B(() => {
      const [t] = i.options.filter((e) => a.value === e.id);
      t ? l.value = t : l.value = {};
    });
    const m = r(""), c = r([]), w = async ({ query: t }) => {
      try {
        const e = await d.autocomplete({ query: t });
        c.value = e.data.rows;
      } catch (e) {
        n("error", { detail: e.message });
      }
    };
    async function I(t) {
      return (await d.autocomplete({ id: t })).data.rows[0] || null;
    }
    const f = async (t) => {
      const e = t.target.value;
      if (e === "" || e === "0") {
        a.value = e, l.value = {};
        return;
      }
      try {
        const o = await I(t.target.value);
        if (!o) {
          n("error", { detail: "Отсутствует такой ID" }), a.value = m.value;
          return;
        }
        l.value = o, a.value = e;
      } catch (o) {
        n("error", { detail: o.message });
      }
      p("set-value");
    }, V = (t) => {
      a.value = t.value.id, p("set-value");
    };
    return (t, e) => (x(), M(u(U), {
      onKeydown: e[3] || (e[3] = g(S(() => {
      }, ["stop"]), ["tab"]))
    }, {
      default: A(() => [
        y(u(h), {
          modelValue: a.value,
          "onUpdate:modelValue": e[0] || (e[0] = (o) => a.value = o),
          onBlur: f,
          onKeydown: g(f, ["enter"]),
          onFocus: e[1] || (e[1] = (o) => m.value = a.value),
          class: "gts-ac__id-field",
          disabled: s.disabled
        }, null, 8, ["modelValue", "disabled"]),
        y(u(K), {
          modelValue: l.value,
          "onUpdate:modelValue": e[2] || (e[2] = (o) => l.value = o),
          dropdown: "",
          "option-label": "content",
          suggestions: c.value,
          class: "gts-ac__search-field",
          onComplete: w,
          onItemSelect: V,
          disabled: s.disabled
        }, null, 8, ["modelValue", "suggestions", "disabled"])
      ]),
      _: 1
    }));
  }
};
export {
  G as default
};
