import { computed as m, openBlock as d, createElementBlock as p, createVNode as s, unref as i } from "vue";
import c from "primevue/calendar";
const v = {
  __name: "GTSDate",
  props: {
    modelValue: {
      type: String,
      default: ""
    }
  },
  emits: ["update:modelValue"],
  setup(l, { emit: a }) {
    const r = l, u = a, t = m({
      get() {
        return r.modelValue.split("-").reverse().join(".");
      },
      set(o) {
        const e = o.toLocaleDateString("ru-RU").split(".").reverse().join("-");
        u("update:modelValue", e);
      }
    });
    return (o, e) => (d(), p("div", null, [
      s(i(c), {
        modelValue: t.value,
        "onUpdate:modelValue": e[0] || (e[0] = (n) => t.value = n)
      }, null, 8, ["modelValue"])
    ]));
  }
};
export {
  v as default
};
