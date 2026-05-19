import { useState } from "react";

// ── Medicine SVG Images ─────────────────────────────────────────────────────
const MedImg = ({ type, size = 70 }) => {
  const s = size;
  const imgs = {
    tablet_white: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <radialGradient id="tw" cx="40%" cy="35%">
            <stop offset="0%" stopColor="#e8f8ff"/>
            <stop offset="100%" stopColor="#b0d8ef"/>
          </radialGradient>
        </defs>
        <ellipse cx="35" cy="35" rx="28" ry="20" fill="url(#tw)" stroke="#7ec8e3" strokeWidth="1.5"/>
        <ellipse cx="35" cy="35" rx="28" ry="20" fill="none" stroke="rgba(255,255,255,0.6)" strokeWidth="0.8"/>
        <line x1="7" y1="35" x2="63" y2="35" stroke="#7ec8e3" strokeWidth="1.2" strokeDasharray="2,1"/>
        <ellipse cx="35" cy="35" rx="28" ry="20" fill="none" stroke="rgba(0,200,255,0.2)" strokeWidth="3"/>
      </svg>
    ),
    tablet_orange: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <radialGradient id="to" cx="40%" cy="35%">
            <stop offset="0%" stopColor="#ffe0b0"/>
            <stop offset="100%" stopColor="#f97316"/>
          </radialGradient>
        </defs>
        <ellipse cx="35" cy="35" rx="26" ry="18" fill="url(#to)" stroke="#ea580c" strokeWidth="1.5"/>
        <ellipse cx="28" cy="30" rx="6" ry="4" fill="rgba(255,255,255,0.25)"/>
        <line x1="9" y1="35" x2="61" y2="35" stroke="rgba(234,88,12,0.5)" strokeWidth="1"/>
      </svg>
    ),
    capsule_red: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <linearGradient id="cr1" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#ef4444"/>
            <stop offset="50%" stopColor="#ef4444"/>
            <stop offset="50%" stopColor="#fef3c7"/>
            <stop offset="100%" stopColor="#fde68a"/>
          </linearGradient>
        </defs>
        <g transform="rotate(-30,35,35)">
          <rect x="12" y="27" width="46" height="16" rx="8" fill="url(#cr1)" stroke="rgba(0,0,0,0.1)" strokeWidth="1"/>
          <rect x="12" y="27" width="23" height="16" rx="8" fill="#ef4444"/>
          <ellipse cx="20" cy="32" rx="4" ry="3" fill="rgba(255,255,255,0.3)"/>
          <line x1="35" y1="27" x2="35" y2="43" stroke="rgba(0,0,0,0.12)" strokeWidth="1"/>
        </g>
      </svg>
    ),
    tablet_yellow: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <radialGradient id="ty" cx="40%" cy="35%">
            <stop offset="0%" stopColor="#fef9c3"/>
            <stop offset="100%" stopColor="#eab308"/>
          </radialGradient>
        </defs>
        <rect x="12" y="22" width="46" height="26" rx="13" fill="url(#ty)" stroke="#ca8a04" strokeWidth="1.5"/>
        <rect x="15" y="25" width="20" height="10" rx="5" fill="rgba(255,255,255,0.2)"/>
        <line x1="35" y1="22" x2="35" y2="48" stroke="rgba(202,138,4,0.4)" strokeWidth="1"/>
      </svg>
    ),
    capsule_purple: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <linearGradient id="cp" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#7c3aed"/>
            <stop offset="50%" stopColor="#7c3aed"/>
            <stop offset="50%" stopColor="#ede9fe"/>
            <stop offset="100%" stopColor="#c4b5fd"/>
          </linearGradient>
        </defs>
        <g transform="rotate(20,35,35)">
          <rect x="11" y="27" width="48" height="16" rx="8" fill="url(#cp)" stroke="rgba(0,0,0,0.1)" strokeWidth="1"/>
          <ellipse cx="18" cy="32" rx="4" ry="3" fill="rgba(255,255,255,0.35)"/>
          <line x1="35" y1="27" x2="35" y2="43" stroke="rgba(0,0,0,0.1)" strokeWidth="1"/>
        </g>
      </svg>
    ),
    capsule_yellow: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <linearGradient id="cy" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#f59e0b"/>
            <stop offset="50%" stopColor="#f59e0b"/>
            <stop offset="50%" stopColor="#fef3c7"/>
            <stop offset="100%" stopColor="#fde68a"/>
          </linearGradient>
        </defs>
        <g transform="rotate(-15,35,35)">
          <rect x="11" y="27" width="48" height="16" rx="8" fill="url(#cy)" stroke="rgba(0,0,0,0.08)" strokeWidth="1"/>
          <ellipse cx="18" cy="32" rx="4" ry="3" fill="rgba(255,255,255,0.35)"/>
          <line x1="35" y1="27" x2="35" y2="43" stroke="rgba(0,0,0,0.1)" strokeWidth="1"/>
        </g>
      </svg>
    ),
    capsule_green: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <linearGradient id="cg" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#16a34a"/>
            <stop offset="50%" stopColor="#16a34a"/>
            <stop offset="50%" stopColor="#dcfce7"/>
            <stop offset="100%" stopColor="#86efac"/>
          </linearGradient>
        </defs>
        <g transform="rotate(10,35,35)">
          <rect x="11" y="27" width="48" height="16" rx="8" fill="url(#cg)" stroke="rgba(0,0,0,0.08)" strokeWidth="1"/>
          <ellipse cx="18" cy="32" rx="4" ry="3" fill="rgba(255,255,255,0.35)"/>
          <line x1="35" y1="27" x2="35" y2="43" stroke="rgba(0,0,0,0.1)" strokeWidth="1"/>
        </g>
      </svg>
    ),
    tablet_blue: (
      <svg width={s} height={s} viewBox="0 0 70 70">
        <defs>
          <radialGradient id="tb" cx="40%" cy="35%">
            <stop offset="0%" stopColor="#bfdbfe"/>
            <stop offset="100%" stopColor="#2563eb"/>
          </radialGradient>
        </defs>
        <ellipse cx="35" cy="35" rx="27" ry="19" fill="url(#tb)" stroke="#1d4ed8" strokeWidth="1.5"/>
        <ellipse cx="27" cy="30" rx="6" ry="4" fill="rgba(255,255,255,0.28)"/>
        <line x1="8" y1="35" x2="62" y2="35" stroke="rgba(29,78,216,0.4)" strokeWidth="1"/>
      </svg>
    ),
  };
  return imgs[type] || imgs.tablet_white;
};

// ── Data ────────────────────────────────────────────────────────────────────
const MEDICINES = [
  { id:1, name:"Paracetamol",    cat:"Analgesic",        stock:120, unit:"tablets",  expiry:"Dec 2026", status:"In Stock",    desc:"Fever & mild pain relief",      dose:"500mg", img:"tablet_white", req:34, uses:"Headache, Fever, Body pain" },
  { id:2, name:"Ibuprofen",      cat:"Anti-inflammatory",stock:15,  unit:"tablets",  expiry:"Oct 2026", status:"Low Stock",   desc:"Pain, fever & inflammation",    dose:"200mg", img:"tablet_orange",req:18, uses:"Joint pain, Fever, Toothache" },
  { id:3, name:"Amoxicillin",    cat:"Antibiotic",       stock:0,   unit:"capsules", expiry:"Aug 2026", status:"Out of Stock",desc:"Bacterial infection treatment",  dose:"250mg", img:"capsule_red",  req:9,  uses:"Infections, Pneumonia, UTI" },
  { id:4, name:"Cetirizine",     cat:"Antihistamine",    stock:60,  unit:"tablets",  expiry:"Mar 2027", status:"In Stock",    desc:"Allergy & hay fever relief",    dose:"10mg",  img:"tablet_yellow",req:22, uses:"Allergies, Hives, Rhinitis" },
  { id:5, name:"Omeprazole",     cat:"Antacid",          stock:45,  unit:"capsules", expiry:"Jan 2027", status:"In Stock",    desc:"Acid reflux & stomach ulcers",  dose:"20mg",  img:"capsule_purple",req:15, uses:"Acid reflux, Gastritis, GERD" },
  { id:6, name:"Mefenamic Acid", cat:"Analgesic",        stock:8,   unit:"capsules", expiry:"Nov 2026", status:"Low Stock",   desc:"Moderate to severe pain",       dose:"500mg", img:"capsule_yellow",req:27, uses:"Dysmenorrhea, Headache, Pain" },
  { id:7, name:"Loperamide",     cat:"Antidiarrheal",    stock:55,  unit:"capsules", expiry:"Feb 2027", status:"In Stock",    desc:"Diarrhea & loose bowel",        dose:"2mg",   img:"capsule_green", req:12, uses:"Diarrhea, IBS, Loose bowel" },
  { id:8, name:"Loratadine",     cat:"Antihistamine",    stock:30,  unit:"tablets",  expiry:"May 2027", status:"In Stock",    desc:"Allergy symptoms relief",       dose:"10mg",  img:"tablet_blue",  req:19, uses:"Allergies, Runny nose, Hives" },
];
const INIT_REQS = [
  {id:1,name:"Maria Santos",        sid:"2021-00123",type:"Student",med:"Paracetamol",   qty:2,status:"Pending",  time:"10:32 AM",av:"MS",reason:"Fever and headache"},
  {id:2,name:"Prof. Juan Dela Cruz",sid:"FAC-0045",  type:"Faculty",med:"Ibuprofen",     qty:1,status:"Approved", time:"09:15 AM",av:"JD",reason:"Back pain"},
  {id:3,name:"Carlos Reyes",        sid:"2022-00456",type:"Student",med:"Cetirizine",    qty:3,status:"Pending",  time:"11:04 AM",av:"CR",reason:"Allergic rhinitis"},
  {id:4,name:"Ana Lim",             sid:"2023-00789",type:"Student",med:"Mefenamic Acid",qty:2,status:"Dispensed",time:"08:50 AM",av:"AL",reason:"Dysmenorrhea"},
  {id:5,name:"Prof. Rosa Mendoza",  sid:"FAC-0032",  type:"Faculty",med:"Omeprazole",    qty:1,status:"Approved", time:"07:30 AM",av:"RM",reason:"Acid reflux"},
];
const HEALTH_DATA = [
  {m:"Jan",v:45},{m:"Feb",v:62},{m:"Mar",v:38},{m:"Apr",v:71},{m:"May",v:55},{m:"Jun",v:48},
];
const PREDS = [
  {label:"Common Cold",       pct:78,trend:"↑",c:"#f87171"},
  {label:"Allergic Rhinitis", pct:61,trend:"↑",c:"#fb923c"},
  {label:"Headache/Migraine", pct:45,trend:"→",c:"#facc15"},
  {label:"Gastroenteritis",   pct:29,trend:"↓",c:"#4ade80"},
];
const CATS = ["All","Analgesic","Antibiotic","Antihistamine","Anti-inflammatory","Antacid","Antidiarrheal"];

