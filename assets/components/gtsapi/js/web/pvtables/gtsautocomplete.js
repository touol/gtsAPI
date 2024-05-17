import { mergeModels as f, useModel as x, ref as n, watchEffect as C, openBlock as h, createBlock as B, unref as u, withKeys as v, withModifiers as E, withCtx as M, createVNode as g } from "vue";
import S from "primevue/autocomplete";
import A from "primevue/inputgroup";
import w from "axios";
import K from "primevue/inputtext";
import { useNotifications as U } from "pvtables/notify";
const D = {
  __name: "gtsAutoComplete",
  props: /* @__PURE__ */ f({
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
  emits: /* @__PURE__ */ f(["update:id", "set-value"], ["update:id"]),
  setup(i, { emit: y }) {
    const a = x(i, "id"), r = i, d = y, { notify: l } = U(), s = n({});
    C(() => {
      const [t] = r.options.filter((e) => a.value === e.id);
      t ? s.value = t : s.value = {};
    });
    const p = n(""), c = n([]), I = async ({ query: t }) => {
      try {
        const e = await w.post(
          "/api/" + r.table,
          {},
          {
            params: {
              api_action: "autocomplete",
              query: t
            }
          }
        );
        if (!e.data.success)
          throw new Error(e.data.message);
        c.value = e.data.data.rows;
      } catch (e) {
        l("error", e.message);
      }
    };
    async function V(t) {
      const e = await w.post(
        "/api/" + r.table,
        {},
        {
          params: {
            api_action: "autocomplete",
            id: t
          }
        }
      );
      if (!e.data.success)
        throw new Error(e.data.message);
      return e.data.data.rows[0] || null;
    }
    const m = async (t) => {
      const e = t.target.value;
      if (e === "" || e === "0") {
        a.value = e, s.value = {};
        return;
      }
      try {
        const o = await V(t.target.value);
        if (!o) {
          l("error", { detail: "Отсутствует такой ID" }), a.value = p.value;
          return;
        }
        s.value = o, a.value = e;
      } catch (o) {
        l("error", { detail: o.message });
      }
      d("set-value");
    }, b = (t) => {
      a.value = t.value.id, d("set-value");
    };
    return (t, e) => (h(), B(u(A), {
      onKeydown: e[3] || (e[3] = v(E(() => {
      }, ["stop"]), ["tab"]))
    }, {
      default: M(() => [
        g(u(K), {
          modelValue: a.value,
          "onUpdate:modelValue": e[0] || (e[0] = (o) => a.value = o),
          onBlur: m,
          onKeydown: v(m, ["enter"]),
          onFocus: e[1] || (e[1] = (o) => p.value = a.value),
          class: "gts-ac__id-field"
        }, null, 8, ["modelValue"]),
        g(u(S), {
          modelValue: s.value,
          "onUpdate:modelValue": e[2] || (e[2] = (o) => s.value = o),
          dropdown: "",
          "option-label": "content",
          suggestions: c.value,
          class: "gts-ac__search-field",
          onComplete: I,
          onItemSelect: b
        }, null, 8, ["modelValue", "suggestions"])
      ]),
      _: 1
    }));
  }
};
export {
  D as default
};
