const s=e=>e.replace(/_([a-z])/g,(n,r)=>r.toUpperCase());function c(e){return Array.isArray(e)?e.map(c):e!==null&&typeof e=="object"?Object.keys(e).reduce((n,r)=>{const o=s(r);return n[o]=c(e[r]),n},{}):e}export{c};
//# sourceMappingURL=convert-case-to-camel-case.utils-BvCCmEmI.js.map