const sc = s => s==="In Stock"?"#00f5ff":s==="Low Stock"?"#facc15":"#f87171";
const rc = s => ({Approved:"#4ade80",Dispensed:"#a78bfa",Rejected:"#f87171",Pending:"#facc15"}[s]||"#facc15");

// ── Global Styles ───────────────────────────────────────────────────────────
const GS = `
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Exo+2:wght@300;400;500;600;700&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
::-webkit-scrollbar{width:3px}
::-webkit-scrollbar-thumb{background:rgba(0,245,255,.25);border-radius:2px}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes floatA{0%,100%{transform:translateY(0)}50%{transform:translateY(-18px)}}
@keyframes floatB{0%,100%{transform:translateY(-10px)}50%{transform:translateY(10px)}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes toastIn{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
.fade{animation:fadeUp .3s ease both}
.fa{animation:floatA 9s ease-in-out infinite}
.fb{animation:floatB 11s ease-in-out infinite}
.ta{animation:toastIn .28s ease}
.nb{transition:all .2s;cursor:pointer;border-radius:10px}
.nb:hover{background:rgba(0,245,255,.07)!important}
.nb.on{background:rgba(0,245,255,.11)!important}
.mc{transition:all .22s ease;cursor:pointer}
.mc:hover{transform:translateY(-5px);box-shadow:0 22px 55px rgba(0,245,255,.18)!important}
.btn{transition:all .18s;cursor:pointer;border:none;font-family:'Syne',sans-serif}
.btn:hover{filter:brightness(1.08);transform:translateY(-1px)}
.btn:active{transform:translateY(0)}
.gb{transition:all .18s;cursor:pointer}
.gb:hover{background:rgba(0,245,255,.09)!important}
.rr{transition:background .15s;cursor:pointer;border-radius:9px}
.rr:hover{background:rgba(0,245,255,.05)!important}
.rc{transition:all .22s;cursor:pointer;border-radius:16px}
.rc:hover{transform:translateY(-4px)}
input,select,textarea{outline:none;font-family:'Exo 2',sans-serif}
input:focus,select:focus{border-color:rgba(0,245,255,.4)!important;box-shadow:0 0 0 3px rgba(0,245,255,.07)!important}
/* Layout */
.wrap{display:flex;height:100vh;overflow:hidden}
.sb{display:flex;flex-direction:column;flex-shrink:0;transition:width .3s ease;overflow:hidden}
.content{flex:1;overflow-y:auto;padding:18px 20px}
/* Grids */
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.gm{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}
.hg{display:grid;grid-template-columns:1.35fr 1fr;gap:16px}
/* Tablet */
@media(max-width:1024px){
  .g4{grid-template-columns:repeat(2,1fr)}
  .g3{grid-template-columns:repeat(2,1fr)}
  .hg{grid-template-columns:1fr}
  .sb{width:64px!important}
  .slbl{display:none!important}
}
/* Mobile */
@media(max-width:640px){
  .sb{display:none!important}
  .bnav{display:flex!important}
  .g4{grid-template-columns:repeat(2,1fr);gap:9px}
  .g3{grid-template-columns:1fr 1fr;gap:9px}
  .g2{grid-template-columns:1fr}
  .gm{grid-template-columns:1fr 1fr;gap:10px}
  .hg{grid-template-columns:1fr}
  .content{padding:12px 13px;padding-bottom:74px}
  .topbar{padding:10px 13px!important}
  .modal-box{width:calc(100vw - 20px)!important;max-height:90vh;overflow-y:auto}
  .hide-m{display:none!important}
}
@media(max-width:380px){
  .gm{grid-template-columns:1fr}
}
.bnav{display:none;position:fixed;bottom:0;left:0;right:0;z-index:50;background:rgba(2,14,30,.95);backdrop-filter:blur(20px);border-top:1px solid rgba(0,245,255,.1);padding:8px 0 10px}
`;

// ── Sub-components ──────────────────────────────────────────────────────────
const Toast = ({t}) => (
  <div className="ta" style={{position:"fixed",bottom:24,right:20,background:t.type==="error"?"rgba(248,113,113,.12)":"rgba(0,245,255,.09)",border:`1px solid ${t.type==="error"?"rgba(248,113,113,.35)":"rgba(0,245,255,.28)"}`,borderRadius:12,padding:"12px 18px",color:t.type==="error"?"#f87171":"#00f5ff",fontSize:12,fontWeight:600,zIndex:300,backdropFilter:"blur(18px)",display:"flex",alignItems:"center",gap:8,maxWidth:280,boxShadow:"0 8px 28px rgba(0,0,0,.35)"}}>
    <span style={{fontSize:14}}>{t.type==="error"?"✕":"✓"}</span>{t.msg}
  </div>
);

const MedCard = ({m, onDetail, onRequest, isAdmin}) => (
  <div className="mc" onClick={()=> isAdmin ? onDetail(m) : onRequest(m)}
    style={{background:"rgba(255,255,255,.03)",backdropFilter:"blur(24px)",WebkitBackdropFilter:"blur(24px)",border:"1px solid rgba(0,245,255,.1)",borderRadius:20,padding:16,position:"relative",overflow:"hidden",boxShadow:"0 6px 24px rgba(0,0,0,.28)"}}>
    {/* glow */}
    <div style={{position:"absolute",top:-20,right:-20,width:80,height:80,borderRadius:"50%",background:`radial-gradient(circle,${sc(m.status)}12,transparent 70%)`,pointerEvents:"none"}}/>
    {/* Image circle */}
    <div style={{display:"flex",justifyContent:"space-between",alignItems:"flex-start",marginBottom:10}}>
      <div style={{width:60,height:60,borderRadius:"50%",background:"rgba(0,245,255,.06)",border:`2px solid ${sc(m.status)}30`,display:"flex",alignItems:"center",justifyContent:"center",flexShrink:0,overflow:"hidden",boxShadow:`0 0 14px ${sc(m.status)}18`}}>
        <MedImg type={m.img} size={54}/>
      </div>
      <div style={{background:`${sc(m.status)}14`,color:sc(m.status),border:`1px solid ${sc(m.status)}28`,borderRadius:20,fontSize:9,padding:"3px 8px",fontWeight:700,letterSpacing:.3}}>{m.status}</div>
    </div>
    <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13,marginBottom:1}}>{m.name}</div>
    <div style={{color:"rgba(0,245,255,.55)",fontSize:10,marginBottom:4}}>{m.cat} · {m.dose}</div>
    <div style={{color:"rgba(255,255,255,.3)",fontSize:10,lineHeight:1.5,marginBottom:11}}>{m.desc}</div>
    {/* Stock bar */}
    <div style={{marginBottom:10}}>
      <div style={{display:"flex",justifyContent:"space-between",marginBottom:4}}>
        <span style={{color:"rgba(255,255,255,.3)",fontSize:9}}>Stock</span>
        <span style={{color:sc(m.status),fontWeight:700,fontSize:10}}>{m.stock} {m.unit}</span>
      </div>
      <div style={{background:"rgba(255,255,255,.06)",borderRadius:4,height:5}}>
        <div style={{height:"100%",borderRadius:4,width:`${Math.min(100,(m.stock/120)*100)}%`,background:`linear-gradient(90deg,${sc(m.status)},${sc(m.status)}55)`,transition:"width 1s"}}/>
      </div>
    </div>
    <div style={{display:"flex",justifyContent:"space-between",alignItems:"center"}}>
      <span style={{color:"rgba(255,255,255,.2)",fontSize:9}}>Exp: {m.expiry}</span>
      <button className="gb" onClick={e=>{e.stopPropagation(); isAdmin?onDetail(m):onRequest(m);}}
        style={{background:"rgba(0,245,255,.07)",border:"1px solid rgba(0,245,255,.18)",borderRadius:7,padding:"5px 10px",color:"#00f5ff",fontSize:9,fontWeight:700,cursor:"pointer"}}>
        {isAdmin?"Details →":"Request →"}
      </button>
    </div>
  </div>
);

