import c from "axios";
import { useNotifications as i } from "pvtables/notify";
const m = (o, a = 1e4) => {
  const t = c.create({
    baseURL: `/api/${o}`,
    timeout: a
  }), { notify: n } = i();
  return t.interceptors.request.use(
    (e) => e,
    (e) => {
      n("error", { detail: e.message }), Promise.reject(e);
    }
  ), t.interceptors.response.use(
    ({ data: e }) => {
      if (!e.success)
        throw new Error(e.message);
      return e;
    },
    ({ message: e, response: s }) => {
      n("error", { detail: e });
    }
  ), {
    create: async (e = null, s = {}) => await t.put("/", e, { params: s }),
    get: async (e) => {
      let s = {
        limit: 1,
        setTotal: 0,
        filters: { id: { value: e, matchMode: "equals" } }
      };
      const r = await t.get("/", { params: s });
      if (r.data.rows.length == 1)
        return r.data.rows[0];
      throw new Error(r.message);
    },
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
    },
    action: async (e, s = {}) => {
      const r = {
        api_action: e,
        ...s
      };
      return await t.post("/", null, { params: r });
    }
  };
};
export {
  m as default
};
