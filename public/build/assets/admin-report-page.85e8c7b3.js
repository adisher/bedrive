import{b as O,d as U,j as l,c as b,a as t,aA as W,T as f,r as d,an as q,aU as Q,cC as Z,bD as J,bF as z,bc as X,bu as N,v as Y,u as S,cD as ee,m as w,cE as A,h as te,t as L,g as ae,D as se,o as re,p as oe,B as M,ao as ie,e as V,aT as ne,E as le}from"./main.fcd554a6.js";import{T as ce,a as me,b as de}from"./TrendingUp.90fad761.js";import{a2 as $,a3 as j,a4 as ue,a5 as ge,a6 as fe,a7 as he}from"./file-entry-index-filters.398da9db.js";import{t as R,F as pe}from"./admin-routes.80e2a08a.js";import{A as xe}from"./ArrowBack.953d9159.js";import"./Edit.5be662f9.js";import"./use-resume-subscription.c9b7d602.js";const H="admin/reports";function be(e={}){return O([H,e],()=>ve(e),{keepPreviousData:!0})}function ve({types:e,dateRange:a}){const s={};return e&&(s.types=e.join(",")),a&&(s.startDate=a.start.toAbsoluteString(),s.endDate=a.end.toAbsoluteString(),s.timezone=a.start.timeZone),U.get(H,{params:s}).then(r=>r.data)}function G(e){const{title:a,description:s,children:r,className:o,contentIsFlex:i=!0,contentClassName:m,contentRef:n,minHeight:u="min-h-440"}=e;return l("div",{className:b("bg border rounded h-full flex flex-col flex-auto",u,o),children:[l("div",{className:"text-xs p-14 flex-shrink-0 flex justify-between items-center",children:[t("div",{className:"font-semibold text-sm",children:a}),s&&t("div",{className:"text-muted",children:s})]}),t("div",{ref:n,className:b("p-14 relative",i&&"flex-auto flex items-center justify-center",m),children:r})]})}function y(){return l("div",{className:"flex items-center gap-10 text-sm absolute mx-auto",children:[t(W,{isIndeterminate:!0,size:"sm"}),t(f,{message:"Chart loading"})]})}const Ne=d.exports.lazy(()=>q(()=>import("./lazy-chart.6dde381a.js"),["./lazy-chart.6dde381a.js","./main.fcd554a6.js","./main.e8a562e5.css"],import.meta.url));function D(e){const{title:a,description:s,className:r,contentRef:o,isLoading:i}=e;return t(G,{title:a,description:s,className:r,contentRef:o,children:l(d.exports.Suspense,{fallback:t(y,{}),children:[t(Ne,{...e}),i&&t(y,{})]})})}function C(e,{localeCode:a="en",shareFirstDatasetLabels:s=!0}){if(!e)return{datasets:[]};const r=[];return{...e,datasets:e.datasets.map((o,i)=>{const m=o.data.map((n,u)=>{let c;return i===0||!s?(c=we(n,e.granularity,a),r[u]=c):c=r[u],{...c,value:n.value}});return{...o,data:m}})}}function we(e,a,s){return e.label!=null?{label:e.label}:e.date?ye(e,a,s):{label:""}}function ye({date:e,endDate:a,value:s},r="day",o){const i=z(e).toDate(),m=a?z(a).toDate():null;switch(r){case"minute":return{label:g(o,{second:"2-digit"}).format(i),tooltipTitle:g(o,{day:"2-digit",hour:"numeric",minute:"numeric",second:"2-digit"}).format(i)};case"hour":return{label:g(o,{hour:"numeric",minute:"numeric"}).format(i),tooltipTitle:g(o,{month:"short",day:"2-digit",hour:"numeric",minute:"numeric"}).format(i)};case"day":return{label:g(o,{day:"2-digit",weekday:"short"}).format(i),tooltipTitle:g(o,{day:"2-digit",weekday:"short",month:"short"}).format(i)};case"week":return{label:g(o,{month:"short",day:"2-digit"}).format(i),tooltipTitle:g(o,{day:"2-digit",month:"long",year:"numeric"}).formatRange(i,m)};case"month":return{label:g(o,{month:"short",year:"numeric"}).format(i),tooltipTitle:g(o,{month:"long",year:"numeric"}).format(i)};case"year":return{label:g(o,{year:"numeric"}).format(i),tooltipTitle:g(o,{year:"numeric"}).format(i)}}}const g=Q((e,a)=>new Z(e,a),{equals:(e,a)=>J(e,a),callTimeout:void 0}),F=X().themes.all[0].colors["--be-primary"],h=[[`rgb(${F.replaceAll(" ",",")})`,`rgba(${F.replaceAll(" ",",")},0.2)`],["rgb(255,112,67)","rgb(255,112,67,0.2)"],["rgb(255,167,38)","rgb(255,167,38,0.2)"],["rgb(141,110,99)","rgb(141,110,99,0.2)"],["rgb(102,187,106)","rgba(102,187,106,0.2)"],["rgb(92,107,192)","rgb(92,107,192,0.2)"]],De={parsing:{xAxisKey:"label",yAxisKey:"value"},datasets:{line:{fill:"origin",tension:.1,pointBorderWidth:4,pointHitRadius:10}},plugins:{tooltip:{intersect:!1,mode:"index"}}};function Ce({data:e,className:a,...s}){const{localeCode:r}=N(),o=d.exports.useMemo(()=>{const i=C(e,{localeCode:r});return i.datasets=i.datasets.map((m,n)=>({...m,backgroundColor:h[n][1],borderColor:h[n][0],pointBackgroundColor:h[n][0]})),i},[e,r]);return t(D,{...s,className:b(a,"min-w-500"),data:o,type:"line",options:De})}const Te={parsing:{key:"value"},plugins:{tooltip:{intersect:!0}}};function ke({data:e,className:a,...s}){const{localeCode:r}=N(),o=d.exports.useMemo(()=>{var m;const i=C(e,{localeCode:r});return i.labels=(m=i.datasets[0])==null?void 0:m.data.map(n=>n.label),i.datasets=i.datasets.map((n,u)=>({...n,backgroundColor:h.map(c=>c[1]),borderColor:h.map(c=>c[0]),borderWidth:2})),i},[e,r]);return t(D,{type:"polarArea",data:o,options:Te,className:b(a,"min-w-500"),...s})}function Ie({data:e,direction:a="vertical",individualBarColors:s=!1,className:r,...o}){const{localeCode:i}=N(),m=d.exports.useMemo(()=>{const c=C(e,{localeCode:i});return c.datasets=c.datasets.map((x,v)=>({...x,backgroundColor:s?h.map(p=>p[1]):h[v][1],borderColor:s?h.map(p=>p[0]):h[v][0],borderWidth:2})),c},[e,i,s]),n=a==="horizontal",u=d.exports.useMemo(()=>({indexAxis:n?"y":"x",parsing:{xAxisKey:n?"value":"label",yAxisKey:n?"label":"value"}}),[n]);return t(D,{type:"bar",className:b(r,"min-w-500"),data:m,options:u,...o})}const P="https://www.gstatic.com/charts/loader.js";function ze({placeholderRef:e,data:a,country:s,onCountrySelected:r}){const{trans:o}=Y(),{analytics:i}=S(),m=i==null?void 0:i.gchart_api_key,{selectedTheme:n}=ee(),u=d.exports.useRef(),c=!!r&&!s,x=d.exports.useCallback(()=>{var T,k;if(typeof google>"u")return;const p=a.map(I=>[I.label,I.value]);p.unshift([o(s?w("City"):w("Country")),o(w("Clicks"))]);const E=`${R(n.colors["--be-paper"])}`,K={colorAxis:{colors:[`${R(n.colors["--be-primary"])}`]},backgroundColor:E,region:s?s.toUpperCase():void 0,resolution:s?"provinces":"countries",displayMode:s?"markers":"regions",enableRegionInteractivity:c};!u.current&&e.current&&((T=google==null?void 0:google.visualization)==null?void 0:T.GeoChart)&&(u.current=new google.visualization.GeoChart(e.current)),(k=u.current)==null||k.draw(google.visualization.arrayToDataTable(p),K)},[n,a,e,o,s,c]),v=d.exports.useCallback(()=>{A.alreadyLoading(P)||A.loadAsset(P,{type:"js"}).then(()=>{google.charts.load("current",{packages:["geochart"],mapsApiKey:m,callback:()=>{x(),u.current&&r&&google.visualization.events.addListener(u.current,"regionClick",p=>{r==null||r(p.region)})}})})},[m,x,r]);return d.exports.useEffect(()=>{v()},[v]),d.exports.useEffect(()=>{x()},[n,x,a]),{drawGoogleChart:x}}const Ae=te(t("path",{d:"M9 8a1 1 0 0 0-1-1H5.5a1 1 0 1 0 0 2H7v4a1 1 0 0 0 2 0zM4 0h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4zm4 5.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"}),"InfoDialogTrigger");function Re({title:e,body:a,dialogSize:s="sm",className:r}){return l(L,{type:"popover",triggerOnHover:!0,children:[t(ae,{className:b("text-muted opacity-70",r),size:"xs",children:t(Ae,{viewBox:"0 0 16 16"})}),l(se,{size:s,children:[e&&t(re,{padding:"px-18 pt-12",size:"md",hideDismissButton:!0,children:e}),t(oe,{children:a})]})]})}const Fe=d.exports.memo(({code:e})=>{const{localeCode:a}=N(),s=new Intl.DisplayNames([a],{type:"region"});let r;try{r=s.of(e.toUpperCase())}catch{}return t(d.exports.Fragment,{children:r})});function Pe({data:e,isLoading:a,onCountrySelected:s,country:r,...o}){const i=d.exports.useRef(null),m=!!s,n=e==null?void 0:e.datasets[0].data,u=d.exports.useMemo(()=>n||[],[n]);return ze({placeholderRef:i,data:u,country:r,onCountrySelected:s}),l(G,{...o,className:"min-w-500",title:l("div",{className:"flex items-center",children:[t(f,{message:"Top Locations"}),r?l("span",{className:"pl-4",children:["(",t(Fe,{code:r}),")"]}):null,m&&t(Be,{})]}),contentIsFlex:a,children:[a&&t(y,{}),l("div",{className:"flex gap-24",children:[t("div",{ref:i,className:"flex-auto w-[480px] min-h-[340px]"}),l("div",{className:"w-[170px]",children:[t("div",{className:"text-sm max-h-[340px] w-full flex-initial overflow-y-auto",children:u.map(c=>l("div",{className:b("flex items-center gap-4 mb-4",m&&"cursor-pointer hover:underline"),role:m?"button":void 0,onClick:()=>{s==null||s(c.code)},children:[t("div",{className:"max-w-110 whitespace-nowrap overflow-hidden overflow-ellipsis",children:c.label}),l("div",{children:["(",c.percentage,")%"]})]},c.label))}),r&&t(M,{variant:"outline",size:"xs",className:"mt-14",startIcon:t(xe,{}),onClick:()=>{s==null||s(void 0)},children:t(f,{message:"Back to countries"})})]})]})]})}function Be(){return t(Re,{title:t(f,{message:"Zooming in"}),body:t(f,{message:"Click on a country inside the map or country list to zoom in and see city data for that country."})})}const _="gap-12 md:gap-24 mb-12 md:mb-24",B=`flex flex-col md:flex-row md:items-center overflow-x-auto ${_}`;function Le({report:e,isLoading:a}){const s=e==null?void 0:e.pageViews.total;return l(d.exports.Fragment,{children:[l("div",{className:B,children:[t(Ce,{isLoading:a,className:"flex-auto",data:e==null?void 0:e.pageViews,title:t(f,{message:"Pageviews"}),description:s?t(f,{message:":count total views",values:{count:t($,{value:s})}}):null}),t(ke,{isLoading:a,data:e==null?void 0:e.devices,title:t(f,{message:"Top devices"})})]}),l("div",{className:B,children:[t(Ie,{isLoading:a,data:e==null?void 0:e.browsers,className:"flex-auto md:w-1/3",direction:"horizontal",individualBarColors:!0,hideLegend:!0,title:t(f,{message:"Top browsers"})}),t(Pe,{isLoading:a,className:"flex-auto",data:e==null?void 0:e.locations,title:t(f,{message:"Top locations"})})]})]})}function Me({report:e,dateRange:a}){return j,a.start,a.end,t("div",{className:`flex items-center flex-shrink-0 overflow-x-auto h-[97px] ${_}`,children:e==null?void 0:e.map(s=>t(Ve,{datum:s},s.name))})}function Ve({datum:e,label:a}){const s=ie(e.icon);return l("div",{className:"flex items-center flex-auto rounded border p-20 gap-18 h-full whitespace-nowrap",children:[t("div",{className:"bg-primary-light/20 rounded-lg p-10 flex-shrink-0",children:t(s,{size:"lg",className:"text-primary"})}),l("div",{className:"flex-auto",children:[l("div",{className:"flex items-center gap-20 justify-between",children:[t("div",{className:"text-main text-lg font-bold",children:e.type==="fileSize"?t(pe,{bytes:e.currentValue}):t($,{value:e.currentValue})}),a&&t("div",{className:"text-xs text-muted ml-auto",children:a})]}),l("div",{className:"flex items-center gap-20 justify-between",children:[t("h2",{className:"text-muted text-sm",children:e.name}),e.percentageChange!=null&&t("div",{className:"flex items-center gap-10",children:t($e,{percentage:e.percentageChange})})]})]})]},e.name)}function $e({percentage:e}){let a;return e>0?a=t(ce,{size:"md",className:"text-positive"}):e===0?a=t(me,{className:"text-muted"}):a=t(de,{className:"text-danger"}),l(d.exports.Fragment,{children:[a,l("div",{className:"text-sm font-semibold text-muted",children:[e,"%"]})]})}const je={month:"short",day:"2-digit"};function He({value:e,onChange:a,compactOnMobile:s=!0}){const r=V();return l(L,{type:"popover",onClose:o=>{o&&a(o)},children:[t(M,{variant:"outline",color:"chip",endIcon:t(ue,{}),children:t(j,{start:e.start,end:e.end,options:r&&s?je:ne.short})}),t(Ge,{value:e})]})}function Ge({value:e}){const a=V(),s=ge({defaultValue:e,closeDialogOnSelection:!1});return t(fe,{state:s,showInlineDatePickerField:!a})}function Ze(){const[e,a]=d.exports.useState(()=>he[2].getRangeValue()),{isLoading:s,data:r}=be({dateRange:e}),o=t(f,{message:"Visitors report"});return l("div",{className:"min-h-full gap-12 md:gap-24 p-12 md:p-24 overflow-x-hidden",children:[l("div",{className:"md:flex items-center justify-between gap-24 mb-24",children:[t(le,{children:o}),t("h1",{className:"mb-24 md:mb-0 text-3xl font-light",children:o}),t(He,{value:e,onChange:a})]}),t(Me,{report:r==null?void 0:r.headerReport,dateRange:e}),t(Le,{report:r==null?void 0:r.visitorsReport,isLoading:s})]})}export{Ze as default};
//# sourceMappingURL=admin-report-page.85e8c7b3.js.map