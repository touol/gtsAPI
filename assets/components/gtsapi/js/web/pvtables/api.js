import p from "axios";
import { useNotifications as c } from "pvtables/notify";
const l = (o) => {
  const t = p.create({
    baseURL: `/api/${o}`,
    timeout: 1e4
  }), { notify: n } = c();
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
    update: async (e = null, s = {}) => await t.patch("/", e, { params: s }),
    delete: async (e = {}) => await t.delete("/", { params: e }),
    options: async (e = null, s = {}) => {
      const r = {
        api_action: "options",
        ...s
      };
      return await t.post("/", e, { params: r });
    },
    autocomplete: async (e = null, s = {}) => {
      const r = {
        api_action: "autocomplete",
        ...s
      };
      return await t.post("/", e, { params: r });
    }
  };
};
export {
  l as default
};
