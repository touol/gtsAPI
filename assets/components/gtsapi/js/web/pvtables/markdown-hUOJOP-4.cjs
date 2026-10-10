"use strict";Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"});const i=l=>{let t=String(l);const n=(t.match(/^```/gm)||[]).length;n%2===1&&(t+="\n```");const e=t.split(`
`),s=e[e.length-1];return n%2===0&&/^\s*\|/.test(s)&&!/\|\s*$/.test(s)&&(e.pop(),t=e.join(`
`)),(t.match(/\*\*/g)||[]).length%2===1&&(t+="**"),((t.match(/`/g)||[]).length-(t.match(/```/g)||[]).length*3)%2===1&&(t+="`"),t};exports.healPartial=i;
