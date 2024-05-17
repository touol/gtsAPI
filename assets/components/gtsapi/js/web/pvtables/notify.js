import { useToast as c } from "primevue/usetoast";
const i = {
  success: {
    severity: "success",
    summary: "Успешно",
    life: 3e3
  },
  error: {
    severity: "error",
    summary: "Ошибка",
    life: 3e3
  }
}, a = {
  success: "info",
  error: "error",
  warning: "warn"
}, u = () => {
  const s = c();
  return { notify: (e = "", r, t = !1) => {
    const o = {
      ...i[e],
      ...r
    };
    if (s.add(o), t) {
      const n = a[e];
      console[n](o.detail);
    }
  }, toast: s };
};
export {
  u as useNotifications
};
