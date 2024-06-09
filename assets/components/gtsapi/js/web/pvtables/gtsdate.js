import { computed as n, openBlock as s, createElementBlock as m, createVNode as i, unref as p } from "vue";
import c from "primevue/calendar";
const b = {
  __name: "GTSDate",
  props: {
    modelValue: {
      type: String,
      default: ""
    },
    disabled: {
      type: Boolean,
      default: !1
    }
  },
  emits: ["update:modelValue"],
  setup(l, { emit: d }) {
    const o = l, r = d, a = n({
      get() {
        return o.modelValue ? o.modelValue.split("-").reverse().join(".") : "";
      },
      set(t) {
        let e = "";
        t && (e = t.toLocaleDateString("ru-RU").split(".").reverse().join("-")), r("update:modelValue", e);
      }
    });
    return (t, e) => (s(), m("div", null, [
      i(p(c), {
        modelValue: a.value,
        "onUpdate:modelValue": e[0] || (e[0] = (u) => a.value = u),
        showIcon: "",
        showOnFocus: !1,
        disabled: l.disabled
      }, null, 8, ["modelValue", "disabled"])
    ]));
  }
};
export {
  b as default
};
