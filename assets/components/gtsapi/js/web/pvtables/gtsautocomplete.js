import { mergeModels as y, useModel as C, ref as i, watchEffect as A, openBlock as B, createBlock as h, unref as u, withKeys as g, withModifiers as x, withCtx as M, createVNode as b } from "vue";
import S from "primevue/autocomplete";
import K from "primevue/inputgroup";
import N from "primevue/inputtext";
import { useNotifications as U } from "pvtables/notify";
import k from "pvtables/api";
const G = {
  __name: "gtsAutoComplete",
  props: /* @__PURE__ */ y({
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
  emits: /* @__PURE__ */ y(["update:id", "set-value"], ["update:id"]),
  setup(s, { emit: w }) {
    const o = C(s, "id"), n = s, d = k(n.table), p = w, { notify: r } = U(), l = i({});
    A(async () => {
      if (Array.isArray(n.options) && n.options.length) {
        const [t] = n.options.filter((e) => o.value === e.id);
        t ? l.value = t : l.value = {};
      } else if (Number(o.value) > 0)
        try {
          const t = await f(o.value);
          if (!t) {
            r("error", { detail: "Отсутствует такой ID" });
            return;
          }
          l.value = t;
        } catch (t) {
          r("error", { detail: t.message });
        }
    });
    const c = i(""), m = i([]), I = async ({ query: t }) => {
      try {
        const e = await d.autocomplete({ query: t });
        m.value = e.data.rows;
      } catch (e) {
        r("error", { detail: e.message });
      }
    };
    async function f(t) {
      return (await d.autocomplete({ id: t })).data.rows[0] || null;
    }
    const v = async (t) => {
      const e = t.target.value;
      if (e === "" || e === "0") {
        o.value = e, l.value = {};
        return;
      }
      try {
        const a = await f(t.target.value);
        if (!a) {
          r("error", { detail: "Отсутствует такой ID" }), o.value = c.value;
          return;
        }
        l.value = a, o.value = e;
      } catch (a) {
        r("error", { detail: a.message });
      }
      p("set-value");
    }, V = (t) => {
      o.value = t.value.id, p("set-value");
    };
    return (t, e) => (B(), h(u(K), {
      onKeydown: e[3] || (e[3] = g(x(() => {
      }, ["stop"]), ["tab"]))
    }, {
      default: M(() => [
        b(u(N), {
          modelValue: o.value,
          "onUpdate:modelValue": e[0] || (e[0] = (a) => o.value = a),
          onBlur: v,
          onKeydown: g(v, ["enter"]),
          onFocus: e[1] || (e[1] = (a) => c.value = o.value),
          class: "gts-ac__id-field",
          disabled: s.disabled
        }, null, 8, ["modelValue", "disabled"]),
        b(u(S), {
          modelValue: l.value,
          "onUpdate:modelValue": e[2] || (e[2] = (a) => l.value = a),
          dropdown: "",
          "option-label": "content",
          suggestions: m.value,
          class: "gts-ac__search-field",
          onComplete: I,
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
