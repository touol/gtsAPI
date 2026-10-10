import { defineComponent as ht, ref as $u, onBeforeUnmount as Zu, createVNode as Ye, useId as ii, provide as qe, computed as Qe, inject as oe, Fragment as oi, watch as ve, toRef as Ln, nextTick as wu, onMounted as nu, h as ai, shallowRef as bt } from "vue";
import { g as si } from "./_commonjsHelpers-DaMA6jEr.js";
const ci = (e) => {
  const u = typeof e;
  return u !== "function" && u !== "object" || e === null;
}, li = (e) => {
  const u = e.flags === "" ? void 0 : e.flags;
  return new RegExp(e.source, u);
}, Nu = (e, u = /* @__PURE__ */ new WeakMap()) => {
  if (e === null || ci(e))
    return e;
  if (u.has(e))
    return u.get(e);
  if (e instanceof RegExp)
    return li(e);
  if (e instanceof Date)
    return new Date(e.getTime());
  if (e instanceof Function)
    return e;
  if (e instanceof Map) {
    const r = /* @__PURE__ */ new Map();
    return u.set(e, r), e.forEach((i, t) => {
      r.set(t, Nu(i, u));
    }), r;
  }
  if (e instanceof Set) {
    const r = /* @__PURE__ */ new Set();
    u.set(e, r);
    for (const i of e)
      r.add(Nu(i, u));
    return r;
  }
  if (Array.isArray(e)) {
    const r = [];
    return u.set(e, r), e.forEach((i) => {
      r.push(Nu(i, u));
    }), r;
  }
  const n = {};
  u.set(e, n);
  for (const r in e)
    Object.prototype.hasOwnProperty.call(e, r) && (n[r] = Nu(e[r], u));
  return n;
}, fi = (e, u = 200) => {
  let n = 0;
  return (...r) => new Promise((i) => {
    n && (clearTimeout(n), i("cancel")), n = window.setTimeout(() => {
      e.apply(void 0, r), n = 0, i("done");
    }, u);
  });
}, Qt = () => `${Date.now().toString(36)}${Math.random().toString(36).substring(2)}`, lt = (e) => e !== null && typeof e == "object" && !Array.isArray(e), di = /* @__PURE__ */ new Set(["__proto__", "constructor", "prototype"]), pi = (e) => !di.has(e), gt = (e, u, n = {}) => {
  if (Array.isArray(e) && Array.isArray(u))
    return Yt(e, u, n);
  const { excludeKeys: r } = n, i = e, t = u;
  for (const o of Object.keys(t)) {
    if (!pi(o))
      continue;
    const a = t[o], s = i[o];
    r && r(o) ? i[o] = a : Array.isArray(a) && Array.isArray(s) ? i[o] = Yt(s, a, n) : lt(a) && lt(s) ? i[o] = gt(
      s,
      a,
      n
    ) : i[o] = a;
  }
  return e;
}, Yt = (e, u, n) => {
  const r = e.slice();
  return u.forEach((i, t) => {
    const o = r[t];
    Array.isArray(i) && Array.isArray(o) ? r[t] = Yt(o, i, n) : lt(i) && lt(o) ? r[t] = gt(
      o,
      i,
      n
    ) : r[t] = i;
  }), r;
};
var hi = /[\u1680\u2000-\u200A\u202F\u205F\u3000]/, bi = /[\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u0860-\u086A\u08A0-\u08B4\u08B6-\u08BD\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u09FC\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C80\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D54-\u0D56\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16EE-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u1884\u1887-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1C80-\u1C88\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2160-\u2188\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005-\u3007\u3021-\u3029\u3031-\u3035\u3038-\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312E\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FEA\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6EF\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AE\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]|\uD800[\uDC00-\uDC0B\uDC0D-\uDC26\uDC28-\uDC3A\uDC3C\uDC3D\uDC3F-\uDC4D\uDC50-\uDC5D\uDC80-\uDCFA\uDD40-\uDD74\uDE80-\uDE9C\uDEA0-\uDED0\uDF00-\uDF1F\uDF2D-\uDF4A\uDF50-\uDF75\uDF80-\uDF9D\uDFA0-\uDFC3\uDFC8-\uDFCF\uDFD1-\uDFD5]|\uD801[\uDC00-\uDC9D\uDCB0-\uDCD3\uDCD8-\uDCFB\uDD00-\uDD27\uDD30-\uDD63\uDE00-\uDF36\uDF40-\uDF55\uDF60-\uDF67]|\uD802[\uDC00-\uDC05\uDC08\uDC0A-\uDC35\uDC37\uDC38\uDC3C\uDC3F-\uDC55\uDC60-\uDC76\uDC80-\uDC9E\uDCE0-\uDCF2\uDCF4\uDCF5\uDD00-\uDD15\uDD20-\uDD39\uDD80-\uDDB7\uDDBE\uDDBF\uDE00\uDE10-\uDE13\uDE15-\uDE17\uDE19-\uDE33\uDE60-\uDE7C\uDE80-\uDE9C\uDEC0-\uDEC7\uDEC9-\uDEE4\uDF00-\uDF35\uDF40-\uDF55\uDF60-\uDF72\uDF80-\uDF91]|\uD803[\uDC00-\uDC48\uDC80-\uDCB2\uDCC0-\uDCF2]|\uD804[\uDC03-\uDC37\uDC83-\uDCAF\uDCD0-\uDCE8\uDD03-\uDD26\uDD50-\uDD72\uDD76\uDD83-\uDDB2\uDDC1-\uDDC4\uDDDA\uDDDC\uDE00-\uDE11\uDE13-\uDE2B\uDE80-\uDE86\uDE88\uDE8A-\uDE8D\uDE8F-\uDE9D\uDE9F-\uDEA8\uDEB0-\uDEDE\uDF05-\uDF0C\uDF0F\uDF10\uDF13-\uDF28\uDF2A-\uDF30\uDF32\uDF33\uDF35-\uDF39\uDF3D\uDF50\uDF5D-\uDF61]|\uD805[\uDC00-\uDC34\uDC47-\uDC4A\uDC80-\uDCAF\uDCC4\uDCC5\uDCC7\uDD80-\uDDAE\uDDD8-\uDDDB\uDE00-\uDE2F\uDE44\uDE80-\uDEAA\uDF00-\uDF19]|\uD806[\uDCA0-\uDCDF\uDCFF\uDE00\uDE0B-\uDE32\uDE3A\uDE50\uDE5C-\uDE83\uDE86-\uDE89\uDEC0-\uDEF8]|\uD807[\uDC00-\uDC08\uDC0A-\uDC2E\uDC40\uDC72-\uDC8F\uDD00-\uDD06\uDD08\uDD09\uDD0B-\uDD30\uDD46]|\uD808[\uDC00-\uDF99]|\uD809[\uDC00-\uDC6E\uDC80-\uDD43]|[\uD80C\uD81C-\uD820\uD840-\uD868\uD86A-\uD86C\uD86F-\uD872\uD874-\uD879][\uDC00-\uDFFF]|\uD80D[\uDC00-\uDC2E]|\uD811[\uDC00-\uDE46]|\uD81A[\uDC00-\uDE38\uDE40-\uDE5E\uDED0-\uDEED\uDF00-\uDF2F\uDF40-\uDF43\uDF63-\uDF77\uDF7D-\uDF8F]|\uD81B[\uDF00-\uDF44\uDF50\uDF93-\uDF9F\uDFE0\uDFE1]|\uD821[\uDC00-\uDFEC]|\uD822[\uDC00-\uDEF2]|\uD82C[\uDC00-\uDD1E\uDD70-\uDEFB]|\uD82F[\uDC00-\uDC6A\uDC70-\uDC7C\uDC80-\uDC88\uDC90-\uDC99]|\uD835[\uDC00-\uDC54\uDC56-\uDC9C\uDC9E\uDC9F\uDCA2\uDCA5\uDCA6\uDCA9-\uDCAC\uDCAE-\uDCB9\uDCBB\uDCBD-\uDCC3\uDCC5-\uDD05\uDD07-\uDD0A\uDD0D-\uDD14\uDD16-\uDD1C\uDD1E-\uDD39\uDD3B-\uDD3E\uDD40-\uDD44\uDD46\uDD4A-\uDD50\uDD52-\uDEA5\uDEA8-\uDEC0\uDEC2-\uDEDA\uDEDC-\uDEFA\uDEFC-\uDF14\uDF16-\uDF34\uDF36-\uDF4E\uDF50-\uDF6E\uDF70-\uDF88\uDF8A-\uDFA8\uDFAA-\uDFC2\uDFC4-\uDFCB]|\uD83A[\uDC00-\uDCC4\uDD00-\uDD43]|\uD83B[\uDE00-\uDE03\uDE05-\uDE1F\uDE21\uDE22\uDE24\uDE27\uDE29-\uDE32\uDE34-\uDE37\uDE39\uDE3B\uDE42\uDE47\uDE49\uDE4B\uDE4D-\uDE4F\uDE51\uDE52\uDE54\uDE57\uDE59\uDE5B\uDE5D\uDE5F\uDE61\uDE62\uDE64\uDE67-\uDE6A\uDE6C-\uDE72\uDE74-\uDE77\uDE79-\uDE7C\uDE7E\uDE80-\uDE89\uDE8B-\uDE9B\uDEA1-\uDEA3\uDEA5-\uDEA9\uDEAB-\uDEBB]|\uD869[\uDC00-\uDED6\uDF00-\uDFFF]|\uD86D[\uDC00-\uDF34\uDF40-\uDFFF]|\uD86E[\uDC00-\uDC1D\uDC20-\uDFFF]|\uD873[\uDC00-\uDEA1\uDEB0-\uDFFF]|\uD87A[\uDC00-\uDFE0]|\uD87E[\uDC00-\uDE1D]/, gi = /[\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0300-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u0483-\u0487\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u0591-\u05BD\u05BF\u05C1\u05C2\u05C4\u05C5\u05C7\u05D0-\u05EA\u05F0-\u05F2\u0610-\u061A\u0620-\u0669\u066E-\u06D3\u06D5-\u06DC\u06DF-\u06E8\u06EA-\u06FC\u06FF\u0710-\u074A\u074D-\u07B1\u07C0-\u07F5\u07FA\u0800-\u082D\u0840-\u085B\u0860-\u086A\u08A0-\u08B4\u08B6-\u08BD\u08D4-\u08E1\u08E3-\u0963\u0966-\u096F\u0971-\u0983\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BC-\u09C4\u09C7\u09C8\u09CB-\u09CE\u09D7\u09DC\u09DD\u09DF-\u09E3\u09E6-\u09F1\u09FC\u0A01-\u0A03\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A3C\u0A3E-\u0A42\u0A47\u0A48\u0A4B-\u0A4D\u0A51\u0A59-\u0A5C\u0A5E\u0A66-\u0A75\u0A81-\u0A83\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABC-\u0AC5\u0AC7-\u0AC9\u0ACB-\u0ACD\u0AD0\u0AE0-\u0AE3\u0AE6-\u0AEF\u0AF9-\u0AFF\u0B01-\u0B03\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3C-\u0B44\u0B47\u0B48\u0B4B-\u0B4D\u0B56\u0B57\u0B5C\u0B5D\u0B5F-\u0B63\u0B66-\u0B6F\u0B71\u0B82\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BBE-\u0BC2\u0BC6-\u0BC8\u0BCA-\u0BCD\u0BD0\u0BD7\u0BE6-\u0BEF\u0C00-\u0C03\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D-\u0C44\u0C46-\u0C48\u0C4A-\u0C4D\u0C55\u0C56\u0C58-\u0C5A\u0C60-\u0C63\u0C66-\u0C6F\u0C80-\u0C83\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBC-\u0CC4\u0CC6-\u0CC8\u0CCA-\u0CCD\u0CD5\u0CD6\u0CDE\u0CE0-\u0CE3\u0CE6-\u0CEF\u0CF1\u0CF2\u0D00-\u0D03\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D44\u0D46-\u0D48\u0D4A-\u0D4E\u0D54-\u0D57\u0D5F-\u0D63\u0D66-\u0D6F\u0D7A-\u0D7F\u0D82\u0D83\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0DCA\u0DCF-\u0DD4\u0DD6\u0DD8-\u0DDF\u0DE6-\u0DEF\u0DF2\u0DF3\u0E01-\u0E3A\u0E40-\u0E4E\u0E50-\u0E59\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB9\u0EBB-\u0EBD\u0EC0-\u0EC4\u0EC6\u0EC8-\u0ECD\u0ED0-\u0ED9\u0EDC-\u0EDF\u0F00\u0F18\u0F19\u0F20-\u0F29\u0F35\u0F37\u0F39\u0F3E-\u0F47\u0F49-\u0F6C\u0F71-\u0F84\u0F86-\u0F97\u0F99-\u0FBC\u0FC6\u1000-\u1049\u1050-\u109D\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u135D-\u135F\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16EE-\u16F8\u1700-\u170C\u170E-\u1714\u1720-\u1734\u1740-\u1753\u1760-\u176C\u176E-\u1770\u1772\u1773\u1780-\u17D3\u17D7\u17DC\u17DD\u17E0-\u17E9\u180B-\u180D\u1810-\u1819\u1820-\u1877\u1880-\u18AA\u18B0-\u18F5\u1900-\u191E\u1920-\u192B\u1930-\u193B\u1946-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u19D0-\u19D9\u1A00-\u1A1B\u1A20-\u1A5E\u1A60-\u1A7C\u1A7F-\u1A89\u1A90-\u1A99\u1AA7\u1AB0-\u1ABD\u1B00-\u1B4B\u1B50-\u1B59\u1B6B-\u1B73\u1B80-\u1BF3\u1C00-\u1C37\u1C40-\u1C49\u1C4D-\u1C7D\u1C80-\u1C88\u1CD0-\u1CD2\u1CD4-\u1CF9\u1D00-\u1DF9\u1DFB-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u203F\u2040\u2054\u2071\u207F\u2090-\u209C\u20D0-\u20DC\u20E1\u20E5-\u20F0\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2160-\u2188\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D7F-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2DE0-\u2DFF\u2E2F\u3005-\u3007\u3021-\u302F\u3031-\u3035\u3038-\u303C\u3041-\u3096\u3099\u309A\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312E\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FEA\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA62B\uA640-\uA66F\uA674-\uA67D\uA67F-\uA6F1\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AE\uA7B0-\uA7B7\uA7F7-\uA827\uA840-\uA873\uA880-\uA8C5\uA8D0-\uA8D9\uA8E0-\uA8F7\uA8FB\uA8FD\uA900-\uA92D\uA930-\uA953\uA960-\uA97C\uA980-\uA9C0\uA9CF-\uA9D9\uA9E0-\uA9FE\uAA00-\uAA36\uAA40-\uAA4D\uAA50-\uAA59\uAA60-\uAA76\uAA7A-\uAAC2\uAADB-\uAADD\uAAE0-\uAAEF\uAAF2-\uAAF6\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABEA\uABEC\uABED\uABF0-\uABF9\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE00-\uFE0F\uFE20-\uFE2F\uFE33\uFE34\uFE4D-\uFE4F\uFE70-\uFE74\uFE76-\uFEFC\uFF10-\uFF19\uFF21-\uFF3A\uFF3F\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]|\uD800[\uDC00-\uDC0B\uDC0D-\uDC26\uDC28-\uDC3A\uDC3C\uDC3D\uDC3F-\uDC4D\uDC50-\uDC5D\uDC80-\uDCFA\uDD40-\uDD74\uDDFD\uDE80-\uDE9C\uDEA0-\uDED0\uDEE0\uDF00-\uDF1F\uDF2D-\uDF4A\uDF50-\uDF7A\uDF80-\uDF9D\uDFA0-\uDFC3\uDFC8-\uDFCF\uDFD1-\uDFD5]|\uD801[\uDC00-\uDC9D\uDCA0-\uDCA9\uDCB0-\uDCD3\uDCD8-\uDCFB\uDD00-\uDD27\uDD30-\uDD63\uDE00-\uDF36\uDF40-\uDF55\uDF60-\uDF67]|\uD802[\uDC00-\uDC05\uDC08\uDC0A-\uDC35\uDC37\uDC38\uDC3C\uDC3F-\uDC55\uDC60-\uDC76\uDC80-\uDC9E\uDCE0-\uDCF2\uDCF4\uDCF5\uDD00-\uDD15\uDD20-\uDD39\uDD80-\uDDB7\uDDBE\uDDBF\uDE00-\uDE03\uDE05\uDE06\uDE0C-\uDE13\uDE15-\uDE17\uDE19-\uDE33\uDE38-\uDE3A\uDE3F\uDE60-\uDE7C\uDE80-\uDE9C\uDEC0-\uDEC7\uDEC9-\uDEE6\uDF00-\uDF35\uDF40-\uDF55\uDF60-\uDF72\uDF80-\uDF91]|\uD803[\uDC00-\uDC48\uDC80-\uDCB2\uDCC0-\uDCF2]|\uD804[\uDC00-\uDC46\uDC66-\uDC6F\uDC7F-\uDCBA\uDCD0-\uDCE8\uDCF0-\uDCF9\uDD00-\uDD34\uDD36-\uDD3F\uDD50-\uDD73\uDD76\uDD80-\uDDC4\uDDCA-\uDDCC\uDDD0-\uDDDA\uDDDC\uDE00-\uDE11\uDE13-\uDE37\uDE3E\uDE80-\uDE86\uDE88\uDE8A-\uDE8D\uDE8F-\uDE9D\uDE9F-\uDEA8\uDEB0-\uDEEA\uDEF0-\uDEF9\uDF00-\uDF03\uDF05-\uDF0C\uDF0F\uDF10\uDF13-\uDF28\uDF2A-\uDF30\uDF32\uDF33\uDF35-\uDF39\uDF3C-\uDF44\uDF47\uDF48\uDF4B-\uDF4D\uDF50\uDF57\uDF5D-\uDF63\uDF66-\uDF6C\uDF70-\uDF74]|\uD805[\uDC00-\uDC4A\uDC50-\uDC59\uDC80-\uDCC5\uDCC7\uDCD0-\uDCD9\uDD80-\uDDB5\uDDB8-\uDDC0\uDDD8-\uDDDD\uDE00-\uDE40\uDE44\uDE50-\uDE59\uDE80-\uDEB7\uDEC0-\uDEC9\uDF00-\uDF19\uDF1D-\uDF2B\uDF30-\uDF39]|\uD806[\uDCA0-\uDCE9\uDCFF\uDE00-\uDE3E\uDE47\uDE50-\uDE83\uDE86-\uDE99\uDEC0-\uDEF8]|\uD807[\uDC00-\uDC08\uDC0A-\uDC36\uDC38-\uDC40\uDC50-\uDC59\uDC72-\uDC8F\uDC92-\uDCA7\uDCA9-\uDCB6\uDD00-\uDD06\uDD08\uDD09\uDD0B-\uDD36\uDD3A\uDD3C\uDD3D\uDD3F-\uDD47\uDD50-\uDD59]|\uD808[\uDC00-\uDF99]|\uD809[\uDC00-\uDC6E\uDC80-\uDD43]|[\uD80C\uD81C-\uD820\uD840-\uD868\uD86A-\uD86C\uD86F-\uD872\uD874-\uD879][\uDC00-\uDFFF]|\uD80D[\uDC00-\uDC2E]|\uD811[\uDC00-\uDE46]|\uD81A[\uDC00-\uDE38\uDE40-\uDE5E\uDE60-\uDE69\uDED0-\uDEED\uDEF0-\uDEF4\uDF00-\uDF36\uDF40-\uDF43\uDF50-\uDF59\uDF63-\uDF77\uDF7D-\uDF8F]|\uD81B[\uDF00-\uDF44\uDF50-\uDF7E\uDF8F-\uDF9F\uDFE0\uDFE1]|\uD821[\uDC00-\uDFEC]|\uD822[\uDC00-\uDEF2]|\uD82C[\uDC00-\uDD1E\uDD70-\uDEFB]|\uD82F[\uDC00-\uDC6A\uDC70-\uDC7C\uDC80-\uDC88\uDC90-\uDC99\uDC9D\uDC9E]|\uD834[\uDD65-\uDD69\uDD6D-\uDD72\uDD7B-\uDD82\uDD85-\uDD8B\uDDAA-\uDDAD\uDE42-\uDE44]|\uD835[\uDC00-\uDC54\uDC56-\uDC9C\uDC9E\uDC9F\uDCA2\uDCA5\uDCA6\uDCA9-\uDCAC\uDCAE-\uDCB9\uDCBB\uDCBD-\uDCC3\uDCC5-\uDD05\uDD07-\uDD0A\uDD0D-\uDD14\uDD16-\uDD1C\uDD1E-\uDD39\uDD3B-\uDD3E\uDD40-\uDD44\uDD46\uDD4A-\uDD50\uDD52-\uDEA5\uDEA8-\uDEC0\uDEC2-\uDEDA\uDEDC-\uDEFA\uDEFC-\uDF14\uDF16-\uDF34\uDF36-\uDF4E\uDF50-\uDF6E\uDF70-\uDF88\uDF8A-\uDFA8\uDFAA-\uDFC2\uDFC4-\uDFCB\uDFCE-\uDFFF]|\uD836[\uDE00-\uDE36\uDE3B-\uDE6C\uDE75\uDE84\uDE9B-\uDE9F\uDEA1-\uDEAF]|\uD838[\uDC00-\uDC06\uDC08-\uDC18\uDC1B-\uDC21\uDC23\uDC24\uDC26-\uDC2A]|\uD83A[\uDC00-\uDCC4\uDCD0-\uDCD6\uDD00-\uDD4A\uDD50-\uDD59]|\uD83B[\uDE00-\uDE03\uDE05-\uDE1F\uDE21\uDE22\uDE24\uDE27\uDE29-\uDE32\uDE34-\uDE37\uDE39\uDE3B\uDE42\uDE47\uDE49\uDE4B\uDE4D-\uDE4F\uDE51\uDE52\uDE54\uDE57\uDE59\uDE5B\uDE5D\uDE5F\uDE61\uDE62\uDE64\uDE67-\uDE6A\uDE6C-\uDE72\uDE74-\uDE77\uDE79-\uDE7C\uDE7E\uDE80-\uDE89\uDE8B-\uDE9B\uDEA1-\uDEA3\uDEA5-\uDEA9\uDEAB-\uDEBB]|\uD869[\uDC00-\uDED6\uDF00-\uDFFF]|\uD86D[\uDC00-\uDF34\uDF40-\uDFFF]|\uD86E[\uDC00-\uDC1D\uDC20-\uDFFF]|\uD873[\uDC00-\uDEA1\uDEB0-\uDFFF]|\uD87A[\uDC00-\uDFE0]|\uD87E[\uDC00-\uDE1D]|\uDB40[\uDD00-\uDDEF]/, Ot = {
  Space_Separator: hi,
  ID_Start: bi,
  ID_Continue: gi
}, De = {
  isSpaceSeparator(e) {
    return typeof e == "string" && Ot.Space_Separator.test(e);
  },
  isIdStartChar(e) {
    return typeof e == "string" && (e >= "a" && e <= "z" || e >= "A" && e <= "Z" || e === "$" || e === "_" || Ot.ID_Start.test(e));
  },
  isIdContinueChar(e) {
    return typeof e == "string" && (e >= "a" && e <= "z" || e >= "A" && e <= "Z" || e >= "0" && e <= "9" || e === "$" || e === "_" || e === "‌" || e === "‍" || Ot.ID_Continue.test(e));
  },
  isDigit(e) {
    return typeof e == "string" && /[0-9]/.test(e);
  },
  isHexDigit(e) {
    return typeof e == "string" && /[0-9A-Fa-f]/.test(e);
  }
};
let en, we, eu, ft, du, ze, _e, fn, Lu;
var mi = function(e, u) {
  en = String(e), we = "start", eu = [], ft = 0, du = 1, ze = 0, _e = void 0, fn = void 0, Lu = void 0;
  do
    _e = Di(), Ci[we]();
  while (_e.type !== "eof");
  return typeof u == "function" ? un({ "": Lu }, "", u) : Lu;
};
function un(e, u, n) {
  const r = e[u];
  if (r != null && typeof r == "object")
    if (Array.isArray(r))
      for (let i = 0; i < r.length; i++) {
        const t = String(i), o = un(r, t, n);
        o === void 0 ? delete r[t] : Object.defineProperty(r, t, {
          value: o,
          writable: !0,
          enumerable: !0,
          configurable: !0
        });
      }
    else
      for (const i in r) {
        const t = un(r, i, n);
        t === void 0 ? delete r[i] : Object.defineProperty(r, i, {
          value: t,
          writable: !0,
          enumerable: !0,
          configurable: !0
        });
      }
  return n.call(e, u, r);
}
let J, X, Ru, Je, ee;
function Di() {
  for (J = "default", X = "", Ru = !1, Je = 1; ; ) {
    ee = tu();
    const e = Rr[J]();
    if (e)
      return e;
  }
}
function tu() {
  if (en[ft])
    return String.fromCodePoint(en.codePointAt(ft));
}
function v() {
  const e = tu();
  return e === `
` ? (du++, ze = 0) : e ? ze += e.length : ze++, e && (ft += e.length), e;
}
const Rr = {
  default() {
    switch (ee) {
      case "	":
      case "\v":
      case "\f":
      case " ":
      case " ":
      case "\uFEFF":
      case `
`:
      case "\r":
      case "\u2028":
      case "\u2029":
        v();
        return;
      case "/":
        v(), J = "comment";
        return;
      case void 0:
        return v(), de("eof");
    }
    if (De.isSpaceSeparator(ee)) {
      v();
      return;
    }
    return Rr[we]();
  },
  comment() {
    switch (ee) {
      case "*":
        v(), J = "multiLineComment";
        return;
      case "/":
        v(), J = "singleLineComment";
        return;
    }
    throw be(v());
  },
  multiLineComment() {
    switch (ee) {
      case "*":
        v(), J = "multiLineCommentAsterisk";
        return;
      case void 0:
        throw be(v());
    }
    v();
  },
  multiLineCommentAsterisk() {
    switch (ee) {
      case "*":
        v();
        return;
      case "/":
        v(), J = "default";
        return;
      case void 0:
        throw be(v());
    }
    v(), J = "multiLineComment";
  },
  singleLineComment() {
    switch (ee) {
      case `
`:
      case "\r":
      case "\u2028":
      case "\u2029":
        v(), J = "default";
        return;
      case void 0:
        return v(), de("eof");
    }
    v();
  },
  value() {
    switch (ee) {
      case "{":
      case "[":
        return de("punctuator", v());
      case "n":
        return v(), Du("ull"), de("null", null);
      case "t":
        return v(), Du("rue"), de("boolean", !0);
      case "f":
        return v(), Du("alse"), de("boolean", !1);
      case "-":
      case "+":
        v() === "-" && (Je = -1), J = "sign";
        return;
      case ".":
        X = v(), J = "decimalPointLeading";
        return;
      case "0":
        X = v(), J = "zero";
        return;
      case "1":
      case "2":
      case "3":
      case "4":
      case "5":
      case "6":
      case "7":
      case "8":
      case "9":
        X = v(), J = "decimalInteger";
        return;
      case "I":
        return v(), Du("nfinity"), de("numeric", 1 / 0);
      case "N":
        return v(), Du("aN"), de("numeric", NaN);
      case '"':
      case "'":
        Ru = v() === '"', X = "", J = "string";
        return;
    }
    throw be(v());
  },
  identifierNameStartEscape() {
    if (ee !== "u")
      throw be(v());
    v();
    const e = tn();
    switch (e) {
      case "$":
      case "_":
        break;
      default:
        if (!De.isIdStartChar(e))
          throw zn();
        break;
    }
    X += e, J = "identifierName";
  },
  identifierName() {
    switch (ee) {
      case "$":
      case "_":
      case "‌":
      case "‍":
        X += v();
        return;
      case "\\":
        v(), J = "identifierNameEscape";
        return;
    }
    if (De.isIdContinueChar(ee)) {
      X += v();
      return;
    }
    return de("identifier", X);
  },
  identifierNameEscape() {
    if (ee !== "u")
      throw be(v());
    v();
    const e = tn();
    switch (e) {
      case "$":
      case "_":
      case "‌":
      case "‍":
        break;
      default:
        if (!De.isIdContinueChar(e))
          throw zn();
        break;
    }
    X += e, J = "identifierName";
  },
  sign() {
    switch (ee) {
      case ".":
        X = v(), J = "decimalPointLeading";
        return;
      case "0":
        X = v(), J = "zero";
        return;
      case "1":
      case "2":
      case "3":
      case "4":
      case "5":
      case "6":
      case "7":
      case "8":
      case "9":
        X = v(), J = "decimalInteger";
        return;
      case "I":
        return v(), Du("nfinity"), de("numeric", Je * (1 / 0));
      case "N":
        return v(), Du("aN"), de("numeric", NaN);
    }
    throw be(v());
  },
  zero() {
    switch (ee) {
      case ".":
        X += v(), J = "decimalPoint";
        return;
      case "e":
      case "E":
        X += v(), J = "decimalExponent";
        return;
      case "x":
      case "X":
        X += v(), J = "hexadecimal";
        return;
    }
    return de("numeric", Je * 0);
  },
  decimalInteger() {
    switch (ee) {
      case ".":
        X += v(), J = "decimalPoint";
        return;
      case "e":
      case "E":
        X += v(), J = "decimalExponent";
        return;
    }
    if (De.isDigit(ee)) {
      X += v();
      return;
    }
    return de("numeric", Je * Number(X));
  },
  decimalPointLeading() {
    if (De.isDigit(ee)) {
      X += v(), J = "decimalFraction";
      return;
    }
    throw be(v());
  },
  decimalPoint() {
    switch (ee) {
      case "e":
      case "E":
        X += v(), J = "decimalExponent";
        return;
    }
    if (De.isDigit(ee)) {
      X += v(), J = "decimalFraction";
      return;
    }
    return de("numeric", Je * Number(X));
  },
  decimalFraction() {
    switch (ee) {
      case "e":
      case "E":
        X += v(), J = "decimalExponent";
        return;
    }
    if (De.isDigit(ee)) {
      X += v();
      return;
    }
    return de("numeric", Je * Number(X));
  },
  decimalExponent() {
    switch (ee) {
      case "+":
      case "-":
        X += v(), J = "decimalExponentSign";
        return;
    }
    if (De.isDigit(ee)) {
      X += v(), J = "decimalExponentInteger";
      return;
    }
    throw be(v());
  },
  decimalExponentSign() {
    if (De.isDigit(ee)) {
      X += v(), J = "decimalExponentInteger";
      return;
    }
    throw be(v());
  },
  decimalExponentInteger() {
    if (De.isDigit(ee)) {
      X += v();
      return;
    }
    return de("numeric", Je * Number(X));
  },
  hexadecimal() {
    if (De.isHexDigit(ee)) {
      X += v(), J = "hexadecimalInteger";
      return;
    }
    throw be(v());
  },
  hexadecimalInteger() {
    if (De.isHexDigit(ee)) {
      X += v();
      return;
    }
    return de("numeric", Je * Number(X));
  },
  string() {
    switch (ee) {
      case "\\":
        v(), X += Ei();
        return;
      case '"':
        if (Ru)
          return v(), de("string", X);
        X += v();
        return;
      case "'":
        if (!Ru)
          return v(), de("string", X);
        X += v();
        return;
      case `
`:
      case "\r":
        throw be(v());
      case "\u2028":
      case "\u2029":
        Ai(ee);
        break;
      case void 0:
        throw be(v());
    }
    X += v();
  },
  start() {
    switch (ee) {
      case "{":
      case "[":
        return de("punctuator", v());
    }
    J = "value";
  },
  beforePropertyName() {
    switch (ee) {
      case "$":
      case "_":
        X = v(), J = "identifierName";
        return;
      case "\\":
        v(), J = "identifierNameStartEscape";
        return;
      case "}":
        return de("punctuator", v());
      case '"':
      case "'":
        Ru = v() === '"', J = "string";
        return;
    }
    if (De.isIdStartChar(ee)) {
      X += v(), J = "identifierName";
      return;
    }
    throw be(v());
  },
  afterPropertyName() {
    if (ee === ":")
      return de("punctuator", v());
    throw be(v());
  },
  beforePropertyValue() {
    J = "value";
  },
  afterPropertyValue() {
    switch (ee) {
      case ",":
      case "}":
        return de("punctuator", v());
    }
    throw be(v());
  },
  beforeArrayValue() {
    if (ee === "]")
      return de("punctuator", v());
    J = "value";
  },
  afterArrayValue() {
    switch (ee) {
      case ",":
      case "]":
        return de("punctuator", v());
    }
    throw be(v());
  },
  end() {
    throw be(v());
  }
};
function de(e, u) {
  return {
    type: e,
    value: u,
    line: du,
    column: ze
  };
}
function Du(e) {
  for (const u of e) {
    if (tu() !== u)
      throw be(v());
    v();
  }
}
function Ei() {
  switch (tu()) {
    case "b":
      return v(), "\b";
    case "f":
      return v(), "\f";
    case "n":
      return v(), `
`;
    case "r":
      return v(), "\r";
    case "t":
      return v(), "	";
    case "v":
      return v(), "\v";
    case "0":
      if (v(), De.isDigit(tu()))
        throw be(v());
      return "\0";
    case "x":
      return v(), xi();
    case "u":
      return v(), tn();
    case `
`:
    case "\u2028":
    case "\u2029":
      return v(), "";
    case "\r":
      return v(), tu() === `
` && v(), "";
    case "1":
    case "2":
    case "3":
    case "4":
    case "5":
    case "6":
    case "7":
    case "8":
    case "9":
      throw be(v());
    case void 0:
      throw be(v());
  }
  return v();
}
function xi() {
  let e = "", u = tu();
  if (!De.isHexDigit(u) || (e += v(), u = tu(), !De.isHexDigit(u)))
    throw be(v());
  return e += v(), String.fromCodePoint(parseInt(e, 16));
}
function tn() {
  let e = "", u = 4;
  for (; u-- > 0; ) {
    const n = tu();
    if (!De.isHexDigit(n))
      throw be(v());
    e += v();
  }
  return String.fromCodePoint(parseInt(e, 16));
}
const Ci = {
  start() {
    if (_e.type === "eof")
      throw Eu();
    Nt();
  },
  beforePropertyName() {
    switch (_e.type) {
      case "identifier":
      case "string":
        fn = _e.value, we = "afterPropertyName";
        return;
      case "punctuator":
        tt();
        return;
      case "eof":
        throw Eu();
    }
  },
  afterPropertyName() {
    if (_e.type === "eof")
      throw Eu();
    we = "beforePropertyValue";
  },
  beforePropertyValue() {
    if (_e.type === "eof")
      throw Eu();
    Nt();
  },
  beforeArrayValue() {
    if (_e.type === "eof")
      throw Eu();
    if (_e.type === "punctuator" && _e.value === "]") {
      tt();
      return;
    }
    Nt();
  },
  afterPropertyValue() {
    if (_e.type === "eof")
      throw Eu();
    switch (_e.value) {
      case ",":
        we = "beforePropertyName";
        return;
      case "}":
        tt();
    }
  },
  afterArrayValue() {
    if (_e.type === "eof")
      throw Eu();
    switch (_e.value) {
      case ",":
        we = "beforeArrayValue";
        return;
      case "]":
        tt();
    }
  },
  end() {
  }
};
function Nt() {
  let e;
  switch (_e.type) {
    case "punctuator":
      switch (_e.value) {
        case "{":
          e = {};
          break;
        case "[":
          e = [];
          break;
      }
      break;
    case "null":
    case "boolean":
    case "numeric":
    case "string":
      e = _e.value;
      break;
  }
  if (Lu === void 0)
    Lu = e;
  else {
    const u = eu[eu.length - 1];
    Array.isArray(u) ? u.push(e) : Object.defineProperty(u, fn, {
      value: e,
      writable: !0,
      enumerable: !0,
      configurable: !0
    });
  }
  if (e !== null && typeof e == "object")
    eu.push(e), Array.isArray(e) ? we = "beforeArrayValue" : we = "beforePropertyName";
  else {
    const u = eu[eu.length - 1];
    u == null ? we = "end" : Array.isArray(u) ? we = "afterArrayValue" : we = "afterPropertyValue";
  }
}
function tt() {
  eu.pop();
  const e = eu[eu.length - 1];
  e == null ? we = "end" : Array.isArray(e) ? we = "afterArrayValue" : we = "afterPropertyValue";
}
function be(e) {
  return dn(e === void 0 ? `JSON5: invalid end of input at ${du}:${ze}` : `JSON5: invalid character '${$r(e)}' at ${du}:${ze}`);
}
function Eu() {
  return dn(`JSON5: invalid end of input at ${du}:${ze}`);
}
function zn() {
  return ze -= 5, dn(`JSON5: invalid identifier character at ${du}:${ze}`);
}
function Ai(e) {
  console.warn(`JSON5: '${$r(e)}' in strings is not valid ECMAScript; consider escaping`);
}
function $r(e) {
  const u = {
    "'": "\\'",
    '"': '\\"',
    "\\": "\\\\",
    "\b": "\\b",
    "\f": "\\f",
    "\n": "\\n",
    "\r": "\\r",
    "	": "\\t",
    "\v": "\\v",
    "\0": "\\0",
    "\u2028": "\\u2028",
    "\u2029": "\\u2029"
  };
  if (u[e])
    return u[e];
  if (e < " ") {
    const n = e.charCodeAt(0).toString(16);
    return "\\x" + ("00" + n).substring(n.length);
  }
  return e;
}
function dn(e) {
  const u = new SyntaxError(e);
  return u.lineNumber = du, u.columnNumber = ze, u;
}
var _i = function(e, u, n) {
  const r = [];
  let i = "", t, o, a = "", s;
  if (u != null && typeof u == "object" && !Array.isArray(u) && (n = u.space, s = u.quote, u = u.replacer), typeof u == "function")
    o = u;
  else if (Array.isArray(u)) {
    t = [];
    for (const d of u) {
      let x;
      typeof d == "string" ? x = d : (typeof d == "number" || d instanceof String || d instanceof Number) && (x = String(d)), x !== void 0 && t.indexOf(x) < 0 && t.push(x);
    }
  }
  return n instanceof Number ? n = Number(n) : n instanceof String && (n = String(n)), typeof n == "number" ? n > 0 && (n = Math.min(10, Math.floor(n)), a = "          ".substr(0, n)) : typeof n == "string" && (a = n.substr(0, 10)), l("", { "": e });
  function l(d, x) {
    let g = x[d];
    switch (g != null && (typeof g.toJSON5 == "function" ? g = g.toJSON5(d) : typeof g.toJSON == "function" && (g = g.toJSON(d))), o && (g = o.call(x, d, g)), g instanceof Number ? g = Number(g) : g instanceof String ? g = String(g) : g instanceof Boolean && (g = g.valueOf()), g) {
      case null:
        return "null";
      case !0:
        return "true";
      case !1:
        return "false";
    }
    if (typeof g == "string")
      return f(g);
    if (typeof g == "number")
      return String(g);
    if (typeof g == "object")
      return Array.isArray(g) ? p(g) : c(g);
  }
  function f(d) {
    const x = {
      "'": 0.1,
      '"': 0.2
    }, g = {
      "'": "\\'",
      '"': '\\"',
      "\\": "\\\\",
      "\b": "\\b",
      "\f": "\\f",
      "\n": "\\n",
      "\r": "\\r",
      "	": "\\t",
      "\v": "\\v",
      "\0": "\\0",
      "\u2028": "\\u2028",
      "\u2029": "\\u2029"
    };
    let _ = "";
    for (let D = 0; D < d.length; D++) {
      const m = d[D];
      switch (m) {
        case "'":
        case '"':
          x[m]++, _ += m;
          continue;
        case "\0":
          if (De.isDigit(d[D + 1])) {
            _ += "\\x00";
            continue;
          }
      }
      if (g[m]) {
        _ += g[m];
        continue;
      }
      if (m < " ") {
        let A = m.charCodeAt(0).toString(16);
        _ += "\\x" + ("00" + A).substring(A.length);
        continue;
      }
      _ += m;
    }
    const E = s || Object.keys(x).reduce((D, m) => x[D] < x[m] ? D : m);
    return _ = _.replace(new RegExp(E, "g"), g[E]), E + _ + E;
  }
  function c(d) {
    if (r.indexOf(d) >= 0)
      throw TypeError("Converting circular structure to JSON5");
    r.push(d);
    let x = i;
    i = i + a;
    let g = t || Object.keys(d), _ = [];
    for (const D of g) {
      const m = l(D, d);
      if (m !== void 0) {
        let A = h(D) + ":";
        a !== "" && (A += " "), A += m, _.push(A);
      }
    }
    let E;
    if (_.length === 0)
      E = "{}";
    else {
      let D;
      if (a === "")
        D = _.join(","), E = "{" + D + "}";
      else {
        let m = `,
` + i;
        D = _.join(m), E = `{
` + i + D + `,
` + x + "}";
      }
    }
    return r.pop(), i = x, E;
  }
  function h(d) {
    if (d.length === 0)
      return f(d);
    const x = String.fromCodePoint(d.codePointAt(0));
    if (!De.isIdStartChar(x))
      return f(d);
    for (let g = x.length; g < d.length; g++)
      if (!De.isIdContinueChar(String.fromCodePoint(d.codePointAt(g))))
        return f(d);
    return d;
  }
  function p(d) {
    if (r.indexOf(d) >= 0)
      throw TypeError("Converting circular structure to JSON5");
    r.push(d);
    let x = i;
    i = i + a;
    let g = [];
    for (let E = 0; E < d.length; E++) {
      const D = l(String(E), d);
      g.push(D !== void 0 ? D : "null");
    }
    let _;
    if (g.length === 0)
      _ = "[]";
    else if (a === "")
      _ = "[" + g.join(",") + "]";
    else {
      let E = `,
` + i, D = g.join(E);
      _ = `[
` + i + D + `,
` + x + "]";
    }
    return r.pop(), i = x, _;
  }
};
const Fi = {
  parse: mi,
  stringify: _i
};
var yi = Fi;
const mt = (e) => e.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#39;"), ki = /^\s*\d+(?:\s*-\s*\d+)?(?:\s*,\s*\d+(?:\s*-\s*\d+)?)*\s*$/, vi = (e) => {
  const u = e.search(/\s/);
  let n = "", r = !1;
  for (let i = 0; i < e.length; i += 1) {
    const t = e[i];
    if (n) {
      if (r) {
        r = !1;
        continue;
      }
      if (t === "\\") {
        r = !0;
        continue;
      }
      t === n && (n = "");
      continue;
    }
    if (t === '"' || t === "'" || t === "`") {
      n = t;
      continue;
    }
    if (t !== "{")
      continue;
    const o = e.indexOf("}", i + 1);
    if (o === -1)
      break;
    const a = e.slice(i + 1, o);
    if (!ki.test(a))
      continue;
    const s = e[i - 1] || "", l = e[o + 1] || "", f = !l || /\s/.test(l), c = (u === -1 || i < u) && (!s || !/[\s="'`]/.test(s)) && f, h = (!s || /\s/.test(s)) && f;
    if (c || h)
      return {
        endIndex: o + 1,
        startIndex: i,
        value: a
      };
  }
  return null;
}, at = (e, u = "") => {
  const n = `${e}${u ? ` ${u}` : ""}`.trim(), r = vi(n), i = r ? `${n.slice(0, r.startIndex)} ${n.slice(r.endIndex)}`.trim() : n, t = i.search(/\s/), o = t === -1 ? i : i.slice(0, t), a = t === -1 ? "" : i.slice(t).trim(), s = r ? r.value.split(",").reduce((l, f) => {
    const [c, h] = f.split("-").map((d) => Number.parseInt(d.trim(), 10)), p = h ?? c;
    return c > 0 && p >= c && l.push({ start: c, end: p }), l;
  }, []) : [];
  return {
    attrs: a,
    language: o,
    lineHighlightRanges: s
  };
}, zu = (e, u, n) => `<${e.tag}${n.renderAttrs(e)}>${u}</${e.tag}>`, Pn = (e, u) => {
  const n = e.attrs ? e.attrs.slice() : [];
  return u.forEach((r) => {
    const i = e.attrIndex(r[0]);
    i < 0 ? n.push(r) : (n[i] = n[i].slice(), n[i][1] += ` ${r[1]}`);
  }), n;
}, Be = (e) => e !== null && typeof e == "object" && !Array.isArray(e), ku = (e, u) => Array.isArray(e) ? e.map((n) => Be(n) ? u(n) : n) : Be(e) ? u(e) : e, nn = (e) => {
  if (typeof e != "string") return "";
  try {
    const u = new URL(e, "https://md-editor.invalid/");
    return ["http:", "https:", "mailto:", "tel:"].includes(u.protocol) ? e : "";
  } catch {
    return "";
  }
}, Hn = (e) => [0, 1, 2].map((u) => {
  const n = e[u];
  return n == null ? n : mt(String(n));
}), jn = (e) => {
  const u = { ...e };
  e.lang != null && (u.lang = Hn(e.lang));
  const n = e.feature?.dataView;
  if (Be(n)) {
    const r = { ...n };
    n.title != null && (r.title = mt(String(n.title))), n.lang != null && (r.lang = Hn(n.lang)), u.feature = { ...e.feature, dataView: r };
  }
  return u;
}, st = (e) => {
  const u = { ...e };
  return Object.hasOwn(e, "link") && (u.link = nn(e.link)), e.children && (u.children = ku(e.children, st)), u;
}, qn = (e) => {
  const u = e.id;
  return typeof u == "string" || typeof u == "number" ? String(u) : void 0;
}, wi = (e) => {
  if (!Be(e))
    throw new TypeError("ECharts option must be an object.");
  const u = [Be(e.baseOption) ? e.baseOption : e];
  Array.isArray(e.options) && u.push(...e.options.filter(Be)), Array.isArray(e.media) && e.media.forEach((o) => {
    Be(o) && Be(o.option) && u.push(o.option);
  });
  const n = /* @__PURE__ */ new Map();
  let r = !1;
  for (const o of u) {
    const a = Array.isArray(o.series) ? o.series : [o.series];
    for (const s of a) {
      if (!Be(s) || !s.type) continue;
      const l = s.type === "treemap" || s.type === "sunburst";
      r ||= l;
      const f = qn(s);
      f !== void 0 && n.set(f, n.get(f) === !0 || l);
    }
  }
  const i = (o) => {
    const a = jn(o);
    for (const s of ["link", "sublink"])
      Object.hasOwn(o, s) && (a[s] = nn(o[s]));
    if (o.tooltip != null) {
      const s = (l) => ({
        ...Be(l) ? l : {},
        ...l === !1 ? { show: !1 } : {},
        renderMode: "richText"
      });
      a.tooltip = Array.isArray(o.tooltip) ? o.tooltip.map(s) : s(o.tooltip);
    }
    return o.title && (a.title = ku(o.title, (s) => {
      const l = { ...s };
      for (const f of ["link", "sublink"])
        Object.hasOwn(s, f) && (l[f] = nn(s[f]));
      return l;
    })), o.toolbox && (a.toolbox = ku(o.toolbox, (s) => {
      const l = jn(s);
      return s.title != null && (l.title = mt(String(s.title))), l;
    })), o.series && (a.series = ku(o.series, (s) => {
      const l = qn(s), f = l === void 0 ? void 0 : n.get(l);
      if (!(s.type ? s.type === "treemap" || s.type === "sunburst" : f ?? r)) return s;
      const c = st(s);
      return s.data && (c.data = ku(s.data, st)), s.levels && (c.levels = ku(s.levels, st)), c;
    })), a;
  }, t = i(e);
  return Be(e.baseOption) && (t.baseOption = i(e.baseOption)), Array.isArray(e.options) && (t.options = e.options.map(
    (o) => Be(o) ? i(o) : o
  )), Array.isArray(e.media) && (t.media = e.media.map(
    (o) => Be(o) && Be(o.option) ? { ...o, option: i(o.option) } : o
  )), t;
}, T = "md-editor", le = "https://unpkg.com", Bi = `${le}/@highlightjs/cdn-assets@11.12.0/highlight.min.js`, Un = {
  main: `${le}/prettier@3.9.6/standalone.js`,
  markdown: `${le}/prettier@3.9.6/plugins/markdown.js`
}, Si = {
  css: `${le}/cropperjs@1.6.3/dist/cropper.min.css`,
  js: `${le}/cropperjs@1.6.3/dist/cropper.min.js`
}, Ti = `${le}/screenfull@5.2.0/dist/screenfull.js`, Ii = `${le}/mermaid@11.17.2/dist/mermaid.min.js`, Mi = {
  js: `${le}/katex@0.18.5/dist/katex.min.js`,
  css: `${le}/katex@0.18.5/dist/katex.min.css`
}, rn = {
  a11y: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/a11y-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/a11y-dark.min.css`
  },
  atom: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/atom-one-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/atom-one-dark.min.css`
  },
  github: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/github.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/github-dark.min.css`
  },
  gradient: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/gradient-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/gradient-dark.min.css`
  },
  kimbie: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/kimbie-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/kimbie-dark.min.css`
  },
  paraiso: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/paraiso-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/paraiso-dark.min.css`
  },
  qtcreator: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/qtcreator-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/qtcreator-dark.min.css`
  },
  stackoverflow: {
    light: `${le}/@highlightjs/cdn-assets@11.12.0/styles/stackoverflow-light.min.css`,
    dark: `${le}/@highlightjs/cdn-assets@11.12.0/styles/stackoverflow-dark.min.css`
  }
}, Oi = `${le}/echarts@6.1.0/dist/echarts.min.js`, Zn = {
  "zh-CN": {
    toolbarTips: {
      bold: "加粗",
      underline: "下划线",
      italic: "斜体",
      strikeThrough: "删除线",
      title: "标题",
      sub: "下标",
      sup: "上标",
      quote: "引用",
      unorderedList: "无序列表",
      orderedList: "有序列表",
      task: "任务列表",
      codeRow: "行内代码",
      code: "块级代码",
      link: "链接",
      image: "图片",
      table: "表格",
      mermaid: "mermaid图",
      katex: "katex公式",
      revoke: "后退",
      next: "前进",
      save: "保存",
      prettier: "美化",
      pageFullscreen: "浏览器全屏",
      fullscreen: "屏幕全屏",
      preview: "预览",
      previewOnly: "仅预览",
      htmlPreview: "html代码预览",
      catalog: "目录",
      github: "源码地址"
    },
    titleItem: {
      h1: "一级标题",
      h2: "二级标题",
      h3: "三级标题",
      h4: "四级标题",
      h5: "五级标题",
      h6: "六级标题"
    },
    imgTitleItem: {
      link: "添加链接",
      upload: "上传图片",
      clip2upload: "裁剪上传"
    },
    linkModalTips: {
      linkTitle: "添加链接",
      imageTitle: "添加图片",
      descLabel: "链接描述：",
      descLabelPlaceHolder: "请输入描述...",
      urlLabel: "链接地址：",
      urlLabelPlaceHolder: "请输入链接...",
      buttonOK: "确定"
    },
    clipModalTips: {
      title: "裁剪图片上传",
      buttonUpload: "上传"
    },
    copyCode: {
      text: "复制代码",
      successTips: "已复制！",
      failTips: "复制失败！"
    },
    mermaid: {
      flow: "流程图",
      sequence: "时序图",
      gantt: "甘特图",
      class: "类图",
      state: "状态图",
      pie: "饼图",
      relationship: "关系图",
      journey: "旅程图"
    },
    katex: {
      inline: "行内公式",
      block: "块级公式"
    },
    footer: {
      markdownTotal: "字数",
      scrollAuto: "同步滚动"
    }
  },
  "en-US": {
    toolbarTips: {
      bold: "bold",
      underline: "underline",
      italic: "italic",
      strikeThrough: "strikeThrough",
      title: "title",
      sub: "subscript",
      sup: "superscript",
      quote: "quote",
      unorderedList: "unordered list",
      orderedList: "ordered list",
      task: "task list",
      codeRow: "inline code",
      code: "block-level code",
      link: "link",
      image: "image",
      table: "table",
      mermaid: "mermaid",
      katex: "formula",
      revoke: "revoke",
      next: "undo revoke",
      save: "save",
      prettier: "prettier",
      pageFullscreen: "fullscreen in page",
      fullscreen: "fullscreen",
      preview: "preview",
      previewOnly: "preview only",
      htmlPreview: "html preview",
      catalog: "catalog",
      github: "source code"
    },
    titleItem: {
      h1: "Lv1 Heading",
      h2: "Lv2 Heading",
      h3: "Lv3 Heading",
      h4: "Lv4 Heading",
      h5: "Lv5 Heading",
      h6: "Lv6 Heading"
    },
    imgTitleItem: {
      link: "Add Image Link",
      upload: "Upload Images",
      clip2upload: "Crop And Upload"
    },
    linkModalTips: {
      linkTitle: "Add Link",
      imageTitle: "Add Image",
      descLabel: "Desc:",
      descLabelPlaceHolder: "Enter a description...",
      urlLabel: "Link:",
      urlLabelPlaceHolder: "Enter a link...",
      buttonOK: "OK"
    },
    clipModalTips: {
      title: "Crop Image",
      buttonUpload: "Upload"
    },
    copyCode: {
      text: "Copy",
      successTips: "Copied!",
      failTips: "Copy failed!"
    },
    mermaid: {
      flow: "flow",
      sequence: "sequence",
      gantt: "gantt",
      class: "class",
      state: "state",
      pie: "pie",
      relationship: "relationship",
      journey: "journey"
    },
    katex: {
      inline: "inline",
      block: "block"
    },
    footer: {
      markdownTotal: "Character Count",
      scrollAuto: "Scroll Auto"
    }
  }
}, Oe = {
  editorExtensions: {
    highlight: {
      js: Bi,
      css: rn
    },
    prettier: {
      standaloneJs: Un.main,
      parserMarkdownJs: Un.markdown
    },
    cropper: {
      ...Si
    },
    screenfull: {
      js: Ti
    },
    mermaid: {
      js: Ii,
      enableZoom: !0
    },
    katex: {
      ...Mi
    },
    echarts: {
      js: Oi,
      parseOption: (e) => {
        let u;
        try {
          u = yi.parse(e);
        } catch (n) {
          const r = n instanceof Error ? n.message : String(n);
          throw new SyntaxError(`Invalid ECharts option: ${r}`, {
            cause: n
          });
        }
        if (!u || typeof u != "object" || Array.isArray(u))
          throw new TypeError("ECharts option must be an object.");
        return u;
      },
      sanitizeOption: wi
    }
  },
  editorExtensionsAttrs: {},
  editorConfig: {
    languageUserDefined: {},
    mermaidTemplate: {},
    renderDelay: 500,
    zIndex: 2e4
  },
  codeMirrorExtensions: (e) => e,
  markdownItConfig: () => {
  },
  markdownItPlugins: (e) => e,
  mermaidConfig: (e) => e,
  katexConfig: (e) => e,
  echartsConfig: (e) => e
}, Ni = (e) => gt(Oe, e, {
  excludeKeys(u) {
    return /[iI]{1}nstance/.test(u);
  }
}), Ri = ({
  instance: e,
  ctx: u,
  props: n = {}
}, r = "default") => {
  const i = e?.$slots[r] || u?.slots[r];
  return (i ? i(e) : "") || n[r];
}, $i = "buildFinished", on = "errorCatcher", Rt = "catalogChanged", Li = "pushCatalog", Lr = "rerender", zi = "taskStateChanged";
class Pi {
  // 事件池
  pools = {};
  // 移除事件监听
  remove(u, n, r) {
    const i = this.pools[u] && this.pools[u][n];
    i && (this.pools[u][n] = i.filter((t) => t !== r));
  }
  // 清空全部事件，由于单一实例，多次注册会被共享内容
  clear(u) {
    this.pools[u] = {};
  }
  // 注册事件监听
  on(u, n) {
    return this.pools[u] || (this.pools[u] = {}), this.pools[u][n.name] || (this.pools[u][n.name] = []), this.pools[u][n.name].push(n.callback), this.pools[u][n.name].includes(n.callback);
  }
  // 触发事件
  emit(u, n, ...r) {
    this.pools[u] || (this.pools[u] = {});
    const i = this.pools[u][n];
    i && i.forEach((t) => {
      try {
        t(...r);
      } catch (o) {
        console.error(`${n} monitor event exception！`, o);
      }
    });
  }
}
const Me = new Pi(), Hi = /^<\s*(\/?)\s*([a-zA-Z][a-zA-Z0-9:_-]*)(?:\s[\s\S]*?)?\s*(\/?)>$/, ji = /* @__PURE__ */ new Set([
  "area",
  "base",
  "br",
  "col",
  "embed",
  "hr",
  "img",
  "input",
  "link",
  "meta",
  "param",
  "source",
  "track",
  "wbr"
]), qi = (e) => {
  const u = e.replace(/\r\n?/g, `
`), n = u.endsWith(`
`) ? u.slice(0, -1) : u;
  return n ? n.split(`
`) : [""];
}, zr = (e, u) => {
  if (e.startsWith("<!--", u)) {
    const r = e.indexOf("-->", u + 4);
    return r === -1 ? -1 : r + 2;
  }
  if (e.startsWith("<![CDATA[", u)) {
    const r = e.indexOf("]]>", u + 9);
    return r === -1 ? -1 : r + 2;
  }
  let n = "";
  for (let r = u + 1; r < e.length; r += 1) {
    const i = e[r];
    if (n) {
      i === n && (n = "");
      continue;
    }
    if (i === '"' || i === "'")
      n = i;
    else if (i === ">")
      return r;
  }
  return -1;
}, Pr = (e) => {
  const u = Hi.exec(e);
  if (!u)
    return null;
  const n = u[2].toLowerCase();
  return {
    isClosing: u[1] === "/",
    isSelfClosing: u[3] === "/" || ji.has(n),
    name: n,
    source: e
  };
}, an = (e, u) => {
  const n = e.indexOf("<", u);
  if (n === -1)
    return null;
  const r = zr(e, n);
  return r === -1 ? null : {
    endIndex: r,
    parsedTag: Pr(e.slice(n, r + 1)),
    startIndex: n
  };
}, Ui = (e) => e.map((u) => u.openingTag).join(""), Zi = (e) => [...e].reverse().map((u) => `</${u.name}>`).join(""), Gi = (e) => {
  let u = 0;
  for (; u < e.length; ) {
    const n = an(e, u), r = n?.startIndex ?? e.length;
    if (e.slice(u, r).trim())
      return !0;
    if (!n)
      return !1;
    u = n.endIndex + 1;
  }
  return !1;
}, Vi = (e) => {
  const u = an(e, 0);
  if (!u || u.startIndex !== 0 || u.parsedTag?.name !== "pre" || u.parsedTag.isClosing || u.parsedTag.isSelfClosing)
    return null;
  let n = 0, r = -1, i = u.endIndex + 1;
  for (; i < e.length; ) {
    const t = an(e, i);
    if (!t)
      break;
    const o = t.parsedTag;
    if (!o) {
      i = t.endIndex + 1;
      continue;
    }
    if (o.name === "pre" && o.isClosing && n === 0)
      break;
    if (o.name === "code")
      if (o.isClosing) {
        if (n > 0 && (n -= 1, n === 0))
          return {
            endIndex: t.startIndex,
            startIndex: r
          };
      } else o.isSelfClosing || (n === 0 && (r = t.endIndex + 1), n += 1);
    i = t.endIndex + 1;
  }
  return null;
}, Wi = (e) => {
  const u = e.replace(/\r\n?/g, `
`), n = [""], r = [];
  let i = 0, t = 0;
  for (; t < u.length; ) {
    const o = u[t];
    if (o === "<") {
      const a = zr(u, t);
      if (a === -1) {
        n[i] += u.slice(t);
        break;
      }
      const s = u.slice(t, a + 1), l = Pr(s);
      if (n[i] += s, l)
        if (l.isClosing) {
          for (let f = r.length - 1; f >= 0; f -= 1)
            if (r[f].name === l.name) {
              r.splice(f, 1);
              break;
            }
        } else l.isSelfClosing || r.push({
          name: l.name,
          openingTag: l.source
        });
      t = a + 1;
      continue;
    }
    if (o === `
`) {
      n[i] += Zi(r), n.push(Ui(r)), i += 1, t += 1;
      continue;
    }
    n[i] += o, t += 1;
  }
  return n;
}, Hr = (e, u, n = {}) => {
  const { lineHighlightRanges: r = [], showLineNumber: i = !1 } = n, t = qi(u), o = Wi(e);
  for (; o.length > t.length && !Gi(o[o.length - 1]); )
    o.pop();
  const a = o.length === t.length, s = t.map((f, c) => {
    const h = a ? o[c] : mt(f), p = c + 1, d = r.some(
      ({ start: _, end: E }) => p >= _ && p <= E
    ), x = [
      `${T}-code-line`,
      i ? `${T}-code-line-numbered` : "",
      d ? `${T}-code-line-highlight` : ""
    ].filter(Boolean).join(" "), g = i ? `<span rn-wrapper aria-hidden="true" data-line-number="${p}"></span>` : "";
    return [
      `<span class="${x}">`,
      g,
      `<span class="${T}-code-line-content">${h}</span>`,
      "</span>"
    ].join("");
  }), l = i ? ` style="--md-code-line-number-width: ${Math.max(2, String(t.length).length) + 0.5}ch;"` : "";
  return `<span class="${T}-code-block-lines"${l}>${s.join(`
`)}</span>`;
}, Ki = (e, u, n = {}) => {
  const r = Vi(e);
  if (!r)
    return null;
  const i = Hr(
    e.slice(r.startIndex, r.endIndex),
    u,
    n
  );
  return `${e.slice(0, r.startIndex)}${i}${e.slice(r.endIndex)}`;
}, Xi = (e, u, n = {}) => {
  const { lineHighlightRanges: r = [], showLineNumber: i = !1 } = n;
  return !i && !r.length ? { html: e, shouldReturnDirectly: !0 } : e.startsWith("<pre") ? {
    html: Ki(e, u, n) ?? e,
    shouldReturnDirectly: !0
  } : { html: e, shouldReturnDirectly: !1 };
};
async function jr(e) {
  if (typeof e == "string") {
    if (window.isSecureContext && navigator.clipboard)
      return await navigator.clipboard.writeText(e);
    {
      const u = document.createElement("textarea");
      let n = !1;
      if (u.value = e, u.style.position = "fixed", u.style.opacity = 0, u.style.zIndex = "-10000", u.style.top = "-10000", document.body.appendChild(u), u.select(), n = document.execCommand("copy"), document.body.removeChild(u), n)
        return;
      throw new Error('Failed to copy content via "execCommand"!');
    }
  }
}
const Ji = {
  copy: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy ${T}-icon"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>`,
  "collapse-tips": `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-chevron-left ${T}-icon"><circle cx="12" cy="12" r="10"/><path d="m14 16-4-4 4-4"/></svg>`,
  pin: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pin ${T}-icon"><path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/></svg>`,
  "pin-off": `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pin-off ${T}-icon"><path d="M12 17v5"/><path d="M15 9.34V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H7.89"/><path d="m2 2 20 20"/><path d="M9 9v1.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h11"/></svg>`,
  check: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check ${T}-icon"><path d="M20 6 9 17l-5-5"/></svg>`
}, Ue = (e, u) => typeof u[e] == "string" ? u[e] : Ji[e], pu = (e, u, n = "") => {
  const r = document.getElementById(u.id);
  if (r)
    n !== "" && (Reflect.get(window, n) ? u.onload?.call(r, new Event("load")) : u.onload && r.addEventListener("load", u.onload));
  else {
    const i = { ...u };
    i.onload = null;
    const t = Yi(e, i);
    u.onload && t.addEventListener("load", u.onload), document.head.appendChild(t);
  }
}, Qi = (e, u) => {
  document.getElementById(u.id)?.remove(), pu(e, u);
}, Yi = (e, u) => {
  const n = document.createElement(e);
  return Object.keys(u).forEach((r) => {
    u[r] !== void 0 && (n[r] = u[r]);
  }), n;
}, eo = (e, u) => {
  const n = /* @__PURE__ */ new Map();
  return e?.forEach((r) => {
    let i = r.querySelector(`.${T}-mermaid-action`);
    i ? i.querySelector(`.${T}-mermaid-copy`) || i.insertAdjacentHTML(
      "beforeend",
      `<span class="${T}-mermaid-copy">${Ue("copy", u.customIcon)}</span>`
    ) : (r.insertAdjacentHTML(
      "beforeend",
      `<div class="${T}-mermaid-action"><span class="${T}-mermaid-copy">${Ue("copy", u.customIcon)}</span></div>`
    ), i = r.querySelector(`.${T}-mermaid-action`));
    const t = i.querySelector(`.${T}-mermaid-copy`);
    let o = -1;
    const a = () => {
      clearTimeout(o), jr(r.dataset.content || "").then(() => {
        t.innerHTML = Ue("check", u.customIcon);
      }).catch(() => {
        t.innerHTML = Ue("copy", u.customIcon);
      }).finally(() => {
        o = window.setTimeout(() => {
          t.innerHTML = Ue("copy", u.customIcon);
        }, 1500);
      });
    };
    t.addEventListener("click", a), n.set(r, {
      removeClick: () => {
        t.removeEventListener("click", a);
      }
    });
  }), () => {
    n.forEach(({ removeClick: r }) => {
      r?.();
    }), n.clear();
  };
}, uo = /* @__PURE__ */ (() => {
  const e = (u) => {
    if (!u)
      return () => {
      };
    const n = u.firstChild;
    let r = 1, i = 0, t = 0, o = !1, a, s, l, f = 1;
    const c = () => {
      n.style.transform = `translate(${i}px, ${t}px) scale(${r})`;
    }, h = (m) => {
      m.touches.length === 1 ? (o = !0, a = m.touches[0].clientX - i, s = m.touches[0].clientY - t) : m.touches.length === 2 && (l = Math.hypot(
        m.touches[0].clientX - m.touches[1].clientX,
        m.touches[0].clientY - m.touches[1].clientY
      ), f = r);
    }, p = (m) => {
      if (m.preventDefault(), o && m.touches.length === 1)
        i = m.touches[0].clientX - a, t = m.touches[0].clientY - s, c();
      else if (m.touches.length === 2) {
        const A = Math.hypot(
          m.touches[0].clientX - m.touches[1].clientX,
          m.touches[0].clientY - m.touches[1].clientY
        ) / l, k = r;
        r = f * (1 + (A - 1));
        const B = (m.touches[0].clientX + m.touches[1].clientX) / 2, C = (m.touches[0].clientY + m.touches[1].clientY) / 2, U = n.getBoundingClientRect(), Z = (B - U.left) / k, H = (C - U.top) / k;
        i -= Z * (r - k), t -= H * (r - k), c();
      }
    }, d = () => {
      o = !1;
    }, x = (m) => {
      m.preventDefault();
      const A = 0.02, k = r;
      m.deltaY < 0 ? r += A : r = Math.max(0.1, r - A);
      const B = n.getBoundingClientRect(), C = m.clientX - B.left, U = m.clientY - B.top;
      i -= C / k * (r - k), t -= U / k * (r - k), c();
    }, g = (m) => {
      o = !0, a = m.clientX - i, s = m.clientY - t;
    }, _ = (m) => {
      o && (i = m.clientX - a, t = m.clientY - s, c());
    }, E = () => {
      o = !1;
    }, D = () => {
      o = !1;
    };
    return u.addEventListener("touchstart", h, { passive: !1 }), u.addEventListener("touchmove", p, { passive: !1 }), u.addEventListener("touchend", d), u.addEventListener("wheel", x, { passive: !1 }), u.addEventListener("mousedown", g), u.addEventListener("mousemove", _), u.addEventListener("mouseup", E), u.addEventListener("mouseleave", D), () => {
      u.removeEventListener("touchstart", h), u.removeEventListener("touchmove", p), u.removeEventListener("touchend", d), u.removeEventListener("wheel", x), u.removeEventListener("mousedown", g), u.removeEventListener("mousemove", _), u.removeEventListener("mouseup", E), u.removeEventListener("mouseleave", D);
    };
  };
  return (u, n) => {
    const r = /* @__PURE__ */ new Map();
    return u?.forEach((i) => {
      let t = i.querySelector(`.${T}-mermaid-action`);
      t ? t.querySelector(`.${T}-mermaid-zoom`) || t.insertAdjacentHTML(
        "beforeend",
        `<span class="${T}-mermaid-zoom">${Ue("pin-off", n.customIcon)}</span>`
      ) : (i.insertAdjacentHTML(
        "beforeend",
        `<div class="${T}-mermaid-action"><span class="${T}-mermaid-zoom">${Ue("pin-off", n.customIcon)}</span></div>`
      ), t = i.querySelector(`.${T}-mermaid-action`));
      const o = t.querySelector(`.${T}-mermaid-zoom`), a = () => {
        const s = r.get(i);
        if (s?.removeEvent)
          s.removeEvent(), i.removeAttribute("data-grab"), r.set(i, { removeClick: s.removeClick }), o.innerHTML = Ue("pin-off", n.customIcon);
        else {
          const l = e(i);
          i.setAttribute("data-grab", ""), r.set(i, { removeEvent: l, removeClick: s?.removeClick }), o.innerHTML = Ue("pin", n.customIcon);
        }
      };
      o.addEventListener("click", a), r.set(i, {
        removeClick: () => o.removeEventListener("click", a)
      });
    }), () => {
      r.forEach(({ removeEvent: i, removeClick: t }) => {
        i?.(), t?.();
      }), r.clear();
    };
  };
})(), Gn = {};
function to(e) {
  let u = Gn[e];
  if (u)
    return u;
  u = Gn[e] = [];
  for (let n = 0; n < 128; n++) {
    const r = String.fromCharCode(n);
    u.push(r);
  }
  for (let n = 0; n < e.length; n++) {
    const r = e.charCodeAt(n);
    u[r] = "%" + ("0" + r.toString(16).toUpperCase()).slice(-2);
  }
  return u;
}
function Bu(e, u) {
  typeof u != "string" && (u = Bu.defaultChars);
  const n = to(u);
  return e.replace(/(%[a-f0-9]{2})+/gi, function(r) {
    let i = "";
    for (let t = 0, o = r.length; t < o; t += 3) {
      const a = parseInt(r.slice(t + 1, t + 3), 16);
      if (a < 128) {
        i += n[a];
        continue;
      }
      if ((a & 224) === 192 && t + 3 < o) {
        const s = parseInt(r.slice(t + 4, t + 6), 16);
        if ((s & 192) === 128) {
          const l = a << 6 & 1984 | s & 63;
          l < 128 ? i += "��" : i += String.fromCharCode(l), t += 3;
          continue;
        }
      }
      if ((a & 240) === 224 && t + 6 < o) {
        const s = parseInt(r.slice(t + 4, t + 6), 16), l = parseInt(r.slice(t + 7, t + 9), 16);
        if ((s & 192) === 128 && (l & 192) === 128) {
          const f = a << 12 & 61440 | s << 6 & 4032 | l & 63;
          f < 2048 || f >= 55296 && f <= 57343 ? i += "���" : i += String.fromCharCode(f), t += 6;
          continue;
        }
      }
      if ((a & 248) === 240 && t + 9 < o) {
        const s = parseInt(r.slice(t + 4, t + 6), 16), l = parseInt(r.slice(t + 7, t + 9), 16), f = parseInt(r.slice(t + 10, t + 12), 16);
        if ((s & 192) === 128 && (l & 192) === 128 && (f & 192) === 128) {
          let c = a << 18 & 1835008 | s << 12 & 258048 | l << 6 & 4032 | f & 63;
          c < 65536 || c > 1114111 ? i += "����" : (c -= 65536, i += String.fromCharCode(55296 + (c >> 10), 56320 + (c & 1023))), t += 9;
          continue;
        }
      }
      i += "�";
    }
    return i;
  });
}
Bu.defaultChars = ";/?:@&=+$,#";
Bu.componentChars = "";
const Vn = {};
function no(e) {
  let u = Vn[e];
  if (u)
    return u;
  u = Vn[e] = [];
  for (let n = 0; n < 128; n++) {
    const r = String.fromCharCode(n);
    /^[0-9a-z]$/i.test(r) ? u.push(r) : u.push("%" + ("0" + n.toString(16).toUpperCase()).slice(-2));
  }
  for (let n = 0; n < e.length; n++)
    u[e.charCodeAt(n)] = e[n];
  return u;
}
function Gu(e, u, n) {
  typeof u != "string" && (n = u, u = Gu.defaultChars), typeof n > "u" && (n = !0);
  const r = no(u);
  let i = "";
  for (let t = 0, o = e.length; t < o; t++) {
    const a = e.charCodeAt(t);
    if (n && a === 37 && t + 2 < o && /^[0-9a-f]{2}$/i.test(e.slice(t + 1, t + 3))) {
      i += e.slice(t, t + 3), t += 2;
      continue;
    }
    if (a < 128) {
      i += r[a];
      continue;
    }
    if (a >= 55296 && a <= 57343) {
      if (a >= 55296 && a <= 56319 && t + 1 < o) {
        const s = e.charCodeAt(t + 1);
        if (s >= 56320 && s <= 57343) {
          i += encodeURIComponent(e[t] + e[t + 1]), t++;
          continue;
        }
      }
      i += "%EF%BF%BD";
      continue;
    }
    i += encodeURIComponent(e[t]);
  }
  return i;
}
Gu.defaultChars = ";/?:@&=+$,-_.!~*'()#";
Gu.componentChars = "-_.!~*'()";
function pn(e) {
  let u = "";
  return u += e.protocol || "", u += e.slashes ? "//" : "", u += e.auth ? e.auth + "@" : "", e.hostname && e.hostname.indexOf(":") !== -1 ? u += "[" + e.hostname + "]" : u += e.hostname || "", u += e.port ? ":" + e.port : "", u += e.pathname || "", u += e.search || "", u += e.hash || "", u;
}
function dt() {
  this.protocol = null, this.slashes = null, this.auth = null, this.port = null, this.hostname = null, this.hash = null, this.search = null, this.pathname = null;
}
const ro = /^([a-z0-9.+-]+:)/i, io = /:[0-9]*$/, oo = /^(\/\/?(?!\/)[^\?\s]*)(\?[^\s]*)?$/, ao = ["<", ">", '"', "`", " ", "\r", `
`, "	"], so = ["{", "}", "|", "\\", "^", "`"].concat(ao), co = ["'"].concat(so), Wn = ["%", "/", "?", ";", "#"].concat(co), Kn = ["/", "?", "#"], lo = 255, Xn = /^[+a-z0-9A-Z_-]{0,63}$/, fo = /^([+a-z0-9A-Z_-]{0,63})(.*)$/, Jn = {
  javascript: !0,
  "javascript:": !0
}, Qn = {
  http: !0,
  https: !0,
  ftp: !0,
  gopher: !0,
  file: !0,
  "http:": !0,
  "https:": !0,
  "ftp:": !0,
  "gopher:": !0,
  "file:": !0
};
function hn(e, u) {
  if (e && e instanceof dt) return e;
  const n = new dt();
  return n.parse(e, u), n;
}
dt.prototype.parse = function(e, u) {
  let n, r, i, t = e;
  if (t = t.trim(), !u && e.split("#").length === 1) {
    const l = oo.exec(t);
    if (l)
      return this.pathname = l[1], l[2] && (this.search = l[2]), this;
  }
  let o = ro.exec(t);
  if (o && (o = o[0], n = o.toLowerCase(), this.protocol = o, t = t.substr(o.length)), (u || o || t.match(/^\/\/[^@\/]+@[^@\/]+/)) && (i = t.substr(0, 2) === "//", i && !(o && Jn[o]) && (t = t.substr(2), this.slashes = !0)), !Jn[o] && (i || o && !Qn[o])) {
    let l = -1;
    for (let d = 0; d < Kn.length; d++)
      r = t.indexOf(Kn[d]), r !== -1 && (l === -1 || r < l) && (l = r);
    let f, c;
    l === -1 ? c = t.lastIndexOf("@") : c = t.lastIndexOf("@", l), c !== -1 && (f = t.slice(0, c), t = t.slice(c + 1), this.auth = f), l = -1;
    for (let d = 0; d < Wn.length; d++)
      r = t.indexOf(Wn[d]), r !== -1 && (l === -1 || r < l) && (l = r);
    l === -1 && (l = t.length), t[l - 1] === ":" && l--;
    const h = t.slice(0, l);
    t = t.slice(l), this.parseHost(h), this.hostname = this.hostname || "";
    const p = this.hostname[0] === "[" && this.hostname[this.hostname.length - 1] === "]";
    if (!p) {
      const d = this.hostname.split(/\./);
      for (let x = 0, g = d.length; x < g; x++) {
        const _ = d[x];
        if (_ && !_.match(Xn)) {
          let E = "";
          for (let D = 0, m = _.length; D < m; D++)
            _.charCodeAt(D) > 127 ? E += "x" : E += _[D];
          if (!E.match(Xn)) {
            const D = d.slice(0, x), m = d.slice(x + 1), A = _.match(fo);
            A && (D.push(A[1]), m.unshift(A[2])), m.length && (t = m.join(".") + t), this.hostname = D.join(".");
            break;
          }
        }
      }
    }
    this.hostname.length > lo && (this.hostname = ""), p && (this.hostname = this.hostname.substr(1, this.hostname.length - 2));
  }
  const a = t.indexOf("#");
  a !== -1 && (this.hash = t.substr(a), t = t.slice(0, a));
  const s = t.indexOf("?");
  return s !== -1 && (this.search = t.substr(s), t = t.slice(0, s)), t && (this.pathname = t), Qn[n] && this.hostname && !this.pathname && (this.pathname = ""), this;
};
dt.prototype.parseHost = function(e) {
  let u = io.exec(e);
  u && (u = u[0], u !== ":" && (this.port = u.substr(1)), e = e.substr(0, e.length - u.length)), e && (this.hostname = e);
};
const po = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  decode: Bu,
  encode: Gu,
  format: pn,
  parse: hn
}, Symbol.toStringTag, { value: "Module" })), qr = /[\0-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF]/, Ur = /[\0-\x1F\x7F-\x9F]/, ho = /[\xAD\u0600-\u0605\u061C\u06DD\u070F\u0890\u0891\u08E2\u180E\u200B-\u200F\u202A-\u202E\u2060-\u2064\u2066-\u206F\uFEFF\uFFF9-\uFFFB]|\uD804[\uDCBD\uDCCD]|\uD80D[\uDC30-\uDC3F]|\uD82F[\uDCA0-\uDCA3]|\uD834[\uDD73-\uDD7A]|\uDB40[\uDC01\uDC20-\uDC7F]/, bn = /[!-#%-\*,-\/:;\?@\[-\]_\{\}\xA1\xA7\xAB\xB6\xB7\xBB\xBF\u037E\u0387\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061D-\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u09FD\u0A76\u0AF0\u0C77\u0C84\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F14\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1360-\u1368\u1400\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1B7D\u1B7E\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CC0-\u1CC7\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u2308-\u230B\u2329\u232A\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30-\u2E4F\u2E52-\u2E5D\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA8FC\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uAAF0\uAAF1\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uFF3F\uFF5B\uFF5D\uFF5F-\uFF65]|\uD800[\uDD00-\uDD02\uDF9F\uDFD0]|\uD801\uDD6F|\uD802[\uDC57\uDD1F\uDD3F\uDE50-\uDE58\uDE7F\uDEF0-\uDEF6\uDF39-\uDF3F\uDF99-\uDF9C]|\uD803[\uDEAD\uDF55-\uDF59\uDF86-\uDF89]|\uD804[\uDC47-\uDC4D\uDCBB\uDCBC\uDCBE-\uDCC1\uDD40-\uDD43\uDD74\uDD75\uDDC5-\uDDC8\uDDCD\uDDDB\uDDDD-\uDDDF\uDE38-\uDE3D\uDEA9]|\uD805[\uDC4B-\uDC4F\uDC5A\uDC5B\uDC5D\uDCC6\uDDC1-\uDDD7\uDE41-\uDE43\uDE60-\uDE6C\uDEB9\uDF3C-\uDF3E]|\uD806[\uDC3B\uDD44-\uDD46\uDDE2\uDE3F-\uDE46\uDE9A-\uDE9C\uDE9E-\uDEA2\uDF00-\uDF09]|\uD807[\uDC41-\uDC45\uDC70\uDC71\uDEF7\uDEF8\uDF43-\uDF4F\uDFFF]|\uD809[\uDC70-\uDC74]|\uD80B[\uDFF1\uDFF2]|\uD81A[\uDE6E\uDE6F\uDEF5\uDF37-\uDF3B\uDF44]|\uD81B[\uDE97-\uDE9A\uDFE2]|\uD82F\uDC9F|\uD836[\uDE87-\uDE8B]|\uD83A[\uDD5E\uDD5F]/, Zr = /[\$\+<->\^`\|~\xA2-\xA6\xA8\xA9\xAC\xAE-\xB1\xB4\xB8\xD7\xF7\u02C2-\u02C5\u02D2-\u02DF\u02E5-\u02EB\u02ED\u02EF-\u02FF\u0375\u0384\u0385\u03F6\u0482\u058D-\u058F\u0606-\u0608\u060B\u060E\u060F\u06DE\u06E9\u06FD\u06FE\u07F6\u07FE\u07FF\u0888\u09F2\u09F3\u09FA\u09FB\u0AF1\u0B70\u0BF3-\u0BFA\u0C7F\u0D4F\u0D79\u0E3F\u0F01-\u0F03\u0F13\u0F15-\u0F17\u0F1A-\u0F1F\u0F34\u0F36\u0F38\u0FBE-\u0FC5\u0FC7-\u0FCC\u0FCE\u0FCF\u0FD5-\u0FD8\u109E\u109F\u1390-\u1399\u166D\u17DB\u1940\u19DE-\u19FF\u1B61-\u1B6A\u1B74-\u1B7C\u1FBD\u1FBF-\u1FC1\u1FCD-\u1FCF\u1FDD-\u1FDF\u1FED-\u1FEF\u1FFD\u1FFE\u2044\u2052\u207A-\u207C\u208A-\u208C\u20A0-\u20C0\u2100\u2101\u2103-\u2106\u2108\u2109\u2114\u2116-\u2118\u211E-\u2123\u2125\u2127\u2129\u212E\u213A\u213B\u2140-\u2144\u214A-\u214D\u214F\u218A\u218B\u2190-\u2307\u230C-\u2328\u232B-\u2426\u2440-\u244A\u249C-\u24E9\u2500-\u2767\u2794-\u27C4\u27C7-\u27E5\u27F0-\u2982\u2999-\u29D7\u29DC-\u29FB\u29FE-\u2B73\u2B76-\u2B95\u2B97-\u2BFF\u2CE5-\u2CEA\u2E50\u2E51\u2E80-\u2E99\u2E9B-\u2EF3\u2F00-\u2FD5\u2FF0-\u2FFF\u3004\u3012\u3013\u3020\u3036\u3037\u303E\u303F\u309B\u309C\u3190\u3191\u3196-\u319F\u31C0-\u31E3\u31EF\u3200-\u321E\u322A-\u3247\u3250\u3260-\u327F\u328A-\u32B0\u32C0-\u33FF\u4DC0-\u4DFF\uA490-\uA4C6\uA700-\uA716\uA720\uA721\uA789\uA78A\uA828-\uA82B\uA836-\uA839\uAA77-\uAA79\uAB5B\uAB6A\uAB6B\uFB29\uFBB2-\uFBC2\uFD40-\uFD4F\uFDCF\uFDFC-\uFDFF\uFE62\uFE64-\uFE66\uFE69\uFF04\uFF0B\uFF1C-\uFF1E\uFF3E\uFF40\uFF5C\uFF5E\uFFE0-\uFFE6\uFFE8-\uFFEE\uFFFC\uFFFD]|\uD800[\uDD37-\uDD3F\uDD79-\uDD89\uDD8C-\uDD8E\uDD90-\uDD9C\uDDA0\uDDD0-\uDDFC]|\uD802[\uDC77\uDC78\uDEC8]|\uD805\uDF3F|\uD807[\uDFD5-\uDFF1]|\uD81A[\uDF3C-\uDF3F\uDF45]|\uD82F\uDC9C|\uD833[\uDF50-\uDFC3]|\uD834[\uDC00-\uDCF5\uDD00-\uDD26\uDD29-\uDD64\uDD6A-\uDD6C\uDD83\uDD84\uDD8C-\uDDA9\uDDAE-\uDDEA\uDE00-\uDE41\uDE45\uDF00-\uDF56]|\uD835[\uDEC1\uDEDB\uDEFB\uDF15\uDF35\uDF4F\uDF6F\uDF89\uDFA9\uDFC3]|\uD836[\uDC00-\uDDFF\uDE37-\uDE3A\uDE6D-\uDE74\uDE76-\uDE83\uDE85\uDE86]|\uD838[\uDD4F\uDEFF]|\uD83B[\uDCAC\uDCB0\uDD2E\uDEF0\uDEF1]|\uD83C[\uDC00-\uDC2B\uDC30-\uDC93\uDCA0-\uDCAE\uDCB1-\uDCBF\uDCC1-\uDCCF\uDCD1-\uDCF5\uDD0D-\uDDAD\uDDE6-\uDE02\uDE10-\uDE3B\uDE40-\uDE48\uDE50\uDE51\uDE60-\uDE65\uDF00-\uDFFF]|\uD83D[\uDC00-\uDED7\uDEDC-\uDEEC\uDEF0-\uDEFC\uDF00-\uDF76\uDF7B-\uDFD9\uDFE0-\uDFEB\uDFF0]|\uD83E[\uDC00-\uDC0B\uDC10-\uDC47\uDC50-\uDC59\uDC60-\uDC87\uDC90-\uDCAD\uDCB0\uDCB1\uDD00-\uDE53\uDE60-\uDE6D\uDE70-\uDE7C\uDE80-\uDE88\uDE90-\uDEBD\uDEBF-\uDEC5\uDECE-\uDEDB\uDEE0-\uDEE8\uDEF0-\uDEF8\uDF00-\uDF92\uDF94-\uDFCA]/, Gr = /[ \xA0\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000]/, bo = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  Any: qr,
  Cc: Ur,
  Cf: ho,
  P: bn,
  S: Zr,
  Z: Gr
}, Symbol.toStringTag, { value: "Module" })), go = new Uint16Array(
  // prettier-ignore
  'ᵁ<Õıʊҝջאٵ۞ޢߖࠏ੊ઑඡ๭༉༦჊ረዡᐕᒝᓃᓟᔥ\0\0\0\0\0\0ᕫᛍᦍᰒᷝ὾⁠↰⊍⏀⏻⑂⠤⤒ⴈ⹈⿎〖㊺㘹㞬㣾㨨㩱㫠㬮ࠀEMabcfglmnoprstu\\bfms¦³¹ÈÏlig耻Æ䃆P耻&䀦cute耻Á䃁reve;䄂Āiyx}rc耻Â䃂;䐐r;쀀𝔄rave耻À䃀pha;䎑acr;䄀d;橓Āgp¡on;䄄f;쀀𝔸plyFunction;恡ing耻Å䃅Ācs¾Ãr;쀀𝒜ign;扔ilde耻Ã䃃ml耻Ä䃄ЀaceforsuåûþėĜĢħĪĀcrêòkslash;或Ŷöø;櫧ed;挆y;䐑ƀcrtąċĔause;戵noullis;愬a;䎒r;쀀𝔅pf;쀀𝔹eve;䋘còēmpeq;扎܀HOacdefhilorsuōőŖƀƞƢƵƷƺǜȕɳɸɾcy;䐧PY耻©䂩ƀcpyŝŢźute;䄆Ā;iŧŨ拒talDifferentialD;慅leys;愭ȀaeioƉƎƔƘron;䄌dil耻Ç䃇rc;䄈nint;戰ot;䄊ĀdnƧƭilla;䂸terDot;䂷òſi;䎧rcleȀDMPTǇǋǑǖot;抙inus;抖lus;投imes;抗oĀcsǢǸkwiseContourIntegral;戲eCurlyĀDQȃȏoubleQuote;思uote;怙ȀlnpuȞȨɇɕonĀ;eȥȦ户;橴ƀgitȯȶȺruent;扡nt;戯ourIntegral;戮ĀfrɌɎ;愂oduct;成nterClockwiseContourIntegral;戳oss;樯cr;쀀𝒞pĀ;Cʄʅ拓ap;才րDJSZacefiosʠʬʰʴʸˋ˗ˡ˦̳ҍĀ;oŹʥtrahd;椑cy;䐂cy;䐅cy;䐏ƀgrsʿ˄ˇger;怡r;憡hv;櫤Āayː˕ron;䄎;䐔lĀ;t˝˞戇a;䎔r;쀀𝔇Āaf˫̧Ācm˰̢riticalȀADGT̖̜̀̆cute;䂴oŴ̋̍;䋙bleAcute;䋝rave;䁠ilde;䋜ond;拄ferentialD;慆Ѱ̽\0\0\0͔͂\0Ѕf;쀀𝔻ƀ;DE͈͉͍䂨ot;惜qual;扐blèCDLRUVͣͲ΂ϏϢϸontourIntegraìȹoɴ͹\0\0ͻ»͉nArrow;懓Āeo·ΤftƀARTΐΖΡrrow;懐ightArrow;懔eåˊngĀLRΫτeftĀARγιrrow;柸ightArrow;柺ightArrow;柹ightĀATϘϞrrow;懒ee;抨pɁϩ\0\0ϯrrow;懑ownArrow;懕erticalBar;戥ǹABLRTaВЪаўѿͼrrowƀ;BUНОТ憓ar;椓pArrow;懵reve;䌑eft˒к\0ц\0ѐightVector;楐eeVector;楞ectorĀ;Bљњ憽ar;楖ightǔѧ\0ѱeeVector;楟ectorĀ;BѺѻ懁ar;楗eeĀ;A҆҇护rrow;憧ĀctҒҗr;쀀𝒟rok;䄐ࠀNTacdfglmopqstuxҽӀӄӋӞӢӧӮӵԡԯԶՒ՝ՠեG;䅊H耻Ð䃐cute耻É䃉ƀaiyӒӗӜron;䄚rc耻Ê䃊;䐭ot;䄖r;쀀𝔈rave耻È䃈ement;戈ĀapӺӾcr;䄒tyɓԆ\0\0ԒmallSquare;旻erySmallSquare;斫ĀgpԦԪon;䄘f;쀀𝔼silon;䎕uĀaiԼՉlĀ;TՂՃ橵ilde;扂librium;懌Āci՗՚r;愰m;橳a;䎗ml耻Ë䃋Āipժկsts;戃onentialE;慇ʀcfiosօֈ֍ֲ׌y;䐤r;쀀𝔉lledɓ֗\0\0֣mallSquare;旼erySmallSquare;斪Ͱֺ\0ֿ\0\0ׄf;쀀𝔽All;戀riertrf;愱cò׋؀JTabcdfgorstר׬ׯ׺؀ؒؖ؛؝أ٬ٲcy;䐃耻>䀾mmaĀ;d׷׸䎓;䏜reve;䄞ƀeiy؇،ؐdil;䄢rc;䄜;䐓ot;䄠r;쀀𝔊;拙pf;쀀𝔾eater̀EFGLSTصلَٖٛ٦qualĀ;Lؾؿ扥ess;招ullEqual;执reater;檢ess;扷lantEqual;橾ilde;扳cr;쀀𝒢;扫ЀAacfiosuڅڋږڛڞڪھۊRDcy;䐪Āctڐڔek;䋇;䁞irc;䄤r;愌lbertSpace;愋ǰگ\0ڲf;愍izontalLine;攀Āctۃۅòکrok;䄦mpńېۘownHumðįqual;扏܀EJOacdfgmnostuۺ۾܃܇܎ܚܞܡܨ݄ݸދޏޕcy;䐕lig;䄲cy;䐁cute耻Í䃍Āiyܓܘrc耻Î䃎;䐘ot;䄰r;愑rave耻Ì䃌ƀ;apܠܯܿĀcgܴܷr;䄪inaryI;慈lieóϝǴ݉\0ݢĀ;eݍݎ戬Āgrݓݘral;戫section;拂isibleĀCTݬݲomma;恣imes;恢ƀgptݿރވon;䄮f;쀀𝕀a;䎙cr;愐ilde;䄨ǫޚ\0ޞcy;䐆l耻Ï䃏ʀcfosuެ޷޼߂ߐĀiyޱ޵rc;䄴;䐙r;쀀𝔍pf;쀀𝕁ǣ߇\0ߌr;쀀𝒥rcy;䐈kcy;䐄΀HJacfosߤߨ߽߬߱ࠂࠈcy;䐥cy;䐌ppa;䎚Āey߶߻dil;䄶;䐚r;쀀𝔎pf;쀀𝕂cr;쀀𝒦րJTaceflmostࠥࠩࠬࡐࡣ঳সে্਷ੇcy;䐉耻<䀼ʀcmnpr࠷࠼ࡁࡄࡍute;䄹bda;䎛g;柪lacetrf;愒r;憞ƀaeyࡗ࡜ࡡron;䄽dil;䄻;䐛Āfsࡨ॰tԀACDFRTUVarࡾࢩࢱࣦ࣠ࣼयज़ΐ४Ānrࢃ࢏gleBracket;柨rowƀ;BR࢙࢚࢞憐ar;懤ightArrow;懆eiling;挈oǵࢷ\0ࣃbleBracket;柦nǔࣈ\0࣒eeVector;楡ectorĀ;Bࣛࣜ懃ar;楙loor;挊ightĀAV࣯ࣵrrow;憔ector;楎Āerँगeƀ;AVउऊऐ抣rrow;憤ector;楚iangleƀ;BEतथऩ抲ar;槏qual;抴pƀDTVषूौownVector;楑eeVector;楠ectorĀ;Bॖॗ憿ar;楘ectorĀ;B॥०憼ar;楒ightáΜs̀EFGLSTॾঋকঝঢভqualGreater;拚ullEqual;扦reater;扶ess;檡lantEqual;橽ilde;扲r;쀀𝔏Ā;eঽা拘ftarrow;懚idot;䄿ƀnpw৔ਖਛgȀLRlr৞৷ਂਐeftĀAR০৬rrow;柵ightArrow;柷ightArrow;柶eftĀarγਊightáοightáϊf;쀀𝕃erĀLRਢਬeftArrow;憙ightArrow;憘ƀchtਾੀੂòࡌ;憰rok;䅁;扪Ѐacefiosuਗ਼੝੠੷੼અઋ઎p;椅y;䐜Ādl੥੯iumSpace;恟lintrf;愳r;쀀𝔐nusPlus;戓pf;쀀𝕄cò੶;䎜ҀJacefostuણધભીଔଙඑ඗ඞcy;䐊cute;䅃ƀaey઴હાron;䅇dil;䅅;䐝ƀgswે૰଎ativeƀMTV૓૟૨ediumSpace;怋hiĀcn૦૘ë૙eryThiî૙tedĀGL૸ଆreaterGreateòٳessLesóੈLine;䀊r;쀀𝔑ȀBnptଢନଷ଺reak;恠BreakingSpace;䂠f;愕ڀ;CDEGHLNPRSTV୕ୖ୪୼஡௫ఄ౞಄ದ೘ൡඅ櫬Āou୛୤ngruent;扢pCap;扭oubleVerticalBar;戦ƀlqxஃஊ஛ement;戉ualĀ;Tஒஓ扠ilde;쀀≂̸ists;戄reater΀;EFGLSTஶஷ஽௉௓௘௥扯qual;扱ullEqual;쀀≧̸reater;쀀≫̸ess;批lantEqual;쀀⩾̸ilde;扵umpń௲௽ownHump;쀀≎̸qual;쀀≏̸eĀfsఊధtTriangleƀ;BEచఛడ拪ar;쀀⧏̸qual;括s̀;EGLSTవశ఼ౄోౘ扮qual;扰reater;扸ess;쀀≪̸lantEqual;쀀⩽̸ilde;扴estedĀGL౨౹reaterGreater;쀀⪢̸essLess;쀀⪡̸recedesƀ;ESಒಓಛ技qual;쀀⪯̸lantEqual;拠ĀeiಫಹverseElement;戌ghtTriangleƀ;BEೋೌ೒拫ar;쀀⧐̸qual;拭ĀquೝഌuareSuĀbp೨೹setĀ;E೰ೳ쀀⊏̸qual;拢ersetĀ;Eഃആ쀀⊐̸qual;拣ƀbcpഓതൎsetĀ;Eഛഞ쀀⊂⃒qual;抈ceedsȀ;ESTലള഻െ抁qual;쀀⪰̸lantEqual;拡ilde;쀀≿̸ersetĀ;E൘൛쀀⊃⃒qual;抉ildeȀ;EFT൮൯൵ൿ扁qual;扄ullEqual;扇ilde;扉erticalBar;戤cr;쀀𝒩ilde耻Ñ䃑;䎝܀Eacdfgmoprstuvලෂ෉෕ෛ෠෧෼ขภยา฿ไlig;䅒cute耻Ó䃓Āiy෎ීrc耻Ô䃔;䐞blac;䅐r;쀀𝔒rave耻Ò䃒ƀaei෮ෲ෶cr;䅌ga;䎩cron;䎟pf;쀀𝕆enCurlyĀDQฎบoubleQuote;怜uote;怘;橔Āclวฬr;쀀𝒪ash耻Ø䃘iŬื฼de耻Õ䃕es;樷ml耻Ö䃖erĀBP๋๠Āar๐๓r;怾acĀek๚๜;揞et;掴arenthesis;揜Ҁacfhilors๿ງຊຏຒດຝະ໼rtialD;戂y;䐟r;쀀𝔓i;䎦;䎠usMinus;䂱Āipຢອncareplanåڝf;愙Ȁ;eio຺ູ໠໤檻cedesȀ;EST່້໏໚扺qual;檯lantEqual;扼ilde;找me;怳Ādp໩໮uct;戏ortionĀ;aȥ໹l;戝Āci༁༆r;쀀𝒫;䎨ȀUfos༑༖༛༟OT耻"䀢r;쀀𝔔pf;愚cr;쀀𝒬؀BEacefhiorsu༾གྷཇའཱིྦྷྪྭ႖ႩႴႾarr;椐G耻®䂮ƀcnrཎནབute;䅔g;柫rĀ;tཛྷཝ憠l;椖ƀaeyཧཬཱron;䅘dil;䅖;䐠Ā;vླྀཹ愜erseĀEUྂྙĀlq྇ྎement;戋uilibrium;懋pEquilibrium;楯r»ཹo;䎡ghtЀACDFTUVa࿁࿫࿳ဢဨၛႇϘĀnr࿆࿒gleBracket;柩rowƀ;BL࿜࿝࿡憒ar;懥eftArrow;懄eiling;按oǵ࿹\0စbleBracket;柧nǔည\0နeeVector;楝ectorĀ;Bဝသ懂ar;楕loor;挋Āerိ၃eƀ;AVဵံြ抢rrow;憦ector;楛iangleƀ;BEၐၑၕ抳ar;槐qual;抵pƀDTVၣၮၸownVector;楏eeVector;楜ectorĀ;Bႂႃ憾ar;楔ectorĀ;B႑႒懀ar;楓Āpuႛ႞f;愝ndImplies;楰ightarrow;懛ĀchႹႼr;愛;憱leDelayed;槴ڀHOacfhimoqstuფჱჷჽᄙᄞᅑᅖᅡᅧᆵᆻᆿĀCcჩხHcy;䐩y;䐨FTcy;䐬cute;䅚ʀ;aeiyᄈᄉᄎᄓᄗ檼ron;䅠dil;䅞rc;䅜;䐡r;쀀𝔖ortȀDLRUᄪᄴᄾᅉownArrow»ОeftArrow»࢚ightArrow»࿝pArrow;憑gma;䎣allCircle;战pf;쀀𝕊ɲᅭ\0\0ᅰt;戚areȀ;ISUᅻᅼᆉᆯ斡ntersection;抓uĀbpᆏᆞsetĀ;Eᆗᆘ抏qual;抑ersetĀ;Eᆨᆩ抐qual;抒nion;抔cr;쀀𝒮ar;拆ȀbcmpᇈᇛሉላĀ;sᇍᇎ拐etĀ;Eᇍᇕqual;抆ĀchᇠህeedsȀ;ESTᇭᇮᇴᇿ扻qual;檰lantEqual;扽ilde;承Tháྌ;我ƀ;esሒሓሣ拑rsetĀ;Eሜም抃qual;抇et»ሓրHRSacfhiorsሾቄ቉ቕ቞ቱቶኟዂወዑORN耻Þ䃞ADE;愢ĀHc቎ቒcy;䐋y;䐦Ābuቚቜ;䀉;䎤ƀaeyብቪቯron;䅤dil;䅢;䐢r;쀀𝔗Āeiቻ኉ǲኀ\0ኇefore;戴a;䎘Ācn኎ኘkSpace;쀀  Space;怉ldeȀ;EFTካኬኲኼ戼qual;扃ullEqual;扅ilde;扈pf;쀀𝕋ipleDot;惛Āctዖዛr;쀀𝒯rok;䅦ૡዷጎጚጦ\0ጬጱ\0\0\0\0\0ጸጽ፷ᎅ\0᏿ᐄᐊᐐĀcrዻጁute耻Ú䃚rĀ;oጇገ憟cir;楉rǣጓ\0጖y;䐎ve;䅬Āiyጞጣrc耻Û䃛;䐣blac;䅰r;쀀𝔘rave耻Ù䃙acr;䅪Ādiፁ፩erĀBPፈ፝Āarፍፐr;䁟acĀekፗፙ;揟et;掵arenthesis;揝onĀ;P፰፱拃lus;抎Āgp፻፿on;䅲f;쀀𝕌ЀADETadps᎕ᎮᎸᏄϨᏒᏗᏳrrowƀ;BDᅐᎠᎤar;椒ownArrow;懅ownArrow;憕quilibrium;楮eeĀ;AᏋᏌ报rrow;憥ownáϳerĀLRᏞᏨeftArrow;憖ightArrow;憗iĀ;lᏹᏺ䏒on;䎥ing;䅮cr;쀀𝒰ilde;䅨ml耻Ü䃜ҀDbcdefosvᐧᐬᐰᐳᐾᒅᒊᒐᒖash;披ar;櫫y;䐒ashĀ;lᐻᐼ抩;櫦Āerᑃᑅ;拁ƀbtyᑌᑐᑺar;怖Ā;iᑏᑕcalȀBLSTᑡᑥᑪᑴar;戣ine;䁼eparator;杘ilde;所ThinSpace;怊r;쀀𝔙pf;쀀𝕍cr;쀀𝒱dash;抪ʀcefosᒧᒬᒱᒶᒼirc;䅴dge;拀r;쀀𝔚pf;쀀𝕎cr;쀀𝒲Ȁfiosᓋᓐᓒᓘr;쀀𝔛;䎞pf;쀀𝕏cr;쀀𝒳ҀAIUacfosuᓱᓵᓹᓽᔄᔏᔔᔚᔠcy;䐯cy;䐇cy;䐮cute耻Ý䃝Āiyᔉᔍrc;䅶;䐫r;쀀𝔜pf;쀀𝕐cr;쀀𝒴ml;䅸ЀHacdefosᔵᔹᔿᕋᕏᕝᕠᕤcy;䐖cute;䅹Āayᕄᕉron;䅽;䐗ot;䅻ǲᕔ\0ᕛoWidtè૙a;䎖r;愨pf;愤cr;쀀𝒵௡ᖃᖊᖐ\0ᖰᖶᖿ\0\0\0\0ᗆᗛᗫᙟ᙭\0ᚕ᚛ᚲᚹ\0ᚾcute耻á䃡reve;䄃̀;Ediuyᖜᖝᖡᖣᖨᖭ戾;쀀∾̳;房rc耻â䃢te肻´̆;䐰lig耻æ䃦Ā;r²ᖺ;쀀𝔞rave耻à䃠ĀepᗊᗖĀfpᗏᗔsym;愵èᗓha;䎱ĀapᗟcĀclᗤᗧr;䄁g;樿ɤᗰ\0\0ᘊʀ;adsvᗺᗻᗿᘁᘇ戧nd;橕;橜lope;橘;橚΀;elmrszᘘᘙᘛᘞᘿᙏᙙ戠;榤e»ᘙsdĀ;aᘥᘦ戡ѡᘰᘲᘴᘶᘸᘺᘼᘾ;榨;榩;榪;榫;榬;榭;榮;榯tĀ;vᙅᙆ戟bĀ;dᙌᙍ抾;榝Āptᙔᙗh;戢»¹arr;捼Āgpᙣᙧon;䄅f;쀀𝕒΀;Eaeiop዁ᙻᙽᚂᚄᚇᚊ;橰cir;橯;扊d;手s;䀧roxĀ;e዁ᚒñᚃing耻å䃥ƀctyᚡᚦᚨr;쀀𝒶;䀪mpĀ;e዁ᚯñʈilde耻ã䃣ml耻ä䃤Āciᛂᛈoninôɲnt;樑ࠀNabcdefiklnoprsu᛭ᛱᜰ᜼ᝃᝈ᝸᝽០៦ᠹᡐᜍ᤽᥈ᥰot;櫭Ācrᛶ᜞kȀcepsᜀᜅᜍᜓong;扌psilon;䏶rime;怵imĀ;e᜚᜛戽q;拍Ŷᜢᜦee;抽edĀ;gᜬᜭ挅e»ᜭrkĀ;t፜᜷brk;掶Āoyᜁᝁ;䐱quo;怞ʀcmprtᝓ᝛ᝡᝤᝨausĀ;eĊĉptyv;榰séᜌnoõēƀahwᝯ᝱ᝳ;䎲;愶een;扬r;쀀𝔟g΀costuvwឍឝឳេ៕៛៞ƀaiuបពរðݠrc;旯p»፱ƀdptឤឨឭot;樀lus;樁imes;樂ɱឹ\0\0ើcup;樆ar;昅riangleĀdu៍្own;施p;斳plus;樄eåᑄåᒭarow;植ƀako៭ᠦᠵĀcn៲ᠣkƀlst៺֫᠂ozenge;槫riangleȀ;dlr᠒᠓᠘᠝斴own;斾eft;旂ight;斸k;搣Ʊᠫ\0ᠳƲᠯ\0ᠱ;斒;斑4;斓ck;斈ĀeoᠾᡍĀ;qᡃᡆ쀀=⃥uiv;쀀≡⃥t;挐Ȁptwxᡙᡞᡧᡬf;쀀𝕓Ā;tᏋᡣom»Ꮜtie;拈؀DHUVbdhmptuvᢅᢖᢪᢻᣗᣛᣬ᣿ᤅᤊᤐᤡȀLRlrᢎᢐᢒᢔ;敗;敔;敖;敓ʀ;DUduᢡᢢᢤᢦᢨ敐;敦;敩;敤;敧ȀLRlrᢳᢵᢷᢹ;敝;敚;敜;教΀;HLRhlrᣊᣋᣍᣏᣑᣓᣕ救;敬;散;敠;敫;敢;敟ox;槉ȀLRlrᣤᣦᣨᣪ;敕;敒;攐;攌ʀ;DUduڽ᣷᣹᣻᣽;敥;敨;攬;攴inus;抟lus;択imes;抠ȀLRlrᤙᤛᤝ᤟;敛;敘;攘;攔΀;HLRhlrᤰᤱᤳᤵᤷ᤻᤹攂;敪;敡;敞;攼;攤;攜Āevģ᥂bar耻¦䂦Ȁceioᥑᥖᥚᥠr;쀀𝒷mi;恏mĀ;e᜚᜜lƀ;bhᥨᥩᥫ䁜;槅sub;柈Ŭᥴ᥾lĀ;e᥹᥺怢t»᥺pƀ;Eeįᦅᦇ;檮Ā;qۜۛೡᦧ\0᧨ᨑᨕᨲ\0ᨷᩐ\0\0᪴\0\0᫁\0\0ᬡᬮ᭍᭒\0᯽\0ᰌƀcpr᦭ᦲ᧝ute;䄇̀;abcdsᦿᧀᧄ᧊᧕᧙戩nd;橄rcup;橉Āau᧏᧒p;橋p;橇ot;橀;쀀∩︀Āeo᧢᧥t;恁îړȀaeiu᧰᧻ᨁᨅǰ᧵\0᧸s;橍on;䄍dil耻ç䃧rc;䄉psĀ;sᨌᨍ橌m;橐ot;䄋ƀdmnᨛᨠᨦil肻¸ƭptyv;榲t脀¢;eᨭᨮ䂢räƲr;쀀𝔠ƀceiᨽᩀᩍy;䑇ckĀ;mᩇᩈ朓ark»ᩈ;䏇r΀;Ecefms᩟᩠ᩢᩫ᪤᪪᪮旋;槃ƀ;elᩩᩪᩭ䋆q;扗eɡᩴ\0\0᪈rrowĀlr᩼᪁eft;憺ight;憻ʀRSacd᪒᪔᪖᪚᪟»ཇ;擈st;抛irc;抚ash;抝nint;樐id;櫯cir;槂ubsĀ;u᪻᪼晣it»᪼ˬ᫇᫔᫺\0ᬊonĀ;eᫍᫎ䀺Ā;qÇÆɭ᫙\0\0᫢aĀ;t᫞᫟䀬;䁀ƀ;fl᫨᫩᫫戁îᅠeĀmx᫱᫶ent»᫩eóɍǧ᫾\0ᬇĀ;dኻᬂot;橭nôɆƀfryᬐᬔᬗ;쀀𝕔oäɔ脀©;sŕᬝr;愗Āaoᬥᬩrr;憵ss;朗Ācuᬲᬷr;쀀𝒸Ābpᬼ᭄Ā;eᭁᭂ櫏;櫑Ā;eᭉᭊ櫐;櫒dot;拯΀delprvw᭠᭬᭷ᮂᮬᯔ᯹arrĀlr᭨᭪;椸;椵ɰ᭲\0\0᭵r;拞c;拟arrĀ;p᭿ᮀ憶;椽̀;bcdosᮏᮐᮖᮡᮥᮨ截rcap;橈Āauᮛᮞp;橆p;橊ot;抍r;橅;쀀∪︀Ȁalrv᮵ᮿᯞᯣrrĀ;mᮼᮽ憷;椼yƀevwᯇᯔᯘqɰᯎ\0\0ᯒreã᭳uã᭵ee;拎edge;拏en耻¤䂤earrowĀlrᯮ᯳eft»ᮀight»ᮽeäᯝĀciᰁᰇoninôǷnt;戱lcty;挭ঀAHabcdefhijlorstuwz᰸᰻᰿ᱝᱩᱵᲊᲞᲬᲷ᳻᳿ᴍᵻᶑᶫᶻ᷆᷍rò΁ar;楥Ȁglrs᱈ᱍ᱒᱔ger;怠eth;愸òᄳhĀ;vᱚᱛ怐»ऊūᱡᱧarow;椏aã̕Āayᱮᱳron;䄏;䐴ƀ;ao̲ᱼᲄĀgrʿᲁr;懊tseq;橷ƀglmᲑᲔᲘ耻°䂰ta;䎴ptyv;榱ĀirᲣᲨsht;楿;쀀𝔡arĀlrᲳᲵ»ࣜ»သʀaegsv᳂͸᳖᳜᳠mƀ;oș᳊᳔ndĀ;ș᳑uit;晦amma;䏝in;拲ƀ;io᳧᳨᳸䃷de脀÷;o᳧ᳰntimes;拇nø᳷cy;䑒cɯᴆ\0\0ᴊrn;挞op;挍ʀlptuwᴘᴝᴢᵉᵕlar;䀤f;쀀𝕕ʀ;emps̋ᴭᴷᴽᵂqĀ;d͒ᴳot;扑inus;戸lus;戔quare;抡blebarwedgåúnƀadhᄮᵝᵧownarrowóᲃarpoonĀlrᵲᵶefôᲴighôᲶŢᵿᶅkaro÷གɯᶊ\0\0ᶎrn;挟op;挌ƀcotᶘᶣᶦĀryᶝᶡ;쀀𝒹;䑕l;槶rok;䄑Ādrᶰᶴot;拱iĀ;fᶺ᠖斿Āah᷀᷃ròЩaòྦangle;榦Āci᷒ᷕy;䑟grarr;柿ऀDacdefglmnopqrstuxḁḉḙḸոḼṉṡṾấắẽỡἪἷὄ὎὚ĀDoḆᴴoôᲉĀcsḎḔute耻é䃩ter;橮ȀaioyḢḧḱḶron;䄛rĀ;cḭḮ扖耻ê䃪lon;払;䑍ot;䄗ĀDrṁṅot;扒;쀀𝔢ƀ;rsṐṑṗ檚ave耻è䃨Ā;dṜṝ檖ot;檘Ȁ;ilsṪṫṲṴ檙nters;揧;愓Ā;dṹṺ檕ot;檗ƀapsẅẉẗcr;䄓tyƀ;svẒẓẕ戅et»ẓpĀ1;ẝẤĳạả;怄;怅怃ĀgsẪẬ;䅋p;怂ĀgpẴẸon;䄙f;쀀𝕖ƀalsỄỎỒrĀ;sỊị拕l;槣us;橱iƀ;lvỚớở䎵on»ớ;䏵ȀcsuvỪỳἋἣĀioữḱrc»Ḯɩỹ\0\0ỻíՈantĀglἂἆtr»ṝess»Ṻƀaeiἒ἖Ἒls;䀽st;扟vĀ;DȵἠD;橸parsl;槥ĀDaἯἳot;打rr;楱ƀcdiἾὁỸr;愯oô͒ĀahὉὋ;䎷耻ð䃰Āmrὓὗl耻ë䃫o;悬ƀcipὡὤὧl;䀡sôծĀeoὬὴctatioîՙnentialåչৡᾒ\0ᾞ\0ᾡᾧ\0\0ῆῌ\0ΐ\0ῦῪ \0 ⁚llingdotseñṄy;䑄male;晀ƀilrᾭᾳ῁lig;耀ﬃɩᾹ\0\0᾽g;耀ﬀig;耀ﬄ;쀀𝔣lig;耀ﬁlig;쀀fjƀaltῙ῜ῡt;晭ig;耀ﬂns;斱of;䆒ǰ΅\0ῳf;쀀𝕗ĀakֿῷĀ;vῼ´拔;櫙artint;樍Āao‌⁕Ācs‑⁒α‚‰‸⁅⁈\0⁐β•‥‧‪‬\0‮耻½䂽;慓耻¼䂼;慕;慙;慛Ƴ‴\0‶;慔;慖ʴ‾⁁\0\0⁃耻¾䂾;慗;慜5;慘ƶ⁌\0⁎;慚;慝8;慞l;恄wn;挢cr;쀀𝒻ࢀEabcdefgijlnorstv₂₉₟₥₰₴⃰⃵⃺⃿℃ℒℸ̗ℾ⅒↞Ā;lٍ₇;檌ƀcmpₐₕ₝ute;䇵maĀ;dₜ᳚䎳;檆reve;䄟Āiy₪₮rc;䄝;䐳ot;䄡Ȁ;lqsؾق₽⃉ƀ;qsؾٌ⃄lanô٥Ȁ;cdl٥⃒⃥⃕c;檩otĀ;o⃜⃝檀Ā;l⃢⃣檂;檄Ā;e⃪⃭쀀⋛︀s;檔r;쀀𝔤Ā;gٳ؛mel;愷cy;䑓Ȁ;Eajٚℌℎℐ;檒;檥;檤ȀEaesℛℝ℩ℴ;扩pĀ;p℣ℤ檊rox»ℤĀ;q℮ℯ檈Ā;q℮ℛim;拧pf;쀀𝕘Āci⅃ⅆr;愊mƀ;el٫ⅎ⅐;檎;檐茀>;cdlqr׮ⅠⅪⅮⅳⅹĀciⅥⅧ;檧r;橺ot;拗Par;榕uest;橼ʀadelsↄⅪ←ٖ↛ǰ↉\0↎proø₞r;楸qĀlqؿ↖lesó₈ií٫Āen↣↭rtneqq;쀀≩︀Å↪ԀAabcefkosy⇄⇇⇱⇵⇺∘∝∯≨≽ròΠȀilmr⇐⇔⇗⇛rsðᒄf»․ilôکĀdr⇠⇤cy;䑊ƀ;cwࣴ⇫⇯ir;楈;憭ar;意irc;䄥ƀalr∁∎∓rtsĀ;u∉∊晥it»∊lip;怦con;抹r;쀀𝔥sĀew∣∩arow;椥arow;椦ʀamopr∺∾≃≞≣rr;懿tht;戻kĀlr≉≓eftarrow;憩ightarrow;憪f;쀀𝕙bar;怕ƀclt≯≴≸r;쀀𝒽asè⇴rok;䄧Ābp⊂⊇ull;恃hen»ᱛૡ⊣\0⊪\0⊸⋅⋎\0⋕⋳\0\0⋸⌢⍧⍢⍿\0⎆⎪⎴cute耻í䃭ƀ;iyݱ⊰⊵rc耻î䃮;䐸Ācx⊼⊿y;䐵cl耻¡䂡ĀfrΟ⋉;쀀𝔦rave耻ì䃬Ȁ;inoܾ⋝⋩⋮Āin⋢⋦nt;樌t;戭fin;槜ta;愩lig;䄳ƀaop⋾⌚⌝ƀcgt⌅⌈⌗r;䄫ƀelpܟ⌏⌓inåގarôܠh;䄱f;抷ed;䆵ʀ;cfotӴ⌬⌱⌽⍁are;愅inĀ;t⌸⌹戞ie;槝doô⌙ʀ;celpݗ⍌⍐⍛⍡al;抺Āgr⍕⍙eróᕣã⍍arhk;樗rod;樼Ȁcgpt⍯⍲⍶⍻y;䑑on;䄯f;쀀𝕚a;䎹uest耻¿䂿Āci⎊⎏r;쀀𝒾nʀ;EdsvӴ⎛⎝⎡ӳ;拹ot;拵Ā;v⎦⎧拴;拳Ā;iݷ⎮lde;䄩ǫ⎸\0⎼cy;䑖l耻ï䃯̀cfmosu⏌⏗⏜⏡⏧⏵Āiy⏑⏕rc;䄵;䐹r;쀀𝔧ath;䈷pf;쀀𝕛ǣ⏬\0⏱r;쀀𝒿rcy;䑘kcy;䑔Ѐacfghjos␋␖␢␧␭␱␵␻ppaĀ;v␓␔䎺;䏰Āey␛␠dil;䄷;䐺r;쀀𝔨reen;䄸cy;䑅cy;䑜pf;쀀𝕜cr;쀀𝓀஀ABEHabcdefghjlmnoprstuv⑰⒁⒆⒍⒑┎┽╚▀♎♞♥♹♽⚚⚲⛘❝❨➋⟀⠁⠒ƀart⑷⑺⑼rò৆òΕail;椛arr;椎Ā;gঔ⒋;檋ar;楢ॣ⒥\0⒪\0⒱\0\0\0\0\0⒵Ⓔ\0ⓆⓈⓍ\0⓹ute;䄺mptyv;榴raîࡌbda;䎻gƀ;dlࢎⓁⓃ;榑åࢎ;檅uo耻«䂫rЀ;bfhlpst࢙ⓞⓦⓩ⓫⓮⓱⓵Ā;f࢝ⓣs;椟s;椝ë≒p;憫l;椹im;楳l;憢ƀ;ae⓿─┄檫il;椙Ā;s┉┊檭;쀀⪭︀ƀabr┕┙┝rr;椌rk;杲Āak┢┬cĀek┨┪;䁻;䁛Āes┱┳;榋lĀdu┹┻;榏;榍Ȁaeuy╆╋╖╘ron;䄾Ādi═╔il;䄼ìࢰâ┩;䐻Ȁcqrs╣╦╭╽a;椶uoĀ;rนᝆĀdu╲╷har;楧shar;楋h;憲ʀ;fgqs▋▌উ◳◿扤tʀahlrt▘▤▷◂◨rrowĀ;t࢙□aé⓶arpoonĀdu▯▴own»њp»०eftarrows;懇ightƀahs◍◖◞rrowĀ;sࣴࢧarpoonó྘quigarro÷⇰hreetimes;拋ƀ;qs▋ও◺lanôবʀ;cdgsব☊☍☝☨c;檨otĀ;o☔☕橿Ā;r☚☛檁;檃Ā;e☢☥쀀⋚︀s;檓ʀadegs☳☹☽♉♋pproøⓆot;拖qĀgq♃♅ôউgtò⒌ôছiíলƀilr♕࣡♚sht;楼;쀀𝔩Ā;Eজ♣;檑š♩♶rĀdu▲♮Ā;l॥♳;楪lk;斄cy;䑙ʀ;achtੈ⚈⚋⚑⚖rò◁orneòᴈard;楫ri;旺Āio⚟⚤dot;䅀ustĀ;a⚬⚭掰che»⚭ȀEaes⚻⚽⛉⛔;扨pĀ;p⛃⛄檉rox»⛄Ā;q⛎⛏檇Ā;q⛎⚻im;拦Ѐabnoptwz⛩⛴⛷✚✯❁❇❐Ānr⛮⛱g;柬r;懽rëࣁgƀlmr⛿✍✔eftĀar০✇ightá৲apsto;柼ightá৽parrowĀlr✥✩efô⓭ight;憬ƀafl✶✹✽r;榅;쀀𝕝us;樭imes;樴š❋❏st;戗áፎƀ;ef❗❘᠀旊nge»❘arĀ;l❤❥䀨t;榓ʀachmt❳❶❼➅➇ròࢨorneòᶌarĀ;d྘➃;業;怎ri;抿̀achiqt➘➝ੀ➢➮➻quo;怹r;쀀𝓁mƀ;egল➪➬;檍;檏Ābu┪➳oĀ;rฟ➹;怚rok;䅂萀<;cdhilqrࠫ⟒☹⟜⟠⟥⟪⟰Āci⟗⟙;檦r;橹reå◲mes;拉arr;楶uest;橻ĀPi⟵⟹ar;榖ƀ;ef⠀भ᠛旃rĀdu⠇⠍shar;楊har;楦Āen⠗⠡rtneqq;쀀≨︀Å⠞܀Dacdefhilnopsu⡀⡅⢂⢎⢓⢠⢥⢨⣚⣢⣤ઃ⣳⤂Dot;戺Ȁclpr⡎⡒⡣⡽r耻¯䂯Āet⡗⡙;時Ā;e⡞⡟朠se»⡟Ā;sျ⡨toȀ;dluျ⡳⡷⡻owîҌefôएðᏑker;斮Āoy⢇⢌mma;権;䐼ash;怔asuredangle»ᘦr;쀀𝔪o;愧ƀcdn⢯⢴⣉ro耻µ䂵Ȁ;acdᑤ⢽⣀⣄sôᚧir;櫰ot肻·Ƶusƀ;bd⣒ᤃ⣓戒Ā;uᴼ⣘;横ţ⣞⣡p;櫛ò−ðઁĀdp⣩⣮els;抧f;쀀𝕞Āct⣸⣽r;쀀𝓂pos»ᖝƀ;lm⤉⤊⤍䎼timap;抸ఀGLRVabcdefghijlmoprstuvw⥂⥓⥾⦉⦘⧚⧩⨕⨚⩘⩝⪃⪕⪤⪨⬄⬇⭄⭿⮮ⰴⱧⱼ⳩Āgt⥇⥋;쀀⋙̸Ā;v⥐௏쀀≫⃒ƀelt⥚⥲⥶ftĀar⥡⥧rrow;懍ightarrow;懎;쀀⋘̸Ā;v⥻ే쀀≪⃒ightarrow;懏ĀDd⦎⦓ash;抯ash;抮ʀbcnpt⦣⦧⦬⦱⧌la»˞ute;䅄g;쀀∠⃒ʀ;Eiop඄⦼⧀⧅⧈;쀀⩰̸d;쀀≋̸s;䅉roø඄urĀ;a⧓⧔普lĀ;s⧓ସǳ⧟\0⧣p肻 ଷmpĀ;e௹ఀʀaeouy⧴⧾⨃⨐⨓ǰ⧹\0⧻;橃on;䅈dil;䅆ngĀ;dൾ⨊ot;쀀⩭̸p;橂;䐽ash;怓΀;Aadqsxஒ⨩⨭⨻⩁⩅⩐rr;懗rĀhr⨳⨶k;椤Ā;oᏲᏰot;쀀≐̸uiöୣĀei⩊⩎ar;椨í஘istĀ;s஠டr;쀀𝔫ȀEest௅⩦⩹⩼ƀ;qs஼⩭௡ƀ;qs஼௅⩴lanô௢ií௪Ā;rஶ⪁»ஷƀAap⪊⪍⪑rò⥱rr;憮ar;櫲ƀ;svྍ⪜ྌĀ;d⪡⪢拼;拺cy;䑚΀AEadest⪷⪺⪾⫂⫅⫶⫹rò⥦;쀀≦̸rr;憚r;急Ȁ;fqs఻⫎⫣⫯tĀar⫔⫙rro÷⫁ightarro÷⪐ƀ;qs఻⪺⫪lanôౕĀ;sౕ⫴»శiíౝĀ;rవ⫾iĀ;eచథiäඐĀpt⬌⬑f;쀀𝕟膀¬;in⬙⬚⬶䂬nȀ;Edvஉ⬤⬨⬮;쀀⋹̸ot;쀀⋵̸ǡஉ⬳⬵;拷;拶iĀ;vಸ⬼ǡಸ⭁⭃;拾;拽ƀaor⭋⭣⭩rȀ;ast୻⭕⭚⭟lleì୻l;쀀⫽⃥;쀀∂̸lint;樔ƀ;ceಒ⭰⭳uåಥĀ;cಘ⭸Ā;eಒ⭽ñಘȀAait⮈⮋⮝⮧rò⦈rrƀ;cw⮔⮕⮙憛;쀀⤳̸;쀀↝̸ghtarrow»⮕riĀ;eೋೖ΀chimpqu⮽⯍⯙⬄୸⯤⯯Ȁ;cerല⯆ഷ⯉uå൅;쀀𝓃ortɭ⬅\0\0⯖ará⭖mĀ;e൮⯟Ā;q൴൳suĀbp⯫⯭å೸åഋƀbcp⯶ⰑⰙȀ;Ees⯿ⰀഢⰄ抄;쀀⫅̸etĀ;eഛⰋqĀ;qണⰀcĀ;eലⰗñസȀ;EesⰢⰣൟⰧ抅;쀀⫆̸etĀ;e൘ⰮqĀ;qൠⰣȀgilrⰽⰿⱅⱇìௗlde耻ñ䃱çృiangleĀlrⱒⱜeftĀ;eచⱚñదightĀ;eೋⱥñ೗Ā;mⱬⱭ䎽ƀ;esⱴⱵⱹ䀣ro;愖p;怇ҀDHadgilrsⲏⲔⲙⲞⲣⲰⲶⳓⳣash;抭arr;椄p;쀀≍⃒ash;抬ĀetⲨⲬ;쀀≥⃒;쀀>⃒nfin;槞ƀAetⲽⳁⳅrr;椂;쀀≤⃒Ā;rⳊⳍ쀀<⃒ie;쀀⊴⃒ĀAtⳘⳜrr;椃rie;쀀⊵⃒im;쀀∼⃒ƀAan⳰⳴ⴂrr;懖rĀhr⳺⳽k;椣Ā;oᏧᏥear;椧ቓ᪕\0\0\0\0\0\0\0\0\0\0\0\0\0ⴭ\0ⴸⵈⵠⵥ⵲ⶄᬇ\0\0ⶍⶫ\0ⷈⷎ\0ⷜ⸙⸫⸾⹃Ācsⴱ᪗ute耻ó䃳ĀiyⴼⵅrĀ;c᪞ⵂ耻ô䃴;䐾ʀabios᪠ⵒⵗǈⵚlac;䅑v;樸old;榼lig;䅓Ācr⵩⵭ir;榿;쀀𝔬ͯ⵹\0\0⵼\0ⶂn;䋛ave耻ò䃲;槁Ābmⶈ෴ar;榵Ȁacitⶕ⶘ⶥⶨrò᪀Āir⶝ⶠr;榾oss;榻nå๒;槀ƀaeiⶱⶵⶹcr;䅍ga;䏉ƀcdnⷀⷅǍron;䎿;榶pf;쀀𝕠ƀaelⷔ⷗ǒr;榷rp;榹΀;adiosvⷪⷫⷮ⸈⸍⸐⸖戨rò᪆Ȁ;efmⷷⷸ⸂⸅橝rĀ;oⷾⷿ愴f»ⷿ耻ª䂪耻º䂺gof;抶r;橖lope;橗;橛ƀclo⸟⸡⸧ò⸁ash耻ø䃸l;折iŬⸯ⸴de耻õ䃵esĀ;aǛ⸺s;樶ml耻ö䃶bar;挽ૡ⹞\0⹽\0⺀⺝\0⺢⺹\0\0⻋ຜ\0⼓\0\0⼫⾼\0⿈rȀ;astЃ⹧⹲຅脀¶;l⹭⹮䂶leìЃɩ⹸\0\0⹻m;櫳;櫽y;䐿rʀcimpt⺋⺏⺓ᡥ⺗nt;䀥od;䀮il;怰enk;怱r;쀀𝔭ƀimo⺨⺰⺴Ā;v⺭⺮䏆;䏕maô੶ne;明ƀ;tv⺿⻀⻈䏀chfork»´;䏖Āau⻏⻟nĀck⻕⻝kĀ;h⇴⻛;愎ö⇴sҀ;abcdemst⻳⻴ᤈ⻹⻽⼄⼆⼊⼎䀫cir;樣ir;樢Āouᵀ⼂;樥;橲n肻±ຝim;樦wo;樧ƀipu⼙⼠⼥ntint;樕f;쀀𝕡nd耻£䂣Ԁ;Eaceinosu່⼿⽁⽄⽇⾁⾉⾒⽾⾶;檳p;檷uå໙Ā;c໎⽌̀;acens່⽙⽟⽦⽨⽾pproø⽃urlyeñ໙ñ໎ƀaes⽯⽶⽺pprox;檹qq;檵im;拨iíໟmeĀ;s⾈ຮ怲ƀEas⽸⾐⽺ð⽵ƀdfp໬⾙⾯ƀals⾠⾥⾪lar;挮ine;挒urf;挓Ā;t໻⾴ï໻rel;抰Āci⿀⿅r;쀀𝓅;䏈ncsp;怈̀fiopsu⿚⋢⿟⿥⿫⿱r;쀀𝔮pf;쀀𝕢rime;恗cr;쀀𝓆ƀaeo⿸〉〓tĀei⿾々rnionóڰnt;樖stĀ;e【】䀿ñἙô༔઀ABHabcdefhilmnoprstux぀けさすムㄎㄫㅇㅢㅲㆎ㈆㈕㈤㈩㉘㉮㉲㊐㊰㊷ƀartぇおがròႳòϝail;検aròᱥar;楤΀cdenqrtとふへみわゔヌĀeuねぱ;쀀∽̱te;䅕iãᅮmptyv;榳gȀ;del࿑らるろ;榒;榥å࿑uo耻»䂻rր;abcfhlpstw࿜ガクシスゼゾダッデナp;極Ā;f࿠ゴs;椠;椳s;椞ë≝ð✮l;楅im;楴l;憣;憝Āaiパフil;椚oĀ;nホボ戶aló༞ƀabrョリヮrò៥rk;杳ĀakンヽcĀekヹ・;䁽;䁝Āes㄂㄄;榌lĀduㄊㄌ;榎;榐Ȁaeuyㄗㄜㄧㄩron;䅙Ādiㄡㄥil;䅗ì࿲âヺ;䑀Ȁclqsㄴㄷㄽㅄa;椷dhar;楩uoĀ;rȎȍh;憳ƀacgㅎㅟངlȀ;ipsླྀㅘㅛႜnåႻarôྩt;断ƀilrㅩဣㅮsht;楽;쀀𝔯ĀaoㅷㆆrĀduㅽㅿ»ѻĀ;l႑ㆄ;楬Ā;vㆋㆌ䏁;䏱ƀgns㆕ㇹㇼht̀ahlrstㆤㆰ㇂㇘㇤㇮rrowĀ;t࿜ㆭaéトarpoonĀduㆻㆿowîㅾp»႒eftĀah㇊㇐rrowó࿪arpoonóՑightarrows;應quigarro÷ニhreetimes;拌g;䋚ingdotseñἲƀahm㈍㈐㈓rò࿪aòՑ;怏oustĀ;a㈞㈟掱che»㈟mid;櫮Ȁabpt㈲㈽㉀㉒Ānr㈷㈺g;柭r;懾rëဃƀafl㉇㉊㉎r;榆;쀀𝕣us;樮imes;樵Āap㉝㉧rĀ;g㉣㉤䀩t;榔olint;樒arò㇣Ȁachq㉻㊀Ⴜ㊅quo;怺r;쀀𝓇Ābu・㊊oĀ;rȔȓƀhir㊗㊛㊠reåㇸmes;拊iȀ;efl㊪ၙᠡ㊫方tri;槎luhar;楨;愞ൡ㋕㋛㋟㌬㌸㍱\0㍺㎤\0\0㏬㏰\0㐨㑈㑚㒭㒱㓊㓱\0㘖\0\0㘳cute;䅛quï➺Ԁ;Eaceinpsyᇭ㋳㋵㋿㌂㌋㌏㌟㌦㌩;檴ǰ㋺\0㋼;檸on;䅡uåᇾĀ;dᇳ㌇il;䅟rc;䅝ƀEas㌖㌘㌛;檶p;檺im;择olint;樓iíሄ;䑁otƀ;be㌴ᵇ㌵担;橦΀Aacmstx㍆㍊㍗㍛㍞㍣㍭rr;懘rĀhr㍐㍒ë∨Ā;oਸ਼਴t耻§䂧i;䀻war;椩mĀin㍩ðnuóñt;朶rĀ;o㍶⁕쀀𝔰Ȁacoy㎂㎆㎑㎠rp;景Āhy㎋㎏cy;䑉;䑈rtɭ㎙\0\0㎜iäᑤaraì⹯耻­䂭Āgm㎨㎴maƀ;fv㎱㎲㎲䏃;䏂Ѐ;deglnprካ㏅㏉㏎㏖㏞㏡㏦ot;橪Ā;q኱ኰĀ;E㏓㏔檞;檠Ā;E㏛㏜檝;檟e;扆lus;樤arr;楲aròᄽȀaeit㏸㐈㐏㐗Āls㏽㐄lsetmé㍪hp;樳parsl;槤Ādlᑣ㐔e;挣Ā;e㐜㐝檪Ā;s㐢㐣檬;쀀⪬︀ƀflp㐮㐳㑂tcy;䑌Ā;b㐸㐹䀯Ā;a㐾㐿槄r;挿f;쀀𝕤aĀdr㑍ЂesĀ;u㑔㑕晠it»㑕ƀcsu㑠㑹㒟Āau㑥㑯pĀ;sᆈ㑫;쀀⊓︀pĀ;sᆴ㑵;쀀⊔︀uĀbp㑿㒏ƀ;esᆗᆜ㒆etĀ;eᆗ㒍ñᆝƀ;esᆨᆭ㒖etĀ;eᆨ㒝ñᆮƀ;afᅻ㒦ְrť㒫ֱ»ᅼaròᅈȀcemt㒹㒾㓂㓅r;쀀𝓈tmîñiì㐕aræᆾĀar㓎㓕rĀ;f㓔ឿ昆Āan㓚㓭ightĀep㓣㓪psiloîỠhé⺯s»⡒ʀbcmnp㓻㕞ሉ㖋㖎Ҁ;Edemnprs㔎㔏㔑㔕㔞㔣㔬㔱㔶抂;櫅ot;檽Ā;dᇚ㔚ot;櫃ult;櫁ĀEe㔨㔪;櫋;把lus;檿arr;楹ƀeiu㔽㕒㕕tƀ;en㔎㕅㕋qĀ;qᇚ㔏eqĀ;q㔫㔨m;櫇Ābp㕚㕜;櫕;櫓c̀;acensᇭ㕬㕲㕹㕻㌦pproø㋺urlyeñᇾñᇳƀaes㖂㖈㌛pproø㌚qñ㌗g;晪ڀ123;Edehlmnps㖩㖬㖯ሜ㖲㖴㗀㗉㗕㗚㗟㗨㗭耻¹䂹耻²䂲耻³䂳;櫆Āos㖹㖼t;檾ub;櫘Ā;dሢ㗅ot;櫄sĀou㗏㗒l;柉b;櫗arr;楻ult;櫂ĀEe㗤㗦;櫌;抋lus;櫀ƀeiu㗴㘉㘌tƀ;enሜ㗼㘂qĀ;qሢ㖲eqĀ;q㗧㗤m;櫈Ābp㘑㘓;櫔;櫖ƀAan㘜㘠㘭rr;懙rĀhr㘦㘨ë∮Ā;oਫ਩war;椪lig耻ß䃟௡㙑㙝㙠ዎ㙳㙹\0㙾㛂\0\0\0\0\0㛛㜃\0㜉㝬\0\0\0㞇ɲ㙖\0\0㙛get;挖;䏄rë๟ƀaey㙦㙫㙰ron;䅥dil;䅣;䑂lrec;挕r;쀀𝔱Ȁeiko㚆㚝㚵㚼ǲ㚋\0㚑eĀ4fኄኁaƀ;sv㚘㚙㚛䎸ym;䏑Ācn㚢㚲kĀas㚨㚮pproø዁im»ኬsðኞĀas㚺㚮ð዁rn耻þ䃾Ǭ̟㛆⋧es膀×;bd㛏㛐㛘䃗Ā;aᤏ㛕r;樱;樰ƀeps㛡㛣㜀á⩍Ȁ;bcf҆㛬㛰㛴ot;挶ir;櫱Ā;o㛹㛼쀀𝕥rk;櫚á㍢rime;怴ƀaip㜏㜒㝤dåቈ΀adempst㜡㝍㝀㝑㝗㝜㝟ngleʀ;dlqr㜰㜱㜶㝀㝂斵own»ᶻeftĀ;e⠀㜾ñम;扜ightĀ;e㊪㝋ñၚot;旬inus;樺lus;樹b;槍ime;樻ezium;揢ƀcht㝲㝽㞁Āry㝷㝻;쀀𝓉;䑆cy;䑛rok;䅧Āio㞋㞎xô᝷headĀlr㞗㞠eftarro÷ࡏightarrow»ཝऀAHabcdfghlmoprstuw㟐㟓㟗㟤㟰㟼㠎㠜㠣㠴㡑㡝㡫㢩㣌㣒㣪㣶ròϭar;楣Ācr㟜㟢ute耻ú䃺òᅐrǣ㟪\0㟭y;䑞ve;䅭Āiy㟵㟺rc耻û䃻;䑃ƀabh㠃㠆㠋ròᎭlac;䅱aòᏃĀir㠓㠘sht;楾;쀀𝔲rave耻ù䃹š㠧㠱rĀlr㠬㠮»ॗ»ႃlk;斀Āct㠹㡍ɯ㠿\0\0㡊rnĀ;e㡅㡆挜r»㡆op;挏ri;旸Āal㡖㡚cr;䅫肻¨͉Āgp㡢㡦on;䅳f;쀀𝕦̀adhlsuᅋ㡸㡽፲㢑㢠ownáᎳarpoonĀlr㢈㢌efô㠭ighô㠯iƀ;hl㢙㢚㢜䏅»ᏺon»㢚parrows;懈ƀcit㢰㣄㣈ɯ㢶\0\0㣁rnĀ;e㢼㢽挝r»㢽op;挎ng;䅯ri;旹cr;쀀𝓊ƀdir㣙㣝㣢ot;拰lde;䅩iĀ;f㜰㣨»᠓Āam㣯㣲rò㢨l耻ü䃼angle;榧ހABDacdeflnoprsz㤜㤟㤩㤭㦵㦸㦽㧟㧤㧨㧳㧹㧽㨁㨠ròϷarĀ;v㤦㤧櫨;櫩asèϡĀnr㤲㤷grt;榜΀eknprst㓣㥆㥋㥒㥝㥤㦖appá␕othinçẖƀhir㓫⻈㥙opô⾵Ā;hᎷ㥢ïㆍĀiu㥩㥭gmá㎳Ābp㥲㦄setneqĀ;q㥽㦀쀀⊊︀;쀀⫋︀setneqĀ;q㦏㦒쀀⊋︀;쀀⫌︀Āhr㦛㦟etá㚜iangleĀlr㦪㦯eft»थight»ၑy;䐲ash»ံƀelr㧄㧒㧗ƀ;beⷪ㧋㧏ar;抻q;扚lip;拮Ābt㧜ᑨaòᑩr;쀀𝔳tré㦮suĀbp㧯㧱»ജ»൙pf;쀀𝕧roð໻tré㦴Ācu㨆㨋r;쀀𝓋Ābp㨐㨘nĀEe㦀㨖»㥾nĀEe㦒㨞»㦐igzag;榚΀cefoprs㨶㨻㩖㩛㩔㩡㩪irc;䅵Ādi㩀㩑Ābg㩅㩉ar;機eĀ;qᗺ㩏;扙erp;愘r;쀀𝔴pf;쀀𝕨Ā;eᑹ㩦atèᑹcr;쀀𝓌ૣណ㪇\0㪋\0㪐㪛\0\0㪝㪨㪫㪯\0\0㫃㫎\0㫘ៜ៟tré៑r;쀀𝔵ĀAa㪔㪗ròσrò৶;䎾ĀAa㪡㪤ròθrò৫að✓is;拻ƀdptឤ㪵㪾Āfl㪺ឩ;쀀𝕩imåឲĀAa㫇㫊ròώròਁĀcq㫒ីr;쀀𝓍Āpt៖㫜ré។Ѐacefiosu㫰㫽㬈㬌㬑㬕㬛㬡cĀuy㫶㫻te耻ý䃽;䑏Āiy㬂㬆rc;䅷;䑋n耻¥䂥r;쀀𝔶cy;䑗pf;쀀𝕪cr;쀀𝓎Ācm㬦㬩y;䑎l耻ÿ䃿Ԁacdefhiosw㭂㭈㭔㭘㭤㭩㭭㭴㭺㮀cute;䅺Āay㭍㭒ron;䅾;䐷ot;䅼Āet㭝㭡træᕟa;䎶r;쀀𝔷cy;䐶grarr;懝pf;쀀𝕫cr;쀀𝓏Ājn㮅㮇;怍j;怌'.split("").map((e) => e.charCodeAt(0))
), mo = new Uint16Array(
  // prettier-ignore
  "Ȁaglq	\x1Bɭ\0\0p;䀦os;䀧t;䀾t;䀼uot;䀢".split("").map((e) => e.charCodeAt(0))
);
var $t;
const Do = /* @__PURE__ */ new Map([
  [0, 65533],
  // C1 Unicode control character reference replacements
  [128, 8364],
  [130, 8218],
  [131, 402],
  [132, 8222],
  [133, 8230],
  [134, 8224],
  [135, 8225],
  [136, 710],
  [137, 8240],
  [138, 352],
  [139, 8249],
  [140, 338],
  [142, 381],
  [145, 8216],
  [146, 8217],
  [147, 8220],
  [148, 8221],
  [149, 8226],
  [150, 8211],
  [151, 8212],
  [152, 732],
  [153, 8482],
  [154, 353],
  [155, 8250],
  [156, 339],
  [158, 382],
  [159, 376]
]), Eo = (
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, node/no-unsupported-features/es-builtins
  ($t = String.fromCodePoint) !== null && $t !== void 0 ? $t : function(e) {
    let u = "";
    return e > 65535 && (e -= 65536, u += String.fromCharCode(e >>> 10 & 1023 | 55296), e = 56320 | e & 1023), u += String.fromCharCode(e), u;
  }
);
function xo(e) {
  var u;
  return e >= 55296 && e <= 57343 || e > 1114111 ? 65533 : (u = Do.get(e)) !== null && u !== void 0 ? u : e;
}
var Fe;
(function(e) {
  e[e.NUM = 35] = "NUM", e[e.SEMI = 59] = "SEMI", e[e.EQUALS = 61] = "EQUALS", e[e.ZERO = 48] = "ZERO", e[e.NINE = 57] = "NINE", e[e.LOWER_A = 97] = "LOWER_A", e[e.LOWER_F = 102] = "LOWER_F", e[e.LOWER_X = 120] = "LOWER_X", e[e.LOWER_Z = 122] = "LOWER_Z", e[e.UPPER_A = 65] = "UPPER_A", e[e.UPPER_F = 70] = "UPPER_F", e[e.UPPER_Z = 90] = "UPPER_Z";
})(Fe || (Fe = {}));
const Co = 32;
var lu;
(function(e) {
  e[e.VALUE_LENGTH = 49152] = "VALUE_LENGTH", e[e.BRANCH_LENGTH = 16256] = "BRANCH_LENGTH", e[e.JUMP_TABLE = 127] = "JUMP_TABLE";
})(lu || (lu = {}));
function sn(e) {
  return e >= Fe.ZERO && e <= Fe.NINE;
}
function Ao(e) {
  return e >= Fe.UPPER_A && e <= Fe.UPPER_F || e >= Fe.LOWER_A && e <= Fe.LOWER_F;
}
function _o(e) {
  return e >= Fe.UPPER_A && e <= Fe.UPPER_Z || e >= Fe.LOWER_A && e <= Fe.LOWER_Z || sn(e);
}
function Fo(e) {
  return e === Fe.EQUALS || _o(e);
}
var Ae;
(function(e) {
  e[e.EntityStart = 0] = "EntityStart", e[e.NumericStart = 1] = "NumericStart", e[e.NumericDecimal = 2] = "NumericDecimal", e[e.NumericHex = 3] = "NumericHex", e[e.NamedEntity = 4] = "NamedEntity";
})(Ae || (Ae = {}));
var uu;
(function(e) {
  e[e.Legacy = 0] = "Legacy", e[e.Strict = 1] = "Strict", e[e.Attribute = 2] = "Attribute";
})(uu || (uu = {}));
class yo {
  constructor(u, n, r) {
    this.decodeTree = u, this.emitCodePoint = n, this.errors = r, this.state = Ae.EntityStart, this.consumed = 1, this.result = 0, this.treeIndex = 0, this.excess = 1, this.decodeMode = uu.Strict;
  }
  /** Resets the instance to make it reusable. */
  startEntity(u) {
    this.decodeMode = u, this.state = Ae.EntityStart, this.result = 0, this.treeIndex = 0, this.excess = 1, this.consumed = 1;
  }
  /**
   * Write an entity to the decoder. This can be called multiple times with partial entities.
   * If the entity is incomplete, the decoder will return -1.
   *
   * Mirrors the implementation of `getDecoder`, but with the ability to stop decoding if the
   * entity is incomplete, and resume when the next string is written.
   *
   * @param string The string containing the entity (or a continuation of the entity).
   * @param offset The offset at which the entity begins. Should be 0 if this is not the first call.
   * @returns The number of characters that were consumed, or -1 if the entity is incomplete.
   */
  write(u, n) {
    switch (this.state) {
      case Ae.EntityStart:
        return u.charCodeAt(n) === Fe.NUM ? (this.state = Ae.NumericStart, this.consumed += 1, this.stateNumericStart(u, n + 1)) : (this.state = Ae.NamedEntity, this.stateNamedEntity(u, n));
      case Ae.NumericStart:
        return this.stateNumericStart(u, n);
      case Ae.NumericDecimal:
        return this.stateNumericDecimal(u, n);
      case Ae.NumericHex:
        return this.stateNumericHex(u, n);
      case Ae.NamedEntity:
        return this.stateNamedEntity(u, n);
    }
  }
  /**
   * Switches between the numeric decimal and hexadecimal states.
   *
   * Equivalent to the `Numeric character reference state` in the HTML spec.
   *
   * @param str The string containing the entity (or a continuation of the entity).
   * @param offset The current offset.
   * @returns The number of characters that were consumed, or -1 if the entity is incomplete.
   */
  stateNumericStart(u, n) {
    return n >= u.length ? -1 : (u.charCodeAt(n) | Co) === Fe.LOWER_X ? (this.state = Ae.NumericHex, this.consumed += 1, this.stateNumericHex(u, n + 1)) : (this.state = Ae.NumericDecimal, this.stateNumericDecimal(u, n));
  }
  addToNumericResult(u, n, r, i) {
    if (n !== r) {
      const t = r - n;
      this.result = this.result * Math.pow(i, t) + parseInt(u.substr(n, t), i), this.consumed += t;
    }
  }
  /**
   * Parses a hexadecimal numeric entity.
   *
   * Equivalent to the `Hexademical character reference state` in the HTML spec.
   *
   * @param str The string containing the entity (or a continuation of the entity).
   * @param offset The current offset.
   * @returns The number of characters that were consumed, or -1 if the entity is incomplete.
   */
  stateNumericHex(u, n) {
    const r = n;
    for (; n < u.length; ) {
      const i = u.charCodeAt(n);
      if (sn(i) || Ao(i))
        n += 1;
      else
        return this.addToNumericResult(u, r, n, 16), this.emitNumericEntity(i, 3);
    }
    return this.addToNumericResult(u, r, n, 16), -1;
  }
  /**
   * Parses a decimal numeric entity.
   *
   * Equivalent to the `Decimal character reference state` in the HTML spec.
   *
   * @param str The string containing the entity (or a continuation of the entity).
   * @param offset The current offset.
   * @returns The number of characters that were consumed, or -1 if the entity is incomplete.
   */
  stateNumericDecimal(u, n) {
    const r = n;
    for (; n < u.length; ) {
      const i = u.charCodeAt(n);
      if (sn(i))
        n += 1;
      else
        return this.addToNumericResult(u, r, n, 10), this.emitNumericEntity(i, 2);
    }
    return this.addToNumericResult(u, r, n, 10), -1;
  }
  /**
   * Validate and emit a numeric entity.
   *
   * Implements the logic from the `Hexademical character reference start
   * state` and `Numeric character reference end state` in the HTML spec.
   *
   * @param lastCp The last code point of the entity. Used to see if the
   *               entity was terminated with a semicolon.
   * @param expectedLength The minimum number of characters that should be
   *                       consumed. Used to validate that at least one digit
   *                       was consumed.
   * @returns The number of characters that were consumed.
   */
  emitNumericEntity(u, n) {
    var r;
    if (this.consumed <= n)
      return (r = this.errors) === null || r === void 0 || r.absenceOfDigitsInNumericCharacterReference(this.consumed), 0;
    if (u === Fe.SEMI)
      this.consumed += 1;
    else if (this.decodeMode === uu.Strict)
      return 0;
    return this.emitCodePoint(xo(this.result), this.consumed), this.errors && (u !== Fe.SEMI && this.errors.missingSemicolonAfterCharacterReference(), this.errors.validateNumericCharacterReference(this.result)), this.consumed;
  }
  /**
   * Parses a named entity.
   *
   * Equivalent to the `Named character reference state` in the HTML spec.
   *
   * @param str The string containing the entity (or a continuation of the entity).
   * @param offset The current offset.
   * @returns The number of characters that were consumed, or -1 if the entity is incomplete.
   */
  stateNamedEntity(u, n) {
    const { decodeTree: r } = this;
    let i = r[this.treeIndex], t = (i & lu.VALUE_LENGTH) >> 14;
    for (; n < u.length; n++, this.excess++) {
      const o = u.charCodeAt(n);
      if (this.treeIndex = ko(r, i, this.treeIndex + Math.max(1, t), o), this.treeIndex < 0)
        return this.result === 0 || // If we are parsing an attribute
        this.decodeMode === uu.Attribute && // We shouldn't have consumed any characters after the entity,
        (t === 0 || // And there should be no invalid characters.
        Fo(o)) ? 0 : this.emitNotTerminatedNamedEntity();
      if (i = r[this.treeIndex], t = (i & lu.VALUE_LENGTH) >> 14, t !== 0) {
        if (o === Fe.SEMI)
          return this.emitNamedEntityData(this.treeIndex, t, this.consumed + this.excess);
        this.decodeMode !== uu.Strict && (this.result = this.treeIndex, this.consumed += this.excess, this.excess = 0);
      }
    }
    return -1;
  }
  /**
   * Emit a named entity that was not terminated with a semicolon.
   *
   * @returns The number of characters consumed.
   */
  emitNotTerminatedNamedEntity() {
    var u;
    const { result: n, decodeTree: r } = this, i = (r[n] & lu.VALUE_LENGTH) >> 14;
    return this.emitNamedEntityData(n, i, this.consumed), (u = this.errors) === null || u === void 0 || u.missingSemicolonAfterCharacterReference(), this.consumed;
  }
  /**
   * Emit a named entity.
   *
   * @param result The index of the entity in the decode tree.
   * @param valueLength The number of bytes in the entity.
   * @param consumed The number of characters consumed.
   *
   * @returns The number of characters consumed.
   */
  emitNamedEntityData(u, n, r) {
    const { decodeTree: i } = this;
    return this.emitCodePoint(n === 1 ? i[u] & ~lu.VALUE_LENGTH : i[u + 1], r), n === 3 && this.emitCodePoint(i[u + 2], r), r;
  }
  /**
   * Signal to the parser that the end of the input was reached.
   *
   * Remaining data will be emitted and relevant errors will be produced.
   *
   * @returns The number of characters consumed.
   */
  end() {
    var u;
    switch (this.state) {
      case Ae.NamedEntity:
        return this.result !== 0 && (this.decodeMode !== uu.Attribute || this.result === this.treeIndex) ? this.emitNotTerminatedNamedEntity() : 0;
      // Otherwise, emit a numeric entity if we have one.
      case Ae.NumericDecimal:
        return this.emitNumericEntity(0, 2);
      case Ae.NumericHex:
        return this.emitNumericEntity(0, 3);
      case Ae.NumericStart:
        return (u = this.errors) === null || u === void 0 || u.absenceOfDigitsInNumericCharacterReference(this.consumed), 0;
      case Ae.EntityStart:
        return 0;
    }
  }
}
function Vr(e) {
  let u = "";
  const n = new yo(e, (r) => u += Eo(r));
  return function(i, t) {
    let o = 0, a = 0;
    for (; (a = i.indexOf("&", a)) >= 0; ) {
      u += i.slice(o, a), n.startEntity(t);
      const l = n.write(
        i,
        // Skip the "&"
        a + 1
      );
      if (l < 0) {
        o = a + n.end();
        break;
      }
      o = a + l, a = l === 0 ? o + 1 : o;
    }
    const s = u + i.slice(o);
    return u = "", s;
  };
}
function ko(e, u, n, r) {
  const i = (u & lu.BRANCH_LENGTH) >> 7, t = u & lu.JUMP_TABLE;
  if (i === 0)
    return t !== 0 && r === t ? n : -1;
  if (t) {
    const s = r - t;
    return s < 0 || s >= i ? -1 : e[n + s] - 1;
  }
  let o = n, a = o + i - 1;
  for (; o <= a; ) {
    const s = o + a >>> 1, l = e[s];
    if (l < r)
      o = s + 1;
    else if (l > r)
      a = s - 1;
    else
      return e[s + i];
  }
  return -1;
}
const Wr = Vr(go);
Vr(mo);
function vo(e, u = uu.Legacy) {
  return Wr(e, u);
}
function wo(e) {
  return Wr(e, uu.Strict);
}
function Bo(e) {
  return Object.prototype.toString.call(e);
}
function gn(e) {
  return Bo(e) === "[object String]";
}
const So = Object.prototype.hasOwnProperty;
function To(e, u) {
  return So.call(e, u);
}
function Dt(e) {
  return Array.prototype.slice.call(arguments, 1).forEach(function(n) {
    if (n) {
      if (typeof n != "object")
        throw new TypeError(n + "must be object");
      Object.keys(n).forEach(function(r) {
        e[r] = n[r];
      });
    }
  }), e;
}
function Io(e, u, n) {
  return [].concat(e.slice(0, u), n, e.slice(u + 1));
}
function mn(e) {
  return !(e >= 55296 && e <= 57343 || e >= 64976 && e <= 65007 || (e & 65535) === 65535 || (e & 65535) === 65534 || e >= 0 && e <= 8 || e === 11 || e >= 14 && e <= 31 || e >= 127 && e <= 159 || e > 1114111);
}
function Pu(e) {
  if (e > 65535) {
    e -= 65536;
    const u = 55296 + (e >> 10), n = 56320 + (e & 1023);
    return String.fromCharCode(u, n);
  }
  return String.fromCharCode(e);
}
const Kr = /\\([!"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~])/g, Mo = /&([a-z#][a-z0-9]{1,31});/gi, Oo = new RegExp(Kr.source + "|" + Mo.source, "gi"), No = /^#((?:x[a-f0-9]{1,8}|[0-9]{1,8}))$/i;
function Ro(e, u) {
  if (u.charCodeAt(0) === 35 && No.test(u)) {
    const r = u[1].toLowerCase() === "x" ? parseInt(u.slice(2), 16) : parseInt(u.slice(1), 10);
    return mn(r) ? Pu(r) : e;
  }
  const n = vo(e);
  return n !== e ? n : e;
}
function $o(e) {
  return e.indexOf("\\") < 0 ? e : e.replace(Kr, "$1");
}
function Su(e) {
  return e.indexOf("\\") < 0 && e.indexOf("&") < 0 ? e : e.replace(Oo, function(u, n, r) {
    return n || Ro(u, r);
  });
}
const Lo = /[&<>"]/, zo = /[&<>"]/g, Po = {
  "&": "&amp;",
  "<": "&lt;",
  ">": "&gt;",
  '"': "&quot;"
};
function Ho(e) {
  return Po[e];
}
function hu(e) {
  return Lo.test(e) ? e.replace(zo, Ho) : e;
}
const jo = /[.?*+^$[\]\\(){}|-]/g;
function qo(e) {
  return e.replace(jo, "\\$&");
}
function fe(e) {
  switch (e) {
    case 9:
    case 32:
      return !0;
  }
  return !1;
}
function Hu(e) {
  if (e >= 8192 && e <= 8202)
    return !0;
  switch (e) {
    case 9:
    // \t
    case 10:
    // \n
    case 11:
    // \v
    case 12:
    // \f
    case 13:
    // \r
    case 32:
    case 160:
    case 5760:
    case 8239:
    case 8287:
    case 12288:
      return !0;
  }
  return !1;
}
function Xr(e) {
  return bn.test(e) || Zr.test(e);
}
function ju(e) {
  return Xr(Pu(e));
}
function qu(e) {
  switch (e) {
    case 33:
    case 34:
    case 35:
    case 36:
    case 37:
    case 38:
    case 39:
    case 40:
    case 41:
    case 42:
    case 43:
    case 44:
    case 45:
    case 46:
    case 47:
    case 58:
    case 59:
    case 60:
    case 61:
    case 62:
    case 63:
    case 64:
    case 91:
    case 92:
    case 93:
    case 94:
    case 95:
    case 96:
    case 123:
    case 124:
    case 125:
    case 126:
      return !0;
    default:
      return !1;
  }
}
function Et(e) {
  return e = e.trim().replace(/\s+/g, " "), "ẞ".toLowerCase() === "Ṿ" && (e = e.replace(/ẞ/g, "ß")), e.toLowerCase().toUpperCase();
}
function Yn(e) {
  return e === 32 || e === 9 || e === 10 || e === 13;
}
function xt(e) {
  let u = 0;
  for (; u < e.length && Yn(e.charCodeAt(u)); u++)
    ;
  let n = e.length - 1;
  for (; n >= u && Yn(e.charCodeAt(n)); n--)
    ;
  return e.slice(u, n + 1);
}
const Uo = { mdurl: po, ucmicro: bo }, Zo = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  arrayReplaceAt: Io,
  asciiTrim: xt,
  assign: Dt,
  escapeHtml: hu,
  escapeRE: qo,
  fromCodePoint: Pu,
  has: To,
  isMdAsciiPunct: qu,
  isPunctChar: Xr,
  isPunctCharCode: ju,
  isSpace: fe,
  isString: gn,
  isValidEntityCode: mn,
  isWhiteSpace: Hu,
  lib: Uo,
  normalizeReference: Et,
  unescapeAll: Su,
  unescapeMd: $o
}, Symbol.toStringTag, { value: "Module" }));
function Go(e, u, n) {
  let r, i, t, o;
  const a = e.posMax, s = e.pos;
  for (e.pos = u + 1, r = 1; e.pos < a; ) {
    if (t = e.src.charCodeAt(e.pos), t === 93 && (r--, r === 0)) {
      i = !0;
      break;
    }
    if (o = e.pos, e.md.inline.skipToken(e), t === 91) {
      if (o === e.pos - 1)
        r++;
      else if (n)
        return e.pos = s, -1;
    }
  }
  let l = -1;
  return i && (l = e.pos), e.pos = s, l;
}
function Vo(e, u, n) {
  let r, i = u;
  const t = {
    ok: !1,
    pos: 0,
    str: ""
  };
  if (e.charCodeAt(i) === 60) {
    for (i++; i < n; ) {
      if (r = e.charCodeAt(i), r === 10 || r === 60)
        return t;
      if (r === 62)
        return t.pos = i + 1, t.str = Su(e.slice(u + 1, i)), t.ok = !0, t;
      if (r === 92 && i + 1 < n) {
        i += 2;
        continue;
      }
      i++;
    }
    return t;
  }
  let o = 0;
  for (; i < n && (r = e.charCodeAt(i), !(r === 32 || r < 32 || r === 127)); ) {
    if (r === 92 && i + 1 < n) {
      if (e.charCodeAt(i + 1) === 32) {
        i++;
        continue;
      }
      i += 2;
      continue;
    }
    if (r === 40 && (o++, o > 32))
      return t;
    if (r === 41) {
      if (o === 0)
        break;
      o--;
    }
    i++;
  }
  return u === i || o !== 0 || (t.str = Su(e.slice(u, i)), t.pos = i, t.ok = !0), t;
}
function Wo(e, u, n, r) {
  let i, t = u;
  const o = {
    // if `true`, this is a valid link title
    ok: !1,
    // if `true`, this link can be continued on the next line
    can_continue: !1,
    // if `ok`, it's the position of the first character after the closing marker
    pos: 0,
    // if `ok`, it's the unescaped title
    str: "",
    // expected closing marker character code
    marker: 0
  };
  if (r)
    o.str = r.str, o.marker = r.marker;
  else {
    if (t >= n)
      return o;
    let a = e.charCodeAt(t);
    if (a !== 34 && a !== 39 && a !== 40)
      return o;
    u++, t++, a === 40 && (a = 41), o.marker = a;
  }
  for (; t < n; ) {
    if (i = e.charCodeAt(t), i === o.marker)
      return o.pos = t + 1, o.str += Su(e.slice(u, t)), o.ok = !0, o;
    if (i === 40 && o.marker === 41)
      return o;
    i === 92 && t + 1 < n && t++, t++;
  }
  return o.can_continue = !0, o.str += Su(e.slice(u, t)), o;
}
const Ko = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  parseLinkDestination: Vo,
  parseLinkLabel: Go,
  parseLinkTitle: Wo
}, Symbol.toStringTag, { value: "Module" })), Ve = {};
Ve.code_inline = function(e, u, n, r, i) {
  const t = e[u];
  return "<code" + i.renderAttrs(t) + ">" + hu(t.content) + "</code>";
};
Ve.code_block = function(e, u, n, r, i) {
  const t = e[u];
  return "<pre" + i.renderAttrs(t) + "><code>" + hu(e[u].content) + `</code></pre>
`;
};
Ve.fence = function(e, u, n, r, i) {
  const t = e[u], o = t.info ? Su(t.info).trim() : "";
  let a = "", s = "";
  if (o) {
    const f = o.split(/(\s+)/g);
    a = f[0], s = f.slice(2).join("");
  }
  let l;
  if (n.highlight ? l = n.highlight(t.content, a, s) || hu(t.content) : l = hu(t.content), l.indexOf("<pre") === 0)
    return l + `
`;
  if (o) {
    const f = t.attrIndex("class"), c = t.attrs ? t.attrs.slice() : [];
    f < 0 ? c.push(["class", n.langPrefix + a]) : (c[f] = c[f].slice(), c[f][1] += " " + n.langPrefix + a);
    const h = {
      attrs: c
    };
    return `<pre><code${i.renderAttrs(h)}>${l}</code></pre>
`;
  }
  return `<pre><code${i.renderAttrs(t)}>${l}</code></pre>
`;
};
Ve.image = function(e, u, n, r, i) {
  const t = e[u];
  return t.attrs[t.attrIndex("alt")][1] = i.renderInlineAsText(t.children, n, r), i.renderToken(e, u, n);
};
Ve.hardbreak = function(e, u, n) {
  return n.xhtmlOut ? `<br />
` : `<br>
`;
};
Ve.softbreak = function(e, u, n) {
  return n.breaks ? n.xhtmlOut ? `<br />
` : `<br>
` : `
`;
};
Ve.text = function(e, u) {
  return hu(e[u].content);
};
Ve.html_block = function(e, u) {
  return e[u].content;
};
Ve.html_inline = function(e, u) {
  return e[u].content;
};
function Tu() {
  this.rules = Dt({}, Ve);
}
Tu.prototype.renderAttrs = function(u) {
  let n, r, i;
  if (!u.attrs)
    return "";
  for (i = "", n = 0, r = u.attrs.length; n < r; n++)
    i += " " + hu(u.attrs[n][0]) + '="' + hu(u.attrs[n][1]) + '"';
  return i;
};
Tu.prototype.renderToken = function(u, n, r) {
  const i = u[n];
  let t = "";
  if (i.hidden)
    return "";
  i.block && i.nesting !== -1 && n && u[n - 1].hidden && (t += `
`), t += (i.nesting === -1 ? "</" : "<") + i.tag, t += this.renderAttrs(i), i.nesting === 0 && r.xhtmlOut && (t += " /");
  let o = !1;
  if (i.block && (o = !0, i.nesting === 1 && n + 1 < u.length)) {
    const a = u[n + 1];
    (a.type === "inline" || a.hidden || a.nesting === -1 && a.tag === i.tag) && (o = !1);
  }
  return t += o ? `>
` : ">", t;
};
Tu.prototype.renderInline = function(e, u, n) {
  let r = "";
  const i = this.rules;
  for (let t = 0, o = e.length; t < o; t++) {
    const a = e[t].type;
    typeof i[a] < "u" ? r += i[a](e, t, u, n, this) : r += this.renderToken(e, t, u);
  }
  return r;
};
Tu.prototype.renderInlineAsText = function(e, u, n) {
  let r = "";
  for (let i = 0, t = e.length; i < t; i++)
    switch (e[i].type) {
      case "text":
        r += e[i].content;
        break;
      case "image":
        r += this.renderInlineAsText(e[i].children, u, n);
        break;
      case "html_inline":
      case "html_block":
        r += e[i].content;
        break;
      case "softbreak":
      case "hardbreak":
        r += `
`;
        break;
    }
  return r;
};
Tu.prototype.render = function(e, u, n) {
  let r = "";
  const i = this.rules;
  for (let t = 0, o = e.length; t < o; t++) {
    const a = e[t].type;
    a === "inline" ? r += this.renderInline(e[t].children, u, n) : typeof i[a] < "u" ? r += i[a](e, t, u, n, this) : r += this.renderToken(e, t, u, n);
  }
  return r;
};
function Se() {
  this.__rules__ = [], this.__cache__ = null;
}
Se.prototype.__find__ = function(e) {
  for (let u = 0; u < this.__rules__.length; u++)
    if (this.__rules__[u].name === e)
      return u;
  return -1;
};
Se.prototype.__compile__ = function() {
  const e = this, u = [""];
  e.__rules__.forEach(function(n) {
    n.enabled && n.alt.forEach(function(r) {
      u.indexOf(r) < 0 && u.push(r);
    });
  }), e.__cache__ = {}, u.forEach(function(n) {
    e.__cache__[n] = [], e.__rules__.forEach(function(r) {
      r.enabled && (n && r.alt.indexOf(n) < 0 || e.__cache__[n].push(r.fn));
    });
  });
};
Se.prototype.at = function(e, u, n) {
  const r = this.__find__(e), i = n || {};
  if (r === -1)
    throw new Error("Parser rule not found: " + e);
  this.__rules__[r].fn = u, this.__rules__[r].alt = i.alt || [], this.__cache__ = null;
};
Se.prototype.before = function(e, u, n, r) {
  const i = this.__find__(e), t = r || {};
  if (i === -1)
    throw new Error("Parser rule not found: " + e);
  this.__rules__.splice(i, 0, {
    name: u,
    enabled: !0,
    fn: n,
    alt: t.alt || []
  }), this.__cache__ = null;
};
Se.prototype.after = function(e, u, n, r) {
  const i = this.__find__(e), t = r || {};
  if (i === -1)
    throw new Error("Parser rule not found: " + e);
  this.__rules__.splice(i + 1, 0, {
    name: u,
    enabled: !0,
    fn: n,
    alt: t.alt || []
  }), this.__cache__ = null;
};
Se.prototype.push = function(e, u, n) {
  const r = n || {};
  this.__rules__.push({
    name: e,
    enabled: !0,
    fn: u,
    alt: r.alt || []
  }), this.__cache__ = null;
};
Se.prototype.enable = function(e, u) {
  Array.isArray(e) || (e = [e]);
  const n = [];
  return e.forEach(function(r) {
    const i = this.__find__(r);
    if (i < 0) {
      if (u)
        return;
      throw new Error("Rules manager: invalid rule name " + r);
    }
    this.__rules__[i].enabled = !0, n.push(r);
  }, this), this.__cache__ = null, n;
};
Se.prototype.enableOnly = function(e, u) {
  Array.isArray(e) || (e = [e]), this.__rules__.forEach(function(n) {
    n.enabled = !1;
  }), this.enable(e, u);
};
Se.prototype.disable = function(e, u) {
  Array.isArray(e) || (e = [e]);
  const n = [];
  return e.forEach(function(r) {
    const i = this.__find__(r);
    if (i < 0) {
      if (u)
        return;
      throw new Error("Rules manager: invalid rule name " + r);
    }
    this.__rules__[i].enabled = !1, n.push(r);
  }, this), this.__cache__ = null, n;
};
Se.prototype.getRules = function(e) {
  return this.__cache__ === null && this.__compile__(), this.__cache__[e] || [];
};
function Pe(e, u, n) {
  this.type = e, this.tag = u, this.attrs = null, this.map = null, this.nesting = n, this.level = 0, this.children = null, this.content = "", this.markup = "", this.info = "", this.meta = null, this.block = !1, this.hidden = !1;
}
Pe.prototype.attrIndex = function(u) {
  if (!this.attrs)
    return -1;
  const n = this.attrs;
  for (let r = 0, i = n.length; r < i; r++)
    if (n[r][0] === u)
      return r;
  return -1;
};
Pe.prototype.attrPush = function(u) {
  this.attrs ? this.attrs.push(u) : this.attrs = [u];
};
Pe.prototype.attrSet = function(u, n) {
  const r = this.attrIndex(u), i = [u, n];
  r < 0 ? this.attrPush(i) : this.attrs[r] = i;
};
Pe.prototype.attrGet = function(u) {
  const n = this.attrIndex(u);
  let r = null;
  return n >= 0 && (r = this.attrs[n][1]), r;
};
Pe.prototype.attrJoin = function(u, n) {
  const r = this.attrIndex(u);
  r < 0 ? this.attrPush([u, n]) : this.attrs[r][1] = this.attrs[r][1] + " " + n;
};
function Jr(e, u, n) {
  this.src = e, this.env = n, this.tokens = [], this.inlineMode = !1, this.md = u;
}
Jr.prototype.Token = Pe;
const Xo = /\r\n?|\n/g, Jo = /\0/g;
function Qo(e) {
  let u;
  u = e.src.replace(Xo, `
`), u = u.replace(Jo, "�"), e.src = u;
}
function Yo(e) {
  let u;
  e.inlineMode ? (u = new e.Token("inline", "", 0), u.content = e.src, u.map = [0, 1], u.children = [], e.tokens.push(u)) : e.md.block.parse(e.src, e.md, e.env, e.tokens);
}
function ea(e) {
  const u = e.tokens;
  for (let n = 0, r = u.length; n < r; n++) {
    const i = u[n];
    i.type === "inline" && e.md.inline.parse(i.content, e.md, e.env, i.children);
  }
}
function ua(e) {
  return /^<a[>\s]/i.test(e);
}
function ta(e) {
  return /^<\/a\s*>/i.test(e);
}
function na(e) {
  const u = e.tokens;
  if (e.md.options.linkify)
    for (let n = 0, r = u.length; n < r; n++) {
      if (u[n].type !== "inline" || !e.md.linkify.pretest(u[n].content))
        continue;
      const i = u[n].children, t = [];
      let o = 0;
      for (let a = i.length - 1; a >= 0; a--) {
        const s = i[a];
        if (s.type === "link_close") {
          for (a--; i[a].level !== s.level && i[a].type !== "link_open"; )
            a--;
          continue;
        }
        if (s.type === "html_inline" && (ua(s.content) && o > 0 && o--, ta(s.content) && o++), !(o > 0) && s.type === "text" && e.md.linkify.test(s.content)) {
          const l = s.content;
          let f = e.md.linkify.match(l);
          const c = [];
          let h = s.level, p = 0;
          f.length > 0 && f[0].index === 0 && a > 0 && i[a - 1].type === "text_special" && (f = f.slice(1));
          for (let d = 0; d < f.length; d++) {
            const x = f[d].url, g = e.md.normalizeLink(x);
            if (!e.md.validateLink(g))
              continue;
            let _ = f[d].text;
            f[d].schema ? f[d].schema === "mailto:" && !/^mailto:/i.test(_) ? _ = e.md.normalizeLinkText("mailto:" + _).replace(/^mailto:/, "") : _ = e.md.normalizeLinkText(_) : _ = e.md.normalizeLinkText("http://" + _).replace(/^http:\/\//, "");
            const E = f[d].index;
            if (E > p) {
              const k = new e.Token("text", "", 0);
              k.content = l.slice(p, E), k.level = h, c.push(k);
            }
            const D = new e.Token("link_open", "a", 1);
            D.attrs = [["href", g]], D.level = h++, D.markup = "linkify", D.info = "auto", c.push(D);
            const m = new e.Token("text", "", 0);
            m.content = _, m.level = h, c.push(m);
            const A = new e.Token("link_close", "a", -1);
            A.level = --h, A.markup = "linkify", A.info = "auto", c.push(A), p = f[d].lastIndex;
          }
          if (p < l.length) {
            const d = new e.Token("text", "", 0);
            d.content = l.slice(p), d.level = h, c.push(d);
          }
          t.push({ index: a, nodes: c });
        }
      }
      if (t.length > 0) {
        let a = i.length;
        for (const c of t)
          a += c.nodes.length - 1;
        const s = new Array(a);
        let l = 0, f = 0;
        t.reverse();
        for (let c = 0; c < i.length; c++) {
          const h = t[l];
          if (h?.index === c) {
            for (const p of h.nodes)
              s[f++] = p;
            l++;
          } else
            s[f++] = i[c];
        }
        u[n].children = s;
      }
    }
}
const Qr = /\+-|\.\.|\?\?\?\?|!!!!|,,|--/, ra = /\((c|tm|r)\)/i, ia = /\((c|tm|r)\)/ig, oa = {
  c: "©",
  r: "®",
  tm: "™"
};
function aa(e, u) {
  return oa[u.toLowerCase()];
}
function sa(e) {
  let u = 0;
  for (let n = e.length - 1; n >= 0; n--) {
    const r = e[n];
    r.type === "text" && !u && (r.content = r.content.replace(ia, aa)), r.type === "link_open" && r.info === "auto" && u--, r.type === "link_close" && r.info === "auto" && u++;
  }
}
function ca(e) {
  let u = 0;
  for (let n = e.length - 1; n >= 0; n--) {
    const r = e[n];
    r.type === "text" && !u && Qr.test(r.content) && (r.content = r.content.replace(/\+-/g, "±").replace(/\.{2,}/g, "…").replace(/([?!])…/g, "$1..").replace(/([?!]){4,}/g, "$1$1$1").replace(/,{2,}/g, ",").replace(/(^|[^-])---(?=[^-]|$)/mg, "$1—").replace(/(^|\s)--(?=\s|$)/mg, "$1–").replace(/(^|[^-\s])--(?=[^-\s]|$)/mg, "$1–")), r.type === "link_open" && r.info === "auto" && u--, r.type === "link_close" && r.info === "auto" && u++;
  }
}
function la(e) {
  let u;
  if (e.md.options.typographer)
    for (u = e.tokens.length - 1; u >= 0; u--)
      e.tokens[u].type === "inline" && (ra.test(e.tokens[u].content) && sa(e.tokens[u].children), Qr.test(e.tokens[u].content) && ca(e.tokens[u].children));
}
const fa = /['"]/, er = /['"]/g, ur = "’", da = 1e3;
function tr(e, u, n) {
  for (; e.length > n; ) {
    const r = e.pop();
    r.isSingleQuote ? u.single = r.prevSameQuoteIdx : u.double = r.prevSameQuoteIdx;
  }
}
function nt(e, u, n, r) {
  e[u] || (e[u] = []), e[u].push({ pos: n, ch: r });
}
function pa(e, u) {
  let n = "", r = 0;
  u.sort((i, t) => i.pos - t.pos);
  for (let i = 0; i < u.length; i++) {
    const t = u[i];
    n += e.slice(r, t.pos) + t.ch, r = t.pos + 1;
  }
  return n + e.slice(r);
}
function ha(e, u) {
  let n;
  const r = [], i = { single: -1, double: -1 }, t = {};
  for (let o = 0; o < e.length; o++) {
    const a = e[o], s = e[o].level;
    for (n = r.length - 1; n >= 0 && !(r[n].level <= s); n--)
      ;
    if (tr(r, i, n + 1), a.type !== "text")
      continue;
    const l = a.content;
    let f = 0;
    const c = l.length;
    e:
      for (; f < c; ) {
        er.lastIndex = f;
        const h = er.exec(l);
        if (!h)
          break;
        let p = !0, d = !0;
        f = h.index + 1;
        const x = h[0] === "'";
        let g = 32;
        if (h.index - 1 >= 0)
          g = l.charCodeAt(h.index - 1);
        else
          for (n = o - 1; n >= 0 && !(e[n].type === "softbreak" || e[n].type === "hardbreak"); n--)
            if (e[n].content) {
              g = e[n].content.charCodeAt(e[n].content.length - 1);
              break;
            }
        let _ = 32;
        if (f < c)
          _ = l.charCodeAt(f);
        else
          for (n = o + 1; n < e.length && !(e[n].type === "softbreak" || e[n].type === "hardbreak"); n++)
            if (e[n].content) {
              _ = e[n].content.charCodeAt(0);
              break;
            }
        const E = qu(g) || ju(g), D = qu(_) || ju(_), m = Hu(g), A = Hu(_);
        if (A ? p = !1 : D && (m || E || (p = !1)), m ? d = !1 : E && (A || D || (d = !1)), _ === 34 && h[0] === '"' && g >= 48 && g <= 57 && (d = p = !1), p && d && (p = E, d = D), !p && !d) {
          x && nt(t, o, h.index, ur);
          continue;
        }
        if (d && (n = x ? i.single : i.double, n >= 0 && r[n].level === s)) {
          const k = r[n];
          let B, C;
          x ? (B = u.md.options.quotes[2], C = u.md.options.quotes[3]) : (B = u.md.options.quotes[0], C = u.md.options.quotes[1]), nt(t, o, h.index, C), nt(t, k.tokenIdx, k.contentPos, B), tr(r, i, n);
          continue e;
        }
        if (p) {
          if (r.length >= da)
            return;
          r.push({
            tokenIdx: o,
            contentPos: h.index,
            isSingleQuote: x,
            level: s,
            // stack index of the previous opener of the same quote type, -1 if none
            prevSameQuoteIdx: x ? i.single : i.double
          }), x ? i.single = r.length - 1 : i.double = r.length - 1;
        } else d && x && nt(t, o, h.index, ur);
      }
  }
  Object.keys(t).forEach(function(o) {
    e[o].content = pa(e[o].content, t[o]);
  });
}
function ba(e) {
  if (e.md.options.typographer)
    for (let u = e.tokens.length - 1; u >= 0; u--)
      e.tokens[u].type !== "inline" || !fa.test(e.tokens[u].content) || ha(e.tokens[u].children, e);
}
function ga(e) {
  let u, n;
  const r = e.tokens, i = r.length;
  for (let t = 0; t < i; t++) {
    if (r[t].type !== "inline") continue;
    const o = r[t].children, a = o.length;
    for (u = 0; u < a; u++)
      o[u].type === "text_special" && (o[u].type = "text");
    for (u = n = 0; u < a; u++)
      o[u].type === "text" && u + 1 < a && o[u + 1].type === "text" ? o[u + 1].content = o[u].content + o[u + 1].content : (u !== n && (o[n] = o[u]), n++);
    u !== n && (o.length = n);
  }
}
const Lt = [
  ["normalize", Qo],
  ["block", Yo],
  ["inline", ea],
  ["linkify", na],
  ["replacements", la],
  ["smartquotes", ba],
  // `text_join` finds `text_special` tokens (for escape sequences)
  // and joins them with the rest of the text
  ["text_join", ga]
];
function Dn() {
  this.ruler = new Se();
  for (let e = 0; e < Lt.length; e++)
    this.ruler.push(Lt[e][0], Lt[e][1]);
}
Dn.prototype.process = function(e) {
  const u = this.ruler.getRules("");
  for (let n = 0, r = u.length; n < r; n++)
    u[n](e);
};
Dn.prototype.State = Jr;
function We(e, u, n, r) {
  this.src = e, this.md = u, this.env = n, this.tokens = r, this.bMarks = [], this.eMarks = [], this.tShift = [], this.sCount = [], this.bsCount = [], this.blkIndent = 0, this.line = 0, this.lineMax = 0, this.tight = !1, this.ddIndent = -1, this.listIndent = -1, this.parentType = "root", this.level = 0;
  const i = this.src;
  for (let t = 0, o = 0, a = 0, s = 0, l = i.length, f = !1; o < l; o++) {
    const c = i.charCodeAt(o);
    if (!f)
      if (fe(c)) {
        a++, c === 9 ? s += 4 - s % 4 : s++;
        continue;
      } else
        f = !0;
    (c === 10 || o === l - 1) && (c !== 10 && o++, this.bMarks.push(t), this.eMarks.push(o), this.tShift.push(a), this.sCount.push(s), this.bsCount.push(0), f = !1, a = 0, s = 0, t = o + 1);
  }
  this.bMarks.push(i.length), this.eMarks.push(i.length), this.tShift.push(0), this.sCount.push(0), this.bsCount.push(0), this.lineMax = this.bMarks.length - 1;
}
We.prototype.push = function(e, u, n) {
  const r = new Pe(e, u, n);
  return r.block = !0, n < 0 && this.level--, r.level = this.level, n > 0 && this.level++, this.tokens.push(r), r;
};
We.prototype.isEmpty = function(u) {
  return this.bMarks[u] + this.tShift[u] >= this.eMarks[u];
};
We.prototype.skipEmptyLines = function(u) {
  for (let n = this.lineMax; u < n && !(this.bMarks[u] + this.tShift[u] < this.eMarks[u]); u++)
    ;
  return u;
};
We.prototype.skipSpaces = function(u) {
  for (let n = this.src.length; u < n; u++) {
    const r = this.src.charCodeAt(u);
    if (!fe(r))
      break;
  }
  return u;
};
We.prototype.skipSpacesBack = function(u, n) {
  if (u <= n)
    return u;
  for (; u > n; )
    if (!fe(this.src.charCodeAt(--u)))
      return u + 1;
  return u;
};
We.prototype.skipChars = function(u, n) {
  for (let r = this.src.length; u < r && this.src.charCodeAt(u) === n; u++)
    ;
  return u;
};
We.prototype.skipCharsBack = function(u, n, r) {
  if (u <= r)
    return u;
  for (; u > r; )
    if (n !== this.src.charCodeAt(--u))
      return u + 1;
  return u;
};
We.prototype.getLines = function(u, n, r, i) {
  if (u >= n)
    return "";
  const t = new Array(n - u);
  for (let o = 0, a = u; a < n; a++, o++) {
    let s = 0;
    const l = this.bMarks[a];
    let f = l, c;
    for (a + 1 < n || i ? c = this.eMarks[a] + 1 : c = this.eMarks[a]; f < c && s < r; ) {
      const h = this.src.charCodeAt(f);
      if (fe(h))
        h === 9 ? s += 4 - (s + this.bsCount[a]) % 4 : s++;
      else if (f - l < this.tShift[a])
        s++;
      else
        break;
      f++;
    }
    s > r ? t[o] = new Array(s - r + 1).join(" ") + this.src.slice(f, c) : t[o] = this.src.slice(f, c);
  }
  return t.join("");
};
We.prototype.Token = Pe;
const ma = 65536;
function zt(e, u) {
  const n = e.bMarks[u] + e.tShift[u], r = e.eMarks[u];
  return e.src.slice(n, r);
}
function nr(e) {
  const u = [], n = e.length;
  let r = 0, i = e.charCodeAt(r), t = !1, o = 0, a = "";
  for (; r < n; )
    i === 124 && (t ? (a += e.substring(o, r - 1), o = r) : (u.push(a + e.substring(o, r)), a = "", o = r + 1)), t = i === 92, r++, i = e.charCodeAt(r);
  return u.push(a + e.substring(o)), u;
}
function Da(e, u, n, r) {
  if (u + 2 > n)
    return !1;
  let i = u + 1;
  if (e.sCount[i] < e.blkIndent || e.sCount[i] - e.blkIndent >= 4)
    return !1;
  let t = e.bMarks[i] + e.tShift[i];
  if (t >= e.eMarks[i])
    return !1;
  const o = e.src.charCodeAt(t++);
  if (o !== 124 && o !== 45 && o !== 58 || t >= e.eMarks[i])
    return !1;
  const a = e.src.charCodeAt(t++);
  if (a !== 124 && a !== 45 && a !== 58 && !fe(a) || o === 45 && fe(a))
    return !1;
  for (; t < e.eMarks[i]; ) {
    const m = e.src.charCodeAt(t);
    if (m !== 124 && m !== 45 && m !== 58 && !fe(m))
      return !1;
    t++;
  }
  let s = zt(e, u + 1), l = s.split("|");
  const f = [];
  for (let m = 0; m < l.length; m++) {
    const A = l[m].trim();
    if (!A) {
      if (m === 0 || m === l.length - 1)
        continue;
      return !1;
    }
    if (!/^:?-+:?$/.test(A))
      return !1;
    A.charCodeAt(A.length - 1) === 58 ? f.push(A.charCodeAt(0) === 58 ? "center" : "right") : A.charCodeAt(0) === 58 ? f.push("left") : f.push("");
  }
  if (s = zt(e, u).trim(), s.indexOf("|") === -1 || e.sCount[u] - e.blkIndent >= 4)
    return !1;
  l = nr(s), l.length && l[0] === "" && l.shift(), l.length && l[l.length - 1] === "" && l.pop();
  const c = l.length;
  if (c === 0 || c !== f.length)
    return !1;
  if (r)
    return !0;
  const h = e.parentType;
  e.parentType = "table";
  const p = e.md.block.ruler.getRules("blockquote"), d = e.push("table_open", "table", 1), x = [u, 0];
  d.map = x;
  const g = e.push("thead_open", "thead", 1);
  g.map = [u, u + 1];
  const _ = e.push("tr_open", "tr", 1);
  _.map = [u, u + 1];
  for (let m = 0; m < l.length; m++) {
    const A = e.push("th_open", "th", 1);
    f[m] && (A.attrs = [["style", "text-align:" + f[m]]]);
    const k = e.push("inline", "", 0);
    k.content = l[m].trim(), k.children = [], e.push("th_close", "th", -1);
  }
  e.push("tr_close", "tr", -1), e.push("thead_close", "thead", -1);
  let E, D = 0;
  for (i = u + 2; i < n && !(e.sCount[i] < e.blkIndent); i++) {
    let m = !1;
    for (let k = 0, B = p.length; k < B; k++)
      if (p[k](e, i, n, !0)) {
        m = !0;
        break;
      }
    if (m || (s = zt(e, i).trim(), !s) || e.sCount[i] - e.blkIndent >= 4 || (l = nr(s), l.length && l[0] === "" && l.shift(), l.length && l[l.length - 1] === "" && l.pop(), D += c - l.length, D > ma))
      break;
    if (i === u + 2) {
      const k = e.push("tbody_open", "tbody", 1);
      k.map = E = [u + 2, 0];
    }
    const A = e.push("tr_open", "tr", 1);
    A.map = [i, i + 1];
    for (let k = 0; k < c; k++) {
      const B = e.push("td_open", "td", 1);
      f[k] && (B.attrs = [["style", "text-align:" + f[k]]]);
      const C = e.push("inline", "", 0);
      C.content = l[k] ? l[k].trim() : "", C.children = [], e.push("td_close", "td", -1);
    }
    e.push("tr_close", "tr", -1);
  }
  return E && (e.push("tbody_close", "tbody", -1), E[1] = i), e.push("table_close", "table", -1), x[1] = i, e.parentType = h, e.line = i, !0;
}
function Ea(e, u, n) {
  if (e.sCount[u] - e.blkIndent < 4)
    return !1;
  let r = u + 1, i = r;
  for (; r < n; ) {
    if (e.isEmpty(r)) {
      r++;
      continue;
    }
    if (e.sCount[r] - e.blkIndent >= 4) {
      r++, i = r;
      continue;
    }
    break;
  }
  e.line = i;
  const t = e.push("code_block", "code", 0);
  return t.content = e.getLines(u, i, 4 + e.blkIndent, !1) + `
`, t.map = [u, e.line], !0;
}
function xa(e, u, n, r) {
  let i = e.bMarks[u] + e.tShift[u], t = e.eMarks[u];
  if (e.sCount[u] - e.blkIndent >= 4 || i + 3 > t)
    return !1;
  const o = e.src.charCodeAt(i);
  if (o !== 126 && o !== 96)
    return !1;
  let a = i;
  i = e.skipChars(i, o);
  let s = i - a;
  if (s < 3)
    return !1;
  const l = e.src.slice(a, i), f = e.src.slice(i, t);
  if (o === 96 && f.indexOf(String.fromCharCode(o)) >= 0)
    return !1;
  if (r)
    return !0;
  let c = u, h = !1;
  for (; c++, !(c >= n || (i = a = e.bMarks[c] + e.tShift[c], t = e.eMarks[c], i < t && e.sCount[c] < e.blkIndent)); )
    if (e.src.charCodeAt(i) === o && !(e.sCount[c] - e.blkIndent >= 4) && (i = e.skipChars(i, o), !(i - a < s) && (i = e.skipSpaces(i), !(i < t)))) {
      h = !0;
      break;
    }
  s = e.sCount[u], e.line = c + (h ? 1 : 0);
  const p = e.push("fence", "code", 0);
  return p.info = f, p.content = e.getLines(u + 1, c, s, !0), p.markup = l, p.map = [u, e.line], !0;
}
function Ca(e, u, n, r) {
  let i = e.bMarks[u] + e.tShift[u], t = e.eMarks[u];
  const o = e.lineMax;
  if (e.sCount[u] - e.blkIndent >= 4 || e.src.charCodeAt(i) !== 62)
    return !1;
  if (r)
    return !0;
  const a = [], s = [], l = [], f = [], c = e.md.block.ruler.getRules("blockquote"), h = e.parentType;
  e.parentType = "blockquote";
  let p = !1, d;
  for (d = u; d < n; d++) {
    const D = e.sCount[d] < e.blkIndent;
    if (i = e.bMarks[d] + e.tShift[d], t = e.eMarks[d], i >= t)
      break;
    if (e.src.charCodeAt(i++) === 62 && !D) {
      let A = e.sCount[d] + 1, k, B;
      e.src.charCodeAt(i) === 32 ? (i++, A++, B = !1, k = !0) : e.src.charCodeAt(i) === 9 ? (k = !0, (e.bsCount[d] + A) % 4 === 3 ? (i++, A++, B = !1) : B = !0) : k = !1;
      let C = A;
      for (a.push(e.bMarks[d]), e.bMarks[d] = i; i < t; ) {
        const U = e.src.charCodeAt(i);
        if (fe(U))
          U === 9 ? C += 4 - (C + e.bsCount[d] + (B ? 1 : 0)) % 4 : C++;
        else
          break;
        i++;
      }
      p = i >= t, s.push(e.bsCount[d]), e.bsCount[d] = e.sCount[d] + 1 + (k ? 1 : 0), l.push(e.sCount[d]), e.sCount[d] = C - A, f.push(e.tShift[d]), e.tShift[d] = i - e.bMarks[d];
      continue;
    }
    if (p)
      break;
    let m = !1;
    for (let A = 0, k = c.length; A < k; A++)
      if (c[A](e, d, n, !0)) {
        m = !0;
        break;
      }
    if (m) {
      e.lineMax = d, e.blkIndent !== 0 && (a.push(e.bMarks[d]), s.push(e.bsCount[d]), f.push(e.tShift[d]), l.push(e.sCount[d]), e.sCount[d] -= e.blkIndent);
      break;
    }
    a.push(e.bMarks[d]), s.push(e.bsCount[d]), f.push(e.tShift[d]), l.push(e.sCount[d]), e.sCount[d] = -1;
  }
  const x = e.blkIndent;
  e.blkIndent = 0;
  const g = e.push("blockquote_open", "blockquote", 1);
  g.markup = ">";
  const _ = [u, 0];
  g.map = _, e.md.block.tokenize(e, u, d);
  const E = e.push("blockquote_close", "blockquote", -1);
  E.markup = ">", e.lineMax = o, e.parentType = h, _[1] = e.line;
  for (let D = 0; D < f.length; D++)
    e.bMarks[D + u] = a[D], e.tShift[D + u] = f[D], e.sCount[D + u] = l[D], e.bsCount[D + u] = s[D];
  return e.blkIndent = x, !0;
}
function Aa(e, u, n, r) {
  const i = e.eMarks[u];
  if (e.sCount[u] - e.blkIndent >= 4)
    return !1;
  let t = e.bMarks[u] + e.tShift[u];
  const o = e.src.charCodeAt(t++);
  if (o !== 42 && o !== 45 && o !== 95)
    return !1;
  let a = 1;
  for (; t < i; ) {
    const l = e.src.charCodeAt(t++);
    if (l !== o && !fe(l))
      return !1;
    l === o && a++;
  }
  if (a < 3)
    return !1;
  if (r)
    return !0;
  e.line = u + 1;
  const s = e.push("hr", "hr", 0);
  return s.map = [u, e.line], s.markup = Array(a + 1).join(String.fromCharCode(o)), !0;
}
function rr(e, u) {
  const n = e.eMarks[u];
  let r = e.bMarks[u] + e.tShift[u];
  const i = e.src.charCodeAt(r++);
  if (i !== 42 && i !== 45 && i !== 43)
    return -1;
  if (r < n) {
    const t = e.src.charCodeAt(r);
    if (!fe(t))
      return -1;
  }
  return r;
}
function ir(e, u) {
  const n = e.bMarks[u] + e.tShift[u], r = e.eMarks[u];
  let i = n;
  if (i + 1 >= r)
    return -1;
  let t = e.src.charCodeAt(i++);
  if (t < 48 || t > 57)
    return -1;
  for (; ; ) {
    if (i >= r)
      return -1;
    if (t = e.src.charCodeAt(i++), t >= 48 && t <= 57) {
      if (i - n >= 10)
        return -1;
      continue;
    }
    if (t === 41 || t === 46)
      break;
    return -1;
  }
  return i < r && (t = e.src.charCodeAt(i), !fe(t)) ? -1 : i;
}
function _a(e, u) {
  const n = e.level + 2;
  for (let r = u + 2, i = e.tokens.length - 2; r < i; r++)
    e.tokens[r].level === n && e.tokens[r].type === "paragraph_open" && (e.tokens[r + 2].hidden = !0, e.tokens[r].hidden = !0, r += 2);
}
function Fa(e, u, n, r) {
  let i, t, o, a, s = u, l = !0;
  if (e.sCount[s] - e.blkIndent >= 4 || e.listIndent >= 0 && e.sCount[s] - e.listIndent >= 4 && e.sCount[s] < e.blkIndent)
    return !1;
  let f = !1;
  r && e.parentType === "paragraph" && e.sCount[s] >= e.blkIndent && (f = !0);
  let c, h, p;
  if ((p = ir(e, s)) >= 0) {
    if (c = !0, o = e.bMarks[s] + e.tShift[s], h = Number(e.src.slice(o, p - 1)), f && h !== 1) return !1;
  } else if ((p = rr(e, s)) >= 0)
    c = !1;
  else
    return !1;
  if (f && e.skipSpaces(p) >= e.eMarks[s])
    return !1;
  if (r)
    return !0;
  const d = e.src.charCodeAt(p - 1), x = e.tokens.length;
  c ? (a = e.push("ordered_list_open", "ol", 1), h !== 1 && (a.attrs = [["start", h]])) : a = e.push("bullet_list_open", "ul", 1);
  const g = [s, 0];
  a.map = g, a.markup = String.fromCharCode(d);
  let _ = !1;
  const E = e.md.block.ruler.getRules("list"), D = e.parentType;
  for (e.parentType = "list"; s < n; ) {
    t = p, i = e.eMarks[s];
    const m = e.sCount[s] + p - (e.bMarks[s] + e.tShift[s]);
    let A = m;
    for (; t < i; ) {
      const N = e.src.charCodeAt(t);
      if (N === 9)
        A += 4 - (A + e.bsCount[s]) % 4;
      else if (N === 32)
        A++;
      else
        break;
      t++;
    }
    const k = t;
    let B;
    k >= i ? B = 1 : B = A - m, B > 4 && (B = 1);
    const C = m + B;
    a = e.push("list_item_open", "li", 1), a.markup = String.fromCharCode(d);
    const U = [s, 0];
    a.map = U, c && (a.info = e.src.slice(o, p - 1));
    const Z = e.tight, H = e.tShift[s], O = e.sCount[s], z = e.listIndent;
    if (e.listIndent = e.blkIndent, e.blkIndent = C, e.tight = !0, e.tShift[s] = k - e.bMarks[s], e.sCount[s] = A, k >= i && e.isEmpty(s + 1) ? e.line = Math.min(e.line + 2, n) : e.md.block.tokenize(e, s, n, !0), (!e.tight || _) && (l = !1), _ = e.line - s > 1 && e.isEmpty(e.line - 1), e.blkIndent = e.listIndent, e.listIndent = z, e.tShift[s] = H, e.sCount[s] = O, e.tight = Z, a = e.push("list_item_close", "li", -1), a.markup = String.fromCharCode(d), s = e.line, U[1] = s, s >= n || e.sCount[s] < e.blkIndent || e.sCount[s] - e.blkIndent >= 4)
      break;
    let $ = !1;
    for (let N = 0, F = E.length; N < F; N++)
      if (E[N](e, s, n, !0)) {
        $ = !0;
        break;
      }
    if ($)
      break;
    if (c) {
      if (p = ir(e, s), p < 0)
        break;
      o = e.bMarks[s] + e.tShift[s];
    } else if (p = rr(e, s), p < 0)
      break;
    if (d !== e.src.charCodeAt(p - 1))
      break;
  }
  return c ? a = e.push("ordered_list_close", "ol", -1) : a = e.push("bullet_list_close", "ul", -1), a.markup = String.fromCharCode(d), g[1] = s, e.line = s, e.parentType = D, l && _a(e, x), !0;
}
function ya(e, u, n, r) {
  let i = e.bMarks[u] + e.tShift[u], t = e.eMarks[u], o = u + 1;
  if (e.sCount[u] - e.blkIndent >= 4 || e.src.charCodeAt(i) !== 91)
    return !1;
  function a(E) {
    const D = e.lineMax;
    if (E >= D || e.isEmpty(E))
      return null;
    let m = !1;
    if (e.sCount[E] - e.blkIndent > 3 && (m = !0), e.sCount[E] < 0 && (m = !0), !m) {
      const B = e.md.block.ruler.getRules("reference"), C = e.parentType;
      e.parentType = "reference";
      let U = !1;
      for (let Z = 0, H = B.length; Z < H; Z++)
        if (B[Z](e, E, D, !0)) {
          U = !0;
          break;
        }
      if (e.parentType = C, U)
        return null;
    }
    const A = e.bMarks[E] + e.tShift[E], k = e.eMarks[E];
    return e.src.slice(A, k + 1);
  }
  let s = e.src.slice(i, t + 1);
  t = s.length;
  let l = -1;
  for (i = 1; i < t; i++) {
    const E = s.charCodeAt(i);
    if (E === 91)
      return !1;
    if (E === 93) {
      l = i;
      break;
    } else if (E === 10) {
      const D = a(o);
      D !== null && (s += D, t = s.length, o++);
    } else if (E === 92 && (i++, i < t && s.charCodeAt(i) === 10)) {
      const D = a(o);
      D !== null && (s += D, t = s.length, o++);
    }
  }
  if (l < 0 || s.charCodeAt(l + 1) !== 58)
    return !1;
  for (i = l + 2; i < t; i++) {
    const E = s.charCodeAt(i);
    if (E === 10) {
      const D = a(o);
      D !== null && (s += D, t = s.length, o++);
    } else if (!fe(E)) break;
  }
  const f = e.md.helpers.parseLinkDestination(s, i, t);
  if (!f.ok)
    return !1;
  const c = e.md.normalizeLink(f.str);
  if (!e.md.validateLink(c))
    return !1;
  i = f.pos;
  const h = i, p = o, d = i;
  for (; i < t; i++) {
    const E = s.charCodeAt(i);
    if (E === 10) {
      const D = a(o);
      D !== null && (s += D, t = s.length, o++);
    } else if (!fe(E)) break;
  }
  let x = e.md.helpers.parseLinkTitle(s, i, t);
  for (; x.can_continue; ) {
    const E = a(o);
    if (E === null) break;
    s += E, i = t, t = s.length, o++, x = e.md.helpers.parseLinkTitle(s, i, t, x);
  }
  let g;
  for (i < t && d !== i && x.ok ? (g = x.str, i = x.pos) : (g = "", i = h, o = p); i < t; ) {
    const E = s.charCodeAt(i);
    if (!fe(E))
      break;
    i++;
  }
  if (i < t && s.charCodeAt(i) !== 10 && g)
    for (g = "", i = h, o = p; i < t; ) {
      const E = s.charCodeAt(i);
      if (!fe(E))
        break;
      i++;
    }
  if (i < t && s.charCodeAt(i) !== 10)
    return !1;
  const _ = Et(s.slice(1, l));
  return _ ? (r || (typeof e.env.references > "u" && (e.env.references = {}), typeof e.env.references[_] > "u" && (e.env.references[_] = { title: g, href: c }), e.line = o), !0) : !1;
}
const ka = [
  "address",
  "article",
  "aside",
  "base",
  "basefont",
  "blockquote",
  "body",
  "caption",
  "center",
  "col",
  "colgroup",
  "dd",
  "details",
  "dialog",
  "dir",
  "div",
  "dl",
  "dt",
  "fieldset",
  "figcaption",
  "figure",
  "footer",
  "form",
  "frame",
  "frameset",
  "h1",
  "h2",
  "h3",
  "h4",
  "h5",
  "h6",
  "head",
  "header",
  "hr",
  "html",
  "iframe",
  "legend",
  "li",
  "link",
  "main",
  "menu",
  "menuitem",
  "nav",
  "noframes",
  "ol",
  "optgroup",
  "option",
  "p",
  "param",
  "search",
  "section",
  "summary",
  "table",
  "tbody",
  "td",
  "tfoot",
  "th",
  "thead",
  "title",
  "tr",
  "track",
  "ul"
], va = "[a-zA-Z_:][a-zA-Z0-9:._-]*", wa = "[^\"'=<>`\\x00-\\x20]+", Ba = "'[^']*'", Sa = '"[^"]*"', Ta = "(?:" + wa + "|" + Ba + "|" + Sa + ")", Ia = "(?:\\s+" + va + "(?:\\s*=\\s*" + Ta + ")?)", Yr = "<[A-Za-z][A-Za-z0-9\\-]*" + Ia + "*\\s*\\/?>", e0 = "<\\/[A-Za-z][A-Za-z0-9\\-]*\\s*>", Ma = "<!---?>|<!--(?:[^-]|-[^-]|--[^>])*-->", Oa = "<[?][\\s\\S]*?[?]>", Na = "<![A-Za-z][^>]*>", Ra = "<!\\[CDATA\\[[\\s\\S]*?\\]\\]>", $a = new RegExp("^(?:" + Yr + "|" + e0 + "|" + Ma + "|" + Oa + "|" + Na + "|" + Ra + ")"), La = new RegExp("^(?:" + Yr + "|" + e0 + ")"), xu = [
  [/^<(script|pre|style|textarea)(?=(\s|>|$))/i, /<\/(script|pre|style|textarea)>/i, !0],
  [/^<!--/, /-->/, !0],
  [/^<\?/, /\?>/, !0],
  [/^<![A-Za-z]/, />/, !0],
  [/^<!\[CDATA\[/, /\]\]>/, !0],
  [new RegExp("^</?(" + ka.join("|") + ")(?=(\\s|/?>|$))", "i"), /^$/, !0],
  [new RegExp(La.source + "\\s*$"), /^$/, !1]
];
function za(e, u, n, r) {
  let i = e.bMarks[u] + e.tShift[u], t = e.eMarks[u];
  if (e.sCount[u] - e.blkIndent >= 4 || !e.md.options.html || e.src.charCodeAt(i) !== 60)
    return !1;
  let o = e.src.slice(i, t), a = 0;
  for (; a < xu.length && !xu[a][0].test(o); a++)
    ;
  if (a === xu.length)
    return !1;
  if (r)
    return xu[a][2];
  let s = u + 1;
  const l = xu[a][1].test("");
  if (!xu[a][1].test(o)) {
    for (; s < n && !(e.sCount[s] < e.blkIndent && (l || !e.isEmpty(s))); s++)
      if (i = e.bMarks[s] + e.tShift[s], t = e.eMarks[s], o = e.src.slice(i, t), xu[a][1].test(o)) {
        o.length !== 0 && s++;
        break;
      }
  }
  e.line = s;
  const f = e.push("html_block", "", 0);
  return f.map = [u, s], f.content = e.getLines(u, s, e.blkIndent, !0), !0;
}
function Pa(e, u, n, r) {
  let i = e.bMarks[u] + e.tShift[u], t = e.eMarks[u];
  if (e.sCount[u] - e.blkIndent >= 4)
    return !1;
  let o = e.src.charCodeAt(i);
  if (o !== 35 || i >= t)
    return !1;
  let a = 1;
  for (o = e.src.charCodeAt(++i); o === 35 && i < t && a <= 6; )
    a++, o = e.src.charCodeAt(++i);
  if (a > 6 || i < t && !fe(o))
    return !1;
  if (r)
    return !0;
  t = e.skipSpacesBack(t, i);
  const s = e.skipCharsBack(t, 35, i);
  s > i && fe(e.src.charCodeAt(s - 1)) && (t = s), e.line = u + 1;
  const l = e.push("heading_open", "h" + String(a), 1);
  l.markup = "########".slice(0, a), l.map = [u, e.line];
  const f = e.push("inline", "", 0);
  f.content = xt(e.src.slice(i, t)), f.map = [u, e.line], f.children = [];
  const c = e.push("heading_close", "h" + String(a), -1);
  return c.markup = "########".slice(0, a), !0;
}
function Ha(e, u, n) {
  const r = e.md.block.ruler.getRules("paragraph");
  if (e.sCount[u] - e.blkIndent >= 4)
    return !1;
  const i = e.parentType;
  e.parentType = "paragraph";
  let t = 0, o, a = u + 1;
  for (; a < n && !e.isEmpty(a); a++) {
    if (e.sCount[a] - e.blkIndent > 3)
      continue;
    if (e.sCount[a] >= e.blkIndent) {
      let p = e.bMarks[a] + e.tShift[a];
      const d = e.eMarks[a];
      if (p < d && (o = e.src.charCodeAt(p), (o === 45 || o === 61) && (p = e.skipChars(p, o), p = e.skipSpaces(p), p >= d))) {
        t = o === 61 ? 1 : 2;
        break;
      }
    }
    if (e.sCount[a] < 0)
      continue;
    let h = !1;
    for (let p = 0, d = r.length; p < d; p++)
      if (r[p](e, a, n, !0)) {
        h = !0;
        break;
      }
    if (h)
      break;
  }
  if (!t)
    return e.parentType = i, !1;
  const s = xt(e.getLines(u, a, e.blkIndent, !1));
  e.line = a + 1;
  const l = e.push("heading_open", "h" + String(t), 1);
  l.markup = String.fromCharCode(o), l.map = [u, e.line];
  const f = e.push("inline", "", 0);
  f.content = s, f.map = [u, e.line - 1], f.children = [];
  const c = e.push("heading_close", "h" + String(t), -1);
  return c.markup = String.fromCharCode(o), e.parentType = i, !0;
}
function ja(e, u, n) {
  const r = e.md.block.ruler.getRules("paragraph"), i = e.parentType;
  let t = u + 1;
  for (e.parentType = "paragraph"; t < n && !e.isEmpty(t); t++) {
    if (e.sCount[t] - e.blkIndent > 3 || e.sCount[t] < 0)
      continue;
    let l = !1;
    for (let f = 0, c = r.length; f < c; f++)
      if (r[f](e, t, n, !0)) {
        l = !0;
        break;
      }
    if (l)
      break;
  }
  const o = xt(e.getLines(u, t, e.blkIndent, !1));
  e.line = t;
  const a = e.push("paragraph_open", "p", 1);
  a.map = [u, e.line];
  const s = e.push("inline", "", 0);
  return s.content = o, s.map = [u, e.line], s.children = [], e.push("paragraph_close", "p", -1), e.parentType = i, !0;
}
const rt = [
  // First 2 params - rule name & source. Secondary array - list of rules,
  // which can be terminated by this one.
  ["table", Da, ["paragraph", "reference"]],
  ["code", Ea],
  ["fence", xa, ["paragraph", "reference", "blockquote", "list"]],
  ["blockquote", Ca, ["paragraph", "reference", "blockquote", "list"]],
  ["hr", Aa, ["paragraph", "reference", "blockquote", "list"]],
  ["list", Fa, ["paragraph", "reference", "blockquote"]],
  ["reference", ya],
  ["html_block", za, ["paragraph", "reference", "blockquote"]],
  ["heading", Pa, ["paragraph", "reference", "blockquote"]],
  ["lheading", Ha],
  ["paragraph", ja]
];
function Ct() {
  this.ruler = new Se();
  for (let e = 0; e < rt.length; e++)
    this.ruler.push(rt[e][0], rt[e][1], { alt: (rt[e][2] || []).slice() });
}
Ct.prototype.tokenize = function(e, u, n) {
  const r = this.ruler.getRules(""), i = r.length, t = e.md.options.maxNesting;
  let o = u, a = !1;
  for (; o < n && (e.line = o = e.skipEmptyLines(o), !(o >= n || e.sCount[o] < e.blkIndent)); ) {
    if (e.level >= t) {
      e.line = n;
      break;
    }
    const s = e.line;
    let l = !1;
    for (let f = 0; f < i; f++)
      if (l = r[f](e, o, n, !1), l) {
        if (s >= e.line)
          throw new Error("block rule didn't increment state.line");
        break;
      }
    if (!l) throw new Error("none of the block rules matched");
    e.tight = !a, e.isEmpty(e.line - 1) && (a = !0), o = e.line, o < n && e.isEmpty(o) && (a = !0, o++, e.line = o);
  }
};
Ct.prototype.parse = function(e, u, n, r) {
  if (!e)
    return;
  const i = new this.State(e, u, n, r);
  this.tokenize(i, i.line, i.lineMax);
};
Ct.prototype.State = We;
function Vu(e, u, n, r) {
  this.src = e, this.env = n, this.md = u, this.tokens = r, this.tokens_meta = Array(r.length), this.pos = 0, this.posMax = this.src.length, this.level = 0, this.pending = "", this.pendingLevel = 0, this.cache = {}, this.delimiters = [], this._prev_delimiters = [], this.backticks = {}, this.backticksScanned = !1, this.linkLevel = 0;
}
Vu.prototype.pushPending = function() {
  const e = new Pe("text", "", 0);
  return e.content = this.pending, e.level = this.pendingLevel, this.tokens.push(e), this.pending = "", e;
};
Vu.prototype.push = function(e, u, n) {
  this.pending && this.pushPending();
  const r = new Pe(e, u, n);
  let i = null;
  return n < 0 && (this.level--, this.delimiters = this._prev_delimiters.pop()), r.level = this.level, n > 0 && (this.level++, this._prev_delimiters.push(this.delimiters), this.delimiters = [], i = { delimiters: this.delimiters }), this.pendingLevel = this.level, this.tokens.push(r), this.tokens_meta.push(i), r;
};
Vu.prototype.scanDelims = function(e, u) {
  const n = this.posMax, r = this.src.charCodeAt(e);
  let i;
  if (e === 0)
    i = 32;
  else if (e === 1)
    i = this.src.charCodeAt(0), (i & 63488) === 55296 && (i = 65533);
  else if (i = this.src.charCodeAt(e - 1), (i & 64512) === 56320) {
    const g = this.src.charCodeAt(e - 2);
    i = (g & 64512) === 55296 ? 65536 + (g - 55296 << 10) + (i - 56320) : 65533;
  } else (i & 64512) === 55296 && (i = 65533);
  let t = e;
  for (; t < n && this.src.charCodeAt(t) === r; )
    t++;
  const o = t - e;
  let a = t < n ? this.src.charCodeAt(t) : 32;
  if ((a & 64512) === 55296) {
    const g = this.src.charCodeAt(t + 1);
    a = (g & 64512) === 56320 ? 65536 + (a - 55296 << 10) + (g - 56320) : 65533;
  } else (a & 64512) === 56320 && (a = 65533);
  const s = qu(i) || ju(i), l = qu(a) || ju(a), f = Hu(i), c = Hu(a), h = !c && (!l || f || s), p = !f && (!s || c || l);
  return { can_open: h && (u || !p || s), can_close: p && (u || !h || l), length: o };
};
Vu.prototype.Token = Pe;
function qa(e) {
  switch (e) {
    case 10:
    case 33:
    case 35:
    case 36:
    case 37:
    case 38:
    case 42:
    case 43:
    case 45:
    case 58:
    case 60:
    case 61:
    case 62:
    case 64:
    case 91:
    case 92:
    case 93:
    case 94:
    case 95:
    case 96:
    case 123:
    case 125:
    case 126:
      return !0;
    default:
      return !1;
  }
}
function Ua(e, u) {
  let n = e.pos;
  for (; n < e.posMax && !qa(e.src.charCodeAt(n)); )
    n++;
  return n === e.pos ? !1 : (u || (e.pending += e.src.slice(e.pos, n)), e.pos = n, !0);
}
function Za(e) {
  return e >= 65 && e <= 90 || e >= 97 && e <= 122;
}
function Ga(e) {
  return e >= 65 && e <= 90 || e >= 97 && e <= 122 || e >= 48 && e <= 57 || e === 43 || e === 45 || e === 46;
}
function Va(e, u) {
  if (!e.md.options.linkify || e.linkLevel > 0) return !1;
  const n = e.pos, r = e.posMax;
  if (n + 3 > r || e.src.charCodeAt(n) !== 58 || e.src.charCodeAt(n + 1) !== 47 || e.src.charCodeAt(n + 2) !== 47) return !1;
  const i = n - Math.min(10, e.pending.length, n);
  let t = n;
  for (; t > i && Ga(e.src.charCodeAt(t - 1)); )
    t--;
  if (t === n || !Za(e.src.charCodeAt(t))) return !1;
  const o = n - t, a = e.md.linkify.matchAtStart(e.src.slice(t));
  if (!a) return !1;
  let s = a.url;
  if (s.length <= o) return !1;
  let l = s.length;
  for (; l > 0 && s.charCodeAt(l - 1) === 42; )
    l--;
  l !== s.length && (s = s.slice(0, l));
  const f = e.md.normalizeLink(s);
  if (!e.md.validateLink(f)) return !1;
  if (!u) {
    e.pending = e.pending.slice(0, -o);
    const c = e.push("link_open", "a", 1);
    c.attrs = [["href", f]], c.markup = "linkify", c.info = "auto";
    const h = e.push("text", "", 0);
    h.content = e.md.normalizeLinkText(s);
    const p = e.push("link_close", "a", -1);
    p.markup = "linkify", p.info = "auto";
  }
  return e.pos += s.length - o, !0;
}
function Wa(e, u) {
  let n = e.pos;
  if (e.src.charCodeAt(n) !== 10)
    return !1;
  const r = e.pending.length - 1, i = e.posMax;
  if (!u)
    if (r >= 0 && e.pending.charCodeAt(r) === 32)
      if (r >= 1 && e.pending.charCodeAt(r - 1) === 32) {
        let t = r - 1;
        for (; t >= 1 && e.pending.charCodeAt(t - 1) === 32; ) t--;
        e.pending = e.pending.slice(0, t), e.push("hardbreak", "br", 0);
      } else
        e.pending = e.pending.slice(0, -1), e.push("softbreak", "br", 0);
    else
      e.push("softbreak", "br", 0);
  for (n++; n < i && fe(e.src.charCodeAt(n)); )
    n++;
  return e.pos = n, !0;
}
const En = [];
for (let e = 0; e < 256; e++)
  En.push(0);
"\\!\"#$%&'()*+,./:;<=>?@[]^_`{|}~-".split("").forEach(function(e) {
  En[e.charCodeAt(0)] = 1;
});
function Ka(e, u) {
  let n = e.pos;
  const r = e.posMax;
  if (e.src.charCodeAt(n) !== 92 || (n++, n >= r)) return !1;
  let i = e.src.charCodeAt(n);
  if (i === 10) {
    for (u || e.push("hardbreak", "br", 0), n++; n < r && (i = e.src.charCodeAt(n), !!fe(i)); )
      n++;
    return e.pos = n, !0;
  }
  if (i === 32) {
    if (!u) {
      const a = e.push("text_special", "", 0);
      a.content = "\\", a.markup = "\\", a.info = "escape";
    }
    return e.pos = n, !0;
  }
  let t = e.src[n];
  if (i >= 55296 && i <= 56319 && n + 1 < r) {
    const a = e.src.charCodeAt(n + 1);
    a >= 56320 && a <= 57343 && (t += e.src[n + 1], n++);
  }
  const o = "\\" + t;
  if (!u) {
    const a = e.push("text_special", "", 0);
    i < 256 && En[i] !== 0 ? a.content = t : a.content = o, a.markup = o, a.info = "escape";
  }
  return e.pos = n + 1, !0;
}
function Xa(e, u) {
  let n = e.pos;
  if (e.src.charCodeAt(n) !== 96)
    return !1;
  const i = n;
  n++;
  const t = e.posMax;
  for (; n < t && e.src.charCodeAt(n) === 96; )
    n++;
  const o = e.src.slice(i, n), a = o.length;
  if (e.backticksScanned && (e.backticks[a] || 0) <= i)
    return u || (e.pending += o), e.pos += a, !0;
  let s = n, l;
  for (; (l = e.src.indexOf("`", s)) !== -1; ) {
    for (s = l + 1; s < t && e.src.charCodeAt(s) === 96; )
      s++;
    const f = s - l;
    if (f === a) {
      if (!u) {
        const c = e.push("code_inline", "code", 0);
        c.markup = o, c.content = e.src.slice(n, l).replace(/\n/g, " ").replace(/^ (.+) $/, "$1");
      }
      return e.pos = s, !0;
    }
    e.backticks[f] = l;
  }
  return e.backticksScanned = !0, u || (e.pending += o), e.pos += a, !0;
}
function Ja(e, u) {
  const n = e.pos, r = e.src.charCodeAt(n);
  if (u || r !== 126)
    return !1;
  const i = e.scanDelims(e.pos, !0);
  let t = i.length;
  const o = String.fromCharCode(r);
  if (t < 2)
    return !1;
  let a;
  t % 2 && (a = e.push("text", "", 0), a.content = o, t--);
  for (let s = 0; s < t; s += 2)
    a = e.push("text", "", 0), a.content = o + o, e.delimiters.push({
      marker: r,
      length: 0,
      // disable "rule of 3" length checks meant for emphasis
      token: e.tokens.length - 1,
      end: -1,
      open: i.can_open,
      close: i.can_close
    });
  return e.pos += i.length, !0;
}
function or(e, u) {
  let n;
  const r = [], i = u.length;
  for (let t = 0; t < i; t++) {
    const o = u[t];
    if (o.marker !== 126 || o.end === -1)
      continue;
    const a = u[o.end];
    n = e.tokens[o.token], n.type = "s_open", n.tag = "s", n.nesting = 1, n.markup = "~~", n.content = "", n = e.tokens[a.token], n.type = "s_close", n.tag = "s", n.nesting = -1, n.markup = "~~", n.content = "", e.tokens[a.token - 1].type === "text" && e.tokens[a.token - 1].content === "~" && r.push(a.token - 1);
  }
  for (; r.length; ) {
    const t = r.pop();
    let o = t + 1;
    for (; o < e.tokens.length && e.tokens[o].type === "s_close"; )
      o++;
    o--, t !== o && (n = e.tokens[o], e.tokens[o] = e.tokens[t], e.tokens[t] = n);
  }
}
function Qa(e) {
  const u = e.tokens_meta, n = e.tokens_meta.length;
  or(e, e.delimiters);
  for (let r = 0; r < n; r++)
    u[r] && u[r].delimiters && or(e, u[r].delimiters);
}
const u0 = {
  tokenize: Ja,
  postProcess: Qa
};
function Ya(e, u) {
  const n = e.pos, r = e.src.charCodeAt(n);
  if (u || r !== 95 && r !== 42)
    return !1;
  const i = e.scanDelims(e.pos, r === 42);
  for (let t = 0; t < i.length; t++) {
    const o = e.push("text", "", 0);
    o.content = String.fromCharCode(r), e.delimiters.push({
      // Char code of the starting marker (number).
      //
      marker: r,
      // Total length of these series of delimiters.
      //
      length: i.length,
      // A position of the token this delimiter corresponds to.
      //
      token: e.tokens.length - 1,
      // If this delimiter is matched as a valid opener, `end` will be
      // equal to its position, otherwise it's `-1`.
      //
      end: -1,
      // Boolean flags that determine if this delimiter could open or close
      // an emphasis.
      //
      open: i.can_open,
      close: i.can_close
    });
  }
  return e.pos += i.length, !0;
}
function ar(e, u) {
  const n = u.length;
  for (let r = n - 1; r >= 0; r--) {
    const i = u[r];
    if (i.marker !== 95 && i.marker !== 42 || i.end === -1)
      continue;
    const t = u[i.end], o = r > 0 && u[r - 1].end === i.end + 1 && // check that first two markers match and adjacent
    u[r - 1].marker === i.marker && u[r - 1].token === i.token - 1 && // check that last two markers are adjacent (we can safely assume they match)
    u[i.end + 1].token === t.token + 1, a = String.fromCharCode(i.marker), s = e.tokens[i.token];
    s.type = o ? "strong_open" : "em_open", s.tag = o ? "strong" : "em", s.nesting = 1, s.markup = o ? a + a : a, s.content = "";
    const l = e.tokens[t.token];
    l.type = o ? "strong_close" : "em_close", l.tag = o ? "strong" : "em", l.nesting = -1, l.markup = o ? a + a : a, l.content = "", o && (e.tokens[u[r - 1].token].content = "", e.tokens[u[i.end + 1].token].content = "", r--);
  }
}
function es(e) {
  const u = e.tokens_meta, n = e.tokens_meta.length;
  ar(e, e.delimiters);
  for (let r = 0; r < n; r++)
    u[r] && u[r].delimiters && ar(e, u[r].delimiters);
}
const t0 = {
  tokenize: Ya,
  postProcess: es
};
function us(e, u) {
  let n, r, i, t, o = "", a = "", s = e.pos, l = !0;
  if (e.src.charCodeAt(e.pos) !== 91)
    return !1;
  const f = e.pos, c = e.posMax, h = e.pos + 1, p = e.md.helpers.parseLinkLabel(e, e.pos, !0);
  if (p < 0)
    return !1;
  let d = p + 1;
  if (d < c && e.src.charCodeAt(d) === 40) {
    for (l = !1, d++; d < c && (n = e.src.charCodeAt(d), !(!fe(n) && n !== 10)); d++)
      ;
    if (d >= c)
      return !1;
    if (s = d, i = e.md.helpers.parseLinkDestination(e.src, d, e.posMax), i.ok) {
      for (o = e.md.normalizeLink(i.str), e.md.validateLink(o) ? d = i.pos : o = "", s = d; d < c && (n = e.src.charCodeAt(d), !(!fe(n) && n !== 10)); d++)
        ;
      if (i = e.md.helpers.parseLinkTitle(e.src, d, e.posMax), d < c && s !== d && i.ok)
        for (a = i.str, d = i.pos; d < c && (n = e.src.charCodeAt(d), !(!fe(n) && n !== 10)); d++)
          ;
    }
    (d >= c || e.src.charCodeAt(d) !== 41) && (l = !0), d++;
  }
  if (l) {
    if (typeof e.env.references > "u")
      return !1;
    if (d < c && e.src.charCodeAt(d) === 91 ? (s = d + 1, d = e.md.helpers.parseLinkLabel(e, d), d >= 0 ? r = e.src.slice(s, d++) : d = p + 1) : d = p + 1, r || (r = e.src.slice(h, p)), t = e.env.references[Et(r)], !t)
      return e.pos = f, !1;
    o = t.href, a = t.title;
  }
  if (!u) {
    e.pos = h, e.posMax = p;
    const x = e.push("link_open", "a", 1), g = [["href", o]];
    x.attrs = g, a && g.push(["title", a]), e.linkLevel++, e.md.inline.tokenize(e), e.linkLevel--, e.push("link_close", "a", -1);
  }
  return e.pos = d, e.posMax = c, !0;
}
function ts(e, u) {
  let n, r, i, t, o, a, s, l, f = "";
  const c = e.pos, h = e.posMax;
  if (e.src.charCodeAt(e.pos) !== 33 || e.src.charCodeAt(e.pos + 1) !== 91)
    return !1;
  const p = e.pos + 2, d = e.md.helpers.parseLinkLabel(e, e.pos + 1, !1);
  if (d < 0)
    return !1;
  if (t = d + 1, t < h && e.src.charCodeAt(t) === 40) {
    for (t++; t < h && (n = e.src.charCodeAt(t), !(!fe(n) && n !== 10)); t++)
      ;
    if (t >= h)
      return !1;
    for (l = t, a = e.md.helpers.parseLinkDestination(e.src, t, e.posMax), a.ok && (f = e.md.normalizeLink(a.str), e.md.validateLink(f) ? t = a.pos : f = ""), l = t; t < h && (n = e.src.charCodeAt(t), !(!fe(n) && n !== 10)); t++)
      ;
    if (a = e.md.helpers.parseLinkTitle(e.src, t, e.posMax), t < h && l !== t && a.ok)
      for (s = a.str, t = a.pos; t < h && (n = e.src.charCodeAt(t), !(!fe(n) && n !== 10)); t++)
        ;
    else
      s = "";
    if (t >= h || e.src.charCodeAt(t) !== 41)
      return e.pos = c, !1;
    t++;
  } else {
    if (typeof e.env.references > "u")
      return !1;
    if (t < h && e.src.charCodeAt(t) === 91 ? (l = t + 1, t = e.md.helpers.parseLinkLabel(e, t), t >= 0 ? i = e.src.slice(l, t++) : t = d + 1) : t = d + 1, i || (i = e.src.slice(p, d)), o = e.env.references[Et(i)], !o)
      return e.pos = c, !1;
    f = o.href, s = o.title;
  }
  if (!u) {
    r = e.src.slice(p, d);
    const x = [];
    e.md.inline.parse(
      r,
      e.md,
      e.env,
      x
    );
    const g = e.push("image", "img", 0), _ = [["src", f], ["alt", ""]];
    g.attrs = _, g.children = x, g.content = r, s && _.push(["title", s]);
  }
  return e.pos = t, e.posMax = h, !0;
}
const ns = /^([a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*)$/, rs = /^([a-zA-Z][a-zA-Z0-9+.-]{1,31}):([^<>\x00-\x20]*)$/;
function is(e, u) {
  let n = e.pos;
  if (e.src.charCodeAt(n) !== 60)
    return !1;
  const r = e.pos, i = e.posMax;
  for (; ; ) {
    if (++n >= i) return !1;
    const o = e.src.charCodeAt(n);
    if (o === 60) return !1;
    if (o === 62) break;
  }
  const t = e.src.slice(r + 1, n);
  if (rs.test(t)) {
    const o = e.md.normalizeLink(t);
    if (!e.md.validateLink(o))
      return !1;
    if (!u) {
      const a = e.push("link_open", "a", 1);
      a.attrs = [["href", o]], a.markup = "autolink", a.info = "auto";
      const s = e.push("text", "", 0);
      s.content = e.md.normalizeLinkText(t);
      const l = e.push("link_close", "a", -1);
      l.markup = "autolink", l.info = "auto";
    }
    return e.pos += t.length + 2, !0;
  }
  if (ns.test(t)) {
    const o = e.md.normalizeLink("mailto:" + t);
    if (!e.md.validateLink(o))
      return !1;
    if (!u) {
      const a = e.push("link_open", "a", 1);
      a.attrs = [["href", o]], a.markup = "autolink", a.info = "auto";
      const s = e.push("text", "", 0);
      s.content = e.md.normalizeLinkText(t);
      const l = e.push("link_close", "a", -1);
      l.markup = "autolink", l.info = "auto";
    }
    return e.pos += t.length + 2, !0;
  }
  return !1;
}
function os(e) {
  return /^<a[>\s]/i.test(e);
}
function as(e) {
  return /^<\/a\s*>/i.test(e);
}
function ss(e) {
  const u = e | 32;
  return u >= 97 && u <= 122;
}
function cs(e, u) {
  if (!e.md.options.html)
    return !1;
  const n = e.posMax, r = e.pos;
  if (e.src.charCodeAt(r) !== 60 || r + 2 >= n)
    return !1;
  const i = e.src.charCodeAt(r + 1);
  if (i !== 33 && i !== 63 && i !== 47 && !ss(i))
    return !1;
  const t = e.src.slice(r).match($a);
  if (!t)
    return !1;
  if (!u) {
    const o = e.push("html_inline", "", 0);
    o.content = t[0], os(o.content) && e.linkLevel++, as(o.content) && e.linkLevel--;
  }
  return e.pos += t[0].length, !0;
}
const ls = /^&#((?:x[a-f0-9]{1,6}|[0-9]{1,7}));/i, fs = /^&([a-z][a-z0-9]{1,31});/i;
function ds(e, u) {
  const n = e.pos, r = e.posMax;
  if (e.src.charCodeAt(n) !== 38 || n + 1 >= r) return !1;
  if (e.src.charCodeAt(n + 1) === 35) {
    const t = e.src.slice(n).match(ls);
    if (t) {
      if (!u) {
        const o = t[1][0].toLowerCase() === "x" ? parseInt(t[1].slice(1), 16) : parseInt(t[1], 10), a = e.push("text_special", "", 0);
        a.content = mn(o) ? Pu(o) : Pu(65533), a.markup = t[0], a.info = "entity";
      }
      return e.pos += t[0].length, !0;
    }
  } else {
    const t = e.src.slice(n).match(fs);
    if (t) {
      const o = wo(t[0]);
      if (o !== t[0]) {
        if (!u) {
          const a = e.push("text_special", "", 0);
          a.content = o, a.markup = t[0], a.info = "entity";
        }
        return e.pos += t[0].length, !0;
      }
    }
  }
  return !1;
}
function sr(e) {
  const u = {}, n = e.length;
  if (!n) return;
  let r = 0, i = -2;
  const t = [];
  for (let o = 0; o < n; o++) {
    const a = e[o];
    if (t.push(0), (e[r].marker !== a.marker || i !== a.token - 1) && (r = o), i = a.token, a.length = a.length || 0, !a.close) continue;
    u.hasOwnProperty(a.marker) || (u[a.marker] = [-1, -1, -1, -1, -1, -1]);
    const s = u[a.marker][(a.open ? 3 : 0) + a.length % 3];
    let l = r - t[r] - 1, f = l;
    for (; l > s; l -= t[l] + 1) {
      const c = e[l];
      if (c.marker === a.marker && c.open && c.end < 0) {
        let h = !1;
        if ((c.close || a.open) && (c.length + a.length) % 3 === 0 && (c.length % 3 !== 0 || a.length % 3 !== 0) && (h = !0), !h) {
          const p = l > 0 && !e[l - 1].open ? t[l - 1] + 1 : 0;
          t[o] = o - l + p, t[l] = p, a.open = !1, c.end = o, c.close = !1, f = -1, i = -2;
          break;
        }
      }
    }
    f !== -1 && (u[a.marker][(a.open ? 3 : 0) + (a.length || 0) % 3] = f);
  }
}
function ps(e) {
  const u = e.tokens_meta, n = e.tokens_meta.length;
  sr(e.delimiters);
  for (let r = 0; r < n; r++)
    u[r] && u[r].delimiters && sr(u[r].delimiters);
}
function hs(e) {
  let u, n, r = 0;
  const i = e.tokens, t = e.tokens.length;
  for (u = n = 0; u < t; u++)
    i[u].nesting < 0 && r--, i[u].level = r, i[u].nesting > 0 && r++, i[u].type === "text" && u + 1 < t && i[u + 1].type === "text" ? i[u + 1].content = i[u].content + i[u + 1].content : (u !== n && (i[n] = i[u]), n++);
  u !== n && (i.length = n);
}
const Pt = [
  ["text", Ua],
  ["linkify", Va],
  ["newline", Wa],
  ["escape", Ka],
  ["backticks", Xa],
  ["strikethrough", u0.tokenize],
  ["emphasis", t0.tokenize],
  ["link", us],
  ["image", ts],
  ["autolink", is],
  ["html_inline", cs],
  ["entity", ds]
], Ht = [
  ["balance_pairs", ps],
  ["strikethrough", u0.postProcess],
  ["emphasis", t0.postProcess],
  // rules for pairs separate '**' into its own text tokens, which may be left unused,
  // rule below merges unused segments back with the rest of the text
  ["fragments_join", hs]
];
function Wu() {
  this.ruler = new Se();
  for (let e = 0; e < Pt.length; e++)
    this.ruler.push(Pt[e][0], Pt[e][1]);
  this.ruler2 = new Se();
  for (let e = 0; e < Ht.length; e++)
    this.ruler2.push(Ht[e][0], Ht[e][1]);
}
Wu.prototype.skipToken = function(e) {
  const u = e.pos, n = this.ruler.getRules(""), r = n.length, i = e.md.options.maxNesting, t = e.cache;
  if (typeof t[u] < "u") {
    e.pos = t[u];
    return;
  }
  let o = !1;
  if (e.level < i) {
    for (let a = 0; a < r; a++)
      if (e.level++, o = n[a](e, !0), e.level--, o) {
        if (u >= e.pos)
          throw new Error("inline rule didn't increment state.pos");
        break;
      }
  } else
    e.pos = e.posMax;
  o || e.pos++, t[u] = e.pos;
};
Wu.prototype.tokenize = function(e) {
  const u = this.ruler.getRules(""), n = u.length, r = e.posMax, i = e.md.options.maxNesting;
  for (; e.pos < r; ) {
    const t = e.pos;
    let o = !1;
    if (e.level < i) {
      for (let a = 0; a < n; a++)
        if (o = u[a](e, !1), o) {
          if (t >= e.pos)
            throw new Error("inline rule didn't increment state.pos");
          break;
        }
    }
    if (o) {
      if (e.pos >= r)
        break;
      continue;
    }
    e.pending += e.src[e.pos++];
  }
  e.pending && e.pushPending();
};
Wu.prototype.parse = function(e, u, n, r) {
  const i = new this.State(e, u, n, r);
  this.tokenize(i);
  const t = this.ruler2.getRules(""), o = t.length;
  for (let a = 0; a < o; a++)
    t[a](i);
};
Wu.prototype.State = Vu;
function bs(e) {
  const u = {};
  e = e || {}, u.src_Any = qr.source, u.src_Cc = Ur.source, u.src_Z = Gr.source, u.src_P = bn.source, u.src_ZPCc = [u.src_Z, u.src_P, u.src_Cc].join("|"), u.src_ZCc = [u.src_Z, u.src_Cc].join("|");
  const n = "[><｜]";
  return u.src_pseudo_letter = `(?:(?!${n}|${u.src_ZPCc})${u.src_Any})`, u.src_ip4 = "(?:(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)", u.src_auth = `(?:(?:(?!${u.src_ZCc}|[@/\\[\\]()]).){1,50}@)?`, u.src_port = "(?::(?:6(?:[0-4]\\d{3}|5(?:[0-4]\\d{2}|5(?:[0-2]\\d|3[0-5])))|[1-5]?\\d{1,4}))?", u.src_host_terminator = `(?=$|${n}|${u.src_ZPCc})(?!${e["---"] ? "-(?!--)|" : "-|"}_|:\\d|\\.-|\\.(?!$|${u.src_ZPCc}))`, u.src_path = `(?:[/?#](?:(?!${u.src_ZCc}|${n}|[()[\\]{}.,"'?!\\-;]).|\\[(?:(?!${u.src_ZCc}|\\]).)*\\]|\\((?:(?!${u.src_ZCc}|[)]).)*\\)|\\{(?:(?!${u.src_ZCc}|[}]).)*\\}|\\"(?:(?!${u.src_ZCc}|["]).)+\\"|\\'(?:(?!${u.src_ZCc}|[']).)+\\'|\\'(?=${u.src_pseudo_letter}|[-])|\\.{2,}[a-zA-Z0-9%/&]|\\.(?!${u.src_ZCc}|[.]|$)|` + (e["---"] ? "\\-(?!--(?:[^-]|$))(?:-*)|" : "\\-+|") + // allow `,,,` in paths
  `,(?!${u.src_ZCc}|$)|;(?!${u.src_ZCc}|$)|\\!+(?!${u.src_ZCc}|[!]|$)|\\?(?!${u.src_ZCc}|[?]|$))+|\\/)?`, u.src_email_name = '[\\-;:&=\\+\\$,\\.a-zA-Z0-9_][\\-;:&=\\+\\$,\\"\\.a-zA-Z0-9_]{0,63}', u.src_xn = "xn--[a-z0-9\\-]{1,59}", u.src_domain_root = // Allow letters & digits (http://test1)
  "(?:" + u.src_xn + `|${u.src_pseudo_letter}{1,63})`, u.src_domain = "(?:" + u.src_xn + `|(?:${u.src_pseudo_letter})|(?:${u.src_pseudo_letter}(?:-|${u.src_pseudo_letter}){0,61}${u.src_pseudo_letter}))`, u.src_host = `(?:(?:(?:(?:${u.src_domain})\\.)*${u.src_domain}))`, u.tpl_host_fuzzy = "(?:" + u.src_ip4 + `|(?:(?:(?:${u.src_domain})\\.)+(?:%TLDS%)))`, u.tpl_host_no_ip_fuzzy = `(?:(?:(?:${u.src_domain})\\.)+(?:%TLDS%))`, u.src_host_strict = u.src_host + u.src_host_terminator, u.tpl_host_fuzzy_strict = u.tpl_host_fuzzy + u.src_host_terminator, u.src_host_port_strict = u.src_host + u.src_port + u.src_host_terminator, u.tpl_host_port_fuzzy_strict = u.tpl_host_fuzzy + u.src_port + u.src_host_terminator, u.tpl_host_port_no_ip_fuzzy_strict = u.tpl_host_no_ip_fuzzy + u.src_port + u.src_host_terminator, u.tpl_host_fuzzy_test = `localhost|www\\.|\\.\\d{1,3}\\.|(?:\\.(?:%TLDS%)(?:${u.src_ZPCc}|>|$))`, u.tpl_email_fuzzy = `(^|${n}|"|\\(|${u.src_ZCc})(${u.src_email_name}@${u.tpl_host_fuzzy_strict})`, u.tpl_link_fuzzy = // Fuzzy link can't be prepended with .:/\- and non punctuation.
  // but can start with > (markdown blockquote)
  `(^|(?![.:/\\-_@])(?:[$+<=>^\`|｜]|${u.src_ZPCc}))((?![$+<=>^\`|｜])${u.tpl_host_port_fuzzy_strict}${u.src_path})`, u.tpl_link_no_ip_fuzzy = // Fuzzy link can't be prepended with .:/\- and non punctuation.
  // but can start with > (markdown blockquote)
  `(^|(?![.:/\\-_@])(?:[$+<=>^\`|｜]|${u.src_ZPCc}))((?![$+<=>^\`|｜])${u.tpl_host_port_no_ip_fuzzy_strict}${u.src_path})`, u;
}
function cn(e) {
  return Array.prototype.slice.call(arguments, 1).forEach(function(n) {
    n && Object.keys(n).forEach(function(r) {
      e[r] = n[r];
    });
  }), e;
}
function At(e) {
  return Object.prototype.toString.call(e);
}
function gs(e) {
  return At(e) === "[object String]";
}
function ms(e) {
  return At(e) === "[object Object]";
}
function Ds(e) {
  return At(e) === "[object RegExp]";
}
function cr(e) {
  return At(e) === "[object Function]";
}
function Es(e) {
  return e.replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
}
const n0 = {
  fuzzyLink: !0,
  fuzzyEmail: !0,
  fuzzyIP: !1
};
function xs(e) {
  return Object.keys(e || {}).reduce(function(u, n) {
    return u || n0.hasOwnProperty(n);
  }, !1);
}
const Cs = {
  "http:": {
    validate: function(e, u, n) {
      const r = e.slice(u);
      return n.re.http || (n.re.http = new RegExp(
        `^\\/\\/${n.re.src_auth}${n.re.src_host_port_strict}${n.re.src_path}`,
        "i"
      )), n.re.http.test(r) ? r.match(n.re.http)[0].length : 0;
    }
  },
  "https:": "http:",
  "ftp:": "http:",
  "//": {
    validate: function(e, u, n) {
      const r = e.slice(u);
      return n.re.no_http || (n.re.no_http = new RegExp(
        "^" + n.re.src_auth + // Don't allow single-level domains, because of false positives like '//test'
        // with code comments
        `(?:localhost|(?:(?:${n.re.src_domain})\\.)+${n.re.src_domain_root})` + n.re.src_port + n.re.src_host_terminator + n.re.src_path,
        "i"
      )), n.re.no_http.test(r) ? u >= 3 && e[u - 3] === ":" || u >= 3 && e[u - 3] === "/" ? 0 : r.match(n.re.no_http)[0].length : 0;
    }
  },
  "mailto:": {
    validate: function(e, u, n) {
      const r = e.slice(u);
      return n.re.mailto || (n.re.mailto = new RegExp(
        `^${n.re.src_email_name}@${n.re.src_host_strict}`,
        "i"
      )), n.re.mailto.test(r) ? r.match(n.re.mailto)[0].length : 0;
    }
  }
}, As = "a[cdefgilmnoqrstuwxz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvwxyz]|d[ejkmoz]|e[cegrstu]|f[ijkmor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdeghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eosuw]|s[abcdeghijklmnortuvxyz]|t[cdfghjklmnortvwz]|u[agksyz]|v[aceginu]|w[fs]|y[et]|z[amw]", _s = "biz|com|edu|gov|net|org|pro|web|xxx|aero|asia|coop|info|museum|name|shop|рф".split("|");
function Fs(e) {
  return function(u, n) {
    const r = u.slice(n);
    return e.test(r) ? r.match(e)[0].length : 0;
  };
}
function lr() {
  return function(e, u) {
    u.normalize(e);
  };
}
function pt(e) {
  const u = e.re = bs(e.__opts__), n = e.__tlds__.slice();
  e.onCompile(), e.__tlds_replaced__ || n.push(As), n.push(u.src_xn), u.src_tlds = n.join("|");
  function r(a) {
    return a.replace("%TLDS%", u.src_tlds);
  }
  u.email_fuzzy = RegExp(r(u.tpl_email_fuzzy), "i"), u.email_fuzzy_global = RegExp(r(u.tpl_email_fuzzy), "ig"), u.link_fuzzy = RegExp(r(u.tpl_link_fuzzy), "i"), u.link_fuzzy_global = RegExp(r(u.tpl_link_fuzzy), "ig"), u.link_no_ip_fuzzy = RegExp(r(u.tpl_link_no_ip_fuzzy), "i"), u.link_no_ip_fuzzy_global = RegExp(r(u.tpl_link_no_ip_fuzzy), "ig"), u.host_fuzzy_test = RegExp(r(u.tpl_host_fuzzy_test), "i");
  const i = [];
  e.__compiled__ = {};
  function t(a, s) {
    throw new Error(`(LinkifyIt) Invalid schema "${a}": ${s}`);
  }
  Object.keys(e.__schemas__).forEach(function(a) {
    const s = e.__schemas__[a];
    if (s === null)
      return;
    const l = { validate: null, link: null };
    if (e.__compiled__[a] = l, ms(s)) {
      Ds(s.validate) ? l.validate = Fs(s.validate) : cr(s.validate) ? l.validate = s.validate : t(a, s), cr(s.normalize) ? l.normalize = s.normalize : s.normalize ? t(a, s) : l.normalize = lr();
      return;
    }
    if (gs(s)) {
      i.push(a);
      return;
    }
    t(a, s);
  }), i.forEach(function(a) {
    e.__compiled__[e.__schemas__[a]] && (e.__compiled__[a].validate = e.__compiled__[e.__schemas__[a]].validate, e.__compiled__[a].normalize = e.__compiled__[e.__schemas__[a]].normalize);
  }), e.__compiled__[""] = { validate: null, normalize: lr() };
  const o = Object.keys(e.__compiled__).filter(function(a) {
    return a.length > 0 && e.__compiled__[a];
  }).map(Es).join("|");
  e.re.schema_test = RegExp(`(^|(?!_)(?:[><｜]|${u.src_ZPCc}))(${o})`, "i"), e.re.schema_search = RegExp(`(^|(?!_)(?:[><｜]|${u.src_ZPCc}))(${o})`, "ig"), e.re.schema_at_start = RegExp(`^${e.re.schema_search.source}`, "i"), e.re.pretest = RegExp(
    `(${e.re.schema_test.source})|(${e.re.host_fuzzy_test.source})|@`,
    "i"
  );
}
function r0(e, u, n, r) {
  const i = e.slice(n, r);
  this.schema = u.toLowerCase(), this.index = n, this.lastIndex = r, this.raw = i, this.text = i, this.url = i;
}
function Ie(e, u) {
  if (!(this instanceof Ie))
    return new Ie(e, u);
  u || xs(e) && (u = e, e = {}), this.__opts__ = cn({}, n0, u), this.__schemas__ = cn({}, Cs, e), this.__compiled__ = {}, this.__tlds__ = _s, this.__tlds_replaced__ = !1, this.re = {}, pt(this);
}
Ie.prototype.add = function(u, n) {
  return this.__schemas__[u] = n, pt(this), this;
};
Ie.prototype.set = function(u) {
  return this.__opts__ = cn(this.__opts__, u), this;
};
Ie.prototype.test = function(u) {
  if (!u.length)
    return !1;
  let n, r;
  if (this.re.schema_test.test(u)) {
    for (r = this.re.schema_search, r.lastIndex = 0; (n = r.exec(u)) !== null; )
      if (this.testSchemaAt(u, n[2], r.lastIndex))
        return !0;
  }
  return !!(this.__opts__.fuzzyLink && this.__compiled__["http:"] && u.search(this.re.host_fuzzy_test) >= 0 && u.match(this.__opts__.fuzzyIP ? this.re.link_fuzzy : this.re.link_no_ip_fuzzy) !== null || this.__opts__.fuzzyEmail && this.__compiled__["mailto:"] && u.indexOf("@") >= 0 && u.match(this.re.email_fuzzy) !== null);
};
Ie.prototype.pretest = function(u) {
  return this.re.pretest.test(u);
};
Ie.prototype.testSchemaAt = function(u, n, r) {
  return this.__compiled__[n.toLowerCase()] ? this.__compiled__[n.toLowerCase()].validate(u, r, this) : 0;
};
Ie.prototype.match = function(u) {
  const n = [], r = [], i = [], t = [];
  let o, a, s;
  function l(h, p) {
    return h ? p ? h.index !== p.index ? h.index < p.index ? h : p : h.lastIndex >= p.lastIndex ? h : p : h : p;
  }
  if (!u.length)
    return null;
  if (this.re.schema_test.test(u))
    for (s = this.re.schema_search, s.lastIndex = 0; (o = s.exec(u)) !== null; )
      a = this.testSchemaAt(u, o[2], s.lastIndex), a && r.push({
        schema: o[2],
        index: o.index + o[1].length,
        lastIndex: o.index + o[0].length + a
      });
  if (this.__opts__.fuzzyLink && this.__compiled__["http:"])
    for (s = this.__opts__.fuzzyIP ? this.re.link_fuzzy_global : this.re.link_no_ip_fuzzy_global, s.lastIndex = 0; (o = s.exec(u)) !== null; )
      i.push({
        schema: "",
        index: o.index + o[1].length,
        lastIndex: o.index + o[0].length
      });
  if (this.__opts__.fuzzyEmail && this.__compiled__["mailto:"])
    for (s = this.re.email_fuzzy_global, s.lastIndex = 0; (o = s.exec(u)) !== null; )
      t.push({
        schema: "mailto:",
        index: o.index + o[1].length,
        lastIndex: o.index + o[0].length
      });
  const f = [0, 0, 0];
  let c = 0;
  for (; ; ) {
    const h = [
      r[f[0]],
      t[f[1]],
      i[f[2]]
    ], p = l(l(h[0], h[1]), h[2]);
    if (!p)
      break;
    if (p === h[0] ? f[0]++ : p === h[1] ? f[1]++ : f[2]++, p.index < c)
      continue;
    const d = new r0(u, p.schema, p.index, p.lastIndex);
    this.__compiled__[d.schema].normalize(d, this), n.push(d), c = p.lastIndex;
  }
  return n.length ? n : null;
};
Ie.prototype.matchAtStart = function(u) {
  if (!u.length) return null;
  const n = this.re.schema_at_start.exec(u);
  if (!n) return null;
  const r = this.testSchemaAt(u, n[2], n[0].length);
  if (!r) return null;
  const i = new r0(u, n[2], n.index + n[1].length, n.index + n[0].length + r);
  return this.__compiled__[i.schema].normalize(i, this), i;
};
Ie.prototype.tlds = function(u, n) {
  return u = Array.isArray(u) ? u : [u], n ? (this.__tlds__ = this.__tlds__.concat(u).sort().filter(function(r, i, t) {
    return r !== t[i - 1];
  }).reverse(), pt(this), this) : (this.__tlds__ = u.slice(), this.__tlds_replaced__ = !0, pt(this), this);
};
Ie.prototype.normalize = function(u) {
  u.schema || (u.url = `http://${u.url}`), u.schema === "mailto:" && !/^mailto:/i.test(u.url) && (u.url = `mailto:${u.url}`);
};
Ie.prototype.onCompile = function() {
};
const vu = 2147483647, Ze = 36, xn = 1, Uu = 26, ys = 38, ks = 700, i0 = 72, o0 = 128, a0 = "-", vs = /^xn--/, ws = /[^\0-\x7F]/, Bs = /[\x2E\u3002\uFF0E\uFF61]/g, Ss = {
  overflow: "Overflow: input needs wider integers to process",
  "not-basic": "Illegal input >= 0x80 (not a basic code point)",
  "invalid-input": "Invalid input"
}, jt = Ze - xn, Ge = Math.floor, qt = String.fromCharCode;
function cu(e) {
  throw new RangeError(Ss[e]);
}
function Ts(e, u) {
  const n = [];
  let r = e.length;
  for (; r--; )
    n[r] = u(e[r]);
  return n;
}
function s0(e, u) {
  const n = e.split("@");
  let r = "";
  n.length > 1 && (r = n[0] + "@", e = n[1]), e = e.replace(Bs, ".");
  const i = e.split("."), t = Ts(i, u).join(".");
  return r + t;
}
function c0(e) {
  const u = [];
  let n = 0;
  const r = e.length;
  for (; n < r; ) {
    const i = e.charCodeAt(n++);
    if (i >= 55296 && i <= 56319 && n < r) {
      const t = e.charCodeAt(n++);
      (t & 64512) == 56320 ? u.push(((i & 1023) << 10) + (t & 1023) + 65536) : (u.push(i), n--);
    } else
      u.push(i);
  }
  return u;
}
const Is = (e) => String.fromCodePoint(...e), Ms = function(e) {
  return e >= 48 && e < 58 ? 26 + (e - 48) : e >= 65 && e < 91 ? e - 65 : e >= 97 && e < 123 ? e - 97 : Ze;
}, fr = function(e, u) {
  return e + 22 + 75 * (e < 26) - ((u != 0) << 5);
}, l0 = function(e, u, n) {
  let r = 0;
  for (e = n ? Ge(e / ks) : e >> 1, e += Ge(e / u); e > jt * Uu >> 1; r += Ze)
    e = Ge(e / jt);
  return Ge(r + (jt + 1) * e / (e + ys));
}, f0 = function(e) {
  const u = [], n = e.length;
  let r = 0, i = o0, t = i0, o = e.lastIndexOf(a0);
  o < 0 && (o = 0);
  for (let a = 0; a < o; ++a)
    e.charCodeAt(a) >= 128 && cu("not-basic"), u.push(e.charCodeAt(a));
  for (let a = o > 0 ? o + 1 : 0; a < n; ) {
    const s = r;
    for (let f = 1, c = Ze; ; c += Ze) {
      a >= n && cu("invalid-input");
      const h = Ms(e.charCodeAt(a++));
      h >= Ze && cu("invalid-input"), h > Ge((vu - r) / f) && cu("overflow"), r += h * f;
      const p = c <= t ? xn : c >= t + Uu ? Uu : c - t;
      if (h < p)
        break;
      const d = Ze - p;
      f > Ge(vu / d) && cu("overflow"), f *= d;
    }
    const l = u.length + 1;
    t = l0(r - s, l, s == 0), Ge(r / l) > vu - i && cu("overflow"), i += Ge(r / l), r %= l, u.splice(r++, 0, i);
  }
  return String.fromCodePoint(...u);
}, d0 = function(e) {
  const u = [];
  e = c0(e);
  const n = e.length;
  let r = o0, i = 0, t = i0;
  for (const s of e)
    s < 128 && u.push(qt(s));
  const o = u.length;
  let a = o;
  for (o && u.push(a0); a < n; ) {
    let s = vu;
    for (const f of e)
      f >= r && f < s && (s = f);
    const l = a + 1;
    s - r > Ge((vu - i) / l) && cu("overflow"), i += (s - r) * l, r = s;
    for (const f of e)
      if (f < r && ++i > vu && cu("overflow"), f === r) {
        let c = i;
        for (let h = Ze; ; h += Ze) {
          const p = h <= t ? xn : h >= t + Uu ? Uu : h - t;
          if (c < p)
            break;
          const d = c - p, x = Ze - p;
          u.push(
            qt(fr(p + d % x, 0))
          ), c = Ge(d / x);
        }
        u.push(qt(fr(c, 0))), t = l0(i, l, a === o), i = 0, ++a;
      }
    ++i, ++r;
  }
  return u.join("");
}, Os = function(e) {
  return s0(e, function(u) {
    return vs.test(u) ? f0(u.slice(4).toLowerCase()) : u;
  });
}, Ns = function(e) {
  return s0(e, function(u) {
    return ws.test(u) ? "xn--" + d0(u) : u;
  });
}, p0 = {
  /**
   * A string representing the current Punycode.js version number.
   * @memberOf punycode
   * @type String
   */
  version: "2.3.1",
  /**
   * An object of methods to convert from JavaScript's internal character
   * representation (UCS-2) to Unicode code points, and back.
   * @see <https://mathiasbynens.be/notes/javascript-encoding>
   * @memberOf punycode
   * @type Object
   */
  ucs2: {
    decode: c0,
    encode: Is
  },
  decode: f0,
  encode: d0,
  toASCII: Ns,
  toUnicode: Os
}, Rs = {
  options: {
    // Enable HTML tags in source
    html: !1,
    // Use '/' to close single tags (<br />)
    xhtmlOut: !1,
    // Convert '\n' in paragraphs into <br>
    breaks: !1,
    // CSS language prefix for fenced blocks
    langPrefix: "language-",
    // autoconvert URL-like texts to links
    linkify: !1,
    // Enable some language-neutral replacements + quotes beautification
    typographer: !1,
    // Double + single quotes replacement pairs, when typographer enabled,
    // and smartquotes on. Could be either a String or an Array.
    //
    // For example, you can use '«»„“' for Russian, '„“‚‘' for German,
    // and ['«\xA0', '\xA0»', '‹\xA0', '\xA0›'] for French (including nbsp).
    quotes: "“”‘’",
    /* “”‘’ */
    // Highlighter function. Should return escaped HTML,
    // or '' if the source string is not changed and should be escaped externaly.
    // If result starts with <pre... internal wrapper is skipped.
    //
    // function (/*str, lang*/) { return ''; }
    //
    highlight: null,
    // Internal protection, recursion limit
    maxNesting: 100
  },
  components: {
    core: {},
    block: {},
    inline: {}
  }
}, $s = {
  options: {
    // Enable HTML tags in source
    html: !1,
    // Use '/' to close single tags (<br />)
    xhtmlOut: !1,
    // Convert '\n' in paragraphs into <br>
    breaks: !1,
    // CSS language prefix for fenced blocks
    langPrefix: "language-",
    // autoconvert URL-like texts to links
    linkify: !1,
    // Enable some language-neutral replacements + quotes beautification
    typographer: !1,
    // Double + single quotes replacement pairs, when typographer enabled,
    // and smartquotes on. Could be either a String or an Array.
    //
    // For example, you can use '«»„“' for Russian, '„“‚‘' for German,
    // and ['«\xA0', '\xA0»', '‹\xA0', '\xA0›'] for French (including nbsp).
    quotes: "“”‘’",
    /* “”‘’ */
    // Highlighter function. Should return escaped HTML,
    // or '' if the source string is not changed and should be escaped externaly.
    // If result starts with <pre... internal wrapper is skipped.
    //
    // function (/*str, lang*/) { return ''; }
    //
    highlight: null,
    // Internal protection, recursion limit
    maxNesting: 20
  },
  components: {
    core: {
      rules: [
        "normalize",
        "block",
        "inline",
        "text_join"
      ]
    },
    block: {
      rules: [
        "paragraph"
      ]
    },
    inline: {
      rules: [
        "text"
      ],
      rules2: [
        "balance_pairs",
        "fragments_join"
      ]
    }
  }
}, Ls = {
  options: {
    // Enable HTML tags in source
    html: !0,
    // Use '/' to close single tags (<br />)
    xhtmlOut: !0,
    // Convert '\n' in paragraphs into <br>
    breaks: !1,
    // CSS language prefix for fenced blocks
    langPrefix: "language-",
    // autoconvert URL-like texts to links
    linkify: !1,
    // Enable some language-neutral replacements + quotes beautification
    typographer: !1,
    // Double + single quotes replacement pairs, when typographer enabled,
    // and smartquotes on. Could be either a String or an Array.
    //
    // For example, you can use '«»„“' for Russian, '„“‚‘' for German,
    // and ['«\xA0', '\xA0»', '‹\xA0', '\xA0›'] for French (including nbsp).
    quotes: "“”‘’",
    /* “”‘’ */
    // Highlighter function. Should return escaped HTML,
    // or '' if the source string is not changed and should be escaped externaly.
    // If result starts with <pre... internal wrapper is skipped.
    //
    // function (/*str, lang*/) { return ''; }
    //
    highlight: null,
    // Internal protection, recursion limit
    maxNesting: 20
  },
  components: {
    core: {
      rules: [
        "normalize",
        "block",
        "inline",
        "text_join"
      ]
    },
    block: {
      rules: [
        "blockquote",
        "code",
        "fence",
        "heading",
        "hr",
        "html_block",
        "lheading",
        "list",
        "reference",
        "paragraph"
      ]
    },
    inline: {
      rules: [
        "autolink",
        "backticks",
        "emphasis",
        "entity",
        "escape",
        "html_inline",
        "image",
        "link",
        "newline",
        "text"
      ],
      rules2: [
        "balance_pairs",
        "emphasis",
        "fragments_join"
      ]
    }
  }
}, zs = {
  default: Rs,
  zero: $s,
  commonmark: Ls
}, Ps = /^(vbscript|javascript|file|data):/, Hs = /^data:image\/(gif|png|jpeg|webp);/;
function js(e) {
  const u = e.trim().toLowerCase();
  return Ps.test(u) ? Hs.test(u) : !0;
}
const h0 = ["http:", "https:", "mailto:"];
function qs(e) {
  const u = hn(e, !0);
  if (u.hostname && (!u.protocol || h0.indexOf(u.protocol) >= 0))
    try {
      u.hostname = p0.toASCII(u.hostname);
    } catch {
    }
  return Gu(pn(u));
}
function Us(e) {
  const u = hn(e, !0);
  if (u.hostname && (!u.protocol || h0.indexOf(u.protocol) >= 0))
    try {
      u.hostname = p0.toUnicode(u.hostname);
    } catch {
    }
  return Bu(pn(u), Bu.defaultChars + "%");
}
function Ne(e, u) {
  if (!(this instanceof Ne))
    return new Ne(e, u);
  u || gn(e) || (u = e || {}, e = "default"), this.inline = new Wu(), this.block = new Ct(), this.core = new Dn(), this.renderer = new Tu(), this.linkify = new Ie(), this.validateLink = js, this.normalizeLink = qs, this.normalizeLinkText = Us, this.utils = Zo, this.helpers = Dt({}, Ko), this.options = {}, this.configure(e), u && this.set(u);
}
Ne.prototype.set = function(e) {
  return Dt(this.options, e), this;
};
Ne.prototype.configure = function(e) {
  const u = this;
  if (gn(e)) {
    const n = e;
    if (e = zs[n], !e)
      throw new Error('Wrong `markdown-it` preset "' + n + '", check name');
  }
  if (!e)
    throw new Error("Wrong `markdown-it` preset, can't be empty");
  return e.options && u.set(e.options), e.components && Object.keys(e.components).forEach(function(n) {
    e.components[n].rules && u[n].ruler.enableOnly(e.components[n].rules), e.components[n].rules2 && u[n].ruler2.enableOnly(e.components[n].rules2);
  }), this;
};
Ne.prototype.enable = function(e, u) {
  let n = [];
  Array.isArray(e) || (e = [e]), ["core", "block", "inline"].forEach(function(i) {
    n = n.concat(this[i].ruler.enable(e, !0));
  }, this), n = n.concat(this.inline.ruler2.enable(e, !0));
  const r = e.filter(function(i) {
    return n.indexOf(i) < 0;
  });
  if (r.length && !u)
    throw new Error("MarkdownIt. Failed to enable unknown rule(s): " + r);
  return this;
};
Ne.prototype.disable = function(e, u) {
  let n = [];
  Array.isArray(e) || (e = [e]), ["core", "block", "inline"].forEach(function(i) {
    n = n.concat(this[i].ruler.disable(e, !0));
  }, this), n = n.concat(this.inline.ruler2.disable(e, !0));
  const r = e.filter(function(i) {
    return n.indexOf(i) < 0;
  });
  if (r.length && !u)
    throw new Error("MarkdownIt. Failed to disable unknown rule(s): " + r);
  return this;
};
Ne.prototype.use = function(e) {
  const u = [this].concat(Array.prototype.slice.call(arguments, 1));
  return e.apply(e, u), this;
};
Ne.prototype.parse = function(e, u) {
  if (typeof e != "string")
    throw new Error("Input data should be a String");
  const n = new this.core.State(e, this, u);
  return this.core.process(n), n.tokens;
};
Ne.prototype.render = function(e, u) {
  return u = u || {}, this.renderer.render(this.parse(e, u), this.options, u);
};
Ne.prototype.parseInline = function(e, u) {
  const n = new this.core.State(e, this, u);
  return n.inlineMode = !0, this.core.process(n), n.tokens;
};
Ne.prototype.renderInline = function(e, u) {
  return u = u || {}, this.renderer.render(this.parseInline(e, u), this.options, u);
};
const dr = /* @__PURE__ */ new Set([!0, !1, "alt", "title"]);
function b0(e, u) {
  return (Array.isArray(e) ? e : []).filter(([n]) => n !== u);
}
function g0(e, u) {
  e && e.attrs && (e.attrs = b0(e.attrs, u));
}
function Zs(e, u) {
  if (!dr.has(e)) throw new TypeError(`figcaption must be one of: ${[...dr]}.`);
  if (e === "alt") return u.content;
  const n = u.attrs.find(([r]) => r === "title");
  return Array.isArray(n) && n[1] ? (g0(u, "title"), n[1]) : void 0;
}
function Gs(e, u) {
  u = u || {}, e.core.ruler.before("linkify", "image_figures", function(n) {
    let r = 1;
    for (let i = 1, t = n.tokens.length; i < t - 1; ++i) {
      const o = n.tokens[i];
      if (o.type !== "inline" || !o.children || o.children.length !== 1 && o.children.length !== 3 || o.children.length === 1 && o.children[0].type !== "image") continue;
      if (o.children.length === 3) {
        const [l, f, c] = o.children;
        if (l.type !== "link_open" || f.type !== "image" || c.type !== "link_close") continue;
      }
      if (i !== 0 && n.tokens[i - 1].type !== "paragraph_open" || i !== t - 1 && n.tokens[i + 1].type !== "paragraph_close") continue;
      const a = n.tokens[i - 1];
      let s;
      if (a.type = "figure_open", a.tag = "figure", n.tokens[i + 1].type = "figure_close", n.tokens[i + 1].tag = "figure", u.dataType && n.tokens[i - 1].attrPush(["data-type", "image"]), u.link && o.children.length === 1) {
        [s] = o.children;
        const l = new n.Token("link_open", "a", 1);
        l.attrPush(["href", s.attrGet("src")]), o.children.unshift(l), o.children.push(new n.Token("link_close", "a", -1));
      }
      if (s = o.children.length === 1 ? o.children[0] : o.children[1], u.figcaption) {
        const l = Zs(u.figcaption, s);
        if (l) {
          const [f] = e.parseInline(l, n.env);
          o.children.push(new n.Token("figcaption_open", "figcaption", 1)), o.children.push(...f.children), o.children.push(new n.Token("figcaption_close", "figcaption", -1)), s.attrs && (s.attrs = b0(s.attrs, "title"));
        }
      }
      if (u.copyAttrs && s.attrs) {
        const l = u.copyAttrs === !0 ? "" : u.copyAttrs;
        a.attrs = s.attrs.filter(([f]) => f.match(l)).map((f) => Array.from(f));
      }
      if (u.tabindex && (n.tokens[i - 1].attrPush(["tabindex", r]), r++), u.lazy && (s.attrs.some(([l]) => l === "loading") || s.attrs.push(["loading", "lazy"])), u.async && (s.attrs.some(([l]) => l === "decoding") || s.attrs.push(["decoding", "async"])), u.classes && typeof u.classes == "string") {
        let l = !1;
        for (let f = 0, c = s.attrs.length; f < c && !l; f++) {
          const h = s.attrs[f];
          h[0] === "class" && (h[1] = `${h[1]} ${u.classes}`, l = !0);
        }
        l || s.attrs.push(["class", u.classes]);
      }
      if (u.removeSrc) {
        const l = s.attrs.find(([f]) => f === "src");
        s.attrs.push(["data-src", l[1]]), g0(s, "src");
      }
    }
  });
}
const Vs = /\\([ \\!"#$%&'()*+,./:;<=>?@[\]^_`{|}~-])/g;
function Ws(e, u) {
  const n = e.posMax, r = e.pos;
  if (e.src.charCodeAt(r) !== 126 || u || r + 2 >= n)
    return !1;
  e.pos = r + 1;
  let i = !1;
  for (; e.pos < n; ) {
    if (e.src.charCodeAt(e.pos) === 126) {
      i = !0;
      break;
    }
    e.md.inline.skipToken(e);
  }
  if (!i || r + 1 === e.pos)
    return e.pos = r, !1;
  const t = e.src.slice(r + 1, e.pos);
  if (t.match(/(^|[^\\])(\\\\)*\s/))
    return e.pos = r, !1;
  e.posMax = e.pos, e.pos = r + 1;
  const o = e.push("sub_open", "sub", 1);
  o.markup = "~";
  const a = e.push("text", "", 0);
  a.content = t.replace(Vs, "$1");
  const s = e.push("sub_close", "sub", -1);
  return s.markup = "~", e.pos = e.posMax + 1, e.posMax = n, !0;
}
function Ks(e) {
  e.inline.ruler.after("emphasis", "sub", Ws);
}
const Xs = /\\([ \\!"#$%&'()*+,./:;<=>?@[\]^_`{|}~-])/g;
function Js(e, u) {
  const n = e.posMax, r = e.pos;
  if (e.src.charCodeAt(r) !== 94 || u || r + 2 >= n)
    return !1;
  e.pos = r + 1;
  let i = !1;
  for (; e.pos < n; ) {
    if (e.src.charCodeAt(e.pos) === 94) {
      i = !0;
      break;
    }
    e.md.inline.skipToken(e);
  }
  if (!i || r + 1 === e.pos)
    return e.pos = r, !1;
  const t = e.src.slice(r + 1, e.pos);
  if (t.match(/(^|[^\\])(\\\\)*\s/))
    return e.pos = r, !1;
  e.posMax = e.pos, e.pos = r + 1;
  const o = e.push("sup_open", "sup", 1);
  o.markup = "^";
  const a = e.push("text", "", 0);
  a.content = t.replace(Xs, "$1");
  const s = e.push("sup_close", "sup", -1);
  return s.markup = "^", e.pos = e.posMax + 1, e.posMax = n, !0;
}
function Qs(e) {
  e.inline.ruler.after("emphasis", "sup", Js);
}
/*! medium-zoom 1.1.0 | MIT License | https://github.com/francoischalifour/medium-zoom */
var Au = Object.assign || function(e) {
  for (var u = 1; u < arguments.length; u++) {
    var n = arguments[u];
    for (var r in n)
      Object.prototype.hasOwnProperty.call(n, r) && (e[r] = n[r]);
  }
  return e;
}, it = function(u) {
  return u.tagName === "IMG";
}, Ys = function(u) {
  return NodeList.prototype.isPrototypeOf(u);
}, ct = function(u) {
  return u && u.nodeType === 1;
}, pr = function(u) {
  var n = u.currentSrc || u.src;
  return n.substr(-4).toLowerCase() === ".svg";
}, hr = function(u) {
  try {
    return Array.isArray(u) ? u.filter(it) : Ys(u) ? [].slice.call(u).filter(it) : ct(u) ? [u].filter(it) : typeof u == "string" ? [].slice.call(document.querySelectorAll(u)).filter(it) : [];
  } catch {
    throw new TypeError(`The provided selector is invalid.
Expects a CSS selector, a Node element, a NodeList or an array.
See: https://github.com/francoischalifour/medium-zoom`);
  }
}, ec = function(u) {
  var n = document.createElement("div");
  return n.classList.add("medium-zoom-overlay"), n.style.background = u, n;
}, uc = function(u) {
  var n = u.getBoundingClientRect(), r = n.top, i = n.left, t = n.width, o = n.height, a = u.cloneNode(), s = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0, l = window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0;
  return a.removeAttribute("id"), a.style.position = "absolute", a.style.top = r + s + "px", a.style.left = i + l + "px", a.style.width = t + "px", a.style.height = o + "px", a.style.transform = "", a;
}, yu = function(u, n) {
  var r = Au({
    bubbles: !1,
    cancelable: !1,
    detail: void 0
  }, n);
  if (typeof window.CustomEvent == "function")
    return new CustomEvent(u, r);
  var i = document.createEvent("CustomEvent");
  return i.initCustomEvent(u, r.bubbles, r.cancelable, r.detail), i;
}, tc = function e(u) {
  var n = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {}, r = window.Promise || function(O) {
    function z() {
    }
    O(z, z);
  }, i = function(O) {
    var z = O.target;
    if (z === U) {
      d();
      return;
    }
    D.indexOf(z) !== -1 && x({ target: z });
  }, t = function() {
    if (!(A || !C.original)) {
      var O = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
      Math.abs(k - O) > B.scrollOffset && setTimeout(d, 150);
    }
  }, o = function(O) {
    var z = O.key || O.keyCode;
    (z === "Escape" || z === "Esc" || z === 27) && d();
  }, a = function() {
    var O = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {}, z = O;
    if (O.background && (U.style.background = O.background), O.container && O.container instanceof Object && (z.container = Au({}, B.container, O.container)), O.template) {
      var $ = ct(O.template) ? O.template : document.querySelector(O.template);
      z.template = $;
    }
    return B = Au({}, B, z), D.forEach(function(N) {
      N.dispatchEvent(yu("medium-zoom:update", {
        detail: { zoom: Z }
      }));
    }), Z;
  }, s = function() {
    var O = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
    return e(Au({}, B, O));
  }, l = function() {
    for (var O = arguments.length, z = Array(O), $ = 0; $ < O; $++)
      z[$] = arguments[$];
    var N = z.reduce(function(F, q) {
      return [].concat(F, hr(q));
    }, []);
    return N.filter(function(F) {
      return D.indexOf(F) === -1;
    }).forEach(function(F) {
      D.push(F), F.classList.add("medium-zoom-image");
    }), m.forEach(function(F) {
      var q = F.type, w = F.listener, G = F.options;
      N.forEach(function(Y) {
        Y.addEventListener(q, w, G);
      });
    }), Z;
  }, f = function() {
    for (var O = arguments.length, z = Array(O), $ = 0; $ < O; $++)
      z[$] = arguments[$];
    C.zoomed && d();
    var N = z.length > 0 ? z.reduce(function(F, q) {
      return [].concat(F, hr(q));
    }, []) : D;
    return N.forEach(function(F) {
      F.classList.remove("medium-zoom-image"), F.dispatchEvent(yu("medium-zoom:detach", {
        detail: { zoom: Z }
      }));
    }), D = D.filter(function(F) {
      return N.indexOf(F) === -1;
    }), Z;
  }, c = function(O, z) {
    var $ = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
    return D.forEach(function(N) {
      N.addEventListener("medium-zoom:" + O, z, $);
    }), m.push({ type: "medium-zoom:" + O, listener: z, options: $ }), Z;
  }, h = function(O, z) {
    var $ = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
    return D.forEach(function(N) {
      N.removeEventListener("medium-zoom:" + O, z, $);
    }), m = m.filter(function(N) {
      return !(N.type === "medium-zoom:" + O && N.listener.toString() === z.toString());
    }), Z;
  }, p = function() {
    var O = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {}, z = O.target, $ = function() {
      var F = {
        width: document.documentElement.clientWidth,
        height: document.documentElement.clientHeight,
        left: 0,
        top: 0,
        right: 0,
        bottom: 0
      }, q = void 0, w = void 0;
      if (B.container)
        if (B.container instanceof Object)
          F = Au({}, F, B.container), q = F.width - F.left - F.right - B.margin * 2, w = F.height - F.top - F.bottom - B.margin * 2;
        else {
          var G = ct(B.container) ? B.container : document.querySelector(B.container), Y = G.getBoundingClientRect(), ie = Y.width, ne = Y.height, ue = Y.left, ae = Y.top;
          F = Au({}, F, {
            width: ie,
            height: ne,
            left: ue,
            top: ae
          });
        }
      q = q || F.width - B.margin * 2, w = w || F.height - B.margin * 2;
      var pe = C.zoomedHd || C.original, Re = pr(pe) ? q : pe.naturalWidth || q, iu = pr(pe) ? w : pe.naturalHeight || w, $e = pe.getBoundingClientRect(), ou = $e.top, Ke = $e.left, bu = $e.width, Iu = $e.height, _t = Math.min(Math.max(bu, Re), q) / bu, Ft = Math.min(Math.max(Iu, iu), w) / Iu, Mu = Math.min(_t, Ft), yt = (-Ke + (q - bu) / 2 + B.margin + F.left) / Mu, kt = (-ou + (w - Iu) / 2 + B.margin + F.top) / Mu, Ku = "scale(" + Mu + ") translate3d(" + yt + "px, " + kt + "px, 0)";
      C.zoomed.style.transform = Ku, C.zoomedHd && (C.zoomedHd.style.transform = Ku);
    };
    return new r(function(N) {
      if (z && D.indexOf(z) === -1) {
        N(Z);
        return;
      }
      var F = function ie() {
        A = !1, C.zoomed.removeEventListener("transitionend", ie), C.original.dispatchEvent(yu("medium-zoom:opened", {
          detail: { zoom: Z }
        })), N(Z);
      };
      if (C.zoomed) {
        N(Z);
        return;
      }
      if (z)
        C.original = z;
      else if (D.length > 0) {
        var q = D;
        C.original = q[0];
      } else {
        N(Z);
        return;
      }
      if (C.original.dispatchEvent(yu("medium-zoom:open", {
        detail: { zoom: Z }
      })), k = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0, A = !0, C.zoomed = uc(C.original), document.body.appendChild(U), B.template) {
        var w = ct(B.template) ? B.template : document.querySelector(B.template);
        C.template = document.createElement("div"), C.template.appendChild(w.content.cloneNode(!0)), document.body.appendChild(C.template);
      }
      if (C.original.parentElement && C.original.parentElement.tagName === "PICTURE" && C.original.currentSrc && (C.zoomed.src = C.original.currentSrc), document.body.appendChild(C.zoomed), window.requestAnimationFrame(function() {
        document.body.classList.add("medium-zoom--opened");
      }), C.original.classList.add("medium-zoom-image--hidden"), C.zoomed.classList.add("medium-zoom-image--opened"), C.zoomed.addEventListener("click", d), C.zoomed.addEventListener("transitionend", F), C.original.getAttribute("data-zoom-src")) {
        C.zoomedHd = C.zoomed.cloneNode(), C.zoomedHd.removeAttribute("srcset"), C.zoomedHd.removeAttribute("sizes"), C.zoomedHd.removeAttribute("loading"), C.zoomedHd.src = C.zoomed.getAttribute("data-zoom-src"), C.zoomedHd.onerror = function() {
          clearInterval(G), console.warn("Unable to reach the zoom image target " + C.zoomedHd.src), C.zoomedHd = null, $();
        };
        var G = setInterval(function() {
          C.zoomedHd.complete && (clearInterval(G), C.zoomedHd.classList.add("medium-zoom-image--opened"), C.zoomedHd.addEventListener("click", d), document.body.appendChild(C.zoomedHd), $());
        }, 10);
      } else if (C.original.hasAttribute("srcset")) {
        C.zoomedHd = C.zoomed.cloneNode(), C.zoomedHd.removeAttribute("sizes"), C.zoomedHd.removeAttribute("loading");
        var Y = C.zoomedHd.addEventListener("load", function() {
          C.zoomedHd.removeEventListener("load", Y), C.zoomedHd.classList.add("medium-zoom-image--opened"), C.zoomedHd.addEventListener("click", d), document.body.appendChild(C.zoomedHd), $();
        });
      } else
        $();
    });
  }, d = function() {
    return new r(function(O) {
      if (A || !C.original) {
        O(Z);
        return;
      }
      var z = function $() {
        C.original.classList.remove("medium-zoom-image--hidden"), document.body.removeChild(C.zoomed), C.zoomedHd && document.body.removeChild(C.zoomedHd), document.body.removeChild(U), C.zoomed.classList.remove("medium-zoom-image--opened"), C.template && document.body.removeChild(C.template), A = !1, C.zoomed.removeEventListener("transitionend", $), C.original.dispatchEvent(yu("medium-zoom:closed", {
          detail: { zoom: Z }
        })), C.original = null, C.zoomed = null, C.zoomedHd = null, C.template = null, O(Z);
      };
      A = !0, document.body.classList.remove("medium-zoom--opened"), C.zoomed.style.transform = "", C.zoomedHd && (C.zoomedHd.style.transform = ""), C.template && (C.template.style.transition = "opacity 150ms", C.template.style.opacity = 0), C.original.dispatchEvent(yu("medium-zoom:close", {
        detail: { zoom: Z }
      })), C.zoomed.addEventListener("transitionend", z);
    });
  }, x = function() {
    var O = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {}, z = O.target;
    return C.original ? d() : p({ target: z });
  }, g = function() {
    return B;
  }, _ = function() {
    return D;
  }, E = function() {
    return C.original;
  }, D = [], m = [], A = !1, k = 0, B = n, C = {
    original: null,
    zoomed: null,
    zoomedHd: null,
    template: null
    // If the selector is omitted, it's replaced by the options
  };
  Object.prototype.toString.call(u) === "[object Object]" ? B = u : (u || typeof u == "string") && l(u), B = Au({
    margin: 0,
    background: "#fff",
    scrollOffset: 40,
    container: null,
    template: null
  }, B);
  var U = ec(B.background);
  document.addEventListener("click", i), document.addEventListener("keyup", o), document.addEventListener("scroll", t), window.addEventListener("resize", d);
  var Z = {
    open: p,
    close: d,
    toggle: x,
    update: a,
    clone: s,
    attach: l,
    detach: f,
    on: c,
    off: h,
    getOptions: g,
    getImages: _,
    getZoomedImage: E
  };
  return Z;
};
function nc(e, u) {
  u === void 0 && (u = {});
  var n = u.insertAt;
  if (!(typeof document > "u")) {
    var r = document.head || document.getElementsByTagName("head")[0], i = document.createElement("style");
    i.type = "text/css", n === "top" && r.firstChild ? r.insertBefore(i, r.firstChild) : r.appendChild(i), i.styleSheet ? i.styleSheet.cssText = e : i.appendChild(document.createTextNode(e));
  }
}
var rc = ".medium-zoom-overlay{position:fixed;top:0;right:0;bottom:0;left:0;opacity:0;transition:opacity .3s;will-change:opacity}.medium-zoom--opened .medium-zoom-overlay{cursor:pointer;cursor:zoom-out;opacity:1}.medium-zoom-image{cursor:pointer;cursor:zoom-in;transition:transform .3s cubic-bezier(.2,0,.2,1)!important}.medium-zoom-image--hidden{visibility:hidden}.medium-zoom-image--opened{position:relative;cursor:pointer;cursor:zoom-out;will-change:transform}";
nc(rc);
const ic = (e, u, n) => {
  const { editorId: r } = n, i = {
    rerender() {
      Me.emit(r, Lr);
    }
  };
  u.expose(i);
}, fu = {
  hljs: `${T}-hljs`,
  hlcss: `${T}-hlCss`,
  mermaidM: `${T}-mermaid-m`,
  mermaid: `${T}-mermaid`,
  katexjs: `${T}-katex`,
  katexcss: `${T}-katexCss`,
  echarts: `${T}-echarts`
}, oc = (e, {
  editorId: u,
  rootRef: n,
  setting: r
}) => {
  const i = Oe.editorExtensions.highlight, t = Oe.editorExtensionsAttrs.highlight;
  qe("editorId", u), qe("rootRef", n), qe(
    "theme",
    Qe(() => e.theme)
  ), qe(
    "language",
    Qe(() => e.language)
  ), qe(
    "highlight",
    Qe(() => {
      const { js: a } = i, s = {
        ...rn,
        ...i.css
      }, { js: l, css: f = {} } = t || {}, c = e.codeStyleReverse && e.codeStyleReverseList.includes(e.previewTheme) ? "dark" : e.theme, h = s[e.codeTheme] ? s[e.codeTheme][c] : rn.atom[c], p = s[e.codeTheme] && f[e.codeTheme] ? f[e.codeTheme][c] : f.atom ? f.atom[c] : {};
      return {
        js: {
          src: a,
          ...l
        },
        css: {
          href: h,
          ...p
        }
      };
    })
  ), qe("showCodeRowNumber", e.showCodeRowNumber);
  const o = Qe(() => {
    const a = {
      ...Zn,
      ...Oe.editorConfig.languageUserDefined
    };
    return gt(
      Nu(Zn["en-US"]),
      a[e.language] || {}
    );
  });
  return qe("usedLanguageText", o), qe(
    "previewTheme",
    Qe(() => e.previewTheme)
  ), qe(
    "customIcon",
    Qe(() => e.customIcon)
  ), qe(
    "setting",
    Qe(() => r ? {
      // setting是reactive，不转化是可以直接赋值的
      ...r
    } : {
      preview: !0,
      htmlPreview: !1,
      previewOnly: !1,
      pageFullscreen: !1,
      fullscreen: !1
    })
  ), { editorId: u };
}, ac = (e) => {
  const u = ii();
  return e.id || e.editorId || `${T}-${u}`;
}, sc = (e, u, n) => {
  const r = oe("editorId"), i = oe("rootRef"), t = oe("usedLanguageText"), o = oe("setting"), a = () => {
    i.value.querySelectorAll(`#${r} .${T}-preview .${T}-code`).forEach((f) => {
      let c = -1;
      const h = f.querySelector(
        `.${T}-copy-button:not([data-processed])`
      );
      h && (h.onclick = (p) => {
        p.preventDefault(), clearTimeout(c);
        const d = (f.querySelector("input:checked + pre code") || f.querySelector("pre code")).textContent, { text: x, successTips: g, failTips: _ } = t.value.copyCode;
        let E = g;
        jr(e.formatCopiedText(d || "")).catch(() => {
          E = _;
        }).finally(() => {
          h.dataset.isIcon ? h.dataset.tips = E : h.textContent = E, c = window.setTimeout(() => {
            h.dataset.isIcon ? h.dataset.tips = x : h.textContent = x;
          }, 1500);
        });
      }, h.setAttribute("data-processed", "true"));
    });
  }, s = () => {
    wu(a);
  }, l = (f) => {
    f && wu(a);
  };
  ve([u, n], s), ve(() => o.value.preview, l), ve(() => o.value.htmlPreview, l), nu(a);
}, cc = (e) => {
  const u = oe("editorId"), n = oe("theme"), r = oe("rootRef"), { editorExtensions: i, editorExtensionsAttrs: t, echartsConfig: o } = Oe;
  let a = i.echarts.instance;
  const s = bt(-1), l = () => {
    !e.noEcharts && a && (s.value = s.value + 1);
  };
  ve(
    () => n.value,
    () => {
      l();
    }
  ), nu(() => {
    if (e.noEcharts || a)
      return;
    const x = i.echarts.js;
    pu(
      "script",
      {
        ...t.echarts?.js,
        src: x,
        id: fu.echarts,
        onload() {
          a = window.echarts, l();
        }
      },
      "echarts"
    );
  });
  let f = [], c = [], h = [];
  const p = (x = !1) => {
    if (!f.length) {
      x && (c.forEach((D) => {
        D.dispose?.();
      }), h.forEach((D) => {
        D.disconnect?.();
      }), c = [], h = []);
      return;
    }
    const g = [], _ = [], E = [];
    f.forEach((D, m) => {
      const A = c[m], k = h[m];
      if (x || !D || !D.isConnected || r?.value && !r.value.contains(D)) {
        A?.dispose?.(), k?.disconnect?.();
        return;
      }
      g.push(D), A && _.push(A), k && E.push(k);
    }), f = g, c = _, h = E;
  }, d = () => {
    if (p(), !e.noEcharts && a) {
      const x = r.value;
      if (!x) return;
      Array.from(
        x.querySelectorAll(`div.${T}-echarts:not([data-processed])`)
      ).forEach((g) => {
        if (g.dataset.closed === "false")
          return !1;
        const _ = g.textContent || "";
        let E, D;
        try {
          const m = {
            editorId: u,
            element: g
          }, A = i.echarts.parseOption(_, m), k = i.echarts.sanitizeOption(
            o(A),
            m
          );
          E = a.init(g, n.value), E.setOption(k), D = new ResizeObserver(() => {
            E.resize();
          }), D.observe(g), g.setAttribute("data-processed", ""), f.push(g), c.push(E), h.push(D);
        } catch (m) {
          E && (D?.disconnect(), E.dispose(), g.textContent = _), Me.emit(u, on, {
            name: "echarts",
            message: m?.message,
            error: m
          });
        }
      });
    }
  };
  return Zu(() => {
    p(!0);
  }), { reRenderEcharts: s, replaceEcharts: d };
}, lc = (e) => {
  const u = oe("highlight"), n = bt(Oe.editorExtensions.highlight.instance);
  return nu(() => {
    e.noHighlight || n.value || (pu("link", {
      ...u.value.css,
      rel: "stylesheet",
      id: fu.hlcss
    }), pu(
      "script",
      {
        ...u.value.js,
        id: fu.hljs,
        onload() {
          n.value = window.hljs;
        }
      },
      "hljs"
    ));
  }), ve(
    () => u.value.css,
    () => {
      e.noHighlight || Oe.editorExtensions.highlight.instance || Qi("link", {
        ...u.value.css,
        rel: "stylesheet",
        id: fu.hlcss
      });
    }
  ), n;
}, fc = (e) => {
  const u = bt(Oe.editorExtensions.katex.instance);
  return nu(() => {
    if (e.noKatex || u.value)
      return;
    const { editorExtensions: n, editorExtensionsAttrs: r } = Oe;
    pu(
      "script",
      {
        ...r.katex?.js,
        src: n.katex.js,
        id: fu.katexjs,
        onload() {
          u.value = window.katex;
        }
      },
      "katex"
    ), pu("link", {
      ...r.katex?.css,
      rel: "stylesheet",
      href: n.katex.css,
      id: fu.katexcss
    });
  }), u;
};
class dc {
  constructor(u) {
    this.options = u;
  }
  options;
  cache = /* @__PURE__ */ new Map();
  isExpired(u) {
    return u.expiresAt <= Date.now();
  }
  deleteExpired(u, n) {
    const r = n ?? this.cache.get(u);
    return !r || !this.isExpired(r) ? !1 : (this.cache.delete(u), !0);
  }
  trim() {
    for (const [u, n] of this.cache)
      if (!this.deleteExpired(u, n))
        break;
    for (; this.cache.size > this.options.max; ) {
      const u = this.cache.keys().next().value;
      if (u === void 0)
        break;
      this.cache.delete(u);
    }
  }
  get(u) {
    const n = this.cache.get(u);
    if (!(!n || this.deleteExpired(u, n)))
      return this.cache.delete(u), this.cache.set(u, n), n.value;
  }
  set(u, n) {
    this.cache.delete(u), this.cache.set(u, {
      value: n,
      expiresAt: Date.now() + this.options.ttl
    }), this.trim();
  }
  clear() {
    this.cache.clear();
  }
}
const br = /* @__PURE__ */ new WeakMap(), pc = (e, u) => {
  const n = (br.get(e) || Promise.resolve()).then(u);
  return br.set(
    e,
    n.then(
      () => {
      },
      () => {
      }
    )
  ), n;
}, hc = (e) => {
  const u = oe("editorId"), n = oe("theme"), r = oe("rootRef"), { editorExtensions: i, editorExtensionsAttrs: t, mermaidConfig: o } = Oe;
  let a = i.mermaid.instance;
  const s = bt(0), l = new dc({ max: 1e3, ttl: 6e5 }), f = /* @__PURE__ */ new WeakSet(), c = /* @__PURE__ */ new WeakMap();
  let h = 0, p = !1;
  const d = (_, E) => l.get(JSON.stringify([s.value, _, E])), x = () => {
    h += 1, l.clear(), s.value += 1;
  };
  ve([n, () => e.sanitizeMermaid], x, { flush: "sync" }), ve(
    () => e.modelValue,
    () => {
      h += 1;
    },
    { flush: "sync" }
  ), nu(() => {
    if (e.noMermaid || a)
      return;
    const _ = i.mermaid.js, E = (D) => {
      p || (a = D, x());
    };
    /\.mjs/.test(_) ? (pu("link", {
      ...t.mermaid?.js,
      rel: "modulepreload",
      href: _,
      id: fu.mermaidM
    }), import(
      /* @vite-ignore */
      /* webpackIgnore: true */
      _
    ).then((D) => E(D.default)).catch((D) => {
      p || Me.emit(u, on, {
        name: "mermaid",
        message: `Failed to load mermaid module: ${D.message}`,
        error: D
      });
    })) : pu(
      "script",
      {
        ...t.mermaid?.js,
        src: _,
        id: fu.mermaid,
        onload() {
          E(window.mermaid);
        }
      },
      "mermaid"
    );
  });
  const g = async () => {
    const _ = r.value;
    if (p || e.noMermaid || !a || !_) return;
    const E = a, D = _.querySelectorAll(
      `div.${T}-mermaid, p.${T}-mermaid[data-processed]`
    );
    await Promise.all(
      Array.from(D).filter((m) => m.dataset.closed !== "false" && m.dataset.mermaidRevision === String(s.value) && !f.has(m)).map((m) => {
        const A = c.get(m);
        if (A?.generation === h) return A.promise;
        const k = (async () => {
          const B = h, C = s.value, U = m.dataset.content ?? m.textContent ?? "", Z = m.dataset.mermaidBlock, H = JSON.stringify([C, Z, U]), O = e.sanitizeMermaid, z = n.value, $ = () => !p && h === B && m.isConnected && _.contains(m);
          if ($())
            try {
              let N = l.get(H);
              if (N && m.dataset.processed !== void 0) {
                N.bindFunctions?.(m), f.add(m);
                return;
              }
              if (!N) {
                const w = await pc(E, async () => {
                  if (!$()) return;
                  const Y = {
                    startOnLoad: !1,
                    securityLevel: "strict",
                    secure: Array.from(
                      /* @__PURE__ */ new Set([
                        ...E.mermaidAPI?.defaultConfig?.secure || [],
                        "secure",
                        "securityLevel",
                        "startOnLoad",
                        "maxTextSize",
                        "suppressErrorRendering",
                        "maxEdges",
                        "dompurifyConfig"
                      ])
                    )
                  }, ie = {
                    ...Y,
                    ...z === "dark" ? { theme: "dark" } : {
                      theme: "base",
                      themeVariables: {
                        background: "#ffffff",
                        primaryColor: "#ffffff",
                        primaryTextColor: "#1f2329",
                        primaryBorderColor: "#b7c0cc",
                        secondaryColor: "#f7f8fa",
                        tertiaryColor: "#f7f8fa",
                        lineColor: "#596273",
                        edgeLabelBackground: "#ffffff",
                        clusterBkg: "#ffffff",
                        clusterBorder: "#b7c0cc"
                      }
                    }
                  }, ne = o(ie);
                  E.initialize({
                    ...ne,
                    // 返回部分主题配置时仍继承安全默认值，显式覆盖则尊重调用方选择。
                    startOnLoad: ne?.startOnLoad ?? !1,
                    securityLevel: ne?.securityLevel ?? "strict",
                    secure: ne?.secure ?? Y.secure
                  });
                  const ue = document.createElement("div");
                  ue.style.cssText = "position:fixed;z-index:-10000;top:-10000px;left:-10000px;", ue.style.width = Math.max(document.body.offsetWidth, 1366) + "px", ue.style.height = Math.max(document.body.offsetHeight, 768) + "px", document.body.appendChild(ue);
                  try {
                    return await E.render(Qt(), U, ue);
                  } finally {
                    ue.remove();
                  }
                });
                if (!w || !$()) return;
                const G = O ? await O(w.svg) : w.svg;
                if (!$()) return;
                if (typeof G != "string")
                  throw new TypeError("Mermaid sanitizer must return a string.");
                N = { svg: G, bindFunctions: w.bindFunctions };
              }
              if (!$()) return;
              const F = document.createElement("p");
              for (const w of Array.from(m.attributes))
                F.setAttribute(w.name, w.value);
              F.setAttribute("data-processed", ""), F.setAttribute("data-content", U), F.innerHTML = N.svg, F.children[0]?.removeAttribute("height");
              const q = { ...N, svg: F.innerHTML };
              m.replaceWith(F);
              try {
                N.bindFunctions?.(F), f.add(F);
              } catch (w) {
                throw F.replaceWith(m), w;
              }
              l.set(H, q);
            } catch (N) {
              if (!$()) return;
              m.dataset.processed !== void 0 && (m.textContent = U, m.removeAttribute("data-processed")), Me.emit(u, on, {
                name: "mermaid",
                message: N?.message,
                error: N
              });
            }
        })().finally(() => {
          c.get(m)?.promise === k && c.delete(m);
        });
        return c.set(m, { generation: h, promise: k }), k;
      })
    );
  };
  return Zu(() => {
    p = !0, h += 1, l.clear();
  }), { reRenderRef: s, replaceMermaid: g, invalidateMermaid: x, getCachedMermaid: d };
}, bc = (e, u) => {
  u = u || {};
  const n = 3, r = u.marker || "!", i = r.charCodeAt(0), t = r.length;
  let o = "", a = "";
  const s = (f, c, h, p, d) => {
    const x = f[c];
    return x.type === "admonition_open" ? f[c].attrPush([
      "class",
      `${T}-admonition ${T}-admonition-${x.info}`
    ]) : x.type === "admonition_title_open" && f[c].attrPush(["class", `${T}-admonition-title`]), d.renderToken(f, c, h);
  }, l = (f) => {
    const c = f.trim().split(" ", 2);
    a = "", o = c[0], c.length > 1 && (a = f.substring(o.length + 2));
  };
  e.block.ruler.before(
    "code",
    "admonition",
    (f, c, h, p) => {
      let d, x, g, _ = !1, E = f.bMarks[c] + f.tShift[c], D = f.eMarks[c];
      if (i !== f.src.charCodeAt(E))
        return !1;
      for (d = E + 1; d <= D && r[(d - E) % t] === f.src[d]; d++)
        ;
      const m = Math.floor((d - E) / t);
      if (m !== n)
        return !1;
      d -= (d - E) % t;
      const A = f.src.slice(E, d), k = f.src.slice(d, D);
      if (l(k), p)
        return !0;
      for (x = c; x++, !(x >= h || (E = f.bMarks[x] + f.tShift[x], D = f.eMarks[x], E < D && f.sCount[x] < f.blkIndent)); )
        if (i === f.src.charCodeAt(E) && !(f.sCount[x] - f.blkIndent >= 4)) {
          for (d = E + 1; d <= D && r[(d - E) % t] === f.src[d]; d++)
            ;
          if (!(Math.floor((d - E) / t) < m) && (d -= (d - E) % t, d = f.skipSpaces(d), !(d < D))) {
            _ = !0;
            break;
          }
        }
      const B = f.parentType, C = f.lineMax;
      return f.parentType = "root", f.lineMax = x, g = f.push("admonition_open", "div", 1), g.markup = A, g.block = !0, g.info = o, g.map = [c, x], a && (g = f.push("admonition_title_open", "p", 1), g.markup = A + " " + o, g.map = [c, x], g = f.push("inline", "", 0), g.content = a, g.map = [c, f.line - 1], g.children = [], g = f.push("admonition_title_close", "p", -1), g.markup = A + " " + o), f.md.block.tokenize(f, c + 1, x), g = f.push("admonition_close", "div", -1), g.markup = f.src.slice(E, d), g.block = !0, f.parentType = B, f.lineMax = C, f.line = x + (_ ? 1 : 0), !0;
    },
    {
      alt: ["paragraph", "reference", "blockquote", "list"]
    }
  ), e.renderer.rules.admonition_open = s, e.renderer.rules.admonition_title_open = s, e.renderer.rules.admonition_title_close = s, e.renderer.rules.admonition_close = s;
}, gc = (e) => Array.from(e).map(
  (u) => /[A-Za-z0-9_-]/.test(u) ? u : `_${u.codePointAt(0).toString(16)}_`
).join(""), mc = (e, u) => {
  const n = e.renderer.rules.fence, r = e.utils.unescapeAll, i = /\[(\w*)(?::([\w ]*))?\]/, t = /::(open|close)/, o = gc(u.editorId), a = (h) => h.info ? r(h.info).trim() : "", s = (h) => {
    const p = a(h), [d = null, x = ""] = (i.exec(p) || []).slice(1);
    return [d, x];
  }, l = (h) => at(a(h)).language, f = (h) => {
    const p = h.info.match(t) || [], d = p[1] === "open" || p[1] !== "close" && u.codeFoldable && h.content.trim().split(`
`).length < u.autoFoldThreshold, x = p[1] || u.codeFoldable ? "details" : "div", g = p[1] || u.codeFoldable ? "summary" : "div";
    return { open: d, tagContainer: x, tagHeader: g };
  }, c = (h, p, d, x, g) => {
    if (h[p].hidden)
      return "";
    const _ = u.usedLanguageTextRef.value?.copyCode.text || "", E = u.customIconRef.value.copy, D = e.utils.escapeHtml(_), m = E || D, A = !!E, k = `<span class="${T}-collapse-tips">${Ue("collapse-tips", u.customIconRef.value)}</span>`, [B] = s(h[p]);
    if (B === null) {
      const { open: ue, tagContainer: ae, tagHeader: pe } = f(h[p]), Re = [["class", `${T}-code`]];
      ue && Re.push(["open", ""]);
      const iu = {
        attrs: Pn(h[p], Re)
      };
      h[p].info = h[p].info.replace(t, "");
      const { attrs: $e, language: ou } = at(
        a(h[p])
      ), Ke = [ou, $e].filter(Boolean).join(" "), bu = n(h, p, d, x, g);
      return `
        <${ae} ${g.renderAttrs(iu)}>
          <${pe} class="${T}-code-head">
            <div class="${T}-code-flag"><span></span><span></span><span></span></div>
            <div class="${T}-code-action">
              <span class="${T}-code-lang">${e.utils.escapeHtml(Ke)}</span>
              <span class="${T}-copy-button" data-tips="${D}"${A ? " data-is-icon=true" : ""}>${m}</span>
              ${u.extraTools instanceof Function ? u.extraTools({ lang: Ke }) : u.extraTools || ""}
              ${ae === "details" ? k : ""}
            </div>
          </${pe}>
          ${bu}
        </${ae}>
      `;
    }
    let C, U, Z, H, O = "", z = "", $ = "";
    const { open: N, tagContainer: F, tagHeader: q } = f(h[p]), w = [["class", `${T}-code`]];
    N && w.push(["open", ""]);
    const G = {
      attrs: Pn(h[p], w)
    };
    for (let ue = p; ue < h.length && (C = h[ue], [U, Z] = s(C), U === B); ue++) {
      C.info = C.info.replace(i, "").replace(t, ""), C.hidden = !0;
      const ae = `${T}-codetab-${o}-${p}-${ue - p}`;
      H = ue - p > 0 ? "" : "checked", O += `
        <li>
          <input
            type="radio"
            id="label-${T}-codetab-label-1-${o}-${p}-${ue - p}"
            name="${T}-codetab-label-${o}-${p}"
            class="${ae}"
            ${H}
          >
          <label
            for="label-${T}-codetab-label-1-${o}-${p}-${ue - p}"
            onclick="this.getRootNode().querySelectorAll('.${ae}').forEach(e => e.click())"
          >
            ${e.utils.escapeHtml(Z || l(C))}
          </label>
        </li>`, z += `
        <div role="tabpanel">
          <input
            type="radio"
            name="${T}-codetab-pre-${o}-${p}"
            class="${ae}"
            ${H}
            role="presentation">
          ${n(h, ue, d, x, g)}
        </div>`, $ += `
        <input
          type="radio"
          name="${T}-codetab-lang-${o}-${p}"
          class="${ae}"
          ${H}
          role="presentation">
        <span class=${T}-code-lang role="note">${e.utils.escapeHtml(l(C))}</span>`;
    }
    const { attrs: Y, language: ie } = at(
      a(h[p])
    ), ne = [ie, Y].filter(Boolean).join(" ");
    return `
      <${F} ${g.renderAttrs(G)}>
        <${q} class="${T}-code-head">
          <div class="${T}-code-flag">
            <ul class="${T}-codetab-label" role="tablist">${O}</ul>
          </div>
          <div class="${T}-code-action">
            <span class="${T}-codetab-lang">${$}</span>
            <span class="${T}-copy-button" data-tips="${D}"${A ? " data-is-icon=true" : ""}>${m}</span>
            ${u.extraTools instanceof Function ? u.extraTools({ lang: ne }) : u.extraTools || ""}
            ${F === "details" ? k : ""}
          </div>
        </${q}>
        ${z}
      </${F}>
    `;
  };
  e.renderer.rules.fence = c, e.renderer.rules.code_block = c;
}, Dc = (e, u) => {
  e.core.ruler.after("block", "echarts-token-attrs", (r) => {
    let i;
    r.tokens.forEach((t) => {
      if (!(t.type !== "fence" || t.info.trim() !== "echarts") && (t.tag = "div", t.attrJoin("class", `${T}-echarts`), t.attrSet("data-echarts-theme", u.themeRef.value), t.attrSet("style", "width: 100%; aspect-ratio: 4 / 3;"), t.map)) {
        i ??= r.src.split(`
`);
        const o = t.content ? t.content.split(`
`).length - Number(t.content.endsWith(`
`)) : 0, a = i[t.map[1] - 1].trimEnd(), s = t.map[1] - t.map[0] === o + 2 && a.endsWith(t.markup);
        t.attrSet("data-closed", `${s}`), t.level === 0 && t.attrSet("data-line", String(t.map[0]));
      }
    });
  });
  const n = e.renderer.rules.fence.bind(e.renderer.rules);
  e.renderer.rules.fence = (r, i, t, o, a) => {
    const s = r[i];
    return s.info.trim() === "echarts" ? zu(s, e.utils.escapeHtml(s.content.trim()), a) : n(r, i, t, o, a);
  };
}, Ec = (e, u) => {
  e.renderer.rules.heading_open = (n, r) => {
    const i = n[r], t = n[r + 1].children?.reduce((a, s) => a + (["text", "code_inline", "math_inline"].includes(s.type) && s.content || ""), "") || "", o = i.markup.length;
    return u.headsRef.value.push({
      text: t,
      level: o,
      line: i.map[0],
      currentToken: i,
      nextToken: n[r + 1]
    }), i.map && i.level === 0 && i.attrSet(
      "id",
      u.mdHeadingId({
        text: t,
        level: o,
        index: u.headsRef.value.length,
        currentToken: i,
        nextToken: n[r + 1]
      })
    ), e.renderer.renderToken(n, r, u);
  }, e.renderer.rules.heading_close = (n, r, i, t, o) => o.renderToken(n, r, i);
}, gr = {
  block: [
    { open: "$$", close: "$$" },
    { open: "\\[", close: "\\]" }
  ],
  inline: [
    { open: "$$", close: "$$" },
    { open: "$", close: "$" },
    { open: "\\[", close: "\\]" },
    { open: "\\(", close: "\\)" }
  ]
}, xc = (e) => (u, n) => {
  const r = e.delimiters;
  for (const i of r) {
    if (!u.src.startsWith(i.open, u.pos))
      continue;
    const t = u.pos + i.open.length;
    let o = t;
    for (; (o = u.src.indexOf(i.close, o)) !== -1; ) {
      let a = 0, s = o - 1;
      for (; s >= 0 && u.src[s] === "\\"; )
        a++, s--;
      if (a % 2 === 0)
        break;
      o += i.close.length;
    }
    if (o !== -1) {
      if (o - t === 0)
        return n || (u.pending += i.open + i.close), u.pos = o + i.close.length, !0;
      if (!n) {
        const a = u.push("math_inline", e.tag, 0);
        a.attrSet("class", e.className), a.markup = i.open, a.content = u.src.slice(t, o);
      }
      return u.pos = o + i.close.length, !0;
    }
  }
  return !1;
}, Cc = (e) => (u, n, r, i) => {
  const t = e.delimiters, o = u.bMarks[n] + u.tShift[n], a = u.eMarks[n], s = (l, f, c) => {
    u.line = f;
    const h = u.push("math_block", e.tag, 0);
    return h.attrSet("class", e.className), h.block = !0, h.content = l, h.map = [n, u.line], h.markup = c, !0;
  };
  for (const l of t) {
    const f = o;
    if (u.src.slice(f, f + l.open.length) !== l.open)
      continue;
    const c = f + l.open.length, h = u.src.slice(c, a).trim(), p = h === "", d = h === l.close, x = h.endsWith(l.close);
    if (!p && !d && !x)
      continue;
    if (i)
      return !0;
    if (d)
      return s("", n + 1, l.open);
    if (!p && x) {
      const m = h.slice(0, -l.close.length);
      return s(m, n + 1, l.open);
    }
    let g = n + 1, _ = !1, E = "";
    for (; g < r; g++) {
      const m = u.bMarks[g] + u.tShift[g], A = u.eMarks[g];
      if (m < A && u.tShift[g] < u.blkIndent)
        break;
      if (u.src.slice(m, A).trim().endsWith(l.close)) {
        const k = u.src.slice(0, A).lastIndexOf(l.close);
        E = u.src.slice(m, k), _ = !0;
        break;
      }
    }
    if (!_)
      continue;
    const D = u.getLines(n + 1, g, u.tShift[n], !0) + (E.trim() ? E : "");
    return s(D, g + 1, l.open);
  }
  return !1;
}, Ac = (e, { katexRef: u, inlineDelimiters: n, blockDelimiters: r }) => {
  const i = (a, s, l = !1) => {
    if (!u.value)
      return zu(a, e.utils.escapeHtml(a.content), s);
    const f = { throwOnError: !1, displayMode: l, trust: !1 }, c = Oe.katexConfig(f), h = u.value.renderToString(a.content, {
      ...f,
      ...c,
      // 配置其他选项不应隐式开放 HTML 命令；调用方仍可显式传 true 或判断函数。
      trust: c?.trust ?? !1
    });
    return a.attrSet("data-processed", ""), zu(a, String(h), s);
  }, t = (a, s, l, f, c) => i(a[s], c), o = (a, s, l, f, c) => i(a[s], c, !0);
  e.inline.ruler.before(
    "escape",
    "math_inline",
    xc({
      delimiters: n || gr.inline,
      className: `${T}-katex-inline`,
      tag: "span"
    })
  ), e.block.ruler.after(
    "blockquote",
    "math_block",
    Cc({
      delimiters: r || gr.block,
      className: `${T}-katex-block`,
      tag: "p"
    }),
    {
      alt: ["paragraph", "reference", "blockquote", "list"]
    }
  ), e.renderer.rules.math_inline = t, e.renderer.rules.math_block = o;
}, _c = (e, u) => {
  e.core.ruler.after("block", "mermaid-token-attrs", (r) => {
    r.tokens.forEach((i, t) => {
      if (!(i.type !== "fence" || i.info !== "mermaid") && (i.tag = "div", i.attrJoin("class", `${T}-mermaid`), i.attrSet("data-mermaid-theme", u.themeRef.value), i.attrSet("data-mermaid-block", String(i.map?.[0] ?? t)), i.attrSet("data-mermaid-revision", String(u.revision?.value ?? 0)), i.map && i.level === 0)) {
        const o = i.map[1] - 1, a = !!r.env.srcLines?.[o]?.trim()?.startsWith("```");
        i.attrSet("data-closed", `${a}`), i.attrSet("data-line", String(i.map[0]));
      }
    });
  });
  const n = e.renderer.rules.fence.bind(e.renderer.rules);
  e.renderer.rules.fence = (r, i, t, o, a) => {
    const s = r[i], l = s.content.trim();
    if (s.info === "mermaid") {
      const f = s.attrGet("data-closed") !== "false" ? u.getCached?.(s.attrGet("data-mermaid-block") ?? void 0, l) : void 0;
      return f ? (s.tag = "p", s.attrSet("data-processed", ""), s.attrSet("data-content", l), zu(s, f.svg, a)) : zu(s, e.utils.escapeHtml(l), a);
    }
    return n(r, i, t, o, a);
  };
}, mr = (e, u, n) => {
  const r = e.attrIndex(u), i = [u, n];
  r < 0 ? e.attrPush(i) : (e.attrs = e.attrs || [], e.attrs[r] = i);
}, Fc = (e) => e.type === "inline", yc = (e) => e.type === "paragraph_open", kc = (e) => e.type === "list_item_open", vc = (e) => e.content.indexOf("[ ] ") === 0 || e.content.indexOf("[x] ") === 0 || e.content.indexOf("[X] ") === 0, wc = (e, u) => Fc(e[u]) && yc(e[u - 1]) && kc(e[u - 2]) && vc(e[u]), Bc = (e, u) => {
  const n = e[u].level - 1;
  for (let r = u - 1; r >= 0; r--)
    if (e[r].level === n)
      return r;
  return -1;
}, Dr = (e) => new e("label_open", "label", 1), Er = (e) => new e("label_close", "label", -1), Sc = (e, u, n) => {
  const r = new u("html_inline", "", 0), i = n.enabled ? " " : ' disabled="" ';
  return e.content.indexOf("[ ] ") === 0 ? r.content = '<input class="task-list-item-checkbox"' + i + 'type="checkbox">' : (e.content.indexOf("[x] ") === 0 || e.content.indexOf("[X] ") === 0) && (r.content = '<input class="task-list-item-checkbox" checked=""' + i + 'type="checkbox">'), r;
}, Tc = (e, u, n) => {
  if (e.children = e.children || [], e.children.unshift(Sc(e, u, n)), e.children[1].content = e.children[1].content.slice(3), e.content = e.content.slice(3), n.label)
    if (n.labelAfter) {
      const r = "task-item-" + Math.ceil(Math.random() * 1e7 - 1e3);
      e.children[0].content = e.children[0].content.slice(0, -1) + ' id="' + r + '">';
      const i = Dr(u);
      i.attrSet("class", "task-list-item-label"), i.attrSet("for", r), e.children.splice(1, 0, i), e.children.push(Er(u));
    } else
      e.children.unshift(Dr(u)), e.children.push(Er(u));
}, Ic = (e, u = {}) => {
  e.core.ruler.after("inline", "github-task-lists", (n) => {
    const r = n.tokens;
    for (let i = 2; i < r.length; i++)
      wc(r, i) && (Tc(r[i], n.Token, u), mr(
        r[i - 2],
        "class",
        "task-list-item" + (u.enabled ? " enabled" : " ")
      ), mr(r[Bc(r, i - 2)], "class", "contains-task-list"));
  });
}, Mc = (e) => {
  e.core.ruler.push("init-line-number", (u) => (u.tokens.forEach((n) => {
    n.map && n.attrSet("data-line", n.map[0].toString());
  }), !0));
}, Oc = (e, u) => {
  const { editorConfig: n, markdownItConfig: r, markdownItPlugins: i, editorExtensions: t } = Oe, o = oe("editorId"), a = oe("language"), s = oe(
    "usedLanguageText"
  ), l = oe("showCodeRowNumber"), f = oe("theme"), c = oe("customIcon"), h = oe("rootRef"), p = oe("setting"), d = $u([]), x = lc(e), g = fc(e), { reRenderRef: _, replaceMermaid: E, invalidateMermaid: D, getCachedMermaid: m } = hc(e), { reRenderEcharts: A, replaceEcharts: k } = cc(e), B = Ne({
    // 关闭源文本中的原生 HTML 解析，插件生成的 HTML 不受影响。
    html: !1,
    breaks: !0,
    linkify: !0
  });
  r(B, {
    editorId: o
  });
  const C = [
    {
      type: "image",
      plugin: Gs,
      options: { figcaption: !0, classes: "md-zoom" }
    },
    {
      type: "admonition",
      plugin: bc,
      options: {}
    },
    {
      type: "taskList",
      plugin: Ic,
      options: {}
    },
    {
      type: "heading",
      plugin: Ec,
      options: { mdHeadingId: e.mdHeadingId, headsRef: d }
    },
    {
      type: "code",
      plugin: mc,
      options: {
        editorId: o,
        usedLanguageTextRef: s,
        // showCodeRowNumber,
        codeFoldable: e.codeFoldable,
        autoFoldThreshold: e.autoFoldThreshold,
        customIconRef: c
      }
    },
    {
      type: "sub",
      plugin: Ks,
      options: {}
    },
    {
      type: "sup",
      plugin: Qs,
      options: {}
    }
  ];
  e.noKatex || C.push({
    type: "katex",
    plugin: Ac,
    options: { katexRef: g }
  }), e.noMermaid || C.push({
    type: "mermaid",
    plugin: _c,
    options: { themeRef: f, revision: _, getCached: m }
  }), e.noEcharts || C.push({
    type: "echarts",
    plugin: Dc,
    options: { themeRef: f }
  }), i(C, {
    editorId: o
  }).forEach((w) => {
    B.use(w.plugin, w.options);
  });
  const U = B.options.highlight;
  B.set({
    highlight: (w, G, Y) => {
      const ie = at(G, Y), { attrs: ne, language: ue, lineHighlightRanges: ae } = ie;
      let pe = "";
      if (U) {
        const $e = U(w, ue, ne);
        if ($e) {
          const ou = Xi($e, w, {
            lineHighlightRanges: ae,
            showLineNumber: l
          });
          if (ou.shouldReturnDirectly)
            return ou.html;
          pe = ou.html;
        }
      }
      pe || (!e.noHighlight && x.value ? x.value.getLanguage(ue) ? pe = x.value.highlight(w, {
        language: ue,
        ignoreIllegals: !0
      }).value : pe = x.value.highlightAuto(w).value : pe = B.utils.escapeHtml(w));
      const Re = B.utils.escapeHtml(ue);
      let iu = `<span class="${T}-code-block">${pe.replace(/^\n+|\n+$/g, "")}</span>`;
      return (l || ae.length) && (iu = Hr(pe, w, {
        lineHighlightRanges: ae,
        showLineNumber: l
      })), `<pre><code class="language-${Re}" language="${Re}">${iu}</code></pre>`;
    }
  }), Mc(B);
  const Z = $u(`_article-key_${Qt()}`), H = $u(
    e.sanitize(
      B.render(e.modelValue, {
        srcLines: e.modelValue.split(`
`)
      })
    )
  );
  let O = () => {
  }, z = () => {
  };
  const $ = () => {
    const w = h.value?.querySelectorAll(
      `#${o} p.${T}-mermaid:not([data-closed=false])`
    );
    z(), z = eo(w, {
      customIcon: c.value
    }), t.mermaid?.enableZoom && (O(), O = uo(w, {
      customIcon: c.value
    }));
  }, N = () => {
    Me.emit(o, $i, H.value), e.onHtmlChanged(H.value), e.onGetCatalog(d.value), Me.emit(o, Rt, d.value), wu(() => {
      E().then($), k();
    });
  }, F = () => {
    d.value = [], H.value = e.sanitize(
      B.render(e.modelValue, {
        srcLines: e.modelValue.split(`
`)
      })
    );
  }, q = Qe(() => (e.noKatex || !!g.value) && (e.noHighlight || !!x.value));
  return ve(
    [
      Ln(e, "modelValue"),
      Ln(e, "sanitize"),
      q,
      _,
      a
    ],
    (w, G, Y) => {
      const ie = window.setTimeout(
        () => {
          F();
        },
        u ? 0 : n.renderDelay
      );
      Y(() => {
        clearTimeout(ie);
      });
    }
  ), ve(
    () => p.value.preview,
    () => {
      p.value.preview && wu(() => {
        E().then($), k(), Me.emit(o, Rt, d.value);
      });
    }
  ), ve([H, A, Z], () => {
    N();
  }), nu(N), nu(() => {
    Me.on(o, {
      name: Li,
      callback() {
        Me.emit(o, Rt, d.value);
      }
    }), Me.on(o, {
      name: Lr,
      callback: () => {
        D(), Z.value = `_article-key_${Qt()}`, F();
      }
    });
  }), Zu(() => {
    O(), z();
  }), { html: H, key: Z };
}, Nc = (e, u) => {
  const n = oe("editorId"), r = oe("setting"), { noImgZoomIn: i } = e, t = fi(() => {
    const o = document.querySelectorAll(
      `#${n}-preview img:not(.not-zoom):not(.medium-zoom-image)`
    );
    o.length !== 0 && tc(o, {
      background: "#00000073"
    });
  });
  nu(async () => {
    !i && r.value.preview && await t();
  }), ve([u, () => r.value.preview], async () => {
    !i && r.value.preview && await t();
  });
}, xr = {
  checked: {
    regexp: /- \[x\]/,
    value: "- [ ]"
  },
  unChecked: {
    regexp: /- \[\s\]/,
    value: "- [x]"
  }
}, Rc = (e, u) => {
  const n = oe("editorId"), r = oe("rootRef");
  let i = () => {
  };
  const t = () => {
    if (!r.value)
      return !1;
    const o = r.value.querySelectorAll(".task-list-item.enabled"), a = (s) => {
      s.preventDefault();
      const l = s.target.checked ? "unChecked" : "checked", f = s.target.parentElement?.dataset.line;
      if (!f)
        return;
      const c = Number(f), h = e.modelValue.split(`
`), p = h[Number(c)].replace(
        xr[l].regexp,
        xr[l].value
      );
      e.previewOnly ? (h[Number(c)] = p, e.onChange(h.join(`
`))) : Me.emit(n, zi, c + 1, p);
    };
    o.forEach((s) => {
      s.addEventListener("click", a);
    }), i = () => {
      o.forEach((s) => {
        s.removeEventListener("click", a);
      });
    };
  };
  Zu(() => {
    i();
  }), ve(
    [u],
    () => {
      i(), wu(t);
    },
    {
      immediate: !0
    }
  );
}, $c = (e, u, n) => {
  const r = oe("setting"), i = () => {
    wu(() => {
      e.onRemount?.();
    });
  }, t = (o) => {
    o && i();
  };
  ve([u, n], i), ve(() => r.value.preview, t), ve(() => r.value.htmlPreview, t), nu(i);
}, m0 = {
  modelValue: {
    type: String,
    default: ""
  },
  onChange: {
    type: Function,
    default: () => {
    }
  },
  onHtmlChanged: {
    type: Function,
    default: () => {
    }
  },
  onGetCatalog: {
    type: Function,
    default: () => {
    }
  },
  mdHeadingId: {
    type: Function,
    default: () => ""
  },
  noMermaid: {
    type: Boolean,
    default: !1
  },
  sanitize: {
    type: Function,
    default: (e) => e
  },
  // 不使用该函数功能
  noKatex: {
    type: Boolean,
    default: !1
  },
  formatCopiedText: {
    type: Function,
    default: (e) => e
  },
  noHighlight: {
    type: Boolean,
    default: !1
  },
  previewOnly: {
    type: Boolean,
    default: !1
  },
  noImgZoomIn: {
    type: Boolean
  },
  sanitizeMermaid: {
    type: Function
  },
  codeFoldable: {
    type: Boolean
  },
  autoFoldThreshold: {
    type: Number
  },
  onRemount: {
    type: Function
  },
  noEcharts: {
    type: Boolean
  },
  previewComponent: {
    type: [Object, Function],
    default: void 0
  }
};
({
  ...m0
});
const Cr = (e) => {
  const u = new DOMParser().parseFromString(e, "text/html");
  return Array.from(u.body.childNodes);
}, Lc = (e, u) => e.nodeType !== u.nodeType ? !1 : e.nodeType === Node.TEXT_NODE || e.nodeType === Node.COMMENT_NODE ? e.textContent === u.textContent : e.nodeType === Node.ELEMENT_NODE ? e.outerHTML === u.outerHTML : e.isEqualNode ? e.isEqualNode(u) : !1, zc = /* @__PURE__ */ ht({
  name: "UpdateOnDemand",
  props: {
    id: {
      type: String,
      required: !0
    },
    class: {
      type: [String, Array, Object],
      required: !0
    },
    html: {
      type: String,
      required: !0
    }
  },
  setup(e) {
    const u = $u(), n = e.html, r = (i, t) => {
      if (!u.value) return;
      const o = u.value, a = Array.from(o.childNodes), s = Math.min(i.length, t.length);
      let l = -1;
      for (let c = 0; c < s; c++)
        if (!Lc(i[c], t[c])) {
          l = c;
          break;
        }
      if (l === -1)
        if (t.length > i.length)
          l = i.length;
        else if (i.length > t.length)
          l = t.length;
        else
          return;
      const f = Math.min(l, a.length);
      for (let c = a.length - 1; c >= f; c--)
        a[c].remove();
      for (let c = l; c < i.length; c++)
        o.appendChild(i[c].cloneNode(!0));
    };
    return ve(() => e.html, (i, t) => {
      const o = Cr(i), a = Cr(t || "");
      r(o, a);
    }), () => Ye("div", {
      id: e.id,
      class: e.class,
      innerHTML: n,
      ref: u
    }, null);
  }
}), Pc = /* @__PURE__ */ ht({
  name: "ContentPreview",
  props: m0,
  setup(e) {
    const u = oe("editorId"), n = oe("setting"), r = oe("previewTheme"), i = oe("showCodeRowNumber"), {
      html: t,
      key: o
    } = Oc(e, e.previewOnly);
    sc(e, t, o), Nc(e, t), Rc(e, t), $c(e, t, o);
    const a = Qe(() => [`${T}-preview`, `${r?.value}-theme`, i && `${T}-scrn`].filter(Boolean)), s = () => {
      const l = `${u}-preview`;
      return e.previewComponent ? ai(e.previewComponent, {
        key: o.value,
        html: t.value,
        id: l,
        class: a.value
      }) : Ye(zc, {
        key: o.value,
        html: t.value,
        id: l,
        class: a.value
      }, null);
    };
    return () => Ye(oi, null, [n.value.preview && (e.previewOnly ? s() : Ye("div", {
      id: `${u}-preview-wrapper`,
      class: `${T}-preview-wrapper`,
      key: "content-preview-wrapper"
    }, [s()])), n.value.htmlPreview && Ye("div", {
      id: `${u}-html-wrapper`,
      class: `${T}-preview-wrapper`,
      key: "html-preview-wrapper"
    }, [Ye("div", {
      class: `${T}-html`
    }, [t.value])])]);
  }
}), Hc = ({ text: e }) => e, D0 = {
  /**
   * markdown content.
   *
   * @default ''
   */
  modelValue: {
    type: String,
    default: ""
  },
  /**
   * input回调事件
   */
  onChange: {
    type: Function,
    default: void 0
  },
  /**
   * 主题，支持light和dark
   *
   * @default 'light'
   */
  theme: {
    type: String,
    default: "light"
  },
  /**
   * 外层类名
   *
   * @default ''
   */
  class: {
    type: String,
    default: ""
  },
  /**
   * 预设语言名称
   *
   * @default 'zh-CN'
   */
  language: {
    type: String,
    default: "zh-CN"
  },
  /**
   * html变化事件
   */
  onHtmlChanged: {
    type: Function,
    default: void 0
  },
  /**
   * 获取目录结构
   */
  onGetCatalog: {
    type: Function,
    default: void 0
  },
  /**
   * 编辑器唯一标识
   *
   * @default 'md-editor-v3'
   * @deprecated 5.x版本开始使用 id 替换
   */
  editorId: {
    type: String,
    default: void 0
  },
  /**
   * 5.x版本开始 editorId 的替换
   *
   * @default 'md-editor-v3'
   */
  id: {
    type: String,
    default: void 0
  },
  /**
   * 预览中代码是否显示行号
   *
   * @default true
   */
  showCodeRowNumber: {
    type: Boolean,
    default: !0
  },
  /**
   * 预览内容样式
   *
   * @default 'default'
   */
  previewTheme: {
    type: String,
    default: "default"
  },
  /**
   * 编辑器样式
   */
  style: {
    type: Object,
    default: () => ({})
  },
  /**
   * 标题的id生成方式
   *
   * @default (text: string) => text
   */
  mdHeadingId: {
    type: Function,
    default: Hc
  },
  /**
   *
   * Markdown 编译后的 HTML 后处理入口。原生 HTML 默认关闭；显式开启或使用
   * 自定义 renderer 时，可在这里接入 DOMPurify、sanitize-html 等业务清洗策略。
   *
   * @default (text: string) => text
   */
  sanitize: {
    type: Function,
    default: (e) => e
  },
  /**
   * 不使用该mermaid
   *
   * @default false
   */
  noMermaid: {
    type: Boolean,
    default: !1
  },
  /**
   * 不使用katex
   *
   * @default false
   */
  noKatex: {
    type: Boolean,
    default: !1
  },
  /**
   * 代码主题
   *
   * @default 'atom'
   */
  codeTheme: {
    type: String,
    default: "atom"
  },
  /**
   * 复制代码格式化方法
   *
   * @default (text) => text
   */
  formatCopiedText: {
    type: Function,
    default: (e) => e
  },
  /**
   * 某些预览主题的代码模块背景是暗色系
   * 将这个属性设置为true，会自动在该主题下的light模式下使用暗色系的代码风格
   *
   * @default true
   */
  codeStyleReverse: {
    type: Boolean,
    default: !0
  },
  /**
   * 需要自动调整的预览主题
   *
   * @default ['default', 'mk-cute']
   */
  codeStyleReverseList: {
    type: Array,
    default: ["default", "mk-cute"]
  },
  noHighlight: {
    type: Boolean,
    default: !1
  },
  /**
   * 是否关闭编辑器默认的放大缩小功能
   */
  noImgZoomIn: {
    type: Boolean,
    default: !1
  },
  /**
   * 自定义的图标
   */
  customIcon: {
    type: Object,
    default: {}
  },
  /** Mermaid 在默认 strict 渲染之后的异步 SVG 后处理入口。 */
  sanitizeMermaid: {
    type: Function,
    default: (e) => Promise.resolve(e)
  },
  /**
   * 是否开启折叠代码功能
   * 不开启会使用div标签替代details标签
   *
   * @default true
   */
  codeFoldable: {
    type: Boolean,
    default: !0
  },
  /**
   * 触发自动折叠代码的行数阈值
   *
   * @default 30
   */
  autoFoldThreshold: {
    type: Number,
    default: 30
  },
  /**
   * 内容重新挂载事件
   *
   * 相比起onHtmlChanged，onRemount会在重新挂载后触发
   */
  onRemount: {
    type: Function,
    default: void 0
  },
  /**
   * 不使用 echarts
   */
  noEcharts: {
    type: Boolean,
    default: !1
  },
  previewComponent: {
    type: [Object, Function],
    default: void 0
  }
};
({
  ...D0
});
const E0 = [
  "onHtmlChanged",
  "onGetCatalog",
  "onChange",
  "onRemount",
  "update:modelValue"
];
[
  ...E0
];
const Ut = /* @__PURE__ */ ht({
  name: "MdPreview",
  props: D0,
  emits: E0,
  setup(e, u) {
    const {
      noKatex: n,
      noMermaid: r,
      noHighlight: i
    } = e, t = $u(), o = ac(e);
    oc(e, {
      rootRef: t,
      editorId: o
    }), ic(e, u, {
      editorId: o
    }), Zu(() => {
      Me.clear(o);
    });
    const a = (c) => {
      e.onChange?.(c), u.emit("onChange", c), u.emit("update:modelValue", c);
    }, s = (c) => {
      e.onHtmlChanged?.(c), u.emit("onHtmlChanged", c);
    }, l = (c) => {
      e.onGetCatalog?.(c), u.emit("onGetCatalog", c);
    }, f = () => {
      e.onRemount?.(), u.emit("onRemount");
    };
    return () => Ye("div", {
      id: o,
      class: [T, e.class, `${T}-previewOnly`],
      "data-theme": e.theme,
      style: e.style,
      ref: t
    }, [Ye(Pc, {
      modelValue: e.modelValue,
      onChange: a,
      onHtmlChanged: s,
      onGetCatalog: l,
      mdHeadingId: e.mdHeadingId,
      noMermaid: r,
      sanitize: e.sanitize,
      noKatex: n,
      formatCopiedText: e.formatCopiedText,
      noHighlight: i,
      noImgZoomIn: e.noImgZoomIn,
      previewOnly: !0,
      sanitizeMermaid: e.sanitizeMermaid,
      codeFoldable: e.codeFoldable,
      autoFoldThreshold: e.autoFoldThreshold,
      onRemount: f,
      noEcharts: e.noEcharts,
      previewComponent: e.previewComponent
    }, null)]);
  }
});
Ut.install = (e) => (e.component(Ut.name, Ut), e);
const jc = {
  onClick: {
    type: Function,
    default: void 0
  },
  /**
   * ==没有意义，仅用于规避克隆组件自动嵌入insert方法时，传入的是该组件而产生的waring
   */
  language: {
    type: String,
    default: void 0
  },
  theme: {
    type: String,
    default: void 0
  },
  disabled: {
    type: Boolean,
    default: void 0
  }
  /**
   * ==结束
   */
}, Zt = /* @__PURE__ */ ht({
  name: "NormalFooterToolbar",
  props: jc,
  emits: ["onClick"],
  setup(e, u) {
    return () => {
      const n = Ri({
        props: e,
        ctx: u
      });
      return Ye("div", {
        class: [`${T}-footer-item`, e.disabled && `${T}-disabled`],
        onClick: (r) => {
          e.disabled || (e.onClick?.(r), u.emit("onClick", r));
        }
      }, [n]);
    };
  }
});
Zt.install = (e) => (e.component(Zt.name, Zt), e);
var Gt = { exports: {} }, he = {}, Vt = { exports: {} }, Cu = {}, Ar;
function x0() {
  if (Ar) return Cu;
  Ar = 1;
  function e() {
    var t = {};
    return t["align-content"] = !1, t["align-items"] = !1, t["align-self"] = !1, t["alignment-adjust"] = !1, t["alignment-baseline"] = !1, t.all = !1, t["anchor-point"] = !1, t.animation = !1, t["animation-delay"] = !1, t["animation-direction"] = !1, t["animation-duration"] = !1, t["animation-fill-mode"] = !1, t["animation-iteration-count"] = !1, t["animation-name"] = !1, t["animation-play-state"] = !1, t["animation-timing-function"] = !1, t.azimuth = !1, t["backface-visibility"] = !1, t.background = !0, t["background-attachment"] = !0, t["background-clip"] = !0, t["background-color"] = !0, t["background-image"] = !0, t["background-origin"] = !0, t["background-position"] = !0, t["background-repeat"] = !0, t["background-size"] = !0, t["baseline-shift"] = !1, t.binding = !1, t.bleed = !1, t["bookmark-label"] = !1, t["bookmark-level"] = !1, t["bookmark-state"] = !1, t.border = !0, t["border-bottom"] = !0, t["border-bottom-color"] = !0, t["border-bottom-left-radius"] = !0, t["border-bottom-right-radius"] = !0, t["border-bottom-style"] = !0, t["border-bottom-width"] = !0, t["border-collapse"] = !0, t["border-color"] = !0, t["border-image"] = !0, t["border-image-outset"] = !0, t["border-image-repeat"] = !0, t["border-image-slice"] = !0, t["border-image-source"] = !0, t["border-image-width"] = !0, t["border-left"] = !0, t["border-left-color"] = !0, t["border-left-style"] = !0, t["border-left-width"] = !0, t["border-radius"] = !0, t["border-right"] = !0, t["border-right-color"] = !0, t["border-right-style"] = !0, t["border-right-width"] = !0, t["border-spacing"] = !0, t["border-style"] = !0, t["border-top"] = !0, t["border-top-color"] = !0, t["border-top-left-radius"] = !0, t["border-top-right-radius"] = !0, t["border-top-style"] = !0, t["border-top-width"] = !0, t["border-width"] = !0, t.bottom = !1, t["box-decoration-break"] = !0, t["box-shadow"] = !0, t["box-sizing"] = !0, t["box-snap"] = !0, t["box-suppress"] = !0, t["break-after"] = !0, t["break-before"] = !0, t["break-inside"] = !0, t["caption-side"] = !1, t.chains = !1, t.clear = !0, t.clip = !1, t["clip-path"] = !1, t["clip-rule"] = !1, t.color = !0, t["color-interpolation-filters"] = !0, t["column-count"] = !1, t["column-fill"] = !1, t["column-gap"] = !1, t["column-rule"] = !1, t["column-rule-color"] = !1, t["column-rule-style"] = !1, t["column-rule-width"] = !1, t["column-span"] = !1, t["column-width"] = !1, t.columns = !1, t.contain = !1, t.content = !1, t["counter-increment"] = !1, t["counter-reset"] = !1, t["counter-set"] = !1, t.crop = !1, t.cue = !1, t["cue-after"] = !1, t["cue-before"] = !1, t.cursor = !1, t.direction = !1, t.display = !0, t["display-inside"] = !0, t["display-list"] = !0, t["display-outside"] = !0, t["dominant-baseline"] = !1, t.elevation = !1, t["empty-cells"] = !1, t.filter = !1, t.flex = !1, t["flex-basis"] = !1, t["flex-direction"] = !1, t["flex-flow"] = !1, t["flex-grow"] = !1, t["flex-shrink"] = !1, t["flex-wrap"] = !1, t.float = !1, t["float-offset"] = !1, t["flood-color"] = !1, t["flood-opacity"] = !1, t["flow-from"] = !1, t["flow-into"] = !1, t.font = !0, t["font-family"] = !0, t["font-feature-settings"] = !0, t["font-kerning"] = !0, t["font-language-override"] = !0, t["font-size"] = !0, t["font-size-adjust"] = !0, t["font-stretch"] = !0, t["font-style"] = !0, t["font-synthesis"] = !0, t["font-variant"] = !0, t["font-variant-alternates"] = !0, t["font-variant-caps"] = !0, t["font-variant-east-asian"] = !0, t["font-variant-ligatures"] = !0, t["font-variant-numeric"] = !0, t["font-variant-position"] = !0, t["font-weight"] = !0, t.grid = !1, t["grid-area"] = !1, t["grid-auto-columns"] = !1, t["grid-auto-flow"] = !1, t["grid-auto-rows"] = !1, t["grid-column"] = !1, t["grid-column-end"] = !1, t["grid-column-start"] = !1, t["grid-row"] = !1, t["grid-row-end"] = !1, t["grid-row-start"] = !1, t["grid-template"] = !1, t["grid-template-areas"] = !1, t["grid-template-columns"] = !1, t["grid-template-rows"] = !1, t["hanging-punctuation"] = !1, t.height = !0, t.hyphens = !1, t.icon = !1, t["image-orientation"] = !1, t["image-resolution"] = !1, t["ime-mode"] = !1, t["initial-letters"] = !1, t["inline-box-align"] = !1, t["justify-content"] = !1, t["justify-items"] = !1, t["justify-self"] = !1, t.left = !1, t["letter-spacing"] = !0, t["lighting-color"] = !0, t["line-box-contain"] = !1, t["line-break"] = !1, t["line-grid"] = !1, t["line-height"] = !1, t["line-snap"] = !1, t["line-stacking"] = !1, t["line-stacking-ruby"] = !1, t["line-stacking-shift"] = !1, t["line-stacking-strategy"] = !1, t["list-style"] = !0, t["list-style-image"] = !0, t["list-style-position"] = !0, t["list-style-type"] = !0, t.margin = !0, t["margin-bottom"] = !0, t["margin-left"] = !0, t["margin-right"] = !0, t["margin-top"] = !0, t["marker-offset"] = !1, t["marker-side"] = !1, t.marks = !1, t.mask = !1, t["mask-box"] = !1, t["mask-box-outset"] = !1, t["mask-box-repeat"] = !1, t["mask-box-slice"] = !1, t["mask-box-source"] = !1, t["mask-box-width"] = !1, t["mask-clip"] = !1, t["mask-image"] = !1, t["mask-origin"] = !1, t["mask-position"] = !1, t["mask-repeat"] = !1, t["mask-size"] = !1, t["mask-source-type"] = !1, t["mask-type"] = !1, t["max-height"] = !0, t["max-lines"] = !1, t["max-width"] = !0, t["min-height"] = !0, t["min-width"] = !0, t["move-to"] = !1, t["nav-down"] = !1, t["nav-index"] = !1, t["nav-left"] = !1, t["nav-right"] = !1, t["nav-up"] = !1, t["object-fit"] = !1, t["object-position"] = !1, t.opacity = !1, t.order = !1, t.orphans = !1, t.outline = !1, t["outline-color"] = !1, t["outline-offset"] = !1, t["outline-style"] = !1, t["outline-width"] = !1, t.overflow = !1, t["overflow-wrap"] = !1, t["overflow-x"] = !1, t["overflow-y"] = !1, t.padding = !0, t["padding-bottom"] = !0, t["padding-left"] = !0, t["padding-right"] = !0, t["padding-top"] = !0, t.page = !1, t["page-break-after"] = !1, t["page-break-before"] = !1, t["page-break-inside"] = !1, t["page-policy"] = !1, t.pause = !1, t["pause-after"] = !1, t["pause-before"] = !1, t.perspective = !1, t["perspective-origin"] = !1, t.pitch = !1, t["pitch-range"] = !1, t["play-during"] = !1, t.position = !1, t["presentation-level"] = !1, t.quotes = !1, t["region-fragment"] = !1, t.resize = !1, t.rest = !1, t["rest-after"] = !1, t["rest-before"] = !1, t.richness = !1, t.right = !1, t.rotation = !1, t["rotation-point"] = !1, t["ruby-align"] = !1, t["ruby-merge"] = !1, t["ruby-position"] = !1, t["shape-image-threshold"] = !1, t["shape-outside"] = !1, t["shape-margin"] = !1, t.size = !1, t.speak = !1, t["speak-as"] = !1, t["speak-header"] = !1, t["speak-numeral"] = !1, t["speak-punctuation"] = !1, t["speech-rate"] = !1, t.stress = !1, t["string-set"] = !1, t["tab-size"] = !1, t["table-layout"] = !1, t["text-align"] = !0, t["text-align-last"] = !0, t["text-combine-upright"] = !0, t["text-decoration"] = !0, t["text-decoration-color"] = !0, t["text-decoration-line"] = !0, t["text-decoration-skip"] = !0, t["text-decoration-style"] = !0, t["text-emphasis"] = !0, t["text-emphasis-color"] = !0, t["text-emphasis-position"] = !0, t["text-emphasis-style"] = !0, t["text-height"] = !0, t["text-indent"] = !0, t["text-justify"] = !0, t["text-orientation"] = !0, t["text-overflow"] = !0, t["text-shadow"] = !0, t["text-space-collapse"] = !0, t["text-transform"] = !0, t["text-underline-position"] = !0, t["text-wrap"] = !0, t.top = !1, t.transform = !1, t["transform-origin"] = !1, t["transform-style"] = !1, t.transition = !1, t["transition-delay"] = !1, t["transition-duration"] = !1, t["transition-property"] = !1, t["transition-timing-function"] = !1, t["unicode-bidi"] = !1, t["vertical-align"] = !1, t.visibility = !1, t["voice-balance"] = !1, t["voice-duration"] = !1, t["voice-family"] = !1, t["voice-pitch"] = !1, t["voice-range"] = !1, t["voice-rate"] = !1, t["voice-stress"] = !1, t["voice-volume"] = !1, t.volume = !1, t["white-space"] = !1, t.widows = !1, t.width = !0, t["will-change"] = !1, t["word-break"] = !0, t["word-spacing"] = !0, t["word-wrap"] = !0, t["wrap-flow"] = !1, t["wrap-through"] = !1, t["writing-mode"] = !1, t["z-index"] = !1, t;
  }
  function u(t, o, a) {
  }
  function n(t, o, a) {
  }
  var r = /javascript\s*\:/img;
  function i(t, o) {
    return r.test(o) ? "" : o;
  }
  return Cu.whiteList = e(), Cu.getDefaultWhiteList = e, Cu.onAttr = u, Cu.onIgnoreAttr = n, Cu.safeAttrValue = i, Cu;
}
var _r, Fr;
function C0() {
  return Fr || (Fr = 1, _r = {
    indexOf: function(e, u) {
      var n, r;
      if (Array.prototype.indexOf)
        return e.indexOf(u);
      for (n = 0, r = e.length; n < r; n++)
        if (e[n] === u)
          return n;
      return -1;
    },
    forEach: function(e, u, n) {
      var r, i;
      if (Array.prototype.forEach)
        return e.forEach(u, n);
      for (r = 0, i = e.length; r < i; r++)
        u.call(n, e[r], r, e);
    },
    trim: function(e) {
      return String.prototype.trim ? e.trim() : e.replace(/(^\s*)|(\s*$)/g, "");
    },
    trimRight: function(e) {
      return String.prototype.trimRight ? e.trimRight() : e.replace(/(\s*$)/g, "");
    }
  }), _r;
}
var Wt, yr;
function qc() {
  if (yr) return Wt;
  yr = 1;
  var e = C0();
  function u(n, r) {
    n = e.trimRight(n), n[n.length - 1] !== ";" && (n += ";");
    var i = n.length, t = !1, o = 0, a = 0, s = "";
    function l() {
      if (!t) {
        var h = e.trim(n.slice(o, a)), p = h.indexOf(":");
        if (p !== -1) {
          var d = e.trim(h.slice(0, p)), x = e.trim(h.slice(p + 1));
          if (d) {
            var g = r(o, s.length, d, x, h);
            g && (s += g + "; ");
          }
        }
      }
      o = a + 1;
    }
    for (; a < i; a++) {
      var f = n[a];
      if (f === "/" && n[a + 1] === "*") {
        var c = n.indexOf("*/", a + 2);
        if (c === -1) break;
        a = c + 1, o = a + 1, t = !1;
      } else f === "(" ? t = !0 : f === ")" ? t = !1 : f === ";" ? t || l() : f === `
` && l();
    }
    return e.trim(s);
  }
  return Wt = u, Wt;
}
var Kt, kr;
function Uc() {
  if (kr) return Kt;
  kr = 1;
  var e = x0(), u = qc();
  C0();
  function n(t) {
    return t == null;
  }
  function r(t) {
    var o = {};
    for (var a in t)
      o[a] = t[a];
    return o;
  }
  function i(t) {
    t = r(t || {}), t.whiteList = t.whiteList || e.whiteList, t.onAttr = t.onAttr || e.onAttr, t.onIgnoreAttr = t.onIgnoreAttr || e.onIgnoreAttr, t.safeAttrValue = t.safeAttrValue || e.safeAttrValue, this.options = t;
  }
  return i.prototype.process = function(t) {
    if (t = t || "", t = t.toString(), !t) return "";
    var o = this, a = o.options, s = a.whiteList, l = a.onAttr, f = a.onIgnoreAttr, c = a.safeAttrValue, h = u(t, function(p, d, x, g, _) {
      var E = s[x], D = !1;
      if (E === !0 ? D = E : typeof E == "function" ? D = E(g) : E instanceof RegExp && (D = E.test(g)), D !== !0 && (D = !1), g = c(x, g), !!g) {
        var m = {
          position: d,
          sourcePosition: p,
          source: _,
          isWhite: D
        };
        if (D) {
          var A = l(x, g, m);
          return n(A) ? x + ":" + g : A;
        } else {
          var A = f(x, g, m);
          if (!n(A))
            return A;
        }
      }
    });
    return h;
  }, Kt = i, Kt;
}
var vr;
function ln() {
  return vr || (vr = 1, (function(e, u) {
    var n = x0(), r = Uc();
    function i(o, a) {
      var s = new r(a);
      return s.process(o);
    }
    u = e.exports = i, u.FilterCSS = r;
    for (var t in n) u[t] = n[t];
    typeof window < "u" && (window.filterCSS = e.exports);
  })(Vt, Vt.exports)), Vt.exports;
}
var wr, Br;
function Cn() {
  return Br || (Br = 1, wr = {
    indexOf: function(e, u) {
      var n, r;
      if (Array.prototype.indexOf)
        return e.indexOf(u);
      for (n = 0, r = e.length; n < r; n++)
        if (e[n] === u)
          return n;
      return -1;
    },
    forEach: function(e, u, n) {
      var r, i;
      if (Array.prototype.forEach)
        return e.forEach(u, n);
      for (r = 0, i = e.length; r < i; r++)
        u.call(n, e[r], r, e);
    },
    trim: function(e) {
      return String.prototype.trim ? e.trim() : e.replace(/(^\s*)|(\s*$)/g, "");
    },
    spaceIndex: function(e) {
      var u = /\s|\n|\t/, n = u.exec(e);
      return n ? n.index : -1;
    }
  }), wr;
}
var Sr;
function A0() {
  if (Sr) return he;
  Sr = 1;
  var e = ln().FilterCSS, u = ln().getDefaultWhiteList, n = Cn();
  function r() {
    return {
      a: ["target", "href", "title"],
      abbr: ["title"],
      address: [],
      area: ["shape", "coords", "href", "alt"],
      article: [],
      aside: [],
      audio: [
        "autoplay",
        "controls",
        "crossorigin",
        "loop",
        "muted",
        "preload",
        "src"
      ],
      b: [],
      bdi: ["dir"],
      bdo: ["dir"],
      big: [],
      blockquote: ["cite"],
      br: [],
      caption: [],
      center: [],
      cite: [],
      code: [],
      col: ["align", "valign", "span", "width"],
      colgroup: ["align", "valign", "span", "width"],
      dd: [],
      del: ["datetime"],
      details: ["open"],
      div: [],
      dl: [],
      dt: [],
      em: [],
      figcaption: [],
      figure: [],
      font: ["color", "size", "face"],
      footer: [],
      h1: [],
      h2: [],
      h3: [],
      h4: [],
      h5: [],
      h6: [],
      header: [],
      hr: [],
      i: [],
      img: ["src", "alt", "title", "width", "height", "loading"],
      ins: ["datetime"],
      kbd: [],
      li: [],
      mark: [],
      nav: [],
      ol: [],
      p: [],
      pre: [],
      s: [],
      section: [],
      small: [],
      span: [],
      sub: [],
      summary: [],
      sup: [],
      strong: [],
      strike: [],
      table: ["width", "border", "align", "valign"],
      tbody: ["align", "valign"],
      td: ["width", "rowspan", "colspan", "align", "valign"],
      tfoot: ["align", "valign"],
      th: ["width", "rowspan", "colspan", "align", "valign"],
      thead: ["align", "valign"],
      tr: ["rowspan", "align", "valign"],
      tt: [],
      u: [],
      ul: [],
      video: [
        "autoplay",
        "controls",
        "crossorigin",
        "loop",
        "muted",
        "playsinline",
        "poster",
        "preload",
        "src",
        "height",
        "width"
      ]
    };
  }
  var i = new e();
  function t(F, q, w) {
  }
  function o(F, q, w) {
  }
  function a(F, q, w) {
  }
  function s(F, q, w) {
  }
  function l(F) {
    return F.replace(c, "&lt;").replace(h, "&gt;");
  }
  function f(F, q, w, G) {
    if (w = Z(w), q === "href" || q === "src") {
      if (w = n.trim(w), w === "#") return "#";
      if (!(w.substr(0, 7) === "http://" || w.substr(0, 8) === "https://" || w.substr(0, 7) === "mailto:" || w.substr(0, 4) === "tel:" || w.substr(0, 11) === "data:image/" || w.substr(0, 6) === "ftp://" || w.substr(0, 2) === "./" || w.substr(0, 3) === "../" || w[0] === "#" || w[0] === "/"))
        return "";
    } else if (q === "background") {
      if (E.lastIndex = 0, E.test(w))
        return "";
    } else if (q === "style") {
      if (D.lastIndex = 0, D.test(w) || (m.lastIndex = 0, m.test(w) && (E.lastIndex = 0, E.test(w))))
        return "";
      G !== !1 && (G = G || i, w = G.process(w));
    }
    return w = H(w), w;
  }
  var c = /</g, h = />/g, p = /"/g, d = /&quot;/g, x = /&#([a-zA-Z0-9]*);?/gim, g = /&colon;?/gim, _ = /&newline;?/gim, E = /((j\s*a\s*v\s*a|v\s*b|l\s*i\s*v\s*e)\s*s\s*c\s*r\s*i\s*p\s*t\s*|m\s*o\s*c\s*h\s*a):/gi, D = /e\s*x\s*p\s*r\s*e\s*s\s*s\s*i\s*o\s*n\s*\(.*/gi, m = /u\s*r\s*l\s*\(.*/gi;
  function A(F) {
    return F.replace(p, "&quot;");
  }
  function k(F) {
    return F.replace(d, '"');
  }
  function B(F) {
    return F.replace(x, function(q, w) {
      return w[0] === "x" || w[0] === "X" ? String.fromCharCode(parseInt(w.substr(1), 16)) : String.fromCharCode(parseInt(w, 10));
    });
  }
  function C(F) {
    return F.replace(g, ":").replace(_, " ");
  }
  function U(F) {
    for (var q = "", w = 0, G = F.length; w < G; w++)
      q += F.charCodeAt(w) < 32 ? " " : F.charAt(w);
    return n.trim(q);
  }
  function Z(F) {
    return F = k(F), F = B(F), F = C(F), F = U(F), F;
  }
  function H(F) {
    return F = A(F), F = l(F), F;
  }
  function O() {
    return "";
  }
  function z(F, q) {
    typeof q != "function" && (q = function() {
    });
    var w = !Array.isArray(F);
    function G(ne) {
      return w ? !0 : n.indexOf(F, ne) !== -1;
    }
    var Y = [], ie = !1;
    return {
      onIgnoreTag: function(ne, ue, ae) {
        if (G(ne))
          if (ae.isClosing) {
            var pe = "[/removed]", Re = ae.position + pe.length;
            return Y.push([
              ie !== !1 ? ie : ae.position,
              Re
            ]), ie = !1, pe;
          } else
            return ie || (ie = ae.position), "[removed]";
        else
          return q(ne, ue, ae);
      },
      remove: function(ne) {
        var ue = "", ae = 0;
        return n.forEach(Y, function(pe) {
          ue += ne.slice(ae, pe[0]), ae = pe[1];
        }), ue += ne.slice(ae), ue;
      }
    };
  }
  function $(F) {
    for (var q = "", w = 0; w < F.length; ) {
      var G = F.indexOf("<!--", w);
      if (G === -1) {
        q += F.slice(w);
        break;
      }
      q += F.slice(w, G);
      var Y = F.indexOf("-->", G);
      if (Y === -1)
        break;
      w = Y + 3;
    }
    return q;
  }
  function N(F) {
    var q = F.split("");
    return q = q.filter(function(w) {
      var G = w.charCodeAt(0);
      return G === 127 ? !1 : G <= 31 ? G === 10 || G === 13 : !0;
    }), q.join("");
  }
  return he.whiteList = r(), he.getDefaultWhiteList = r, he.onTag = t, he.onIgnoreTag = o, he.onTagAttr = a, he.onIgnoreTagAttr = s, he.safeAttrValue = f, he.escapeHtml = l, he.escapeQuote = A, he.unescapeQuote = k, he.escapeHtmlEntities = B, he.escapeDangerHtml5Entities = C, he.clearNonPrintableCharacter = U, he.friendlyAttrValue = Z, he.escapeAttrValue = H, he.onIgnoreTagStripAll = O, he.StripTagBody = z, he.stripCommentTag = $, he.stripBlankChar = N, he.attributeWrapSign = '"', he.cssFilter = i, he.getDefaultCSSWhiteList = u, he;
}
var ot = {}, Tr;
function _0() {
  if (Tr) return ot;
  Tr = 1;
  var e = Cn();
  function u(c) {
    var h = e.spaceIndex(c), p;
    return h === -1 ? p = c.slice(1, -1) : p = c.slice(1, h + 1), p = e.trim(p).toLowerCase(), p.slice(0, 1) === "/" && (p = p.slice(1)), p.slice(-1) === "/" && (p = p.slice(0, -1)), p;
  }
  function n(c) {
    return c.slice(0, 2) === "</";
  }
  function r(c, h, p) {
    var d = "", x = 0, g = !1, _ = !1, E = 0, D = c.length, m = "", A = "";
    e: for (E = 0; E < D; E++) {
      var k = c.charAt(E);
      if (g === !1) {
        if (k === "<") {
          g = E;
          continue;
        }
      } else if (_ === !1) {
        if (k === "<") {
          d += p(c.slice(x, E)), g = E, x = E;
          continue;
        }
        if (k === ">" || E === D - 1) {
          d += p(c.slice(x, g)), A = c.slice(g, E + 1), m = u(A), d += h(
            g,
            d.length,
            m,
            A,
            n(A)
          ), x = E + 1, g = !1;
          continue;
        }
        if (k === '"' || k === "'")
          for (var B = 1, C = c.charAt(E - B); C.trim() === "" || C === "="; ) {
            if (C === "=") {
              _ = k;
              continue e;
            }
            C = c.charAt(E - ++B);
          }
      } else if (k === _) {
        _ = !1;
        continue;
      }
    }
    return x < D && (d += p(c.substr(x))), d;
  }
  var i = /[^a-zA-Z0-9\\_:.-]/gim;
  function t(c, h) {
    var p = 0, d = 0, x = [], g = !1, _ = c.length;
    function E(B, C) {
      if (B = e.trim(B), B = B.replace(i, "").toLowerCase(), !(B.length < 1)) {
        var U = h(B, C || "");
        U && x.push(U);
      }
    }
    for (var D = 0; D < _; D++) {
      var m = c.charAt(D), A, k;
      if (g === !1 && m === "=") {
        g = c.slice(p, D), p = D + 1, d = c.charAt(p) === '"' || c.charAt(p) === "'" ? p : a(c, D + 1);
        continue;
      }
      if (g !== !1 && D === d) {
        if (k = c.indexOf(m, D + 1), k === -1)
          break;
        A = e.trim(c.slice(d + 1, k)), E(g, A), g = !1, D = k, p = D + 1;
        continue;
      }
      if (/\s|\n|\t/.test(m))
        if (c = c.replace(/\s|\n|\t/g, " "), g === !1)
          if (k = o(c, D), k === -1) {
            A = e.trim(c.slice(p, D)), E(A), g = !1, p = D + 1;
            continue;
          } else {
            D = k - 1;
            continue;
          }
        else if (k = s(c, D - 1), k === -1) {
          A = e.trim(c.slice(p, D)), A = f(A), E(g, A), g = !1, p = D + 1;
          continue;
        } else
          continue;
    }
    return p < c.length && (g === !1 ? E(c.slice(p)) : E(g, f(e.trim(c.slice(p))))), e.trim(x.join(" "));
  }
  function o(c, h) {
    for (; h < c.length; h++) {
      var p = c[h];
      if (p !== " ")
        return p === "=" ? h : -1;
    }
  }
  function a(c, h) {
    for (; h < c.length; h++) {
      var p = c[h];
      if (p !== " ")
        return p === "'" || p === '"' ? h : -1;
    }
  }
  function s(c, h) {
    for (; h > 0; h--) {
      var p = c[h];
      if (p !== " ")
        return p === "=" ? h : -1;
    }
  }
  function l(c) {
    return c[0] === '"' && c[c.length - 1] === '"' || c[0] === "'" && c[c.length - 1] === "'";
  }
  function f(c) {
    return l(c) ? c.substr(1, c.length - 2) : c;
  }
  return ot.parseTag = r, ot.parseAttr = t, ot;
}
var Xt, Ir;
function Zc() {
  if (Ir) return Xt;
  Ir = 1;
  var e = ln().FilterCSS, u = A0(), n = _0(), r = n.parseTag, i = n.parseAttr, t = Cn();
  function o(c) {
    return c == null;
  }
  function a(c) {
    var h = t.spaceIndex(c);
    if (h === -1)
      return {
        html: "",
        closing: c[c.length - 2] === "/"
      };
    c = t.trim(c.slice(h + 1, -1));
    var p = c[c.length - 1] === "/";
    return p && (c = t.trim(c.slice(0, -1))), {
      html: c,
      closing: p
    };
  }
  function s(c) {
    var h = {};
    for (var p in c)
      h[p] = c[p];
    return h;
  }
  function l(c) {
    var h = {};
    for (var p in c)
      Array.isArray(c[p]) ? h[p.toLowerCase()] = c[p].map(function(d) {
        return d.toLowerCase();
      }) : h[p.toLowerCase()] = c[p];
    return h;
  }
  function f(c) {
    c = s(c || {}), c.stripIgnoreTag && (c.onIgnoreTag && console.error(
      'Notes: cannot use these two options "stripIgnoreTag" and "onIgnoreTag" at the same time'
    ), c.onIgnoreTag = u.onIgnoreTagStripAll), c.whiteList || c.allowList ? c.whiteList = l(c.whiteList || c.allowList) : c.whiteList = u.whiteList, this.attributeWrapSign = c.singleQuotedAttributeValue === !0 ? "'" : u.attributeWrapSign, c.onTag = c.onTag || u.onTag, c.onTagAttr = c.onTagAttr || u.onTagAttr, c.onIgnoreTag = c.onIgnoreTag || u.onIgnoreTag, c.onIgnoreTagAttr = c.onIgnoreTagAttr || u.onIgnoreTagAttr, c.safeAttrValue = c.safeAttrValue || u.safeAttrValue, c.escapeHtml = c.escapeHtml || u.escapeHtml, this.options = c, c.css === !1 ? this.cssFilter = !1 : (c.css = c.css || {}, this.cssFilter = new e(c.css));
  }
  return f.prototype.process = function(c) {
    if (c = c || "", c = c.toString(), !c) return "";
    var h = this, p = h.options, d = p.whiteList, x = p.onTag, g = p.onIgnoreTag, _ = p.onTagAttr, E = p.onIgnoreTagAttr, D = p.safeAttrValue, m = p.escapeHtml, A = h.attributeWrapSign, k = h.cssFilter;
    p.stripBlankChar && (c = u.stripBlankChar(c)), p.allowCommentTag || (c = u.stripCommentTag(c));
    var B = !1;
    p.stripIgnoreTagBody && (B = u.StripTagBody(
      p.stripIgnoreTagBody,
      g
    ), g = B.onIgnoreTag);
    var C = r(
      c,
      function(U, Z, H, O, z) {
        var $ = {
          sourcePosition: U,
          position: Z,
          isClosing: z,
          isWhite: Object.prototype.hasOwnProperty.call(d, H)
        }, N = x(H, O, $);
        if (!o(N)) return N;
        if ($.isWhite) {
          if ($.isClosing)
            return "</" + H + ">";
          var F = a(O), q = d[H], w = i(F.html, function(G, Y) {
            var ie = t.indexOf(q, G) !== -1, ne = _(H, G, Y, ie);
            return o(ne) ? ie ? (Y = D(H, G, Y, k), Y ? G + "=" + A + Y + A : G) : (ne = E(H, G, Y, ie), o(ne) ? void 0 : ne) : ne;
          });
          return O = "<" + H, w && (O += " " + w), F.closing && (O += " /"), O += ">", O;
        } else
          return N = g(H, O, $), o(N) ? m(O) : N;
      },
      m
    );
    return B && (C = B.remove(C)), C;
  }, Xt = f, Xt;
}
var Mr;
function Gc() {
  return Mr || (Mr = 1, (function(e, u) {
    var n = A0(), r = _0(), i = Zc();
    function t(a, s) {
      var l = new i(s);
      return l.process(a);
    }
    u = e.exports = t, u.filterXSS = t, u.FilterXSS = i, (function() {
      for (var a in n)
        u[a] = n[a];
      for (var s in r)
        u[s] = r[s];
    })(), typeof window < "u" && (window.filterXSS = e.exports);
    function o() {
      return typeof self < "u" && typeof DedicatedWorkerGlobalScope < "u" && self instanceof DedicatedWorkerGlobalScope;
    }
    o() && (self.filterXSS = e.exports);
  })(Gt, Gt.exports)), Gt.exports;
}
Gc();
var Jt, Or;
function Vc() {
  if (Or) return Jt;
  Or = 1;
  function e(b) {
    return b instanceof Map ? b.clear = b.delete = b.set = function() {
      throw new Error("map is read-only");
    } : b instanceof Set && (b.add = b.clear = b.delete = function() {
      throw new Error("set is read-only");
    }), Object.freeze(b), Object.getOwnPropertyNames(b).forEach((y) => {
      const I = b[y], W = typeof I;
      (W === "object" || W === "function") && !Object.isFrozen(I) && e(I);
    }), b;
  }
  class u {
    /**
     * @param {CompiledMode} mode
     */
    constructor(y) {
      y.data === void 0 && (y.data = {}), this.data = y.data, this.isMatchIgnored = !1;
    }
    ignoreMatch() {
      this.isMatchIgnored = !0;
    }
  }
  function n(b) {
    return b.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#x27;");
  }
  function r(b, ...y) {
    const I = /* @__PURE__ */ Object.create(null);
    for (const W in b)
      I[W] = b[W];
    return y.forEach(function(W) {
      for (const ge in W)
        I[ge] = W[ge];
    }), /** @type {T} */
    I;
  }
  const i = "</span>", t = (b) => !!b.scope, o = (b, { prefix: y }) => {
    if (b.startsWith("language:"))
      return b.replace("language:", "language-");
    if (b.includes(".")) {
      const I = b.split(".");
      return [
        `${y}${I.shift()}`,
        ...I.map((W, ge) => `${W}${"_".repeat(ge + 1)}`)
      ].join(" ");
    }
    return `${y}${b}`;
  };
  class a {
    /**
     * Creates a new HTMLRenderer
     *
     * @param {Tree} parseTree - the parse tree (must support `walk` API)
     * @param {{classPrefix: string}} options
     */
    constructor(y, I) {
      this.buffer = "", this.classPrefix = I.classPrefix, y.walk(this);
    }
    /**
     * Adds texts to the output stream
     *
     * @param {string} text */
    addText(y) {
      this.buffer += n(y);
    }
    /**
     * Adds a node open to the output stream (if needed)
     *
     * @param {Node} node */
    openNode(y) {
      if (!t(y)) return;
      const I = o(
        y.scope,
        { prefix: this.classPrefix }
      );
      this.span(I);
    }
    /**
     * Adds a node close to the output stream (if needed)
     *
     * @param {Node} node */
    closeNode(y) {
      t(y) && (this.buffer += i);
    }
    /**
     * returns the accumulated buffer
    */
    value() {
      return this.buffer;
    }
    // helpers
    /**
     * Builds a span element
     *
     * @param {string} className */
    span(y) {
      this.buffer += `<span class="${y}">`;
    }
  }
  const s = (b = {}) => {
    const y = { children: [] };
    return Object.assign(y, b), y;
  };
  class l {
    constructor() {
      this.rootNode = s(), this.stack = [this.rootNode];
    }
    get top() {
      return this.stack[this.stack.length - 1];
    }
    get root() {
      return this.rootNode;
    }
    /** @param {Node} node */
    add(y) {
      this.top.children.push(y);
    }
    /** @param {string} scope */
    openNode(y) {
      const I = s({ scope: y });
      this.add(I), this.stack.push(I);
    }
    closeNode() {
      if (this.stack.length > 1)
        return this.stack.pop();
    }
    closeAllNodes() {
      for (; this.closeNode(); ) ;
    }
    toJSON() {
      return JSON.stringify(this.rootNode, null, 4);
    }
    /**
     * @typedef { import("./html_renderer").Renderer } Renderer
     * @param {Renderer} builder
     */
    walk(y) {
      return this.constructor._walk(y, this.rootNode);
    }
    /**
     * @param {Renderer} builder
     * @param {Node} node
     */
    static _walk(y, I) {
      return typeof I == "string" ? y.addText(I) : I.children && (y.openNode(I), I.children.forEach((W) => this._walk(y, W)), y.closeNode(I)), y;
    }
    /**
     * @param {Node} node
     */
    static _collapse(y) {
      typeof y != "string" && y.children && (y.children.every((I) => typeof I == "string") ? y.children = [y.children.join("")] : y.children.forEach((I) => {
        l._collapse(I);
      }));
    }
  }
  class f extends l {
    /**
     * @param {*} options
     */
    constructor(y) {
      super(), this.options = y;
    }
    /**
     * @param {string} text
     */
    addText(y) {
      y !== "" && this.add(y);
    }
    /** @param {string} scope */
    startScope(y) {
      this.openNode(y);
    }
    endScope() {
      this.closeNode();
    }
    /**
     * @param {Emitter & {root: DataNode}} emitter
     * @param {string} name
     */
    __addSublanguage(y, I) {
      const W = y.root;
      I && (W.scope = `language:${I}`), this.add(W);
    }
    toHTML() {
      return new a(this, this.options).value();
    }
    finalize() {
      return this.closeAllNodes(), !0;
    }
  }
  function c(b) {
    return b ? typeof b == "string" ? b : b.source : null;
  }
  function h(b) {
    return x("(?=", b, ")");
  }
  function p(b) {
    return x("(?:", b, ")*");
  }
  function d(b) {
    return x("(?:", b, ")?");
  }
  function x(...b) {
    return b.map((I) => c(I)).join("");
  }
  function g(b) {
    const y = b[b.length - 1];
    return typeof y == "object" && y.constructor === Object ? (b.splice(b.length - 1, 1), y) : {};
  }
  function _(...b) {
    return "(" + (g(b).capture ? "" : "?:") + b.map((W) => c(W)).join("|") + ")";
  }
  function E(b) {
    return new RegExp(b.toString() + "|").exec("").length - 1;
  }
  function D(b, y) {
    const I = b && b.exec(y);
    return I && I.index === 0;
  }
  const m = new RegExp(_(
    /\[(?:[^\\\]]|\\.)*\]/,
    // a character class, inside which ( and \ lose their meaning
    /\(\?<(?![=!])[^>]+>/,
    // a named capture group `(?<name>` (not a lookbehind `(?<=` / `(?<!`)
    /\(\?'[^']+'/,
    // a named capture group `(?'name'`
    /\(\??/,
    // an opening parenthesis, capturing or non-capturing / lookahead
    /\\([1-9][0-9]*)/,
    // a backreference like `\1`
    /\\./
    // any other escape sequence
  ));
  function A(b, { joinWith: y }) {
    let I = 0;
    return b.map((W) => {
      I += 1;
      const ge = I;
      let me = c(W), L = "";
      for (; me.length > 0; ) {
        const R = m.exec(me);
        if (!R) {
          L += me;
          break;
        }
        L += me.substring(0, R.index), me = me.substring(R.index + R[0].length), R[0][0] === "\\" && R[1] ? L += "\\" + String(Number(R[1]) + ge) : (L += R[0], (R[0] === "(" || /^\(\?[<']/.test(R[0])) && I++);
      }
      return L;
    }).map((W) => `(${W})`).join(y);
  }
  const k = /\b\B/, B = "[a-zA-Z]\\w*", C = "[a-zA-Z_]\\w*", U = "\\b\\d+(\\.\\d+)?", Z = "(-?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)", H = "\\b(0b[01]+)", O = "!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~", z = (b = {}) => {
    const y = /^#![ ]*\//;
    return b.binary && (b.begin = x(
      y,
      /.*\b/,
      b.binary,
      /\b.*/
    )), r({
      scope: "meta",
      begin: y,
      end: /$/,
      relevance: 0,
      /** @type {ModeCallback} */
      "on:begin": (I, W) => {
        I.index !== 0 && W.ignoreMatch();
      }
    }, b);
  }, $ = {
    begin: "\\\\[\\s\\S]",
    relevance: 0
  }, N = {
    scope: "string",
    begin: "'",
    end: "'",
    illegal: "\\n",
    contains: [$]
  }, F = {
    scope: "string",
    begin: '"',
    end: '"',
    illegal: "\\n",
    contains: [$]
  }, q = {
    begin: /\b(a|an|the|are|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such|will|you|your|they|like|more)\b/
  }, w = function(b, y, I = {}) {
    const W = r(
      {
        scope: "comment",
        begin: b,
        end: y,
        contains: []
      },
      I
    );
    W.contains.push({
      scope: "doctag",
      // hack to avoid the space from being included. the space is necessary to
      // match here to prevent the plain text rule below from gobbling up doctags
      begin: "[ ]*(?=(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):)",
      end: /(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):/,
      excludeBegin: !0,
      relevance: 0
    });
    const ge = _(
      // list of common 1 and 2 letter words in English
      "I",
      "a",
      "is",
      "so",
      "us",
      "to",
      "at",
      "if",
      "in",
      "it",
      "on",
      // note: this is not an exhaustive list of contractions, just popular ones
      /[A-Za-z]+['](d|ve|re|ll|t|s|n)/,
      // contractions - can't we'd they're let's, etc
      /[A-Za-z]+[-][a-z]+/,
      // `no-way`, etc.
      /[A-Za-z][a-z]{2,}/
      // allow capitalized words at beginning of sentences
    );
    return W.contains.push(
      {
        // TODO: how to include ", (, ) without breaking grammars that use these for
        // comment delimiters?
        // begin: /[ ]+([()"]?([A-Za-z'-]{3,}|is|a|I|so|us|[tT][oO]|at|if|in|it|on)[.]?[()":]?([.][ ]|[ ]|\))){3}/
        // ---
        // this tries to find sequences of 3 english words in a row (without any
        // "programming" type syntax) this gives us a strong signal that we've
        // TRULY found a comment - vs perhaps scanning with the wrong language.
        // It's possible to find something that LOOKS like the start of the
        // comment - but then if there is no readable text - good chance it is a
        // false match and not a comment.
        //
        // for a visual example please see:
        // https://github.com/highlightjs/highlight.js/issues/2827
        begin: x(
          /[ ]+/,
          // necessary to prevent us gobbling up doctags like /* @author Bob Mcgill */
          "(",
          ge,
          /[.]?[:]?([.][ ]|[ ])/,
          "){3}"
        )
        // look for 3 words in a row
      }
    ), W;
  }, G = w("//", "$"), Y = w("/\\*", "\\*/"), ie = w("#", "$"), ne = {
    scope: "number",
    begin: U,
    relevance: 0
  }, ue = {
    scope: "number",
    begin: Z,
    relevance: 0
  }, ae = {
    scope: "number",
    begin: H,
    relevance: 0
  }, pe = {
    scope: "regexp",
    begin: /\/(?=[^/\n]*\/)/,
    end: /\/[gimuy]*/,
    contains: [
      $,
      {
        begin: /\[/,
        end: /\]/,
        relevance: 0,
        contains: [$]
      }
    ]
  }, Re = {
    scope: "title",
    begin: B,
    relevance: 0
  }, iu = {
    scope: "title",
    begin: C,
    relevance: 0
  }, $e = {
    // excludes method names from keyword processing
    begin: "\\.\\s*" + C,
    relevance: 0
  };
  var Ke = /* @__PURE__ */ Object.freeze({
    __proto__: null,
    APOS_STRING_MODE: N,
    BACKSLASH_ESCAPE: $,
    BINARY_NUMBER_MODE: ae,
    BINARY_NUMBER_RE: H,
    COMMENT: w,
    C_BLOCK_COMMENT_MODE: Y,
    C_LINE_COMMENT_MODE: G,
    C_NUMBER_MODE: ue,
    C_NUMBER_RE: Z,
    END_SAME_AS_BEGIN: function(b) {
      return Object.assign(
        b,
        {
          /** @type {ModeCallback} */
          "on:begin": (y, I) => {
            I.data._beginMatch = y[1];
          },
          /** @type {ModeCallback} */
          "on:end": (y, I) => {
            I.data._beginMatch !== y[1] && I.ignoreMatch();
          }
        }
      );
    },
    HASH_COMMENT_MODE: ie,
    IDENT_RE: B,
    MATCH_NOTHING_RE: k,
    METHOD_GUARD: $e,
    NUMBER_MODE: ne,
    NUMBER_RE: U,
    PHRASAL_WORDS_MODE: q,
    QUOTE_STRING_MODE: F,
    REGEXP_MODE: pe,
    RE_STARTERS_RE: O,
    SHEBANG: z,
    TITLE_MODE: Re,
    UNDERSCORE_IDENT_RE: C,
    UNDERSCORE_TITLE_MODE: iu
  });
  function bu(b, y) {
    b.input[b.index - 1] === "." && y.ignoreMatch();
  }
  function Iu(b, y) {
    b.className !== void 0 && (b.scope = b.className, delete b.className);
  }
  function _t(b, y) {
    y && b.beginKeywords && (b.begin = "\\b(" + b.beginKeywords.split(" ").join("|") + ")(?!\\.)(?=\\b|\\s)", b.__beforeBegin = bu, b.keywords = b.keywords || b.beginKeywords, delete b.beginKeywords, b.relevance === void 0 && (b.relevance = 0));
  }
  function Ft(b, y) {
    Array.isArray(b.illegal) && (b.illegal = _(...b.illegal));
  }
  function Mu(b, y) {
    if (b.match) {
      if (b.begin || b.end) throw new Error("begin & end are not supported with match");
      b.begin = b.match, delete b.match;
    }
  }
  function yt(b, y) {
    b.relevance === void 0 && (b.relevance = 1);
  }
  const kt = (b, y) => {
    if (!b.beforeMatch) return;
    if (b.starts) throw new Error("beforeMatch cannot be used with starts");
    const I = Object.assign({}, b);
    Object.keys(b).forEach((W) => {
      delete b[W];
    }), b.keywords = I.keywords, b.begin = x(I.beforeMatch, h(I.begin)), b.starts = {
      relevance: 0,
      contains: [
        Object.assign(I, { endsParent: !0 })
      ]
    }, b.relevance = 0, delete I.beforeMatch;
  }, Ku = [
    "of",
    "and",
    "for",
    "in",
    "not",
    "or",
    "if",
    "then",
    "parent",
    // common variable name
    "list",
    // common variable name
    "value"
    // common variable name
  ], B0 = "keyword";
  function An(b, y, I = B0) {
    const W = /* @__PURE__ */ Object.create(null);
    return typeof b == "string" ? ge(I, b.split(" ")) : Array.isArray(b) ? ge(I, b) : Object.keys(b).forEach(function(me) {
      Object.assign(
        W,
        An(b[me], y, me)
      );
    }), W;
    function ge(me, L) {
      y && (L = L.map((R) => R.toLowerCase())), L.forEach(function(R) {
        const V = R.split("|");
        W[V[0]] = [me, S0(V[0], V[1])];
      });
    }
  }
  function S0(b, y) {
    return y ? Number(y) : T0(b) ? 0 : 1;
  }
  function T0(b) {
    return Ku.includes(b.toLowerCase());
  }
  const _n = {}, gu = (b) => {
    console.error(b);
  }, Fn = (b, ...y) => {
    console.log(`WARN: ${b}`, ...y);
  }, _u = (b, y) => {
    _n[`${b}/${y}`] || (console.log(`Deprecated as of ${b}. ${y}`), _n[`${b}/${y}`] = !0);
  }, Xu = new Error();
  function yn(b, y, { key: I }) {
    let W = 0;
    const ge = b[I], me = {}, L = {};
    for (let R = 1; R <= y.length; R++)
      L[R + W] = ge[R], me[R + W] = !0, W += E(y[R - 1]);
    b[I] = L, b[I]._emit = me, b[I]._multi = !0;
  }
  function I0(b) {
    if (Array.isArray(b.begin)) {
      if (b.skip || b.excludeBegin || b.returnBegin)
        throw gu("skip, excludeBegin, returnBegin not compatible with beginScope: {}"), Xu;
      if (typeof b.beginScope != "object" || b.beginScope === null)
        throw gu("beginScope must be object"), Xu;
      yn(b, b.begin, { key: "beginScope" }), b.begin = A(b.begin, { joinWith: "" });
    }
  }
  function M0(b) {
    if (Array.isArray(b.end)) {
      if (b.skip || b.excludeEnd || b.returnEnd)
        throw gu("skip, excludeEnd, returnEnd not compatible with endScope: {}"), Xu;
      if (typeof b.endScope != "object" || b.endScope === null)
        throw gu("endScope must be object"), Xu;
      yn(b, b.end, { key: "endScope" }), b.end = A(b.end, { joinWith: "" });
    }
  }
  function O0(b) {
    b.scope && typeof b.scope == "object" && b.scope !== null && (b.beginScope = b.scope, delete b.scope);
  }
  function N0(b) {
    O0(b), typeof b.beginScope == "string" && (b.beginScope = { _wrap: b.beginScope }), typeof b.endScope == "string" && (b.endScope = { _wrap: b.endScope }), I0(b), M0(b);
  }
  function R0(b) {
    function y(L, R) {
      return new RegExp(
        c(L),
        "m" + (b.case_insensitive ? "i" : "") + (b.unicodeRegex ? "u" : "") + (R ? "g" : "")
      );
    }
    class I {
      constructor() {
        this.matchIndexes = {}, this.regexes = [], this.matchAt = 1, this.position = 0;
      }
      // @ts-ignore
      addRule(R, V) {
        V.position = this.position++, this.matchIndexes[this.matchAt] = V, this.regexes.push([V, R]), this.matchAt += E(R) + 1;
      }
      compile() {
        this.regexes.length === 0 && (this.exec = () => null);
        const R = this.regexes.map((V) => V[1]);
        this.matcherRe = y(A(R, { joinWith: "|" }), !0), this.lastIndex = 0;
      }
      /** @param {string} s */
      exec(R) {
        this.matcherRe.lastIndex = this.lastIndex;
        const V = this.matcherRe.exec(R);
        if (!V)
          return null;
        const Ce = V.findIndex((Ou, wt) => wt > 0 && Ou !== void 0), Ee = this.matchIndexes[Ce];
        return V.splice(0, Ce), Object.assign(V, Ee);
      }
    }
    class W {
      constructor() {
        this.rules = [], this.multiRegexes = [], this.count = 0, this.lastIndex = 0, this.regexIndex = 0;
      }
      // @ts-ignore
      getMatcher(R) {
        if (this.multiRegexes[R]) return this.multiRegexes[R];
        const V = new I();
        return this.rules.slice(R).forEach(([Ce, Ee]) => V.addRule(Ce, Ee)), V.compile(), this.multiRegexes[R] = V, V;
      }
      resumingScanAtSamePosition() {
        return this.regexIndex !== 0;
      }
      considerAll() {
        this.regexIndex = 0;
      }
      // @ts-ignore
      addRule(R, V) {
        this.rules.push([R, V]), V.type === "begin" && this.count++;
      }
      /** @param {string} s */
      exec(R) {
        const V = this.getMatcher(this.regexIndex);
        V.lastIndex = this.lastIndex;
        let Ce = V.exec(R);
        if (this.resumingScanAtSamePosition() && !(Ce && Ce.index === this.lastIndex)) {
          const Ee = this.getMatcher(0);
          Ee.lastIndex = this.lastIndex + 1, Ce = Ee.exec(R);
        }
        return Ce && (this.regexIndex += Ce.position + 1, this.regexIndex === this.count && this.considerAll()), Ce;
      }
    }
    function ge(L) {
      const R = new W();
      return L.contains.forEach((V) => R.addRule(V.begin, { rule: V, type: "begin" })), L.terminatorEnd && R.addRule(L.terminatorEnd, { type: "end" }), L.illegal && R.addRule(L.illegal, { type: "illegal" }), R;
    }
    function me(L, R) {
      const V = (
        /** @type CompiledMode */
        L
      );
      if (L.isCompiled) return V;
      [
        Iu,
        // do this early so compiler extensions generally don't have to worry about
        // the distinction between match/begin
        Mu,
        N0,
        kt
      ].forEach((Ee) => Ee(L, R)), b.compilerExtensions.forEach((Ee) => Ee(L, R)), L.__beforeBegin = null, [
        _t,
        // do this later so compiler extensions that come earlier have access to the
        // raw array if they wanted to perhaps manipulate it, etc.
        Ft,
        // default to 1 relevance if not specified
        yt
      ].forEach((Ee) => Ee(L, R)), L.isCompiled = !0;
      let Ce = null;
      return typeof L.keywords == "object" && L.keywords.$pattern && (L.keywords = Object.assign({}, L.keywords), Ce = L.keywords.$pattern, delete L.keywords.$pattern), Ce = Ce || /\w+/, L.keywords && (L.keywords = An(L.keywords, b.case_insensitive)), V.keywordPatternRe = y(Ce, !0), R && (L.begin || (L.begin = /\B|\b/), V.beginRe = y(V.begin), !L.end && !L.endsWithParent && (L.end = /\B|\b/), L.end && (V.endRe = y(V.end)), V.terminatorEnd = c(V.end) || "", L.endsWithParent && R.terminatorEnd && (V.terminatorEnd += (L.end ? "|" : "") + R.terminatorEnd)), L.illegal && (V.illegalRe = y(
        /** @type {RegExp | string} */
        L.illegal
      )), L.contains || (L.contains = []), L.contains = [].concat(...L.contains.map(function(Ee) {
        return $0(Ee === "self" ? L : Ee);
      })), L.contains.forEach(function(Ee) {
        me(
          /** @type Mode */
          Ee,
          V
        );
      }), L.starts && me(L.starts, R), V.matcher = ge(V), V;
    }
    if (b.compilerExtensions || (b.compilerExtensions = []), b.contains && b.contains.includes("self"))
      throw new Error("ERR: contains `self` is not supported at the top-level of a language.  See documentation.");
    return b.classNameAliases = r(b.classNameAliases || {}), me(
      /** @type Mode */
      b
    );
  }
  function kn(b) {
    return b ? b.endsWithParent || kn(b.starts) : !1;
  }
  function $0(b) {
    return b.variants && !b.cachedVariants && (b.cachedVariants = b.variants.map(function(y) {
      return r(b, { variants: null }, y);
    })), b.cachedVariants ? b.cachedVariants : kn(b) ? r(b, { starts: b.starts ? r(b.starts) : null }) : Object.isFrozen(b) ? r(b) : b;
  }
  var L0 = "11.12.0";
  class z0 extends Error {
    constructor(y, I) {
      super(y), this.name = "HTMLInjectionError", this.html = I;
    }
  }
  const vt = n, vn = r, wn = Symbol("nomatch"), P0 = 7, Bn = function(b) {
    const y = /* @__PURE__ */ Object.create(null), I = /* @__PURE__ */ Object.create(null), W = [];
    let ge = !0;
    const me = "Could not find the language '{}', did you forget to load/include a language module?", L = { disableAutodetect: !0, name: "Plain text", contains: [] };
    let R = {
      ignoreUnescapedHTML: !1,
      throwUnescapedHTML: !1,
      noHighlightRe: /^(no-?highlight)$/i,
      languageDetectRe: /\blang(?:uage)?-([\w-]+)\b/i,
      classPrefix: "hljs-",
      cssSelector: "pre code",
      languages: null,
      // beta configuration options, subject to change, welcome to discuss
      // https://github.com/highlightjs/highlight.js/issues/1086
      __emitter: f
    };
    function V(S) {
      return R.noHighlightRe.test(S);
    }
    function Ce(S) {
      let j = S.className + " ";
      j += S.parentNode ? S.parentNode.className : "";
      const te = R.languageDetectRe.exec(j);
      if (te) {
        const se = au(te[1]);
        return se || (Fn(me.replace("{}", te[1])), Fn("Falling back to no-highlight mode for this block.", S)), se ? te[1] : "no-highlight";
      }
      return j.split(/\s+/).find((se) => V(se) || au(se));
    }
    function Ee(S, j, te) {
      let se = "", xe = "";
      typeof j == "object" ? (se = S, te = j.ignoreIllegals, xe = j.language) : (_u("10.7.0", "highlight(lang, code, ...args) has been deprecated."), _u("10.7.0", `Please use highlight(code, options) instead.
https://github.com/highlightjs/highlight.js/issues/2277`), xe = S, se = j), te === void 0 && (te = !0);
      const Le = {
        code: se,
        language: xe
      };
      Qu("before:highlight", Le);
      const su = Le.result ? Le.result : Ou(Le.language, Le.code, te);
      return su.code = Le.code, Qu("after:highlight", su), su;
    }
    function Ou(S, j, te, se) {
      const xe = /* @__PURE__ */ Object.create(null);
      function Le(M, P) {
        return M.keywords[P];
      }
      function su() {
        if (!K.keywords) {
          ye.addText(ce);
          return;
        }
        let M = 0;
        K.keywordPatternRe.lastIndex = 0;
        let P = K.keywordPatternRe.exec(ce), Q = "";
        for (; P; ) {
          Q += ce.substring(M, P.index);
          const re = je.case_insensitive ? P[0].toLowerCase() : P[0], ke = Le(K, re);
          if (ke) {
            const [Xe, ni] = ke;
            if (ye.addText(Q), Q = "", xe[re] = (xe[re] || 0) + 1, xe[re] <= P0 && (ut += ni), Xe.startsWith("_"))
              Q += P[0];
            else {
              const ri = je.classNameAliases[Xe] || Xe;
              He(P[0], ri);
            }
          } else
            Q += P[0];
          M = K.keywordPatternRe.lastIndex, P = K.keywordPatternRe.exec(ce);
        }
        Q += ce.substring(M), ye.addText(Q);
      }
      function Yu() {
        if (ce === "") return;
        let M = null;
        if (typeof K.subLanguage == "string") {
          if (!y[K.subLanguage]) {
            ye.addText(ce);
            return;
          }
          M = Ou(K.subLanguage, ce, !0, $n[K.subLanguage]), $n[K.subLanguage] = /** @type {CompiledMode} */
          M._top;
        } else
          M = Bt(ce, K.subLanguage.length ? K.subLanguage : null);
        K.relevance > 0 && (ut += M.relevance), ye.__addSublanguage(M._emitter, M.language);
      }
      function Te() {
        K.subLanguage != null ? Yu() : su(), ce = "";
      }
      function He(M, P) {
        M !== "" && (ye.startScope(P), ye.addText(M), ye.endScope());
      }
      function Mn(M, P) {
        let Q = 1;
        const re = P.length - 1;
        for (; Q <= re; ) {
          if (!M._emit[Q]) {
            Q++;
            continue;
          }
          const ke = je.classNameAliases[M[Q]] || M[Q], Xe = P[Q];
          ke ? He(Xe, ke) : (ce = Xe, su(), ce = ""), Q++;
        }
      }
      function On(M, P) {
        return M.scope && typeof M.scope == "string" && ye.openNode(je.classNameAliases[M.scope] || M.scope), M.beginScope && (M.beginScope._wrap ? (He(ce, je.classNameAliases[M.beginScope._wrap] || M.beginScope._wrap), ce = "") : M.beginScope._multi && (Mn(M.beginScope, P), ce = "")), K = Object.create(M, { parent: { value: K } }), K;
      }
      function Nn(M, P, Q) {
        let re = D(M.endRe, Q);
        if (re) {
          if (M["on:end"]) {
            const ke = new u(M);
            M["on:end"](P, ke), ke.isMatchIgnored && (re = !1);
          }
          if (re) {
            for (; M.endsParent && M.parent; )
              M = M.parent;
            return M;
          }
        }
        if (M.endsWithParent)
          return Nn(M.parent, P, Q);
      }
      function Q0(M) {
        return K.matcher.regexIndex === 0 ? (ce += M[0], 1) : (Mt = !0, 0);
      }
      function Y0(M) {
        const P = M[0], Q = M.rule, re = new u(Q), ke = [Q.__beforeBegin, Q["on:begin"]];
        for (const Xe of ke)
          if (Xe && (Xe(M, re), re.isMatchIgnored))
            return Q0(P);
        return Q.skip ? ce += P : (Q.excludeBegin && (ce += P), Te(), !Q.returnBegin && !Q.excludeBegin && (ce = P)), On(Q, M), Q.returnBegin ? 0 : P.length;
      }
      function ei(M) {
        const P = M[0], Q = j.substring(M.index), re = Nn(K, M, Q);
        if (!re)
          return wn;
        const ke = K;
        K.endScope && K.endScope._wrap ? (Te(), He(P, K.endScope._wrap)) : K.endScope && K.endScope._multi ? (Te(), Mn(K.endScope, M)) : ke.skip ? ce += P : (ke.returnEnd || ke.excludeEnd || (ce += P), Te(), ke.excludeEnd && (ce = P));
        do
          K.scope && ye.closeNode(), !K.skip && !K.subLanguage && (ut += K.relevance), K = K.parent;
        while (K !== re.parent);
        return re.starts && On(re.starts, M), ke.returnEnd ? 0 : P.length;
      }
      function ui() {
        const M = [];
        for (let P = K; P !== je; P = P.parent)
          P.scope && M.unshift(P.scope);
        M.forEach((P) => ye.openNode(P));
      }
      let et = {};
      function Rn(M, P) {
        const Q = P && P[0];
        if (ce += M, Q == null)
          return Te(), 0;
        if (et.type === "begin" && P.type === "end" && et.index === P.index && Q === "") {
          if (ce += j.slice(P.index, P.index + 1), !ge) {
            const re = new Error(`0 width match regex (${S})`);
            throw re.languageName = S, re.badRule = et.rule, re;
          }
          return 1;
        }
        if (et = P, P.type === "begin")
          return Y0(P);
        if (P.type === "illegal" && !te) {
          const re = new Error('Illegal lexeme "' + Q + '" for mode "' + (K.scope || "<unnamed>") + '"');
          throw re.mode = K, re;
        } else if (P.type === "end") {
          const re = ei(P);
          if (re !== wn)
            return re;
        }
        if (P.type === "illegal" && Q === "")
          return P.index === j.length || (ce += `
`), 1;
        if (It > 1e5 && It > P.index * 3)
          throw new Error("potential infinite loop, way more iterations than matches");
        return ce += Q, Q.length;
      }
      const je = au(S);
      if (!je)
        throw gu(me.replace("{}", S)), new Error('Unknown language: "' + S + '"');
      const ti = R0(je);
      let Tt = "", K = se || ti;
      const $n = {}, ye = new R.__emitter(R);
      ui();
      let ce = "", ut = 0, mu = 0, It = 0, Mt = !1;
      try {
        if (je.__emitTokens)
          je.__emitTokens(j, ye);
        else {
          for (K.matcher.considerAll(); ; ) {
            It++, Mt ? Mt = !1 : K.matcher.considerAll(), K.matcher.lastIndex = mu;
            const M = K.matcher.exec(j);
            if (!M) break;
            const P = j.substring(mu, M.index), Q = Rn(P, M);
            mu = M.index + Q;
          }
          Rn(j.substring(mu));
        }
        return ye.finalize(), Tt = ye.toHTML(), {
          language: S,
          value: Tt,
          relevance: ut,
          illegal: !1,
          _emitter: ye,
          _top: K
        };
      } catch (M) {
        if (M.message && M.message.includes("Illegal"))
          return {
            language: S,
            value: vt(j),
            illegal: !0,
            relevance: 0,
            _illegalBy: {
              message: M.message,
              index: mu,
              context: j.slice(mu - 100, mu + 100),
              mode: M.mode,
              resultSoFar: Tt
            },
            _emitter: ye
          };
        if (ge)
          return {
            language: S,
            value: vt(j),
            illegal: !1,
            relevance: 0,
            errorRaised: M,
            _emitter: ye,
            _top: K
          };
        throw M;
      }
    }
    function wt(S) {
      const j = {
        value: vt(S),
        illegal: !1,
        relevance: 0,
        _top: L,
        _emitter: new R.__emitter(R)
      };
      return j._emitter.addText(S), j;
    }
    function Bt(S, j) {
      j = j || R.languages || Object.keys(y);
      const te = wt(S), se = j.filter(au).filter(In).map(
        (Te) => Ou(Te, S, !1)
      );
      se.unshift(te);
      const xe = se.sort((Te, He) => {
        if (Te.relevance !== He.relevance) return He.relevance - Te.relevance;
        if (Te.language && He.language) {
          if (au(Te.language).supersetOf === He.language)
            return 1;
          if (au(He.language).supersetOf === Te.language)
            return -1;
        }
        return 0;
      }), [Le, su] = xe, Yu = Le;
      return Yu.secondBest = su, Yu;
    }
    function H0(S, j, te) {
      const se = j && I[j] || te;
      S.classList.add("hljs"), S.classList.add(`language-${se}`);
    }
    function St(S) {
      let j = null;
      const te = Ce(S);
      if (V(te)) return;
      if (Qu(
        "before:highlightElement",
        { el: S, language: te }
      ), S.dataset.highlighted) {
        console.log("Element previously highlighted. To highlight again, first unset `dataset.highlighted`.", S);
        return;
      }
      if (S.children.length > 0 && (R.ignoreUnescapedHTML || (console.warn("One of your code blocks includes unescaped HTML. This is a potentially serious security risk."), console.warn("https://github.com/highlightjs/highlight.js/wiki/security"), console.warn("The element with unescaped HTML:"), console.warn(S)), R.throwUnescapedHTML))
        throw new z0(
          "One of your code blocks includes unescaped HTML.",
          S.innerHTML
        );
      j = S;
      const se = j.textContent, xe = te ? Ee(se, { language: te, ignoreIllegals: !0 }) : Bt(se);
      S.innerHTML = xe.value, S.dataset.highlighted = "yes", H0(S, te, xe.language), S.result = {
        language: xe.language,
        // TODO: remove with version 11.0
        re: xe.relevance,
        relevance: xe.relevance
      }, xe.secondBest && (S.secondBest = {
        language: xe.secondBest.language,
        relevance: xe.secondBest.relevance
      }), Qu("after:highlightElement", { el: S, result: xe, text: se });
    }
    function j0(S) {
      R = vn(R, S);
    }
    const q0 = () => {
      Ju(), _u("10.6.0", "initHighlighting() deprecated.  Use highlightAll() now.");
    };
    function U0() {
      Ju(), _u("10.6.0", "initHighlightingOnLoad() deprecated.  Use highlightAll() now.");
    }
    let Sn = !1;
    function Ju() {
      function S() {
        Ju();
      }
      if (document.readyState === "loading") {
        Sn || window.addEventListener("DOMContentLoaded", S, !1), Sn = !0;
        return;
      }
      document.querySelectorAll(R.cssSelector).forEach(St);
    }
    function Z0(S, j) {
      let te = null;
      try {
        te = j(b);
      } catch (se) {
        if (gu("Language definition for '{}' could not be registered.".replace("{}", S)), ge)
          gu(se);
        else
          throw se;
        te = L;
      }
      te.name || (te.name = S), y[S] = te, te.rawDefinition = j.bind(null, b), te.aliases && Tn(te.aliases, { languageName: S });
    }
    function G0(S) {
      delete y[S];
      for (const j of Object.keys(I))
        I[j] === S && delete I[j];
    }
    function V0() {
      return Object.keys(y);
    }
    function au(S) {
      return S = (S || "").toLowerCase(), y[S] || y[I[S]];
    }
    function Tn(S, { languageName: j }) {
      typeof S == "string" && (S = [S]), S.forEach((te) => {
        I[te.toLowerCase()] = j;
      });
    }
    function In(S) {
      const j = au(S);
      return j && !j.disableAutodetect;
    }
    function W0(S) {
      S["before:highlightBlock"] && !S["before:highlightElement"] && (S["before:highlightElement"] = (j) => {
        S["before:highlightBlock"](
          Object.assign({ block: j.el }, j)
        );
      }), S["after:highlightBlock"] && !S["after:highlightElement"] && (S["after:highlightElement"] = (j) => {
        S["after:highlightBlock"](
          Object.assign({ block: j.el }, j)
        );
      });
    }
    function K0(S) {
      W0(S), W.push(S);
    }
    function X0(S) {
      const j = W.indexOf(S);
      j !== -1 && W.splice(j, 1);
    }
    function Qu(S, j) {
      const te = S;
      W.forEach(function(se) {
        se[te] && se[te](j);
      });
    }
    function J0(S) {
      return _u("10.7.0", "highlightBlock will be removed entirely in v12.0"), _u("10.7.0", "Please use highlightElement now."), St(S);
    }
    Object.assign(b, {
      highlight: Ee,
      highlightAuto: Bt,
      highlightAll: Ju,
      highlightElement: St,
      // TODO: Remove with v12 API
      highlightBlock: J0,
      configure: j0,
      initHighlighting: q0,
      initHighlightingOnLoad: U0,
      registerLanguage: Z0,
      unregisterLanguage: G0,
      listLanguages: V0,
      getLanguage: au,
      registerAliases: Tn,
      autoDetection: In,
      inherit: vn,
      addPlugin: K0,
      removePlugin: X0
    }), b.debugMode = function() {
      ge = !1;
    }, b.safeMode = function() {
      ge = !0;
    }, b.versionString = L0, b.regex = {
      concat: x,
      lookahead: h,
      either: _,
      optional: d,
      anyNumberOfTimes: p
    };
    for (const S in Ke)
      typeof Ke[S] == "object" && e(Ke[S]);
    return Object.assign(b, Ke), b;
  }, Fu = Bn({});
  return Fu.newInstance = () => Bn({}), Jt = Fu, Fu.HighlightJS = Fu, Fu.default = Fu, Jt;
}
var Wc = /* @__PURE__ */ Vc();
const ru = /* @__PURE__ */ si(Wc);
function Kc(e) {
  const u = e.regex, n = /(?![A-Za-z0-9])(?![$])/, r = u.concat(
    /[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/,
    n
  ), i = u.concat(
    /(\\?[A-Z][a-z0-9_\x7f-\xff]+|\\?[A-Z]+(?=[A-Z][a-z0-9_\x7f-\xff])){1,}/,
    n
  ), t = u.concat(
    /[A-Z]+/,
    n
  ), o = {
    scope: "variable",
    match: "\\$+" + r
  }, a = {
    scope: "meta",
    variants: [
      { begin: /<\?php/, relevance: 10 },
      // boost for obvious PHP
      { begin: /<\?=/ },
      // less relevant per PSR-1 which says not to use short-tags
      { begin: /<\?/, relevance: 0.1 },
      { begin: /\?>/ }
      // end php tag
    ]
  }, s = {
    scope: "subst",
    variants: [
      { begin: /\$\w+/ },
      {
        begin: /\{\$/,
        end: /\}/
      }
    ]
  }, l = e.inherit(e.APOS_STRING_MODE, { illegal: null }), f = e.inherit(e.QUOTE_STRING_MODE, {
    illegal: null,
    contains: e.QUOTE_STRING_MODE.contains.concat(s)
  }), c = {
    begin: /<<<[ \t]*(?:(\w+)|"(\w+)")\n/,
    end: /[ \t]*(\w+)\b/,
    contains: e.QUOTE_STRING_MODE.contains.concat(s),
    "on:begin": ($, N) => {
      N.data._beginMatch = $[1] || $[2];
    },
    "on:end": ($, N) => {
      N.data._beginMatch !== $[1] && N.ignoreMatch();
    }
  }, h = e.END_SAME_AS_BEGIN({
    begin: /<<<[ \t]*'(\w+)'\n/,
    end: /[ \t]*(\w+)\b/
  }), p = `[ 	
]`, d = {
    scope: "string",
    variants: [
      f,
      l,
      c,
      h
    ]
  }, x = {
    scope: "number",
    variants: [
      { begin: "\\b0[bB][01]+(?:_[01]+)*\\b" },
      // Binary w/ underscore support
      { begin: "\\b0[oO][0-7]+(?:_[0-7]+)*\\b" },
      // Octals w/ underscore support
      { begin: "\\b0[xX][\\da-fA-F]+(?:_[\\da-fA-F]+)*\\b" },
      // Hex w/ underscore support
      // Decimals w/ underscore support, with optional fragments and scientific exponent (e) suffix.
      { begin: "(?:\\b\\d+(?:_\\d+)*(\\.(?:\\d+(?:_\\d+)*))?|\\B\\.\\d+)(?:[eE][+-]?\\d+)?" }
    ],
    relevance: 0
  }, g = [
    "false",
    "null",
    "true"
  ], _ = [
    // Magic constants:
    // <https://www.php.net/manual/en/language.constants.predefined.php>
    "__CLASS__",
    "__DIR__",
    "__FILE__",
    "__FUNCTION__",
    "__COMPILER_HALT_OFFSET__",
    "__LINE__",
    "__METHOD__",
    "__NAMESPACE__",
    "__TRAIT__",
    // Function that look like language construct or language construct that look like function:
    // List of keywords that may not require parenthesis
    "die",
    "echo",
    "exit",
    "include",
    "include_once",
    "print",
    "require",
    "require_once",
    // These are not language construct (function) but operate on the currently-executing function and can access the current symbol table
    // 'compact extract func_get_arg func_get_args func_num_args get_called_class get_parent_class ' +
    // Other keywords:
    // <https://www.php.net/manual/en/reserved.php>
    // <https://www.php.net/manual/en/language.types.type-juggling.php>
    "array",
    "abstract",
    "and",
    "as",
    "binary",
    "bool",
    "boolean",
    "break",
    "callable",
    "case",
    "catch",
    "class",
    "clone",
    "const",
    "continue",
    "declare",
    "default",
    "do",
    "double",
    "else",
    "elseif",
    "empty",
    "enddeclare",
    "endfor",
    "endforeach",
    "endif",
    "endswitch",
    "endwhile",
    "enum",
    "eval",
    "extends",
    "final",
    "finally",
    "float",
    "for",
    "foreach",
    "from",
    "global",
    "goto",
    "if",
    "implements",
    "instanceof",
    "insteadof",
    "int",
    "integer",
    "interface",
    "isset",
    "iterable",
    "list",
    "match|0",
    "mixed",
    "new",
    "never",
    "object",
    "or",
    "private",
    "protected",
    "public",
    "readonly",
    "real",
    "return",
    "string",
    "switch",
    "throw",
    "trait",
    "try",
    "unset",
    "use",
    "var",
    "void",
    "while",
    "xor",
    "yield"
  ], E = [
    // Standard PHP library:
    // <https://www.php.net/manual/en/book.spl.php>
    "Error|0",
    "AppendIterator",
    "ArgumentCountError",
    "ArithmeticError",
    "ArrayIterator",
    "ArrayObject",
    "AssertionError",
    "BadFunctionCallException",
    "BadMethodCallException",
    "CachingIterator",
    "CallbackFilterIterator",
    "CompileError",
    "Countable",
    "DirectoryIterator",
    "DivisionByZeroError",
    "DomainException",
    "EmptyIterator",
    "ErrorException",
    "Exception",
    "FilesystemIterator",
    "FilterIterator",
    "GlobIterator",
    "InfiniteIterator",
    "InvalidArgumentException",
    "IteratorIterator",
    "LengthException",
    "LimitIterator",
    "LogicException",
    "MultipleIterator",
    "NoRewindIterator",
    "OutOfBoundsException",
    "OutOfRangeException",
    "OuterIterator",
    "OverflowException",
    "ParentIterator",
    "ParseError",
    "RangeException",
    "RecursiveArrayIterator",
    "RecursiveCachingIterator",
    "RecursiveCallbackFilterIterator",
    "RecursiveDirectoryIterator",
    "RecursiveFilterIterator",
    "RecursiveIterator",
    "RecursiveIteratorIterator",
    "RecursiveRegexIterator",
    "RecursiveTreeIterator",
    "RegexIterator",
    "RuntimeException",
    "SeekableIterator",
    "SplDoublyLinkedList",
    "SplFileInfo",
    "SplFileObject",
    "SplFixedArray",
    "SplHeap",
    "SplMaxHeap",
    "SplMinHeap",
    "SplObjectStorage",
    "SplObserver",
    "SplPriorityQueue",
    "SplQueue",
    "SplStack",
    "SplSubject",
    "SplTempFileObject",
    "TypeError",
    "UnderflowException",
    "UnexpectedValueException",
    "UnhandledMatchError",
    // Reserved interfaces:
    // <https://www.php.net/manual/en/reserved.interfaces.php>
    "ArrayAccess",
    "BackedEnum",
    "Closure",
    "Fiber",
    "Generator",
    "Iterator",
    "IteratorAggregate",
    "Serializable",
    "Stringable",
    "Throwable",
    "Traversable",
    "UnitEnum",
    "WeakReference",
    "WeakMap",
    // Reserved classes:
    // <https://www.php.net/manual/en/reserved.classes.php>
    "Directory",
    "__PHP_Incomplete_Class",
    "parent",
    "php_user_filter",
    "self",
    "static",
    "stdClass"
  ], m = {
    keyword: _,
    literal: (($) => {
      const N = [];
      return $.forEach((F) => {
        N.push(F), F.toLowerCase() === F ? N.push(F.toUpperCase()) : N.push(F.toLowerCase());
      }), N;
    })(g),
    built_in: E
  }, A = ($) => $.map((N) => N.replace(/\|\d+$/, "")), k = { variants: [
    {
      match: [
        /new/,
        u.concat(p, "+"),
        // to prevent built ins from being confused as the class constructor call
        u.concat("(?!", A(E).join("\\b|"), "\\b)"),
        i
      ],
      scope: {
        1: "keyword",
        4: "title.class"
      }
    }
  ] }, B = u.concat(r, "\\b(?!\\()"), C = { variants: [
    {
      match: [
        u.concat(
          /::/,
          u.lookahead(/(?!class\b)/)
        ),
        B
      ],
      scope: { 2: "variable.constant" }
    },
    {
      match: [
        /::/,
        /class/
      ],
      scope: { 2: "variable.language" }
    },
    {
      match: [
        i,
        u.concat(
          /::/,
          u.lookahead(/(?!class\b)/)
        ),
        B
      ],
      scope: {
        1: "title.class",
        3: "variable.constant"
      }
    },
    {
      match: [
        i,
        u.concat(
          "::",
          u.lookahead(/(?!class\b)/)
        )
      ],
      scope: { 1: "title.class" }
    },
    {
      match: [
        i,
        /::/,
        /class/
      ],
      scope: {
        1: "title.class",
        3: "variable.language"
      }
    }
  ] }, U = {
    scope: "attr",
    match: u.concat(r, u.lookahead(":"), u.lookahead(/(?!::)/))
  }, Z = {
    relevance: 0,
    begin: /\(/,
    end: /\)/,
    keywords: m,
    contains: [
      U,
      o,
      C,
      e.C_BLOCK_COMMENT_MODE,
      e.C_LINE_COMMENT_MODE,
      e.HASH_COMMENT_MODE,
      d,
      x,
      k
    ]
  }, H = {
    relevance: 0,
    match: [
      /\b/,
      // to prevent keywords from being confused as the function title
      u.concat("(?!fn\\b|function\\b|", A(_).join("\\b|"), "|", A(E).join("\\b|"), "\\b)"),
      r,
      u.concat(p, "*"),
      u.lookahead(/(?=\()/)
    ],
    scope: { 3: "title.function.invoke" },
    contains: [Z]
  };
  Z.contains.push(H);
  const O = [
    U,
    C,
    e.C_BLOCK_COMMENT_MODE,
    e.C_LINE_COMMENT_MODE,
    e.HASH_COMMENT_MODE,
    d,
    x,
    k
  ], z = {
    begin: u.concat(
      /#\[\s*\\?/,
      u.either(
        i,
        t
      )
    ),
    beginScope: "meta",
    end: /]/,
    endScope: "meta",
    keywords: {
      literal: g,
      keyword: [
        "new",
        "array"
      ]
    },
    contains: [
      {
        begin: /\[/,
        end: /]/,
        keywords: {
          literal: g,
          keyword: [
            "new",
            "array"
          ]
        },
        contains: [
          "self",
          ...O
        ]
      },
      ...O,
      {
        scope: "meta",
        variants: [
          { match: i },
          { match: t }
        ]
      }
    ]
  };
  return {
    case_insensitive: !1,
    keywords: m,
    contains: [
      z,
      e.HASH_COMMENT_MODE,
      e.COMMENT("//", "$"),
      e.COMMENT(
        "/\\*",
        "\\*/",
        { contains: [
          {
            scope: "doctag",
            match: "@[A-Za-z]+"
          }
        ] }
      ),
      {
        match: /__halt_compiler\(\);/,
        keywords: "__halt_compiler",
        starts: {
          scope: "comment",
          end: e.MATCH_NOTHING_RE,
          contains: [
            {
              match: /\?>/,
              scope: "meta",
              endsParent: !0
            }
          ]
        }
      },
      a,
      {
        scope: "variable.language",
        match: /\$this\b/
      },
      o,
      H,
      C,
      {
        match: [
          /const/,
          /\s/,
          r
        ],
        scope: {
          1: "keyword",
          3: "variable.constant"
        }
      },
      k,
      {
        scope: "function",
        relevance: 0,
        beginKeywords: "fn function",
        end: /[;{]/,
        excludeEnd: !0,
        illegal: "[$%\\[]",
        contains: [
          { beginKeywords: "use" },
          e.UNDERSCORE_TITLE_MODE,
          {
            begin: "=>",
            // No markup, just a relevance booster
            endsParent: !0
          },
          {
            scope: "params",
            begin: "\\(",
            end: "\\)",
            excludeBegin: !0,
            excludeEnd: !0,
            keywords: m,
            contains: [
              "self",
              z,
              o,
              C,
              e.C_BLOCK_COMMENT_MODE,
              e.C_LINE_COMMENT_MODE,
              e.HASH_COMMENT_MODE,
              d,
              x
            ]
          }
        ]
      },
      {
        scope: "class",
        variants: [
          {
            beginKeywords: "enum",
            illegal: /[($"]/
          },
          {
            beginKeywords: "class interface trait",
            illegal: /[:($"]/
          }
        ],
        relevance: 0,
        end: /\{/,
        excludeEnd: !0,
        contains: [
          { beginKeywords: "extends implements" },
          e.UNDERSCORE_TITLE_MODE
        ]
      },
      // both use and namespace still use "old style" rules (vs multi-match)
      // because the namespace name can include `\` and we still want each
      // element to be treated as its own *individual* title
      {
        beginKeywords: "namespace",
        relevance: 0,
        end: ";",
        illegal: /[.']/,
        contains: [e.inherit(e.UNDERSCORE_TITLE_MODE, { scope: "title.class" })]
      },
      {
        beginKeywords: "use",
        relevance: 0,
        end: ";",
        contains: [
          // TODO: title.function vs title.class
          {
            match: /\b(as|const|function)\b/,
            scope: "keyword"
          },
          // TODO: could be title.class or title.function
          e.UNDERSCORE_TITLE_MODE
        ]
      },
      d,
      x
    ]
  };
}
const Nr = "[A-Za-z$_][0-9A-Za-z$_]*", Xc = [
  "as",
  // for exports
  "in",
  "of",
  "if",
  "for",
  "while",
  "finally",
  "var",
  "new",
  "function",
  "do",
  "return",
  "void",
  "else",
  "break",
  "catch",
  "instanceof",
  "with",
  "throw",
  "case",
  "default",
  "try",
  "switch",
  "continue",
  "typeof",
  "delete",
  "let",
  "yield",
  "const",
  "class",
  // JS handles these with a special rule
  // "get",
  // "set",
  "debugger",
  "async",
  "await",
  "static",
  "import",
  "from",
  "export",
  "extends",
  // It's reached stage 3, which is "recommended for implementation":
  "using"
], Jc = [
  "true",
  "false",
  "null",
  "undefined",
  "NaN",
  "Infinity"
], F0 = [
  // Fundamental objects
  "Object",
  "Function",
  "Boolean",
  "Symbol",
  // numbers and dates
  "Math",
  "Date",
  "Number",
  "BigInt",
  // text
  "String",
  "RegExp",
  // Indexed collections
  "Array",
  "Float32Array",
  "Float64Array",
  "Int8Array",
  "Uint8Array",
  "Uint8ClampedArray",
  "Int16Array",
  "Int32Array",
  "Uint16Array",
  "Uint32Array",
  "BigInt64Array",
  "BigUint64Array",
  // Keyed collections
  "Set",
  "Map",
  "WeakSet",
  "WeakMap",
  // Structured data
  "ArrayBuffer",
  "SharedArrayBuffer",
  "Atomics",
  "DataView",
  "JSON",
  // Control abstraction objects
  "Promise",
  "Generator",
  "GeneratorFunction",
  "AsyncFunction",
  // Reflection
  "Reflect",
  "Proxy",
  // Internationalization
  "Intl",
  // WebAssembly
  "WebAssembly"
], y0 = [
  "Error",
  "EvalError",
  "InternalError",
  "RangeError",
  "ReferenceError",
  "SyntaxError",
  "TypeError",
  "URIError"
], k0 = [
  "setInterval",
  "setTimeout",
  "clearInterval",
  "clearTimeout",
  "require",
  "exports",
  "eval",
  "isFinite",
  "isNaN",
  "parseFloat",
  "parseInt",
  "decodeURI",
  "decodeURIComponent",
  "encodeURI",
  "encodeURIComponent",
  "escape",
  "unescape"
], Qc = [
  "arguments",
  "this",
  "super",
  "console",
  "window",
  "document",
  "localStorage",
  "sessionStorage",
  "module",
  "self",
  "global"
  // Node.js
], Yc = [].concat(
  k0,
  F0,
  y0
);
function v0(e) {
  const u = e.regex, n = (w, { after: G }) => {
    const Y = "</" + w[0].slice(1);
    return w.input.indexOf(Y, G) !== -1;
  }, r = Nr, i = {
    begin: "<>",
    end: "</>"
  }, t = /<[A-Za-z0-9\\._:-]+\s*\/>/, o = {
    begin: /<[A-Za-z0-9\\._:-]+/,
    end: /\/[A-Za-z0-9\\._:-]+>|\/>/,
    /**
     * @param {RegExpMatchArray} match
     * @param {CallbackResponse} response
     */
    isTrulyOpeningTag: (w, G) => {
      const Y = w[0].length + w.index, ie = w.input[Y];
      if (
        // HTML should not include another raw `<` inside a tag
        // nested type?
        // `<Array<Array<number>>`, etc.
        ie === "<" || // the , gives away that this is not HTML
        // `<T, A extends keyof T, V>`
        ie === ","
      ) {
        G.ignoreMatch();
        return;
      }
      ie === ">" && (n(w, { after: Y }) || G.ignoreMatch());
      let ne;
      const ue = w.input.substring(Y);
      if (ne = ue.match(/^\s*=/)) {
        G.ignoreMatch();
        return;
      }
      if ((ne = ue.match(/^\s+extends\s+/)) && ne.index === 0) {
        G.ignoreMatch();
        return;
      }
    }
  }, a = {
    $pattern: Nr,
    keyword: Xc,
    literal: Jc,
    built_in: Yc,
    "variable.language": Qc
  }, s = "[0-9](_?[0-9])*", l = `\\.(${s})`, f = "0|[1-9](_?[0-9])*|0[0-7]*[89][0-9]*", c = {
    className: "number",
    variants: [
      // DecimalLiteral
      { begin: `(\\b(${f})((${l})|\\.)?|(${l}))[eE][+-]?(${s})\\b` },
      { begin: `\\b(${f})\\b((${l})\\b|\\.)?|(${l})\\b` },
      // DecimalBigIntegerLiteral
      { begin: "\\b(0|[1-9](_?[0-9])*)n\\b" },
      // NonDecimalIntegerLiteral
      { begin: "\\b0[xX][0-9a-fA-F](_?[0-9a-fA-F])*n?\\b" },
      { begin: "\\b0[bB][0-1](_?[0-1])*n?\\b" },
      { begin: "\\b0[oO][0-7](_?[0-7])*n?\\b" },
      // LegacyOctalIntegerLiteral (does not include underscore separators)
      // https://tc39.es/ecma262/#sec-additional-syntax-numeric-literals
      { begin: "\\b0[0-7]+n?\\b" }
    ],
    relevance: 0
  }, h = {
    className: "subst",
    begin: "\\$\\{",
    end: "\\}",
    keywords: a,
    contains: []
    // defined later
  }, p = {
    begin: ".?html`",
    end: "",
    starts: {
      end: "`",
      returnEnd: !1,
      contains: [
        e.BACKSLASH_ESCAPE,
        h
      ],
      subLanguage: "xml"
    }
  }, d = {
    begin: ".?css`",
    end: "",
    starts: {
      end: "`",
      returnEnd: !1,
      contains: [
        e.BACKSLASH_ESCAPE,
        h
      ],
      subLanguage: "css"
    }
  }, x = {
    begin: ".?gql`",
    end: "",
    starts: {
      end: "`",
      returnEnd: !1,
      contains: [
        e.BACKSLASH_ESCAPE,
        h
      ],
      subLanguage: "graphql"
    }
  }, g = {
    className: "string",
    begin: "`",
    end: "`",
    contains: [
      e.BACKSLASH_ESCAPE,
      h
    ]
  }, E = {
    className: "comment",
    variants: [
      e.COMMENT(
        /\/\*\*(?!\/)/,
        "\\*/",
        {
          relevance: 0,
          contains: [
            {
              begin: "(?=@[A-Za-z]+)",
              relevance: 0,
              contains: [
                {
                  className: "doctag",
                  begin: "@[A-Za-z]+"
                },
                {
                  className: "type",
                  begin: "\\{",
                  end: "\\}",
                  excludeEnd: !0,
                  excludeBegin: !0,
                  relevance: 0
                },
                {
                  className: "variable",
                  begin: r + "(?=\\s*(-)|$)",
                  endsParent: !0,
                  relevance: 0
                },
                // eat spaces (not newlines) so we can find
                // types or variables
                {
                  begin: /(?=[^\n])\s/,
                  relevance: 0
                }
              ]
            }
          ]
        }
      ),
      e.C_BLOCK_COMMENT_MODE,
      e.C_LINE_COMMENT_MODE
    ]
  }, D = [
    e.APOS_STRING_MODE,
    e.QUOTE_STRING_MODE,
    p,
    d,
    x,
    g,
    // Skip numbers when they are part of a variable name
    { match: /\$\d+/ },
    c
    // This is intentional:
    // See https://github.com/highlightjs/highlight.js/issues/3288
    // hljs.REGEXP_MODE
  ];
  h.contains = D.concat({
    // we need to pair up {} inside our subst to prevent
    // it from ending too early by matching another }
    begin: /\{/,
    end: /\}/,
    keywords: a,
    contains: [
      "self"
    ].concat(D)
  });
  const m = [].concat(E, h.contains), A = m.concat([
    // eat recursive parens in sub expressions
    {
      begin: /(\s*)\(/,
      end: /\)/,
      keywords: a,
      contains: ["self"].concat(m)
    }
  ]), k = {
    className: "params",
    // convert this to negative lookbehind in v12
    begin: /(\s*)\(/,
    // to match the parms with
    end: /\)/,
    excludeBegin: !0,
    excludeEnd: !0,
    keywords: a,
    contains: A
  }, B = {
    variants: [
      // class Car extends vehicle
      {
        match: [
          /class/,
          /\s+/,
          r,
          /\s+/,
          /extends/,
          /\s+/,
          u.concat(r, "(", u.concat(/\./, r), ")*")
        ],
        scope: {
          1: "keyword",
          3: "title.class",
          5: "keyword",
          7: "title.class.inherited"
        }
      },
      // class Car
      {
        match: [
          /class/,
          /\s+/,
          r
        ],
        scope: {
          1: "keyword",
          3: "title.class"
        }
      }
    ]
  }, C = {
    relevance: 0,
    match: u.either(
      // Hard coded exceptions
      /\bJSON/,
      // Float32Array, OutT
      /\b[A-Z][a-z]+([A-Z][a-z]*|\d)*/,
      // CSSFactory, CSSFactoryT
      /\b[A-Z]{2,}([A-Z][a-z]+|\d)+([A-Z][a-z]*)*/,
      // FPs, FPsT
      /\b[A-Z]{2,}[a-z]+([A-Z][a-z]+|\d)*([A-Z][a-z]*)*/
      // P
      // single letters are not highlighted
      // BLAH
      // this will be flagged as a UPPER_CASE_CONSTANT instead
    ),
    className: "title.class",
    keywords: {
      _: [
        // se we still get relevance credit for JS library classes
        ...F0,
        ...y0
      ]
    }
  }, U = {
    label: "use_strict",
    className: "meta",
    relevance: 10,
    begin: /^\s*['"]use (strict|asm)['"]/
  }, Z = {
    variants: [
      {
        match: [
          /function/,
          /\s+/,
          r,
          /(?=\s*\()/
        ]
      },
      // anonymous function
      {
        match: [
          /function/,
          /\s*(?=\()/
        ]
      }
    ],
    className: {
      1: "keyword",
      3: "title.function"
    },
    label: "func.def",
    contains: [k],
    illegal: /%/
  }, H = {
    relevance: 0,
    match: /\b[A-Z][A-Z_0-9]+\b/,
    className: "variable.constant"
  };
  function O(w) {
    return u.concat("(?!", w.join("|"), ")");
  }
  const z = {
    match: u.concat(
      /\b/,
      O([
        ...k0,
        "super",
        "import",
        "await"
      ].map((w) => `${w}\\s*\\(`)),
      r,
      u.lookahead(/\s*\(/)
    ),
    className: "title.function",
    relevance: 0
  }, $ = {
    begin: u.concat(/\./, u.lookahead(
      u.concat(r, /(?![0-9A-Za-z$_(])/)
    )),
    end: r,
    excludeBegin: !0,
    keywords: "prototype",
    className: "property",
    relevance: 0
  }, N = {
    match: [
      /get|set/,
      /\s+/,
      r,
      /(?=\()/
    ],
    className: {
      1: "keyword",
      3: "title.function"
    },
    contains: [
      {
        // eat to avoid empty params
        begin: /\(\)/
      },
      k
    ]
  }, F = "(\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)|" + e.UNDERSCORE_IDENT_RE + ")\\s*=>", q = {
    match: [
      /const|var|let/,
      /\s+/,
      r,
      /\s*/,
      /=\s*/,
      /(async\s*)?/,
      // async is optional
      u.lookahead(F)
    ],
    keywords: "async",
    className: {
      1: "keyword",
      3: "title.function"
    },
    contains: [
      k
    ]
  };
  return {
    name: "JavaScript",
    aliases: ["js", "jsx", "mjs", "cjs"],
    keywords: a,
    // this will be extended by TypeScript
    exports: { PARAMS_CONTAINS: A, CLASS_REFERENCE: C },
    illegal: /#(?![$_A-Za-z])/,
    contains: [
      e.SHEBANG({
        label: "shebang",
        binary: "node",
        relevance: 5
      }),
      U,
      e.APOS_STRING_MODE,
      e.QUOTE_STRING_MODE,
      p,
      d,
      x,
      g,
      E,
      // Skip numbers when they are part of a variable name
      { match: /\$\d+/ },
      c,
      C,
      {
        scope: "attr",
        match: r + u.lookahead(":"),
        relevance: 0
      },
      q,
      {
        // "value" container
        begin: "(" + e.RE_STARTERS_RE + "|\\b(case|return|throw)\\b)\\s*",
        keywords: "return throw case",
        relevance: 0,
        contains: [
          E,
          e.REGEXP_MODE,
          {
            className: "function",
            // we have to count the parens to make sure we actually have the
            // correct bounding ( ) before the =>.  There could be any number of
            // sub-expressions inside also surrounded by parens.
            begin: F,
            returnBegin: !0,
            end: "\\s*=>",
            contains: [
              {
                className: "params",
                variants: [
                  {
                    begin: e.UNDERSCORE_IDENT_RE,
                    relevance: 0
                  },
                  {
                    className: null,
                    begin: /\(\s*\)/,
                    skip: !0
                  },
                  {
                    begin: /(\s*)\(/,
                    end: /\)/,
                    excludeBegin: !0,
                    excludeEnd: !0,
                    keywords: a,
                    contains: A
                  }
                ]
              }
            ]
          },
          {
            // could be a comma delimited list of params to a function call
            begin: /,/,
            relevance: 0
          },
          {
            match: /\s+/,
            relevance: 0
          },
          {
            // JSX
            variants: [
              { begin: i.begin, end: i.end },
              { match: t },
              {
                begin: o.begin,
                // we carefully check the opening tag to see if it truly
                // is a tag and not a false positive
                "on:begin": o.isTrulyOpeningTag,
                end: o.end
              }
            ],
            subLanguage: "xml",
            contains: [
              {
                begin: o.begin,
                end: o.end,
                skip: !0,
                contains: ["self"]
              }
            ]
          }
        ]
      },
      Z,
      {
        // prevent this from getting swallowed up by function
        // since they appear "function like"
        beginKeywords: "while if switch catch for"
      },
      {
        // we have to count the parens to make sure we actually have the correct
        // bounding ( ).  There could be any number of sub-expressions inside
        // also surrounded by parens.
        begin: "\\b(?!function)" + e.UNDERSCORE_IDENT_RE + "\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)\\s*\\{",
        // end parens
        returnBegin: !0,
        label: "func.def",
        contains: [
          k,
          e.inherit(e.TITLE_MODE, { begin: r, className: "title.function" })
        ]
      },
      // catch ... so it won't trigger the property rule below
      {
        match: /\.\.\./,
        relevance: 0
      },
      $,
      // hack: prevents detection of keywords in some circumstances
      // .keyword()
      // $keyword = x
      {
        match: "\\$" + r,
        relevance: 0
      },
      {
        match: [/\bconstructor(?=\s*\()/],
        className: { 1: "title.function" },
        contains: [k]
      },
      z,
      H,
      B,
      N,
      {
        match: /\$[(.]/
        // relevance booster for a pattern common to JS libs: `$(something)` and `$.something`
      }
    ]
  };
}
function el(e) {
  const u = e.regex, n = e.COMMENT("--", "$"), r = {
    scope: "string",
    variants: [
      {
        begin: /'/,
        end: /'/,
        contains: [{ match: /''/ }]
      }
    ]
  }, i = {
    begin: /"/,
    end: /"/,
    contains: [{ match: /""/ }]
  }, t = [
    "true",
    "false",
    // Not sure it's correct to call NULL literal, and clauses like IS [NOT] NULL look strange that way.
    // "null",
    "unknown"
  ], o = [
    "double precision",
    "large object",
    "with timezone",
    "without timezone"
  ], a = [
    "bigint",
    "binary",
    "blob",
    "boolean",
    "char",
    "character",
    "clob",
    "date",
    "dec",
    "decfloat",
    "decimal",
    "float",
    "int",
    "integer",
    "interval",
    "nchar",
    "nclob",
    "national",
    "numeric",
    "real",
    "row",
    "smallint",
    "time",
    "timestamp",
    "varchar",
    "varying",
    // modifier (character varying)
    "varbinary"
  ], s = [
    "add",
    "asc",
    "collation",
    "desc",
    "final",
    "first",
    "last",
    "view"
  ], l = [
    "abs",
    "acos",
    "all",
    "allocate",
    "alter",
    "and",
    "any",
    "are",
    "array",
    "array_agg",
    "array_max_cardinality",
    "as",
    "asensitive",
    "asin",
    "asymmetric",
    "at",
    "atan",
    "atomic",
    "authorization",
    "avg",
    "begin",
    "begin_frame",
    "begin_partition",
    "between",
    "bigint",
    "binary",
    "blob",
    "boolean",
    "both",
    "by",
    "call",
    "called",
    "cardinality",
    "cascaded",
    "case",
    "cast",
    "ceil",
    "ceiling",
    "char",
    "char_length",
    "character",
    "character_length",
    "check",
    "classifier",
    "clob",
    "close",
    "coalesce",
    "collate",
    "collect",
    "column",
    "commit",
    "condition",
    "connect",
    "constraint",
    "contains",
    "convert",
    "copy",
    "corr",
    "corresponding",
    "cos",
    "cosh",
    "count",
    "covar_pop",
    "covar_samp",
    "create",
    "cross",
    "cube",
    "cume_dist",
    "current",
    "current_catalog",
    "current_date",
    "current_default_transform_group",
    "current_path",
    "current_role",
    "current_row",
    "current_schema",
    "current_time",
    "current_timestamp",
    "current_path",
    "current_role",
    "current_transform_group_for_type",
    "current_user",
    "cursor",
    "cycle",
    "date",
    "day",
    "deallocate",
    "dec",
    "decimal",
    "decfloat",
    "declare",
    "default",
    "define",
    "delete",
    "dense_rank",
    "deref",
    "describe",
    "deterministic",
    "disconnect",
    "distinct",
    "double",
    "drop",
    "dynamic",
    "each",
    "element",
    "else",
    "empty",
    "end",
    "end_frame",
    "end_partition",
    "end-exec",
    "equals",
    "escape",
    "every",
    "except",
    "exec",
    "execute",
    "exists",
    "exp",
    "external",
    "extract",
    "false",
    "fetch",
    "filter",
    "first_value",
    "float",
    "floor",
    "for",
    "foreign",
    "frame_row",
    "free",
    "from",
    "full",
    "function",
    "fusion",
    "get",
    "global",
    "grant",
    "group",
    "grouping",
    "groups",
    "having",
    "hold",
    "hour",
    "identity",
    "in",
    "indicator",
    "initial",
    "inner",
    "inout",
    "insensitive",
    "insert",
    "int",
    "integer",
    "intersect",
    "intersection",
    "interval",
    "into",
    "is",
    "join",
    "json_array",
    "json_arrayagg",
    "json_exists",
    "json_object",
    "json_objectagg",
    "json_query",
    "json_table",
    "json_table_primitive",
    "json_value",
    "lag",
    "language",
    "large",
    "last_value",
    "lateral",
    "lead",
    "leading",
    "left",
    "like",
    "like_regex",
    "listagg",
    "ln",
    "local",
    "localtime",
    "localtimestamp",
    "log",
    "log10",
    "lower",
    "match",
    "match_number",
    "match_recognize",
    "matches",
    "max",
    "member",
    "merge",
    "method",
    "min",
    "minute",
    "mod",
    "modifies",
    "module",
    "month",
    "multiset",
    "national",
    "natural",
    "nchar",
    "nclob",
    "new",
    "no",
    "none",
    "normalize",
    "not",
    "nth_value",
    "ntile",
    "null",
    "nullif",
    "numeric",
    "octet_length",
    "occurrences_regex",
    "of",
    "offset",
    "old",
    "omit",
    "on",
    "one",
    "only",
    "open",
    "or",
    "order",
    "out",
    "outer",
    "over",
    "overlaps",
    "overlay",
    "parameter",
    "partition",
    "pattern",
    "per",
    "percent",
    "percent_rank",
    "percentile_cont",
    "percentile_disc",
    "period",
    "portion",
    "position",
    "position_regex",
    "power",
    "precedes",
    "precision",
    "prepare",
    "primary",
    "procedure",
    "ptf",
    "range",
    "rank",
    "reads",
    "real",
    "recursive",
    "ref",
    "references",
    "referencing",
    "regr_avgx",
    "regr_avgy",
    "regr_count",
    "regr_intercept",
    "regr_r2",
    "regr_slope",
    "regr_sxx",
    "regr_sxy",
    "regr_syy",
    "release",
    "result",
    "return",
    "returns",
    "revoke",
    "right",
    "rollback",
    "rollup",
    "row",
    "row_number",
    "rows",
    "running",
    "savepoint",
    "scope",
    "scroll",
    "search",
    "second",
    "seek",
    "select",
    "sensitive",
    "session_user",
    "set",
    "show",
    "similar",
    "sin",
    "sinh",
    "skip",
    "smallint",
    "some",
    "specific",
    "specifictype",
    "sql",
    "sqlexception",
    "sqlstate",
    "sqlwarning",
    "sqrt",
    "start",
    "static",
    "stddev_pop",
    "stddev_samp",
    "submultiset",
    "subset",
    "substring",
    "substring_regex",
    "succeeds",
    "sum",
    "symmetric",
    "system",
    "system_time",
    "system_user",
    "table",
    "tablesample",
    "tan",
    "tanh",
    "then",
    "time",
    "timestamp",
    "timezone_hour",
    "timezone_minute",
    "to",
    "trailing",
    "translate",
    "translate_regex",
    "translation",
    "treat",
    "trigger",
    "trim",
    "trim_array",
    "true",
    "truncate",
    "uescape",
    "union",
    "unique",
    "unknown",
    "unnest",
    "update",
    "upper",
    "user",
    "using",
    "value",
    "values",
    "value_of",
    "var_pop",
    "var_samp",
    "varbinary",
    "varchar",
    "varying",
    "versioning",
    "when",
    "whenever",
    "where",
    "width_bucket",
    "window",
    "with",
    "within",
    "without",
    "year"
  ], f = [
    "abs",
    "acos",
    "array_agg",
    "asin",
    "atan",
    "avg",
    "cast",
    "ceil",
    "ceiling",
    "coalesce",
    "corr",
    "cos",
    "cosh",
    "count",
    "covar_pop",
    "covar_samp",
    "cume_dist",
    "dense_rank",
    "deref",
    "element",
    "exp",
    "extract",
    "first_value",
    "floor",
    "json_array",
    "json_arrayagg",
    "json_exists",
    "json_object",
    "json_objectagg",
    "json_query",
    "json_table",
    "json_table_primitive",
    "json_value",
    "lag",
    "last_value",
    "lead",
    "listagg",
    "ln",
    "log",
    "log10",
    "lower",
    "max",
    "min",
    "mod",
    "nth_value",
    "ntile",
    "nullif",
    "percent_rank",
    "percentile_cont",
    "percentile_disc",
    "position",
    "position_regex",
    "power",
    "rank",
    "regr_avgx",
    "regr_avgy",
    "regr_count",
    "regr_intercept",
    "regr_r2",
    "regr_slope",
    "regr_sxx",
    "regr_sxy",
    "regr_syy",
    "row_number",
    "sin",
    "sinh",
    "sqrt",
    "stddev_pop",
    "stddev_samp",
    "substring",
    "substring_regex",
    "sum",
    "tan",
    "tanh",
    "translate",
    "translate_regex",
    "treat",
    "trim",
    "trim_array",
    "unnest",
    "upper",
    "value_of",
    "var_pop",
    "var_samp",
    "width_bucket"
  ], c = [
    "current_catalog",
    "current_date",
    "current_default_transform_group",
    "current_path",
    "current_role",
    "current_schema",
    "current_transform_group_for_type",
    "current_user",
    "session_user",
    "system_time",
    "system_user",
    "current_time",
    "localtime",
    "current_timestamp",
    "localtimestamp"
  ], h = [
    "create table",
    "insert into",
    "primary key",
    "foreign key",
    "not null",
    "alter table",
    "add constraint",
    "grouping sets",
    "on overflow",
    "character set",
    "respect nulls",
    "ignore nulls",
    "nulls first",
    "nulls last",
    "depth first",
    "breadth first"
  ], p = f, d = [
    ...l,
    ...s
  ].filter((A) => !f.includes(A)), x = {
    scope: "variable",
    match: /@[a-z0-9][a-z0-9_]*/
  }, g = {
    scope: "operator",
    match: /[-+*/=%^~]|&&?|\|\|?|!=?|<(?:=>?|<|>)?|>[>=]?/,
    relevance: 0
  }, _ = {
    match: u.concat(/\b/, u.either(...p), /\s*\(/),
    relevance: 0,
    keywords: { built_in: p }
  };
  function E(A) {
    return u.concat(
      /\b/,
      u.either(...A.map((k) => k.replace(/\s+/, "\\s+"))),
      /\b/
    );
  }
  const D = {
    scope: "keyword",
    match: E(h),
    relevance: 0
  };
  function m(A, {
    exceptions: k,
    when: B
  } = {}) {
    const C = B;
    return k = k || [], A.map((U) => U.match(/\|\d+$/) || k.includes(U) ? U : C(U) ? `${U}|0` : U);
  }
  return {
    name: "SQL",
    case_insensitive: !0,
    // does not include {} or HTML tags `</`
    illegal: /[{}]|<\//,
    keywords: {
      $pattern: /\b[\w\.]+/,
      keyword: m(d, { when: (A) => A.length < 3 }),
      literal: t,
      type: a,
      built_in: c
    },
    contains: [
      {
        scope: "type",
        match: E(o)
      },
      D,
      _,
      x,
      r,
      i,
      e.C_NUMBER_MODE,
      e.C_BLOCK_COMMENT_MODE,
      n,
      g
    ]
  };
}
const ul = "([-+]?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)|NaN|[-+]?Infinity", tl = {
  scope: "number",
  match: ul,
  relevance: 0
};
function nl(e) {
  const u = {
    className: "attr",
    begin: /(("(\\.|[^\\"\r\n])*")|('(\\.|[^\\'\r\n])*'))(?=\s*:)/,
    relevance: 1.01
  }, n = {
    match: /[{}[\],:]/,
    className: "punctuation",
    relevance: 0
  }, r = [
    "true",
    "false",
    "null"
  ], i = {
    scope: "literal",
    beginKeywords: r.join(" ")
  };
  return {
    name: "JSON",
    aliases: ["jsonc", "json5"],
    keywords: {
      literal: r
    },
    contains: [
      u,
      n,
      e.APOS_STRING_MODE,
      e.QUOTE_STRING_MODE,
      i,
      tl,
      e.C_LINE_COMMENT_MODE,
      e.C_BLOCK_COMMENT_MODE
    ],
    illegal: "\\S"
  };
}
function w0(e) {
  const u = e.regex, n = u.concat(/[\p{L}_]/u, u.optional(/[\p{L}0-9_.-]*:/u), /[\p{L}0-9_.-]*/u), r = /[\p{L}0-9._:-]+/u, i = {
    className: "symbol",
    begin: /&[a-z]+;|&#[0-9]+;|&#x[a-f0-9]+;/
  }, t = {
    begin: /\s/,
    contains: [
      {
        className: "keyword",
        begin: /#?[a-z_][a-z1-9_-]+/,
        illegal: /\n/
      }
    ]
  }, o = e.inherit(t, {
    begin: /\(/,
    end: /\)/
  }), a = e.inherit(e.APOS_STRING_MODE, { className: "string" }), s = e.inherit(e.QUOTE_STRING_MODE, { className: "string" }), l = {
    endsWithParent: !0,
    illegal: /</,
    relevance: 0,
    contains: [
      {
        className: "attr",
        begin: r,
        relevance: 0
      },
      {
        begin: /=\s*/,
        relevance: 0,
        contains: [
          {
            className: "string",
            endsParent: !0,
            variants: [
              {
                begin: /"/,
                end: /"/,
                contains: [i]
              },
              {
                begin: /'/,
                end: /'/,
                contains: [i]
              },
              { begin: /[^\s"'=<>`]+/ }
            ]
          }
        ]
      }
    ]
  };
  return {
    name: "HTML, XML",
    aliases: [
      "html",
      "xhtml",
      "rss",
      "atom",
      "xjb",
      "xsd",
      "xsl",
      "plist",
      "wsf",
      "svg"
    ],
    case_insensitive: !0,
    unicodeRegex: !0,
    contains: [
      {
        className: "meta",
        begin: /<![a-z]/,
        end: />/,
        relevance: 10,
        contains: [
          t,
          s,
          a,
          o,
          {
            begin: /\[/,
            end: /\]/,
            contains: [
              {
                className: "meta",
                begin: /<![a-z]/,
                end: />/,
                contains: [
                  t,
                  o,
                  s,
                  a
                ]
              }
            ]
          }
        ]
      },
      e.COMMENT(
        /<!--/,
        /-->/,
        { relevance: 10 }
      ),
      {
        begin: /<!\[CDATA\[/,
        end: /\]\]>/,
        relevance: 10
      },
      i,
      // xml processing instructions
      {
        className: "meta",
        end: /\?>/,
        variants: [
          {
            begin: /<\?xml/,
            relevance: 10,
            contains: [
              s
            ]
          },
          {
            begin: /<\?[a-z][a-z0-9]+/
          }
        ]
      },
      {
        className: "tag",
        /*
        The lookahead pattern (?=...) ensures that 'begin' only matches
        '<style' as a single word, followed by a whitespace or an
        ending bracket.
        */
        begin: /<style(?=\s|>)/,
        end: />/,
        keywords: { name: "style" },
        contains: [l],
        starts: {
          end: /<\/style>/,
          returnEnd: !0,
          subLanguage: "css"
        }
      },
      {
        className: "tag",
        // See the comment in the <style tag about the lookahead pattern
        begin: /<script(?=\s|>)/,
        end: />/,
        keywords: { name: "script" },
        contains: [l],
        starts: {
          end: /<\/script>/,
          returnEnd: !0,
          subLanguage: "javascript"
        }
      },
      // we need this for now for jSX
      {
        className: "tag",
        begin: /<>|<\/>/
      },
      // open tag
      {
        className: "tag",
        begin: u.concat(
          /</,
          u.lookahead(u.concat(
            n,
            // <tag/>
            // <tag>
            // <tag ...
            u.either(/\/>/, />/, /\s/)
          ))
        ),
        end: /\/?>/,
        contains: [
          {
            className: "name",
            begin: n,
            relevance: 0,
            starts: l
          }
        ]
      },
      // close tag
      {
        className: "tag",
        begin: u.concat(
          /<\//,
          u.lookahead(u.concat(
            n,
            />/
          ))
        ),
        contains: [
          {
            className: "name",
            begin: n,
            relevance: 0
          },
          {
            begin: />/,
            relevance: 0,
            endsParent: !0
          }
        ]
      }
    ]
  };
}
function rl(e) {
  const u = e.regex, n = {}, r = {
    begin: /\$\{/,
    end: /\}/,
    contains: [
      "self",
      {
        begin: /:-/,
        contains: [n]
      }
      // default values
    ]
  };
  Object.assign(n, {
    className: "variable",
    variants: [
      { begin: u.concat(
        /\$[\w\d#@][\w\d_]*/,
        // negative look-ahead tries to avoid matching patterns that are not
        // Perl at all like $ident$, @ident@, etc.
        "(?![\\w\\d])(?![$])"
      ) },
      r
    ]
  });
  const i = {
    className: "subst",
    begin: /\$\(/,
    end: /\)/,
    contains: [e.BACKSLASH_ESCAPE]
  }, t = e.inherit(
    e.COMMENT(),
    {
      match: [
        /(^|\s)/,
        /#.*$/
      ],
      scope: {
        2: "comment"
      }
    }
  ), o = {
    begin: /<<-?\s*(?=\w+)/,
    starts: { contains: [
      e.END_SAME_AS_BEGIN({
        begin: /(\w+)/,
        end: /(\w+)/,
        className: "string"
      })
    ] }
  }, a = {
    className: "string",
    begin: /"/,
    end: /"/,
    contains: [
      e.BACKSLASH_ESCAPE,
      n,
      i
    ]
  };
  i.contains.push(a);
  const s = {
    match: /\\"/
  }, l = {
    className: "string",
    begin: /'/,
    end: /'/
  }, f = {
    match: /\\'/
  }, c = {
    begin: /\$?\(\(/,
    end: /\)\)/,
    contains: [
      {
        begin: /\d+#[0-9a-f]+/,
        className: "number"
      },
      e.NUMBER_MODE,
      n
    ]
  }, h = [
    "fish",
    "bash",
    "zsh",
    "sh",
    "csh",
    "ksh",
    "tcsh",
    "dash",
    "scsh"
  ], p = e.SHEBANG({
    binary: `(${h.join("|")})`,
    relevance: 10
  }), d = {
    className: "function",
    begin: /\w[\w\d_]*\s*\(\s*\)\s*\{/,
    returnBegin: !0,
    contains: [e.inherit(e.TITLE_MODE, { begin: /\w[\w\d_]*/ })],
    relevance: 0
  }, x = [
    "if",
    "then",
    "else",
    "elif",
    "fi",
    "time",
    "for",
    "while",
    "until",
    "in",
    "do",
    "done",
    "case",
    "esac",
    "coproc",
    "function",
    "select"
  ], g = [
    "true",
    "false"
  ], _ = { match: /(\/[a-z._-]+)+/ }, E = [
    "break",
    "cd",
    "continue",
    "eval",
    "exec",
    "exit",
    "export",
    "getopts",
    "hash",
    "pwd",
    "readonly",
    "return",
    "shift",
    "test",
    "times",
    "trap",
    "umask",
    "unset"
  ], D = [
    "alias",
    "bind",
    "builtin",
    "caller",
    "command",
    "declare",
    "echo",
    "enable",
    "help",
    "let",
    "local",
    "logout",
    "mapfile",
    "printf",
    "read",
    "readarray",
    "source",
    "sudo",
    "type",
    "typeset",
    "ulimit",
    "unalias"
  ], m = [
    "autoload",
    "bg",
    "bindkey",
    "bye",
    "cap",
    "chdir",
    "clone",
    "comparguments",
    "compcall",
    "compctl",
    "compdescribe",
    "compfiles",
    "compgroups",
    "compquote",
    "comptags",
    "comptry",
    "compvalues",
    "dirs",
    "disable",
    "disown",
    "echotc",
    "echoti",
    "emulate",
    "fc",
    "fg",
    "float",
    "functions",
    "getcap",
    "getln",
    "history",
    "integer",
    "jobs",
    "kill",
    "limit",
    "log",
    "noglob",
    "popd",
    "print",
    "pushd",
    "pushln",
    "rehash",
    "sched",
    "setcap",
    "setopt",
    "stat",
    "suspend",
    "ttyctl",
    "unfunction",
    "unhash",
    "unlimit",
    "unsetopt",
    "vared",
    "wait",
    "whence",
    "where",
    "which",
    "zcompile",
    "zformat",
    "zftp",
    "zle",
    "zmodload",
    "zparseopts",
    "zprof",
    "zpty",
    "zregexparse",
    "zsocket",
    "zstyle",
    "ztcp"
  ], A = [
    "chcon",
    "chgrp",
    "chown",
    "chmod",
    "cp",
    "dd",
    "df",
    "dir",
    "dircolors",
    "ln",
    "ls",
    "mkdir",
    "mkfifo",
    "mknod",
    "mktemp",
    "mv",
    "realpath",
    "rm",
    "rmdir",
    "shred",
    "sync",
    "touch",
    "truncate",
    "vdir",
    "b2sum",
    "base32",
    "base64",
    "cat",
    "cksum",
    "comm",
    "csplit",
    "cut",
    "expand",
    "fmt",
    "fold",
    "head",
    "join",
    "md5sum",
    "nl",
    "numfmt",
    "od",
    "paste",
    "ptx",
    "pr",
    "sha1sum",
    "sha224sum",
    "sha256sum",
    "sha384sum",
    "sha512sum",
    "shuf",
    "sort",
    "split",
    "sum",
    "tac",
    "tail",
    "tr",
    "tsort",
    "unexpand",
    "uniq",
    "wc",
    "arch",
    "basename",
    "chroot",
    "date",
    "dirname",
    "du",
    "echo",
    "env",
    "expr",
    "factor",
    // "false", // keyword literal already
    "groups",
    "hostid",
    "id",
    "link",
    "logname",
    "nice",
    "nohup",
    "nproc",
    "pathchk",
    "pinky",
    "printenv",
    "printf",
    "pwd",
    "readlink",
    "runcon",
    "seq",
    "sleep",
    "stat",
    "stdbuf",
    "stty",
    "tee",
    "test",
    "timeout",
    // "true", // keyword literal already
    "tty",
    "uname",
    "unlink",
    "uptime",
    "users",
    "who",
    "whoami",
    "yes"
  ];
  return {
    name: "Bash",
    aliases: [
      "sh",
      "zsh"
    ],
    keywords: {
      $pattern: /\b[a-z][a-z0-9._-]+\b/,
      keyword: x,
      literal: g,
      built_in: [
        ...E,
        ...D,
        // Shell modifiers
        "set",
        "shopt",
        ...m,
        ...A
      ]
    },
    contains: [
      p,
      // to catch known shells and boost relevancy
      e.SHEBANG(),
      // to catch unknown shells but still highlight the shebang
      d,
      c,
      t,
      o,
      _,
      a,
      s,
      l,
      f,
      n
    ]
  };
}
ru.registerLanguage("php", Kc);
ru.registerLanguage("javascript", v0);
ru.registerLanguage("js", v0);
ru.registerLanguage("sql", el);
ru.registerLanguage("json", nl);
ru.registerLanguage("xml", w0);
ru.registerLanguage("html", w0);
ru.registerLanguage("bash", rl);
Ni({
  // ⚠️ По умолчанию подсветка, формулы и диаграммы грузятся с unpkg. В локальной
  // сети и за периметром это просто не работает, поэтому отдаём свой экземпляр
  // highlight.js, собранный в бандл.
  editorExtensions: {
    highlight: { instance: ru }
  },
  // Подписи интерфейса (кнопка копирования кода и подсказки) по умолчанию
  // китайские — объявляем свои.
  editorConfig: {
    languageUserDefined: {
      "ru-RU": {
        copyCode: {
          text: "Копировать",
          successTips: "Скопировано",
          failTips: "Не удалось скопировать"
        },
        mermaid: {
          flow: "схема",
          sequence: "последовательность",
          gantt: "Гантт",
          class: "классы",
          state: "состояния",
          pie: "круговая",
          relationship: "связи",
          journey: "путь"
        }
      }
    }
  },
  // HTML в ответе показываем как HTML, а не текстом: модель им пользуется для
  // мелкой разметки. Безопасность остаётся на встроенном xss-фильтре пакета —
  // он режет script, обработчики on* и опасные протоколы.
  markdownItConfig(e) {
    e.set({ html: !0 });
  }
});
export {
  Ut as MdPreview
};
