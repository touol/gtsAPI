import { mergeModels as i, useModel as v, ref as d, watchEffect as g, openBlock as b, createBlock as S, unref as x } from "vue";
import y from "primevue/autocomplete";
const M = {
  __name: "gtsSelect",
  props: /* @__PURE__ */ i({
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
  emits: /* @__PURE__ */ i(["update:id", "set-value"], ["update:id"]),
  setup(n, { emit: u }) {
    const a = v(n, "id"), s = n, c = u, l = d({});
    g(() => {
      const [e] = s.options.filter((t) => a.value == t.id);
      e ? l.value = e : l.value = {};
    });
    const o = d([]), m = async ({ query: e }) => {
      e ? o.value = s.options.filter((t) => t.content.indexOf(e) != -1) : o.value = s.options;
    }, p = (e) => {
      o.value = [], a.value = e.value.id.toString(), c("set-value");
    }, r = () => {
      o.value = [];
    };
    return (e, t) => (b(), S(x(y), {
      modelValue: l.value,
      "onUpdate:modelValue": t[0] || (t[0] = (f) => l.value = f),
      dropdown: "",
      "option-label": "content",
      suggestions: o.value,
      onComplete: m,
      onItemSelect: p,
      onHide: r,
      disabled: n.disabled
    }, null, 8, ["modelValue", "suggestions", "disabled"]));
  }
};
export {
  M as default
};
