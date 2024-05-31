import { computed as r, openBlock as s, createElementBlock as m, createVNode as i, unref as p } from "vue";
import c from "primevue/calendar";
const v = {
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
  setup(t, { emit: a }) {
    const d = t, u = a, l = r({
      get() {
        return d.modelValue.split("-").reverse().join(".");
      },
      set(o) {
        const e = o.toLocaleDateString("ru-RU").split(".").reverse().join("-");
        u("update:modelValue", e);
      }
    });
    return (o, e) => (s(), m("div", null, [
      i(p(c), {
        modelValue: l.value,
        "onUpdate:modelValue": e[0] || (e[0] = (n) => l.value = n),
        showIcon: "",
        showOnFocus: !1,
        disabled: t.disabled
      }, null, 8, ["modelValue", "disabled"])
    ]));
  }
};
export {
  v as default
};
