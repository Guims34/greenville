if(!self.define){let e,s={};const i=(i,r)=>(i=new URL(i+".js",r).href,s[i]||new Promise((s=>{if("document"in self){const e=document.createElement("script");e.src=i,e.onload=s,document.head.appendChild(e)}else e=i,importScripts(i),s()})).then((()=>{let e=s[i];if(!e)throw new Error(`Module ${i} didn’t register its module`);return e})));self.define=(r,n)=>{const t=e||("document"in self?document.currentScript.src:"")||location.href;if(s[t])return;let o={};const l=e=>i(e,t),u={module:{uri:t},exports:o,require:l};s[t]=Promise.all(r.map((e=>u[e]||l(e)))).then((e=>(n(...e),o)))}}define(["./workbox-5ffe50d4"],(function(e){"use strict";self.skipWaiting(),e.clientsClaim(),e.precacheAndRoute([{url:"assets/auth-dmfF-aKe.js",revision:null},{url:"assets/form-BA5jX7O0.js",revision:null},{url:"assets/index-CjiQeeTX.css",revision:null},{url:"assets/index-DIPouyWF.js",revision:null},{url:"assets/ui-Dx9Yb_6s.js",revision:null},{url:"assets/vendor-hGpyXIeq.js",revision:null},{url:"index.html",revision:"00a913f99b9c47b4a88e546f7ab97025"},{url:"registerSW.js",revision:"1872c500de691dce40960bb85481de07"},{url:"robots.txt",revision:"56a77a9c10482619cde0790b6c65a802"},{url:"manifest.webmanifest",revision:"be12952cd3ce4ba1ca2f601c6dfeace2"}],{}),e.cleanupOutdatedCaches(),e.registerRoute(new e.NavigationRoute(e.createHandlerBoundToURL("index.html")))}));
//# sourceMappingURL=sw.js.map