// ── Main App ────────────────────────────────────────────────────────────────
export default function App() {
  const [role,   setRole]   = useState(null);
  const [step,   setStep]   = useState("role");
  const [selR,   setSelR]   = useState(null);
  const [uType,  setUType]  = useState("Student");
  const [creds,  setCreds]  = useState({id:"",pw:""});
  const [credE,  setCredE]  = useState("");

  const [reqs,   setReqs]   = useState(INIT_REQS);
  const [myReqs, setMyReqs] = useState([
    {id:101,med:"Paracetamol",qty:2,status:"Dispensed",date:"May 10",reason:"Headache"},
    {id:102,med:"Cetirizine", qty:1,status:"Approved",  date:"May 14",reason:"Allergy"},
  ]);
  const [visits, setVisits] = useState([
    {date:"May 10",complaint:"Headache",         med:"Paracetamol",status:"Resolved"},
    {date:"Apr 22",complaint:"Allergic Rhinitis",med:"Cetirizine", status:"Resolved"},
  ]);

  const [toast,   setToast]   = useState(null);
  const [reqMod,  setReqMod]  = useState(null);
  const [medMod,  setMedMod]  = useState(null);
  const [addMod,  setAddMod]  = useState(false);
  const [stuMod,  setStuMod]  = useState(null);
  const [hForm,   setHForm]   = useState({complaint:"",temp:"",bp:""});
  const [rForm,   setRForm]   = useState({qty:1,reason:""});
  const [cat,     setCat]     = useState("All");
  const [search,  setSearch]  = useState("");
  const [aPage,   setAPage]   = useState("dashboard");
  const [sPage,   setSPage]   = useState("home");

  const notify = (msg,type="success") => { setToast({msg,type}); setTimeout(()=>setToast(null),2800); };

  const login = () => {
    if(!creds.id||!creds.pw){setCredE("Fill in all fields");return;}
    setCredE(""); setRole(selR==="admin"?"admin":"student");
  };
  const logout = () => {
    setRole(null);setStep("role");setSelR(null);setCreds({id:"",pw:""});
    setAPage("dashboard");setSPage("home");
  };
  const approve  = id=>{setReqs(p=>p.map(r=>r.id===id?{...r,status:"Approved"}:r));  notify("Approved!"); setReqMod(null);};
  const reject   = id=>{setReqs(p=>p.map(r=>r.id===id?{...r,status:"Rejected"}:r));  notify("Rejected","error"); setReqMod(null);};
  const dispense = id=>{setReqs(p=>p.map(r=>r.id===id?{...r,status:"Dispensed"}:r)); notify("Medicine dispensed!"); setReqMod(null);};
  const submitReq=()=>{
    if(!rForm.reason){notify("Please state your reason","error");return;}
    const nr={id:Date.now(),med:stuMod.name,qty:rForm.qty,status:"Pending",date:"May 16",reason:rForm.reason};
    setMyReqs(p=>[nr,...p]);
    setReqs(p=>[{id:Date.now()+1,name:uType==="Faculty"?"Prof. Demo User":"Demo Student",sid:creds.id||"2024-00001",type:uType,med:stuMod.name,qty:rForm.qty,status:"Pending",time:"Now",av:"DU",reason:rForm.reason},...p]);
    notify("Request submitted!"); setStuMod(null); setRForm({qty:1,reason:""});
  };
  const logVisit=()=>{
    if(!hForm.complaint){notify("Enter your complaint","error");return;}
    setVisits(p=>[{date:"May 16",complaint:hForm.complaint,med:"Pending assessment",status:"Active"},...p]);
    notify("Health record logged!"); setHForm({complaint:"",temp:"",bp:""});
  };

  const isAdmin = role==="admin";
  const page    = isAdmin?aPage:sPage;
  const setPage = isAdmin?setAPage:setSPage;
  const filtM   = MEDICINES.filter(m=>(cat==="All"||m.cat===cat)&&m.name.toLowerCase().includes(search.toLowerCase()));
  const pendN   = reqs.filter(r=>r.status==="Pending").length;
  const adminNav= [{id:"dashboard",lbl:"Dashboard",ic:"⬡"},{id:"inventory",lbl:"Inventory",ic:"◈"},{id:"requests",lbl:"Requests",ic:"◎",badge:pendN},{id:"health",lbl:"Health",ic:"◇"},{id:"reports",lbl:"Reports",ic:"▦"},{id:"settings",lbl:"Settings",ic:"⊙"}];
  const stuNav  = [{id:"home",lbl:"Home",ic:"⬡"},{id:"medicines",lbl:"Medicines",ic:"💊"},{id:"myrequests",lbl:"Requests",ic:"◎",badge:myReqs.filter(r=>r.status==="Pending").length||0},{id:"myhealth",lbl:"My Health",ic:"◇"}];
  const nav     = isAdmin?adminNav:stuNav;

  const BG = (
    <div style={{position:"fixed",inset:0,pointerEvents:"none",zIndex:0,overflow:"hidden"}}>
      <div className="fa" style={{position:"absolute",width:500,height:500,borderRadius:"50%",background:"radial-gradient(circle,rgba(0,245,255,.045) 0%,transparent 70%)",top:-120,left:-120}}/>
      <div className="fb" style={{position:"absolute",width:380,height:380,borderRadius:"50%",background:"radial-gradient(circle,rgba(0,100,255,.055) 0%,transparent 70%)",bottom:-70,right:-70}}/>
      <div style={{position:"absolute",inset:0,backgroundImage:"radial-gradient(rgba(0,245,255,.022) 1px,transparent 1px)",backgroundSize:"40px 40px"}}/>
    </div>
  );

  // ── LOGIN ────────────────────────────────────────────────────────────────
  if(!role) return (
    <div style={{minHeight:"100vh",background:"#020b18",display:"flex",alignItems:"center",justifyContent:"center",fontFamily:"'Syne','Exo 2',sans-serif",padding:16,position:"relative"}}>
      <style>{GS}</style>{BG}
      <div className="fade" style={{width:"100%",maxWidth:440,zIndex:1}}>
        <div style={{textAlign:"center",marginBottom:28}}>
          <div style={{width:62,height:62,borderRadius:18,background:"linear-gradient(135deg,#00f5ff,#0055ff)",display:"inline-flex",alignItems:"center",justifyContent:"center",fontSize:28,boxShadow:"0 0 36px rgba(0,245,255,.32)",marginBottom:12}}>⚕</div>
          <div style={{color:"#00f5ff",fontFamily:"Syne",fontWeight:800,fontSize:18,letterSpacing:2}}>SCHOOL CLINIC</div>
          <div style={{color:"rgba(255,255,255,.25)",fontSize:9,letterSpacing:3,marginTop:2}}>INVENTORY MANAGEMENT SYSTEM</div>
        </div>
        <div style={{background:"rgba(0,245,255,.028)",border:"1px solid rgba(0,245,255,.12)",borderRadius:22,padding:"24px 20px"}}>
          {step==="role" ? (
            <>
              <div style={{color:"rgba(255,255,255,.35)",fontSize:10,textAlign:"center",marginBottom:18,letterSpacing:1}}>SELECT YOUR ROLE TO CONTINUE</div>
              <div className="g2" style={{marginBottom:18}}>
                {[{id:"admin",lbl:"Admin / Nurse",ic:"🏥",desc:"Manage inventory & requests",c:"#00f5ff"},{id:"student",lbl:"Student / Faculty",ic:"🎓",desc:"Browse & request medicines",c:"#a78bfa"}].map(r=>(
                  <div key={r.id} className="rc" onClick={()=>setSelR(r.id)}
                    style={{background:selR===r.id?"rgba(0,245,255,.06)":"rgba(255,255,255,.018)",border:`1.5px solid ${selR===r.id?r.c:"rgba(255,255,255,.07)"}`,padding:"18px 12px",textAlign:"center",boxShadow:selR===r.id?`0 0 22px ${r.c}20`:"none"}}>
                    <div style={{fontSize:28,marginBottom:7}}>{r.ic}</div>
                    <div style={{color:"white",fontWeight:700,fontSize:12,marginBottom:4}}>{r.lbl}</div>
                    <div style={{color:"rgba(255,255,255,.3)",fontSize:10,lineHeight:1.5}}>{r.desc}</div>
                    {selR===r.id&&<div style={{marginTop:8,color:r.c,fontSize:8,fontWeight:800,letterSpacing:1}}>✓ SELECTED</div>}
                  </div>
                ))}
              </div>
              <button className="btn" disabled={!selR} onClick={()=>selR&&setStep("creds")}
                style={{width:"100%",background:selR?"linear-gradient(135deg,#00f5ff,#0055ff)":"rgba(255,255,255,.04)",color:selR?"#020b18":"rgba(255,255,255,.18)",borderRadius:13,padding:12,fontWeight:800,fontSize:13}}>
                Continue →
              </button>
            </>
          ):(
            <>
              <button onClick={()=>setStep("role")} style={{background:"transparent",border:"none",color:"rgba(0,245,255,.55)",cursor:"pointer",fontSize:11,marginBottom:16,display:"flex",alignItems:"center",gap:4}}>← Back</button>
              <div style={{textAlign:"center",marginBottom:20}}>
                <div style={{fontSize:24,marginBottom:5}}>{selR==="admin"?"🏥":"🎓"}</div>
                <div style={{color:"white",fontWeight:700,fontSize:14}}>{selR==="admin"?"Admin / School Nurse":"Student / Faculty Login"}</div>
              </div>
              {selR==="student"&&(
                <div style={{display:"flex",gap:5,marginBottom:16,background:"rgba(255,255,255,.025)",borderRadius:10,padding:3}}>
                  {["Student","Faculty"].map(t=>(
                    <button key={t} onClick={()=>setUType(t)} style={{flex:1,background:uType===t?"rgba(0,245,255,.11)":"transparent",border:uType===t?"1px solid rgba(0,245,255,.22)":"1px solid transparent",borderRadius:8,padding:8,color:uType===t?"#00f5ff":"rgba(255,255,255,.35)",fontSize:11,fontWeight:600,cursor:"pointer",transition:"all .2s"}}>{t}</button>
                  ))}
                </div>
              )}
              {[{lbl:selR==="admin"?"Admin ID":"School ID",k:"id",ph:selR==="admin"?"e.g. admin":"e.g. 2024-00123"},{lbl:"Password",k:"pw",ph:"Enter password",type:"password"}].map(f=>(
                <div key={f.k} style={{marginBottom:12}}>
                  <div style={{color:"rgba(0,245,255,.5)",fontSize:9,fontWeight:700,letterSpacing:1,marginBottom:5}}>{f.lbl.toUpperCase()}</div>
                  <input type={f.type||"text"} value={creds[f.k]} onChange={e=>setCreds(p=>({...p,[f.k]:e.target.value}))} placeholder={f.ph} onKeyDown={e=>e.key==="Enter"&&login()}
                    style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.13)",borderRadius:10,padding:"10px 13px",color:"white",fontSize:12}}/>
                </div>
              ))}
              {credE&&<div style={{color:"#f87171",fontSize:10,marginBottom:8}}>{credE}</div>}
              <div style={{color:"rgba(255,255,255,.18)",fontSize:9,marginBottom:12}}>💡 Demo: any credentials work</div>
              <button className="btn" onClick={login} style={{width:"100%",background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:13,padding:12,fontWeight:800,fontSize:13,boxShadow:"0 4px 20px rgba(0,245,255,.26)"}}>Login</button>
            </>
          )}
        </div>
      </div>
      {toast&&<Toast t={toast}/>}
    </div>
  );

  // ── MAIN APP ─────────────────────────────────────────────────────────────
  return (
    <div className="wrap" style={{background:"#020b18",fontFamily:"'Syne','Exo 2',sans-serif",position:"relative"}}>
      <style>{GS}</style>{BG}

      {/* ── SIDEBAR ──────────────────────────────────────────────────────── */}
      <aside className="sb" style={{width:220,background:"rgba(2,14,30,.92)",backdropFilter:"blur(22px)",borderRight:"1px solid rgba(0,245,255,.08)",padding:"16px 9px",zIndex:10}}>
        {/* Logo */}
        <div style={{display:"flex",alignItems:"center",gap:8,padding:"0 4px 16px",borderBottom:"1px solid rgba(0,245,255,.07)",marginBottom:14,overflow:"hidden"}}>
          <div style={{width:34,height:34,borderRadius:10,background:"linear-gradient(135deg,#00f5ff,#0055ff)",display:"flex",alignItems:"center",justifyContent:"center",fontSize:16,flexShrink:0,boxShadow:"0 0 12px rgba(0,245,255,.28)"}}>⚕</div>
          <div className="slbl">
            <div style={{color:"#00f5ff",fontFamily:"Syne",fontWeight:800,fontSize:11,letterSpacing:1}}>CLINIC</div>
            <div style={{color:"rgba(255,255,255,.25)",fontSize:8,letterSpacing:2}}>MANAGEMENT</div>
          </div>
        </div>
        {/* Role badge */}
        <div className="slbl" style={{marginBottom:12,padding:"0 3px"}}>
          <div style={{background:isAdmin?"rgba(0,245,255,.06)":"rgba(167,139,250,.06)",border:`1px solid ${isAdmin?"rgba(0,245,255,.16)":"rgba(167,139,250,.16)"}`,borderRadius:7,padding:"4px 9px",color:isAdmin?"#00f5ff":"#a78bfa",fontSize:8,fontWeight:800,letterSpacing:1}}>
            {isAdmin?"🏥 ADMIN":"🎓 "+uType.toUpperCase()}
          </div>
        </div>
        {/* Nav */}
        <nav style={{flex:1,display:"flex",flexDirection:"column",gap:1}}>
          {nav.map(item=>(
            <div key={item.id} className={`nb ${page===item.id?"on":""}`} onClick={()=>setPage(item.id)}
              style={{display:"flex",alignItems:"center",gap:8,padding:"9px 8px",color:page===item.id?"#00f5ff":"rgba(255,255,255,.38)",position:"relative"}}>
              {page===item.id&&<div style={{position:"absolute",left:0,top:"18%",bottom:"18%",width:2.5,background:"#00f5ff",borderRadius:"0 2px 2px 0",boxShadow:"0 0 7px #00f5ff"}}/>}
              <span style={{fontSize:14,flexShrink:0}}>{item.ic}</span>
              <span className="slbl" style={{fontFamily:"Exo 2",fontWeight:500,fontSize:11,whiteSpace:"nowrap"}}>{item.lbl}</span>
              {item.badge>0&&<div style={{marginLeft:"auto",background:"#00f5ff",color:"#020b18",borderRadius:20,fontSize:8,fontWeight:800,padding:"1px 5px",flexShrink:0}}>{item.badge}</div>}
            </div>
          ))}
        </nav>
        {/* User / Logout */}
        <div style={{borderTop:"1px solid rgba(0,245,255,.07)",paddingTop:10}}>
          <div style={{display:"flex",alignItems:"center",gap:7,padding:"4px 5px",marginBottom:7,overflow:"hidden"}}>
            <div style={{width:28,height:28,borderRadius:"50%",background:"linear-gradient(135deg,#00f5ff,#0055ff)",display:"flex",alignItems:"center",justifyContent:"center",fontSize:9,fontWeight:700,color:"#020b18",flexShrink:0}}>
              {isAdmin?"AD":(creds.id||"ST").substring(0,2).toUpperCase()}
            </div>
            <div className="slbl">
              <div style={{color:"white",fontSize:10,fontWeight:600}}>{isAdmin?"School Nurse":uType}</div>
              <div style={{color:"rgba(0,245,255,.4)",fontSize:8}}>{creds.id||"demo@school"}</div>
            </div>
          </div>
          <button className="gb" onClick={logout} style={{width:"100%",background:"transparent",border:"1px solid rgba(248,113,113,.16)",borderRadius:7,padding:"6px",color:"rgba(248,113,113,.65)",fontSize:10,fontWeight:600,display:"flex",alignItems:"center",justifyContent:"center",gap:4}}>
            <span>⏻</span><span className="slbl">Logout</span>
          </button>
        </div>
      </aside>

      {/* ── MAIN COLUMN ──────────────────────────────────────────────────── */}
      <div style={{flex:1,display:"flex",flexDirection:"column",overflow:"hidden",zIndex:1}}>
        {/* Topbar */}
        <header className="topbar" style={{padding:"11px 20px",background:"rgba(2,14,30,.75)",backdropFilter:"blur(20px)",borderBottom:"1px solid rgba(0,245,255,.07)",display:"flex",alignItems:"center",justifyContent:"space-between",flexShrink:0}}>
          <div>
            <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:16}}>{nav.find(n=>n.id===page)?.lbl}</div>
            <div style={{color:"rgba(255,255,255,.24)",fontSize:9,marginTop:1}}>Sat, May 16 2026</div>
          </div>
          <div style={{display:"flex",gap:8,alignItems:"center"}}>
            {pendN>0&&isAdmin&&<div style={{background:"rgba(248,113,113,.12)",border:"1px solid rgba(248,113,113,.22)",borderRadius:8,padding:"5px 9px",color:"#f87171",fontSize:9,fontWeight:700}}>{pendN} pending</div>}
            <button className="btn" onClick={()=>isAdmin?setAddMod(true):setSPage("medicines")}
              style={{background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:8,padding:"7px 13px",fontWeight:700,fontSize:10,boxShadow:"0 3px 14px rgba(0,245,255,.24)"}}>
              {isAdmin?"+ Add Medicine":"+ Request"}
            </button>
          </div>
        </header>

        {/* Page */}
        <div key={page} className="fade content">

          {/* ══ ADMIN PAGES ══════════════════════════════════════════════════ */}
          {isAdmin&&<>

            {/* Dashboard */}
            {page==="dashboard"&&(
              <div style={{display:"flex",flexDirection:"column",gap:16}}>
                <div className="g4">
                  {[{lbl:"Total Medicines",val:"24",sub:"8 types available",ic:"💊",c:"#00f5ff",go:"inventory"},{lbl:"Low Stock",val:"2",sub:"Need restock",ic:"⚠️",c:"#facc15",go:"inventory"},{lbl:"Pending Requests",val:pendN,sub:"Awaiting action",ic:"📋",c:"#f87171",go:"requests"},{lbl:"Patients Today",val:"18",sub:"+3 from yesterday",ic:"👥",c:"#4ade80",go:"health"}].map((s,i)=>(
                    <div key={i} className="mc" onClick={()=>setAPage(s.go)} style={{background:"rgba(0,245,255,.035)",border:"1px solid rgba(0,245,255,.09)",borderRadius:14,padding:14,position:"relative",overflow:"hidden"}}>
                      <div style={{position:"absolute",top:-10,right:-10,width:55,height:55,borderRadius:"50%",background:`radial-gradient(circle,${s.c}15,transparent 70%)`,pointerEvents:"none"}}/>
                      <div style={{fontSize:20}}>{s.ic}</div>
                      <div style={{color:s.c,fontFamily:"Syne",fontWeight:800,fontSize:22,marginTop:6}}>{s.val}</div>
                      <div style={{color:"rgba(255,255,255,.65)",fontSize:11,fontWeight:600,marginTop:1}}>{s.lbl}</div>
                      <div style={{color:"rgba(255,255,255,.28)",fontSize:9,marginTop:1}}>{s.sub}</div>
                    </div>
                  ))}
                </div>
                <div className="g2">
                  <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:16}}>
                    <div style={{display:"flex",justifyContent:"space-between",alignItems:"center",marginBottom:12}}>
                      <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:12}}>Recent Requests</div>
                      <button className="gb" onClick={()=>setAPage("requests")} style={{background:"transparent",border:"1px solid rgba(0,245,255,.16)",color:"#00f5ff",borderRadius:6,padding:"3px 9px",fontSize:9,cursor:"pointer"}}>All</button>
                    </div>
                    {reqs.slice(0,5).map(r=>(
                      <div key={r.id} className="rr" onClick={()=>setReqMod(r)} style={{display:"flex",alignItems:"center",gap:8,padding:"7px 5px",marginBottom:2}}>
                        <div style={{width:28,height:28,borderRadius:"50%",background:"rgba(0,245,255,.08)",border:"1px solid rgba(0,245,255,.15)",display:"flex",alignItems:"center",justifyContent:"center",fontSize:8,fontWeight:700,color:"#00f5ff",flexShrink:0}}>{r.av}</div>
                        <div style={{flex:1,overflow:"hidden"}}>
                          <div style={{color:"white",fontSize:11,fontWeight:600,overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}}>{r.name}</div>
                          <div style={{color:"rgba(255,255,255,.28)",fontSize:9}}>{r.med} ×{r.qty}</div>
                        </div>
                        <div style={{background:`${rc(r.status)}13`,color:rc(r.status),border:`1px solid ${rc(r.status)}28`,borderRadius:20,fontSize:8,padding:"2px 7px",fontWeight:700,flexShrink:0}}>{r.status}</div>
                      </div>
                    ))}
                  </div>
                  <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:16}}>
                    <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:12,marginBottom:12}}>Stock Levels</div>
                    {MEDICINES.map(m=>(
                      <div key={m.id} style={{marginBottom:9}}>
                        <div style={{display:"flex",justifyContent:"space-between",marginBottom:3}}>
                          <div style={{display:"flex",alignItems:"center",gap:6}}>
                            <div style={{width:18,height:18,borderRadius:"50%",background:"rgba(0,245,255,.07)",display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",flexShrink:0}}>
                              <MedImg type={m.img} size={16}/>
                            </div>
                            <span style={{color:"rgba(255,255,255,.5)",fontSize:9}}>{m.name}</span>
                          </div>
                          <span style={{color:sc(m.status),fontSize:9,fontWeight:700}}>{m.stock}</span>
                        </div>
                        <div style={{background:"rgba(255,255,255,.05)",borderRadius:3,height:4}}>
                          <div style={{height:"100%",borderRadius:3,width:`${Math.min(100,(m.stock/120)*100)}%`,background:`linear-gradient(90deg,${sc(m.status)},${sc(m.status)}50)`,transition:"width 1s"}}/>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Inventory */}
            {page==="inventory"&&(
              <div style={{display:"flex",flexDirection:"column",gap:12}}>
                <div style={{display:"flex",gap:8,flexWrap:"wrap"}}>
                  <div style={{flex:1,position:"relative",minWidth:160}}>
                    <span style={{position:"absolute",left:11,top:"50%",transform:"translateY(-50%)",color:"rgba(0,245,255,.4)",fontSize:12}}>🔍</span>
                    <input value={search} onChange={e=>setSearch(e.target.value)} placeholder="Search medicines..."
                      style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.12)",borderRadius:9,padding:"9px 12px 9px 30px",color:"white",fontSize:11}}/>
                  </div>
                  <div style={{display:"flex",gap:4,flexWrap:"wrap"}}>
                    {CATS.map(c=>(
                      <button key={c} onClick={()=>setCat(c)}
                        style={{background:cat===c?"linear-gradient(135deg,#00f5ff,#0055ff)":"rgba(0,245,255,.04)",border:cat===c?"none":"1px solid rgba(0,245,255,.1)",borderRadius:7,padding:"6px 10px",color:cat===c?"#020b18":"rgba(255,255,255,.4)",fontSize:9,fontWeight:cat===c?800:400,cursor:"pointer",transition:"all .2s"}}>
                        {c}
                      </button>
                    ))}
                  </div>
                </div>
                <div className="gm">
                  {filtM.map(m=><MedCard key={m.id} m={m} onDetail={setMedMod} isAdmin={true}/>)}
                </div>
              </div>
            )}

            {/* Requests */}
            {page==="requests"&&(
              <div style={{display:"flex",flexDirection:"column",gap:10}}>
                <div style={{display:"flex",gap:6,flexWrap:"wrap"}}>
                  {["All","Pending","Approved","Dispensed","Rejected"].map(f=>(
                    <button key={f} onClick={()=>notify(`Filtered: ${f}`)}
                      style={{background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:7,padding:"5px 10px",color:"rgba(255,255,255,.45)",fontSize:9,cursor:"pointer",display:"flex",alignItems:"center",gap:4}}>
                      {f}{f==="Pending"&&<span style={{background:"#f87171",color:"white",borderRadius:10,fontSize:7,padding:"1px 4px",fontWeight:800}}>{pendN}</span>}
                    </button>
                  ))}
                </div>
                <div style={{background:"rgba(0,245,255,.02)",border:"1px solid rgba(0,245,255,.07)",borderRadius:13,overflow:"hidden"}}>
                  {reqs.map(r=>(
                    <div key={r.id} className="rr" onClick={()=>setReqMod(r)} style={{padding:"12px 14px",borderBottom:"1px solid rgba(0,245,255,.04)"}}>
                      <div style={{display:"flex",alignItems:"center",gap:9,marginBottom:7}}>
                        <div style={{width:32,height:32,borderRadius:"50%",background:"rgba(0,245,255,.08)",border:"1px solid rgba(0,245,255,.15)",display:"flex",alignItems:"center",justifyContent:"center",fontSize:9,fontWeight:700,color:"#00f5ff",flexShrink:0}}>{r.av}</div>
                        <div style={{flex:1,overflow:"hidden"}}>
                          <div style={{color:"white",fontSize:12,fontWeight:600}}>{r.name}</div>
                          <div style={{color:"rgba(255,255,255,.28)",fontSize:9}}>{r.sid} · {r.type} · {r.med} ×{r.qty} · {r.time}</div>
                        </div>
                        <div style={{background:`${rc(r.status)}12`,color:rc(r.status),border:`1px solid ${rc(r.status)}25`,borderRadius:20,fontSize:9,padding:"2px 8px",fontWeight:700,flexShrink:0}}>{r.status}</div>
                      </div>
                      <div style={{color:"rgba(255,255,255,.28)",fontSize:9,marginBottom:7,paddingLeft:41}}>Reason: {r.reason}</div>
                      <div style={{display:"flex",gap:6,paddingLeft:41}}>
                        {r.status==="Pending"&&<>
                          <button className="btn" onClick={e=>{e.stopPropagation();approve(r.id);}} style={{background:"rgba(74,222,128,.1)",border:"1px solid rgba(74,222,128,.25)",borderRadius:6,padding:"4px 10px",color:"#4ade80",fontSize:9,fontWeight:700,cursor:"pointer"}}>✓ Approve</button>
                          <button className="btn" onClick={e=>{e.stopPropagation();reject(r.id);}}  style={{background:"rgba(248,113,113,.08)",border:"1px solid rgba(248,113,113,.2)",borderRadius:6,padding:"4px 10px",color:"#f87171",fontSize:9,fontWeight:700,cursor:"pointer"}}>✕ Reject</button>
                        </>}
                        {r.status==="Approved"&&<button className="btn" onClick={e=>{e.stopPropagation();dispense(r.id);}} style={{background:"rgba(167,139,250,.1)",border:"1px solid rgba(167,139,250,.25)",borderRadius:6,padding:"4px 10px",color:"#a78bfa",fontSize:9,fontWeight:700,cursor:"pointer"}}>Dispense</button>}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Health */}
            {page==="health"&&(
              <div style={{display:"flex",flexDirection:"column",gap:16}}>
                <div className="hg">
                  <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                    <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13,marginBottom:2}}>Monthly Cases</div>
                    <div style={{color:"rgba(255,255,255,.25)",fontSize:9,marginBottom:16}}>School Year 2025–2026</div>
                    <div style={{display:"flex",alignItems:"flex-end",gap:6,height:100}}>
                      {HEALTH_DATA.map((d,i)=>(
                        <div key={i} style={{flex:1,display:"flex",flexDirection:"column",alignItems:"center",gap:4}}>
                          <span style={{color:"rgba(0,245,255,.6)",fontSize:8,fontWeight:700}}>{d.v}</span>
                          <div style={{width:"100%",height:`${(d.v/80)*100}%`,background:i===3?"linear-gradient(to top,#00f5ff,#0055ff)":"rgba(0,245,255,.13)",borderRadius:"4px 4px 0 0",border:"1px solid rgba(0,245,255,.13)"}}/>
                          <span style={{color:"rgba(255,255,255,.25)",fontSize:8}}>{d.m}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                  <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                    <div style={{display:"flex",alignItems:"center",gap:6,marginBottom:2}}>
                      <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13}}>Risk Predictor</div>
                      <div style={{background:"rgba(0,245,255,.09)",border:"1px solid rgba(0,245,255,.18)",borderRadius:20,fontSize:7,padding:"2px 5px",color:"#00f5ff",fontWeight:800}}>AI</div>
                    </div>
                    <div style={{color:"rgba(255,255,255,.25)",fontSize:9,marginBottom:14}}>Predicted outbreak probability</div>
                    {PREDS.map((p,i)=>(
                      <div key={i} style={{marginBottom:12}}>
                        <div style={{display:"flex",justifyContent:"space-between",marginBottom:3}}>
                          <span style={{color:"rgba(255,255,255,.6)",fontSize:10,display:"flex",alignItems:"center",gap:3}}>
                            <span style={{color:p.trend==="↑"?"#f87171":p.trend==="↓"?"#4ade80":"#facc15"}}>{p.trend}</span>{p.label}
                          </span>
                          <span style={{color:p.c,fontWeight:700,fontSize:10}}>{p.pct}%</span>
                        </div>
                        <div style={{background:"rgba(255,255,255,.05)",borderRadius:4,height:5}}>
                          <div style={{height:"100%",borderRadius:4,width:`${p.pct}%`,background:`linear-gradient(90deg,${p.c},${p.c}50)`,transition:"width 1s"}}/>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
                <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                  <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13,marginBottom:14}}>Log Patient Visit</div>
                  <div style={{display:"flex",flexWrap:"wrap",gap:10}}>
                    {[{l:"Patient Name",ph:"Full name"},{l:"ID Number",ph:"Student/Faculty ID"},{l:"Complaint",ph:"e.g. Fever, Headache"}].map(f=>(
                      <div key={f.l} style={{flex:"1 1 150px"}}>
                        <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:5}}>{f.l.toUpperCase()}</div>
                        <input placeholder={f.ph} style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.11)",borderRadius:9,padding:"9px 12px",color:"white",fontSize:11}}/>
                      </div>
                    ))}
                    <div style={{display:"flex",alignItems:"flex-end"}}>
                      <button className="btn" onClick={()=>notify("Visit logged!")} style={{background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:9,padding:"9px 16px",fontWeight:700,fontSize:11,boxShadow:"0 3px 12px rgba(0,245,255,.22)",whiteSpace:"nowrap"}}>Log Visit</button>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Reports */}
            {page==="reports"&&(
              <div style={{display:"flex",flexDirection:"column",gap:14}}>
                <div className="g3">
                  {[{title:"Inventory Report",desc:"Medicine usage & stock",icon:"📊",c:"#00f5ff"},{title:"Patient Summary",desc:"Visits & complaints",icon:"👤",c:"#4ade80"},{title:"Expiry Alerts",desc:"Expiring within 60 days",icon:"⏰",c:"#facc15"}].map((r,i)=>(
                    <div key={i} className="mc" onClick={()=>notify(`Generating ${r.title}...`)} style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:16,padding:18}}>
                      <div style={{fontSize:24,marginBottom:8}}>{r.icon}</div>
                      <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:12,marginBottom:3}}>{r.title}</div>
                      <div style={{color:"rgba(255,255,255,.3)",fontSize:10,marginBottom:13,lineHeight:1.5}}>{r.desc}</div>
                      <button className="gb" style={{background:`${r.c}0f`,border:`1px solid ${r.c}25`,borderRadius:7,padding:"5px 11px",color:r.c,fontSize:9,fontWeight:700,cursor:"pointer"}}>Generate →</button>
                    </div>
                  ))}
                </div>
                <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                  <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:12,marginBottom:13}}>Top Requested Medicines</div>
                  {[...MEDICINES].sort((a,b)=>b.req-a.req).map((m,i)=>(
                    <div key={m.id} style={{display:"flex",alignItems:"center",gap:10,marginBottom:10}}>
                      <span style={{color:"rgba(0,245,255,.35)",fontSize:9,fontWeight:700,width:14}}>{i+1}</span>
                      <div style={{width:22,height:22,borderRadius:"50%",background:"rgba(0,245,255,.06)",display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",flexShrink:0}}><MedImg type={m.img} size={20}/></div>
                      <div style={{flex:1}}>
                        <div style={{display:"flex",justifyContent:"space-between",marginBottom:3}}>
                          <span style={{color:"rgba(255,255,255,.6)",fontSize:10}}>{m.name}</span>
                          <span style={{color:"#00f5ff",fontWeight:700,fontSize:10}}>{m.req}</span>
                        </div>
                        <div style={{background:"rgba(255,255,255,.05)",borderRadius:3,height:4}}>
                          <div style={{height:"100%",borderRadius:3,width:`${(m.req/35)*100}%`,background:"linear-gradient(90deg,#00f5ff,#0055ff)"}}/>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Settings */}
            {page==="settings"&&(
              <div style={{display:"flex",flexDirection:"column",gap:14,maxWidth:560}}>
                {[{sec:"Clinic Information",fields:[{l:"Clinic Name",v:"School Health Clinic"},{l:"Nurse-in-Charge",v:"Admin Nurse"},{l:"Contact Number",v:"+63 912 345 6789"}]},{sec:"Notifications",fields:[{l:"Low Stock Alert Threshold",v:"20"},{l:"Expiry Alert (days before)",v:"60"}]}].map((g,gi)=>(
                  <div key={gi} style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                    <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:12,marginBottom:13}}>{g.sec}</div>
                    {g.fields.map(f=>(
                      <div key={f.l} style={{marginBottom:11}}>
                        <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:5}}>{f.l.toUpperCase()}</div>
                        <input defaultValue={f.v} style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.11)",borderRadius:9,padding:"9px 12px",color:"white",fontSize:11}}/>
                      </div>
                    ))}
                  </div>
                ))}
                <button className="btn" onClick={()=>notify("Settings saved!")} style={{background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:11,padding:"10px 22px",fontWeight:800,fontSize:12,boxShadow:"0 4px 16px rgba(0,245,255,.25)",alignSelf:"flex-start"}}>Save Changes</button>
              </div>
            )}
          </>}

          {/* ══ STUDENT PAGES ════════════════════════════════════════════════ */}
          {!isAdmin&&<>

            {/* Home */}
            {page==="home"&&(
              <div style={{display:"flex",flexDirection:"column",gap:14}}>
                <div style={{background:"linear-gradient(135deg,rgba(0,245,255,.06),rgba(0,80,255,.06))",border:"1px solid rgba(0,245,255,.12)",borderRadius:18,padding:"18px 20px",position:"relative",overflow:"hidden"}}>
                  <div style={{position:"absolute",top:-20,right:-20,width:90,height:90,borderRadius:"50%",background:"radial-gradient(circle,rgba(0,245,255,.09),transparent 70%)",pointerEvents:"none"}}/>
                  <div style={{color:"rgba(255,255,255,.38)",fontSize:10,marginBottom:3}}>Welcome back,</div>
                  <div style={{color:"white",fontFamily:"Syne",fontWeight:800,fontSize:18,marginBottom:2}}>{uType==="Faculty"?"Prof. Demo User":"Demo Student"} 👋</div>
                  <div style={{color:"rgba(0,245,255,.55)",fontSize:10}}>ID: {creds.id||"2024-00001"} · {uType}</div>
                  <div style={{marginTop:12,padding:"8px 12px",background:"rgba(0,245,255,.045)",borderRadius:9,border:"1px solid rgba(0,245,255,.09)",display:"inline-flex",alignItems:"center",gap:6}}>
                    <span style={{fontSize:12}}>💡</span>
                    <span style={{color:"rgba(255,255,255,.42)",fontSize:10}}>Drink 8 glasses of water daily to stay healthy!</span>
                  </div>
                </div>
                <div className="g3">
                  {[{lbl:"My Requests",val:myReqs.length,ic:"📋",c:"#00f5ff",go:"myrequests"},{lbl:"Pending",val:myReqs.filter(r=>r.status==="Pending").length,ic:"⏳",c:"#facc15",go:"myrequests"},{lbl:"Clinic Visits",val:visits.length,ic:"🏥",c:"#4ade80",go:"myhealth"}].map((s,i)=>(
                    <div key={i} className="mc" onClick={()=>setSPage(s.go)} style={{background:"rgba(0,245,255,.03)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:14,position:"relative",overflow:"hidden"}}>
                      <div style={{position:"absolute",top:-8,right:-8,width:48,height:48,borderRadius:"50%",background:`radial-gradient(circle,${s.c}15,transparent 70%)`,pointerEvents:"none"}}/>
                      <div style={{fontSize:18}}>{s.ic}</div>
                      <div style={{color:s.c,fontFamily:"Syne",fontWeight:800,fontSize:20,marginTop:6}}>{s.val}</div>
                      <div style={{color:"rgba(255,255,255,.5)",fontSize:10,marginTop:1}}>{s.lbl}</div>
                    </div>
                  ))}
                </div>
                <div style={{display:"flex",gap:9,flexWrap:"wrap"}}>
                  <button className="btn" onClick={()=>setSPage("medicines")} style={{flex:1,minWidth:130,background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:13,padding:"12px 14px",fontWeight:800,fontSize:12,boxShadow:"0 4px 16px rgba(0,245,255,.24)"}}>💊 Browse Medicines</button>
                  <button className="gb" onClick={()=>setSPage("myhealth")} style={{flex:1,minWidth:130,background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.16)",borderRadius:13,padding:"12px 14px",color:"#00f5ff",fontWeight:700,fontSize:12,cursor:"pointer"}}>◇ Log Symptoms</button>
                </div>
                {myReqs.length>0&&(
                  <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.07)",borderRadius:14,padding:16}}>
                    <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:12,marginBottom:11}}>Latest Request Status</div>
                    {myReqs.slice(0,3).map(r=>(
                      <div key={r.id} style={{display:"flex",alignItems:"center",gap:10,padding:"8px 0",borderBottom:"1px solid rgba(0,245,255,.04)"}}>
                        <div style={{width:32,height:32,borderRadius:10,background:"rgba(0,245,255,.06)",border:"1px solid rgba(0,245,255,.1)",display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",flexShrink:0}}>
                          <MedImg type={MEDICINES.find(m=>m.name===r.med)?.img||"tablet_white"} size={28}/>
                        </div>
                        <div style={{flex:1}}>
                          <div style={{color:"white",fontSize:11,fontWeight:600}}>{r.med}</div>
                          <div style={{color:"rgba(255,255,255,.28)",fontSize:9}}>{r.date} · {r.qty}x · {r.reason}</div>
                        </div>
                        <div style={{background:`${rc(r.status)}12`,color:rc(r.status),border:`1px solid ${rc(r.status)}25`,borderRadius:20,fontSize:8,padding:"2px 8px",fontWeight:700}}>{r.status}</div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* Browse Medicines */}
            {page==="medicines"&&(
              <div style={{display:"flex",flexDirection:"column",gap:12}}>
                <div style={{color:"rgba(255,255,255,.3)",fontSize:10,background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:8,padding:"8px 12px"}}>
                  💡 Click on any medicine card to submit a request
                </div>
                <div style={{position:"relative"}}>
                  <span style={{position:"absolute",left:11,top:"50%",transform:"translateY(-50%)",color:"rgba(0,245,255,.38)",fontSize:12}}>🔍</span>
                  <input value={search} onChange={e=>setSearch(e.target.value)} placeholder="Search medicines..."
                    style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:9,padding:"9px 12px 9px 30px",color:"white",fontSize:11}}/>
                </div>
                <div style={{display:"flex",gap:4,flexWrap:"wrap"}}>
                  {CATS.map(c=>(
                    <button key={c} onClick={()=>setCat(c)}
                      style={{background:cat===c?"linear-gradient(135deg,#00f5ff,#0055ff)":"rgba(0,245,255,.04)",border:cat===c?"none":"1px solid rgba(0,245,255,.09)",borderRadius:7,padding:"5px 9px",color:cat===c?"#020b18":"rgba(255,255,255,.38)",fontSize:9,fontWeight:cat===c?800:400,cursor:"pointer",transition:"all .2s"}}>
                      {c}
                    </button>
                  ))}
                </div>
                <div className="gm">
                  {filtM.map(m=><MedCard key={m.id} m={m} onRequest={m.stock>0?setStuMod:()=>notify("Out of stock","error")} isAdmin={false}/>)}
                </div>
              </div>
            )}

            {/* My Requests */}
            {page==="myrequests"&&(
              <div style={{display:"flex",flexDirection:"column",gap:12}}>
                <div style={{display:"flex",justifyContent:"space-between",alignItems:"center"}}>
                  <div style={{color:"rgba(255,255,255,.3)",fontSize:10}}>{myReqs.length} total requests</div>
                  <button className="btn" onClick={()=>setSPage("medicines")} style={{background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:8,padding:"6px 12px",fontWeight:700,fontSize:10}}>+ New Request</button>
                </div>
                {myReqs.map(r=>(
                  <div key={r.id} style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:13,padding:14}}>
                    <div style={{display:"flex",alignItems:"center",gap:11}}>
                      <div style={{width:42,height:42,borderRadius:12,background:"rgba(0,245,255,.06)",border:`1.5px solid ${rc(r.status)}25`,display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",flexShrink:0}}>
                        <MedImg type={MEDICINES.find(m=>m.name===r.med)?.img||"tablet_white"} size={38}/>
                      </div>
                      <div style={{flex:1}}>
                        <div style={{color:"white",fontSize:13,fontWeight:700}}>{r.med}</div>
                        <div style={{color:"rgba(255,255,255,.28)",fontSize:9,marginTop:2}}>{r.date} · {r.qty} unit(s) · {r.reason}</div>
                      </div>
                      <div style={{background:`${rc(r.status)}12`,color:rc(r.status),border:`1px solid ${rc(r.status)}25`,borderRadius:20,fontSize:9,padding:"3px 10px",fontWeight:700,flexShrink:0}}>{r.status}</div>
                    </div>
                    {r.status==="Approved"&&(
                      <div style={{marginTop:10,background:"rgba(74,222,128,.05)",border:"1px solid rgba(74,222,128,.15)",borderRadius:8,padding:"7px 10px",color:"rgba(74,222,128,.75)",fontSize:9}}>
                        ✓ Approved — Please proceed to the clinic to claim your medicine.
                      </div>
                    )}
                    {r.status==="Dispensed"&&(
                      <div style={{marginTop:10,background:"rgba(167,139,250,.05)",border:"1px solid rgba(167,139,250,.15)",borderRadius:8,padding:"7px 10px",color:"rgba(167,139,250,.75)",fontSize:9}}>
                        ✓ Dispensed — Medicine has been given to you.
                      </div>
                    )}
                  </div>
                ))}
                {myReqs.length===0&&<div style={{textAlign:"center",color:"rgba(255,255,255,.2)",fontSize:12,padding:"40px 0"}}>No requests yet. Browse medicines to start.</div>}
              </div>
            )}

            {/* My Health */}
            {page==="myhealth"&&(
              <div style={{display:"flex",flexDirection:"column",gap:14}}>
                <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                  <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13,marginBottom:14}}>Log My Symptoms</div>
                  <div style={{display:"flex",flexDirection:"column",gap:11}}>
                    <div>
                      <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:5}}>COMPLAINT / SYMPTOM</div>
                      <input value={hForm.complaint} onChange={e=>setHForm(p=>({...p,complaint:e.target.value}))} placeholder="e.g. Fever, Headache, Cough..."
                        style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:9,padding:"10px 13px",color:"white",fontSize:11}}/>
                    </div>
                    <div className="g2">
                      <div>
                        <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:5}}>TEMPERATURE (°C)</div>
                        <input value={hForm.temp} onChange={e=>setHForm(p=>({...p,temp:e.target.value}))} placeholder="e.g. 37.5"
                          style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:9,padding:"10px 13px",color:"white",fontSize:11}}/>
                      </div>
                      <div>
                        <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:5}}>BLOOD PRESSURE</div>
                        <input value={hForm.bp} onChange={e=>setHForm(p=>({...p,bp:e.target.value}))} placeholder="e.g. 120/80"
                          style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:9,padding:"10px 13px",color:"white",fontSize:11}}/>
                      </div>
                    </div>
                    <button className="btn" onClick={logVisit} style={{background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:10,padding:"11px",fontWeight:800,fontSize:12,boxShadow:"0 4px 16px rgba(0,245,255,.24)"}}>Submit Health Record</button>
                  </div>
                </div>
                <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                  <div style={{display:"flex",alignItems:"center",gap:6,marginBottom:14}}>
                    <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13}}>Health Prediction</div>
                    <div style={{background:"rgba(0,245,255,.09)",border:"1px solid rgba(0,245,255,.18)",borderRadius:20,fontSize:7,padding:"2px 5px",color:"#00f5ff",fontWeight:800}}>AI</div>
                  </div>
                  {PREDS.map((p,i)=>(
                    <div key={i} style={{marginBottom:12}}>
                      <div style={{display:"flex",justifyContent:"space-between",marginBottom:3}}>
                        <span style={{color:"rgba(255,255,255,.6)",fontSize:10,display:"flex",alignItems:"center",gap:3}}>
                          <span style={{color:p.trend==="↑"?"#f87171":p.trend==="↓"?"#4ade80":"#facc15"}}>{p.trend}</span>{p.label}
                        </span>
                        <span style={{color:p.c,fontWeight:700,fontSize:10}}>{p.pct}%</span>
                      </div>
                      <div style={{background:"rgba(255,255,255,.05)",borderRadius:4,height:5}}>
                        <div style={{height:"100%",borderRadius:4,width:`${p.pct}%`,background:`linear-gradient(90deg,${p.c},${p.c}50)`,transition:"width 1s"}}/>
                      </div>
                    </div>
                  ))}
                </div>
                <div style={{background:"rgba(0,245,255,.025)",border:"1px solid rgba(0,245,255,.08)",borderRadius:14,padding:18}}>
                  <div style={{color:"white",fontFamily:"Syne",fontWeight:700,fontSize:13,marginBottom:13}}>Visit History</div>
                  {visits.map((v,i)=>(
                    <div key={i} style={{display:"flex",gap:11,padding:"9px 0",borderBottom:"1px solid rgba(0,245,255,.04)",alignItems:"center"}}>
                      <div style={{width:34,height:34,borderRadius:10,background:"rgba(0,245,255,.05)",border:"1px solid rgba(0,245,255,.1)",display:"flex",alignItems:"center",justifyContent:"center",fontSize:15,flexShrink:0}}>🏥</div>
                      <div style={{flex:1}}>
                        <div style={{color:"white",fontSize:11,fontWeight:600}}>{v.complaint}</div>
                        <div style={{color:"rgba(255,255,255,.28)",fontSize:9}}>{v.date} · {v.med}</div>
                      </div>
                      <div style={{background:v.status==="Resolved"?"rgba(74,222,128,.1)":"rgba(0,245,255,.1)",color:v.status==="Resolved"?"#4ade80":"#00f5ff",border:`1px solid ${v.status==="Resolved"?"rgba(74,222,128,.22)":"rgba(0,245,255,.22)"}`,borderRadius:20,fontSize:8,padding:"2px 8px",fontWeight:700}}>{v.status}</div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </>}
        </div>

        {/* ── BOTTOM NAV (mobile) ─────────────────────────────────────────── */}
        <div className="bnav" style={{justifyContent:"space-around",alignItems:"center"}}>
          {nav.slice(0,4).map(item=>(
            <div key={item.id} onClick={()=>setPage(item.id)} style={{display:"flex",flexDirection:"column",alignItems:"center",gap:3,padding:"4px 12px",cursor:"pointer",color:page===item.id?"#00f5ff":"rgba(255,255,255,.35)",position:"relative"}}>
              {item.badge>0&&<div style={{position:"absolute",top:0,right:8,background:"#f87171",borderRadius:20,fontSize:7,fontWeight:800,padding:"1px 4px",color:"white"}}>{item.badge}</div>}
              <span style={{fontSize:16}}>{item.ic}</span>
              <span style={{fontSize:8,fontFamily:"Exo 2",fontWeight:600}}>{item.lbl}</span>
              {page===item.id&&<div style={{position:"absolute",bottom:-10,width:22,height:2,background:"#00f5ff",borderRadius:2,boxShadow:"0 0 6px #00f5ff"}}/>}
            </div>
          ))}
          <div onClick={logout} style={{display:"flex",flexDirection:"column",alignItems:"center",gap:3,padding:"4px 12px",cursor:"pointer",color:"rgba(248,113,113,.55)"}}>
            <span style={{fontSize:16}}>⏻</span>
            <span style={{fontSize:8,fontFamily:"Exo 2",fontWeight:600}}>Logout</span>
          </div>
        </div>
      </div>

      {/* ══ MODAL: Medicine Detail (Admin) ══════════════════════════════════ */}
      {medMod&&(
        <div onClick={()=>setMedMod(null)} style={{position:"fixed",inset:0,background:"rgba(0,0,0,.65)",backdropFilter:"blur(10px)",display:"flex",alignItems:"center",justifyContent:"center",zIndex:100,padding:12}}>
          <div className="modal-box" onClick={e=>e.stopPropagation()} style={{background:"rgba(2,12,28,.96)",border:"1px solid rgba(0,245,255,.18)",borderRadius:22,padding:26,width:380,boxShadow:"0 0 50px rgba(0,245,255,.12)"}}>
            <div style={{display:"flex",justifyContent:"space-between",alignItems:"flex-start",marginBottom:18}}>
              <div style={{width:70,height:70,borderRadius:"50%",background:"rgba(0,245,255,.06)",border:`2px solid ${sc(medMod.status)}28`,display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",boxShadow:`0 0 18px ${sc(medMod.status)}15`}}>
                <MedImg type={medMod.img} size={62}/>
              </div>
              <button onClick={()=>setMedMod(null)} style={{background:"rgba(255,255,255,.05)",border:"1px solid rgba(255,255,255,.09)",borderRadius:7,padding:"5px 10px",color:"rgba(255,255,255,.4)",cursor:"pointer",fontSize:12}}>✕</button>
            </div>
            <div style={{color:"white",fontFamily:"Syne",fontWeight:800,fontSize:18}}>{medMod.name}</div>
            <div style={{color:"rgba(0,245,255,.55)",fontSize:11,marginTop:2,marginBottom:4}}>{medMod.cat} · {medMod.dose}</div>
            <div style={{color:"rgba(255,255,255,.4)",fontSize:11,lineHeight:1.6,marginBottom:14}}>{medMod.desc}</div>
            <div style={{background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.08)",borderRadius:10,padding:"8px 12px",marginBottom:14}}>
              <div style={{color:"rgba(255,255,255,.3)",fontSize:9,marginBottom:3}}>COMMON USES</div>
              <div style={{color:"rgba(255,255,255,.65)",fontSize:11}}>{medMod.uses}</div>
            </div>
            {[["Stock",`${medMod.stock} ${medMod.unit}`],["Expiry",medMod.expiry],["Status",medMod.status],["Total Requests",`${medMod.req} requests`]].map(([l,v])=>(
              <div key={l} style={{display:"flex",justifyContent:"space-between",padding:"9px 0",borderBottom:"1px solid rgba(0,245,255,.05)"}}>
                <span style={{color:"rgba(255,255,255,.35)",fontSize:11}}>{l}</span>
                <span style={{color:l==="Status"?sc(medMod.status):"rgba(255,255,255,.75)",fontSize:11,fontWeight:600}}>{v}</span>
              </div>
            ))}
            <div style={{display:"flex",gap:8,marginTop:18}}>
              <button className="btn" onClick={()=>{notify(`Restocking ${medMod.name}...`);setMedMod(null);}} style={{flex:1,background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:10,padding:10,fontWeight:700,fontSize:11,boxShadow:"0 3px 14px rgba(0,245,255,.24)"}}>Restock</button>
              <button className="gb" onClick={()=>{notify(`Editing ${medMod.name}`);setMedMod(null);}} style={{flex:1,background:"transparent",border:"1px solid rgba(0,245,255,.18)",borderRadius:10,padding:10,color:"#00f5ff",fontSize:11,fontWeight:600,cursor:"pointer"}}>Edit Info</button>
            </div>
          </div>
        </div>
      )}

      {/* ══ MODAL: Request Detail (Admin) ═══════════════════════════════════ */}
      {reqMod&&(
        <div onClick={()=>setReqMod(null)} style={{position:"fixed",inset:0,background:"rgba(0,0,0,.65)",backdropFilter:"blur(10px)",display:"flex",alignItems:"center",justifyContent:"center",zIndex:100,padding:12}}>
          <div className="modal-box" onClick={e=>e.stopPropagation()} style={{background:"rgba(2,12,28,.96)",border:"1px solid rgba(0,245,255,.18)",borderRadius:22,padding:26,width:380,boxShadow:"0 0 50px rgba(0,245,255,.12)"}}>
            <div style={{display:"flex",justifyContent:"space-between",alignItems:"center",marginBottom:18}}>
              <div style={{color:"white",fontFamily:"Syne",fontWeight:800,fontSize:16}}>Request Review</div>
              <button onClick={()=>setReqMod(null)} style={{background:"rgba(255,255,255,.05)",border:"1px solid rgba(255,255,255,.09)",borderRadius:7,padding:"5px 10px",color:"rgba(255,255,255,.4)",cursor:"pointer",fontSize:12}}>✕</button>
            </div>
            <div style={{display:"flex",alignItems:"center",gap:11,marginBottom:18,background:"rgba(0,245,255,.04)",borderRadius:12,padding:13}}>
              <div style={{width:42,height:42,borderRadius:"50%",background:"rgba(0,245,255,.08)",border:"1px solid rgba(0,245,255,.18)",display:"flex",alignItems:"center",justifyContent:"center",fontSize:13,fontWeight:700,color:"#00f5ff",flexShrink:0}}>{reqMod.av}</div>
              <div>
                <div style={{color:"white",fontWeight:700,fontSize:13}}>{reqMod.name}</div>
                <div style={{color:"rgba(0,245,255,.5)",fontSize:10}}>{reqMod.type} · {reqMod.sid} · {reqMod.time}</div>
              </div>
            </div>
            <div style={{display:"flex",alignItems:"center",gap:10,marginBottom:16,background:"rgba(0,245,255,.03)",borderRadius:10,padding:"10px 12px"}}>
              <div style={{width:38,height:38,borderRadius:10,background:"rgba(0,245,255,.06)",display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",flexShrink:0}}>
                <MedImg type={MEDICINES.find(m=>m.name===reqMod.med)?.img||"tablet_white"} size={34}/>
              </div>
              <div>
                <div style={{color:"white",fontSize:13,fontWeight:600}}>{reqMod.med}</div>
                <div style={{color:"rgba(255,255,255,.35)",fontSize:10}}>Qty: {reqMod.qty} · Reason: {reqMod.reason}</div>
              </div>
            </div>
            {[["Status",reqMod.status]].map(([l,v])=>(
              <div key={l} style={{display:"flex",justifyContent:"space-between",padding:"9px 0",borderBottom:"1px solid rgba(0,245,255,.05)"}}>
                <span style={{color:"rgba(255,255,255,.35)",fontSize:11}}>{l}</span>
                <span style={{color:rc(v),fontSize:11,fontWeight:700}}>{v}</span>
              </div>
            ))}
            <div style={{display:"flex",gap:8,marginTop:18}}>
              {reqMod.status==="Pending"&&<>
                <button className="btn" onClick={()=>approve(reqMod.id)} style={{flex:1,background:"linear-gradient(135deg,#4ade80,#16a34a)",color:"#020b18",borderRadius:10,padding:10,fontWeight:700,fontSize:11,boxShadow:"0 3px 14px rgba(74,222,128,.24)"}}>✓ Approve</button>
                <button className="btn" onClick={()=>reject(reqMod.id)}  style={{flex:1,background:"rgba(248,113,113,.08)",border:"1px solid rgba(248,113,113,.22)",borderRadius:10,padding:10,color:"#f87171",fontSize:11,fontWeight:700,cursor:"pointer"}}>✕ Reject</button>
              </>}
              {reqMod.status==="Approved"&&<button className="btn" onClick={()=>dispense(reqMod.id)} style={{width:"100%",background:"linear-gradient(135deg,#a78bfa,#7c3aed)",color:"white",borderRadius:10,padding:10,fontWeight:700,fontSize:11,boxShadow:"0 3px 14px rgba(167,139,250,.25)"}}>Dispense Medicine</button>}
              {(reqMod.status==="Dispensed"||reqMod.status==="Rejected")&&<div style={{color:"rgba(255,255,255,.3)",fontSize:11,textAlign:"center",width:"100%",padding:8}}>This request has been finalized.</div>}
            </div>
          </div>
        </div>
      )}

      {/* ══ MODAL: Add Medicine (Admin) ══════════════════════════════════════ */}
      {addMod&&(
        <div onClick={()=>setAddMod(false)} style={{position:"fixed",inset:0,background:"rgba(0,0,0,.65)",backdropFilter:"blur(10px)",display:"flex",alignItems:"center",justifyContent:"center",zIndex:100,padding:12}}>
          <div className="modal-box" onClick={e=>e.stopPropagation()} style={{background:"rgba(2,12,28,.96)",border:"1px solid rgba(0,245,255,.18)",borderRadius:22,padding:26,width:440,boxShadow:"0 0 50px rgba(0,245,255,.12)"}}>
            <div style={{display:"flex",justifyContent:"space-between",alignItems:"center",marginBottom:20}}>
              <div style={{color:"white",fontFamily:"Syne",fontWeight:800,fontSize:16}}>Add New Medicine</div>
              <button onClick={()=>setAddMod(false)} style={{background:"rgba(255,255,255,.05)",border:"1px solid rgba(255,255,255,.09)",borderRadius:7,padding:"5px 10px",color:"rgba(255,255,255,.4)",cursor:"pointer",fontSize:12}}>✕</button>
            </div>
            {/* Image preview area */}
            <div style={{background:"rgba(0,245,255,.03)",border:"2px dashed rgba(0,245,255,.15)",borderRadius:14,padding:"16px",textAlign:"center",marginBottom:16,cursor:"pointer"}} onClick={()=>notify("Image upload coming in full build!")}>
              <div style={{width:60,height:60,borderRadius:"50%",background:"rgba(0,245,255,.06)",border:"1px solid rgba(0,245,255,.15)",margin:"0 auto 8px",display:"flex",alignItems:"center",justifyContent:"center",fontSize:24}}>📷</div>
              <div style={{color:"rgba(0,245,255,.55)",fontSize:10,fontWeight:600}}>Click to upload medicine image</div>
              <div style={{color:"rgba(255,255,255,.2)",fontSize:9,marginTop:3}}>JPG, PNG up to 2MB · Will appear in card circle</div>
            </div>
            <div style={{display:"grid",gridTemplateColumns:"1fr 1fr",gap:11}}>
              {[{l:"Medicine Name",ph:"e.g. Paracetamol"},{l:"Dosage",ph:"e.g. 500mg"},{l:"Category",ph:"e.g. Analgesic"},{l:"Initial Stock",ph:"e.g. 100"},{l:"Unit",ph:"e.g. tablets"},{l:"Expiry Date",ph:"MM/YYYY"}].map(f=>(
                <div key={f.l}>
                  <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:5}}>{f.l.toUpperCase()}</div>
                  <input placeholder={f.ph} style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:9,padding:"9px 12px",color:"white",fontSize:11}}/>
                </div>
              ))}
            </div>
            <button className="btn" onClick={()=>{notify("Medicine added!");setAddMod(false);}} style={{width:"100%",marginTop:18,background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:11,padding:12,fontWeight:800,fontSize:13,boxShadow:"0 4px 18px rgba(0,245,255,.26)"}}>Add to Inventory</button>
          </div>
        </div>
      )}

      {/* ══ MODAL: Student Request Medicine ════════════════════════════════ */}
      {stuMod&&(
        <div onClick={()=>setStuMod(null)} style={{position:"fixed",inset:0,background:"rgba(0,0,0,.65)",backdropFilter:"blur(10px)",display:"flex",alignItems:"center",justifyContent:"center",zIndex:100,padding:12}}>
          <div className="modal-box" onClick={e=>e.stopPropagation()} style={{background:"rgba(2,12,28,.96)",border:"1px solid rgba(0,245,255,.18)",borderRadius:22,padding:26,width:380,boxShadow:"0 0 50px rgba(0,245,255,.12)"}}>
            <div style={{display:"flex",justifyContent:"space-between",alignItems:"center",marginBottom:18}}>
              <div style={{color:"white",fontFamily:"Syne",fontWeight:800,fontSize:15}}>Request Medicine</div>
              <button onClick={()=>setStuMod(null)} style={{background:"rgba(255,255,255,.05)",border:"1px solid rgba(255,255,255,.09)",borderRadius:7,padding:"5px 10px",color:"rgba(255,255,255,.4)",cursor:"pointer",fontSize:12}}>✕</button>
            </div>
            {/* Medicine info */}
            <div style={{display:"flex",gap:13,alignItems:"center",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:14,padding:"14px",marginBottom:18}}>
              <div style={{width:60,height:60,borderRadius:"50%",background:"rgba(0,245,255,.06)",border:"2px solid rgba(0,245,255,.14)",display:"flex",alignItems:"center",justifyContent:"center",overflow:"hidden",flexShrink:0}}>
                <MedImg type={stuMod.img} size={54}/>
              </div>
              <div>
                <div style={{color:"white",fontWeight:700,fontSize:14}}>{stuMod.name}</div>
                <div style={{color:"rgba(0,245,255,.55)",fontSize:10,marginTop:1}}>{stuMod.cat} · {stuMod.dose}</div>
                <div style={{color:"rgba(255,255,255,.35)",fontSize:10,marginTop:1}}>{stuMod.stock} {stuMod.unit} available</div>
              </div>
            </div>
            <div style={{marginBottom:13}}>
              <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:6}}>QUANTITY</div>
              <div style={{display:"flex",gap:6,alignItems:"center"}}>
                <button className="gb" onClick={()=>setRForm(p=>({...p,qty:Math.max(1,p.qty-1)}))} style={{width:32,height:32,background:"rgba(0,245,255,.06)",border:"1px solid rgba(0,245,255,.15)",borderRadius:8,color:"#00f5ff",fontSize:16,cursor:"pointer",fontWeight:700,display:"flex",alignItems:"center",justifyContent:"center"}}>−</button>
                <div style={{flex:1,background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:8,padding:"8px",textAlign:"center",color:"white",fontSize:14,fontWeight:700}}>{rForm.qty}</div>
                <button className="gb" onClick={()=>setRForm(p=>({...p,qty:Math.min(5,p.qty+1)}))} style={{width:32,height:32,background:"rgba(0,245,255,.06)",border:"1px solid rgba(0,245,255,.15)",borderRadius:8,color:"#00f5ff",fontSize:16,cursor:"pointer",fontWeight:700,display:"flex",alignItems:"center",justifyContent:"center"}}>+</button>
              </div>
            </div>
            <div style={{marginBottom:18}}>
              <div style={{color:"rgba(0,245,255,.45)",fontSize:8,fontWeight:800,letterSpacing:1,marginBottom:6}}>REASON / COMPLAINT</div>
              <textarea value={rForm.reason} onChange={e=>setRForm(p=>({...p,reason:e.target.value}))} placeholder="Briefly describe your complaint..."
                style={{width:"100%",background:"rgba(0,245,255,.04)",border:"1px solid rgba(0,245,255,.1)",borderRadius:9,padding:"10px 13px",color:"white",fontSize:11,resize:"none",height:72,fontFamily:"'Exo 2',sans-serif"}}/>
            </div>
            <button className="btn" onClick={submitReq} style={{width:"100%",background:"linear-gradient(135deg,#00f5ff,#0055ff)",color:"#020b18",borderRadius:11,padding:12,fontWeight:800,fontSize:13,boxShadow:"0 4px 18px rgba(0,245,255,.26)"}}>Submit Request</button>
          </div>
        </div>
      )}

      {toast&&<Toast t={toast}/>}
    </div>
  );
}