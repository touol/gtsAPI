const l = (c) => {
  let t = String(c);
  const s = (t.match(/^```/gm) || []).length;
  s % 2 === 1 && (t += "\n```");
  const n = t.split(`
`), e = n[n.length - 1];
  return s % 2 === 0 && /^\s*\|/.test(e) && !/\|\s*$/.test(e) && (n.pop(), t = n.join(`
`)), (t.match(/\*\*/g) || []).length % 2 === 1 && (t += "**"), ((t.match(/`/g) || []).length - (t.match(/```/g) || []).length * 3) % 2 === 1 && (t += "`"), t;
};
export {
  l as healPartial
};
