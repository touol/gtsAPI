import a from "axios";
import { useNotifications as p } from "pvtables/notify";
const l = (o) => {
  const t = a.create({
    baseURL: `/api/${o}`,
    timeout: 1e4
  }), { notify: n } = p();
  return t.interceptors.request.use(
    (e) => e,
    (e) => {
      n("error", { detail: e.message }), Promise.reject(e);
    }
  ), t.interceptors.response.use(
    ({ data: e }) => {
      if (!e.success)
        throw new Error(response.message);
      return e;
    },
    ({ message: e, response: s }) => {
      n("error", { detail: e });
    }
  ), {
    create: async (e = null, s = {}) => await t.put("/", e, { params: s }),
    read: async (e = {}) => await t.get("/", { params: e }),
    update: async (e = null, s = {}) => await t.patch("/", e, s),
    delete: async (e = {}) => await t.delete("/", { params: e }),
    options: async (e = null, s = {}) => {
      const r = {
        api_action: "options",
        ...s
      };
      return await t.post("/", e, { params: r });
    },
    autocomplete: async (e = {}) => {
      const s = {
        api_action: "autocomplete",
        ...e
      };
      return await t.post("/", null, { params: s });
    }
  };
};
export {
  l as default
};
