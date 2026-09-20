var Zm={};let qi="https://huggingface.co/snowfluke/ppu-paddle-ocr-models/resolve/main",Xm="https://huggingface.co/snowfluke/ppu-paddle-ocr-models/resolve/main",Qm="https://github.com/PT-Perkasa-Pilar-Utama/ppu-paddle-ocr-models/raw/main";function Jm(e){let t=Zm?.PPU_PADDLE_OCR_MODEL_MIRROR;return!t||t==="0"||t==="false"||!e.startsWith(qi)?null:Qm+e.slice(qi.length)}let Ed={detection:`${qi}/detection/ort/PP-OCRv6_tiny_det.ort`,recognition:`${qi}/recognition/ort/PP-OCRv6_tiny_rec.ort`,charactersDictionary:`${Xm}/recognition/ppocrv6_tiny_dict.txt`},eg=Ed,Dt=eg;/*!
 * ONNX Runtime Web v1.23.2
 * Copyright (c) Microsoft Corporation. All rights reserved.
 * Licensed under the MIT License.
 */var ja=Object.defineProperty,tg=Object.getOwnPropertyDescriptor,ig=Object.getOwnPropertyNames,rg=Object.prototype.hasOwnProperty,ag=(e=>typeof require<"u"?require:typeof Proxy<"u"?new Proxy(e,{get:(t,r)=>(typeof require<"u"?require:t)[r]}):e)(function(e){if(typeof require<"u")return require.apply(this,arguments);throw Error('Dynamic require of "'+e+'" is not supported')}),L=(e,t)=>()=>(e&&(t=e(e=0)),t),qt=(e,t)=>{for(var r in t)ja(e,r,{get:t[r],enumerable:!0})},ng=(e,t,r,i)=>{if(t&&typeof t=="object"||typeof t=="function")for(let a of ig(t))!rg.call(e,a)&&a!==r&&ja(e,a,{get:()=>t[a],enumerable:!(i=tg(t,a))||i.enumerable});return e},ci=e=>ng(ja({},"__esModule",{value:!0}),e),Zt,ct,Et,Ms,zd,Ad=L(()=>{Zt=new Map,ct=[],Et=(e,t,r)=>{if(t&&typeof t.init=="function"&&typeof t.createInferenceSessionHandler=="function"){let i=Zt.get(e);if(i===void 0)Zt.set(e,{backend:t,priority:r});else{if(i.priority>r)return;if(i.priority===r&&i.backend!==t)throw new Error(`cannot register backend "${e}" using priority ${r}`)}if(r>=0){let a=ct.indexOf(e);a!==-1&&ct.splice(a,1);for(let n=0;n<ct.length;n++)if(Zt.get(ct[n]).priority<=r){ct.splice(n,0,e);return}ct.push(e)}return}throw new TypeError("not a valid backend")},Ms=async e=>{let t=Zt.get(e);if(!t)return"backend not found.";if(t.initialized)return t.backend;if(t.aborted)return t.error;{let r=!!t.initPromise;try{return r||(t.initPromise=t.backend.init(e)),await t.initPromise,t.initialized=!0,t.backend}catch(i){return r||(t.error=`${i}`,t.aborted=!0),t.error}finally{delete t.initPromise}}},zd=async e=>{let t=e.executionProviders||[],r=t.map(l=>typeof l=="string"?l:l.name),i=r.length===0?ct:r,a,n=[],s=new Set;for(let l of i){let d=await Ms(l);typeof d=="string"?n.push({name:l,err:d}):(a||(a=d),a===d&&s.add(l))}if(!a)throw new Error(`no available backend found. ERR: ${n.map(l=>`[${l.name}] ${l.err}`).join(", ")}`);for(let{name:l,err:d}of n)r.includes(l)&&console.warn(`removing requested execution provider "${l}" from session options because it is not available: ${d}`);let o=t.filter(l=>s.has(typeof l=="string"?l:l.name));return[a,new Proxy(e,{get:(l,d)=>d==="executionProviders"?o:Reflect.get(l,d)})]}}),sg=L(()=>{Ad()}),Od,og=L(()=>{Od="1.23.2"}),xr,ke,Rd=L(()=>{og(),xr="warning",ke={wasm:{},webgl:{},webgpu:{},versions:{common:Od},set logLevel(e){if(e!==void 0){if(typeof e!="string"||["verbose","info","warning","error","fatal"].indexOf(e)===-1)throw new Error(`Unsupported logging level: ${e}`);xr=e}},get logLevel(){return xr}},Object.defineProperty(ke,"logLevel",{enumerable:!0})}),ge,ug=L(()=>{Rd(),ge=ke}),Bd,Md,lg=L(()=>{Bd=(e,t)=>{let r=typeof document<"u"?document.createElement("canvas"):new OffscreenCanvas(1,1);r.width=e.dims[3],r.height=e.dims[2];let i=r.getContext("2d");if(i!=null){let a,n;t?.tensorLayout!==void 0&&t.tensorLayout==="NHWC"?(a=e.dims[2],n=e.dims[3]):(a=e.dims[3],n=e.dims[2]);let s=t?.format!==void 0?t.format:"RGB",o=t?.norm,l,d;o===void 0||o.mean===void 0?l=[255,255,255,255]:typeof o.mean=="number"?l=[o.mean,o.mean,o.mean,o.mean]:(l=[o.mean[0],o.mean[1],o.mean[2],0],o.mean[3]!==void 0&&(l[3]=o.mean[3])),o===void 0||o.bias===void 0?d=[0,0,0,0]:typeof o.bias=="number"?d=[o.bias,o.bias,o.bias,o.bias]:(d=[o.bias[0],o.bias[1],o.bias[2],0],o.bias[3]!==void 0&&(d[3]=o.bias[3]));let c=n*a,f=0,m=c,y=c*2,_=-1;s==="RGBA"?(f=0,m=c,y=c*2,_=c*3):s==="RGB"?(f=0,m=c,y=c*2):s==="RBG"&&(f=0,y=c,m=c*2);for(let b=0;b<n;b++)for(let x=0;x<a;x++){let $=(e.data[f++]-d[0])*l[0],w=(e.data[m++]-d[1])*l[1],T=(e.data[y++]-d[2])*l[2],C=_===-1?255:(e.data[_++]-d[3])*l[3];i.fillStyle="rgba("+$+","+w+","+T+","+C+")",i.fillRect(x,b,1,1)}if("toDataURL"in r)return r.toDataURL();throw new Error("toDataURL is not supported")}else throw new Error("Can not access image data")},Md=(e,t)=>{let r=typeof document<"u"?document.createElement("canvas").getContext("2d"):new OffscreenCanvas(1,1).getContext("2d"),i;if(r!=null){let a,n,s;t?.tensorLayout!==void 0&&t.tensorLayout==="NHWC"?(a=e.dims[2],n=e.dims[1],s=e.dims[3]):(a=e.dims[3],n=e.dims[2],s=e.dims[1]);let o=t!==void 0&&t.format!==void 0?t.format:"RGB",l=t?.norm,d,c;l===void 0||l.mean===void 0?d=[255,255,255,255]:typeof l.mean=="number"?d=[l.mean,l.mean,l.mean,l.mean]:(d=[l.mean[0],l.mean[1],l.mean[2],255],l.mean[3]!==void 0&&(d[3]=l.mean[3])),l===void 0||l.bias===void 0?c=[0,0,0,0]:typeof l.bias=="number"?c=[l.bias,l.bias,l.bias,l.bias]:(c=[l.bias[0],l.bias[1],l.bias[2],0],l.bias[3]!==void 0&&(c[3]=l.bias[3]));let f=n*a;if(t!==void 0&&(t.format!==void 0&&s===4&&t.format!=="RGBA"||s===3&&t.format!=="RGB"&&t.format!=="BGR"))throw new Error("Tensor format doesn't match input tensor dims");let m=4,y=0,_=1,b=2,x=3,$=0,w=f,T=f*2,C=-1;o==="RGBA"?($=0,w=f,T=f*2,C=f*3):o==="RGB"?($=0,w=f,T=f*2):o==="RBG"&&($=0,T=f,w=f*2),i=r.createImageData(a,n);for(let I=0;I<n*a;y+=m,_+=m,b+=m,x+=m,I++)i.data[y]=(e.data[$++]-c[0])*d[0],i.data[_]=(e.data[w++]-c[1])*d[1],i.data[b]=(e.data[T++]-c[2])*d[2],i.data[x]=C===-1?255:(e.data[C++]-c[3])*d[3]}else throw new Error("Can not access image data");return i}}),Si,Dd,Nd,Pd,Ud,Wd,dg=L(()=>{Fa(),Si=(e,t)=>{if(e===void 0)throw new Error("Image buffer must be defined");if(t.height===void 0||t.width===void 0)throw new Error("Image height and width must be defined");if(t.tensorLayout==="NHWC")throw new Error("NHWC Tensor layout is not supported yet");let{height:r,width:i}=t,a=t.norm??{mean:255,bias:0},n,s;typeof a.mean=="number"?n=[a.mean,a.mean,a.mean,a.mean]:n=[a.mean[0],a.mean[1],a.mean[2],a.mean[3]??255],typeof a.bias=="number"?s=[a.bias,a.bias,a.bias,a.bias]:s=[a.bias[0],a.bias[1],a.bias[2],a.bias[3]??0];let o=t.format!==void 0?t.format:"RGBA",l=t.tensorFormat!==void 0&&t.tensorFormat!==void 0?t.tensorFormat:"RGB",d=r*i,c=l==="RGBA"?new Float32Array(d*4):new Float32Array(d*3),f=4,m=0,y=1,_=2,b=3,x=0,$=d,w=d*2,T=-1;o==="RGB"&&(f=3,m=0,y=1,_=2,b=-1),l==="RGBA"?T=d*3:l==="RBG"?(x=0,w=d,$=d*2):l==="BGR"&&(w=0,$=d,x=d*2);for(let C=0;C<d;C++,m+=f,_+=f,y+=f,b+=f)c[x++]=(e[m]+s[0])/n[0],c[$++]=(e[y]+s[1])/n[1],c[w++]=(e[_]+s[2])/n[2],T!==-1&&b!==-1&&(c[T++]=(e[b]+s[3])/n[3]);return l==="RGBA"?new Me("float32",c,[1,4,r,i]):new Me("float32",c,[1,3,r,i])},Dd=async(e,t)=>{let r=typeof HTMLImageElement<"u"&&e instanceof HTMLImageElement,i=typeof ImageData<"u"&&e instanceof ImageData,a=typeof ImageBitmap<"u"&&e instanceof ImageBitmap,n=typeof e=="string",s,o=t??{},l=()=>{if(typeof document<"u")return document.createElement("canvas");if(typeof OffscreenCanvas<"u")return new OffscreenCanvas(1,1);throw new Error("Canvas is not supported")},d=c=>typeof HTMLCanvasElement<"u"&&c instanceof HTMLCanvasElement||c instanceof OffscreenCanvas?c.getContext("2d"):null;if(r){let c=l();c.width=e.width,c.height=e.height;let f=d(c);if(f!=null){let m=e.height,y=e.width;if(t!==void 0&&t.resizedHeight!==void 0&&t.resizedWidth!==void 0&&(m=t.resizedHeight,y=t.resizedWidth),t!==void 0){if(o=t,t.tensorFormat!==void 0)throw new Error("Image input config format must be RGBA for HTMLImageElement");o.tensorFormat="RGBA",o.height=m,o.width=y}else o.tensorFormat="RGBA",o.height=m,o.width=y;f.drawImage(e,0,0),s=f.getImageData(0,0,y,m).data}else throw new Error("Can not access image data")}else if(i){let c,f;if(t!==void 0&&t.resizedWidth!==void 0&&t.resizedHeight!==void 0?(c=t.resizedHeight,f=t.resizedWidth):(c=e.height,f=e.width),t!==void 0&&(o=t),o.format="RGBA",o.height=c,o.width=f,t!==void 0){let m=l();m.width=f,m.height=c;let y=d(m);if(y!=null)y.putImageData(e,0,0),s=y.getImageData(0,0,f,c).data;else throw new Error("Can not access image data")}else s=e.data}else if(a){if(t===void 0)throw new Error("Please provide image config with format for Imagebitmap");let c=l();c.width=e.width,c.height=e.height;let f=d(c);if(f!=null){let m=e.height,y=e.width;return f.drawImage(e,0,0,y,m),s=f.getImageData(0,0,y,m).data,o.height=m,o.width=y,Si(s,o)}else throw new Error("Can not access image data")}else{if(n)return new Promise((c,f)=>{let m=l(),y=d(m);if(!e||!y)return f();let _=new Image;_.crossOrigin="Anonymous",_.src=e,_.onload=()=>{m.width=_.width,m.height=_.height,y.drawImage(_,0,0,m.width,m.height);let b=y.getImageData(0,0,m.width,m.height);o.height=m.height,o.width=m.width,c(Si(b.data,o))}});throw new Error("Input data provided is not supported - aborted tensor creation")}if(s!==void 0)return Si(s,o);throw new Error("Input data provided is not supported - aborted tensor creation")},Nd=(e,t)=>{let{width:r,height:i,download:a,dispose:n}=t,s=[1,i,r,4];return new Me({location:"texture",type:"float32",texture:e,dims:s,download:a,dispose:n})},Pd=(e,t)=>{let{dataType:r,dims:i,download:a,dispose:n}=t;return new Me({location:"gpu-buffer",type:r??"float32",gpuBuffer:e,dims:i,download:a,dispose:n})},Ud=(e,t)=>{let{dataType:r,dims:i,download:a,dispose:n}=t;return new Me({location:"ml-tensor",type:r??"float32",mlTensor:e,dims:i,download:a,dispose:n})},Wd=(e,t,r)=>new Me({location:"cpu-pinned",type:e,data:t,dims:r??[t.length]})}),St,oi,Cr,Ld,pg=L(()=>{St=new Map([["float32",Float32Array],["uint8",Uint8Array],["int8",Int8Array],["uint16",Uint16Array],["int16",Int16Array],["int32",Int32Array],["bool",Uint8Array],["float64",Float64Array],["uint32",Uint32Array],["int4",Uint8Array],["uint4",Uint8Array]]),oi=new Map([[Float32Array,"float32"],[Uint8Array,"uint8"],[Int8Array,"int8"],[Uint16Array,"uint16"],[Int16Array,"int16"],[Int32Array,"int32"],[Float64Array,"float64"],[Uint32Array,"uint32"]]),Cr=!1,Ld=()=>{if(!Cr){Cr=!0;let e=typeof BigInt64Array<"u"&&BigInt64Array.from,t=typeof BigUint64Array<"u"&&BigUint64Array.from,r=globalThis.Float16Array,i=typeof r<"u"&&r.from;e&&(St.set("int64",BigInt64Array),oi.set(BigInt64Array,"int64")),t&&(St.set("uint64",BigUint64Array),oi.set(BigUint64Array,"uint64")),i?(St.set("float16",r),oi.set(r,"float16")):St.set("float16",Uint16Array)}}}),qd,Vd,cg=L(()=>{Fa(),qd=e=>{let t=1;for(let r=0;r<e.length;r++){let i=e[r];if(typeof i!="number"||!Number.isSafeInteger(i))throw new TypeError(`dims[${r}] must be an integer, got: ${i}`);if(i<0)throw new RangeError(`dims[${r}] must be a non-negative integer, got: ${i}`);t*=i}return t},Vd=(e,t)=>{switch(e.location){case"cpu":return new Me(e.type,e.data,t);case"cpu-pinned":return new Me({location:"cpu-pinned",data:e.data,type:e.type,dims:t});case"texture":return new Me({location:"texture",texture:e.texture,type:e.type,dims:t});case"gpu-buffer":return new Me({location:"gpu-buffer",gpuBuffer:e.gpuBuffer,type:e.type,dims:t});case"ml-tensor":return new Me({location:"ml-tensor",mlTensor:e.mlTensor,type:e.type,dims:t});default:throw new Error(`tensorReshape: tensor location ${e.location} is not supported`)}}}),Me,Fa=L(()=>{lg(),dg(),pg(),cg(),Me=class{constructor(e,t,r){Ld();let i,a;if(typeof e=="object"&&"location"in e)switch(this.dataLocation=e.location,i=e.type,a=e.dims,e.location){case"cpu-pinned":{let s=St.get(i);if(!s)throw new TypeError(`unsupported type "${i}" to create tensor from pinned buffer`);if(!(e.data instanceof s))throw new TypeError(`buffer should be of type ${s.name}`);this.cpuData=e.data;break}case"texture":{if(i!=="float32")throw new TypeError(`unsupported type "${i}" to create tensor from texture`);this.gpuTextureData=e.texture,this.downloader=e.download,this.disposer=e.dispose;break}case"gpu-buffer":{if(i!=="float32"&&i!=="float16"&&i!=="int32"&&i!=="int64"&&i!=="uint32"&&i!=="uint8"&&i!=="bool"&&i!=="uint4"&&i!=="int4")throw new TypeError(`unsupported type "${i}" to create tensor from gpu buffer`);this.gpuBufferData=e.gpuBuffer,this.downloader=e.download,this.disposer=e.dispose;break}case"ml-tensor":{if(i!=="float32"&&i!=="float16"&&i!=="int32"&&i!=="int64"&&i!=="uint32"&&i!=="uint64"&&i!=="int8"&&i!=="uint8"&&i!=="bool"&&i!=="uint4"&&i!=="int4")throw new TypeError(`unsupported type "${i}" to create tensor from MLTensor`);this.mlTensorData=e.mlTensor,this.downloader=e.download,this.disposer=e.dispose;break}default:throw new Error(`Tensor constructor: unsupported location '${this.dataLocation}'`)}else{let s,o;if(typeof e=="string")if(i=e,o=r,e==="string"){if(!Array.isArray(t))throw new TypeError("A string tensor's data must be a string array.");s=t}else{let l=St.get(e);if(l===void 0)throw new TypeError(`Unsupported tensor type: ${e}.`);if(Array.isArray(t)){if(e==="float16"&&l===Uint16Array||e==="uint4"||e==="int4")throw new TypeError(`Creating a ${e} tensor from number array is not supported. Please use ${l.name} as data.`);e==="uint64"||e==="int64"?s=l.from(t,BigInt):s=l.from(t)}else if(t instanceof l)s=t;else if(t instanceof Uint8ClampedArray)if(e==="uint8")s=Uint8Array.from(t);else throw new TypeError("A Uint8ClampedArray tensor's data must be type of uint8");else if(e==="float16"&&t instanceof Uint16Array&&l!==Uint16Array)s=new globalThis.Float16Array(t.buffer,t.byteOffset,t.length);else throw new TypeError(`A ${i} tensor's data must be type of ${l}`)}else if(o=t,Array.isArray(e)){if(e.length===0)throw new TypeError("Tensor type cannot be inferred from an empty array.");let l=typeof e[0];if(l==="string")i="string",s=e;else if(l==="boolean")i="bool",s=Uint8Array.from(e);else throw new TypeError(`Invalid element type of data array: ${l}.`)}else if(e instanceof Uint8ClampedArray)i="uint8",s=Uint8Array.from(e);else{let l=oi.get(e.constructor);if(l===void 0)throw new TypeError(`Unsupported type for tensor data: ${e.constructor}.`);i=l,s=e}if(o===void 0)o=[s.length];else if(!Array.isArray(o))throw new TypeError("A tensor's dims must be a number array");a=o,this.cpuData=s,this.dataLocation="cpu"}let n=qd(a);if(this.cpuData&&n!==this.cpuData.length&&!((i==="uint4"||i==="int4")&&Math.ceil(n/2)===this.cpuData.length))throw new Error(`Tensor's size(${n}) does not match data length(${this.cpuData.length}).`);this.type=i,this.dims=a,this.size=n}static async fromImage(e,t){return Dd(e,t)}static fromTexture(e,t){return Nd(e,t)}static fromGpuBuffer(e,t){return Pd(e,t)}static fromMLTensor(e,t){return Ud(e,t)}static fromPinnedBuffer(e,t,r){return Wd(e,t,r)}toDataURL(e){return Bd(this,e)}toImageData(e){return Md(this,e)}get data(){if(this.ensureValid(),!this.cpuData)throw new Error("The data is not on CPU. Use `getData()` to download GPU data to CPU, or use `texture` or `gpuBuffer` property to access the GPU data directly.");return this.cpuData}get location(){return this.dataLocation}get texture(){if(this.ensureValid(),!this.gpuTextureData)throw new Error("The data is not stored as a WebGL texture.");return this.gpuTextureData}get gpuBuffer(){if(this.ensureValid(),!this.gpuBufferData)throw new Error("The data is not stored as a WebGPU buffer.");return this.gpuBufferData}get mlTensor(){if(this.ensureValid(),!this.mlTensorData)throw new Error("The data is not stored as a WebNN MLTensor.");return this.mlTensorData}async getData(e){switch(this.ensureValid(),this.dataLocation){case"cpu":case"cpu-pinned":return this.data;case"texture":case"gpu-buffer":case"ml-tensor":{if(!this.downloader)throw new Error("The current tensor is not created with a specified data downloader.");if(this.isDownloading)throw new Error("The current tensor is being downloaded.");try{this.isDownloading=!0;let t=await this.downloader();return this.downloader=void 0,this.dataLocation="cpu",this.cpuData=t,e&&this.disposer&&(this.disposer(),this.disposer=void 0),t}finally{this.isDownloading=!1}}default:throw new Error(`cannot get data from location: ${this.dataLocation}`)}}dispose(){if(this.isDownloading)throw new Error("The current tensor is being downloaded.");this.disposer&&(this.disposer(),this.disposer=void 0),this.cpuData=void 0,this.gpuTextureData=void 0,this.gpuBufferData=void 0,this.mlTensorData=void 0,this.downloader=void 0,this.isDownloading=void 0,this.dataLocation="none"}ensureValid(){if(this.dataLocation==="none")throw new Error("The tensor is disposed.")}reshape(e){if(this.ensureValid(),this.downloader||this.disposer)throw new Error("Cannot reshape a tensor that owns GPU resource.");return Vd(this,e)}}}),He,jd=L(()=>{Fa(),He=Me}),fi,Tr,Ke,Ue,mt,gt,Fd=L(()=>{Rd(),fi=(e,t)=>{(typeof ke.trace>"u"?!ke.wasm.trace:!ke.trace)||console.timeStamp(`${e}::ORT::${t}`)},Tr=(e,t)=>{let r=new Error().stack?.split(/\r\n|\r|\n/g)||[],i=!1;for(let a=0;a<r.length;a++){if(i&&!r[a].includes("TRACE_FUNC")){let n=`FUNC_${e}::${r[a].trim().split(" ")[1]}`;t&&(n+=`::${t}`),fi("CPU",n);return}r[a].includes("TRACE_FUNC")&&(i=!0)}},Ke=e=>{(typeof ke.trace>"u"?!ke.wasm.trace:!ke.trace)||Tr("BEGIN",e)},Ue=e=>{(typeof ke.trace>"u"?!ke.wasm.trace:!ke.trace)||Tr("END",e)},mt=e=>{(typeof ke.trace>"u"?!ke.wasm.trace:!ke.trace)||console.time(`ORT::${e}`)},gt=e=>{(typeof ke.trace>"u"?!ke.wasm.trace:!ke.trace)||console.timeEnd(`ORT::${e}`)}}),Gd,fg=L(()=>{Ad(),jd(),Fd(),Gd=class Hd{constructor(t){this.handler=t}async run(t,r,i){Ke(),mt("InferenceSession.run");let a={},n={};if(typeof t!="object"||t===null||t instanceof He||Array.isArray(t))throw new TypeError("'feeds' must be an object that use input names as keys and OnnxValue as corresponding values.");let s=!0;if(typeof r=="object"){if(r===null)throw new TypeError("Unexpected argument[1]: cannot be null.");if(r instanceof He)throw new TypeError("'fetches' cannot be a Tensor");if(Array.isArray(r)){if(r.length===0)throw new TypeError("'fetches' cannot be an empty array.");s=!1;for(let d of r){if(typeof d!="string")throw new TypeError("'fetches' must be a string array or an object.");if(this.outputNames.indexOf(d)===-1)throw new RangeError(`'fetches' contains invalid output name: ${d}.`);a[d]=null}if(typeof i=="object"&&i!==null)n=i;else if(typeof i<"u")throw new TypeError("'options' must be an object.")}else{let d=!1,c=Object.getOwnPropertyNames(r);for(let f of this.outputNames)if(c.indexOf(f)!==-1){let m=r[f];(m===null||m instanceof He)&&(d=!0,s=!1,a[f]=m)}if(d){if(typeof i=="object"&&i!==null)n=i;else if(typeof i<"u")throw new TypeError("'options' must be an object.")}else n=r}}else if(typeof r<"u")throw new TypeError("Unexpected argument[1]: must be 'fetches' or 'options'.");for(let d of this.inputNames)if(typeof t[d]>"u")throw new Error(`input '${d}' is missing in 'feeds'.`);if(s)for(let d of this.outputNames)a[d]=null;let o=await this.handler.run(t,a,n),l={};for(let d in o)if(Object.hasOwnProperty.call(o,d)){let c=o[d];c instanceof He?l[d]=c:l[d]=new He(c.type,c.data,c.dims)}return gt("InferenceSession.run"),Ue(),l}async release(){return this.handler.dispose()}static async create(t,r,i,a){Ke(),mt("InferenceSession.create");let n,s={};if(typeof t=="string"){if(n=t,typeof r=="object"&&r!==null)s=r;else if(typeof r<"u")throw new TypeError("'options' must be an object.")}else if(t instanceof Uint8Array){if(n=t,typeof r=="object"&&r!==null)s=r;else if(typeof r<"u")throw new TypeError("'options' must be an object.")}else if(t instanceof ArrayBuffer||typeof SharedArrayBuffer<"u"&&t instanceof SharedArrayBuffer){let c=t,f=0,m=t.byteLength;if(typeof r=="object"&&r!==null)s=r;else if(typeof r=="number"){if(f=r,!Number.isSafeInteger(f))throw new RangeError("'byteOffset' must be an integer.");if(f<0||f>=c.byteLength)throw new RangeError(`'byteOffset' is out of range [0, ${c.byteLength}).`);if(m=t.byteLength-f,typeof i=="number"){if(m=i,!Number.isSafeInteger(m))throw new RangeError("'byteLength' must be an integer.");if(m<=0||f+m>c.byteLength)throw new RangeError(`'byteLength' is out of range (0, ${c.byteLength-f}].`);if(typeof a=="object"&&a!==null)s=a;else if(typeof a<"u")throw new TypeError("'options' must be an object.")}else if(typeof i<"u")throw new TypeError("'byteLength' must be a number.")}else if(typeof r<"u")throw new TypeError("'options' must be an object.");n=new Uint8Array(c,f,m)}else throw new TypeError("Unexpected argument[0]: must be 'path' or 'buffer'.");let[o,l]=await zd(s),d=await o.createInferenceSessionHandler(n,l);return gt("InferenceSession.create"),Ue(),new Hd(d)}startProfiling(){this.handler.startProfiling()}endProfiling(){this.handler.endProfiling()}get inputNames(){return this.handler.inputNames}get outputNames(){return this.handler.outputNames}get inputMetadata(){return this.handler.inputMetadata}get outputMetadata(){return this.handler.outputMetadata}}}),Ga,hg=L(()=>{fg(),Ga=Gd}),mg=L(()=>{}),gg=L(()=>{}),yg=L(()=>{}),_g=L(()=>{}),Kd={};qt(Kd,{InferenceSession:()=>Ga,TRACE:()=>fi,TRACE_EVENT_BEGIN:()=>mt,TRACE_EVENT_END:()=>gt,TRACE_FUNC_BEGIN:()=>Ke,TRACE_FUNC_END:()=>Ue,Tensor:()=>He,env:()=>ge,registerBackend:()=>Et});var We=L(()=>{sg(),ug(),hg(),jd(),mg(),gg(),Fd(),yg(),_g()}),Ha=L(()=>{}),Yd={};qt(Yd,{default:()=>Zd});var Sr,Ir,Zd,bg=L(()=>{ih(),Rt(),Ka(),Sr="ort-wasm-proxy-worker",Ir=globalThis.self?.name===Sr,Ir&&(self.onmessage=e=>{let{type:t,in:r}=e.data;try{switch(t){case"init-wasm":Ya(r.wasm).then(()=>{fn(r).then(()=>{postMessage({type:t})},i=>{postMessage({type:t,err:i})})},i=>{postMessage({type:t,err:i})});break;case"init-ep":{let{epName:i,env:a}=r;hn(a,i).then(()=>{postMessage({type:t})},n=>{postMessage({type:t,err:n})});break}case"copy-from":{let{buffer:i}=r,a=Yi(i);postMessage({type:t,out:a});break}case"create":{let{model:i,options:a}=r;mn(i,a).then(n=>{postMessage({type:t,out:n})},n=>{postMessage({type:t,err:n})});break}case"release":gn(r),postMessage({type:t});break;case"run":{let{sessionId:i,inputIndices:a,inputs:n,outputIndices:s,options:o}=r;yn(i,a,n,s,new Array(s.length).fill(null),o).then(l=>{l.some(d=>d[3]!=="cpu")?postMessage({type:t,err:"Proxy does not support non-cpu tensor location."}):postMessage({type:t,out:l},bn([...n,...l]))},l=>{postMessage({type:t,err:l})});break}case"end-profiling":_n(r),postMessage({type:t});break;default:}}catch(i){postMessage({type:t,err:i})}}),Zd=Ir?null:e=>new Worker(e??Be,{type:"module",name:Sr})}),Xd={};qt(Xd,{default:()=>Qd});var kr,Qd,Ds,wg=L(()=>{kr=async function(e={}){var t,r,i=e,a=new Promise((u,p)=>{t=u,r=p}),n=typeof window=="object",s=typeof WorkerGlobalScope<"u",o=s&&self.name?.startsWith("em-pthread");i.mountExternalData=(u,p)=>{u.startsWith("./")&&(u=u.substring(2)),(i.Fb||(i.Fb=new Map)).set(u,p)},i.unmountExternalData=()=>{delete i.Fb};var l=globalThis.SharedArrayBuffer??new WebAssembly.Memory({initial:0,maximum:0,qc:!0}).buffer.constructor;let d=u=>async(...p)=>{try{if(i.Gb)throw Error("Session already started");let h=i.Gb={ec:p[0],errors:[]},g=await u(...p);if(i.Gb!==h)throw Error("Session mismatch");i.Kb?.flush();let v=h.errors;if(0<v.length){let S=await Promise.all(v);if(S=S.filter(E=>E),0<S.length)throw Error(S.join(`
`))}return g}finally{i.Gb=null}};i.jsepInit=(u,p)=>{if(u==="webgpu"){[i.Kb,i.Vb,i.Zb,i.Lb,i.Yb,i.Ab,i.$b,i.bc,i.Wb,i.Xb,i.ac]=p;let h=i.Kb;i.jsepRegisterBuffer=(g,v,S,E)=>h.registerBuffer(g,v,S,E),i.jsepGetBuffer=g=>h.getBuffer(g),i.jsepCreateDownloader=(g,v,S)=>h.createDownloader(g,v,S),i.jsepOnCreateSession=g=>{h.onCreateSession(g)},i.jsepOnReleaseSession=g=>{h.onReleaseSession(g)},i.jsepOnRunStart=g=>h.onRunStart(g),i.cc=(g,v)=>{h.upload(g,v)}}else if(u==="webnn"){let h=p[0];[i.oc,i.Ob,i.webnnEnsureTensor,i.Pb,i.webnnDownloadTensor,i.nc,i.webnnEnableTraceEvent]=p.slice(1),i.webnnReleaseTensorId=i.Ob,i.webnnUploadTensor=i.Pb,i.webnnRegisterMLContext=i.nc,i.webnnOnRunStart=g=>h.onRunStart(g),i.webnnOnRunEnd=h.onRunEnd.bind(h),i.webnnOnReleaseSession=g=>{h.onReleaseSession(g)},i.webnnCreateMLTensorDownloader=(g,v)=>h.createMLTensorDownloader(g,v),i.webnnRegisterMLTensor=(g,v,S,E)=>h.registerMLTensor(g,v,S,E),i.webnnCreateMLContext=g=>h.createMLContext(g),i.webnnRegisterMLConstant=(g,v,S,E,R,P)=>h.registerMLConstant(g,v,S,E,R,i.Fb,P),i.webnnRegisterGraphInput=h.registerGraphInput.bind(h),i.webnnIsGraphInput=h.isGraphInput.bind(h),i.webnnRegisterGraphOutput=h.registerGraphOutput.bind(h),i.webnnIsGraphOutput=h.isGraphOutput.bind(h),i.webnnCreateTemporaryTensor=h.createTemporaryTensor.bind(h),i.webnnIsGraphInputOutputTypeSupported=h.isGraphInputOutputTypeSupported.bind(h)}};let c=()=>{let u=(p,h,g)=>(...v)=>{let S=Xe,E=h?.();v=p(...v);let R=h?.();return E!==R&&(p=R,g(E),h=g=null),Xe!=S?new Promise((P,q)=>{cr={resolve:P,reject:q}}):v};(()=>{for(let p of["_OrtAppendExecutionProvider","_OrtCreateSession","_OrtRun","_OrtRunWithBinding","_OrtBindInput"])i[p]=u(i[p],()=>i[p],h=>i[p]=h)})(),d!==void 0&&(i._OrtRun=d(i._OrtRun),i._OrtRunWithBinding=d(i._OrtRunWithBinding)),c=void 0};i.asyncInit=()=>{c?.()};var f,m,y=(u,p)=>{throw p},_=import.meta.url,b="";if(n||s){try{b=new URL(".",_).href}catch{}s&&(m=u=>{var p=new XMLHttpRequest;return p.open("GET",u,!1),p.responseType="arraybuffer",p.send(null),new Uint8Array(p.response)}),f=async u=>{if(ye(u))return new Promise((h,g)=>{var v=new XMLHttpRequest;v.open("GET",u,!0),v.responseType="arraybuffer",v.onload=()=>{v.status==200||v.status==0&&v.response?h(v.response):g(v.status)},v.onerror=g,v.send(null)});var p=await fetch(u,{credentials:"same-origin"});if(p.ok)return p.arrayBuffer();throw Error(p.status+" : "+p.url)}}var x,$,w,T,C,I,z,k,A,D,V,G,H,F,W,re=console.log.bind(console),ee=console.error.bind(console),K=re,ne=ee,Y=!1,ye=u=>u.startsWith("file://");function U(){return $.buffer!=C.buffer&&Ce(),C}function j(){return $.buffer!=C.buffer&&Ce(),I}function ae(){return $.buffer!=C.buffer&&Ce(),z}function pe(){return $.buffer!=C.buffer&&Ce(),k}function N(){return $.buffer!=C.buffer&&Ce(),A}function le(){return $.buffer!=C.buffer&&Ce(),D}function Ye(){return $.buffer!=C.buffer&&Ce(),V}function be(){return $.buffer!=C.buffer&&Ce(),F}if(o){let u=function(p){try{var h=p.data,g=h.Db;if(g==="load"){let v=[];self.onmessage=S=>v.push(S),self.startWorker=()=>{postMessage({Db:"loaded"});for(let S of v)u(S);self.onmessage=u};for(let S of h.Sb)i[S]&&!i[S].proxy||(i[S]=(...E)=>{postMessage({Db:"callHandler",Rb:S,args:E})},S=="print"&&(K=i[S]),S=="printErr"&&(ne=i[S]));$=h.kc,Ce(),W(h.lc)}else if(g==="run"){zh(h.Bb),_r(h.Bb,0,0,1,0,0),An(),dr(h.Bb),we||(xs(),we=!0);try{Ah(h.hc,h.Jb)}catch(v){if(v!="unwind")throw v}}else h.target!=="setimmediate"&&(g==="checkMailbox"?we&&gi():g&&(ne(`worker: received unknown command ${g}`),ne(h)))}catch(v){throw Cs(),v}};var we=!1;self.onunhandledrejection=p=>{throw p.reason||p},self.onmessage=u}function Ce(){var u=$.buffer;i.HEAP8=C=new Int8Array(u),z=new Int16Array(u),i.HEAPU8=I=new Uint8Array(u),k=new Uint16Array(u),i.HEAP32=A=new Int32Array(u),i.HEAPU32=D=new Uint32Array(u),V=new Float32Array(u),F=new Float64Array(u),G=new BigInt64Array(u),H=new BigUint64Array(u)}function mi(){o?startWorker(i):B.Da()}var Vt,jt=0,Ft=null;function Cn(){if(--jt==0&&Ft){var u=Ft;Ft=null,u()}}function ot(u){throw ne(u="Aborted("+u+")"),Y=!0,u=new WebAssembly.RuntimeError(u+". Build with -sASSERTIONS for more info."),r(u),u}function Tn(){return{a:{L:Km,Aa:Hm,b:Rh,$:Mn,A:Pn,pa:Un,X:Wn,Z:Ln,qa:qn,na:Vn,ga:jn,ma:Fn,J:Gn,Y:Hn,V:Kn,oa:Yn,W:Zn,va:Bh,E:Mh,Q:Dh,O:Ph,D:Wh,v:Lh,s:qh,P:Vh,z:Zh,R:Xh,ja:Qh,T:Jh,aa:em,M:tm,F:im,ia:dr,sa:rm,r:am,Ca:nm,w:um,o:lm,m:pm,c:sr,Ba:cm,n:fm,j:gm,u:ym,p:_m,f:bm,t:wm,l:vm,e:$m,k:xm,h:Cm,g:Tm,d:Sm,da:Im,ea:km,fa:Em,ba:ls,ca:ds,N:ps,xa:Am,ua:Rm,i:Bm,C:Mm,G:Dm,ta:Om,x:Nm,ra:Pm,U:Um,q:zm,y:Wm,K:Lm,S:qm,za:Vm,ya:jm,ka:ms,la:gs,_:ir,B:ys,I:_s,ha:bs,H:ws,a:$,wa:tr}}}class Ji{name="ExitStatus";constructor(p){this.message=`Program terminated with exit(${p})`,this.status=p}}var Sn=u=>{u.terminate(),u.onmessage=()=>{}},er=[],In=u=>{lt.length==0&&(Rn(),On(lt[0]));var p=lt.pop();if(!p)return 6;Gt.push(p),bt[u.Bb]=p,p.Bb=u.Bb;var h={Db:"run",hc:u.fc,Jb:u.Jb,Bb:u.Bb};return p.postMessage(h,u.Nb),0},ut=0,ve=(u,p,...h)=>{for(var g=2*h.length,v=vr(),S=wr(8*g),E=S>>>3,R=0;R<h.length;R++){var P=h[R];typeof P=="bigint"?(G[E+2*R]=1n,G[E+2*R+1]=P):(G[E+2*R]=0n,be()[E+2*R+1>>>0]=P)}return u=Ts(u,0,g,S,p),Ti(v),u};function tr(u){if(o)return ve(0,1,u);if(T=u,!(0<ut)){for(var p of Gt)Sn(p);for(p of lt)Sn(p);lt=[],Gt=[],bt={},Y=!0}y(0,new Ji(u))}function kn(u){if(o)return ve(1,0,u);ir(u)}var ir=u=>{if(T=u,o)throw kn(u),"unwind";tr(u)},lt=[],Gt=[],En=[],bt={},zn=u=>{var p=u.Bb;delete bt[p],lt.push(u),Gt.splice(Gt.indexOf(u),1),u.Bb=0,Ss(p)};function An(){En.forEach(u=>u())}var On=u=>new Promise(p=>{u.onmessage=v=>{var S=(v=v.data).Db;if(v.Hb&&v.Hb!=yr()){var E=bt[v.Hb];E?E.postMessage(v,v.Nb):ne(`Internal error! Worker sent a message "${S}" to target pthread ${v.Hb}, but that thread no longer exists!`)}else S==="checkMailbox"?gi():S==="spawnThread"?In(v):S==="cleanupThread"?zn(bt[v.ic]):S==="loaded"?(u.loaded=!0,p(u)):v.target==="setimmediate"?u.postMessage(v):S==="callHandler"?i[v.Rb](...v.args):S&&ne(`worker sent an unknown command ${S}`)},u.onerror=v=>{throw ne(`worker sent an error! ${v.filename}:${v.lineno}: ${v.message}`),v};var h,g=[];for(h of[])i.propertyIsEnumerable(h)&&g.push(h);u.postMessage({Db:"load",Sb:g,kc:$,lc:w})});function Rn(){var u=new Worker((()=>{let p=URL;return import.meta.url>"file:"&&import.meta.url<"file;"?new p("ort.bundle.min.mjs",import.meta.url):new URL(import.meta.url)})(),{type:"module",workerData:"em-pthread",name:"em-pthread"});lt.push(u)}var zh=u=>{Ce();var p=le()[u+52>>>2>>>0];u=le()[u+56>>>2>>>0],Es(p,p-u),Ti(p)},Ah=(u,p)=>{ut=0,u=zs(u,p),0<ut?T=u:br(u)};class Oh{constructor(p){this.Ib=p-24}}function Rh(u,p,h){var g=new Oh(u>>>=0);throw p>>>=0,h>>>=0,le()[g.Ib+16>>>2>>>0]=0,le()[g.Ib+4>>>2>>>0]=p,le()[g.Ib+8>>>2>>>0]=h,u}function Bn(u,p,h,g){return o?ve(2,1,u,p,h,g):Mn(u,p,h,g)}function Mn(u,p,h,g){if(u>>>=0,h>>>=0,g>>>=0,l===void 0)return 6;var v=[];return o&&v.length===0?Bn(u,p>>>=0,h,g):(u={fc:h,Bb:u,Jb:g,Nb:v},o?(u.Db="spawnThread",postMessage(u,v),0):In(u))}var Dn=typeof TextDecoder<"u"?new TextDecoder:void 0,Nn=(u,p=0,h=NaN)=>{var g=(p>>>=0)+h;for(h=p;u[h]&&!(h>=g);)++h;if(16<h-p&&u.buffer&&Dn)return Dn.decode(u.buffer instanceof ArrayBuffer?u.subarray(p,h):u.slice(p,h));for(g="";p<h;){var v=u[p++];if(128&v){var S=63&u[p++];if((224&v)==192)g+=String.fromCharCode((31&v)<<6|S);else{var E=63&u[p++];65536>(v=(240&v)==224?(15&v)<<12|S<<6|E:(7&v)<<18|S<<12|E<<6|63&u[p++])?g+=String.fromCharCode(v):(v-=65536,g+=String.fromCharCode(55296|v>>10,56320|1023&v))}}else g+=String.fromCharCode(v)}return g},Te=(u,p)=>(u>>>=0)?Nn(j(),u,p):"";function Pn(u,p,h){return o?ve(3,1,u,p,h):0}function Un(u,p){if(o)return ve(4,1,u,p)}function Wn(u,p){if(o)return ve(5,1,u,p)}function Ln(u,p,h){if(o)return ve(6,1,u,p,h)}function qn(u,p,h){return o?ve(7,1,u,p,h):0}function Vn(u,p){if(o)return ve(8,1,u,p)}function jn(u,p,h){if(o)return ve(9,1,u,p,h)}function Fn(u,p,h,g){if(o)return ve(10,1,u,p,h,g)}function Gn(u,p,h,g){if(o)return ve(11,1,u,p,h,g)}function Hn(u,p,h,g){if(o)return ve(12,1,u,p,h,g)}function Kn(u){if(o)return ve(13,1,u)}function Yn(u,p){if(o)return ve(14,1,u,p)}function Zn(u,p,h){if(o)return ve(15,1,u,p,h)}var Xn,Bh=()=>ot(""),Ze=u=>{for(var p="";j()[u>>>0];)p+=Xn[j()[u++>>>0]];return p},rr={},ar={},Mt=i.BindingError=class extends Error{constructor(u){super(u),this.name="BindingError"}};function et(u,p,h={}){return(function(g,v,S={}){var E=v.name;if(!g)throw new Mt(`type "${E}" must have a positive integer typeid pointer`);if(ar.hasOwnProperty(g)){if(S.Tb)return;throw new Mt(`Cannot register type '${E}' twice`)}ar[g]=v,rr.hasOwnProperty(g)&&(v=rr[g],delete rr[g],v.forEach(R=>R()))})(u,p,h)}var Qn=(u,p,h)=>{switch(p){case 1:return h?g=>U()[g>>>0]:g=>j()[g>>>0];case 2:return h?g=>ae()[g>>>1>>>0]:g=>pe()[g>>>1>>>0];case 4:return h?g=>N()[g>>>2>>>0]:g=>le()[g>>>2>>>0];case 8:return h?g=>G[g>>>3]:g=>H[g>>>3];default:throw new TypeError(`invalid integer width (${p}): ${u}`)}};function Mh(u,p,h){h>>>=0,et(u>>>=0,{name:p=Ze(p>>>0),fromWireType:g=>g,toWireType:function(g,v){if(typeof v!="bigint"&&typeof v!="number")throw v=v===null?"null":(g=typeof v)=="object"||g==="array"||g==="function"?v.toString():""+v,new TypeError(`Cannot convert "${v}" to ${this.name}`);return typeof v=="number"&&(v=BigInt(v)),v},Cb:dt,readValueFromPointer:Qn(p,h,p.indexOf("u")==-1),Eb:null})}var dt=8;function Dh(u,p,h,g){et(u>>>=0,{name:p=Ze(p>>>0),fromWireType:function(v){return!!v},toWireType:function(v,S){return S?h:g},Cb:dt,readValueFromPointer:function(v){return this.fromWireType(j()[v>>>0])},Eb:null})}var nr=[],tt=[];function sr(u){9<(u>>>=0)&&--tt[u+1]==0&&(tt[u]=void 0,nr.push(u))}var Ae=u=>{if(!u)throw new Mt(`Cannot use deleted val. handle = ${u}`);return tt[u]},Ne=u=>{switch(u){case void 0:return 2;case null:return 4;case!0:return 6;case!1:return 8;default:let p=nr.pop()||tt.length;return tt[p]=u,tt[p+1]=1,p}};function or(u){return this.fromWireType(le()[u>>>2>>>0])}var Nh={name:"emscripten::val",fromWireType:u=>{var p=Ae(u);return sr(u),p},toWireType:(u,p)=>Ne(p),Cb:dt,readValueFromPointer:or,Eb:null};function Ph(u){return et(u>>>0,Nh)}var Uh=(u,p)=>{switch(p){case 4:return function(h){return this.fromWireType(Ye()[h>>>2>>>0])};case 8:return function(h){return this.fromWireType(be()[h>>>3>>>0])};default:throw new TypeError(`invalid float width (${p}): ${u}`)}};function Wh(u,p,h){h>>>=0,et(u>>>=0,{name:p=Ze(p>>>0),fromWireType:g=>g,toWireType:(g,v)=>v,Cb:dt,readValueFromPointer:Uh(p,h),Eb:null})}function Lh(u,p,h,g,v){if(u>>>=0,h>>>=0,p=Ze(p>>>0),v===-1&&(v=4294967295),v=R=>R,g===0){var S=32-8*h;v=R=>R<<S>>>S}var E=p.includes("unsigned")?function(R,P){return P>>>0}:function(R,P){return P};et(u,{name:p,fromWireType:v,toWireType:E,Cb:dt,readValueFromPointer:Qn(p,h,g!==0),Eb:null})}function qh(u,p,h){function g(S){var E=le()[S>>>2>>>0];return S=le()[S+4>>>2>>>0],new v(U().buffer,S,E)}var v=[Int8Array,Uint8Array,Int16Array,Uint16Array,Int32Array,Uint32Array,Float32Array,Float64Array,BigInt64Array,BigUint64Array][p];et(u>>>=0,{name:h=Ze(h>>>0),fromWireType:g,Cb:dt,readValueFromPointer:g},{Tb:!0})}var wt=(u,p,h)=>{var g=j();if(p>>>=0,0<h){var v=p;h=p+h-1;for(var S=0;S<u.length;++S){var E=u.charCodeAt(S);if(55296<=E&&57343>=E&&(E=65536+((1023&E)<<10)|1023&u.charCodeAt(++S)),127>=E){if(p>=h)break;g[p++>>>0]=E}else{if(2047>=E){if(p+1>=h)break;g[p++>>>0]=192|E>>6}else{if(65535>=E){if(p+2>=h)break;g[p++>>>0]=224|E>>12}else{if(p+3>=h)break;g[p++>>>0]=240|E>>18,g[p++>>>0]=128|E>>12&63}g[p++>>>0]=128|E>>6&63}g[p++>>>0]=128|63&E}}g[p>>>0]=0,u=p-v}else u=0;return u},ur=u=>{for(var p=0,h=0;h<u.length;++h){var g=u.charCodeAt(h);127>=g?p++:2047>=g?p+=2:55296<=g&&57343>=g?(p+=4,++h):p+=3}return p};function Vh(u,p){et(u>>>=0,{name:p=Ze(p>>>0),fromWireType:function(h){for(var g,v=le()[h>>>2>>>0],S=h+4,E=S,R=0;R<=v;++R){var P=S+R;R!=v&&j()[P>>>0]!=0||(E=Te(E,P-E),g===void 0?g=E:(g+="\0",g+=E),E=P+1)}return it(h),g},toWireType:function(h,g){g instanceof ArrayBuffer&&(g=new Uint8Array(g));var v=typeof g=="string";if(!(v||ArrayBuffer.isView(g)&&g.BYTES_PER_ELEMENT==1))throw new Mt("Cannot pass non-string to std::string");var S=v?ur(g):g.length,E=Ci(4+S+1),R=E+4;return le()[E>>>2>>>0]=S,v?wt(g,R,S+1):j().set(g,R>>>0),h!==null&&h.push(it,E),E},Cb:dt,readValueFromPointer:or,Eb(h){it(h)}})}var Jn=typeof TextDecoder<"u"?new TextDecoder("utf-16le"):void 0,jh=(u,p)=>{for(var h=u>>1,g=h+p/2;!(h>=g)&&pe()[h>>>0];)++h;if(32<(h<<=1)-u&&Jn)return Jn.decode(j().slice(u,h));for(h="",g=0;!(g>=p/2);++g){var v=ae()[u+2*g>>>1>>>0];if(v==0)break;h+=String.fromCharCode(v)}return h},Fh=(u,p,h)=>{if(h??=2147483647,2>h)return 0;var g=p;h=(h-=2)<2*u.length?h/2:u.length;for(var v=0;v<h;++v){var S=u.charCodeAt(v);ae()[p>>>1>>>0]=S,p+=2}return ae()[p>>>1>>>0]=0,p-g},Gh=u=>2*u.length,Hh=(u,p)=>{for(var h=0,g="";!(h>=p/4);){var v=N()[u+4*h>>>2>>>0];if(v==0)break;++h,65536<=v?(v-=65536,g+=String.fromCharCode(55296|v>>10,56320|1023&v)):g+=String.fromCharCode(v)}return g},Kh=(u,p,h)=>{if(p>>>=0,h??=2147483647,4>h)return 0;var g=p;h=g+h-4;for(var v=0;v<u.length;++v){var S=u.charCodeAt(v);if(55296<=S&&57343>=S&&(S=65536+((1023&S)<<10)|1023&u.charCodeAt(++v)),N()[p>>>2>>>0]=S,(p+=4)+4>h)break}return N()[p>>>2>>>0]=0,p-g},Yh=u=>{for(var p=0,h=0;h<u.length;++h){var g=u.charCodeAt(h);55296<=g&&57343>=g&&++h,p+=4}return p};function Zh(u,p,h){if(u>>>=0,p>>>=0,h=Ze(h>>>=0),p===2)var g=jh,v=Fh,S=Gh,E=R=>pe()[R>>>1>>>0];else p===4&&(g=Hh,v=Kh,S=Yh,E=R=>le()[R>>>2>>>0]);et(u,{name:h,fromWireType:R=>{for(var P,q=le()[R>>>2>>>0],Z=R+4,te=0;te<=q;++te){var ue=R+4+te*p;te!=q&&E(ue)!=0||(Z=g(Z,ue-Z),P===void 0?P=Z:(P+="\0",P+=Z),Z=ue+p)}return it(R),P},toWireType:(R,P)=>{if(typeof P!="string")throw new Mt(`Cannot pass non-string to C++ string type ${h}`);var q=S(P),Z=Ci(4+q+p);return le()[Z>>>2>>>0]=q/p,v(P,Z+4,q+p),R!==null&&R.push(it,Z),Z},Cb:dt,readValueFromPointer:or,Eb(R){it(R)}})}function Xh(u,p){et(u>>>=0,{Ub:!0,name:p=Ze(p>>>0),Cb:0,fromWireType:()=>{},toWireType:()=>{}})}function Qh(u){_r(u>>>0,!s,1,!n,131072,!1),An()}var lr=u=>{if(!Y)try{if(u(),!(0<ut))try{o?br(T):ir(T)}catch(p){p instanceof Ji||p=="unwind"||y(0,p)}}catch(p){p instanceof Ji||p=="unwind"||y(0,p)}};function dr(u){u>>>=0,typeof Atomics.jc=="function"&&(Atomics.jc(N(),u>>>2,u).value.then(gi),u+=128,Atomics.store(N(),u>>>2,1))}var gi=()=>{var u=yr();u&&(dr(u),lr(ks))};function Jh(u,p){(u>>>=0)==p>>>0?setTimeout(gi):o?postMessage({Hb:u,Db:"checkMailbox"}):(u=bt[u])&&u.postMessage({Db:"checkMailbox"})}var pr=[];function em(u,p,h,g,v){for(p>>>=0,g/=2,pr.length=g,h=v>>>0>>>3,v=0;v<g;v++)pr[v]=G[h+2*v]?G[h+2*v+1]:be()[h+2*v+1>>>0];return(p?gr[p]:Gm[u])(...pr)}var tm=()=>{ut=0};function im(u){u>>>=0,o?postMessage({Db:"cleanupThread",ic:u}):zn(bt[u])}function rm(u){}var yi=(u,p)=>{var h=ar[u];if(h===void 0)throw u=$s(u),h=Ze(u),it(u),new Mt(`${p} has unknown type ${h}`);return h},es=(u,p,h)=>{var g=[];return u=u.toWireType(g,h),g.length&&(le()[p>>>2>>>0]=Ne(g)),u};function am(u,p,h){return p>>>=0,h>>>=0,u=Ae(u>>>0),p=yi(p,"emval::as"),es(p,h,u)}function nm(u,p){return p>>>=0,u=Ae(u>>>0),(p=yi(p,"emval::as")).toWireType(null,u)}var _i=u=>{try{u()}catch(p){ot(p)}},pt=0,Xe=null,ts=0,bi=[],is={},rs={},sm=0,cr=null,om=[];function as(u){return(function(p){if(!Y){if(pt===0){var h=!1,g=!1;p((v=0)=>{if(!Y&&(ts=v,h=!0,g)){pt=2,_i(()=>Rs(Xe)),typeof MainLoop<"u"&&MainLoop.Qb&&MainLoop.resume(),v=!1;try{var S=(function(){var P=N()[Xe+8>>>2>>>0];return P=B[rs[P]],--ut,P()})()}catch(P){S=P,v=!0}var E=!1;if(!Xe){var R=cr;R&&(cr=null,(v?R.reject:R.resolve)(S),E=!0)}if(v&&!E)throw S}}),g=!0,h||(pt=1,Xe=(function(){var v=Ci(65548),S=v+12;le()[v>>>2>>>0]=S,le()[v+4>>>2>>>0]=S+65536,S=bi[0];var E=is[S];return E===void 0&&(E=sm++,is[S]=E,rs[E]=S),S=E,N()[v+8>>>2>>>0]=S,v})(),typeof MainLoop<"u"&&MainLoop.Qb&&MainLoop.pause(),_i(()=>As(Xe)))}else pt===2?(pt=0,_i(Bs),it(Xe),Xe=null,om.forEach(lr)):ot(`invalid state: ${pt}`);return ts}})(p=>{u().then(p)})}function um(u){return u>>>=0,as(async()=>{var p=await Ae(u);return Ne(p)})}var wi=[];function lm(u,p,h,g){return h>>>=0,g>>>=0,(u=wi[u>>>0])(null,p=Ae(p>>>0),h,g)}var dm={},vi=u=>{var p=dm[u];return p===void 0?Ze(u):p};function pm(u,p,h,g,v){return h>>>=0,g>>>=0,v>>>=0,(u=wi[u>>>0])(p=Ae(p>>>0),p[h=vi(h)],g,v)}function cm(u,p){return p>>>=0,(u=Ae(u>>>0))==Ae(p)}var ns=()=>typeof globalThis=="object"?globalThis:Function("return this")();function fm(u){return(u>>>=0)==0?Ne(ns()):(u=vi(u),Ne(ns()[u]))}var hm=u=>{var p=wi.length;return wi.push(u),p},mm=(u,p)=>{for(var h=Array(u),g=0;g<u;++g)h[g]=yi(le()[p+4*g>>>2>>>0],`parameter ${g}`);return h};function gm(u,p,h){var g=(p=mm(u,p>>>0)).shift();u--;var v=`return function (obj, func, destructorsRef, args) {
`,S=0,E=[];h===0&&E.push("obj");for(var R=["retType"],P=[g],q=0;q<u;++q)E.push(`arg${q}`),R.push(`argType${q}`),P.push(p[q]),v+=`  var arg${q} = argType${q}.readValueFromPointer(args${S?"+"+S:""});
`,S+=p[q].Cb;return v+=`  var rv = ${h===1?"new func":"func.call"}(${E.join(", ")});
`,g.Ub||(R.push("emval_returnValue"),P.push(es),v+=`  return emval_returnValue(retType, destructorsRef, rv);
`),u=new Function(...R,v+`};
`)(...P),h=`methodCaller<(${p.map(Z=>Z.name).join(", ")}) => ${g.name}>`,hm(Object.defineProperty(u,"name",{value:h}))}function ym(u){return u=vi(u>>>0),Ne(i[u])}function _m(u,p){return p>>>=0,u=Ae(u>>>0),p=Ae(p),Ne(u[p])}function bm(u){9<(u>>>=0)&&(tt[u+1]+=1)}function wm(){return Ne([])}function vm(u){u=Ae(u>>>0);for(var p=Array(u.length),h=0;h<u.length;h++)p[h]=u[h];return Ne(p)}function $m(u){return Ne(vi(u>>>0))}function xm(){return Ne({})}function Cm(u){for(var p=Ae(u>>>=0);p.length;){var h=p.pop();p.pop()(h)}sr(u)}function Tm(u,p,h){p>>>=0,h>>>=0,u=Ae(u>>>0),p=Ae(p),h=Ae(h),u[p]=h}function Sm(u,p){return p>>>=0,u=(u=yi(u>>>0,"_emval_take_value")).readValueFromPointer(p),Ne(u)}function Im(u,p){u=-9007199254740992>u||9007199254740992<u?NaN:Number(u),p>>>=0,u=new Date(1e3*u),N()[p>>>2>>>0]=u.getUTCSeconds(),N()[p+4>>>2>>>0]=u.getUTCMinutes(),N()[p+8>>>2>>>0]=u.getUTCHours(),N()[p+12>>>2>>>0]=u.getUTCDate(),N()[p+16>>>2>>>0]=u.getUTCMonth(),N()[p+20>>>2>>>0]=u.getUTCFullYear()-1900,N()[p+24>>>2>>>0]=u.getUTCDay(),u=(u.getTime()-Date.UTC(u.getUTCFullYear(),0,1,0,0,0,0))/864e5|0,N()[p+28>>>2>>>0]=u}var ss=u=>u%4==0&&(u%100!=0||u%400==0),os=[0,31,60,91,121,152,182,213,244,274,305,335],us=[0,31,59,90,120,151,181,212,243,273,304,334];function km(u,p){u=-9007199254740992>u||9007199254740992<u?NaN:Number(u),p>>>=0,u=new Date(1e3*u),N()[p>>>2>>>0]=u.getSeconds(),N()[p+4>>>2>>>0]=u.getMinutes(),N()[p+8>>>2>>>0]=u.getHours(),N()[p+12>>>2>>>0]=u.getDate(),N()[p+16>>>2>>>0]=u.getMonth(),N()[p+20>>>2>>>0]=u.getFullYear()-1900,N()[p+24>>>2>>>0]=u.getDay();var h=(ss(u.getFullYear())?os:us)[u.getMonth()]+u.getDate()-1|0;N()[p+28>>>2>>>0]=h,N()[p+36>>>2>>>0]=-60*u.getTimezoneOffset(),h=new Date(u.getFullYear(),6,1).getTimezoneOffset();var g=new Date(u.getFullYear(),0,1).getTimezoneOffset();u=0|(h!=g&&u.getTimezoneOffset()==Math.min(g,h)),N()[p+32>>>2>>>0]=u}function Em(u){u>>>=0;var p=new Date(N()[u+20>>>2>>>0]+1900,N()[u+16>>>2>>>0],N()[u+12>>>2>>>0],N()[u+8>>>2>>>0],N()[u+4>>>2>>>0],N()[u>>>2>>>0],0),h=N()[u+32>>>2>>>0],g=p.getTimezoneOffset(),v=new Date(p.getFullYear(),6,1).getTimezoneOffset(),S=new Date(p.getFullYear(),0,1).getTimezoneOffset(),E=Math.min(S,v);return 0>h?N()[u+32>>>2>>>0]=+(v!=S&&E==g):0<h!=(E==g)&&(v=Math.max(S,v),p.setTime(p.getTime()+6e4*((0<h?E:v)-g))),N()[u+24>>>2>>>0]=p.getDay(),h=(ss(p.getFullYear())?os:us)[p.getMonth()]+p.getDate()-1|0,N()[u+28>>>2>>>0]=h,N()[u>>>2>>>0]=p.getSeconds(),N()[u+4>>>2>>>0]=p.getMinutes(),N()[u+8>>>2>>>0]=p.getHours(),N()[u+12>>>2>>>0]=p.getDate(),N()[u+16>>>2>>>0]=p.getMonth(),N()[u+20>>>2>>>0]=p.getYear(),u=p.getTime(),BigInt(isNaN(u)?-1:u/1e3)}function ls(u,p,h,g,v,S,E){return o?ve(16,1,u,p,h,g,v,S,E):-52}function ds(u,p,h,g,v,S){if(o)return ve(17,1,u,p,h,g,v,S)}var Ht={},zm=()=>performance.timeOrigin+performance.now();function ps(u,p){if(o)return ve(18,1,u,p);if(Ht[u]&&(clearTimeout(Ht[u].id),delete Ht[u]),!p)return 0;var h=setTimeout(()=>{delete Ht[u],lr(()=>Is(u,performance.timeOrigin+performance.now()))},p);return Ht[u]={id:h,rc:p},0}function Am(u,p,h,g){u>>>=0,p>>>=0,h>>>=0,g>>>=0;var v=new Date().getFullYear(),S=new Date(v,0,1).getTimezoneOffset();v=new Date(v,6,1).getTimezoneOffset();var E=Math.max(S,v);le()[u>>>2>>>0]=60*E,N()[p>>>2>>>0]=+(S!=v),u=(p=R=>{var P=Math.abs(R);return`UTC${0<=R?"-":"+"}${String(Math.floor(P/60)).padStart(2,"0")}${String(P%60).padStart(2,"0")}`})(S),p=p(v),v<S?(wt(u,h,17),wt(p,g,17)):(wt(u,g,17),wt(p,h,17))}var Om=()=>Date.now();function Rm(u,p,h){return 0<=u&&3>=u?(u===0?u=Date.now():u=performance.timeOrigin+performance.now(),G[h>>>0>>>3]=BigInt(Math.round(1e6*u)),0):28}var fr=[],cs=(u,p)=>{fr.length=0;for(var h;h=j()[u++>>>0];){var g=h!=105;p+=(g&=h!=112)&&p%8?4:0,fr.push(h==112?le()[p>>>2>>>0]:h==106?G[p>>>3]:h==105?N()[p>>>2>>>0]:be()[p>>>3>>>0]),p+=g?8:4}return fr};function Bm(u,p,h){return u>>>=0,p=cs(p>>>0,h>>>0),gr[u](...p)}function Mm(u,p,h){return u>>>=0,p=cs(p>>>0,h>>>0),gr[u](...p)}var Dm=()=>{};function Nm(u,p){return ne(Te(u>>>0,p>>>0))}var Pm=()=>{throw ut+=1,"unwind"};function Um(){return 4294901760}var Wm=()=>navigator.hardwareConcurrency;function Lm(){return ot("Cannot use emscripten_pc_get_function without -sUSE_OFFSET_CONVERTER"),0}function qm(u){u>>>=0;var p=j().length;if(u<=p||4294901760<u)return!1;for(var h=1;4>=h;h*=2){var g=p*(1+.2/h);g=Math.min(g,u+100663296);e:{g=(Math.min(4294901760,65536*Math.ceil(Math.max(u,g)/65536))-$.buffer.byteLength+65535)/65536|0;try{$.grow(g),Ce();var v=1;break e}catch{}v=void 0}if(v)return!0}return!1}var $i=()=>(ot("Cannot use convertFrameToPC (needed by __builtin_return_address) without -sUSE_OFFSET_CONVERTER"),0),Kt={},fs=u=>{u.forEach(p=>{$i()})};function Vm(){var u=Error().stack.toString().split(`
`);return u[0]=="Error"&&u.shift(),fs(u),Kt.Mb=$i(),Kt.dc=u,Kt.Mb}function jm(u,p,h){if(u>>>=0,p>>>=0,Kt.Mb==u)var g=Kt.dc;else(g=Error().stack.toString().split(`
`))[0]=="Error"&&g.shift(),fs(g);for(var v=3;g[v]&&$i()!=u;)++v;for(u=0;u<h&&g[u+v];++u)N()[p+4*u>>>2>>>0]=$i();return u}var hr,mr={},hs=()=>{if(!hr){var u,p={USER:"web_user",LOGNAME:"web_user",PATH:"/",PWD:"/",HOME:"/home/web_user",LANG:(typeof navigator=="object"&&navigator.languages&&navigator.languages[0]||"C").replace("-","_")+".UTF-8",_:"./this.program"};for(u in mr)mr[u]===void 0?delete p[u]:p[u]=mr[u];var h=[];for(u in p)h.push(`${u}=${p[u]}`);hr=h}return hr};function ms(u,p){if(o)return ve(19,1,u,p);u>>>=0,p>>>=0;var h,g=0,v=0;for(h of hs()){var S=p+g;le()[u+v>>>2>>>0]=S,g+=wt(h,S,1/0)+1,v+=4}return 0}function gs(u,p){if(o)return ve(20,1,u,p);u>>>=0,p>>>=0;var h=hs();for(var g of(le()[u>>>2>>>0]=h.length,u=0,h))u+=ur(g)+1;return le()[p>>>2>>>0]=u,0}function ys(u){return o?ve(21,1,u):52}function _s(u,p,h,g){return o?ve(22,1,u,p,h,g):52}function bs(u,p,h,g){return o?ve(23,1,u,p,h,g):70}var Fm=[null,[],[]];function ws(u,p,h,g){if(o)return ve(24,1,u,p,h,g);p>>>=0,h>>>=0,g>>>=0;for(var v=0,S=0;S<h;S++){var E=le()[p>>>2>>>0],R=le()[p+4>>>2>>>0];p+=8;for(var P=0;P<R;P++){var q=u,Z=j()[E+P>>>0],te=Fm[q];Z===0||Z===10?((q===1?K:ne)(Nn(te)),te.length=0):te.push(Z)}v+=R}return le()[g>>>2>>>0]=v,0}o||(function(){for(var u=i.numThreads-1;u--;)Rn();er.push(()=>{jt++,(function(p){o?p():Promise.all(lt.map(On)).then(p)})(()=>Cn())})})();for(var vs=Array(256),xi=0;256>xi;++xi)vs[xi]=String.fromCharCode(xi);Xn=vs,tt.push(0,1,void 0,1,null,1,!0,1,!1,1),i.count_emval_handles=()=>tt.length/2-5-nr.length,o||($=new WebAssembly.Memory({initial:256,maximum:65536,shared:!0}),Ce()),i.wasmBinary&&(x=i.wasmBinary),i.stackSave=()=>vr(),i.stackRestore=u=>Ti(u),i.stackAlloc=u=>wr(u),i.setValue=function(u,p,h="i8"){switch(h.endsWith("*")&&(h="*"),h){case"i1":case"i8":U()[u>>>0]=p;break;case"i16":ae()[u>>>1>>>0]=p;break;case"i32":N()[u>>>2>>>0]=p;break;case"i64":G[u>>>3]=BigInt(p);break;case"float":Ye()[u>>>2>>>0]=p;break;case"double":be()[u>>>3>>>0]=p;break;case"*":le()[u>>>2>>>0]=p;break;default:ot(`invalid type for setValue: ${h}`)}},i.getValue=function(u,p="i8"){switch(p.endsWith("*")&&(p="*"),p){case"i1":case"i8":return U()[u>>>0];case"i16":return ae()[u>>>1>>>0];case"i32":return N()[u>>>2>>>0];case"i64":return G[u>>>3];case"float":return Ye()[u>>>2>>>0];case"double":return be()[u>>>3>>>0];case"*":return le()[u>>>2>>>0];default:ot(`invalid type for getValue: ${p}`)}},i.UTF8ToString=Te,i.stringToUTF8=wt,i.lengthBytesUTF8=ur;var Gm=[tr,kn,Bn,Pn,Un,Wn,Ln,qn,Vn,jn,Fn,Gn,Hn,Kn,Yn,Zn,ls,ds,ps,ms,gs,ys,_s,bs,ws],gr={893836:(u,p,h,g,v)=>{if(i===void 0||!i.Fb)return 1;if((u=Te(Number(u>>>0))).startsWith("./")&&(u=u.substring(2)),!(u=i.Fb.get(u)))return 2;if(p=Number(p>>>0),h=Number(h>>>0),g=Number(g>>>0),p+h>u.byteLength)return 3;try{let S=u.subarray(p,p+h);switch(v){case 0:j().set(S,g>>>0);break;case 1:i.mc?i.mc(g,S):i.cc(g,S);break;default:return 4}return 0}catch{return 4}},894660:(u,p,h)=>{i.Pb(u,j().subarray(p>>>0,p+h>>>0))},894724:()=>i.oc(),894766:u=>{i.Ob(u)},894803:()=>{i.Wb()},894834:()=>{i.Xb()},894863:()=>{i.ac()},894888:u=>i.Vb(u),894921:u=>i.Zb(u),894953:(u,p,h)=>{i.Lb(Number(u),Number(p),Number(h),!0)},895016:(u,p,h)=>{i.Lb(Number(u),Number(p),Number(h))},895073:()=>typeof wasmOffsetConverter<"u",895130:u=>{i.Ab("Abs",u,void 0)},895181:u=>{i.Ab("Neg",u,void 0)},895232:u=>{i.Ab("Floor",u,void 0)},895285:u=>{i.Ab("Ceil",u,void 0)},895337:u=>{i.Ab("Reciprocal",u,void 0)},895395:u=>{i.Ab("Sqrt",u,void 0)},895447:u=>{i.Ab("Exp",u,void 0)},895498:u=>{i.Ab("Erf",u,void 0)},895549:u=>{i.Ab("Sigmoid",u,void 0)},895604:(u,p,h)=>{i.Ab("HardSigmoid",u,{alpha:p,beta:h})},895683:u=>{i.Ab("Log",u,void 0)},895734:u=>{i.Ab("Sin",u,void 0)},895785:u=>{i.Ab("Cos",u,void 0)},895836:u=>{i.Ab("Tan",u,void 0)},895887:u=>{i.Ab("Asin",u,void 0)},895939:u=>{i.Ab("Acos",u,void 0)},895991:u=>{i.Ab("Atan",u,void 0)},896043:u=>{i.Ab("Sinh",u,void 0)},896095:u=>{i.Ab("Cosh",u,void 0)},896147:u=>{i.Ab("Asinh",u,void 0)},896200:u=>{i.Ab("Acosh",u,void 0)},896253:u=>{i.Ab("Atanh",u,void 0)},896306:u=>{i.Ab("Tanh",u,void 0)},896358:u=>{i.Ab("Not",u,void 0)},896409:(u,p,h)=>{i.Ab("Clip",u,{min:p,max:h})},896478:u=>{i.Ab("Clip",u,void 0)},896530:(u,p)=>{i.Ab("Elu",u,{alpha:p})},896588:u=>{i.Ab("Gelu",u,void 0)},896640:u=>{i.Ab("Relu",u,void 0)},896692:(u,p)=>{i.Ab("LeakyRelu",u,{alpha:p})},896756:(u,p)=>{i.Ab("ThresholdedRelu",u,{alpha:p})},896826:(u,p)=>{i.Ab("Cast",u,{to:p})},896884:u=>{i.Ab("Add",u,void 0)},896935:u=>{i.Ab("Sub",u,void 0)},896986:u=>{i.Ab("Mul",u,void 0)},897037:u=>{i.Ab("Div",u,void 0)},897088:u=>{i.Ab("Pow",u,void 0)},897139:u=>{i.Ab("Equal",u,void 0)},897192:u=>{i.Ab("Greater",u,void 0)},897247:u=>{i.Ab("GreaterOrEqual",u,void 0)},897309:u=>{i.Ab("Less",u,void 0)},897361:u=>{i.Ab("LessOrEqual",u,void 0)},897420:(u,p,h,g,v)=>{i.Ab("ReduceMean",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},897595:(u,p,h,g,v)=>{i.Ab("ReduceMax",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},897769:(u,p,h,g,v)=>{i.Ab("ReduceMin",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},897943:(u,p,h,g,v)=>{i.Ab("ReduceProd",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},898118:(u,p,h,g,v)=>{i.Ab("ReduceSum",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},898292:(u,p,h,g,v)=>{i.Ab("ReduceL1",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},898465:(u,p,h,g,v)=>{i.Ab("ReduceL2",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},898638:(u,p,h,g,v)=>{i.Ab("ReduceLogSum",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},898815:(u,p,h,g,v)=>{i.Ab("ReduceSumSquare",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},898995:(u,p,h,g,v)=>{i.Ab("ReduceLogSumExp",u,{keepDims:!!p,noopWithEmptyAxes:!!h,axes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},899175:u=>{i.Ab("Where",u,void 0)},899228:(u,p,h)=>{i.Ab("Transpose",u,{perm:p?Array.from(N().subarray(Number(p)>>>0,Number(h)>>>0)):[]})},899352:(u,p,h,g)=>{i.Ab("DepthToSpace",u,{blocksize:p,mode:Te(h),format:g?"NHWC":"NCHW"})},899485:(u,p,h,g)=>{i.Ab("DepthToSpace",u,{blocksize:p,mode:Te(h),format:g?"NHWC":"NCHW"})},899618:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se)=>{i.Ab("ConvTranspose",u,{format:P?"NHWC":"NCHW",autoPad:p,dilations:[h],group:g,kernelShape:[v],pads:[S,E],strides:[R],wIsConst:()=>!!U()[q>>>0],outputPadding:Z?Array.from(N().subarray(Number(Z)>>>0,Number(te)>>>0)):[],outputShape:ue?Array.from(N().subarray(Number(ue)>>>0,Number(fe)>>>0)):[],activation:Te(Se)})},900051:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>{i.Ab("ConvTranspose",u,{format:R?"NHWC":"NCHW",autoPad:p,dilations:Array.from(N().subarray(Number(h)>>>0,2+(Number(h)>>>0)>>>0)),group:g,kernelShape:Array.from(N().subarray(Number(v)>>>0,2+(Number(v)>>>0)>>>0)),pads:Array.from(N().subarray(Number(S)>>>0,4+(Number(S)>>>0)>>>0)),strides:Array.from(N().subarray(Number(E)>>>0,2+(Number(E)>>>0)>>>0)),wIsConst:()=>!!U()[P>>>0],outputPadding:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],outputShape:te?Array.from(N().subarray(Number(te)>>>0,Number(ue)>>>0)):[],activation:Te(fe)})},900712:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se)=>{i.Ab("ConvTranspose",u,{format:P?"NHWC":"NCHW",autoPad:p,dilations:[h],group:g,kernelShape:[v],pads:[S,E],strides:[R],wIsConst:()=>!!U()[q>>>0],outputPadding:Z?Array.from(N().subarray(Number(Z)>>>0,Number(te)>>>0)):[],outputShape:ue?Array.from(N().subarray(Number(ue)>>>0,Number(fe)>>>0)):[],activation:Te(Se)})},901145:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>{i.Ab("ConvTranspose",u,{format:R?"NHWC":"NCHW",autoPad:p,dilations:Array.from(N().subarray(Number(h)>>>0,2+(Number(h)>>>0)>>>0)),group:g,kernelShape:Array.from(N().subarray(Number(v)>>>0,2+(Number(v)>>>0)>>>0)),pads:Array.from(N().subarray(Number(S)>>>0,4+(Number(S)>>>0)>>>0)),strides:Array.from(N().subarray(Number(E)>>>0,2+(Number(E)>>>0)>>>0)),wIsConst:()=>!!U()[P>>>0],outputPadding:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],outputShape:te?Array.from(N().subarray(Number(te)>>>0,Number(ue)>>>0)):[],activation:Te(fe)})},901806:(u,p)=>{i.Ab("GlobalAveragePool",u,{format:p?"NHWC":"NCHW"})},901897:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>{i.Ab("AveragePool",u,{format:fe?"NHWC":"NCHW",auto_pad:p,ceil_mode:h,count_include_pad:g,storage_order:v,dilations:S?Array.from(N().subarray(Number(S)>>>0,Number(E)>>>0)):[],kernel_shape:R?Array.from(N().subarray(Number(R)>>>0,Number(P)>>>0)):[],pads:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],strides:te?Array.from(N().subarray(Number(te)>>>0,Number(ue)>>>0)):[]})},902376:(u,p)=>{i.Ab("GlobalAveragePool",u,{format:p?"NHWC":"NCHW"})},902467:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>{i.Ab("AveragePool",u,{format:fe?"NHWC":"NCHW",auto_pad:p,ceil_mode:h,count_include_pad:g,storage_order:v,dilations:S?Array.from(N().subarray(Number(S)>>>0,Number(E)>>>0)):[],kernel_shape:R?Array.from(N().subarray(Number(R)>>>0,Number(P)>>>0)):[],pads:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],strides:te?Array.from(N().subarray(Number(te)>>>0,Number(ue)>>>0)):[]})},902946:(u,p)=>{i.Ab("GlobalMaxPool",u,{format:p?"NHWC":"NCHW"})},903033:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>{i.Ab("MaxPool",u,{format:fe?"NHWC":"NCHW",auto_pad:p,ceil_mode:h,count_include_pad:g,storage_order:v,dilations:S?Array.from(N().subarray(Number(S)>>>0,Number(E)>>>0)):[],kernel_shape:R?Array.from(N().subarray(Number(R)>>>0,Number(P)>>>0)):[],pads:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],strides:te?Array.from(N().subarray(Number(te)>>>0,Number(ue)>>>0)):[]})},903508:(u,p)=>{i.Ab("GlobalMaxPool",u,{format:p?"NHWC":"NCHW"})},903595:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>{i.Ab("MaxPool",u,{format:fe?"NHWC":"NCHW",auto_pad:p,ceil_mode:h,count_include_pad:g,storage_order:v,dilations:S?Array.from(N().subarray(Number(S)>>>0,Number(E)>>>0)):[],kernel_shape:R?Array.from(N().subarray(Number(R)>>>0,Number(P)>>>0)):[],pads:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],strides:te?Array.from(N().subarray(Number(te)>>>0,Number(ue)>>>0)):[]})},904070:(u,p,h,g,v)=>{i.Ab("Gemm",u,{alpha:p,beta:h,transA:g,transB:v})},904174:u=>{i.Ab("MatMul",u,void 0)},904228:(u,p,h,g)=>{i.Ab("ArgMax",u,{keepDims:!!p,selectLastIndex:!!h,axis:g})},904336:(u,p,h,g)=>{i.Ab("ArgMin",u,{keepDims:!!p,selectLastIndex:!!h,axis:g})},904444:(u,p)=>{i.Ab("Softmax",u,{axis:p})},904507:(u,p)=>{i.Ab("Concat",u,{axis:p})},904567:(u,p,h,g,v)=>{i.Ab("Split",u,{axis:p,numOutputs:h,splitSizes:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},904723:u=>{i.Ab("Expand",u,void 0)},904777:(u,p)=>{i.Ab("Gather",u,{axis:Number(p)})},904848:(u,p)=>{i.Ab("GatherElements",u,{axis:Number(p)})},904927:(u,p)=>{i.Ab("GatherND",u,{batch_dims:Number(p)})},905006:(u,p,h,g,v,S,E,R,P,q,Z)=>{i.Ab("Resize",u,{antialias:p,axes:h?Array.from(N().subarray(Number(h)>>>0,Number(g)>>>0)):[],coordinateTransformMode:Te(v),cubicCoeffA:S,excludeOutside:E,extrapolationValue:R,keepAspectRatioPolicy:Te(P),mode:Te(q),nearestMode:Te(Z)})},905368:(u,p,h,g,v,S,E)=>{i.Ab("Slice",u,{starts:p?Array.from(N().subarray(Number(p)>>>0,Number(h)>>>0)):[],ends:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[],axes:S?Array.from(N().subarray(Number(S)>>>0,Number(E)>>>0)):[]})},905632:u=>{i.Ab("Tile",u,void 0)},905684:(u,p,h)=>{i.Ab("InstanceNormalization",u,{epsilon:p,format:h?"NHWC":"NCHW"})},905798:(u,p,h)=>{i.Ab("InstanceNormalization",u,{epsilon:p,format:h?"NHWC":"NCHW"})},905912:u=>{i.Ab("Range",u,void 0)},905965:(u,p)=>{i.Ab("Einsum",u,{equation:Te(p)})},906046:(u,p,h,g,v)=>{i.Ab("Pad",u,{mode:p,value:h,pads:g?Array.from(N().subarray(Number(g)>>>0,Number(v)>>>0)):[]})},906189:(u,p,h,g,v,S)=>{i.Ab("BatchNormalization",u,{epsilon:p,momentum:h,spatial:!!v,trainingMode:!!g,format:S?"NHWC":"NCHW"})},906358:(u,p,h,g,v,S)=>{i.Ab("BatchNormalization",u,{epsilon:p,momentum:h,spatial:!!v,trainingMode:!!g,format:S?"NHWC":"NCHW"})},906527:(u,p,h)=>{i.Ab("CumSum",u,{exclusive:Number(p),reverse:Number(h)})},906624:(u,p,h)=>{i.Ab("DequantizeLinear",u,{axis:p,blockSize:h})},906714:(u,p,h,g,v)=>{i.Ab("GridSample",u,{align_corners:p,mode:Te(h),padding_mode:Te(g),format:v?"NHWC":"NCHW"})},906884:(u,p,h,g,v)=>{i.Ab("GridSample",u,{align_corners:p,mode:Te(h),padding_mode:Te(g),format:v?"NHWC":"NCHW"})},907054:(u,p)=>{i.Ab("ScatterND",u,{reduction:Te(p)})},907139:(u,p,h,g,v,S,E,R,P)=>{i.Ab("Attention",u,{numHeads:p,isUnidirectional:h,maskFilterValue:g,scale:v,doRotary:S,qkvHiddenSizes:E?Array.from(N().subarray(Number(R)>>>0,Number(R)+E>>>0)):[],pastPresentShareBuffer:!!P})},907411:u=>{i.Ab("BiasAdd",u,void 0)},907466:u=>{i.Ab("BiasSplitGelu",u,void 0)},907527:u=>{i.Ab("FastGelu",u,void 0)},907583:(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re)=>{i.Ab("Conv",u,{format:te?"NHWC":"NCHW",auto_pad:p,dilations:h?Array.from(N().subarray(Number(h)>>>0,Number(g)>>>0)):[],group:v,kernel_shape:S?Array.from(N().subarray(Number(S)>>>0,Number(E)>>>0)):[],pads:R?Array.from(N().subarray(Number(R)>>>0,Number(P)>>>0)):[],strides:q?Array.from(N().subarray(Number(q)>>>0,Number(Z)>>>0)):[],w_is_const:()=>!!U()[Number(ue)>>>0],activation:Te(fe),activation_params:Se?Array.from(Ye().subarray(Number(Se)>>>0,Number(Re)>>>0)):[]})},908167:u=>{i.Ab("Gelu",u,void 0)},908219:(u,p,h,g,v,S,E,R,P)=>{i.Ab("GroupQueryAttention",u,{numHeads:p,kvNumHeads:h,scale:g,softcap:v,doRotary:S,rotaryInterleaved:E,smoothSoftmax:R,localWindowSize:P})},908436:(u,p,h,g)=>{i.Ab("LayerNormalization",u,{axis:p,epsilon:h,simplified:!!g})},908547:(u,p,h,g)=>{i.Ab("LayerNormalization",u,{axis:p,epsilon:h,simplified:!!g})},908658:(u,p,h,g,v,S)=>{i.Ab("MatMulNBits",u,{k:p,n:h,accuracyLevel:g,bits:v,blockSize:S})},908785:(u,p,h,g,v,S)=>{i.Ab("MultiHeadAttention",u,{numHeads:p,isUnidirectional:h,maskFilterValue:g,scale:v,doRotary:S})},908944:(u,p)=>{i.Ab("QuickGelu",u,{alpha:p})},909008:(u,p,h,g,v)=>{i.Ab("RotaryEmbedding",u,{interleaved:!!p,numHeads:h,rotaryEmbeddingDim:g,scale:v})},909147:(u,p,h)=>{i.Ab("SkipLayerNormalization",u,{epsilon:p,simplified:!!h})},909249:(u,p,h)=>{i.Ab("SkipLayerNormalization",u,{epsilon:p,simplified:!!h})},909351:(u,p,h,g)=>{i.Ab("GatherBlockQuantized",u,{gatherAxis:p,quantizeAxis:h,blockSize:g})},909472:u=>{i.$b(u)},909506:(u,p)=>i.bc(Number(u),Number(p),i.Gb.ec,i.Gb.errors)};function Hm(u,p,h){return as(async()=>{await i.Yb(Number(u),Number(p),Number(h))})}function Km(){return typeof wasmOffsetConverter<"u"}var B=await(async function(){function u(g,v){return B=g.exports,B=(function(){var S=B,E={};for(let[R,P]of Object.entries(S))E[R]=typeof P=="function"?(...q)=>{bi.push(R);try{return P(...q)}finally{Y||(bi.pop(),Xe&&pt===1&&bi.length===0&&(pt=0,ut+=1,_i(Os),typeof Fibers<"u"&&Fibers.sc()))}}:P;return E})(),B=(function(){var S=B,E=P=>q=>P(q)>>>0,R=P=>()=>P()>>>0;return(S=Object.assign({},S)).Ea=E(S.Ea),S.gb=R(S.gb),S.ib=E(S.ib),S.tb=E(S.tb),S.ub=R(S.ub),S.__cxa_get_exception_ptr=E(S.__cxa_get_exception_ptr),S})(),En.push(B.jb),w=v,Cn(),B}jt++;var p=Tn();if(i.instantiateWasm)return new Promise(g=>{i.instantiateWasm(p,(v,S)=>{g(u(v,S))})});if(o)return new Promise(g=>{W=v=>{var S=new WebAssembly.Instance(v,Tn());g(u(S,v))}});Vt??=i.locateFile?i.locateFile?i.locateFile("ort-wasm-simd-threaded.jsep.wasm",b):b+"ort-wasm-simd-threaded.jsep.wasm":new URL("/build/assets/ort-wasm-simd-threaded.jsep-BGTZ4Y7F.wasm",import.meta.url).href;try{var h=await(async function(g){var v=Vt;if(!x&&typeof WebAssembly.instantiateStreaming=="function"&&!ye(v))try{var S=fetch(v,{credentials:"same-origin"});return await WebAssembly.instantiateStreaming(S,g)}catch(E){ne(`wasm streaming compile failed: ${E}`),ne("falling back to ArrayBuffer instantiation")}return(async function(E,R){try{var P=await(async function(q){if(!x)try{var Z=await f(q);return new Uint8Array(Z)}catch{}if(q==Vt&&x)q=new Uint8Array(x);else{if(!m)throw"both async and sync fetching of the wasm failed";q=m(q)}return q})(E);return await WebAssembly.instantiate(P,R)}catch(q){ne(`failed to asynchronously prepare wasm: ${q}`),ot(q)}})(v,g)})(p);return u(h.instance,h.module)}catch(g){return r(g),Promise.reject(g)}})(),$s=u=>($s=B.Ea)(u),xs=()=>(xs=B.Fa)();i._OrtInit=(u,p)=>(i._OrtInit=B.Ga)(u,p),i._OrtGetLastError=(u,p)=>(i._OrtGetLastError=B.Ha)(u,p),i._OrtCreateSessionOptions=(u,p,h,g,v,S,E,R,P,q)=>(i._OrtCreateSessionOptions=B.Ia)(u,p,h,g,v,S,E,R,P,q),i._OrtAppendExecutionProvider=(u,p,h,g,v)=>(i._OrtAppendExecutionProvider=B.Ja)(u,p,h,g,v),i._OrtAddFreeDimensionOverride=(u,p,h)=>(i._OrtAddFreeDimensionOverride=B.Ka)(u,p,h),i._OrtAddSessionConfigEntry=(u,p,h)=>(i._OrtAddSessionConfigEntry=B.La)(u,p,h),i._OrtReleaseSessionOptions=u=>(i._OrtReleaseSessionOptions=B.Ma)(u),i._OrtCreateSession=(u,p,h)=>(i._OrtCreateSession=B.Na)(u,p,h),i._OrtReleaseSession=u=>(i._OrtReleaseSession=B.Oa)(u),i._OrtGetInputOutputCount=(u,p,h)=>(i._OrtGetInputOutputCount=B.Pa)(u,p,h),i._OrtGetInputOutputMetadata=(u,p,h,g)=>(i._OrtGetInputOutputMetadata=B.Qa)(u,p,h,g),i._OrtFree=u=>(i._OrtFree=B.Ra)(u),i._OrtCreateTensor=(u,p,h,g,v,S)=>(i._OrtCreateTensor=B.Sa)(u,p,h,g,v,S),i._OrtGetTensorData=(u,p,h,g,v)=>(i._OrtGetTensorData=B.Ta)(u,p,h,g,v),i._OrtReleaseTensor=u=>(i._OrtReleaseTensor=B.Ua)(u),i._OrtCreateRunOptions=(u,p,h,g)=>(i._OrtCreateRunOptions=B.Va)(u,p,h,g),i._OrtAddRunConfigEntry=(u,p,h)=>(i._OrtAddRunConfigEntry=B.Wa)(u,p,h),i._OrtReleaseRunOptions=u=>(i._OrtReleaseRunOptions=B.Xa)(u),i._OrtCreateBinding=u=>(i._OrtCreateBinding=B.Ya)(u),i._OrtBindInput=(u,p,h)=>(i._OrtBindInput=B.Za)(u,p,h),i._OrtBindOutput=(u,p,h,g)=>(i._OrtBindOutput=B._a)(u,p,h,g),i._OrtClearBoundOutputs=u=>(i._OrtClearBoundOutputs=B.$a)(u),i._OrtReleaseBinding=u=>(i._OrtReleaseBinding=B.ab)(u),i._OrtRunWithBinding=(u,p,h,g,v)=>(i._OrtRunWithBinding=B.bb)(u,p,h,g,v),i._OrtRun=(u,p,h,g,v,S,E,R)=>(i._OrtRun=B.cb)(u,p,h,g,v,S,E,R),i._OrtEndProfiling=u=>(i._OrtEndProfiling=B.db)(u),i._JsepOutput=(u,p,h)=>(i._JsepOutput=B.eb)(u,p,h),i._JsepGetNodeName=u=>(i._JsepGetNodeName=B.fb)(u);var yr=()=>(yr=B.gb)(),it=i._free=u=>(it=i._free=B.hb)(u),Ci=i._malloc=u=>(Ci=i._malloc=B.ib)(u),_r=(u,p,h,g,v,S)=>(_r=B.kb)(u,p,h,g,v,S),Cs=()=>(Cs=B.lb)(),Ts=(u,p,h,g,v)=>(Ts=B.mb)(u,p,h,g,v),Ss=u=>(Ss=B.nb)(u),br=u=>(br=B.ob)(u),Is=(u,p)=>(Is=B.pb)(u,p),ks=()=>(ks=B.qb)(),Es=(u,p)=>(Es=B.rb)(u,p),Ti=u=>(Ti=B.sb)(u),wr=u=>(wr=B.tb)(u),vr=()=>(vr=B.ub)(),zs=i.dynCall_ii=(u,p)=>(zs=i.dynCall_ii=B.vb)(u,p);i.dynCall_vii=(u,p,h)=>(i.dynCall_vii=B.dynCall_vii)(u,p,h),i.dynCall_iiiii=(u,p,h,g,v)=>(i.dynCall_iiiii=B.dynCall_iiiii)(u,p,h,g,v),i.dynCall_iii=(u,p,h)=>(i.dynCall_iii=B.dynCall_iii)(u,p,h),i.dynCall_iiiiii=(u,p,h,g,v,S)=>(i.dynCall_iiiiii=B.dynCall_iiiiii)(u,p,h,g,v,S),i.dynCall_iiiiiiii=(u,p,h,g,v,S,E,R)=>(i.dynCall_iiiiiiii=B.dynCall_iiiiiiii)(u,p,h,g,v,S,E,R),i.dynCall_iiiiiii=(u,p,h,g,v,S,E)=>(i.dynCall_iiiiiii=B.dynCall_iiiiiii)(u,p,h,g,v,S,E),i.dynCall_vi=(u,p)=>(i.dynCall_vi=B.dynCall_vi)(u,p),i.dynCall_iiii=(u,p,h,g)=>(i.dynCall_iiii=B.dynCall_iiii)(u,p,h,g),i.dynCall_i=u=>(i.dynCall_i=B.dynCall_i)(u),i.dynCall_viiiiiiii=(u,p,h,g,v,S,E,R,P)=>(i.dynCall_viiiiiiii=B.dynCall_viiiiiiii)(u,p,h,g,v,S,E,R,P),i.dynCall_viii=(u,p,h,g)=>(i.dynCall_viii=B.dynCall_viii)(u,p,h,g),i.dynCall_viijj=(u,p,h,g,v)=>(i.dynCall_viijj=B.dynCall_viijj)(u,p,h,g,v),i.dynCall_viiiiii=(u,p,h,g,v,S,E)=>(i.dynCall_viiiiii=B.dynCall_viiiiii)(u,p,h,g,v,S,E),i.dynCall_viiii=(u,p,h,g,v)=>(i.dynCall_viiii=B.dynCall_viiii)(u,p,h,g,v),i.dynCall_viiiii=(u,p,h,g,v,S)=>(i.dynCall_viiiii=B.dynCall_viiiii)(u,p,h,g,v,S),i.dynCall_vfiii=(u,p,h,g,v)=>(i.dynCall_vfiii=B.dynCall_vfiii)(u,p,h,g,v),i.dynCall_viiiiff=(u,p,h,g,v,S,E)=>(i.dynCall_viiiiff=B.dynCall_viiiiff)(u,p,h,g,v,S,E),i.dynCall_viiiiiff=(u,p,h,g,v,S,E,R)=>(i.dynCall_viiiiiff=B.dynCall_viiiiiff)(u,p,h,g,v,S,E,R),i.dynCall_ffff=(u,p,h,g)=>(i.dynCall_ffff=B.dynCall_ffff)(u,p,h,g),i.dynCall_viiff=(u,p,h,g,v)=>(i.dynCall_viiff=B.dynCall_viiff)(u,p,h,g,v),i.dynCall_fffffff=(u,p,h,g,v,S,E)=>(i.dynCall_fffffff=B.dynCall_fffffff)(u,p,h,g,v,S,E),i.dynCall_jjjjjjj=(u,p,h,g,v,S,E)=>(i.dynCall_jjjjjjj=B.dynCall_jjjjjjj)(u,p,h,g,v,S,E),i.dynCall_jjjjjj=(u,p,h,g,v,S)=>(i.dynCall_jjjjjj=B.dynCall_jjjjjj)(u,p,h,g,v,S),i.dynCall_iijjii=(u,p,h,g,v,S)=>(i.dynCall_iijjii=B.dynCall_iijjii)(u,p,h,g,v,S),i.dynCall_viiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe)=>(i.dynCall_viiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe),i.dynCall_viiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z)=>(i.dynCall_viiiiiiiiii=B.dynCall_viiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z),i.dynCall_viiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te)=>(i.dynCall_viiiiiiiiiii=B.dynCall_viiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te),i.dynCall_viiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue)=>(i.dynCall_viiiiiiiiiiii=B.dynCall_viiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue),i.dynCall_viiiiiiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt,Yt)=>(i.dynCall_viiiiiiiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt,Yt),i.dynCall_viiiiiiiii=(u,p,h,g,v,S,E,R,P,q)=>(i.dynCall_viiiiiiiii=B.dynCall_viiiiiiiii)(u,p,h,g,v,S,E,R,P,q),i.dynCall_viiiiiiiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt,Yt,$r)=>(i.dynCall_viiiiiiiiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt,Yt,$r),i.dynCall_viiiiiii=(u,p,h,g,v,S,E,R)=>(i.dynCall_viiiiiii=B.dynCall_viiiiiii)(u,p,h,g,v,S,E,R),i.dynCall_viiiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re)=>(i.dynCall_viiiiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re),i.dynCall_jiji=(u,p,h,g)=>(i.dynCall_jiji=B.dynCall_jiji)(u,p,h,g),i.dynCall_v=u=>(i.dynCall_v=B.dynCall_v)(u),i.dynCall_iidiiii=(u,p,h,g,v,S,E)=>(i.dynCall_iidiiii=B.dynCall_iidiiii)(u,p,h,g,v,S,E),i.dynCall_iiiiiiiii=(u,p,h,g,v,S,E,R,P)=>(i.dynCall_iiiiiiiii=B.dynCall_iiiiiiiii)(u,p,h,g,v,S,E,R,P),i.dynCall_iiij=(u,p,h,g)=>(i.dynCall_iiij=B.dynCall_iiij)(u,p,h,g),i.dynCall_iiiiiiiiii=(u,p,h,g,v,S,E,R,P,q)=>(i.dynCall_iiiiiiiiii=B.dynCall_iiiiiiiiii)(u,p,h,g,v,S,E,R,P,q),i.dynCall_iiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue)=>(i.dynCall_iiiiiiiiiiiii=B.dynCall_iiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue),i.dynCall_iiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z)=>(i.dynCall_iiiiiiiiiii=B.dynCall_iiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z),i.dynCall_ji=(u,p)=>(i.dynCall_ji=B.dynCall_ji)(u,p),i.dynCall_iijii=(u,p,h,g,v)=>(i.dynCall_iijii=B.dynCall_iijii)(u,p,h,g,v),i.dynCall_vij=(u,p,h)=>(i.dynCall_vij=B.dynCall_vij)(u,p,h),i.dynCall_viiijii=(u,p,h,g,v,S,E)=>(i.dynCall_viiijii=B.dynCall_viiijii)(u,p,h,g,v,S,E),i.dynCall_viijiiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt)=>(i.dynCall_viijiiiiiiiiiiiiii=B.dynCall_viijiiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt),i.dynCall_viiiji=(u,p,h,g,v,S)=>(i.dynCall_viiiji=B.dynCall_viiiji)(u,p,h,g,v,S),i.dynCall_fiii=(u,p,h,g)=>(i.dynCall_fiii=B.dynCall_fiii)(u,p,h,g),i.dynCall_viijii=(u,p,h,g,v,S)=>(i.dynCall_viijii=B.dynCall_viijii)(u,p,h,g,v,S),i.dynCall_viij=(u,p,h,g)=>(i.dynCall_viij=B.dynCall_viij)(u,p,h,g),i.dynCall_jiij=(u,p,h,g)=>(i.dynCall_jiij=B.dynCall_jiij)(u,p,h,g),i.dynCall_fi=(u,p)=>(i.dynCall_fi=B.dynCall_fi)(u,p),i.dynCall_fii=(u,p,h)=>(i.dynCall_fii=B.dynCall_fii)(u,p,h),i.dynCall_jii=(u,p,h)=>(i.dynCall_jii=B.dynCall_jii)(u,p,h),i.dynCall_dii=(u,p,h)=>(i.dynCall_dii=B.dynCall_dii)(u,p,h),i.dynCall_fiiii=(u,p,h,g,v)=>(i.dynCall_fiiii=B.dynCall_fiiii)(u,p,h,g,v),i.dynCall_fif=(u,p,h)=>(i.dynCall_fif=B.dynCall_fif)(u,p,h),i.dynCall_jfi=(u,p,h)=>(i.dynCall_jfi=B.dynCall_jfi)(u,p,h),i.dynCall_viiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se)=>(i.dynCall_viiiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se),i.dynCall_viiiiiiiiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt,Yt,$r,Ym)=>(i.dynCall_viiiiiiiiiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt,vt,Yt,$r,Ym),i.dynCall_viiiiiiiiiiiiiiii=(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt)=>(i.dynCall_viiiiiiiiiiiiiiii=B.dynCall_viiiiiiiiiiiiiiii)(u,p,h,g,v,S,E,R,P,q,Z,te,ue,fe,Se,Re,rt),i.dynCall_iif=(u,p,h)=>(i.dynCall_iif=B.dynCall_iif)(u,p,h),i.dynCall_jiiii=(u,p,h,g,v)=>(i.dynCall_jiiii=B.dynCall_jiiii)(u,p,h,g,v),i.dynCall_jiii=(u,p,h,g)=>(i.dynCall_jiii=B.dynCall_jiii)(u,p,h,g),i.dynCall_viif=(u,p,h,g)=>(i.dynCall_viif=B.dynCall_viif)(u,p,h,g),i.dynCall_viiij=(u,p,h,g,v)=>(i.dynCall_viiij=B.dynCall_viiij)(u,p,h,g,v),i.dynCall_viiiijii=(u,p,h,g,v,S,E,R)=>(i.dynCall_viiiijii=B.dynCall_viiiijii)(u,p,h,g,v,S,E,R),i.dynCall_iiiiij=(u,p,h,g,v,S)=>(i.dynCall_iiiiij=B.dynCall_iiiiij)(u,p,h,g,v,S),i.dynCall_iiiiid=(u,p,h,g,v,S)=>(i.dynCall_iiiiid=B.dynCall_iiiiid)(u,p,h,g,v,S),i.dynCall_iiiiijj=(u,p,h,g,v,S,E)=>(i.dynCall_iiiiijj=B.dynCall_iiiiijj)(u,p,h,g,v,S,E),i.dynCall_iiiiiijj=(u,p,h,g,v,S,E,R)=>(i.dynCall_iiiiiijj=B.dynCall_iiiiiijj)(u,p,h,g,v,S,E,R);var As=u=>(As=B.wb)(u),Os=()=>(Os=B.xb)(),Rs=u=>(Rs=B.yb)(u),Bs=()=>(Bs=B.zb)();return(function u(){if(0<jt)Ft=u;else if(o)t(i),mi();else{for(;0<er.length;)er.shift()(i);0<jt?Ft=u:(i.calledRun=!0,Y||(mi(),t(i)))}})(),i.PTR_SIZE=4,a},Qd=kr,Ds=globalThis.self?.name?.startsWith("em-pthread"),Ds&&kr()}),Er,$a,Ns,Be,Jd,Ii,Ps,Us,zr,Ws,Ar,ep,Or,tp,Ka=L(()=>{Ha(),Er=typeof location>"u"?void 0:location.origin,$a=import.meta.url>"file:"&&import.meta.url<"file;",Ns=()=>{{if($a){let e=URL;return new URL(new e("ort.bundle.min.mjs",import.meta.url).href,Er).href}return import.meta.url}},Be=Ns(),Jd=()=>{if(Be&&!Be.startsWith("blob:"))return Be.substring(0,Be.lastIndexOf("/")+1)},Ii=(e,t)=>{try{let r=t??Be;return(r?new URL(e,r):new URL(e)).origin===Er}catch{return!1}},Ps=(e,t)=>{let r=t??Be;try{return(r?new URL(e,r):new URL(e)).href}catch{return}},Us=(e,t)=>`${t??"./"}${e}`,zr=async e=>{let t=await(await fetch(e,{credentials:"same-origin"})).blob();return URL.createObjectURL(t)},Ws=async e=>(await import(e)).default,Ar=(bg(),ci(Yd)).default,ep=async()=>{if(!Be)throw new Error("Failed to load proxy worker: cannot determine the script source URL.");if(Ii(Be))return[void 0,Ar()];let e=await zr(Be);return[e,Ar(e)]},Or=(wg(),ci(Xd)).default,tp=async(e,t,r,i)=>{let a=Or&&!(e||t);if(a)if(Be)a=Ii(Be);else if(i&&!r)a=!0;else throw new Error("cannot determine the script source URL.");if(a)return[void 0,Or];{let n="ort-wasm-simd-threaded.jsep.mjs",s=e??Ps(n,t),o=r&&s&&!Ii(s,t),l=o?await zr(s):s??Us(n,t);return[o?l:void 0,await Ws(l)]}}}),Rr,ki,Xt,Br,Ls,qs,Vs,Ya,_e,Rt=L(()=>{Ka(),ki=!1,Xt=!1,Br=!1,Ls=()=>{if(typeof SharedArrayBuffer>"u")return!1;try{return typeof MessageChannel<"u"&&new MessageChannel().port1.postMessage(new SharedArrayBuffer(1)),WebAssembly.validate(new Uint8Array([0,97,115,109,1,0,0,0,1,4,1,96,0,0,3,2,1,0,5,4,1,3,1,1,10,11,1,9,0,65,0,254,16,2,0,26,11]))}catch{return!1}},qs=()=>{try{return WebAssembly.validate(new Uint8Array([0,97,115,109,1,0,0,0,1,4,1,96,0,0,3,2,1,0,10,30,1,28,0,65,0,253,15,253,12,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,253,186,1,26,11]))}catch{return!1}},Vs=()=>{try{return WebAssembly.validate(new Uint8Array([0,97,115,109,1,0,0,0,1,5,1,96,0,1,123,3,2,1,0,10,19,1,17,0,65,1,253,15,65,2,253,15,65,3,253,15,253,147,2,11]))}catch{return!1}},Ya=async e=>{if(ki)return Promise.resolve();if(Xt)throw new Error("multiple calls to 'initializeWebAssembly()' detected.");if(Br)throw new Error("previous call to 'initializeWebAssembly()' failed.");Xt=!0;let t=e.initTimeout,r=e.numThreads;if(e.simd!==!1){if(e.simd==="relaxed"){if(!Vs())throw new Error("Relaxed WebAssembly SIMD is not supported in the current environment.")}else if(!qs())throw new Error("WebAssembly SIMD is not supported in the current environment.")}let i=Ls();r>1&&!i&&(typeof self<"u"&&!self.crossOriginIsolated&&console.warn("env.wasm.numThreads is set to "+r+", but this will not work unless you enable crossOriginIsolated mode. See https://web.dev/cross-origin-isolation-guide/ for more info."),console.warn("WebAssembly multi-threading is not supported in the current environment. Falling back to single-threading."),e.numThreads=r=1);let a=e.wasmPaths,n=typeof a=="string"?a:void 0,s=a?.mjs,o=s?.href??s,l=a?.wasm,d=l?.href??l,c=e.wasmBinary,[f,m]=await tp(o,n,r>1,!!c||!!d),y=!1,_=[];if(t>0&&_.push(new Promise(b=>{setTimeout(()=>{y=!0,b()},t)})),_.push(new Promise((b,x)=>{let $={numThreads:r};if(c)$.wasmBinary=c;else if(d||n)$.locateFile=w=>d??n+w;else if(o&&o.indexOf("blob:")!==0)$.locateFile=w=>new URL(w,o).href;else if(f){let w=Jd();w&&($.locateFile=T=>w+T)}m($).then(w=>{Xt=!1,ki=!0,Rr=w,b(),f&&URL.revokeObjectURL(f)},w=>{Xt=!1,Br=!0,x(w)})})),await Promise.race(_),y)throw new Error(`WebAssembly backend initializing failed due to timeout: ${t}ms`)},_e=()=>{if(ki&&Rr)return Rr;throw new Error("WebAssembly is not initialized yet.")}}),Ge,Vi,me,Za=L(()=>{Rt(),Ge=(e,t)=>{let r=_e(),i=r.lengthBytesUTF8(e)+1,a=r._malloc(i);return r.stringToUTF8(e,a,i),t.push(a),a},Vi=(e,t,r,i)=>{if(typeof e=="object"&&e!==null){if(r.has(e))throw new Error("Circular reference in options");r.add(e)}Object.entries(e).forEach(([a,n])=>{let s=t?t+a:a;if(typeof n=="object")Vi(n,s+".",r,i);else if(typeof n=="string"||typeof n=="number")i(s,n.toString());else if(typeof n=="boolean")i(s,n?"1":"0");else throw new Error(`Can't handle extra config type: ${typeof n}`)})},me=e=>{let t=_e(),r=t.stackSave();try{let i=t.PTR_SIZE,a=t.stackAlloc(2*i);t._OrtGetLastError(a,a+i);let n=Number(t.getValue(a,i===4?"i32":"i64")),s=t.getValue(a+i,"*"),o=s?t.UTF8ToString(s):"";throw new Error(`${e} ERROR_CODE: ${n}, ERROR_MESSAGE: ${o}`)}finally{t.stackRestore(r)}}}),ip,vg=L(()=>{Rt(),Za(),ip=e=>{let t=_e(),r=0,i=[],a=e||{};try{if(e?.logSeverityLevel===void 0)a.logSeverityLevel=2;else if(typeof e.logSeverityLevel!="number"||!Number.isInteger(e.logSeverityLevel)||e.logSeverityLevel<0||e.logSeverityLevel>4)throw new Error(`log severity level is not valid: ${e.logSeverityLevel}`);if(e?.logVerbosityLevel===void 0)a.logVerbosityLevel=0;else if(typeof e.logVerbosityLevel!="number"||!Number.isInteger(e.logVerbosityLevel))throw new Error(`log verbosity level is not valid: ${e.logVerbosityLevel}`);e?.terminate===void 0&&(a.terminate=!1);let n=0;return e?.tag!==void 0&&(n=Ge(e.tag,i)),r=t._OrtCreateRunOptions(a.logSeverityLevel,a.logVerbosityLevel,!!a.terminate,n),r===0&&me("Can't create run options."),e?.extra!==void 0&&Vi(e.extra,"",new WeakSet,(s,o)=>{let l=Ge(s,i),d=Ge(o,i);t._OrtAddRunConfigEntry(r,l,d)!==0&&me(`Can't set a run config entry: ${s} - ${o}.`)}),[r,i]}catch(n){throw r!==0&&t._OrtReleaseRunOptions(r),i.forEach(s=>t._free(s)),n}}}),js,Fs,Gs,Qt,Hs,rp,$g=L(()=>{Rt(),Za(),js=e=>{switch(e){case"disabled":return 0;case"basic":return 1;case"extended":return 2;case"layout":return 3;case"all":return 99;default:throw new Error(`unsupported graph optimization level: ${e}`)}},Fs=e=>{switch(e){case"sequential":return 0;case"parallel":return 1;default:throw new Error(`unsupported execution mode: ${e}`)}},Gs=e=>{e.extra||(e.extra={}),e.extra.session||(e.extra.session={});let t=e.extra.session;t.use_ort_model_bytes_directly||(t.use_ort_model_bytes_directly="1"),e.executionProviders&&e.executionProviders.some(r=>(typeof r=="string"?r:r.name)==="webgpu")&&(e.enableMemPattern=!1)},Qt=(e,t,r,i)=>{let a=Ge(t,i),n=Ge(r,i);_e()._OrtAddSessionConfigEntry(e,a,n)!==0&&me(`Can't set a session config entry: ${t} - ${r}.`)},Hs=async(e,t,r)=>{for(let i of t){let a=typeof i=="string"?i:i.name,n=[];switch(a){case"webnn":if(a="WEBNN",typeof i!="string"){let c=i?.deviceType;c&&Qt(e,"deviceType",c,r)}break;case"webgpu":if(a="JS",typeof i!="string"){let c=i;if(c?.preferredLayout){if(c.preferredLayout!=="NCHW"&&c.preferredLayout!=="NHWC")throw new Error(`preferredLayout must be either 'NCHW' or 'NHWC': ${c.preferredLayout}`);Qt(e,"preferredLayout",c.preferredLayout,r)}}break;case"wasm":case"cpu":continue;default:throw new Error(`not supported execution provider: ${a}`)}let s=Ge(a,r),o=n.length,l=0,d=0;if(o>0){l=_e()._malloc(o*_e().PTR_SIZE),r.push(l),d=_e()._malloc(o*_e().PTR_SIZE),r.push(d);for(let c=0;c<o;c++)_e().setValue(l+c*_e().PTR_SIZE,n[c][0],"*"),_e().setValue(d+c*_e().PTR_SIZE,n[c][1],"*")}await _e()._OrtAppendExecutionProvider(e,s,l,d,o)!==0&&me(`Can't append execution provider: ${a}.`)}},rp=async e=>{let t=_e(),r=0,i=[],a=e||{};Gs(a);try{let n=js(a.graphOptimizationLevel??"all"),s=Fs(a.executionMode??"sequential"),o=typeof a.logId=="string"?Ge(a.logId,i):0,l=a.logSeverityLevel??2;if(!Number.isInteger(l)||l<0||l>4)throw new Error(`log severity level is not valid: ${l}`);let d=a.logVerbosityLevel??0;if(!Number.isInteger(d)||d<0||d>4)throw new Error(`log verbosity level is not valid: ${d}`);let c=typeof a.optimizedModelFilePath=="string"?Ge(a.optimizedModelFilePath,i):0;if(r=t._OrtCreateSessionOptions(n,!!a.enableCpuMemArena,!!a.enableMemPattern,s,!!a.enableProfiling,0,o,l,d,c),r===0&&me("Can't create session options."),a.executionProviders&&await Hs(r,a.executionProviders,i),a.enableGraphCapture!==void 0){if(typeof a.enableGraphCapture!="boolean")throw new Error(`enableGraphCapture must be a boolean value: ${a.enableGraphCapture}`);Qt(r,"enableGraphCapture",a.enableGraphCapture.toString(),i)}if(a.freeDimensionOverrides)for(let[f,m]of Object.entries(a.freeDimensionOverrides)){if(typeof f!="string")throw new Error(`free dimension override name must be a string: ${f}`);if(typeof m!="number"||!Number.isInteger(m)||m<0)throw new Error(`free dimension override value must be a non-negative integer: ${m}`);let y=Ge(f,i);t._OrtAddFreeDimensionOverride(r,y,m)!==0&&me(`Can't set a free dimension override: ${f} - ${m}.`)}return a.extra!==void 0&&Vi(a.extra,"",new WeakSet,(f,m)=>{Qt(r,f,m,i)}),[r,i]}catch(n){throw r!==0&&t._OrtReleaseSessionOptions(r)!==0&&me("Can't release session options."),i.forEach(s=>t._free(s)),n}}}),It,nt,kt,Xi,ji,Xa,Qa,xa,ie=L(()=>{It=e=>{switch(e){case"int8":return 3;case"uint8":return 2;case"bool":return 9;case"int16":return 5;case"uint16":return 4;case"int32":return 6;case"uint32":return 12;case"float16":return 10;case"float32":return 1;case"float64":return 11;case"string":return 8;case"int64":return 7;case"uint64":return 13;case"int4":return 22;case"uint4":return 21;default:throw new Error(`unsupported data type: ${e}`)}},nt=e=>{switch(e){case 3:return"int8";case 2:return"uint8";case 9:return"bool";case 5:return"int16";case 4:return"uint16";case 6:return"int32";case 12:return"uint32";case 10:return"float16";case 1:return"float32";case 11:return"float64";case 8:return"string";case 7:return"int64";case 13:return"uint64";case 22:return"int4";case 21:return"uint4";default:throw new Error(`unsupported data type: ${e}`)}},kt=(e,t)=>{let r=[-1,4,1,1,2,2,4,8,-1,1,2,8,4,8,-1,-1,-1,-1,-1,-1,-1,.5,.5][e],i=typeof t=="number"?t:t.reduce((a,n)=>a*n,1);return r>0?Math.ceil(i*r):void 0},Xi=e=>{switch(e){case"float16":return typeof Float16Array<"u"&&Float16Array.from?Float16Array:Uint16Array;case"float32":return Float32Array;case"uint8":return Uint8Array;case"int8":return Int8Array;case"uint16":return Uint16Array;case"int16":return Int16Array;case"int32":return Int32Array;case"bool":return Uint8Array;case"float64":return Float64Array;case"uint32":return Uint32Array;case"int64":return BigInt64Array;case"uint64":return BigUint64Array;default:throw new Error(`unsupported type: ${e}`)}},ji=e=>{switch(e){case"verbose":return 0;case"info":return 1;case"warning":return 2;case"error":return 3;case"fatal":return 4;default:throw new Error(`unsupported logging level: ${e}`)}},Xa=e=>e==="float32"||e==="float16"||e==="int32"||e==="int64"||e==="uint32"||e==="uint8"||e==="bool"||e==="uint4"||e==="int4",Qa=e=>e==="float32"||e==="float16"||e==="int32"||e==="int64"||e==="uint32"||e==="uint64"||e==="int8"||e==="uint8"||e==="bool"||e==="uint4"||e==="int4",xa=e=>{switch(e){case"none":return 0;case"cpu":return 1;case"cpu-pinned":return 2;case"texture":return 3;case"gpu-buffer":return 4;case"ml-tensor":return 5;default:throw new Error(`unsupported data location: ${e}`)}}}),Ja,ap=L(()=>{Ha(),Ja=async e=>{if(typeof e=="string"){let t=await fetch(e);if(!t.ok)throw new Error(`failed to load external data file: ${e}`);let r=t.headers.get("Content-Length"),i=r?parseInt(r,10):0;if(i<1073741824)return new Uint8Array(await t.arrayBuffer());{if(!t.body)throw new Error(`failed to load external data file: ${e}, no response body.`);let a=t.body.getReader(),n;try{n=new ArrayBuffer(i)}catch(o){if(o instanceof RangeError){let l=Math.ceil(i/65536);n=new WebAssembly.Memory({initial:l,maximum:l}).buffer}else throw o}let s=0;for(;;){let{done:o,value:l}=await a.read();if(o)break;let d=l.byteLength;new Uint8Array(n,s,d).set(l),s+=d}return new Uint8Array(n,0,i)}}else return e instanceof Blob?new Uint8Array(await e.arrayBuffer()):e instanceof Uint8Array?e:new Uint8Array(e)}}),Ks,Ys,Zs,Xs,en,Qs,de,st=L(()=>{ie(),Ks=["V","I","W","E","F"],Ys=(e,t)=>{console.log(`[${Ks[e]},${new Date().toISOString()}]${t}`)},en=(e,t)=>{Zs=e,Xs=t},Qs=(e,t)=>{let r=ji(e),i=ji(Zs);r>=i&&Ys(r,typeof t=="function"?t():t)},de=(...e)=>{Xs&&Qs(...e)}}),Js,Wt,O,Fi,np,sp,op,se=L(()=>{Js=class{static calcMatMulShape(e,t){return e[1]!==t[0]?void 0:[e[0],t[1]]}},Wt=class{static calcShape(e,t,r=!1){let i=e.length,a=t.length;if(i===0)return t;if(a===0)return e;let n=Math.max(e.length,t.length),s=new Array(n);if(r){if(i<2||a<2)return;let o=Js.calcMatMulShape([e[i-2],e[i-1]],[t[a-2],t[a-1]]);if(o===void 0)return;[s[n-2],s[n-1]]=o}for(let o=r?3:1;o<=n;o++){let l=i-o<0?1:e[i-o],d=a-o<0?1:t[a-o];if(l!==d&&l>1&&d>1)return;let c=Math.max(l,d);if(l&&d)s[n-o]=Math.max(l,d);else{if(c>1)return;s[n-o]=0}}return s}static isValidBroadcast(e,t){let r=e.length,i=t.length;if(r>i)return!1;for(let a=1;a<=r;a++)if(e[r-a]!==1&&e[r-a]!==t[i-a])return!1;return!0}},O=class Wi{static size(t){return Wi.getSizeFromDimensionRange(t,0,t.length)}static convertShape(t,r=4){let i=t.length;if(i===0)return[];let a=new Array(i),n=i-1;for(;n>=0;){if(t[n]%r===0){a[n]=t[n]/r;break}if(r%t[n]!==0)throw new Error("cannot convert shape");a[n]=1,r/=t[n],n--}for(n--;n>=0;n--)a[n]=t[n];return a}static sizeFromDimension(t,r){if(r<0||r>t.length)throw new Error(`invalid dimension of ${r} for sizeFromDimension as Tensor has ${t.length} dimensions.`);return Wi.getSizeFromDimensionRange(t,r,t.length)}static sizeToDimension(t,r){if(r<0||r>t.length)throw new Error(`invalid dimension of ${r} for sizeToDimension as Tensor has ${t.length} dimensions.`);return Wi.getSizeFromDimensionRange(t,0,r)}static getSizeFromDimensionRange(t,r,i){let a=1;for(let n=r;n<i;n++){if(t[n]<0)throw new Error("cannot get valid size from specified dimension range. Most likely the range contains negative values in them.");a*=Number(t[n])}return a}static computeStrides(t){let r=t.length;if(r===0)return[];if(r===1)return[1];let i=new Array(r);i[r-1]=1,i[r-2]=t[r-1];for(let a=r-3;a>=0;--a)i[a]=i[a+1]*t[a+1];return i}static normalizeAxis(t,r){if(t<-r&&t>=r)throw new Error("unsupported axis for this operation.");return t<0?t+r:t}static normalizeAxes(t,r){return t.map(i=>this.normalizeAxis(i,r??t.length))}static sortBasedOnPerm(t,r){return r?r.map(i=>t[i]):t.slice().reverse()}static padShape(t,r){let i=t.length;return t.map((a,n)=>a+r[n]+r[n+i])}static areEqual(t,r){return t.length!==r.length?!1:t.every((i,a)=>i===r[a])}},Fi=class ui{static adjustPoolAttributes(t,r,i,a,n,s){if(!t&&i.length!==r.length-2)throw new Error("length of specified kernel shapes should be 2 less than length of input dimensions");if(t)for(let o=0;o<r.length-2;o++)o>=i.length?i.push(r[o+2]):i[o]=r[o+2];for(let o=0;o<i.length;o++)if(o<a.length){if(a[o]<0)throw new Error("strides should be greater than or equal to 1")}else a.push(1);for(let o=0;o<i.length;o++)if(o<n.length){if(n[o]<0)throw new Error("dilations should be greater than or equal to 1")}else n.push(1);for(let o=0;o<i.length*2;o++)if(o<s.length){if(s[o]<0)throw new Error("pad should be greater than or equal to 1")}else s.push(0);for(let o=0;o<i.length;o++){if(i[o]<=0)throw new Error("kernel shapes need to be greater than 0");if(s[o]>=i[o]||s[o+i.length]>=i[o])throw new Error("pads should be smaller than kernel")}}static adjustPadsBasedOnAutoPad(t,r,i,a,n,s,o){if(o){if(n.length!==2*(t.length-2))throw new Error("length of pads should be twice the length of data dimensions");if(r.length!==t.length-2)throw new Error("length of strides should be the length of data dimensions");if(a.length!==t.length-2)throw new Error("length of kernel shapes should be the length of data dimensions");for(let l=0;l<t.length-2;l++)ui.adjustPadAndReturnShape(t[l+(s?1:2)],r[l],i[l],a[l],n,l,l+t.length-2,o)}}static computePoolOutputShape(t,r,i,a,n,s,o){if(r.length<=0)throw new Error("input shape must be of size greater than 0");let l=[r[0],r[1]];return ui.computeShapeHelper(t,r,l,i,a,n,s,o),l}static computeConvOutputShape(t,r,i,a,n,s,o){if(t.length<=0||r.length<=0)throw new Error("invalid input tensor dims or invalid filter tensor dims");let l=[t[0],r[0]];return ui.computeShapeHelper(!1,t,l,i,a,n,s,o),l}static computeShapeHelper(t,r,i,a,n,s,o,l){if(t)for(let d=0;d<r.length-2;d++)i.push(1);else for(let d=0;d<r.length-2;d++)i.push(ui.adjustPadAndReturnShape(r[d+2],a[d],n[d],s[d],o,d,d+r.length-2,l))}static adjustPadAndReturnShape(t,r,i,a,n,s,o,l){let d=i*(a-1)+1;if(l&&l!=="NOTSET")switch(l){case"VALID":return n[s]=0,n[o]=0,Math.floor((t-d)/r+1);case"SAME_LOWER":case"SAME_UPPER":if(i!==1)throw new Error("Dilation not supported for SAME_UPPER or SAME_LOWER");{let c=((t+r-1)/r-1)*r+a-t;return n[s]=Math.floor(l==="SAME_LOWER"?(c+1)/2:c/2),n[o]=c-n[s],Math.floor((t+c-a)/r+1)}default:throw new Error("Unsupported AutoPad type")}else return Math.floor((t+n[s]+n[o]-d)/r+1)}},np=class{static getShapeOfGemmResult(e,t,r,i,a){if(e.length!==2||r.length!==2)throw new Error("shape need to be of size 2");let n,s,o;t?(n=e[1],s=e[0]):(n=e[0],s=e[1]);let l=-1;if(i?(o=r[0],l=1):(o=r[1],l=0),r[l]!==s)throw new Error("dimension mismatch");if(n<=0||o<=0||s<=0)throw new Error("invalid shape specified");if(a&&!Wt.isValidBroadcast(a,[n,o]))throw new Error("gemm: invalid bias shape for broadcast");return[n,o,s]}},sp=-34028234663852886e22,op=34028234663852886e22}),tn,up=L(()=>{ie(),tn=(e,t)=>new(Xi(t))(e)}),Mr,Ca,Dr,eo,Nr,to,Pr,Ur,Wr,io,lp,xg=L(()=>{ie(),st(),Mr=new Map([["float32",32],["float16",16],["int32",32],["uint32",32],["int64",64],["uint64",64],["int8",8],["uint8",8],["int4",4],["uint4",4]]),Ca=(e,t)=>{if(t==="int32")return e;let r=Mr.get(t);if(!r)throw new Error(`WebNN backend does not support data type: ${t}`);let i=r/8;if(e.byteLength%i!==0)throw new Error(`Invalid Uint8Array length - must be a multiple of ${i}.`);let a=e.byteLength/i,n=new(Xi(t))(e.buffer,e.byteOffset,a);switch(t){case"int64":case"uint64":{let s=new Int32Array(a);for(let o=0;o<a;o++){let l=n[o];if(l>2147483647n||l<-2147483648n)throw new Error("Can not convert int64 data to int32 - value out of range.");s[o]=Number(l)}return new Uint8Array(s.buffer)}case"int8":case"uint8":case"uint32":{if(t==="uint32"&&n.some(o=>o>2147483647))throw new Error("Can not convert uint32 data to int32 - value out of range.");let s=Int32Array.from(n,Number);return new Uint8Array(s.buffer)}default:throw new Error(`Unsupported data conversion from ${t} to 'int32'`)}},Dr=(e,t)=>{if(t==="int32")return e;if(e.byteLength%4!==0)throw new Error("Invalid Uint8Array length - must be a multiple of 4 (int32).");let r=e.byteLength/4,i=new Int32Array(e.buffer,e.byteOffset,r);switch(t){case"int64":{let a=BigInt64Array.from(i,BigInt);return new Uint8Array(a.buffer)}case"uint64":{if(i.some(n=>n<0))throw new Error("Can not convert int32 data to uin64 - negative value found.");let a=BigUint64Array.from(i,BigInt);return new Uint8Array(a.buffer)}case"int8":{if(i.some(n=>n<-128||n>127))throw new Error("Can not convert int32 data to int8 - value out of range.");let a=Int8Array.from(i,Number);return new Uint8Array(a.buffer)}case"uint8":{if(i.some(a=>a<0||a>255))throw new Error("Can not convert int32 data to uint8 - value out of range.");return Uint8Array.from(i,Number)}case"uint32":{if(i.some(n=>n<0))throw new Error("Can not convert int32 data to uint32 - negative value found.");let a=Uint32Array.from(i,Number);return new Uint8Array(a.buffer)}default:throw new Error(`Unsupported data conversion from 'int32' to ${t}`)}},eo=1,Nr=()=>eo++,to=new Map([["int8","int32"],["uint8","int32"],["uint32","int32"],["int64","int32"]]),Pr=(e,t)=>{let r=Mr.get(e);if(!r)throw new Error(`WebNN backend does not support data type: ${e}`);return t.length>0?Math.ceil(t.reduce((i,a)=>i*a)*r/8):0},Ur=class{constructor(e){this.isDataConverted=!1;let{sessionId:t,context:r,tensor:i,dataType:a,shape:n,fallbackDataType:s}=e;this.sessionId=t,this.mlContext=r,this.mlTensor=i,this.dataType=a,this.tensorShape=n,this.fallbackDataType=s}get tensor(){return this.mlTensor}get type(){return this.dataType}get fallbackType(){return this.fallbackDataType}get shape(){return this.tensorShape}get byteLength(){return Pr(this.dataType,this.tensorShape)}destroy(){de("verbose",()=>"[WebNN] TensorWrapper.destroy"),this.mlTensor.destroy()}write(e){this.mlContext.writeTensor(this.mlTensor,e)}async read(e){if(this.fallbackDataType){let t=await this.mlContext.readTensor(this.mlTensor),r=Dr(new Uint8Array(t),this.dataType);if(e){(e instanceof ArrayBuffer?new Uint8Array(e):new Uint8Array(e.buffer,e.byteOffset,e.byteLength)).set(r);return}else return r.buffer}else return e?this.mlContext.readTensor(this.mlTensor,e):this.mlContext.readTensor(this.mlTensor)}canReuseTensor(e,t,r){return this.mlContext===e&&this.dataType===t&&this.tensorShape.length===r.length&&this.tensorShape.every((i,a)=>i===r[a])}setIsDataConverted(e){this.isDataConverted=e}},Wr=class{constructor(e,t){this.tensorManager=e,this.wrapper=t}get tensorWrapper(){return this.wrapper}releaseTensor(){this.tensorWrapper&&(this.tensorManager.releaseTensor(this.tensorWrapper),this.wrapper=void 0)}async ensureTensor(e,t,r,i){let a=this.tensorManager.getMLContext(e),n;if(!a.opSupportLimits().input.dataTypes.includes(t)){if(n=to.get(t),!n||!a.opSupportLimits().input.dataTypes.includes(n))throw new Error(`WebNN backend does not support data type: ${t}`);de("verbose",()=>`[WebNN] TensorIdTracker.ensureTensor: fallback dataType from ${t} to ${n}`)}if(this.wrapper){if(this.wrapper.canReuseTensor(a,t,r))return this.wrapper.tensor;if(i){if(this.wrapper.byteLength!==Pr(t,r))throw new Error("Unable to copy data to tensor with different size.");this.activeUpload=new Uint8Array(await this.wrapper.read())}this.tensorManager.releaseTensor(this.wrapper)}let s=typeof MLTensorUsage>"u"?void 0:MLTensorUsage.READ|MLTensorUsage.WRITE;return this.wrapper=await this.tensorManager.getCachedTensor(e,t,r,s,!0,!0,n),i&&this.activeUpload&&(this.wrapper.write(this.activeUpload),this.activeUpload=void 0),this.wrapper.tensor}upload(e){let t=e;if(this.wrapper){if(this.wrapper.fallbackType)if(this.wrapper.fallbackType==="int32")t=Ca(e,this.wrapper.type),this.wrapper.setIsDataConverted(!0);else throw new Error(`Unsupported fallback data type: ${this.wrapper.fallbackType}`);if(e.byteLength===this.wrapper.byteLength){this.wrapper.write(t);return}else de("verbose",()=>"Data size does not match tensor size. Releasing tensor."),this.releaseTensor()}this.activeUpload?this.activeUpload.set(t):this.activeUpload=new Uint8Array(t)}async download(e){if(this.activeUpload){let t=this.wrapper?.isDataConverted?Dr(this.activeUpload,this.wrapper?.type):this.activeUpload;if(e){e instanceof ArrayBuffer?new Uint8Array(e).set(t):new Uint8Array(e.buffer,e.byteOffset,e.byteLength).set(t);return}else return t.buffer}if(!this.wrapper)throw new Error("Tensor has not been created.");return e?this.wrapper.read(e):this.wrapper.read()}},io=class{constructor(e){this.backend=e,this.tensorTrackersById=new Map,this.freeTensors=[],this.externalTensors=new Set}getMLContext(e){let t=this.backend.getMLContext(e);if(!t)throw new Error("MLContext not found for session.");return t}reserveTensorId(){let e=Nr();return this.tensorTrackersById.set(e,new Wr(this)),e}releaseTensorId(e){let t=this.tensorTrackersById.get(e);t&&(this.tensorTrackersById.delete(e),t.tensorWrapper&&this.releaseTensor(t.tensorWrapper))}async ensureTensor(e,t,r,i,a){de("verbose",()=>`[WebNN] TensorManager.ensureTensor {tensorId: ${t}, dataType: ${r}, shape: ${i}, copyOld: ${a}}`);let n=this.tensorTrackersById.get(t);if(!n)throw new Error("Tensor not found.");return n.ensureTensor(e,r,i,a)}upload(e,t){let r=this.tensorTrackersById.get(e);if(!r)throw new Error("Tensor not found.");r.upload(t)}async download(e,t){de("verbose",()=>`[WebNN] TensorManager.download {tensorId: ${e}, dstBuffer: ${t?.byteLength}}`);let r=this.tensorTrackersById.get(e);if(!r)throw new Error("Tensor not found.");return r.download(t)}releaseTensorsForSession(e){for(let t of this.freeTensors)t.sessionId===e&&t.destroy();this.freeTensors=this.freeTensors.filter(t=>t.sessionId!==e)}registerTensor(e,t,r,i){let a=this.getMLContext(e),n=Nr(),s=new Ur({sessionId:e,context:a,tensor:t,dataType:r,shape:i});return this.tensorTrackersById.set(n,new Wr(this,s)),this.externalTensors.add(s),n}async getCachedTensor(e,t,r,i,a,n,s){let o=this.getMLContext(e);for(let[d,c]of this.freeTensors.entries())if(c.canReuseTensor(o,t,r)){de("verbose",()=>`[WebNN] Reusing tensor {dataType: ${t}, ${s?`fallbackDataType: ${s},`:""} shape: ${r}`);let f=this.freeTensors.splice(d,1)[0];return f.sessionId=e,f}de("verbose",()=>`[WebNN] MLContext.createTensor {dataType: ${t}, ${s?`fallbackDataType: ${s},`:""} shape: ${r}}`);let l=await o.createTensor({dataType:s??t,shape:r,dimensions:r,usage:i,writable:a,readable:n});return new Ur({sessionId:e,context:o,tensor:l,dataType:t,shape:r,fallbackDataType:s})}releaseTensor(e){this.externalTensors.has(e)&&this.externalTensors.delete(e),this.freeTensors.push(e)}},lp=(...e)=>new io(...e)}),Jt,ro,dp,Cg=L(()=>{ie(),Rt(),up(),xg(),st(),Jt=new Map([[1,"float32"],[10,"float16"],[6,"int32"],[12,"uint32"],[7,"int64"],[13,"uint64"],[22,"int4"],[21,"uint4"],[3,"int8"],[2,"uint8"],[9,"uint8"]]),ro=(e,t)=>{if(e===t)return!0;if(e===void 0||t===void 0)return!1;let r=Object.keys(e).sort(),i=Object.keys(t).sort();return r.length===i.length&&r.every((a,n)=>a===i[n]&&e[a]===t[a])},dp=class{constructor(e){this.tensorManager=lp(this),this.mlContextBySessionId=new Map,this.sessionIdsByMLContext=new Map,this.mlContextCache=[],this.sessionGraphInputs=new Map,this.sessionGraphOutputs=new Map,this.temporaryGraphInputs=[],this.temporaryGraphOutputs=[],this.temporarySessionTensorIds=new Map,en(e.logLevel,!!e.debug)}get currentSessionId(){if(this.activeSessionId===void 0)throw new Error("No active session");return this.activeSessionId}onRunStart(e){de("verbose",()=>`[WebNN] onRunStart {sessionId: ${e}}`),this.activeSessionId=e}onRunEnd(e){de("verbose",()=>`[WebNN] onRunEnd {sessionId: ${e}}`);let t=this.temporarySessionTensorIds.get(e);if(t){for(let r of t)de("verbose",()=>`[WebNN] releasing temporary tensor {tensorId: ${r}}`),this.tensorManager.releaseTensorId(r);this.temporarySessionTensorIds.delete(e),this.activeSessionId=void 0}}async createMLContext(e){if(e instanceof GPUDevice){let r=this.mlContextCache.findIndex(i=>i.gpuDevice===e);if(r!==-1)return this.mlContextCache[r].mlContext;{let i=await navigator.ml.createContext(e);return this.mlContextCache.push({gpuDevice:e,mlContext:i}),i}}else if(e===void 0){let r=this.mlContextCache.findIndex(i=>i.options===void 0&&i.gpuDevice===void 0);if(r!==-1)return this.mlContextCache[r].mlContext;{let i=await navigator.ml.createContext();return this.mlContextCache.push({mlContext:i}),i}}let t=this.mlContextCache.findIndex(r=>ro(r.options,e));if(t!==-1)return this.mlContextCache[t].mlContext;{let r=await navigator.ml.createContext(e);return this.mlContextCache.push({options:e,mlContext:r}),r}}registerMLContext(e,t){this.mlContextBySessionId.set(e,t);let r=this.sessionIdsByMLContext.get(t);r||(r=new Set,this.sessionIdsByMLContext.set(t,r)),r.add(e),this.temporaryGraphInputs.length>0&&(this.sessionGraphInputs.set(e,this.temporaryGraphInputs),this.temporaryGraphInputs=[]),this.temporaryGraphOutputs.length>0&&(this.sessionGraphOutputs.set(e,this.temporaryGraphOutputs),this.temporaryGraphOutputs=[])}onReleaseSession(e){this.sessionGraphInputs.delete(e),this.sessionGraphOutputs.delete(e);let t=this.mlContextBySessionId.get(e);if(!t)return;this.tensorManager.releaseTensorsForSession(e),this.mlContextBySessionId.delete(e);let r=this.sessionIdsByMLContext.get(t);if(r.delete(e),r.size===0){this.sessionIdsByMLContext.delete(t);let i=this.mlContextCache.findIndex(a=>a.mlContext===t);i!==-1&&this.mlContextCache.splice(i,1)}}getMLContext(e){return this.mlContextBySessionId.get(e)}reserveTensorId(){return this.tensorManager.reserveTensorId()}releaseTensorId(e){de("verbose",()=>`[WebNN] releaseTensorId {tensorId: ${e}}`),this.tensorManager.releaseTensorId(e)}async ensureTensor(e,t,r,i,a){let n=Jt.get(r);if(!n)throw new Error(`Unsupported ONNX data type: ${r}`);return this.tensorManager.ensureTensor(e??this.currentSessionId,t,n,i,a)}async createTemporaryTensor(e,t,r){de("verbose",()=>`[WebNN] createTemporaryTensor {onnxDataType: ${t}, shape: ${r}}`);let i=Jt.get(t);if(!i)throw new Error(`Unsupported ONNX data type: ${t}`);let a=this.tensorManager.reserveTensorId();await this.tensorManager.ensureTensor(e,a,i,r,!1);let n=this.temporarySessionTensorIds.get(e);return n?n.push(a):this.temporarySessionTensorIds.set(e,[a]),a}uploadTensor(e,t){if(!_e().shouldTransferToMLTensor)throw new Error("Trying to upload to a MLTensor while shouldTransferToMLTensor is false");de("verbose",()=>`[WebNN] uploadTensor {tensorId: ${e}, data: ${t.byteLength}}`),this.tensorManager.upload(e,t)}async downloadTensor(e,t){return this.tensorManager.download(e,t)}createMLTensorDownloader(e,t){return async()=>{let r=await this.tensorManager.download(e);return tn(r,t)}}registerMLTensor(e,t,r,i){let a=Jt.get(r);if(!a)throw new Error(`Unsupported ONNX data type: ${r}`);let n=this.tensorManager.registerTensor(e,t,a,i);return de("verbose",()=>`[WebNN] registerMLTensor {tensor: ${t}, dataType: ${a}, dimensions: ${i}} -> {tensorId: ${n}}`),n}registerMLConstant(e,t,r,i,a,n,s=!1){if(!n)throw new Error("External mounted files are not available.");let o=e;e.startsWith("./")&&(o=e.substring(2));let l=n.get(o);if(!l)throw new Error(`File with name ${o} not found in preloaded files.`);if(t+r>l.byteLength)throw new Error("Out of bounds: data offset and length exceed the external file data size.");let d=l.slice(t,t+r).buffer,c;switch(a.dataType){case"float32":c=new Float32Array(d);break;case"float16":c=typeof Float16Array<"u"&&Float16Array.from?new Float16Array(d):new Uint16Array(d);break;case"int32":c=new Int32Array(d);break;case"uint32":c=new Uint32Array(d);break;case"int64":if(s){let f=Ca(new Uint8Array(d),"int64");c=new Int32Array(f.buffer),a.dataType="int32"}else c=new BigInt64Array(d);break;case"uint64":c=new BigUint64Array(d);break;case"int8":c=new Int8Array(d);break;case"int4":case"uint4":case"uint8":c=new Uint8Array(d);break;default:throw new Error(`Unsupported data type: ${a.dataType} in creating WebNN Constant from external data.`)}return de("verbose",()=>`[WebNN] registerMLConstant {dataType: ${a.dataType}, shape: ${a.shape}}} ${s?"(Note: it was int64 data type and registered to int32 as workaround)":""}`),i.constant(a,c)}registerGraphInput(e){this.temporaryGraphInputs.push(e)}registerGraphOutput(e){this.temporaryGraphOutputs.push(e)}isGraphInput(e,t){let r=this.sessionGraphInputs.get(e);return r?r.includes(t):!1}isGraphOutput(e,t){let r=this.sessionGraphOutputs.get(e);return r?r.includes(t):!1}isGraphInputOutputTypeSupported(e,t,r=!0){let i=this.mlContextBySessionId.get(e),a=Jt.get(It(t));return typeof a>"u"?!1:r?!!i?.opSupportLimits().input.dataTypes.includes(a):!!i?.opSupportLimits().output.dataTypes.includes(a)}flush(){}}}),rn=L(()=>{}),Lr,Ei,zi,ao,no,qr,Ta,so,pp,Tg=L(()=>{st(),rn(),Lr=new Map([[64,250],[128,200],[256,200],[512,200],[2048,230],[4096,200],[8192,50],[16384,50],[32768,50],[65536,50],[131072,50],[262144,50],[524288,50],[1048576,50],[2097152,30],[4194304,20],[8388608,10],[12582912,10],[16777216,10],[26214400,15],[33554432,22],[44236800,2],[58982400,6],[67108864,6],[134217728,6],[167772160,6]]),Ei=[],zi=e=>Math.ceil(Number(e)/16)*16,ao=e=>{for(let t=0;t<Ei.length;t++){let r=Ei[t];if(e<=r)return r}return Math.ceil(e/16)*16},no=1,qr=()=>no++,Ta=async(e,t,r,i)=>{let a=zi(r),n=e.device.createBuffer({size:a,usage:GPUBufferUsage.COPY_DST|GPUBufferUsage.MAP_READ});try{let s=e.getCommandEncoder();e.endComputePass(),s.copyBufferToBuffer(t,0,n,0,a),e.flush(),await n.mapAsync(GPUMapMode.READ);let o=n.getMappedRange();if(i){let l=i();return l.set(new Uint8Array(o,0,r)),l}else return new Uint8Array(o.slice(0,r))}finally{n.destroy()}},so=class{constructor(e){this.backend=e,this.storageCache=new Map,this.freeBuffers=new Map,this.freeUniformBuffers=new Map,this.buffersPending=[],this.capturedPendingBuffers=new Map;for(let[t]of Lr)Ei.push(t),this.freeBuffers.set(t,[]),this.freeUniformBuffers.set(t,[]);this.sessionCount=0}upload(e,t){let r=t.buffer,i=t.byteOffset,a=t.byteLength,n=zi(a),s=this.storageCache.get(e);if(!s)throw new Error("gpu data for uploading does not exist");if(Number(s.originalSize)!==a)throw new Error(`inconsistent data size. gpu data size=${s.originalSize}, data size=${a}`);let o=this.backend.device.createBuffer({mappedAtCreation:!0,size:n,usage:GPUBufferUsage.MAP_WRITE|GPUBufferUsage.COPY_SRC}),l=o.getMappedRange();new Uint8Array(l).set(new Uint8Array(r,i,a)),o.unmap();let d=this.backend.device.createCommandEncoder();d.copyBufferToBuffer(o,0,s.gpuData.buffer,0,n),this.backend.device.queue.submit([d.finish()]),o.destroy(),de("verbose",()=>`[WebGPU] GpuDataManager.upload(id=${e})`)}memcpy(e,t){let r=this.storageCache.get(e);if(!r)throw new Error("source gpu data for memcpy does not exist");let i=this.storageCache.get(t);if(!i)throw new Error("destination gpu data for memcpy does not exist");if(r.originalSize!==i.originalSize)throw new Error("inconsistent source and destination gpu data size");let a=zi(r.originalSize),n=this.backend.getCommandEncoder();this.backend.endComputePass(),n.copyBufferToBuffer(r.gpuData.buffer,0,i.gpuData.buffer,0,a)}registerExternalBuffer(e,t,r){let i;if(r){if(i=r[0],e===r[1])return de("verbose",()=>`[WebGPU] GpuDataManager.registerExternalBuffer(size=${t}) => id=${i}, buffer is the same, skip.`),i;if(this.backend.capturedCommandList.has(this.backend.currentSessionId))throw new Error(`Registering a different external buffer under graph capture mode is not supported yet.
             Please use the previous external buffer!`)}else i=qr();return this.storageCache.set(i,{gpuData:{id:i,type:0,buffer:e},originalSize:t}),de("verbose",()=>`[WebGPU] GpuDataManager.registerExternalBuffer(size=${t}) => id=${i}, registered.`),i}unregisterExternalBuffer(e){e!==void 0&&(this.storageCache.delete(e),de("verbose",()=>`[WebGPU] GpuDataManager.unregisterExternalBuffer() => id=${e}`))}create(e,t=GPUBufferUsage.STORAGE|GPUBufferUsage.COPY_SRC|GPUBufferUsage.COPY_DST){let r=ao(e),i,a=(t&GPUBufferUsage.STORAGE)===GPUBufferUsage.STORAGE,n=(t&GPUBufferUsage.UNIFORM)===GPUBufferUsage.UNIFORM;if(a||n){let o=(a?this.freeBuffers:this.freeUniformBuffers).get(r);o?o.length>0?i=o.pop():i=this.backend.device.createBuffer({size:r,usage:t}):i=this.backend.device.createBuffer({size:r,usage:t})}else i=this.backend.device.createBuffer({size:r,usage:t});let s={id:qr(),type:0,buffer:i};return this.storageCache.set(s.id,{gpuData:s,originalSize:Number(e)}),de("verbose",()=>`[WebGPU] GpuDataManager.create(size=${e}) => id=${s.id}`),s}get(e){return this.storageCache.get(e)?.gpuData}release(e){let t=typeof e=="bigint"?Number(e):e,r=this.storageCache.get(t);if(!r){if(this.storageCache.size===0)return 0;throw new Error("releasing data does not exist")}return de("verbose",()=>`[WebGPU] GpuDataManager.release(id=${t}), gpuDataId=${r.gpuData.id}`),this.storageCache.delete(t),this.buffersPending.push(r.gpuData.buffer),r.originalSize}async download(e,t){let r=this.storageCache.get(Number(e));if(!r)throw new Error("data does not exist");await Ta(this.backend,r.gpuData.buffer,r.originalSize,t)}refreshPendingBuffers(){if(this.buffersPending.length!==0)if(this.backend.sessionStatus==="default"){for(let e of this.buffersPending){let t=Lr.get(e.size);if((e.usage&GPUBufferUsage.STORAGE)===GPUBufferUsage.STORAGE){let r=this.freeBuffers.get(e.size)||[];t===void 0||r.length>=t?e.destroy():r.push(e)}else if((e.usage&GPUBufferUsage.UNIFORM)===GPUBufferUsage.UNIFORM){let r=this.freeUniformBuffers.get(e.size)||[];t===void 0||r.length>=t?e.destroy():r.push(e)}else e.destroy()}this.buffersPending=[]}else{let e=this.capturedPendingBuffers.get(this.backend.currentSessionId);e||(e=[],this.capturedPendingBuffers.set(this.backend.currentSessionId,e));for(let t of this.buffersPending)e.push(t);this.buffersPending=[]}}dispose(){this.freeBuffers.forEach(e=>{e.forEach(t=>{t.destroy()})}),this.freeUniformBuffers.forEach(e=>{e.forEach(t=>{t.destroy()})}),this.storageCache.forEach(e=>{e.gpuData.buffer.destroy()}),this.capturedPendingBuffers.forEach(e=>{e.forEach(t=>{t.destroy()})}),this.storageCache=new Map,this.freeBuffers=new Map,this.freeUniformBuffers=new Map,this.capturedPendingBuffers=new Map}onCreateSession(){this.sessionCount+=1}onReleaseSession(e){let t=this.capturedPendingBuffers.get(e);t&&(t.forEach(r=>{r.destroy()}),this.capturedPendingBuffers.delete(e)),this.sessionCount-=1,this.sessionCount===0&&(de("warning",()=>"[WebGPU] Clearing webgpu buffer cache"),this.storageCache.forEach(r=>{r.gpuData.buffer.destroy()}),this.storageCache=new Map)}},pp=(...e)=>new so(...e)}),oo,he,xe=L(()=>{oo=class{constructor(e){Object.assign(this,e)}get cacheKey(){return this.key||(this.key=Object.getOwnPropertyNames(this).sort().map(e=>`${this[e]}`).join(";")),this.key}},he=e=>new oo(e)}),Lt,Ai,Ie,ze,J,$e,Sa,Pt,yt,Q,ei,M,X,cp,an,uo,fp,oe=L(()=>{ie(),se(),Lt=64,Ai=(e,t)=>{if(t===3)throw new Error("vec3 has same alignment as vec4, use vec4 instead");switch(Number(e)){case 10:return t>1?`vec${t}<f16>`:"f16";case 1:return t>1?`vec${t}<f32>`:"f32";case 6:return t>1?`vec${t}<i32>`:"i32";case 12:return t>1?`vec${t}<u32>`:"u32";case 7:if(t>1)throw new Error("currently not supported vecX of uint64 yet");return["vec2<u32>","i32"];case 13:if(t>1)throw new Error("currently not supported vecX of uint64 yet");return["vec2<u32>","u32"];case 9:if(t!==4)throw new Error("bool must be vec4");return["u32","vec4<bool>"];case 22:return"i32";case 21:return"u32";default:throw new Error(`Unknown data type: ${e}`)}},Ie=(e,t=1)=>{let r=Ai(e,t);return typeof r=="string"?r:r[0]},ze=(e,t=1)=>{let r=Ai(e,t);return typeof r=="string"?r:r[1]},J=(...e)=>{let t=[];return e.forEach(r=>{r.length!==0&&t.push({type:12,data:r},{type:12,data:O.computeStrides(r)})}),t},$e=e=>e%4===0?4:e%2===0?2:1,Sa=(e="f32",t,r="0")=>!t||t===1?`${e}(${r})`:`vec${t}<${e}>(${r})`,Pt=(e,t,r)=>e==="f32"?r:t===1?`f32(${r})`:`vec${t}<f32>(${r})`,yt=(e,t)=>t===4?`(${e}.x + ${e}.y + ${e}.z + ${e}.w)`:t===2?`(${e}.x + ${e}.y)`:t===3?`(${e}.x + ${e}.y + ${e}.z)`:e,Q=(e,t,r,i)=>e.startsWith("uniforms.")&&r>4?typeof t=="string"?i==="f16"?`${e}[(${t}) / 8][(${t}) % 8 / 4][(${t}) % 8 % 4]`:`${e}[(${t}) / 4][(${t}) % 4]`:i==="f16"?`${e}[${Math.floor(t/8)}][${Math.floor(t%8/4)}][${t%8%4}]`:`${e}[${Math.floor(t/4)}][${t%4}]`:r>1?`${e}[${t}]`:e,ei=(e,t,r,i,a)=>{let n=typeof r=="number",s=n?r:r.length,o=[...new Array(s).keys()],l=s<2?"u32":s<=4?`vec${s}<u32>`:`array<u32, ${s}>`,d=Ai(t,a),c=typeof d=="string"?d:d[1],f=typeof d=="string"?d:d[0],m={indices:l,value:c,storage:f,tensor:t},y=U=>typeof U=="string"?U:`${U}u`,_={offsetToIndices:!1,indicesToOffset:!1,broadcastedIndicesToOffset:!1,set:!1,setByIndices:!1,get:!1,getByIndices:!1},b=n?"uniforms.":"",x=`${b}${e}_shape`,$=`${b}${e}_strides`,w="";for(let U=0;U<s-1;U++)w+=`
    let dim${U} = current / ${Q($,U,s)};
    let rest${U} = current % ${Q($,U,s)};
    indices[${U}] = dim${U};
    current = rest${U};
    `;w+=`indices[${s-1}] = current;`;let T=s<2?"":`
  fn o2i_${e}(offset: u32) -> ${m.indices} {
    var indices: ${m.indices};
    var current = offset;
    ${w}
    return indices;
  }`,C=U=>(_.offsetToIndices=!0,s<2?U:`o2i_${e}(${U})`),I=[];if(s>=2)for(let U=s-1;U>=0;U--)I.push(`${Q($,U,s)} * (indices[${U}])`);let z=s<2?"":`
  fn i2o_${e}(indices: ${m.indices}) -> u32 {
    return ${I.join("+")};
  }`,k=U=>(_.indicesToOffset=!0,s<2?U:`i2o_${e}(${U})`),A=(...U)=>s===0?"0u":`${m.indices}(${U.map(y).join(",")})`,D=(U,j)=>s<2?`${U}`:`${Q(U,j,s)}`,V=(U,j,ae)=>s<2?`${U}=${ae};`:`${Q(U,j,s)}=${ae};`,G={},H=(U,j)=>{_.broadcastedIndicesToOffset=!0;let ae=`${j.name}broadcastedIndicesTo${e}Offset`;if(ae in G)return`${ae}(${U})`;let pe=[];for(let N=s-1;N>=0;N--){let le=j.indicesGet("outputIndices",N+j.rank-s);pe.push(`${D($,N)} * (${le} % ${D(x,N)})`)}return G[ae]=`fn ${ae}(outputIndices: ${j.type.indices}) -> u32 {
             return ${pe.length>0?pe.join("+"):"0u"};
           }`,`${ae}(${U})`},F=(U,j)=>(()=>{if(m.storage===m.value)return`${e}[${U}]=${j};`;if(m.storage==="vec2<u32>"&&m.value==="i32")return`${e}[${U}]=vec2<u32>(u32(${j}), select(0u, 0xFFFFFFFFu, ${j} < 0));`;if(m.storage==="vec2<u32>"&&m.value==="u32")return`${e}[${U}]=vec2<u32>(u32(${j}), 0u);`;if(m.storage==="u32"&&m.value==="vec4<bool>")return`${e}[${U}]=dot(vec4<u32>(0x1, 0x100, 0x10000, 0x1000000), vec4<u32>(${j}));`;throw new Error(`not supported combination of storage type ${m.storage} and value type ${m.value} yet`)})(),W=U=>(()=>{if(m.storage===m.value)return`${e}[${U}]`;if(m.storage==="vec2<u32>"&&m.value==="i32")return`i32(${e}[${U}].x)`;if(m.storage==="vec2<u32>"&&m.value==="u32")return`u32(${e}[${U}].x)`;if(m.storage==="u32"&&m.value==="vec4<bool>")return`vec4<bool>(bool(${e}[${U}] & 0xFFu), bool(${e}[${U}] & 0xFF00u), bool(${e}[${U}] & 0xFF0000u), bool(${e}[${U}] & 0xFF000000u))`;throw new Error(`not supported combination of storage type ${m.storage} and value type ${m.value} yet`)})(),re=s<2?"":`
  fn get_${e}ByIndices(indices: ${m.indices}) -> ${c} {
    return ${W(`i2o_${e}(indices)`)};
  }`,ee=s<2?"":(()=>{let U=o.map(ae=>`d${ae}: u32`).join(", "),j=o.map(ae=>`d${ae}`).join(", ");return`
  fn get_${e}(${U}) -> ${c} {
    return get_${e}ByIndices(${A(j)});
  }`})(),K=(...U)=>{if(U.length!==s)throw new Error(`indices length must be ${s}`);let j=U.map(y).join(",");return s===0?W("0u"):s===1?W(j[0]):(_.get=!0,_.getByIndices=!0,_.indicesToOffset=!0,`get_${e}(${j})`)},ne=U=>s<2?W(U):(_.getByIndices=!0,_.indicesToOffset=!0,`get_${e}ByIndices(${U})`),Y=s<2?"":`
  fn set_${e}ByIndices(indices: ${m.indices}, value: ${c}) {
    ${F(`i2o_${e}(indices)`,"value")}
  }`,ye=s<2?"":(()=>{let U=o.map(ae=>`d${ae}: u32`).join(", "),j=o.map(ae=>`d${ae}`).join(", ");return`
  fn set_${e}(${U}, value: ${c}) {
    set_${e}ByIndices(${A(j)}, value);
  }`})();return{impl:()=>{let U=[],j=!1;return _.offsetToIndices&&(U.push(T),j=!0),_.indicesToOffset&&(U.push(z),j=!0),_.broadcastedIndicesToOffset&&(Object.values(G).forEach(ae=>U.push(ae)),j=!0),_.set&&(U.push(ye),j=!0),_.setByIndices&&(U.push(Y),j=!0),_.get&&(U.push(ee),j=!0),_.getByIndices&&(U.push(re),j=!0),!n&&j&&U.unshift(`const ${x} = ${m.indices}(${r.join(",")});`,`const ${$} = ${m.indices}(${O.computeStrides(r).join(",")});`),U.join(`
`)},type:m,offsetToIndices:C,indicesToOffset:k,broadcastedIndicesToOffset:H,indices:A,indicesGet:D,indicesSet:V,set:(...U)=>{if(U.length!==s+1)throw new Error(`indices length must be ${s}`);let j=U[s];if(typeof j!="string")throw new Error("value must be string");let ae=U.slice(0,s).map(y).join(",");return s===0?F("0u",j):s===1?F(ae[0],j):(_.set=!0,_.setByIndices=!0,_.indicesToOffset=!0,`set_${e}(${ae}, ${j})`)},setByOffset:F,setByIndices:(U,j)=>s<2?F(U,j):(_.setByIndices=!0,_.indicesToOffset=!0,`set_${e}ByIndices(${U}, ${j});`),get:K,getByOffset:W,getByIndices:ne,usage:i,name:e,strides:$,shape:x,rank:s}},M=(e,t,r,i=1)=>ei(e,t,r,"input",i),X=(e,t,r,i=1)=>ei(e,t,r,"output",i),cp=(e,t,r)=>ei(e,t,r,"atomicOutput",1),an=(e,t,r,i=1)=>ei(e,t,r,"internal",i),uo=class{constructor(e,t){this.normalizedDispatchGroup=e,this.limits=t,this.internalVariables=[],this.variables=[],this.uniforms=[],this.variableIndex=0}guardAgainstOutOfBoundsWorkgroupSizes(e){return`if (global_idx >= ${typeof e=="number"?`${e}u`:e}) { return; }`}mainStart(e=Lt){let t=typeof e=="number"?e:e[0],r=typeof e=="number"?1:e[1],i=typeof e=="number"?1:e[2];if(t>this.limits.maxComputeWorkgroupSizeX||r>this.limits.maxComputeWorkgroupSizeY||i>this.limits.maxComputeWorkgroupSizeZ)throw new Error(`workgroup size [${t}, ${r}, ${i}] exceeds the maximum workgroup size [${this.limits.maxComputeWorkgroupSizeX}, ${this.limits.maxComputeWorkgroupSizeY}, ${this.limits.maxComputeWorkgroupSizeZ}].`);if(t*r*i>this.limits.maxComputeInvocationsPerWorkgroup)throw new Error(`workgroup size [${t}, ${r}, ${i}] exceeds the maximum workgroup invocations ${this.limits.maxComputeInvocationsPerWorkgroup}.`);let a=this.normalizedDispatchGroup[1]===1&&this.normalizedDispatchGroup[2]===1,n=a?`@builtin(global_invocation_id) global_id : vec3<u32>,
    @builtin(workgroup_id) workgroup_id : vec3<u32>,
    @builtin(local_invocation_index) local_idx : u32,
    @builtin(local_invocation_id) local_id : vec3<u32>`:`@builtin(global_invocation_id) global_id : vec3<u32>,
                                             @builtin(local_invocation_id) local_id : vec3<u32>,
    @builtin(local_invocation_index) local_idx : u32,
    @builtin(workgroup_id) workgroup_id : vec3<u32>,
    @builtin(num_workgroups) num_workgroups : vec3<u32>`,s=a?`let global_idx = global_id.x;
         let workgroup_index = workgroup_id.x;`:`let workgroup_index = workgroup_id.z * num_workgroups[0] * num_workgroups[1] +
             workgroup_id.y * num_workgroups[0] + workgroup_id.x;
         let global_idx = workgroup_index * ${t*r*i}u + local_idx;`;return`@compute @workgroup_size(${t}, ${r}, ${i})
  fn main(${n}) {
    ${s}
  `}appendVariableUniforms(e){e.rank!==0&&(e.shape.startsWith("uniforms.")&&this.uniforms.push({name:e.shape.replace("uniforms.",""),type:"u32",length:e.rank}),e.strides.startsWith("uniforms.")&&this.uniforms.push({name:e.strides.replace("uniforms.",""),type:"u32",length:e.rank}))}declareVariable(e,t){if(e.usage==="internal")throw new Error("cannot use internal variable with declareVariable(). use registerInternalVariables() instead.");this.variables.push(e),this.appendVariableUniforms(e);let r=e.usage==="input"?"read":"read_write",i=e.usage==="atomicOutput"?"atomic<i32>":e.type.storage;return`@group(0) @binding(${t}) var<storage, ${r}> ${e.name}: array<${i}>;`}declareVariables(...e){return e.map(t=>this.declareVariable(t,this.variableIndex++)).join(`
`)}registerInternalVariable(e){if(e.usage!=="internal")throw new Error("cannot use input or output variable with registerInternalVariable(). use declareVariables() instead.");this.internalVariables.push(e),this.appendVariableUniforms(e)}registerInternalVariables(...e){return e.forEach(t=>this.registerInternalVariable(t)),this}registerUniform(e,t,r=1){return this.uniforms.push({name:e,type:t,length:r}),this}registerUniforms(e){return this.uniforms=this.uniforms.concat(e),this}uniformDeclaration(){if(this.uniforms.length===0)return"";let e=[];for(let{name:t,type:r,length:i}of this.uniforms)if(i&&i>4)r==="f16"?e.push(`@align(16) ${t}:array<mat2x4<${r}>, ${Math.ceil(i/8)}>`):e.push(`${t}:array<vec4<${r}>, ${Math.ceil(i/4)}>`);else{let a=i==null||i===1?r:`vec${i}<${r}>`;e.push(`${t}:${a}`)}return`
      struct Uniforms { ${e.join(", ")} };
      @group(0) @binding(${this.variableIndex}) var<uniform> uniforms: Uniforms;`}get additionalImplementations(){return this.uniformDeclaration()+this.variables.map(e=>e.impl()).join(`
`)+this.internalVariables.map(e=>e.impl()).join(`
`)}get variablesInfo(){if(this.uniforms.length===0)return;let e=t=>[12,10,1,6][["u32","f16","f32","i32"].indexOf(t)];return this.uniforms.map(t=>[e(t.type),t.length??1])}},fp=(e,t)=>new uo(e,t)}),lo,Vr,po,co,fo,ho,De,hp,mp,_t=L(()=>{ie(),se(),xe(),oe(),lo=(e,t)=>{if(!e||e.length!==1)throw new Error("Transpose requires 1 input.");if(t.length!==0&&t.length!==e[0].dims.length)throw new Error(`perm size ${t.length} does not match input rank ${e[0].dims.length}`)},Vr=(e,t)=>t.length!==0?t:[...new Array(e).keys()].reverse(),po=(e,t)=>O.sortBasedOnPerm(e,Vr(e.length,t)),co=(e,t,r,i)=>{let a=`fn perm(i: ${i.type.indices}) -> ${r.type.indices} {
    var a: ${r.type.indices};`;for(let n=0;n<t;++n)a+=`a[${e[n]}]=i[${n}];`;return a+="return a;}"},fo=(e,t)=>{let r=[],i=[];for(let a=0;a<e.length;++a)e[a]!==1&&r.push(e[a]),e[t[a]]!==1&&i.push(t[a]);return{newShape:r,newPerm:i}},ho=(e,t)=>{let r=0;for(let i=0;i<e.length;++i)if(t[e[i]]!==1){if(e[i]<r)return!1;r=e[i]}return!0},De=(e,t)=>{let r=e.dataType,i=e.dims.length,a=Vr(i,t),n=po(e.dims,a),s=e.dims,o=n,l=i<2||ho(a,e.dims),d;if(l)return d=_=>{let b=M("input",r,s,4),x=X("output",r,o,4);return`
  ${_.registerUniform("output_size","u32").declareVariables(b,x)}
  ${_.mainStart()}
    ${_.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
    output[global_idx] = input[global_idx];
  }`},{name:"TransposeCopy",shaderCache:{inputDependencies:["type"]},getRunData:()=>{let _=O.size(n);return{outputs:[{dims:n,dataType:e.dataType}],dispatchGroup:{x:Math.ceil(_/64/4)},programUniforms:[{type:12,data:Math.ceil(_/4)}]}},getShaderSource:d};let{newShape:c,newPerm:f}=fo(e.dims,a),m=O.areEqual(f,[2,3,1]),y=O.areEqual(f,[3,1,2]);if(c.length===2||m||y){s=m?[c[0],c[1]*c[2]]:y?[c[0]*c[1],c[2]]:c,o=[s[1],s[0]];let _=16;return d=b=>{let x=M("a",r,s.length),$=X("output",r,o.length);return`
  ${b.registerUniform("output_size","u32").declareVariables(x,$)}
  var<workgroup> tile : array<array<${$.type.value}, ${_+1}>, ${_}>;
  ${b.mainStart([_,_,1])}
    let stride = (uniforms.output_shape[1] - 1) / ${_} + 1;
    let workgroup_id_x = workgroup_index % stride;
    let workgroup_id_y = workgroup_index / stride;
    let input_col = workgroup_id_y * ${_}u + local_id.x;
    let input_row = workgroup_id_x * ${_}u + local_id.y;
    if (input_row < uniforms.a_shape[0] && input_col < uniforms.a_shape[1]) {
      tile[local_id.y][local_id.x] = ${x.getByIndices(`${x.type.indices}(input_row, input_col)`)};
    }
    workgroupBarrier();

    let output_col = workgroup_id_x * ${_}u + local_id.x;
    let output_row = workgroup_id_y * ${_}u + local_id.y;
    if (output_row < uniforms.output_shape[0] && output_col < uniforms.output_shape[1]) {
      ${$.setByIndices(`${$.type.indices}(output_row, output_col)`,"tile[local_id.x][local_id.y]")}
    }
  }`},{name:"TransposeShared",shaderCache:{inputDependencies:["type"]},getRunData:()=>{let b=O.size(n);return{outputs:[{dims:n,dataType:e.dataType}],dispatchGroup:{x:Math.ceil(o[1]/_),y:Math.ceil(o[0]/_)},programUniforms:[{type:12,data:b},...J(s,o)]}},getShaderSource:d}}return d=_=>{let b=M("a",r,s.length),x=X("output",r,o.length);return`
  ${_.registerUniform("output_size","u32").declareVariables(b,x)}

  ${co(a,i,b,x)}

  ${_.mainStart()}
    ${_.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}

    let indices = ${x.offsetToIndices("global_idx")};
    let aIndices = perm(indices);

    ${x.setByOffset("global_idx",b.getByIndices("aIndices"))}
  }`},{name:"Transpose",shaderCache:{hint:`${t}`,inputDependencies:["rank"]},getRunData:()=>{let _=O.size(n);return{outputs:[{dims:n,dataType:e.dataType}],dispatchGroup:{x:Math.ceil(_/64)},programUniforms:[{type:12,data:_},...J(s,o)]}},getShaderSource:d}},hp=(e,t)=>{lo(e.inputs,t.perm),e.compute(De(e.inputs[0],t.perm))},mp=e=>he({perm:e.perm})}),mo,go,yo,_o,bo,wo,vo,$o,xo,Co,Le,gp,yp,_p,bp,wp,vp,$p,xp,Cp,Tp,Sg=L(()=>{ie(),se(),oe(),nn(),_t(),mo={max:"select(bestValue, candidate, candidate > bestValue)",min:"select(bestValue, candidate, candidate < bestValue)",mean:"bestValue + candidate",sum:"bestValue + candidate",prod:"bestValue * candidate",sumSquare:"bestValue + candidate * candidate",logSumExp:"bestValue + exp(candidate)",l1:"bestValue + abs(candidate)",l2:"bestValue + candidate * candidate",logSum:"bestValue + candidate"},go={max:"select(bestValue, candidate, candidate > bestValue)",min:"select(bestValue, candidate, candidate < bestValue)",mean:"bestValue + candidate",sum:"bestValue + candidate",prod:"bestValue * candidate",sumSquare:"bestValue + candidate",logSumExp:"bestValue + candidate",l1:"bestValue + candidate",l2:"bestValue + candidate",logSum:"bestValue + candidate"},yo={max:"_A[offset]",min:"_A[offset]",mean:"0",sum:"0",prod:"1",sumSquare:"0",logSumExp:"0",l1:"0",l2:"0",logSum:"0"},_o={max:"bestValue",min:"bestValue",sum:"bestValue",prod:"bestValue",sumSquare:"bestValue",logSumExp:"log(bestValue)",l1:"bestValue",l2:"sqrt(bestValue)",logSum:"log(bestValue)"},bo=(e,t)=>{let r=[];for(let i=t-e;i<t;++i)r.push(i);return r},wo=(e,t)=>{let r=[],i=e.length;for(let n=0;n<i;n++)t.indexOf(n)===-1&&r.push(e[n]);let a=t.map(n=>e[n]);return[r,a]},vo=(e,t)=>{let r=e.length+t.length,i=[],a=0;for(let n=0;n<r;n++)t.indexOf(n)===-1?i.push(e[a++]):i.push(1);return i},$o=(e,t)=>{for(let r=0;r<e.length;++r)if(e[e.length-r-1]!==t-1-r)return!1;return!0},xo=(e,t)=>{let r=[];if(!$o(e,t)){for(let i=0;i<t;++i)e.indexOf(i)===-1&&r.push(i);e.forEach(i=>r.push(i))}return r},Co=(e,t,r,i,a,n,s)=>{let o=r[0].dims,l=O.size(n),d=O.size(s),c=M("_A",r[0].dataType,o),f=X("output",a,n),m=64;l===1&&(m=256);let y=`
          var<workgroup> aBestValues : array<f32, ${m}>;
       `,_=b=>`
        ${b.registerUniform("reduceSize","u32").declareVariables(c,f)}
        ${y}
        fn DIV_CEIL(a : u32, b : u32) -> u32 {
          return ((a - 1u) / b + 1u);
         }
         ${b.mainStart(m)}

          let outputIndex = global_idx / ${m};
          let offset = outputIndex * uniforms.reduceSize;

          var bestValue = f32(${yo[i]});
          let Length = uniforms.reduceSize;
          for (var k = local_idx; k < Length; k = k + ${m}) {
           let candidate = f32(${c.getByOffset("offset + k")});
           bestValue = ${mo[i]};
          }
          aBestValues[local_idx] = bestValue;
          workgroupBarrier();

         var reduceSize = min(Length, ${m}u);
         for (var currentSize = reduceSize / 2u; reduceSize > 1u;
             currentSize = reduceSize / 2u) {
           let interval = DIV_CEIL(reduceSize, 2u);
           if (local_idx < currentSize) {
            let candidate = aBestValues[local_idx + interval];
            bestValue = ${go[i]};
            aBestValues[local_idx] = bestValue;
           }
           reduceSize = interval;
           workgroupBarrier();
         }

         if (local_idx == 0u) {
          ${f.setByOffset("outputIndex",`${i==="mean"?`${f.type.storage}(bestValue / f32(uniforms.reduceSize))`:`${f.type.storage}(${_o[i]})`}`)};
         }
        }`;return{name:e,shaderCache:{hint:`${t};${m}`,inputDependencies:["type"]},getShaderSource:_,getRunData:()=>({outputs:[{dims:n,dataType:a}],dispatchGroup:{x:l},programUniforms:[{type:12,data:d}]})}},Le=(e,t,r,i)=>{let a=e.inputs.length===1?r:Ia(e.inputs,r),n=a.axes;n.length===0&&!a.noopWithEmptyAxes&&(n=e.inputs[0].dims.map((y,_)=>_));let s=O.normalizeAxes(n,e.inputs[0].dims.length),o=s,l=e.inputs[0],d=xo(o,e.inputs[0].dims.length);d.length>0&&(l=e.compute(De(e.inputs[0],d),{inputs:[0],outputs:[-1]})[0],o=bo(o.length,l.dims.length));let[c,f]=wo(l.dims,o),m=c;a.keepDims&&(m=vo(c,s)),e.compute(Co(t,a.cacheKey,[l],i,e.inputs[0].dataType,m,f),{inputs:[l]})},gp=(e,t)=>{Le(e,"ReduceMeanShared",t,"mean")},yp=(e,t)=>{Le(e,"ReduceL1Shared",t,"l1")},_p=(e,t)=>{Le(e,"ReduceL2Shared",t,"l2")},bp=(e,t)=>{Le(e,"ReduceLogSumExpShared",t,"logSumExp")},wp=(e,t)=>{Le(e,"ReduceMaxShared",t,"max")},vp=(e,t)=>{Le(e,"ReduceMinShared",t,"min")},$p=(e,t)=>{Le(e,"ReduceProdShared",t,"prod")},xp=(e,t)=>{Le(e,"ReduceSumShared",t,"sum")},Cp=(e,t)=>{Le(e,"ReduceSumSquareShared",t,"sumSquare")},Tp=(e,t)=>{Le(e,"ReduceLogSumShared",t,"logSum")}}),qe,To,Gi,Ia,Ve,So,Io,ko,Eo,zo,Ao,Oo,Ro,Bo,Mo,je,Sp,Ip,kp,Ep,zp,Ap,Op,Rp,Bp,Mp,nn=L(()=>{ie(),se(),xe(),oe(),Sg(),qe=e=>{if(!e||e.length===0||e.length>2)throw new Error("Reduce op requires 1 or 2 inputs.");if(e.length===2&&e[1].dims.length!==1)throw new Error("Invalid axes input dims.")},To=e=>["","",`var value = ${e.getByIndices("input_indices")};`,""],Gi=(e,t,r,i,a,n,s=!1,o=!1)=>{let l=[],d=r[0].dims,c=d.length,f=O.normalizeAxes(a,c),m=!o&&f.length===0;d.forEach((b,x)=>{m||f.indexOf(x)>=0?s&&l.push(1):l.push(b)});let y=l.length,_=O.size(l);return{name:e,shaderCache:t,getShaderSource:b=>{let x=[],$=M("_A",r[0].dataType,c),w=X("output",n,y),T=i($,w,f),C=T[2];for(let I=0,z=0;I<c;I++)m||f.indexOf(I)>=0?(s&&z++,C=`for(var j${I}: u32 = 0; j${I} < ${d[I]}; j${I}++) {
                  ${T[2].includes("last_index")?`let last_index = j${I};`:""}
                  ${$.indicesSet("input_indices",I,`j${I}`)}
                  ${C}
                }`):(x.push(`${$.indicesSet("input_indices",I,w.indicesGet("output_indices",z))};`),z++);return`

        ${b.registerUniform("output_size","u32").declareVariables($,w)}

        ${b.mainStart()}
          ${b.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
          var input_indices: ${$.type.indices};
          let output_indices = ${w.offsetToIndices("global_idx")};

          ${x.join(`
`)}
          ${T[0]}       // init ops for reduce max/min
          ${T[1]}
          ${C}
          ${T[3]}
          ${T.length===4?w.setByOffset("global_idx","value"):T.slice(4).join(`
`)}
        }`},getRunData:()=>({outputs:[{dims:l,dataType:n}],dispatchGroup:{x:Math.ceil(_/64)},programUniforms:[{type:12,data:_},...J(d,l)]})}},Ia=(e,t)=>{let r=[];return e[1].dims[0]>0&&e[1].getBigInt64Array().forEach(i=>r.push(Number(i))),he({axes:r,keepDims:t.keepDims,noopWithEmptyAxes:t.noopWithEmptyAxes})},Ve=(e,t,r,i)=>{let a=e.inputs,n=a.length===1?r:Ia(a,r);e.compute(Gi(t,{hint:n.cacheKey,inputDependencies:["rank"]},[a[0]],n.noopWithEmptyAxes&&n.axes.length===0?To:i,n.axes,a[0].dataType,n.keepDims,n.noopWithEmptyAxes),{inputs:[0]})},So=(e,t)=>{qe(e.inputs),Ve(e,"ReduceLogSum",t,(r,i)=>[`var value = ${i.type.storage}(0);`,"",`value += ${r.getByIndices("input_indices")};`,"value = log(value);"])},Io=(e,t)=>{qe(e.inputs),Ve(e,"ReduceL1",t,(r,i)=>[`var value = ${i.type.storage}(0);`,"",`value += abs(${r.getByIndices("input_indices")});`,""])},ko=(e,t)=>{qe(e.inputs),Ve(e,"ReduceL2",t,(r,i)=>[`var t = ${i.type.value}(0); var value = ${i.type.value}(0);`,"",`t = ${r.getByIndices("input_indices")}; value += (t * t);`,"value = sqrt(value);"])},Eo=(e,t)=>{qe(e.inputs),Ve(e,"ReduceLogSumExp",t,(r,i)=>[`var value = ${i.type.storage}(0);`,"",`value += exp(${r.getByIndices("input_indices")});`,"value = log(value);"])},zo=(e,t)=>{qe(e.inputs),Ve(e,"ReduceMax",t,(r,i,a)=>{let n=[];for(let s=0;s<r.rank;s++)(a.indexOf(s)>=0||a.length===0)&&n.push(r.indicesSet("input_indices",s,0));return[`${n.join(`
`)}`,`var value = ${r.getByIndices("input_indices")};`,`value = max(value, ${r.getByIndices("input_indices")});`,""]})},Ao=(e,t)=>{qe(e.inputs),Ve(e,"ReduceMean",t,(r,i,a)=>{let n=1;for(let s=0;s<r.rank;s++)(a.indexOf(s)>=0||a.length===0)&&(n*=e.inputs[0].dims[s]);return["var sum = f32(0);","",`sum += f32(${r.getByIndices("input_indices")});`,`let value = ${i.type.value}(sum / ${n});`]})},Oo=(e,t)=>{qe(e.inputs),Ve(e,"ReduceMin",t,(r,i,a)=>{let n=[];for(let s=0;s<r.rank;s++)(a.indexOf(s)>=0||a.length===0)&&n.push(`input_indices[${s}] = 0;`);return[`${n.join(`
`)}`,`var value = ${r.getByIndices("input_indices")};`,`value = min(value, ${r.getByIndices("input_indices")});`,""]})},Ro=(e,t)=>{qe(e.inputs),Ve(e,"ReduceProd",t,(r,i)=>[`var value = ${i.type.storage}(1);`,"",`value *= ${r.getByIndices("input_indices")};`,""])},Bo=(e,t)=>{qe(e.inputs),Ve(e,"ReduceSum",t,(r,i)=>[`var value = ${i.type.storage}(0);`,"",`value += ${r.getByIndices("input_indices")};`,""])},Mo=(e,t)=>{qe(e.inputs),Ve(e,"ReduceSumSquare",t,(r,i)=>[`var t = ${i.type.value}(0); var value = ${i.type.value}(0);`,"",`t = ${r.getByIndices("input_indices")}; value += t * t;`,""])},je=(e,t,r)=>{if(t.length===0)return r;let i=1,a=1;for(let n=0;n<t.length;n++)t.indexOf(n)===-1?i*=e[n]:a*=e[n];return a<32&&i>1024},Sp=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Ao(e,t):gp(e,t)},Ip=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Io(e,t):yp(e,t)},kp=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?ko(e,t):_p(e,t)},Ep=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Eo(e,t):bp(e,t)},zp=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?zo(e,t):wp(e,t)},Ap=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Oo(e,t):vp(e,t)},Op=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Ro(e,t):$p(e,t)},Rp=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Bo(e,t):xp(e,t)},Bp=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?Mo(e,t):Cp(e,t)},Mp=(e,t)=>{je(e.inputs[0].dims,t.axes,t.noopWithEmptyAxes)?So(e,t):Tp(e,t)}}),jr,Dp,Np,ka,Ig=L(()=>{ie(),xe(),nn(),jr=e=>{if(!e||e.length===0||e.length>2)throw new Error("ArgMinMaxOp op requires 1 or 2 inputs.");if(e[0].dataType!==1)throw new Error("Invalid input type.")},Dp=(e,t)=>{jr(e.inputs);let r=(i,a,n)=>{let s=[];for(let o=0;o<i.rank;o++)(n.indexOf(o)>=0||n.length===0)&&s.push(`input_indices[${o}] = 0;`);return[`${s.join(`
`)}`,`var value = ${i.getByIndices("input_indices")};
var best_index : i32 = 0;`,`if (${i.getByIndices("input_indices")} ${t.selectLastIndex>0?"<=":"<"} value) {
         value = ${i.getByIndices("input_indices")};
         best_index = i32(last_index);
       }`,"",a.setByOffset("global_idx","best_index")]};e.compute(Gi("ArgMin",{hint:t.cacheKey,inputDependencies:["rank"]},[e.inputs[0]],r,[t.axis],7,t.keepDims),{inputs:[0]})},Np=(e,t)=>{jr(e.inputs);let r=(i,a,n)=>{let s=[];for(let o=0;o<i.rank;o++)(n.indexOf(o)>=0||n.length===0)&&s.push(`input_indices[${o}] = 0;`);return[`${s.join(`
`)}`,`var value = ${i.getByIndices("input_indices")};
var best_index : i32 = 0;`,`if (${i.getByIndices("input_indices")} ${t.selectLastIndex>0?">=":">"} value) {
         value = ${i.getByIndices("input_indices")};
         best_index = i32(last_index);
       }`,"",a.setByOffset("global_idx","best_index")]};e.compute(Gi("argMax",{hint:t.cacheKey,inputDependencies:["rank"]},[e.inputs[0]],r,[t.axis],7,t.keepDims),{inputs:[0]})},ka=e=>he(e)}),Do,Oi,No,Po,Uo,hi,Wo,Pp,sn=L(()=>{ie(),se(),rn(),oe(),Do=(e,t)=>{let r=e[0],i=e[1],a=e[2],n=e[3],s=e[4],o=e[5];if(s&&o)throw new Error("Attention cannot have both past and attention_bias");if(r.dims.length!==3)throw new Error('Input "input" must have 3 dimensions');let l=r.dims[0],d=r.dims[1],c=r.dims[2];if(a.dims.length!==1)throw new Error('Input "bias" is expected to have 1 dimensions');if(i.dims.length!==2)throw new Error('Input "weights" is expected to have 2 dimensions');if(i.dims[0]!==c)throw new Error("Input 1 dimension 0 should have same length as dimension 2 of input 0");if(a.dims[0]!==i.dims[1])throw new Error('Input "bias" dimension 0 should have same length as dimension 1 of input "weights"');let f=a.dims[0]/3,m=f,y=m;if(t.qkvHiddenSizes.length>0){if(t.qkvHiddenSizes.length!==3)throw new Error("qkv_hidden_sizes attribute should have 3 elements");for(let T of t.qkvHiddenSizes)if(T%t.numHeads!==0)throw new Error("qkv_hidden_sizes should be divisible by num_heads");f=t.qkvHiddenSizes[0],m=t.qkvHiddenSizes[1],y=t.qkvHiddenSizes[2]}let _=d;if(f!==m)throw new Error("qkv_hidden_sizes first element should be same as the second");if(a.dims[0]!==f+m+y)throw new Error('Input "bias" dimension 0 should have same length as sum of Q/K/V hidden sizes');let b=0;if(s){if(m!==y)throw new Error('Input "past" expect k_hidden_size == v_hidden_size');if(s.dims.length!==5)throw new Error('Input "past" must have 5 dimensions');if(s.dims[0]!==2)throw new Error('Input "past" first dimension must be 2');if(s.dims[1]!==l)throw new Error('Input "past" second dimension must be batch_size');if(s.dims[2]!==t.numHeads)throw new Error('Input "past" third dimension must be num_heads');if(s.dims[4]!==m/t.numHeads)throw new Error('Input "past" fifth dimension must be k_hidden_size / num_heads');t.pastPresentShareBuffer||(b=s.dims[3])}let x=_+b,$=-1,w=0;if(n)throw new Error("Mask not supported");if(s)throw new Error("past is not supported");if(o){if(o.dims.length!==4)throw new Error('Input "attention_bias" must have 4 dimensions');if(o.dims[0]!==l||o.dims[1]!==t.numHeads||o.dims[2]!==d||o.dims[3]!==x)throw new Error('Expect "attention_bias" shape (batch_size, num_heads, sequence_length, total_sequence_length)')}return{batchSize:l,sequenceLength:d,pastSequenceLength:b,kvSequenceLength:_,totalSequenceLength:x,maxSequenceLength:$,inputHiddenSize:c,hiddenSize:f,vHiddenSize:y,headSize:Math.floor(f/t.numHeads),vHeadSize:Math.floor(y/t.numHeads),numHeads:t.numHeads,isUnidirectional:!1,pastPresentShareBuffer:!1,maskFilterValue:t.maskFilterValue,maskType:w,scale:t.scale,broadcastResPosBias:!1,passPastInKv:!1,qkvFormat:1}},Oi=(e,t,r)=>t&&e?`
      let total_sequence_length_input = u32(${t.getByOffset("0")});
      let present_sequence_length = max(total_sequence_length_input, uniforms.past_sequence_length);
      let is_subsequent_prompt: bool = sequence_length > 1 && sequence_length != total_sequence_length_input;
      let is_first_prompt: bool = is_subsequent_prompt == false && sequence_length == total_sequence_length_input;
      total_sequence_length = u32(${e?.getByOffset("batchIdx")}) + 1;
      var past_sequence_length: u32 = 0;
      if (is_first_prompt == false) {
        past_sequence_length = total_sequence_length - sequence_length;
      }
       `:`
    ${r?"let past_sequence_length = uniforms.past_sequence_length":""};
    let present_sequence_length = total_sequence_length;
    `,No=(e,t,r,i,a,n,s,o)=>{let l=$e(s?1:n),d=64,c=n/l;c<d&&(d=32);let f=Math.ceil(n/l/d),m=[{type:12,data:t},{type:12,data:r},{type:12,data:i},{type:12,data:a},{type:12,data:c},{type:12,data:f}],y=Ie(e.dataType,l),_=ze(1,l),b=["type"];s&&b.push("type"),o&&b.push("type");let x=$=>{let w=X("x",e.dataType,e.dims,l),T=[w],C=s?M("seq_lens",s.dataType,s.dims):void 0;C&&T.push(C);let I=o?M("total_sequence_length_input",o.dataType,o.dims):void 0;I&&T.push(I);let z=ze(e.dataType),k=[{name:"batch_size",type:"u32"},{name:"num_heads",type:"u32"},{name:"past_sequence_length",type:"u32"},{name:"sequence_length",type:"u32"},{name:"total_sequence_length",type:"u32"},{name:"elements_per_thread",type:"u32"}];return`
  var<workgroup> thread_max: array<f32, ${d}>;
  var<workgroup> thread_sum: array<f32, ${d}>;
  ${$.registerUniforms(k).declareVariables(...T)}
  ${$.mainStart([d,1,1])}
    let batchIdx = workgroup_id.z / uniforms.num_heads;
    let headIdx = workgroup_id.z % uniforms.num_heads;
    let sequence_length = uniforms.sequence_length;
    var total_sequence_length = uniforms.total_sequence_length;
    ${Oi(C,I,!1)}
    let local_offset = local_idx * uniforms.elements_per_thread;
    let offset = (global_idx / ${d}) * uniforms.total_sequence_length + local_offset;
    let seq_causal_length = ${s?"u32(past_sequence_length + workgroup_id.y + 1)":"total_sequence_length"};
    var thread_max_vector = ${_}(-3.402823e+38f);
    for (var i: u32 = 0; i < uniforms.elements_per_thread && i + local_offset < seq_causal_length; i++) {
      thread_max_vector = max(${_}(x[offset + i]), thread_max_vector);
    }
    thread_max[local_idx] = ${(()=>{switch(l){case 1:return"thread_max_vector";case 2:return"max(thread_max_vector.x, thread_max_vector.y)";case 4:return"max(max(thread_max_vector.x, thread_max_vector.y), max(thread_max_vector.z, thread_max_vector.w))";default:throw new Error(`Unsupported components: ${l}`)}})()};
    workgroupBarrier();

    var max_value =  f32(-3.402823e+38f);
    for (var i = 0u; i < ${d}; i++) {
      max_value = max(thread_max[i], max_value);
    }

    var sum_vector = ${_}(0);
    for (var i: u32 = 0; i < uniforms.elements_per_thread && i + local_offset < seq_causal_length; i++) {
      sum_vector += exp(${_}(x[offset + i]) - max_value);
    }
    thread_sum[local_idx] = ${(()=>{switch(l){case 1:return"sum_vector";case 2:return"sum_vector.x + sum_vector.y";case 4:return"sum_vector.x + sum_vector.y + sum_vector.z + sum_vector.w";default:throw new Error(`Unsupported components: ${l}`)}})()};
    workgroupBarrier();

    var sum: f32 = 0;
    for (var i = 0u; i < ${d}; i++) {
      sum += thread_sum[i];
    }

    if (sum == 0) {
      for (var i: u32 = 0; i < uniforms.elements_per_thread && i + local_offset < seq_causal_length; i++) {
        x[offset + i] = ${w.type.value}(${z}(1.0) / ${z}(seq_causal_length));
      }
    } else {
      for (var i: u32 = 0; i < uniforms.elements_per_thread && i + local_offset < seq_causal_length; i++) {
        var f32input = ${_}(x[offset + i]);
        x[offset + i] = ${w.type.value}(exp(f32input - max_value) / sum);
      }
    }
      ${s?`
        for (var total_seq_id: u32 = seq_causal_length; total_seq_id + local_offset < uniforms.total_sequence_length; total_seq_id++) {
          x[offset + total_seq_id] = ${w.type.value}(${z}(0));
        }`:""};
  }`};return{name:"AttentionProbsSoftmax",shaderCache:{hint:`${d};${y};${l}`,inputDependencies:b},getShaderSource:x,getRunData:()=>({outputs:[],dispatchGroup:{x:1,y:a,z:t*r},programUniforms:m})}},Po=(e,t,r,i,a,n,s,o,l)=>{let d=s+n.kvSequenceLength,c=[n.batchSize,n.numHeads,n.sequenceLength,d],f=e>1&&i,m=n.kvNumHeads?n.kvNumHeads:n.numHeads,y=f?[n.batchSize,m,d,n.headSize]:void 0,_=n.nReps?n.nReps:1,b=n.scale===0?1/Math.sqrt(n.headSize):n.scale,x=$e(n.headSize),$=n.headSize/x,w=12,T={x:Math.ceil(d/w),y:Math.ceil(n.sequenceLength/w),z:n.batchSize*n.numHeads},C=[{type:12,data:n.sequenceLength},{type:12,data:$},{type:12,data:d},{type:12,data:n.numHeads},{type:12,data:n.headSize},{type:1,data:b},{type:12,data:s},{type:12,data:n.kvSequenceLength},{type:12,data:_}],I=f&&i&&O.size(i.dims)>0,z=["type","type"];I&&z.push("type"),a&&z.push("type"),o&&z.push("type"),l&&z.push("type");let k=[{dims:c,dataType:t.dataType,gpuDataType:0}];f&&k.push({dims:y,dataType:t.dataType,gpuDataType:0});let A=D=>{let V=M("q",t.dataType,t.dims,x),G=M("key",r.dataType,r.dims,x),H=[V,G];if(I){let Y=M("past_key",i.dataType,i.dims,x);H.push(Y)}a&&H.push(M("attention_bias",a.dataType,a.dims));let F=o?M("seq_lens",o.dataType,o.dims):void 0;F&&H.push(F);let W=l?M("total_sequence_length_input",l.dataType,l.dims):void 0;W&&H.push(W);let re=X("output",t.dataType,c),ee=[re];f&&ee.push(X("present_key",t.dataType,y,x));let K=ze(1,x),ne=[{name:"M",type:"u32"},{name:"K",type:"u32"},{name:"N",type:"u32"},{name:"num_heads",type:"u32"},{name:"head_size",type:"u32"},{name:"alpha",type:"f32"},{name:"past_sequence_length",type:"u32"},{name:"kv_sequence_length",type:"u32"},{name:"n_reps",type:"u32"}];return`
  const TILE_SIZE = ${w}u;

  var<workgroup> tileQ: array<${V.type.storage}, ${w*w}>;
  var<workgroup> tileK: array<${V.type.storage}, ${w*w}>;
  ${D.registerUniforms(ne).declareVariables(...H,...ee)}
  ${D.mainStart([w,w,1])}
    // x holds the N and y holds the M
    let headIdx = workgroup_id.z % uniforms.num_heads;
    let kvHeadIdx = ${_===1?"headIdx":"headIdx / uniforms.n_reps"};
    let kv_num_heads = ${_===1?"uniforms.num_heads":"uniforms.num_heads / uniforms.n_reps"};
    let batchIdx = workgroup_id.z / uniforms.num_heads;
    let m = workgroup_id.y * TILE_SIZE;
    let n = workgroup_id.x * TILE_SIZE;
    let sequence_length = uniforms.M;
    var total_sequence_length = uniforms.N;
    ${Oi(F,W,!0)}
    let absKvHeadIdx = batchIdx * kv_num_heads + kvHeadIdx;
    let qOffset = workgroup_id.z * uniforms.M * uniforms.K + m * uniforms.K;
    ${I&&f?"let pastKeyOffset = absKvHeadIdx * uniforms.past_sequence_length * uniforms.K;":""};
    let kOffset = absKvHeadIdx * uniforms.kv_sequence_length * uniforms.K;
    ${f?"let presentKeyOffset = absKvHeadIdx * uniforms.N * uniforms.K;":""}
    var value = ${K}(0);
    for (var w: u32 = 0u; w < uniforms.K; w += TILE_SIZE) {
      if (global_id.y < uniforms.M && w + local_id.x < uniforms.K) {
        tileQ[TILE_SIZE * local_id.y + local_id.x] = q[qOffset + local_id.y * uniforms.K + w + local_id.x];
      }
      if (n + local_id.y < uniforms.N && w + local_id.x < uniforms.K) {
        var idx = TILE_SIZE * local_id.y + local_id.x;
      ${I&&f?`
              if (n + local_id.y < past_sequence_length) {
                tileK[idx] = past_key[pastKeyOffset + (n + local_id.y) * uniforms.K + w + local_id.x];
              } else if (n + local_id.y - past_sequence_length < uniforms.kv_sequence_length) {
                tileK[idx] = key[kOffset + (n + local_id.y - past_sequence_length) * uniforms.K + w + local_id.x];
              }`:`
          if (n + local_id.y < uniforms.kv_sequence_length) {
            tileK[idx] = key[kOffset + (n + local_id.y) * uniforms.K + w + local_id.x];
          }`}
      ${f?`if (n + local_id.y < present_sequence_length) {
        present_key[presentKeyOffset + (n + local_id.y) * uniforms.K + w + local_id.x] = tileK[idx];
      }`:""}
      }
      workgroupBarrier();

      for (var k: u32 = 0u; k < TILE_SIZE && w+k < uniforms.K; k++) {
          value += ${K}(tileQ[TILE_SIZE * local_id.y + k] * tileK[TILE_SIZE * local_id.x + k]);
      }

      workgroupBarrier();
    }

    if (global_id.y < uniforms.M && global_id.x < total_sequence_length) {
      let headOffset = workgroup_id.z * uniforms.M * uniforms.N;
      let outputIdx = headOffset + global_id.y * uniforms.N + global_id.x;
      var sum: f32 = ${(()=>{switch(x){case 1:return"value";case 2:return"value.x + value.y";case 4:return"value.x + value.y + value.z + value.w";default:throw new Error(`Unsupported components: ${x}`)}})()};
        output[outputIdx] = ${re.type.value} (sum * uniforms.alpha) + ${a?"attention_bias[outputIdx]":"0.0"};
    }
  }`};return{name:"AttentionProbs",shaderCache:{hint:`${x};${a!==void 0};${i!==void 0};${e}`,inputDependencies:z},getRunData:()=>({outputs:k,dispatchGroup:T,programUniforms:C}),getShaderSource:A}},Uo=(e,t,r,i,a,n,s=void 0,o=void 0)=>{let l=n+a.kvSequenceLength,d=a.nReps?a.nReps:1,c=a.vHiddenSize*d,f=e>1&&i,m=a.kvNumHeads?a.kvNumHeads:a.numHeads,y=f?[a.batchSize,m,l,a.headSize]:void 0,_=[a.batchSize,a.sequenceLength,c],b=12,x={x:Math.ceil(a.vHeadSize/b),y:Math.ceil(a.sequenceLength/b),z:a.batchSize*a.numHeads},$=[{type:12,data:a.sequenceLength},{type:12,data:l},{type:12,data:a.vHeadSize},{type:12,data:a.numHeads},{type:12,data:a.headSize},{type:12,data:c},{type:12,data:n},{type:12,data:a.kvSequenceLength},{type:12,data:d}],w=f&&i&&O.size(i.dims)>0,T=["type","type"];w&&T.push("type"),s&&T.push("type"),o&&T.push("type");let C=[{dims:_,dataType:t.dataType,gpuDataType:0}];f&&C.push({dims:y,dataType:t.dataType,gpuDataType:0});let I=z=>{let k=M("probs",t.dataType,t.dims),A=M("v",r.dataType,r.dims),D=[k,A];w&&D.push(M("past_value",i.dataType,i.dims));let V=s?M("seq_lens",s.dataType,s.dims):void 0;s&&D.push(V);let G=o?M("total_sequence_length_input",o.dataType,o.dims):void 0;o&&D.push(G);let H=[X("output",t.dataType,_)];f&&H.push(X("present_value",t.dataType,y));let F=[{name:"M",type:"u32"},{name:"K",type:"u32"},{name:"N",type:"u32"},{name:"num_heads",type:"u32"},{name:"head_size",type:"u32"},{name:"v_hidden_size",type:"u32"},{name:"past_sequence_length",type:"u32"},{name:"kv_sequence_length",type:"u32"},{name:"n_reps",type:"u32"}];return`
  const TILE_SIZE = ${b}u;
  var<workgroup> tileQ: array<${k.type.value}, ${b*b}>;
  var<workgroup> tileV: array<${k.type.value}, ${b*b}>;
  ${z.registerUniforms(F).declareVariables(...D,...H)}
  ${z.mainStart([b,b,1])}
   let headIdx = workgroup_id.z % uniforms.num_heads;
   let batchIdx = workgroup_id.z / uniforms.num_heads;
   let kvHeadIdx = ${d===1?"headIdx":"headIdx / uniforms.n_reps"};
   let kv_num_heads = ${d===1?"uniforms.num_heads":"uniforms.num_heads / uniforms.n_reps"};
   let m = global_id.y;
   let n = global_id.x;
   let sequence_length = uniforms.M;
   var total_sequence_length = uniforms.K;
   ${Oi(V,G,!0)}
   let offsetA = workgroup_id.z * uniforms.M * uniforms.K + m * uniforms.K;
   let absKvHeadIdx = batchIdx * kv_num_heads + kvHeadIdx; // kvHeadIdx is relative to the batch
   ${w&&f?"let pastValueOffset = absKvHeadIdx * uniforms.N * uniforms.past_sequence_length + n;":""};
   let vOffset = absKvHeadIdx * uniforms.N * uniforms.kv_sequence_length + n;
   ${f?"let presentValueOffset = absKvHeadIdx * uniforms.N * uniforms.K + n;":""}
   var value = ${k.type.storage}(0);
   for (var w: u32 = 0u; w < uniforms.K; w += TILE_SIZE) {
      if (m < uniforms.M && w + local_id.x < uniforms.K) {
        tileQ[TILE_SIZE * local_id.y + local_id.x] = probs[offsetA + w + local_id.x];
      }
      if (n < uniforms.N && w + local_id.y < uniforms.K) {
        var idx = TILE_SIZE * local_id.y + local_id.x;
        ${w&&f?`
        if (w + local_id.y < past_sequence_length) {
          tileV[idx] = past_value[pastValueOffset + (w + local_id.y) * uniforms.N];
        } else if (w + local_id.y - past_sequence_length < uniforms.kv_sequence_length) {
          tileV[idx] = v[vOffset + (w + local_id.y - past_sequence_length) * uniforms.N];
        }
      `:`
            if (w + local_id.y < uniforms.kv_sequence_length) {
              tileV[idx] = v[vOffset + (w + local_id.y) * uniforms.N];
            }`}
        ${f?`
            if (w + local_id.y < present_sequence_length) {
          present_value[presentValueOffset + (w + local_id.y) * uniforms.N] = tileV[idx];
        }`:""}
      }
     workgroupBarrier();
     for (var k: u32 = 0u; k < TILE_SIZE && w+k < total_sequence_length; k++) {
       value += tileQ[TILE_SIZE * local_id.y + k] * tileV[TILE_SIZE * k + local_id.x];
     }
     workgroupBarrier();
   }

   // we need to transpose output from BNSH_v to BSND_v
   if (m < uniforms.M && n < uniforms.N) {
     let outputIdx = batchIdx * uniforms.M * uniforms.v_hidden_size + m * uniforms.v_hidden_size
       + headIdx * uniforms.N + n;
     output[outputIdx] = value;
   }
  }`};return{name:"AttentionScore",shaderCache:{hint:`${i!==void 0};${e}`,inputDependencies:T},getRunData:()=>({outputs:C,dispatchGroup:x,programUniforms:$}),getShaderSource:I}},hi=(e,t,r,i,a,n,s,o,l,d,c=void 0,f=void 0)=>{let m=Math.min(e.outputCount,1+(s?1:0)+(o?1:0)),y=m>1?d.pastSequenceLength:0,_=y+d.kvSequenceLength,b=l&&O.size(l.dims)>0?l:void 0,x=[t,r];m>1&&s&&O.size(s.dims)>0&&x.push(s),b&&x.push(b),c&&x.push(c),f&&x.push(f);let $=e.compute(Po(m,t,r,s,b,d,y,c,f),{inputs:x,outputs:m>1?[-1,1]:[-1]})[0];e.compute(No($,d.batchSize,d.numHeads,y,d.sequenceLength,_,c,f),{inputs:c&&f?[$,c,f]:[$],outputs:[]});let w=[$,i];m>1&&o&&O.size(o.dims)>0&&w.push(o),c&&w.push(c),f&&w.push(f),e.compute(Uo(m,$,i,o,d,y,c,f),{inputs:w,outputs:m>1?[0,2]:[0]})},Wo=(e,t)=>{let r=[t.batchSize,t.numHeads,t.sequenceLength,t.headSize],i=t.sequenceLength,a=t.inputHiddenSize,n=t.headSize,s=12,o={x:Math.ceil(t.headSize/s),y:Math.ceil(t.sequenceLength/s),z:t.batchSize*t.numHeads},l=[e.inputs[0],e.inputs[1],e.inputs[2]],d=[{type:12,data:i},{type:12,data:a},{type:12,data:n},{type:12,data:t.numHeads},{type:12,data:t.headSize},{type:12,data:t.hiddenSize},{type:12,data:t.hiddenSize+t.hiddenSize+t.vHiddenSize}],c=f=>{let m=X("output_q",l[0].dataType,r),y=X("output_k",l[0].dataType,r),_=X("output_v",l[0].dataType,r),b=M("input",l[0].dataType,l[0].dims),x=M("weight",l[1].dataType,l[1].dims),$=M("bias",l[2].dataType,l[2].dims),w=b.type.storage,T=[{name:"M",type:"u32"},{name:"K",type:"u32"},{name:"N",type:"u32"},{name:"num_heads",type:"u32"},{name:"head_size",type:"u32"},{name:"hidden_size",type:"u32"},{name:"ldb",type:"u32"}];return`
  const TILE_SIZE = ${s}u;
  var<workgroup> tileInput: array<${w}, ${s*s}>;
  var<workgroup> tileWeightQ: array<${w}, ${s*s}>;
  var<workgroup> tileWeightK: array<${w}, ${s*s}>;
  var<workgroup> tileWeightV: array<${w}, ${s*s}>;
  ${f.registerUniforms(T).declareVariables(b,x,$,m,y,_)}
  ${f.mainStart([s,s,1])}
    let batchIndex = workgroup_id.z / uniforms.num_heads;
    let headNumber = workgroup_id.z % uniforms.num_heads;
    let m = global_id.y;
    let n = global_id.x;

    let inputOffset = batchIndex * (uniforms.M * uniforms.K) + m * uniforms.K;
    let biasOffsetQ = headNumber * uniforms.head_size;
    let biasOffsetK = uniforms.hidden_size + biasOffsetQ;
    let biasOffsetV = uniforms.hidden_size + biasOffsetK;

    var valueQ = ${w}(0);
    var valueK = ${w}(0);
    var valueV = ${w}(0);
    for (var w: u32 = 0u; w < uniforms.K; w += TILE_SIZE) {
      if (m < uniforms.M && w + local_id.x < uniforms.K) {
        tileInput[TILE_SIZE * local_id.y + local_id.x] = input[inputOffset + w + local_id.x];
      }
      if (n < uniforms.N && w + local_id.y < uniforms.K) {
        let offset = n + (w + local_id.y) * uniforms.ldb;
        tileWeightQ[TILE_SIZE * local_id.y + local_id.x] = weight[biasOffsetQ + offset];
        tileWeightK[TILE_SIZE * local_id.y + local_id.x] = weight[biasOffsetK + offset];
        tileWeightV[TILE_SIZE * local_id.y + local_id.x] = weight[biasOffsetV + offset];
      }
      workgroupBarrier();
      for (var k: u32 = 0u; k<TILE_SIZE && w+k < uniforms.K; k++) {
        let inputTileOffset = TILE_SIZE * local_id.y + k;
        let weightTileOffset = TILE_SIZE * k + local_id.x;
        valueQ += tileInput[inputTileOffset] * tileWeightQ[weightTileOffset];
        valueK += tileInput[inputTileOffset] * tileWeightK[weightTileOffset];
        valueV += tileInput[inputTileOffset] * tileWeightV[weightTileOffset];
      }

      workgroupBarrier();
    }

    let headOffset = (m * uniforms.N + n) % uniforms.head_size;
    valueQ += bias[headOffset + biasOffsetQ];
    valueK += bias[headOffset + biasOffsetK];
    valueV += bias[headOffset + biasOffsetV];

    let offset = workgroup_id.z * uniforms.M * uniforms.N;
    if (m < uniforms.M && n < uniforms.N) {
      let outputIdx = offset + m * uniforms.N + n;
      output_q[outputIdx] = valueQ;
      output_k[outputIdx] = valueK;
      output_v[outputIdx] = valueV;
    }
  }`};return e.compute({name:"AttentionPrepare",shaderCache:{inputDependencies:["type","type","type"]},getRunData:()=>({outputs:[{dims:r,dataType:e.inputs[0].dataType,gpuDataType:0},{dims:r,dataType:e.inputs[0].dataType,gpuDataType:0},{dims:r,dataType:e.inputs[0].dataType,gpuDataType:0}],dispatchGroup:o,programUniforms:d}),getShaderSource:c},{inputs:l,outputs:[-1,-1,-1]})},Pp=(e,t)=>{let r=Do(e.inputs,t),[i,a,n]=Wo(e,r);return hi(e,i,a,n,e.inputs[4],void 0,void 0,void 0,e.inputs[5],r)}}),Lo,qo,Vo,Up,kg=L(()=>{We(),ie(),se(),xe(),oe(),Lo=(e,t)=>{if(!e||e.length!==5)throw new Error("BatchNormalization requires 5 inputs");let r=(i,a,n)=>{let s=a.length;if(s!==i.length)throw new Error(`${n}: num dimensions != ${s}`);a.forEach((o,l)=>{if(o!==i[l])throw new Error(`${n}: dim[${l}] do not match`)})};if(e[0].dims.length>1){let i=t.format==="NHWC"?t.spatial?e[0].dims.slice(-1):e[0].dims.slice(-1).concat(e[0].dims.slice(1,e[0].dims.length-1)):e[0].dims.slice(1,t.spatial?2:void 0);r(e[1].dims,i,"Invalid input scale"),r(e[2].dims,i,"Invalid input B"),r(e[3].dims,i,"Invalid input mean"),r(e[4].dims,i,"Invalid input var")}else r(e[1].dims,[1],"Invalid input scale"),r(e[2].dims,[1],"Invalid input B"),r(e[3].dims,[1],"Invalid input mean"),r(e[4].dims,[1],"Invalid input var")},qo=(e,t)=>{let{epsilon:r,spatial:i,format:a}=t,n=e[0].dims,s=i?$e(n[n.length-1]):1,o=a==="NHWC"&&n.length>1?s:1,l=O.size(n)/s,d=i,c=d?n.length:n,f=M("x",e[0].dataType,e[0].dims,s),m=M("scale",e[1].dataType,e[1].dims,o),y=M("bias",e[2].dataType,e[2].dims,o),_=M("inputMean",e[3].dataType,e[3].dims,o),b=M("inputVar",e[4].dataType,e[4].dims,o),x=X("y",e[0].dataType,c,s),$=()=>{let T="";if(i)T=`let cOffset = ${n.length===1?"0u":a==="NHWC"?`outputIndices[${n.length-1}] / ${s}`:"outputIndices[1]"};`;else if(a==="NCHW")T=`
            ${x.indicesSet("outputIndices","0","0")}
            let cOffset = ${x.indicesToOffset("outputIndices")};`;else{T=`var cIndices = ${m.type.indices}(0);
                       cIndices[0] = outputIndices[${n.length-1}];`;for(let C=1;C<m.rank;C++)T+=`cIndices[${C}] = outputIndices[${C}];`;T+=`let cOffset = ${m.indicesToOffset("cIndices")};`}return T},w=T=>`
  const epsilon = ${r};
  ${T.registerUniform("outputSize","u32").declareVariables(f,m,y,_,b,x)}
  ${T.mainStart()}
  ${T.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
    var outputIndices = ${x.offsetToIndices(`global_idx * ${s}`)};
    ${$()}
    let scale = ${m.getByOffset("cOffset")};
    let bias = ${y.getByOffset("cOffset")};
    let inputMean = ${_.getByOffset("cOffset")};
    let inputVar = ${b.getByOffset("cOffset")};
    let x = ${f.getByOffset("global_idx")};
    let value = (x - inputMean) * inverseSqrt(inputVar + epsilon) * scale + bias;
    ${x.setByOffset("global_idx","value")}
  }`;return{name:"BatchNormalization",shaderCache:{hint:`${t.epsilon}_${t.format}_${i}_${s}`,inputDependencies:d?["rank","type","type","type","type"]:void 0},getShaderSource:w,getRunData:()=>({outputs:[{dims:e[0].dims,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(l/64)},programUniforms:d?[{type:12,data:l},...J(n)]:[{type:12,data:l}]})}},Vo=e=>he(e),Up=(e,t)=>{let{inputs:r,outputCount:i}=e,a=Vo({...t,outputCount:i});if(ge.webgpu.validateInputContent&&Lo(r,a),t.trainingMode)throw new Error("BatchNormalization trainingMode is not supported yet.");e.compute(qo(r,a))}}),jo,Fo,Wp,Eg=L(()=>{se(),oe(),jo=e=>{if(e[0].dims.length!==3)throw new Error("input should have 3 dimensions");if(![320,640,1280].includes(e[0].dims[2]))throw new Error("number of channels should be 320, 640 or 1280");if(e[1].dims.length!==1)throw new Error("bias is expected to have 1 dimensions");if(e[0].dims[2]!==e[1].dims[0])throw new Error("last dimension of input and bias are not the same")},Fo=e=>{let t=e[0].dims,r=e[0].dims[2],i=O.size(t)/4,a=e[0].dataType,n=M("input",a,t,4),s=M("bias",a,[r],4),o=M("residual",a,t,4),l=X("output",a,t,4);return{name:"BiasAdd",getRunData:()=>({outputs:[{dims:t,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(i/64)}}),getShaderSource:d=>`
  const channels = ${r}u / 4;
  ${d.declareVariables(n,s,o,l)}

  ${d.mainStart()}
    ${d.guardAgainstOutOfBoundsWorkgroupSizes(i)}
    let value = ${n.getByOffset("global_idx")}
      + ${s.getByOffset("global_idx % channels")} + ${o.getByOffset("global_idx")};
    ${l.setByOffset("global_idx","value")}
  }`}},Wp=e=>{jo(e.inputs),e.compute(Fo(e.inputs))}}),Go,ce,Lp,qp,Vp,jp,Fp,Gp,Hp,Kp,Yp,Ho,Zp,Xp,Qp,Jp,li,ec,Li,tc,ic,rc,ac,nc,sc,oc,uc,lc,dc,pc,cc,fc,hc,mc,gc,Fr,yc,Ea,za,_c,bc,wc,Ko,Yo,vc,on=L(()=>{ie(),se(),xe(),oe(),Go=(e,t,r,i,a,n,s)=>{let o=Math.ceil(t/4),l="";typeof a=="string"?l=`${a}(a)`:l=a("a");let d=M("inputData",r,[o],4),c=X("outputData",i,[o],4),f=[{name:"vec_size",type:"u32"}];return s&&f.push(...s),`
      ${e.registerUniforms(f).declareVariables(d,c)}

  ${n??""}

  ${e.mainStart()}
    ${e.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.vec_size")}

    let a = ${d.getByOffset("global_idx")};
    ${c.setByOffset("global_idx",l)}
  }`},ce=(e,t,r,i,a,n=e.dataType,s,o)=>{let l=[{type:12,data:Math.ceil(O.size(e.dims)/4)}];return s&&l.push(...s),{name:t,shaderCache:{hint:a,inputDependencies:["type"]},getShaderSource:d=>Go(d,O.size(e.dims),e.dataType,n,r,i,o),getRunData:d=>({outputs:[{dims:e.dims,dataType:n}],dispatchGroup:{x:Math.ceil(O.size(d[0].dims)/64/4)},programUniforms:l})}},Lp=e=>{e.compute(ce(e.inputs[0],"Abs","abs"))},qp=e=>{e.compute(ce(e.inputs[0],"Acos","acos"))},Vp=e=>{e.compute(ce(e.inputs[0],"Acosh","acosh"))},jp=e=>{e.compute(ce(e.inputs[0],"Asin","asin"))},Fp=e=>{e.compute(ce(e.inputs[0],"Asinh","asinh"))},Gp=e=>{e.compute(ce(e.inputs[0],"Atan","atan"))},Hp=e=>{e.compute(ce(e.inputs[0],"Atanh","atanh"))},Kp=e=>he(e),Yp=(e,t)=>{let r;switch(t.to){case 10:r="vec4<f16>";break;case 1:r="vec4<f32>";break;case 12:r="vec4<u32>";break;case 6:r="vec4<i32>";break;case 9:r="vec4<bool>";break;default:throw new RangeError(`not supported type (specified in attribute 'to' from 'Cast' operator): ${t.to}`)}e.compute(ce(e.inputs[0],"Cast",r,void 0,t.cacheKey,t.to))},Ho=e=>{let t,r,i=e.length>=2&&e[1].data!==0,a=e.length>=3&&e[2].data!==0;switch(e[0].dataType){case 1:t=i?e[1].getFloat32Array()[0]:-34028234663852886e22,r=a?e[2].getFloat32Array()[0]:34028234663852886e22;break;case 10:t=i?e[1].getUint16Array()[0]:64511,r=a?e[2].getUint16Array()[0]:31743;break;default:throw new Error("Unsupport data type")}return he({min:t,max:r})},Zp=(e,t)=>{let r=t||Ho(e.inputs),i=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"Clip",a=>`clamp(${a}, vec4<${i}>(uniforms.min), vec4<${i}>(uniforms.max))`,void 0,r.cacheKey,void 0,[{type:e.inputs[0].dataType,data:r.min},{type:e.inputs[0].dataType,data:r.max}],[{name:"min",type:i},{name:"max",type:i}]),{inputs:[0]})},Xp=e=>{e.compute(ce(e.inputs[0],"Ceil","ceil"))},Qp=e=>{e.compute(ce(e.inputs[0],"Cos","cos"))},Jp=e=>{e.compute(ce(e.inputs[0],"Cosh","cosh"))},li=e=>he(e),ec=(e,t)=>{let r=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"Elu",i=>`elu_vf32(${i})`,`
  const elu_alpha_ = ${r}(${t.alpha});

  fn elu_f32(a: ${r}) -> ${r} {
  return select((exp(a) - 1.0) * elu_alpha_, a, a >= 0.0);
  }

  fn elu_vf32(v: vec4<${r}>) -> vec4<${r}> {
  return vec4(elu_f32(v.x), elu_f32(v.y), elu_f32(v.z), elu_f32(v.w));
  }`,t.cacheKey))},Li=(e="f32")=>`
const r0: ${e} = 0.3275911;
const r1: ${e} = 0.254829592;
const r2: ${e} = -0.284496736;
const r3: ${e} = 1.421413741;
const r4: ${e} = -1.453152027;
const r5: ${e} = 1.061405429;

fn erf_vf32(v: vec4<${e}>) -> vec4<${e}> {
  let absv = abs(v);
  let x = 1.0 / (1.0 + r0 * absv);
  return sign(v) * (1.0 - ((((r5 * x + r4) * x + r3) * x + r2) * x + r1) * x * exp(-absv * absv));
}`,tc=e=>{let t=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"Erf",r=>`erf_vf32(${r})`,Li(t)))},ic=e=>{e.compute(ce(e.inputs[0],"Exp","exp"))},rc=e=>{e.compute(ce(e.inputs[0],"Floor","floor"))},ac=e=>{let t=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"Gelu",r=>`0.5 * ${r} * (1.0 + erf_vf32(${r} * 0.7071067811865475))`,Li(t)))},nc=(e,t)=>{let r=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"LeakyRelu",i=>`select(leaky_relu_alpha_ * ${i}, ${i}, ${i} >= vec4<${r}>(0.0))`,`const leaky_relu_alpha_ = ${r}(${t.alpha});`,t.cacheKey))},sc=e=>{e.compute(ce(e.inputs[0],"Not",t=>`!${t}`))},oc=e=>{e.compute(ce(e.inputs[0],"Neg",t=>`-${t}`))},uc=e=>{e.compute(ce(e.inputs[0],"Reciprocal",t=>`1.0/${t}`))},lc=e=>{let t=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"Relu",r=>`select(vec4<${t}>(0.0), ${r}, ${r} > vec4<${t}>(0.0))`))},dc=e=>{e.compute(ce(e.inputs[0],"Sigmoid",t=>`(1.0 / (1.0 + exp(-${t})))`))},pc=e=>he(e),cc=(e,t)=>{let r=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"HardSigmoid",i=>`max(vec4<${r}>(0.0), min(vec4<${r}>(1.0), ${t.alpha} * ${i} + vec4<${r}>(${t.beta})))`,void 0,t.cacheKey))},fc=e=>{e.compute(ce(e.inputs[0],"Sin","sin"))},hc=e=>{e.compute(ce(e.inputs[0],"Sinh","sinh"))},mc=e=>{e.compute(ce(e.inputs[0],"Sqrt","sqrt"))},gc=e=>{e.compute(ce(e.inputs[0],"Tan","tan"))},Fr=e=>`sign(${e}) * (1 - exp(-2 * abs(${e}))) / (1 + exp(-2 * abs(${e})))`,yc=e=>{e.compute(ce(e.inputs[0],"Tanh",Fr))},Ea=(e="f32")=>`
const fast_gelu_a: ${e} = 0.5;
const fast_gelu_b: ${e} = 0.7978845608028654;
const fast_gelu_c: ${e} = 0.035677408136300125;

fn tanh_v(v: vec4<${e}>) -> vec4<${e}> {
  return ${Fr("v")};
}
`,za=e=>`(fast_gelu_a + fast_gelu_a * tanh_v(${e} * (fast_gelu_c * ${e} * ${e} + fast_gelu_b))) * ${e}`,_c=e=>{let t=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"FastGelu",za,Ea(t),void 0,e.inputs[0].dataType))},bc=(e,t)=>{let r=ze(e.inputs[0].dataType);return e.compute(ce(e.inputs[0],"ThresholdedRelu",i=>`select(vec4<${r}>(0.0), ${i}, ${i} > thresholded_relu_alpha_)`,`const thresholded_relu_alpha_ = vec4<${r}>(${t.alpha});`,t.cacheKey)),0},wc=e=>{e.compute(ce(e.inputs[0],"Log","log"))},Ko=(e,t)=>`
const alpha = vec4<${e}>(${t});
const one = ${e}(1.0);
const zero = ${e}(0.0);

fn quick_gelu_impl(x: vec4<${e}>) -> vec4<${e}> {
  let v = x *alpha;
  var x1 : vec4<${e}>;
  for (var i = 0; i < 4; i = i + 1) {
    if (v[i] >= zero) {
      x1[i] = one / (one + exp(-v[i]));
    } else {
      x1[i] = one - one / (one + exp(v[i]));
    }
  }
  return x * x1;
}
`,Yo=e=>`quick_gelu_impl(${e})`,vc=(e,t)=>{let r=ze(e.inputs[0].dataType);e.compute(ce(e.inputs[0],"QuickGelu",Yo,Ko(r,t.alpha),t.cacheKey,e.inputs[0].dataType))}}),Zo,Xo,$c,zg=L(()=>{se(),oe(),on(),Zo=e=>{if(e[0].dims.length!==3)throw new Error("input should have 3 dimensions");if(![2560,5120,10240].includes(e[0].dims[2]))throw new Error("hidden state should be 2560, 5120 or 10240");if(e[1].dims.length!==1)throw new Error("bias is expected to have 1 dimensions");if(e[0].dims[2]!==e[1].dims[0])throw new Error("last dimension of input and bias are not the same")},Xo=e=>{let t=e[0].dims.slice();t[2]=t[2]/2;let r=M("input",e[0].dataType,e[0].dims,4),i=M("bias",e[0].dataType,[e[0].dims[2]],4),a=X("output",e[0].dataType,t,4),n=O.size(t)/4,s=Ie(e[0].dataType);return{name:"BiasSplitGelu",getRunData:()=>({outputs:[{dims:t,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(n/64)}}),getShaderSource:o=>`
  const M_SQRT2 = sqrt(2.0);
  const halfChannels = ${e[0].dims[2]/4/2}u;

  ${o.declareVariables(r,i,a)}

  ${Li(s)}

  ${o.mainStart()}
    ${o.guardAgainstOutOfBoundsWorkgroupSizes(n)}
    let biasIdx = global_idx % halfChannels;
    let batchIndex = global_idx / halfChannels;
    let inputOffset = biasIdx + batchIndex * halfChannels * 2;
    let valueLeft = input[inputOffset] + bias[biasIdx];
    let valueRight = input[inputOffset + halfChannels] + bias[biasIdx + halfChannels];
    let geluRight = valueRight * 0.5 * (erf_vf32(valueRight / M_SQRT2) + 1);

    ${a.setByOffset("global_idx","valueLeft * geluRight")}
  }`}},$c=e=>{Zo(e.inputs),e.compute(Xo(e.inputs))}}),Qo,Jo,Fe,xc,Cc,Tc,Sc,Ic,kc,Ec,zc,Ac,Oc,Ag=L(()=>{ie(),se(),oe(),Qo=(e,t,r,i,a,n,s,o,l,d,c,f)=>{let m,y;typeof o=="string"?m=y=(w,T)=>`${o}((${w}),(${T}))`:typeof o=="function"?m=y=o:(m=o.scalar,y=o.vector);let _=X("outputData",c,i.length,4),b=M("aData",l,t.length,4),x=M("bData",d,r.length,4),$;if(a)if(n){let w=O.size(t)===1,T=O.size(r)===1,C=t.length>0&&t[t.length-1]%4===0,I=r.length>0&&r[r.length-1]%4===0;w||T?$=_.setByOffset("global_idx",y(w?`${b.type.value}(${b.getByOffset("0")}.x)`:b.getByOffset("global_idx"),T?`${x.type.value}(${x.getByOffset("0")}.x)`:x.getByOffset("global_idx"))):$=`
            let outputIndices = ${_.offsetToIndices("global_idx * 4u")};
            let offsetA = ${b.broadcastedIndicesToOffset("outputIndices",_)};
            let offsetB = ${x.broadcastedIndicesToOffset("outputIndices",_)};
            ${_.setByOffset("global_idx",y(s||C?b.getByOffset("offsetA / 4u"):`${b.type.value}(${b.getByOffset("offsetA / 4u")}[offsetA % 4u])`,s||I?x.getByOffset("offsetB / 4u"):`${x.type.value}(${x.getByOffset("offsetB / 4u")}[offsetB % 4u])`))}
          `}else $=_.setByOffset("global_idx",y(b.getByOffset("global_idx"),x.getByOffset("global_idx")));else{if(!n)throw new Error("no necessary to use scalar implementation for element-wise binary op implementation.");let w=(T,C,I="")=>{let z=`aData[indexA${C}][componentA${C}]`,k=`bData[indexB${C}][componentB${C}]`;return`
            let outputIndices${C} = ${_.offsetToIndices(`global_idx * 4u + ${C}u`)};
            let offsetA${C} = ${b.broadcastedIndicesToOffset(`outputIndices${C}`,_)};
            let offsetB${C} = ${x.broadcastedIndicesToOffset(`outputIndices${C}`,_)};
            let indexA${C} = offsetA${C} / 4u;
            let indexB${C} = offsetB${C} / 4u;
            let componentA${C} = offsetA${C} % 4u;
            let componentB${C} = offsetB${C} % 4u;
            ${T}[${C}] = ${I}(${m(z,k)});
          `};c===9?$=`
            var data = vec4<u32>(0);
            ${w("data",0,"u32")}
            ${w("data",1,"u32")}
            ${w("data",2,"u32")}
            ${w("data",3,"u32")}
            outputData[global_idx] = dot(vec4<u32>(0x1, 0x100, 0x10000, 0x1000000), vec4<u32>(data));`:$=`
            ${w("outputData[global_idx]",0)}
            ${w("outputData[global_idx]",1)}
            ${w("outputData[global_idx]",2)}
            ${w("outputData[global_idx]",3)}
          `}return`
        ${e.registerUniform("vec_size","u32").declareVariables(b,x,_)}

        ${f??""}

        ${e.mainStart()}
        ${e.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.vec_size")}
        ${$}
      }`},Jo=(e,t,r,i,a,n,s=r.dataType)=>{let o=r.dims.map(b=>Number(b)??1),l=i.dims.map(b=>Number(b)??1),d=!O.areEqual(o,l),c=o,f=O.size(o),m=!1,y=!1,_=[d];if(d){let b=Wt.calcShape(o,l,!1);if(!b)throw new Error("Can't perform binary op on the given tensors");c=b.slice(),f=O.size(c);let x=O.size(o)===1,$=O.size(l)===1,w=o.length>0&&o[o.length-1]%4===0,T=l.length>0&&l[l.length-1]%4===0;_.push(x),_.push($),_.push(w),_.push(T);let C=1;for(let I=1;I<c.length;I++){let z=o[o.length-I],k=l[l.length-I];if(z===k)C*=z;else break}C%4===0?(y=!0,m=!0):(x||$||w||T)&&(m=!0)}else m=!0;return _.push(m),{name:e,shaderCache:{hint:t+_.map(b=>b.toString()).join("_"),inputDependencies:["rank","rank"]},getShaderSource:b=>Qo(b,o,l,c,m,d,y,a,r.dataType,i.dataType,s,n),getRunData:()=>({outputs:[{dims:c,dataType:s}],dispatchGroup:{x:Math.ceil(f/64/4)},programUniforms:[{type:12,data:Math.ceil(O.size(c)/4)},...J(o,l,c)]})}},Fe=(e,t,r,i,a,n)=>{e.compute(Jo(t,a??"",e.inputs[0],e.inputs[1],r,i,n))},xc=e=>{Fe(e,"Add",(t,r)=>`${t}+${r}`)},Cc=e=>{Fe(e,"Div",(t,r)=>`${t}/${r}`)},Tc=e=>{Fe(e,"Equal",{scalar:(t,r)=>`u32(${t}==${r})`,vector:(t,r)=>`vec4<u32>(${t}==${r})`},void 0,void 0,9)},Sc=e=>{Fe(e,"Mul",(t,r)=>`${t}*${r}`)},Ic=e=>{let t=M("input",e.inputs[0].dataType,e.inputs[0].dims).type.value;Fe(e,"Pow",{scalar:(r,i)=>`pow_custom(${r},${i})`,vector:(r,i)=>`pow_vector_custom(${r},${i})`},`
    fn pow_custom(a : ${t}, b : ${t}) -> ${t} {
      if (b == ${t}(0.0)) {
        return ${t}(1.0);
      } else if (a < ${t}(0.0) && f32(b) != floor(f32(b))) {
        return ${t}(pow(f32(a), f32(b))); // NaN
      }
      return select(sign(a), ${t}(1.0), round(f32(abs(b) % ${t}(2.0))) != 1.0) * ${t}(${t==="i32"?"round":""}(pow(f32(abs(a)), f32(b))));
    }
    fn pow_vector_custom(a : vec4<${t}>, b : vec4<${t}>) -> vec4<${t}> {
      // TODO: implement vectorized pow
      return vec4<${t}>(pow_custom(a.x, b.x), pow_custom(a.y, b.y), pow_custom(a.z, b.z), pow_custom(a.w, b.w));
    }
      `)},kc=e=>{Fe(e,"Sub",(t,r)=>`${t}-${r}`)},Ec=e=>{Fe(e,"Greater",{scalar:(t,r)=>`u32(${t}>${r})`,vector:(t,r)=>`vec4<u32>(${t}>${r})`},void 0,void 0,9)},zc=e=>{Fe(e,"Less",{scalar:(t,r)=>`u32(${t}<${r})`,vector:(t,r)=>`vec4<u32>(${t}<${r})`},void 0,void 0,9)},Ac=e=>{Fe(e,"GreaterOrEqual",{scalar:(t,r)=>`u32(${t}>=${r})`,vector:(t,r)=>`vec4<u32>(${t}>=${r})`},void 0,void 0,9)},Oc=e=>{Fe(e,"LessOrEqual",{scalar:(t,r)=>`u32(${t}<=${r})`,vector:(t,r)=>`vec4<u32>(${t}<=${r})`},void 0,void 0,9)}}),eu,tu,iu,ru,Rc,Bc,Og=L(()=>{ie(),se(),xe(),oe(),eu=(e,t)=>{if(!e||e.length<1)throw new Error("too few inputs");let r=0,i=e[r],a=i.dataType,n=i.dims.length;e.forEach((s,o)=>{if(o!==r){if(s.dataType!==a)throw new Error("input tensors should be one type");if(s.dims.length!==n)throw new Error("input tensors should have the same shape");s.dims.forEach((l,d)=>{if(d!==t&&l!==i.dims[d])throw new Error("non concat dimensions must match")})}})},tu=(e,t)=>`
  fn calculateInputIndex(index: u32) -> u32 {
    let sizeInConcatAxis = array<u32, ${e}u>(${t});
    for (var i: u32 = 0u; i < ${e}; i += 1u ) {
      if (index < sizeInConcatAxis[i]) {
        return i;
      }
    }
    return ${e}u;
  }`,iu=(e,t)=>{let r=e.length,i=[];for(let a=0;a<r;++a){let n=t.setByOffset("global_idx",e[a].getByIndices("indices"));r===1?i.push(n):a===0?i.push(`if (inputIndex == ${a}u) { ${n} }`):a===r-1?i.push(`else { ${n} }`):i.push(`else if (inputIndex == ${a}) { ${n} }`)}return i.join(`
`)},ru=(e,t,r,i)=>{let a=O.size(r),n=new Array(e.length),s=new Array(e.length),o=0,l=[],d=[],c=[{type:12,data:a}];for(let b=0;b<e.length;++b)o+=e[b].dims[t],n[b]=o,d.push(e[b].dims.length),s[b]=M(`input${b}`,i,d[b]),l.push("rank"),c.push({type:12,data:n[b]});for(let b=0;b<e.length;++b)c.push(...J(e[b].dims));c.push(...J(r));let f=X("output",i,r.length),m=f.indicesGet("indices",t),y=Array.from(Array(n.length).keys()).map(b=>`uniforms.sizeInConcatAxis${b}`).join(","),_=b=>`

  ${(()=>{b.registerUniform("outputSize","u32");for(let x=0;x<e.length;x++)b.registerUniform(`sizeInConcatAxis${x}`,"u32");return b.declareVariables(...s,f)})()}

  ${tu(n.length,y)}

  ${b.mainStart()}
    ${b.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}

    var indices = ${f.offsetToIndices("global_idx")};

    let inputIndex = calculateInputIndex(${m});
    if (inputIndex != 0u) {
      let sizeInConcatAxis = array<u32, ${n.length}u>(${y});
      ${m} -= sizeInConcatAxis[inputIndex - 1u];
    }

    ${iu(s,f)}
  }`;return{name:"Concat",shaderCache:{hint:`${t}`,inputDependencies:l},getRunData:()=>({outputs:[{dims:r,dataType:i}],dispatchGroup:{x:Math.ceil(a/64)},programUniforms:c}),getShaderSource:_}},Rc=(e,t)=>{let r=e.inputs,i=r[0].dims,a=O.normalizeAxis(t.axis,i.length);eu(r,a);let n=i.slice();n[a]=r.reduce((o,l)=>o+(l.dims.length>a?l.dims[a]:0),0);let s=r.filter(o=>O.size(o.dims)>0);e.compute(ru(s,a,n,r[0].dataType),{inputs:s})},Bc=e=>he({axis:e.axis})}),zt,At,Ot,un,Bt=L(()=>{ie(),se(),zt=(e,t,r="f32")=>{switch(e.activation){case"Relu":return`value = max(value, ${t}(0.0));`;case"Sigmoid":return`value = (${t}(1.0) / (${t}(1.0) + exp(-value)));`;case"Clip":return`value = clamp(value, ${t}(${r}(uniforms.clip_min)), ${t}(${r}(uniforms.clip_max)));`;case"HardSigmoid":return`value = max(${t}(0.0), min(${t}(1.0), ${r}(uniforms.alpha) * value + ${r}(uniforms.beta)));`;case"LeakyRelu":return`value = select(${r}(uniforms.alpha) * value, value, value >= ${t}(0.0));`;case"Tanh":return`let e2x = exp(-2.0 * abs(value));
              value = sign(value) * (1.0 - e2x) / (1.0 + e2x);
        `;case"":return"";default:throw new Error(`Unsupported activation ${e.activation}`)}},At=(e,t)=>{e.activation==="Clip"?t.push({type:1,data:e.clipMax},{type:1,data:e.clipMin}):e.activation==="HardSigmoid"?t.push({type:1,data:e.alpha},{type:1,data:e.beta}):e.activation==="LeakyRelu"&&t.push({type:1,data:e.alpha})},Ot=(e,t)=>{e.activation==="Clip"?t.push({name:"clip_max",type:"f32"},{name:"clip_min",type:"f32"}):e.activation==="HardSigmoid"?t.push({name:"alpha",type:"f32"},{name:"beta",type:"f32"}):e.activation==="LeakyRelu"&&t.push({name:"alpha",type:"f32"})},un=e=>{let t=e?.activation||"";if(t==="HardSigmoid"){let[r,i]=e?.activation_params||[.2,.5];return{activation:t,alpha:r,beta:i}}else if(t==="Clip"){let[r,i]=e?.activation_params||[sp,op];return{activation:t,clipMax:i,clipMin:r}}else if(t==="LeakyRelu"){let[r]=e?.activation_params||[.01];return{activation:t,alpha:r}}return{activation:t}}}),Ee,Mc,ln=L(()=>{Ee=(e,t)=>{switch(e){case 1:return t;case 2:return`vec2<${t}>`;case 3:return`vec3<${t}>`;case 4:return`vec4<${t}>`;default:throw new Error(`${e}-component is not supported.`)}},Mc=e=>`
      ${e?"value = value + getBiasByOutputCoords(coords);":""}
      `}),Dc,Rg=L(()=>{Dc=e=>`
fn getIndexFromCoords4D(coords : vec4<i32>, shape : vec4<i32>) -> i32 {
  return dot(coords, vec4<i32>(
      shape.y * shape.z * shape.w, shape.z * shape.w, shape.w, 1));
}
fn getOutputIndexFromCoords(coords : vec4<i32>) -> i32 {
  return dot(coords, vec4<i32>(
    i32(${e}.x), i32(${e}.y), i32(${e}.z), 1));
}
`}),pi,dn,pn=L(()=>{ie(),se(),oe(),Bt(),pi=(e,t,r,i,a)=>{let n=i-r;return`
      ${Array.from({length:r}).map((s,o)=>`
      if (${Q(t.shape,o,t.rank)} != 1) {
        ${t.indicesSet(e,o,Q(a,o+n,i))}
      } else {
        ${t.indicesSet(e,o,0)}
      }`).join("")}
`},dn=(e,t,r,i,a=!1,n)=>{let s=e[0].dims,o=e[1].dims,l=s[s.length-2],d=o[o.length-1],c=s[s.length-1],f=$e(d),m=$e(c),y=$e(l),_=O.size(r)/f/y,b=e.length>2,x=i?i.slice(0,-2):r.slice(0,-2),$=[O.size(x),l,d],w=[{type:12,data:_},{type:12,data:l},{type:12,data:d},{type:12,data:c}];At(t,w),w.push(...J(x,s,o)),b&&w.push(...J(e[2].dims)),w.push(...J($));let T=C=>{let I=an("batch_dims",e[0].dataType,x.length),z=M("a",e[0].dataType,s.length,m),k=M("b",e[1].dataType,o.length,f),A=X("output",e[0].dataType,$.length,f),D=Ie(A.type.tensor),V=zt(t,A.type.value,D),G=[z,k],H="";if(b){let re=a?f:1;G.push(M("bias",e[2].dataType,e[2].dims.length,re)),H=`${a?`value += bias[col / ${re}];`:`value += ${A.type.value}(bias[row + i]);`}`}let F=[{name:"output_size",type:"u32"},{name:"M",type:"u32"},{name:"N",type:"u32"},{name:"K",type:"u32"}];Ot(t,F);let W=()=>{let re=`var a_data: ${z.type.value};`;for(let ee=0;ee<m;ee++)re+=`
              let b_data${ee} = b[(b_offset + (k + ${ee}) * uniforms.N + col) / ${f}];`;for(let ee=0;ee<y;ee++){re+=`a_data = a[(a_offset + (row + ${ee}) * uniforms.K + k) / ${m}];`;for(let K=0;K<m;K++)re+=`
            values[${ee}] = fma(${k.type.value}(a_data${m===1?"":`[${K}]`}), b_data${K}, values[${ee}]);
`}return re};return`
  ${C.registerUniforms(F).registerInternalVariables(I).declareVariables(...G,A)}
  ${C.mainStart()}
    ${C.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
    let col = (global_idx % (uniforms.N / ${f})) * ${f};
    var index1 = global_idx / (uniforms.N / ${f});
    let stride1 = uniforms.M / ${y};
    let row = (index1 % stride1) * ${y};
    let batch = index1 / stride1;

    ${r.length===2?"":`let batch_indices = ${I.offsetToIndices("batch")};`}

    var a_indices: ${z.type.indices};
    ${pi("a_indices",z,z.rank-2,I.rank,"batch_indices")}
    ${z.indicesSet("a_indices",z.rank-2,0)}
    ${z.indicesSet("a_indices",z.rank-1,0)}
    let a_offset = ${z.indicesToOffset("a_indices")};

    var b_indices: ${k.type.indices};
    ${pi("b_indices",k,k.rank-2,I.rank,"batch_indices")}
    ${k.indicesSet("b_indices",k.rank-2,0)}
    ${k.indicesSet("b_indices",k.rank-1,0)}
    let b_offset = ${k.indicesToOffset("b_indices")};
    var values: array<${A.type.value}, ${y}>;
    for (var k: u32 = 0u; k < uniforms.K; k = k + ${m}) {
      ${W()}
    }
    for (var i = 0u; i < ${y}u; i++) {
      var value = values[i];
      ${H}
      ${V}
      let cur_indices = ${A.type.indices}(batch, row + i, col);
      let offset = ${A.indicesToOffset("cur_indices")};
      ${A.setByOffset(`offset / ${f}`,"value")};
    }
  }
  `};return{name:"MatMulNaive",shaderCache:{hint:`${t.activation};${f};${m};${y};${a}`,inputDependencies:b?["rank","rank","rank"]:["rank","rank"]},getRunData:()=>({outputs:[{dims:n?n(r):r,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(_/64)},programUniforms:w}),getShaderSource:T}}}),au,nu,Aa,Gr,su,Oa,ou,Hi,cn=L(()=>{ie(),se(),oe(),Bt(),pn(),ln(),au=(e,t)=>e?`
        mm_Asub[inputRow][inputCol] = mm_readA(batch,
          kStart + inputRow,
          globalRowStart / innerElementSize + inputCol${t?", batchIndices":""});
        `:`
        mm_Asub[inputRow][inputCol] = mm_readA(batch,
          globalRow + innerRow,
          kStart / innerElementSize + inputCol${t?", batchIndices":""});
        `,nu=(e,t)=>e?`
        let ACached0 = mm_Asub[k * innerElementSize][localRow];
        let ACached1 = mm_Asub[k * innerElementSize + 1][localRow];
        let ACached2 = mm_Asub[k * innerElementSize + 2][localRow];
        ${t===3?"":"let ACached3 = mm_Asub[k * innerElementSize + 3][localRow];"}
        for (var i = 0; i < rowPerThread; i = i + 1) {
          acc[i] = BCached0 * ACached0[i] + acc[i];
          acc[i] = BCached1 * ACached1[i] + acc[i];
          acc[i] = BCached2 * ACached2[i] + acc[i];
          ${t===3?"":"acc[i] = BCached3 * ACached3[i] + acc[i];"}
        }`:`
        for (var i = 0; i < rowPerThread; i = i + 1) {
          let ACached = mm_Asub[tileRow + i][k];
          acc[i] = BCached0 * ACached.x + acc[i];
          acc[i] = BCached1 * ACached.y + acc[i];
          acc[i] = BCached2 * ACached.z + acc[i];
          ${t===3?"":"acc[i] = BCached3 * ACached.w + acc[i];"}
        }`,Aa=(e,t,r="f32",i,a=!1,n=32,s=!1,o=32)=>{let l=t[1]*e[1],d=t[0]*e[0],c=a?l:n,f=a?n:l,m=c/t[0],y=n/t[1];if(!((a&&m===4&&e[1]===4||!a&&(m===3||m===4))&&c%t[0]===0&&n%t[1]===0&&e[0]===4))throw new Error(`If transposeA ${a} is true, innerElementSize ${m} and workPerThread[1] ${e[1]} must be 4.
      Otherwise, innerElementSize ${m} must be 3 or 4.
  tileAWidth ${c} must be divisible by workgroupSize[0]${t[0]}. tileInner ${n} must be divisible by workgroupSize[1] ${t[1]}. colPerThread ${e[0]} must be 4.`);return`
var<workgroup> mm_Asub: array<array<vec${m}<${r}>, ${c/m}>, ${f}>;
var<workgroup> mm_Bsub: array<array<vec4<${r}>, ${d/e[0]}>, ${n}>;

const rowPerThread = ${e[1]};
const colPerThread = ${e[0]};
const innerElementSize = ${m};
const tileInner = ${n};

@compute @workgroup_size(${t[0]}, ${t[1]}, ${t[2]})
fn main(@builtin(local_invocation_id) localId : vec3<u32>,
        @builtin(global_invocation_id) globalId : vec3<u32>,
        @builtin(workgroup_id) workgroupId : vec3<u32>) {
  let localRow = i32(localId.y);
  let tileRow = localRow * rowPerThread;
  let tileCol = i32(localId.x);

  let globalRow =i32(globalId.y) * rowPerThread;
  let globalCol = i32(globalId.x);
  let batch = ${s?"0":"i32(globalId.z)"};
  ${i?`let batchIndices = ${i.offsetToIndices("u32(batch)")};`:""}
  let globalRowStart = i32(workgroupId.y) * ${l};

  let num_tiles = ${s?`${Math.ceil(o/n)}`:"(uniforms.dim_inner - 1) / tileInner + 1"};
  var kStart = ${s?`i32(globalId.z) * ${o}`:"0"};

  var acc: array<vec4<${r}>, rowPerThread>;

  // Loop over shared dimension.
  let tileRowB = localRow * ${y};
  for (var t = 0; t < num_tiles; t = t + 1) {
      // Load one tile of A into local memory.
      for (var innerRow = 0; innerRow < rowPerThread; innerRow = innerRow + 1) {
          let inputRow = tileRow + innerRow;
          let inputCol = tileCol;
          ${au(a,i)}
      }

      // Load one tile of B into local memory.
      for (var innerRow = 0; innerRow < ${y}; innerRow = innerRow + 1) {
          let inputRow = tileRowB + innerRow;
          let inputCol = tileCol;
          mm_Bsub[inputRow][inputCol] = mm_readB(batch, kStart + inputRow, globalCol${i?", batchIndices":""});
      }
      kStart = kStart + tileInner;
      workgroupBarrier();

      // Compute acc values for a single thread.
      for (var k = 0; k < tileInner / innerElementSize; k = k + 1) {
          let BCached0 = mm_Bsub[k * innerElementSize][tileCol];
          let BCached1 = mm_Bsub[k * innerElementSize + 1][tileCol];
          let BCached2 = mm_Bsub[k * innerElementSize + 2][tileCol];
          ${m===3?"":"let BCached3 = mm_Bsub[k * innerElementSize + 3][tileCol];"}

          ${nu(a,m)}
      }

      workgroupBarrier();
  }

  for (var innerRow = 0; innerRow < rowPerThread; innerRow = innerRow + 1) {
      mm_write(batch, globalRow + innerRow, globalCol, acc[innerRow]);
  }
}`},Gr=(e,t)=>e?`
            mm_Asub[inputRow][inputCol] = mm_readA(batch,
              kStart + inputRow,
              globalRowStart + inputCol${t?", batchIndices":""});
            `:`
            mm_Asub[inputRow][inputCol] = mm_readA(batch,
              globalRowStart + inputRow,
              kStart + inputCol${t?", batchIndices":""});
            `,su=e=>e?"let ACached = mm_Asub[k][tileRow + innerRow];":"let ACached = mm_Asub[tileRow + innerRow][k];",Oa=(e,t,r="f32",i,a=!1,n=32,s=!1,o=32,l=!1)=>{let d=e[1]*t[1],c=e[0]*t[0],f=a?d:n,m=a?n:d;if(!(m%t[1]===0&&f%t[0]===0&&n%t[1]===0))throw new Error(`tileAHight ${m} must be divisible by workgroupSize[1]${t[1]}, tileAWidth ${f} must be divisible by workgroupSize[0]${t[0]}, tileInner ${n} must be divisible by workgroupSize[1]${t[1]}`);let y=m/t[1],_=f/t[0],b=n/t[1],x=l?`
    let localRow = i32(localId.y);
    let localCol = i32(localId.x);
    let globalRowStart = i32(workgroupId.y) * ${d};
    let globalColStart = i32(workgroupId.x) * ${c};

    // Loop over shared dimension.
    for (var t = 0; t < num_tiles; t = t + 1) {
      // Load one tile of A into local memory.
      for (var inputRow = localRow; inputRow < ${m}; inputRow = inputRow + ${t[1]}) {
        for (var inputCol = localCol; inputCol < ${f}; inputCol = inputCol + ${t[0]}) {
          ${Gr(a,i)}
        }
      }
      // Load one tile of B into local memory.
      for (var inputRow = localRow; inputRow < ${n}; inputRow = inputRow + ${t[1]}) {
            for (var inputCol = localCol; inputCol < ${c}; inputCol = inputCol + ${t[0]}) {
          mm_Bsub[inputRow][inputCol] = mm_readB(batch,
            kStart + inputRow,
            globalColStart + inputCol${i?", batchIndices":""});
        }
      }
      kStart = kStart + tileInner;
      workgroupBarrier();

      // Compute acc values for a single thread.
      var BCached : array<${r}, colPerThread>;
      for (var k = 0; k < tileInner; k = k + 1) {
        for (var inner = 0; inner < colPerThread; inner = inner + 1) {
          BCached[inner] = mm_Bsub[k][localCol + inner * ${t[0]}];
        }
        for (var innerRow = 0; innerRow < rowPerThread; innerRow = innerRow + 1) {
          let ACached = ${a?`mm_Asub[k][localRow + innerRow * ${t[1]}];`:`mm_Asub[localRow + innerRow * ${t[1]}][k];`}
          for (var innerCol = 0; innerCol < colPerThread; innerCol = innerCol + 1) {
            acc[innerRow][innerCol] = acc[innerRow][innerCol] +
                ACached * BCached[innerCol];
          }
        }
      }
      workgroupBarrier();
    }
    for (var innerRow = 0; innerRow < rowPerThread; innerRow = innerRow + 1) {
      let gRow = globalRowStart + localRow + innerRow * ${t[1]};
      for (var innerCol = 0; innerCol < colPerThread; innerCol = innerCol + 1) {
        let gCol = globalColStart + localCol + innerCol * ${t[0]};
        mm_write(batch, gRow, gCol, acc[innerRow][innerCol]);
      }
    }
    `:`
let tileRow = i32(localId.y) * rowPerThread;
let tileCol = i32(localId.x) * colPerThread;

let globalRow = i32(globalId.y) * rowPerThread;
let globalCol = i32(globalId.x) * colPerThread;
let globalRowStart = i32(workgroupId.y) * ${d};

let tileRowA = i32(localId.y) * ${y};
let tileColA = i32(localId.x) * ${_};
let tileRowB = i32(localId.y) * ${b};
// Loop over shared dimension.
for (var t = 0; t < num_tiles; t = t + 1) {
  // Load one tile of A into local memory.
  for (var innerRow = 0; innerRow < ${y}; innerRow = innerRow + 1) {
    for (var innerCol = 0; innerCol < ${_}; innerCol = innerCol + 1) {
      let inputRow = tileRowA + innerRow;
      let inputCol = tileColA + innerCol;
      ${Gr(a,i)}
    }
  }

  // Load one tile of B into local memory.
  for (var innerRow = 0; innerRow < ${b}; innerRow = innerRow + 1) {
    for (var innerCol = 0; innerCol < colPerThread; innerCol = innerCol + 1) {
      let inputRow = tileRowB + innerRow;
      let inputCol = tileCol + innerCol;
      mm_Bsub[inputRow][inputCol] = mm_readB(batch,
        kStart + inputRow,
        globalCol + innerCol${i?", batchIndices":""});
    }
  }
  kStart = kStart + tileInner;
  workgroupBarrier();

  // Compute acc values for a single thread.
  var BCached : array<${r}, colPerThread>;
  for (var k = 0; k < tileInner; k = k + 1) {
    for (var inner = 0; inner < colPerThread; inner = inner + 1) {
      BCached[inner] = mm_Bsub[k][tileCol + inner];
    }

    for (var innerRow = 0; innerRow < rowPerThread; innerRow = innerRow + 1) {
      ${su(a)}
      for (var innerCol = 0; innerCol < colPerThread; innerCol = innerCol + 1) {
        acc[innerRow][innerCol] = acc[innerRow][innerCol] + ACached * BCached[innerCol];
      }
    }
  }

  workgroupBarrier();
}

for (var innerRow = 0; innerRow < rowPerThread; innerRow = innerRow + 1) {
  for (var innerCol = 0; innerCol < colPerThread; innerCol = innerCol + 1) {
    mm_write(batch, globalRow + innerRow, globalCol + innerCol,
        acc[innerRow][innerCol]);
  }
}
`;return`
  var<workgroup> mm_Asub : array<array<${r}, ${f}>, ${m}>;
  var<workgroup> mm_Bsub : array<array<${r}, ${c}>, ${n}>;
  const rowPerThread = ${e[1]};
  const colPerThread = ${e[0]};
  const tileInner = ${n};

@compute @workgroup_size(${t[0]}, ${t[1]}, ${t[2]})
fn main(@builtin(local_invocation_id) localId : vec3<u32>,
        @builtin(global_invocation_id) globalId : vec3<u32>,
        @builtin(workgroup_id) workgroupId : vec3<u32>) {
    let batch = ${s?"0":"i32(globalId.z)"};
    ${i?`let batchIndices = ${i.offsetToIndices("u32(batch)")};`:""}
    let num_tiles = ${s?`${Math.ceil(o/n)}`:"(uniforms.dim_inner - 1) / tileInner + 1"};
    var kStart = ${s?`i32(globalId.z) * ${o}`:"0"};

    var acc : array<array<${r}, colPerThread>, rowPerThread>;
    ${x}
  }
`},ou=(e,t,r,i,a=!1)=>{let[n,s,o,l]=i,d=Ie(i[0].type.tensor);return`
    fn mm_readA(batch: i32, row: i32, colIn: i32, batchIndices: ${n.type.indices}) -> ${Ee(e,d)} {
      var value = ${Ee(e,d)}(0.0);
      let col = colIn * ${e};
      if(row < uniforms.dim_a_outer && col < uniforms.dim_inner)
      {
        var aIndices: ${s.type.indices};
        ${pi("aIndices",s,s.rank-2,n.rank,"batchIndices")}
        ${s.indicesSet("aIndices",s.rank-2,"u32(row)")}
        ${s.indicesSet("aIndices",s.rank-1,"u32(colIn)")}
        value = ${s.getByIndices("aIndices")};
      }
      return value;
    }

    fn mm_readB(batch: i32, row: i32, colIn: i32, batchIndices: ${n.type.indices}) -> ${Ee(e,d)} {
      var value = ${Ee(e,d)}(0.0);
      let col = colIn * ${e};
      if(row < uniforms.dim_inner && col < uniforms.dim_b_outer)
      {
        var bIndices: ${o.type.indices};
        ${pi("bIndices",o,o.rank-2,n.rank,"batchIndices")}
        ${o.indicesSet("bIndices",o.rank-2,"u32(row)")}
        ${o.indicesSet("bIndices",o.rank-1,"u32(colIn)")}
        value = ${o.getByIndices("bIndices")};
      }
      return value;
    }

    fn mm_write(batch: i32, row: i32, colIn: i32, valueIn: ${Ee(e,d)}) {
      let col = colIn * ${e};
      if (row < uniforms.dim_a_outer && col < uniforms.dim_b_outer) {
        var value = valueIn;
        let coords = vec3<i32>(batch, row, colIn);
        ${t?`value = value + ${a?"bias[colIn]":`${Ee(e,d)}(bias[row])`};`:""}
        ${r}
        ${l.setByIndices("vec3<u32>(coords)","value")}
      }
    }
    `},Hi=(e,t,r,i,a=!1,n)=>{let s=e[0].dims,o=e[1].dims,l=s.slice(0,-2),d=o.slice(0,-2),c=i?i.slice(0,-2):r.slice(0,-2),f=O.size(c),m=s[s.length-2],y=s[s.length-1],_=o[o.length-1],b=y%4===0&&_%4===0,x=m<=8?[4,1,1]:[4,4,1],$=[8,8,1],w=[Math.ceil(_/$[0]/x[0]),Math.ceil(m/$[1]/x[1]),Math.ceil(f/$[2]/x[2])],T=b?4:1,C=[...l,m,y/T],I=C.length,z=[...d,y,_/T],k=z.length,A=[f,m,_/T],D=[{type:6,data:m},{type:6,data:_},{type:6,data:y}];At(t,D),D.push(...J(c,C,z));let V=["rank","rank"],G=e.length>2;G&&(D.push(...J(e[2].dims)),V.push("rank")),D.push(...J(A));let H=F=>{let W=c.length,re=an("batchDims",e[0].dataType,W,1),ee=Ie(e[0].dataType),K=M("a",e[0].dataType,I,T),ne=M("b",e[1].dataType,k,T),Y=X("result",e[0].dataType,A.length,T),ye=[K,ne];if(G){let N=a?T:1;ye.push(M("bias",e[2].dataType,e[2].dims.length,N))}let U=[{name:"dim_a_outer",type:"i32"},{name:"dim_b_outer",type:"i32"},{name:"dim_inner",type:"i32"}];Ot(t,U);let j=Ie(Y.type.tensor),ae=zt(t,Y.type.value,j),pe=ou(T,G,ae,[re,K,ne,Y],a);return`
  ${F.registerUniforms(U).registerInternalVariables(re).declareVariables(...ye,Y)}
  ${pe}
  ${b?Aa(x,$,ee,re):Oa(x,$,ee,re)}
                   `};return{name:"MatMul",shaderCache:{hint:`${x};${t.activation};${b};${a}`,inputDependencies:V},getRunData:()=>({outputs:[{dims:n?n(r):r,dataType:e[0].dataType}],dispatchGroup:{x:w[0],y:w[1],z:w[2]},programUniforms:D}),getShaderSource:H}}}),uu,Nc,Bg=L(()=>{ie(),st(),oe(),Bt(),ln(),Rg(),cn(),uu=(e,t,r,i,a=!1,n,s=4,o=4,l=4,d="f32")=>{let c=D=>{switch(D){case 1:return"resData = x[xIndex];";case 3:return`resData = vec3<${d}>(x[xIndex], x[xIndex + 1], x[xIndex + 2]);`;case 4:return"resData = x[xIndex / 4];";default:throw new Error(`innerElementSize ${D} is not supported.`)}},f=D=>{switch(D){case 1:return"return w[row * i32(uniforms.w_shape[3]) + colIn];";case 4:return"return w[row * i32(uniforms.w_shape[3]) / 4 + colIn];";default:throw new Error(`innerElementSize ${D} is not supported.`)}},m=e?`
    let coord = vec4<i32>(batch, xRow, xCol, xCh);
    `:`
    let coord = vec4<i32>(batch, xCh, xRow, xCol);
    `,y=e?`
    let coords = vec4<i32>(
      batch,
      row / outWidth,
      row % outWidth,
      col);
    `:`
    let coords = vec4<i32>(
      batch,
      row,
      col / outWidth,
      col % outWidth);
    `,_=e?"i32(uniforms.x_shape[1])":"i32(uniforms.x_shape[2])",b=e?"i32(uniforms.x_shape[2])":"i32(uniforms.x_shape[3])",x=e?"row":"col",$=e?"col":"row",w=`
    let inChannels = i32(uniforms.w_shape[2]);
    let outWidth = ${e?"i32(uniforms.result_shape[2])":"i32(uniforms.result_shape[3])"};
    let outRow = ${x} / outWidth;
    let outCol = ${x} % outWidth;

    let WRow = ${$} / (i32(uniforms.w_shape[1]) * inChannels);
    let WCol = ${$} / inChannels % i32(uniforms.w_shape[1]);
    let xRow = outRow * uniforms.stride[0] + uniforms.dilation[0] * WRow - uniforms.pad[0];
    let xCol = outCol * uniforms.stride[1] + uniforms.dilation[1] * WCol - uniforms.pad[1];
    let xCh = ${$} % inChannels;
    var resData = ${Ee(s,d)}(0.0);
    // The bounds checking is always needed since we use it to pad zero for
    // the 'same' padding type.
    if (xRow >= 0 && xRow < ${_} && xCol >= 0 && xCol < ${b}) {
      ${m}
      let xIndex = getIndexFromCoords4D(coord, vec4<i32>(uniforms.x_shape));
      ${c(s)}
    }
    return resData;`,T=e?t&&i?`
    let col = colIn * ${s};
    ${w}`:`
    let col = colIn * ${s};
    if (row < uniforms.dim_a_outer && col < uniforms.dim_inner) {
      ${w}
    }
    return ${Ee(s,d)}(0.0);`:i&&r?`
    let col = colIn * ${s};
    ${w}`:`
    let col = colIn * ${s};
    if (row < uniforms.dim_inner && col < uniforms.dim_b_outer) {
      ${w}
    }
    return ${Ee(s,d)}(0.0);`,C=e?i&&r?f(o):`
    let col = colIn * ${o};
    if (row < uniforms.dim_inner && col < uniforms.dim_b_outer) {
      ${f(o)}
    }
    return ${Ee(o,d)}(0.0);`:`
    let col = colIn * ${o};
    if (row < uniforms.dim_inner && col < uniforms.dim_a_outer) {
      ${f(o)}
    }
    return ${Ee(o,d)}(0.0);`,I=Ee(l,d),z=Ee(e?s:o,d),k=Ee(e?o:s,d),A=zt(n,I,d);return`
    fn mm_readA(batch: i32, row : i32, colIn : i32) -> ${z} {
      ${e?T:C}
    }

    fn mm_readB(batch: i32, row : i32, colIn : i32) -> ${k} {
      ${e?C:T}
    }

    fn mm_write(batch: i32, row : i32, colIn : i32, valueIn : ${I}) {
      let col = colIn * ${l};
      if (row < uniforms.dim_a_outer && col < uniforms.dim_b_outer)
      {
      var value = valueIn;
      let outWidth = ${e?"i32(uniforms.result_shape[2])":"i32(uniforms.result_shape[3])"};
      ${y}
      ${Mc(a)}
      ${A}
      setOutputAtCoords(coords[0], coords[1], coords[2], coords[3], value);
      }
    }`},Nc=(e,t,r,i,a,n,s,o,l)=>{let d=t.format==="NHWC",c=d?e[0].dims[3]:e[0].dims[1],f=r[0],m=d?r[2]:r[3],y=d?r[1]:r[2],_=d?r[3]:r[1],b=d&&(c%4===0||c%3===0)&&_%4===0,x=d?_:m*y,$=d?m*y:_,w=[8,8,1],T=i<=8?[4,1,1]:[4,4,1],C=[Math.ceil(x/w[0]/T[0]),Math.ceil($/w[1]/T[1]),Math.ceil(f/w[2]/T[2])];de("verbose",()=>`[conv2d_mm_webgpu] dispatch = ${C}`);let I=b?d&&c%4!==0?3:4:1,z=w[1]*T[1],k=w[0]*T[0],A=Math.max(w[0]*I,w[1]),D=i%z===0,V=a%k===0,G=n%A===0,H=b?[I,4,4]:[1,1,1],F=[{type:6,data:i},{type:6,data:a},{type:6,data:n},{type:6,data:[t.pads[0],t.pads[1]]},{type:6,data:t.strides},{type:6,data:t.dilations}];At(t,F),F.push(...J(e[0].dims,e[1].dims));let W=["rank","rank"];s&&(F.push(...J(e[2].dims)),W.push("rank")),F.push(...J(r));let re=ee=>{let K=[{name:"dim_a_outer",type:"i32"},{name:"dim_b_outer",type:"i32"},{name:"dim_inner",type:"i32"},{name:"pad",type:"i32",length:2},{name:"stride",type:"i32",length:2},{name:"dilation",type:"i32",length:2}];Ot(t,K);let ne=b?4:1,Y=Ie(e[0].dataType),ye=`
      fn setOutputAtIndex(flatIndex : i32, value : ${b?`vec4<${Y}>`:Y}) {
        result[flatIndex] = ${b?`vec4<${Y}>`:Y}(value);
      }
      fn setOutputAtCoords(d0 : i32, d1 : i32, d2 : i32, d3 : i32, value : ${b?`vec4<${Y}>`:Y}) {
        let flatIndex = getOutputIndexFromCoords(vec4<i32>(d0, d1, d2, d3));
        setOutputAtIndex(flatIndex ${b?"/ 4":""}, value);
      }`,U=M("x",e[0].dataType,e[0].dims.length,I===3?1:I),j=M("w",e[1].dataType,e[1].dims.length,ne),ae=[U,j],pe=X("result",e[0].dataType,r.length,ne);if(s){let N=M("bias",e[2].dataType,e[2].dims.length,ne);ae.push(N),ye+=`
        fn getBiasByOutputCoords(coords : vec4<i32>) -> ${b?`vec4<${Y}>`:Y} {
          return bias[coords.${d?"w":"y"}${b?"/ 4":""}];
        }`}return`
        ${Dc("uniforms.result_strides")}
        //struct Uniforms { xShape : vec4<i32>, wShape : vec4<i32>, outShape : vec4<i32>,
        //  outShapeStrides: vec3<i32>, filterDims : vec2<i32>, pad : vec2<i32>, stride : vec2<i32>,
        //  dilation : vec2<i32>, dimAOuter : i32, dimBOuter : i32, dimInner : i32 };
        ${ee.registerUniforms(K).declareVariables(...ae,pe)}
        ${ye}
        ${uu(d,D,V,G,s,t,H[0],H[1],H[2],Y)}
        ${b?Aa(T,w,Y,void 0,!d,A):Oa(T,w,Y,void 0,!d,A,!1,void 0,o)}`};return{name:"Conv2DMatMul",shaderCache:{hint:`${t.cacheKey};${I};${b};${D};${V};${G};${z};${k};${A}`,inputDependencies:W},getRunData:()=>({outputs:[{dims:l?l(r):r,dataType:e[0].dataType}],dispatchGroup:{x:C[0],y:C[1],z:C[2]},programUniforms:F}),getShaderSource:re}}}),lu,Hr,ti,du,Kr,pu,Pc,Uc,Mg=L(()=>{ie(),st(),se(),oe(),Bt(),ln(),lu=e=>{let t=1;for(let r=0;r<e.length;r++)t*=e[r];return t},Hr=e=>typeof e=="number"?[e,e,e]:e,ti=(e,t)=>t<=1?e:e+(e-1)*(t-1),du=(e,t,r,i=1)=>{let a=ti(t,i);return Math.floor((e[0]*(r-1)-r+a)/2)},Kr=(e,t,r,i,a)=>{a==null&&(a=du(e,t[0],i[0]));let n=[0,0,0,r];for(let s=0;s<3;s++)e[s]+2*a>=t[s]&&(n[s]=Math.trunc((e[s]-t[s]+2*a)/i[s]+1));return n},pu=(e,t,r,i,a,n,s,o,l,d)=>{let c,f,m,y;if(e==="VALID"&&(e=0),typeof e=="number"){c={top:e,bottom:e,left:e,right:e,front:e,back:e};let _=Kr([t,r,i,1],[o,l,d],1,[a,n,s],e);f=_[0],m=_[1],y=_[2]}else if(Array.isArray(e)){if(!e.every((b,x,$)=>b===$[0]))throw Error(`Unsupported padding parameter: ${e}`);c={top:e[0],bottom:e[1],left:e[2],right:e[3],front:e[4],back:e[5]};let _=Kr([t,r,i,1],[o,l,d],1,[a,n,s],e[0]);f=_[0],m=_[1],y=_[2]}else if(e==="SAME_UPPER"){f=Math.ceil(t/a),m=Math.ceil(r/n),y=Math.ceil(i/s);let _=(f-1)*a+o-t,b=(m-1)*n+l-r,x=(y-1)*s+d-i,$=Math.floor(_/2),w=_-$,T=Math.floor(b/2),C=b-T,I=Math.floor(x/2),z=x-I;c={top:T,bottom:C,left:I,right:z,front:$,back:w}}else throw Error(`Unknown padding parameter: ${e}`);return{padInfo:c,outDepth:f,outHeight:m,outWidth:y}},Pc=(e,t,r,i,a,n=!1,s="channelsLast")=>{let o,l,d,c,f;if(s==="channelsLast")[o,l,d,c,f]=e;else if(s==="channelsFirst")[o,f,l,d,c]=e;else throw new Error(`Unknown dataFormat ${s}`);let[m,,y,_,b]=t,[x,$,w]=Hr(r),[T,C,I]=Hr(i),z=ti(y,T),k=ti(_,C),A=ti(b,I),{padInfo:D,outDepth:V,outHeight:G,outWidth:H}=pu(a,l,d,c,x,$,w,z,k,A),F=n?m*f:m,W=[0,0,0,0,0];return s==="channelsFirst"?W=[o,F,V,G,H]:s==="channelsLast"&&(W=[o,V,G,H,F]),{batchSize:o,dataFormat:s,inDepth:l,inHeight:d,inWidth:c,inChannels:f,outDepth:V,outHeight:G,outWidth:H,outChannels:F,padInfo:D,strideDepth:x,strideHeight:$,strideWidth:w,filterDepth:y,filterHeight:_,filterWidth:b,effectiveFilterDepth:z,effectiveFilterHeight:k,effectiveFilterWidth:A,dilationDepth:T,dilationHeight:C,dilationWidth:I,inShape:e,outShape:W,filterShape:t}},Uc=(e,t,r,i,a,n)=>{let s=n==="channelsLast";s?e[0].dims[3]:e[0].dims[1];let o=[64,1,1],l={x:r.map((x,$)=>$)},d=[Math.ceil(lu(l.x.map(x=>r[x]))/o[0]),1,1];de("verbose",()=>`[conv3d_naive_webgpu] dispatch = ${d}`);let c=1,f=O.size(r),m=[{type:12,data:f},{type:12,data:i},{type:12,data:a},{type:12,data:t.strides},{type:12,data:t.dilations}];At(t,m),m.push(...J(e[0].dims,e[1].dims));let y=["rank","rank"],_=e.length===3;_&&(m.push(...J(e[2].dims)),y.push("rank")),m.push(...J(r));let b=x=>{let $=[{name:"output_size",type:"u32"},{name:"filter_dims",type:"u32",length:i.length},{name:"pads",type:"u32",length:a.length},{name:"strides",type:"u32",length:t.strides.length},{name:"dilations",type:"u32",length:t.dilations.length}];Ot(t,$);let w=1,T=Ie(e[0].dataType),C=M("x",e[0].dataType,e[0].dims.length,c),I=M("W",e[1].dataType,e[1].dims.length,w),z=[C,I],k=X("result",e[0].dataType,r.length,w),A="";if(_){let G=M("bias",e[2].dataType,e[2].dims.length,w);z.push(G),A+=`
        fn getBiasByOutputCoords(coords : array<u32, 5>) -> ${T} {
          return bias[${s?Q("coords",4,5):Q("coords",1,5)}];
        }`}let D=Ee(c,T),V=zt(t,D,T);return`
            ${A}
            fn getX(d0 : u32, d1 : u32, d2 : u32, d3 : u32, d4 : u32) -> f32 {
              let aIndices = array<u32, 5>(d0, d1, d2, d3, d4);
              return ${C.getByIndices("aIndices")};
            }
            fn getW(d0 : u32, d1 : u32, d2 : u32, d3 : u32, d4 : u32) -> f32 {
              let aIndices = array<u32, 5>(d0, d1, d2, d3, d4);
              return ${I.getByIndices("aIndices")};
            }
          ${x.registerUniforms($).declareVariables(...z,k)}
          ${x.mainStart()}
          ${x.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
              let coords = ${k.offsetToIndices("global_idx")};
              let batch = ${Q("coords",0,C.rank)};
              let d2 = ${s?Q("coords",C.rank-1,C.rank):Q("coords",1,C.rank)};
              let xFRCCorner = vec3<u32>(${s?Q("coords",1,C.rank):Q("coords",2,C.rank)},
              ${s?Q("coords",2,C.rank):Q("coords",3,C.rank)},
              ${s?Q("coords",3,C.rank):Q("coords",4,C.rank)}) * uniforms.strides - uniforms.pads;
              let xFCorner = xFRCCorner.x;
              let xRCorner = xFRCCorner.y;
              let xCCorner = xFRCCorner.z;
              let xShapeY = ${s?Q("uniforms.x_shape",1,C.rank):Q("uniforms.x_shape",2,C.rank)};
              let xShapeZ = ${s?Q("uniforms.x_shape",2,C.rank):Q("uniforms.x_shape",3,C.rank)};
              let xShapeW = ${s?Q("uniforms.x_shape",3,C.rank):Q("uniforms.x_shape",4,C.rank)};
              let xShapeU = ${s?Q("uniforms.x_shape",4,C.rank):Q("uniforms.x_shape",1,C.rank)};
              let inputDepthNearestVec4 = (xShapeU / 4) * 4;
              let inputDepthVec4Remainder = xShapeU % 4;

              var value = 0.0;
              for (var wF = 0u; wF < uniforms.filter_dims[0]; wF++) {
                let xF = xFCorner + wF * uniforms.dilations[0];
                if (xF < 0 || xF >= xShapeY) {
                  continue;
                }

                for (var wR = 0u; wR < uniforms.filter_dims[1]; wR++) {
                  let xR = xRCorner + wR * uniforms.dilations[1];
                  if (xR < 0 || xR >= xShapeZ) {
                    continue;
                  }

                  for (var wC = 0u; wC < uniforms.filter_dims[2]; wC++) {
                    let xC = xCCorner + wC * uniforms.dilations[2];
                    if (xC < 0 || xC >= xShapeW) {
                      continue;
                    }

                    for (var d1 = 0u; d1 < inputDepthNearestVec4; d1 += 4) {
                      ${s?`let xValues = vec4<f32>(
                               getX(batch, xF, xR, xC, d1),
                               getX(batch, xF, xR, xC, d1 + 1),
                               getX(batch, xF, xR, xC, d1 + 2),
                               getX(batch, xF, xR, xC, d1 + 3));
                            `:`let xValues = vec4<f32>(
                               getX(batch, d1, xF, xR, xC),
                               getX(batch, d1 + 1, xF, xR, xC),
                               getX(batch, d1 + 2, xF, xR, xC),
                               getX(batch, d1 + 3, xF, xR, xC));
                            `}
                            let wValues = vec4<f32>(
                              getW(d2, d1, wF, wR, wC),
                              getW(d2, d1 + 1, wF, wR, wC),
                              getW(d2, d1 + 2, wF, wR, wC),
                              getW(d2, d1 + 3, wF, wR, wC));
                      value += dot(xValues, wValues);
                    }
                    if (inputDepthVec4Remainder == 1) {
                        ${s?`value += getX(batch, xF, xR, xC, inputDepthNearestVec4)
                          * getW(d2, inputDepthNearestVec4, wF, wR, wC);`:`value += getX(batch, inputDepthNearestVec4, xF, xR, xC)
                          * getW(d2, inputDepthNearestVec4, wF, wR, wC);`}
                    } else if (inputDepthVec4Remainder == 2) {
                      ${s?`let xValues = vec2<f32>(
                        getX(batch, xF, xR, xC, inputDepthNearestVec4),
                        getX(batch, xF, xR, xC, inputDepthNearestVec4 + 1));
                      `:`let xValues = vec2<f32>(
                        getX(batch, inputDepthNearestVec4, xF, xR, xC),
                        getX(batch, inputDepthNearestVec4 + 1, xF, xR, xC));
                    `}
                    let wValues = vec2<f32>(
                      getW(d2, inputDepthNearestVec4, wF, wR, wC),
                      getW(d2, inputDepthNearestVec4 + 1, wF, wR, wC));
                      value += dot(xValues, wValues);
                    } else if (inputDepthVec4Remainder == 3) {
                      ${s?`let xValues = vec3<f32>(
                        getX(batch, xF, xR, xC, inputDepthNearestVec4),
                        getX(batch, xF, xR, xC, inputDepthNearestVec4 + 1),
                        getX(batch, xF, xR, xC, inputDepthNearestVec4 + 2));
                      `:`let xValues = vec3<f32>(
                        getX(batch, inputDepthNearestVec4, xF, xR, xC),
                        getX(batch, inputDepthNearestVec4 + 1, xF, xR, xC),
                        getX(batch, inputDepthNearestVec4 + 2, xF, xR, xC));
                    `}
                    let wValues = vec3<f32>(
                      getW(d2, inputDepthNearestVec4, wF, wR, wC),
                      getW(d2, inputDepthNearestVec4 + 1, wF, wR, wC),
                      getW(d2, inputDepthNearestVec4 + 2, wF, wR, wC));
                      value += dot(xValues, wValues);
                    }
                  }
                }
              }
              ${_?"value = value + getBiasByOutputCoords(coords)":""};
              ${V}
              result[global_idx] = f32(value);
          }`};return{name:"Conv3DNaive",shaderCache:{hint:`${t.cacheKey};${s};${c};${_}`,inputDependencies:y},getRunData:()=>({outputs:[{dims:r,dataType:e[0].dataType}],dispatchGroup:{x:d[0],y:d[1],z:d[2]},programUniforms:m}),getShaderSource:b}}}),Wc,Lc,Dg=L(()=>{ie(),se(),oe(),Bt(),Wc=(e,t,r,i)=>{let a=e.length>2,n=a?"value += b[output_channel];":"",s=e[0].dims,o=e[1].dims,l=t.format==="NHWC",d=l?r[3]:r[1],c=d/t.group,f=l&&c>=4?$e(d):1,m=O.size(r)/f,y=[{type:12,data:m},{type:12,data:t.dilations},{type:12,data:[t.strides[0],t.strides[1]]},{type:12,data:[t.pads[0],t.pads[1]]},{type:12,data:c}];At(t,y),y.push(...J(s,[o[0],o[1],o[2],o[3]/f]));let _=a?["rank","rank","rank"]:["rank","rank"];y.push(...J([r[0],r[1],r[2],r[3]/f]));let b=x=>{let $=X("output",e[0].dataType,r.length,f),w=Ie($.type.tensor),T=zt(t,$.type.value,w),C=M("x",e[0].dataType,s.length),I=M("w",e[1].dataType,o.length,f),z=[C,I];a&&z.push(M("b",e[2].dataType,e[2].dims,f));let k=[{name:"output_size",type:"u32"},{name:"dilations",type:"u32",length:t.dilations.length},{name:"strides",type:"u32",length:2},{name:"pads",type:"u32",length:2},{name:"output_channels_per_group",type:"u32"}];Ot(t,k);let A=l?`
      for (var wHeight: u32 = 0u; wHeight < uniforms.w_shape[0]; wHeight++) {
        let xHeight = xRCCorner.x + wHeight * uniforms.dilations[0];

        if (xHeight < 0u || xHeight >= uniforms.x_shape[1]) {
          continue;
        }

        for (var wWidth: u32 = 0u; wWidth < uniforms.w_shape[1]; wWidth++) {
          let xWidth = xRCCorner.y + wWidth * uniforms.dilations[1];
          if (xWidth < 0u || xWidth >= uniforms.x_shape[2]) {
            continue;
          }

          for (var wInChannel: u32 = 0u; wInChannel < uniforms.w_shape[2]; wInChannel++) {
            let input_channel = in_channel_offset + wInChannel;
            let xVal = ${C.get("batch","xHeight","xWidth","input_channel")};
            let wVal = ${I.get("wHeight","wWidth","wInChannel","output_channel")};
            value += xVal * wVal;
          }
        }
      }
      `:`
      for (var wInChannel: u32 = 0u; wInChannel < uniforms.w_shape[1]; wInChannel++) {
        let input_channel = in_channel_offset + wInChannel;
        for (var wHeight: u32 = 0u; wHeight < uniforms.w_shape[2]; wHeight++) {
          let xHeight = xRCCorner.x + wHeight * uniforms.dilations[0];

          if (xHeight < 0u || xHeight >= uniforms.x_shape[2]) {
            continue;
          }

          for (var wWidth: u32 = 0u; wWidth < uniforms.w_shape[3]; wWidth++) {
            let xWidth = xRCCorner.y + wWidth * uniforms.dilations[1];
            if (xWidth < 0u || xWidth >= uniforms.x_shape[3]) {
              continue;
            }

            let xVal = ${C.get("batch","input_channel","xHeight","xWidth")};
            let wVal = ${I.get("output_channel","wInChannel","wHeight","wWidth")};
            value += xVal * wVal;
          }
        }
      }
      `;return`
  ${x.registerUniforms(k).declareVariables(...z,$)}

  ${x.mainStart()}
    ${x.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}

    let outputIndices = ${$.offsetToIndices("global_idx")};
    let batch: u32 = outputIndices[0];
    let output_channel: u32 = outputIndices[${l?3:1}];
    let xRCCorner: vec2<u32> = vec2<u32>(outputIndices[${l?1:2}], outputIndices[${l?2:3}]) * uniforms.strides - uniforms.pads;
    let group_id: u32 = output_channel * ${f} / uniforms.output_channels_per_group;
    var in_channel_offset = group_id * uniforms.w_shape[${l?2:1}];

    var value: ${$.type.value} = ${$.type.value}(0);
    ${A}
    ${n}
    ${T}
    ${$.setByOffset("global_idx","value")}
  }`};return{name:"GroupedConv",shaderCache:{hint:`${t.cacheKey}_${f}`,inputDependencies:_},getRunData:()=>({outputs:[{dims:i?i(r):r,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(m/64)},programUniforms:y}),getShaderSource:b}},Lc=(e,t,r,i)=>{let a=e.length>2,n=$e(r[3]),s=$e(r[2]),o=O.size(r)/n/s,l=[e[0].dims[0],e[0].dims[1],e[0].dims[2],e[0].dims[3]/n],d=[e[1].dims[0],e[1].dims[1],e[1].dims[2],e[1].dims[3]/n],c=[r[0],r[1],r[2],r[3]/n],f=[{type:12,data:o},{type:6,data:[t.strides[0],t.strides[1]]},{type:6,data:[t.pads[0],t.pads[1]]}];At(t,f),f.push(...J(l,d,c));let m=(s-1)*t.strides[1]+d[1],y=_=>{let b=X("output",e[0].dataType,c.length,n),x=Ie(b.type.tensor),$=zt(t,b.type.value,x),w=M("x",e[0].dataType,l.length,n),T=M("w",e[1].dataType,d.length,n),C=[w,T];a&&C.push(M("b",e[2].dataType,e[2].dims,n));let I=a?"value += b[output_channel];":"",z=[{name:"output_size",type:"u32"},{name:"strides",type:"i32",length:2},{name:"pads",type:"i32",length:2}];return Ot(t,z),`
  ${_.registerUniforms(z).declareVariables(...C,b)}
  ${_.mainStart()}
    ${_.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
    let width0 = uniforms.output_shape[3];
    let output_channel = global_idx % width0;
    var index1 = global_idx / width0;
    let width1 = uniforms.output_shape[2] / ${s}u;
    let col = (index1 % width1) * ${s}u;
    index1 = index1 / width1;
    let row = index1 % uniforms.output_shape[1];
    let batch = index1 / uniforms.output_shape[1];

    let x_corner = vec2<i32>(i32(row), i32(col)) * uniforms.strides - uniforms.pads;

    var x_vals: array<${w.type.value}, ${m}>;
    var values: array<${b.type.value}, ${s}>;
    let input_channel = output_channel;
    // Use constant instead of uniform can give better performance for w's height/width.
    for (var w_height: u32 = 0u; w_height < ${d[0]}; w_height++) {
      let x_height = x_corner.x + i32(w_height);
      if (x_height >= 0 && u32(x_height) < uniforms.x_shape[1]) {
        for (var i = 0; i < ${m}; i++) {
          let x_width = x_corner.y + i;
          if (x_width >= 0 && u32(x_width) < uniforms.x_shape[2]) {
            x_vals[i] = ${w.get("batch","u32(x_height)","u32(x_width)","input_channel")};
          } else {
            x_vals[i] = ${w.type.value}(0);
          }
        }
        for (var w_width: u32 = 0u; w_width < ${d[1]}; w_width++) {
          let w_val = ${T.get("w_height","w_width","0","output_channel")};
          for (var i = 0u; i < ${s}u; i++) {
            values[i] = fma(x_vals[i * u32(uniforms.strides[1]) + w_width], w_val, values[i]);
          }
        }
      }
    }

    for (var i = 0u; i < ${s}u; i++) {
      var value = values[i];
      ${I}
      ${$}
      ${b.set("batch","row","col + i","output_channel","value")};
    }
  }`};return{name:"GroupedConv-Vectorize",shaderCache:{hint:`${t.cacheKey};${n};${s};${m};${d[0]};${d[1]}`,inputDependencies:a?["rank","rank","type"]:["rank","rank"]},getRunData:()=>({outputs:[{dims:i?i(r):r,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(o/64)},programUniforms:f}),getShaderSource:y}}}),cu,Ri,fu,Bi,Ra,Yr,hu,mu,Ba,Ng=L(()=>{se(),Bg(),Mg(),cn(),Dg(),Bt(),pn(),_t(),cu=(e,t,r,i,a,n)=>{let s=e[0],o=e.slice(n?1:2,n?3:4),l=o.length,d=t[0],c=t.slice(2).map((m,y)=>m+(m-1)*(r[y]-1)),f=o.map((m,y)=>m+i[y]+i[y+l]).map((m,y)=>Math.floor((m-c[y]+a[y])/a[y]));return f.splice(0,0,s),f.splice(n?3:1,0,d),f},Ri=[2,3,1,0],fu=(e,t)=>{if(!e||e.length!==2&&e.length!==3)throw new Error("Conv requires 2 or 3 inputs");if(e[0].dims.length>5)throw new Error("greater than 5D is not supported");if(e[0].dims.length!==e[1].dims.length)throw new Error("filter does not have same dimension as input");let r=e[0].dims[t.format==="NHWC"?e[0].dims.length-1:1],i=e[1].dims[1]*t.group;if(r!==i)throw new Error("FILTER_IN_CHANNEL should be equal to DATA_CHANNEL");if(e.length===3&&(e[2].dims.length!==1||e[1].dims[0]!==e[2].dims[0]))throw new Error("invalid bias");let a=e[0].dims.length-2;if(t.dilations.length!==a)throw new Error(`dilations should be ${a}D`);if(t.strides.length!==a)throw new Error(`strides should be ${a}D`);if(t.pads.length!==a*2)throw new Error(`pads should be ${a*2}D`);if(t.kernelShape.length!==0&&t.kernelShape.length!==e[1].dims.length-2)throw new Error("invalid kernel shape")},Bi=(e,t)=>{let r=e.kernelShape.slice();r.length<t[1].dims.length-2&&r.push(...Array(t[1].dims.length-2-r.length).fill(0));for(let n=2;n<t[1].dims.length;++n)r[n-2]===0&&(r[n-2]=t[1].dims[n]);let i=e.pads.slice();Fi.adjustPadsBasedOnAutoPad(t[0].dims,e.strides,e.dilations,r,i,e.format==="NHWC",e.autoPad);let a=Object.assign({},e);return Object.assign(a,{kernelShape:r,pads:i}),a},Ra=e=>{let t=un(e),r=e.format,i=["NOTSET","VALID","SAME_UPPER","SAME_LOWER"][e.auto_pad],a=e.dilations,n=e.group,s=e.kernel_shape,o=e.pads,l=e.strides,d=e.w_is_const();return{autoPad:i,format:r,dilations:a,group:n,kernelShape:s,pads:o,strides:l,wIsConst:d,...t,cacheKey:`${e.format};${t.activation};`}},Yr=(e,t,r,i)=>{let a=r.format==="NHWC",n=cu(t[0].dims,t[1].dims,r.dilations,r.pads,r.strides,a);if(r.group!==1){let z=[t[0]];if(a){let k=e.kernelCustomData.wT??e.compute(De(t[1],Ri),{inputs:[1],outputs:[r.wIsConst?-2:-1]})[0];r.wIsConst&&!e.kernelCustomData.wT&&(e.kernelCustomData.wT=k),z.push(k)}else z.push(t[1]);t.length===3&&z.push(t[2]),!e.adapterInfo.isArchitecture("ampere")&&a&&t[1].dims[0]===r.group&&t[1].dims[1]===1&&r.dilations[0]===1&&r.dilations[1]===1?e.compute(Lc(z,r,n,i),{inputs:z}):e.compute(Wc(z,r,n,i),{inputs:z});return}let s=t.length===3,o=t[0].dims[a?1:2],l=t[0].dims[a?2:3],d=t[0].dims[a?3:1],c=t[1].dims[2],f=t[1].dims[3],m=n[a?1:2],y=n[a?2:3],_=n[a?3:1],b=a&&c===o&&f===l&&r.pads[0]===0&&r.pads[1]===0;if(b||c===1&&f===1&&r.dilations[0]===1&&r.dilations[1]===1&&r.strides[0]===1&&r.strides[1]===1&&r.pads[0]===0&&r.pads[1]===0){let z=n[0],k,A,D,V=[];if(a){let F=e.kernelCustomData.wT??e.compute(De(t[1],Ri),{inputs:[1],outputs:[r.wIsConst?-2:-1]})[0];if(r.wIsConst&&!e.kernelCustomData.wT&&(e.kernelCustomData.wT=F),b){let W=o*l*d;k=t[0].reshape([1,z,W]),A=F.reshape([1,W,_]),D=[1,z,_]}else k=t[0].reshape([z,o*l,d]),A=F.reshape([1,d,_]),D=[z,m*y,_];V.push(k),V.push(A)}else k=t[0].reshape([z,d,o*l]),A=t[1].reshape([1,_,d]),D=[z,_,m*y],V.push(A),V.push(k);s&&V.push(t[2]);let G=D[2],H=V[0].dims[V[0].dims.length-1];G<8&&H<8?e.compute(dn(V,r,n,D,a,i),{inputs:V}):e.compute(Hi(V,r,n,D,a,i),{inputs:V});return}let x=!0,$=e.kernelCustomData.wT??e.compute(De(t[1],Ri),{inputs:[1],outputs:[r.wIsConst?-2:-1]})[0];r.wIsConst&&!e.kernelCustomData.wT&&(e.kernelCustomData.wT=$);let w=[t[0],$];s&&w.push(t[2]);let T=a?m*y:_,C=a?_:m*y,I=c*f*d;e.compute(Nc(w,r,n,T,C,I,s,x,i),{inputs:w})},hu=(e,t)=>{let r=t.format==="NHWC",i=[e.inputs[0].reshape(r?[e.inputs[0].dims[0],1,e.inputs[0].dims[1],e.inputs[0].dims[2]]:[e.inputs[0].dims[0],e.inputs[0].dims[1],1,e.inputs[0].dims[2]]),e.inputs[1].reshape([e.inputs[1].dims[0],e.inputs[1].dims[1],1,e.inputs[1].dims[2]])];e.inputs.length===3&&i.push(e.inputs[2]);let a=[0,t.pads[0],0,t.pads[1]],n=[1].concat(t.strides),s=[1].concat(t.dilations),o=[1].concat(t.kernelShape),l=Bi({...t,pads:a,strides:n,dilations:s,kernelShape:o},i);Yr(e,i,l,d=>r?[d[0],d[2],d[3]]:[d[0],d[1],d[3]])},mu=(e,t,r)=>{let i=r.format==="NHWC"?"channelsLast":"channelsFirst",a=Bi(r,t),n=r.autoPad==="NOTSET"?r.pads:r.autoPad,s=Pc(t[0].dims,t[1].dims,r.strides,r.dilations,n,!1,i);e.compute(Uc(t,a,s.outShape,[s.filterDepth,s.filterHeight,s.filterWidth],[s.padInfo.front,s.padInfo.top,s.padInfo.left],i))},Ba=(e,t)=>{if(fu(e.inputs,t),e.inputs[0].dims.length===3)hu(e,t);else if(e.inputs[0].dims.length===5)mu(e,e.inputs,t);else{let r=Bi(t,e.inputs);Yr(e,e.inputs,r)}}}),qc,Pg=L(()=>{ie(),st(),se(),oe(),qc=(e,t,r)=>{let i=e.length>2,a=t.outputShape,n=t.format==="NHWC",s=t.group,o=e[1].dims,l=o[2]/s,d=o[3],c=n?$e(l):1,f=n&&d===1&&l>=4,m=f?Math.floor(l/4)*4:Math.floor(l/c)*c,y=l-m,_=n?$e(d):1,b=n?d===1?c:_:1,x=O.size(a)/_,$=[Math.ceil(x/64),1,1];de("verbose",()=>`[conv2d_backprop_webgpu] dispatch = ${$}`);let w=["rank","rank"],T=[t.strides[0],t.strides[1]],C=[t.kernelShape[n?1:2],t.kernelShape[n?2:3]],I=[t.dilations[0],t.dilations[1]],z=[C[0]+(t.dilations[0]<=1?0:(t.kernelShape[n?1:2]-1)*(t.dilations[0]-1)),C[1]+(t.dilations[1]<=1?0:(t.kernelShape[n?2:3]-1)*(t.dilations[1]-1))],k=[z[0]-1-Math.floor((t.pads[0]+t.pads[2])/2),z[1]-1-Math.floor((t.pads[1]+t.pads[3])/2)],A=[{type:12,data:x},{type:12,data:T},{type:12,data:C},{type:12,data:I},{type:12,data:z},{type:6,data:k},{type:12,data:m},{type:12,data:l},{type:12,data:d},...J(e[0].dims,e[1].dims)];i&&(A.push(...J(e[2].dims)),w.push("rank")),A.push(...J(a));let D=V=>{let G=[{name:"output_size",type:"u32"},{name:"strides",type:"u32",length:T.length},{name:"filter_dims",type:"u32",length:C.length},{name:"dilations",type:"u32",length:C.length},{name:"effective_filter_dims",type:"u32",length:z.length},{name:"pads",type:"i32",length:k.length},{name:"input_channels_per_group_int",type:"u32"},{name:"input_channels_per_group",type:"u32"},{name:"output_channels_per_group",type:"u32"}],H=Ie(e[0].dataType),F=n?1:2,W=n?2:3,re=n?3:1,ee=M("W",e[1].dataType,e[1].dims.length,b),K=M("Dy",e[0].dataType,e[0].dims.length,c),ne=[K,ee];i&&ne.push(M("bias",e[2].dataType,[a[re]].length,_));let Y=X("result",e[0].dataType,a.length,_),ye=()=>{let ae="";if(f)c===4?ae+=`
        let xValue = ${K.getByOffset("x_offset")};
        let wValue = ${ee.getByOffset("w_offset")};
        dotProd = dotProd + dot(xValue, wValue);
        x_offset += 1u;
        w_offset += 1u;`:c===2?ae+=`
          dotProd = dotProd + dot(vec4<${H}>(${K.getByOffset("x_offset")}, ${K.getByOffset("x_offset + 1u")}), vec4<${H}>(${ee.getByOffset("w_offset")}, ${ee.getByOffset("w_offset + 1u")}));
          x_offset += 2u;
          w_offset += 2u;`:c===1&&(ae+=`
          dotProd = dotProd + dot(vec4<${H}>(${K.getByOffset("x_offset")}, ${K.getByOffset("x_offset + 1u")}, ${K.getByOffset("x_offset + 2u")}, ${K.getByOffset("x_offset + 3u")}), vec4<${H}>(${ee.getByOffset("w_offset")}, ${ee.getByOffset("w_offset + 1u")}, ${ee.getByOffset("w_offset + 2u")}, ${ee.getByOffset("w_offset + 3u")}));
          x_offset += 4u;
          w_offset += 4u;`);else if(ae+=`
                  let xValue = ${n?K.getByOffset(`${K.indicesToOffset(`${K.type.indices}(batch, idyR, idyC, inputChannel)`)} / ${c}`):K.get("batch","inputChannel","idyR","idyC")};
        `,c===1)ae+=`
          let w_offset = ${ee.indicesToOffset(`${ee.type.indices}(u32(wRPerm), u32(wCPerm), inputChannel, wOutChannel)`)};
          let wValue = ${ee.getByOffset(`w_offset / ${b}`)};
          dotProd = dotProd + xValue * wValue;`;else for(let pe=0;pe<c;pe++)ae+=`
            let wValue${pe} = ${ee.getByOffset(`${ee.indicesToOffset(`${ee.type.indices}(u32(wRPerm), u32(wCPerm), inputChannel + ${pe}, wOutChannel)`)} / ${b}`)};
            dotProd = dotProd + xValue[${pe}] * wValue${pe};`;return ae},U=()=>{if(y===0)return"";if(!f)throw new Error(`packInputAs4 ${f} is not true.`);let ae="";if(c===1){ae+="dotProd = dotProd";for(let pe=0;pe<y;pe++)ae+=`
            + ${K.getByOffset(`x_offset + ${pe}`)} * ${ee.getByOffset(`w_offset + ${pe}`)}`;ae+=";"}else if(c===2){if(y!==2)throw new Error(`Invalid inputChannelsRemainder ${y}.`);ae+=`
          let xValue = ${K.getByOffset("x_offset")};
          let wValue = ${ee.getByOffset("w_offset")};
          dotProd = dotProd + dot(xValue, wValue);`}return ae},j=`
            let outputIndices = ${Y.offsetToIndices(`global_idx * ${_}`)};
            let batch = ${Y.indicesGet("outputIndices",0)};
            let d1 = ${Y.indicesGet("outputIndices",re)};
            let r = ${Y.indicesGet("outputIndices",F)};
            let c = ${Y.indicesGet("outputIndices",W)};
            let dyCorner = vec2<i32>(i32(r), i32(c)) - uniforms.pads;
            let dyRCorner = dyCorner.x;
            let dyCCorner = dyCorner.y;
            let groupId = d1 / uniforms.output_channels_per_group;
            let wOutChannel = d1 - groupId * uniforms.output_channels_per_group;
            // Convolve dy(?, ?, d2) with w(:, :, d1, d2) to compute dx(xR, xC, d1).
            // ? = to be determined. : = across all values in that axis.
            var dotProd = ${Y.type.value}(0.0);
            var wR: u32 = 0;
            if (uniforms.dilations.x == 1) {
              // Minimum wR >= 0 that satisfies (dyRCorner + wR) % (uniforms.strides.x) == 0
              wR = u32(((dyRCorner + i32(uniforms.strides.x) - 1) / i32(uniforms.strides.x)) * i32(uniforms.strides.x) - dyRCorner);
            }
            for (; wR < uniforms.effective_filter_dims.x; wR = wR + 1) {
              if (wR % uniforms.dilations.x != 0) {
                continue;
              }
              let dyR = (${H}(dyRCorner) + ${H}(wR)) / ${H}(uniforms.strides[0]);
              let wRPerm = uniforms.filter_dims.x - 1 - wR / uniforms.dilations.x;
              if (dyR < 0.0 || dyR >= ${H}(uniforms.Dy_shape[${F}]) || fract(dyR) > 0.0 ||
                  wRPerm < 0) {
                continue;
              }
              let idyR: u32 = u32(dyR);
              var wC: u32 = 0;
              if (uniforms.dilations.y == 1) {
                // Minimum wC >= 0 that satisfies (dyCCorner + wC) % (uniforms.strides.y) == 0
                wC = u32(((dyCCorner + i32(uniforms.strides.y) - 1) / i32(uniforms.strides.y)) * i32(uniforms.strides.y) - dyCCorner);
              }
              for (; wC < uniforms.effective_filter_dims.y; wC = wC + 1) {
                if (wC % uniforms.dilations.y != 0) {
                  continue;
                }
                let dyC = (${H}(dyCCorner) + ${H}(wC)) / ${H}(uniforms.strides.y);
                let wCPerm = uniforms.filter_dims.y - 1 - wC / uniforms.dilations.y;
                if (dyC < 0.0 || dyC >= ${H}(uniforms.Dy_shape[${W}]) ||
                    fract(dyC) > 0.0 || wCPerm < 0) {
                  continue;
                }
                let idyC: u32 = u32(dyC);
                var inputChannel = groupId * uniforms.input_channels_per_group;
                ${f?`
                var x_offset = ${K.indicesToOffset(`${K.type.indices}(batch, idyR, idyC, inputChannel)`)} / ${c};
                var w_offset = ${ee.indicesToOffset(`${ee.type.indices}(wRPerm, wCPerm, inputChannel, wOutChannel)`)} / ${b};
                  `:""}
                for (var d2: u32 = 0; d2 < uniforms.input_channels_per_group_int; d2 = d2 + ${f?4:c}) {
                  ${ye()}
                  inputChannel = inputChannel + ${f?4:c};
                }
                ${U()}
                wC = wC + uniforms.strides.y - 1;
              }
              wR = wR + uniforms.strides[0] - 1;
            }
            let value = dotProd${i?` + bias[d1 / ${_}]`:""};
            ${Y.setByOffset("global_idx","value")};
          `;return`
    ${V.registerUniforms(G).declareVariables(...ne,Y)}
      ${V.mainStart()}
      ${V.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")};
    ${j}}`};return{name:"ConvTranspose2D",shaderCache:{hint:`${t.cacheKey};${c}${b}${_}${f}${y}`,inputDependencies:w},getRunData:()=>({dispatchGroup:{x:$[0],y:$[1],z:$[2]},outputs:[{dims:r?r(a):a,dataType:e[0].dataType}],programUniforms:A}),getShaderSource:D}}}),gu,yu,_u,Zr,Vc,bu,Xr,wu,jc,Ug=L(()=>{Pg(),Bt(),_t(),gu=(e,t,r,i,a,n)=>(e-1)*t+r+(i-1)*a+1-n,yu=(e,t,r,i,a)=>{let n=Math.floor(e/2);t==="SAME_UPPER"?(r[i]=n,r[a]=e-n):t==="SAME_LOWER"&&(r[i]=e-n,r[a]=n)},_u=(e,t,r,i,a,n,s,o,l,d)=>{let c=e.length-2,f=d.length===0;l.length<c&&l.push(...Array(c-l.length).fill(0));let m=e[0],y=t[o?3:1]*a;for(let _=0,b=e.length-c-(o?1:0);_<c;++_,++b){let x=e[b],$=f?x*s[_]:d[_],w=gu(x,s[_],n[_],t[b],r[_],$);yu(w,i,n,_,_+c),f&&d.push(s[_]*(x-1)+l[_]+(t[b]-1)*r[_]+1-n[_]-n[_+c])}d.splice(0,0,m),d.splice(o?3:1,0,y)},Zr=(e,t)=>{let r=e.kernelShape.slice();if(e.kernelShape.length===0||e.kernelShape.reduce((f,m)=>f*m,1)===0){r.length=0;for(let f=2;f<t[1].dims.length;++f)r.push(t[1].dims[f])}let i=e.format==="NHWC";r.splice(0,0,t[1].dims[0]),r.splice(i?3:1,0,t[1].dims[1]);let a=e.pads.slice(),n=e.outputShape.slice(),s=e.outputPadding.slice(),o=t[0].dims,l=e.dilations.slice();if(l.reduce((f,m)=>f+m,0)===0){let f=t[0].dims.length-2;l=new Array(f).fill(1)}let d=e.strides.slice();if(d.reduce((f,m)=>f+m,0)===0){let f=t[0].dims.length-2;d=new Array(f).fill(1)}_u(o,r,l,e.autoPad,e.group,a,d,i,s,n);let c=Object.assign({},e);return Object.assign(c,{kernelShape:r,pads:a,outputPadding:s,outputShape:n,dilations:l,strides:d}),c},Vc=e=>{let t=un(e),r=e.format,i=["NOTSET","VALID","SAME_UPPER","SAME_LOWER"][typeof e.autoPad>"u"?0:e.autoPad],a=e.dilations,n=e.group,s=e.kernelShape,o=e.pads,l=e.strides,d=e.wIsConst(),c=e.outputPadding,f=e.outputShape;return{autoPad:i,format:r,dilations:a,group:n,kernelShape:s,outputPadding:c,outputShape:f,pads:o,strides:l,wIsConst:d,...t,cacheKey:`${e.format};${t.activation};`}},bu=(e,t)=>{if(!e||e.length!==2&&e.length!==3)throw new Error("Conv requires 2 or 3 inputs");if(e[0].dims.length!==4&&e[0].dims.length!==3)throw new Error("currently only support 2-dimensional conv");if(e[0].dims.length!==e[1].dims.length)throw new Error("filter does not have same dimension as input");let r=e[0].dims[t.format==="NHWC"?e[0].dims.length-1:1],i=e[1].dims[0];if(r!==i)throw new Error("FILTER_IN_CHANNEL should be equal to DATA_CHANNEL");let a=e[1].dims[1]*t.group;if(e.length===3&&(e[2].dims.length!==1||e[2].dims[0]!==a))throw new Error("invalid bias");let n=e[0].dims.length-2;if(t.dilations.reduce((s,o)=>s+o,0)>0&&t.dilations.length!==n)throw new Error(`dilations should be ${n}D`);if(t.strides.reduce((s,o)=>s+o,0)>0&&t.strides.length!==n)throw new Error(`strides should be ${n}D`);if(t.pads.reduce((s,o)=>s+o,0)>0&&t.pads.length!==n*2)throw new Error(`pads should be ${n*2}D`);if(t.outputPadding.length!==n&&t.outputPadding.length!==0)throw new Error(`output_padding should be ${n}D`);if(t.kernelShape.reduce((s,o)=>s+o,0)>0&&t.kernelShape.length!==0&&t.kernelShape.length!==e[1].dims.length-2)throw new Error("invalid kernel shape");if(t.outputShape.length!==0&&t.outputShape.length!==e[0].dims.length-2)throw new Error("invalid output shape")},Xr=(e,t,r,i)=>{let a=e.kernelCustomData.wT??e.compute(De(t[1],[2,3,0,1]),{inputs:[1],outputs:[r.wIsConst?-2:-1]})[0];r.wIsConst&&!e.kernelCustomData.wT&&(e.kernelCustomData.wT=a);let n=[t[0],a];t.length===3&&n.push(t[2]),e.compute(qc(n,r,i),{inputs:n})},wu=(e,t)=>{let r=t.format==="NHWC",i=[e.inputs[0].reshape(r?[e.inputs[0].dims[0],1,e.inputs[0].dims[1],e.inputs[0].dims[2]]:[e.inputs[0].dims[0],e.inputs[0].dims[1],1,e.inputs[0].dims[2]]),e.inputs[1].reshape([e.inputs[1].dims[0],e.inputs[1].dims[1],1,e.inputs[1].dims[2]])];e.inputs.length===3&&i.push(e.inputs[2]);let a=t.kernelShape;(a.length===0||a[0]===0)&&(a=[e.inputs[1].dims[2]]);let n=t.dilations;(n.length===0||n[0]===0)&&(n=[1]);let s=t.strides;(s.length===0||s[0]===0)&&(s=[1]);let o=t.pads;o.length===0&&(o=[0,0]),o=[0,o[0],0,o[1]],s=[1].concat(s),n=[1].concat(n),a=[1].concat(a);let l=t.outputPadding;l=[0].concat(l);let d=Zr({...t,pads:o,strides:s,dilations:n,kernelShape:a,outputPadding:l},i);Xr(e,i,d,c=>r?[c[0],c[2],c[3]]:[c[0],c[1],c[3]])},jc=(e,t)=>{if(bu(e.inputs,t),e.inputs[0].dims.length===3)wu(e,t);else{let r=Zr(t,e.inputs);Xr(e,e.inputs,r)}}}),vu,Fc,Gc,Wg=L(()=>{ie(),se(),xe(),oe(),vu=(e,t,r,i)=>{let a=O.size(t),n=t.length,s=M("input",e,n),o=X("output",e,n),l=r.dataType===6?r.getInt32Array()[0]:Number(r.getBigInt64Array()[0]),d=O.normalizeAxis(l,n),c=f=>{let m=` i32(${s.indicesGet("inputIndices","uniforms.axis")}) `,y=Q("uniforms.input_shape","uniforms.axis",n),_=i.reverse?m+(i.exclusive?" + 1":""):"0",b=i.reverse?y:m+(i.exclusive?"":" + 1");return`
                ${f.registerUniform("outputSize","u32").registerUniform("axis","u32").declareVariables(s,o)}
                ${f.mainStart()}
                  ${f.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
                  var inputIndices = ${o.offsetToIndices("global_idx")};
                  var sum = ${o.type.value}(0);
                  let first : i32 = ${_};
                  let last : i32 = ${b};
                  for (var i : i32 = first; i < last; i++) {
                    ${s.indicesSet("inputIndices","uniforms.axis","u32(i)")};
                    sum = sum + ${s.getByIndices("inputIndices")};
                  }
                  ${o.setByOffset("global_idx","sum")};
                }`};return{name:"CumSum",shaderCache:{hint:i.cacheKey,inputDependencies:["rank"]},getRunData:()=>({outputs:[{dims:t,dataType:e}],dispatchGroup:{x:Math.ceil(a/64)},programUniforms:[{type:12,data:a},{type:12,data:d},...J(t,t)]}),getShaderSource:c}},Fc=(e,t)=>{let r=e.inputs[0].dims,i=e.inputs[0].dataType,a=e.inputs[1];e.compute(vu(i,r,a,t),{inputs:[0]})},Gc=e=>{let t=e.exclusive===1,r=e.reverse===1;return he({exclusive:t,reverse:r})}}),$u,xu,Cu,Hc,Kc,Lg=L(()=>{ie(),se(),xe(),oe(),$u=e=>{if(!e||e.length!==1)throw new Error("DepthToSpace requires 1 input.");if(e[0].dims.length!==4)throw new Error("DepthToSpace requires 4D input.")},xu=(e,t,r,i)=>{let a=[];a.push(`fn perm(i: ${i.type.indices}) -> ${r.type.indices} {
    var a: ${r.type.indices};`);for(let n=0;n<t;++n)a.push(r.indicesSet("a",e[n],`i[${n}]`));return a.push("return a;}"),a.join(`
`)},Cu=(e,t)=>{let r,i,a,n,s,o,l=t.format==="NHWC",d=t.blocksize,c=t.mode==="DCR";l?([r,i,a,n]=e.dims,s=c?[r,i,a,d,d,n/d**2]:[r,i,a,n/d**2,d,d],o=c?[0,1,3,2,4,5]:[0,1,4,2,5,3]):([r,i,a,n]=[e.dims[0],e.dims[2],e.dims[3],e.dims[1]],s=c?[r,d,d,n/d**2,i,a]:[r,n/d**2,d,d,i,a],o=c?[0,3,4,1,5,2]:[0,1,4,2,5,3]);let f=e.reshape(s),m=f.dims.length,y=e.dataType,_=M("a",y,m),b=X("output",y,m),x=$=>`
  ${$.registerUniform("output_size","u32").declareVariables(_,b)}

  ${xu(o,m,_,b)}

  ${$.mainStart()}
    ${$.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}

    let indices = ${b.offsetToIndices("global_idx")};
    let aIndices = perm(indices);

    ${b.setByOffset("global_idx",_.getByIndices("aIndices"))}
  }`;return{name:"DepthToSpace",shaderCache:{hint:`${e.dims};${t.blocksize};${t.mode}`,inputDependencies:["rank"]},getRunData:$=>{let w=l?[r,i*d,a*d,n/d**2]:[r,n/d**2,i*d,a*d],T=O.size(w),C=f.dims,I=O.sortBasedOnPerm(C,o);return{outputs:[{dims:w,dataType:$[0].dataType}],dispatchGroup:{x:Math.ceil(T/64)},programUniforms:[{type:12,data:T},...J(C,I)]}},getShaderSource:x}},Hc=(e,t)=>{$u(e.inputs),e.compute(Cu(e.inputs[0],t))},Kc=e=>he({blocksize:e.blocksize,mode:e.mode,format:e.format})}),Mi,ii,Qr,Tu,Su,Iu,ku,Jr,Eu,Yc,Zc,qg=L(()=>{ie(),se(),xe(),oe(),Mi="[a-zA-Z]|\\.\\.\\.",ii="("+Mi+")+",Qr="^"+ii+"$",Tu="("+ii+",)*"+ii,Su="^"+Tu+"$",Iu=class{constructor(e=-1){this.symbolToIndices=new Map,this.inputIndex=e}addSymbol(e,t){let r=this.symbolToIndices.get(e);r===void 0?r=[t]:r.push(t),this.symbolToIndices.set(e,r)}},ku=class{constructor(e,t){this.equation=t,this.hasEllipsis=!1,this.symbolToInfo=new Map,this.lhs=new Array,this.outputDims=[];let[r,i]=t.includes("->")?t.split("->",2):[t,""];if(!r.match(RegExp(Su)))throw new Error("Invalid LHS term");if(r.split(",").forEach((a,n)=>{let s=e[n].dims.slice();if(!a.match(RegExp(Qr)))throw new Error("Invalid LHS term");let o=this.processTerm(a,!0,s,n);this.lhs.push(o)}),i==="")i+=[...this.symbolToInfo.entries()].filter(([a,n])=>n.count===1||a==="...").map(([a])=>a).join("");else if(!i.match(RegExp(ii)))throw new Error("Invalid RHS");i.match(RegExp(Mi,"g"))?.forEach(a=>{if(a==="...")this.outputDims=this.outputDims.concat(this.ellipsisDims);else{let n=this.symbolToInfo.get(a);if(n===void 0)throw new Error("Invalid RHS symbol");this.outputDims.push(n.dimValue)}}),this.rhs=this.processTerm(i,!1,this.outputDims)}addSymbol(e,t,r){let i=this.symbolToInfo.get(e);if(i!==void 0){if(i.dimValue!==t&&i.count!==1)throw new Error("Dimension mismatch");i.count++,i.inputIndices.push(r)}else i={count:1,dimValue:t,inputIndices:[r]};this.symbolToInfo.set(e,i)}processTerm(e,t,r,i=-1){let a=r.length,n=!1,s=[],o=0;if(!e.match(RegExp(Qr))&&!t&&e!=="")throw new Error("Invalid LHS term");let l=e.match(RegExp(Mi,"g")),d=new Iu(i);return l?.forEach((c,f)=>{if(c==="..."){if(n)throw new Error("Only one ellipsis is allowed per input term");n=!0;let m=a-l.length+1;if(m<0)throw new Error("Ellipsis out of bounds");if(s=r.slice(o,o+m),this.hasEllipsis){if(this.ellipsisDims.length!==s.length||this.ellipsisDims.toString()!==s.toString())throw new Error("Ellipsis dimensions mismatch")}else if(t)this.hasEllipsis=!0,this.ellipsisDims=s;else throw new Error("Ellipsis must be specified in the LHS");for(let y=0;y<s.length;y++){let _=String.fromCharCode(48+y);d.addSymbol(_,f+y),this.addSymbol(_,r[o++],i)}}else d.addSymbol(c,f+(this.hasEllipsis?this.ellipsisDims.length-1:0)),this.addSymbol(c,r[o++],i)}),d}},Jr=e=>e+"_max",Eu=(e,t,r,i)=>{let a=e.map(d=>d.length).map((d,c)=>M(`input${c}`,t,d)),n=O.size(i),s=X("output",t,i.length),o=[...r.symbolToInfo.keys()].filter(d=>!r.rhs.symbolToIndices.has(d)),l=d=>{let c=[],f="var prod = 1.0;",m="var sum = 0.0;",y="sum += prod;",_=[],b=[],x=[],$=[],w=r.symbolToInfo.size===r.rhs.symbolToIndices.size;r.symbolToInfo.forEach((C,I)=>{if(r.rhs.symbolToIndices.has(I)){let z=r.rhs.symbolToIndices.get(I)?.[0];z!==void 0&&r.lhs.forEach((k,A)=>{if(C.inputIndices.includes(A)){let D=k.symbolToIndices.get(I);if(D===void 0)throw new Error("Invalid symbol error");D.forEach(V=>{c.push(`${a[A].indicesSet(`input${A}Indices`,V,s.indicesGet("outputIndices",z))}`)})}})}else r.lhs.forEach((z,k)=>{if(C.inputIndices.includes(k)){let A=z.symbolToIndices.get(I);if(A===void 0)throw new Error("Invalid symbol error");A.forEach(D=>{_.push(`${a[k].indicesSet(`input${k}Indices`,D,`${I}`)}`)}),$.push(`prod *= ${a[k].getByIndices(`input${k}Indices`)};`)}}),b.push(`for(var ${I}: u32 = 0; ${I} < uniforms.${Jr(I)}; ${I}++) {`),x.push("}")});let T=w?[...c,`let sum = ${a.map((C,I)=>C.getByIndices(`input${I}Indices`)).join(" * ")};`]:[...c,m,...b,..._,f,...$,y,...x];return`
            ${d.registerUniforms(o.map(C=>({name:`${Jr(C)}`,type:"u32"}))).registerUniform("outputSize","u32").declareVariables(...a,s)}

            ${d.mainStart()}
            ${d.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
            var outputIndices = ${s.offsetToIndices("global_idx")};
            ${a.map((C,I)=>`var input${I}Indices: ${a[I].type.indices};`).join(`
`)}
            ${T.join(`
`)};
            ${s.setByOffset("global_idx","sum")};
          }`};return{name:"Einsum",shaderCache:{hint:r.equation,inputDependencies:e.map(()=>"rank")},getRunData:()=>{let d=o.filter(f=>r.symbolToInfo.has(f)).map(f=>({type:12,data:r.symbolToInfo.get(f)?.dimValue||0}));d.push({type:12,data:n});let c=e.map((f,m)=>[...J(f)]).reduce((f,m)=>f.concat(m),d);return c.push(...J(i)),{outputs:[{dims:i,dataType:t}],dispatchGroup:{x:Math.ceil(n/64)},programUniforms:c}},getShaderSource:l}},Yc=(e,t)=>{let r=new ku(e.inputs,t.equation),i=r.outputDims,a=e.inputs.map((n,s)=>n.dims);e.compute(Eu(a,e.inputs[0].dataType,r,i))},Zc=e=>{let t=e.equation.replace(/\s+/g,"");return he({equation:t})}}),zu,ea,Au,Ou,Xc,Vg=L(()=>{ie(),se(),oe(),zu=e=>{if(!e||e.length!==2)throw new Error("Expand requires 2 input.");let t=e[0].dims,r=Array.from(e[1].getBigInt64Array(),Number),i=r.length<t.length?0:r.length-t.length,a=t.length<r.length?0:t.length-r.length;for(;i<r.length&&a<t.length;++i,++a)if(r[i]!==t[a]&&r[i]!==1&&t[a]!==1)throw new Error("Expand requires shape to be broadcastable to input")},ea=(e,t)=>{let r=e.length-t.length,i=[];for(let a=0;a<r;++a)i.push(e[a]);for(let a=0;a<t.length;++a)i.push(t[a]===1?e[a+r]:t[a]);return i},Au=(e,t)=>e.length>t.length?ea(e,t):ea(t,e),Ou=e=>{let t=e[0].dims,r=Array.from(e[1].getBigInt64Array(),Number),i=Au(t,r),a=e[0].dataType,n=a===9||O.size(t)===1,s=a===9||t.length>0&&t[t.length-1]%4===0?4:1,o=n||i.length>0&&i[i.length-1]%4===0?4:1,l=Math.ceil(O.size(i)/o),d=f=>{let m=M("input",a,t.length,s),y=X("output",a,i.length,o),_;if(a===9){let b=(x,$,w="")=>`
          let outputIndices${$} = ${y.offsetToIndices(`outputOffset + ${$}u`)};
          let offset${$} = ${m.broadcastedIndicesToOffset(`outputIndices${$}`,y)};
          let index${$} = offset${$} / 4u;
          let component${$} = offset${$} % 4u;
          ${x}[${$}] = ${w}(${m.getByOffset(`index${$}`)}[component${$}]);
        `;_=`
        let outputOffset = global_idx * ${o};
        var data = vec4<u32>(0);
        ${b("data",0,"u32")}
        ${b("data",1,"u32")}
        ${b("data",2,"u32")}
        ${b("data",3,"u32")}
        ${y.setByOffset("global_idx","data")}
      }`}else _=`
        let outputIndices = ${y.offsetToIndices(`global_idx * ${o}`)};
        let inputOffset = ${m.broadcastedIndicesToOffset("outputIndices",y)};
        let data = ${y.type.value}(${m.getByOffset(`inputOffset / ${s}`)});
        ${y.setByOffset("global_idx","data")}
      }`;return`
    ${f.registerUniform("vec_size","u32").declareVariables(m,y)}
    ${f.mainStart()}
    ${f.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.vec_size")}
    ${_}`},c=[{type:12,data:l},...J(t,i)];return{name:"Expand",shaderCache:{hint:`${i.length};${s}${o}`,inputDependencies:["rank"]},getShaderSource:d,getRunData:()=>({outputs:[{dims:i,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(l/64)},programUniforms:c})}},Xc=e=>{zu(e.inputs),e.compute(Ou(e.inputs),{inputs:[0]})}}),Ru,Qc,jg=L(()=>{ie(),se(),oe(),on(),Ru=e=>{let t=e[0].dataType,r=O.size(e[0].dims),i=O.size(e[1].dims),a=i%4===0,n=s=>{let o=M("x",t,[1],4),l=M("bias",t,[1],4),d=X("y",t,[1],4),c=[{name:"output_vec_size",type:"u32"},{name:"bias_size",type:"u32"}],f=y=>`
      let bias${y}_offset: u32 = (global_idx * 4 + ${y}) % uniforms.bias_size;
      let bias${y} = ${l.getByOffset(`bias${y}_offset / 4`)}[bias${y}_offset % 4];`,m=a?`
      let bias = ${l.getByOffset("global_idx % (uniforms.bias_size / 4)")};`:`${f(0)}${f(1)}${f(2)}${f(3)}
      let bias = ${o.type.value}(bias0, bias1, bias2, bias3);`;return`${s.registerUniforms(c).declareVariables(o,l,d)}

    ${Ea(ze(t))}

    ${s.mainStart(Lt)}
      ${s.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_vec_size")}

      let x = ${o.getByOffset("global_idx")};
      ${m}
      let x_in = x + bias;
      ${d.setByOffset("global_idx",za("x_in"))}
    }`};return{name:"FastGeluWithBias",shaderCache:{hint:`${a}`,inputDependencies:["type","type"]},getShaderSource:n,getRunData:s=>({outputs:[{dims:s[0].dims,dataType:s[0].dataType}],programUniforms:[{type:12,data:Math.ceil(r/4)},{type:12,data:i}],dispatchGroup:{x:Math.ceil(r/Lt/4)}})}},Qc=e=>{e.inputs.length<2||O.size(e.inputs[1].dims)===0?_c(e):e.compute(Ru(e.inputs))}}),Bu,Mu,Jc,ef,Fg=L(()=>{ie(),se(),xe(),oe(),Bu=e=>{if(!e||e.length!==2)throw new Error("Gather requires 2 inputs.")},Mu=(e,t)=>{let r=e[0].dims,i=e[1].dims,a=r.length,n=O.normalizeAxis(t.axis,a),s=r.slice(0);s.splice(n,1,...i);let o=r[n],l=e[0].dataType===9?4:1,d=Math.ceil(O.size(s)/l),c=[{type:12,data:d},{type:6,data:o},{type:12,data:n},...J(e[0].dims,e[1].dims,s)],f=m=>{let y=M("data",e[0].dataType,e[0].dims.length,l),_=M("inputIndices",e[1].dataType,e[1].dims.length),b=X("output",e[0].dataType,s.length,l),x=w=>{let T=i.length,C=`var indicesIndices${w}  = ${_.type.indices}(0);`;for(let I=0;I<T;I++)C+=`${T>1?`indicesIndices${w}[${I}]`:`indicesIndices${w}`} = ${s.length>1?`outputIndices${w}[uniforms.axis + ${I}]`:`outputIndices${w}`};`;C+=`
          var idx${w} = ${_.getByIndices(`indicesIndices${w}`)};
          if (idx${w} < 0) {
            idx${w} = idx${w} + uniforms.axisDimLimit;
          }
          var dataIndices${w} : ${y.type.indices};
        `;for(let I=0,z=0;I<a;I++)I===n?(C+=`${a>1?`dataIndices${w}[${I}]`:`dataIndices${w}`} = u32(idx${w});`,z+=T):(C+=`${a>1?`dataIndices${w}[${I}]`:`dataIndices${w}`} = ${s.length>1?`outputIndices${w}[${z}]`:`outputIndices${w}`};`,z++);return C},$;if(e[0].dataType===9){let w=(T,C,I="")=>`
          let outputIndices${C} = ${b.offsetToIndices(`outputOffset + ${C}u`)};
          ${x(C)};
          let offset${C} = ${y.indicesToOffset(`dataIndices${C}`)};
          let index${C} = offset${C} / 4u;
          let component${C} = offset${C} % 4u;
          ${T}[${C}] = ${I}(${y.getByOffset(`index${C}`)}[component${C}]);
        `;$=`
        let outputOffset = global_idx * ${l};
        var value = vec4<u32>(0);
        ${w("value",0,"u32")}
        ${w("value",1,"u32")}
        ${w("value",2,"u32")}
        ${w("value",3,"u32")}
        ${b.setByOffset("global_idx","value")}
      `}else $=`
      let outputIndices = ${b.offsetToIndices("global_idx")};
      ${x("")};
      let value = ${y.getByIndices("dataIndices")};
      ${b.setByOffset("global_idx","value")};
      `;return`
      ${m.registerUniform("outputSize","u32").registerUniform("axisDimLimit","i32").registerUniform("axis","u32").declareVariables(y,_,b)}
      ${m.mainStart()}
        ${m.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
        ${$}
      }`};return{name:"Gather",shaderCache:{hint:t.cacheKey,inputDependencies:["rank","rank"]},getRunData:()=>({outputs:[{dims:s,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(d/64)},programUniforms:c}),getShaderSource:f}},Jc=e=>he({axis:e.axis}),ef=(e,t)=>{let r=e.inputs;Bu(r),e.compute(Mu(e.inputs,t))}}),Du,tf,rf,Gg=L(()=>{ie(),se(),oe(),Du=(e,t,r,i,a,n,s,o,l)=>{let d=[{type:12,data:n},{type:12,data:i},{type:12,data:a},{type:12,data:r},{type:12,data:s},{type:12,data:o},{type:12,data:l}],c=[n];d.push(...J(t.dims,c));let f=m=>{let y=M("indices_data",t.dataType,t.dims.length),_=X("input_slice_offsets_data",12,1,1),b=[y,_],x=[{name:"output_size",type:"u32"},{name:"batch_dims",type:"u32"},{name:"input_dims",type:"u32",length:a.length},{name:"sizes_from_slice_dims_data",type:"u32",length:r.length},{name:"num_slices_per_batch",type:"u32"},{name:"input_batch_stride",type:"u32"},{name:"num_slice_dims",type:"u32"}];return`
  ${m.registerUniforms(x).declareVariables(...b)}
  ${m.mainStart()}
    ${m.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
    let batch_idx = global_idx / uniforms.num_slices_per_batch;
    let base_offset = batch_idx * uniforms.input_batch_stride;

    let slice_indices_base_offset = global_idx * uniforms.num_slice_dims;
    var relative_slice_offset = 0;
    for (var dim_idx = 0u; dim_idx < uniforms.num_slice_dims; dim_idx ++) {
      var index = i32(indices_data[dim_idx + slice_indices_base_offset].x);
      let input_dim_idx = uniforms.batch_dims + dim_idx;
      if (index < 0) {
        ${a.length===1?"index += i32(uniforms.input_dims);":"index += i32(uniforms.input_dims[input_dim_idx]);"}
      }
      ${r.length===1?"relative_slice_offset += index * i32(uniforms.sizes_from_slice_dims_data);":"relative_slice_offset += index * i32(uniforms.sizes_from_slice_dims_data[dim_idx]);"}
    }

    input_slice_offsets_data[global_idx] =  base_offset + u32(relative_slice_offset);
  }`};return e.compute({name:"computeSliceOffsets",shaderCache:{hint:`${a.length}_${r.length}`,inputDependencies:["rank"]},getRunData:()=>({outputs:[{dims:c,dataType:e.inputs[1].dataType}],dispatchGroup:{x:Math.ceil(n/64)},programUniforms:d}),getShaderSource:f},{inputs:[t],outputs:[-1]})[0]},tf=(e,t)=>{let r=e.inputs,i=r[0].dims,a=r[0].dataType,n=r[1].dims,s=n[n.length-1],o=O.sizeToDimension(n,n.length-1),l=O.sizeFromDimension(i,t.batchDims+s),d=O.sizeToDimension(i,t.batchDims),c=O.sizeFromDimension(i,t.batchDims),f=o/d,m=new Array(s),y=l;for(let C=0;C<s;++C)m[s-1-C]=y,y*=i[t.batchDims+s-1-C];let _=Du(e,r[1],m,t.batchDims,i,o,f,c,s),b=t.batchDims+s;if(b>i.length)throw new Error("last dimension of indices must not be larger than rank of input tensor");let x=n.slice(0,-1).concat(i.slice(b)),$=O.size(x),w=[{type:12,data:$},{type:12,data:l},...J(r[0].dims,_.dims,x)],T=C=>{let I=M("data",r[0].dataType,r[0].dims.length),z=M("slice_offsets",12,_.dims.length),k=X("output",r[0].dataType,x.length);return`
          ${C.registerUniform("output_size","u32").registerUniform("slice_size","u32").declareVariables(I,z,k)}
            ${C.mainStart()}
            ${C.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
          let slice_offset = slice_offsets[global_idx / uniforms.slice_size];
          output[global_idx] = data[u32(slice_offset) + global_idx % uniforms.slice_size];
        }`};e.compute({name:"GatherND",shaderCache:{hint:t.cacheKey,inputDependencies:["rank","rank"]},getRunData:()=>({outputs:[{dims:x,dataType:a}],dispatchGroup:{x:Math.ceil($/64)},programUniforms:w}),getShaderSource:T},{inputs:[r[0],_]})},rf=e=>({batchDims:e.batch_dims,cacheKey:""})}),Nu,Pu,af,nf,Hg=L(()=>{ie(),se(),xe(),oe(),Nu=(e,t)=>{if(e.length<3||e.length>4)throw new Error("GatherBlockQuantized requires 3 or 4 inputs.");let r=O.normalizeAxis(t.quantizeAxis,e[0].dims.length),i=t.blockSize,a=e[0],n=e[2],s=e.length===4?e[3]:void 0;if(n.dims.length!==a.dims.length||!a.dims.map((o,l)=>l===r?Math.ceil(o/i)===n.dims[l]:o===n.dims[l]).reduce((o,l)=>o&&l,!0))throw new Error("Scales must have the same rank as the input tensor and the dims should match except on gatherAxis.");if(s){if(s.dataType!==a.dataType)throw new Error("Zero point must have the same data type as the input tensor.");if(s.dims.length!==n.dims.length||!s.dims.map((o,l)=>o===n.dims[l]).reduce((o,l)=>o&&l,!0))throw new Error("Zero point must have the same rank as the input tensor and the dims should match except on quantizeAxis.")}},Pu=(e,t)=>{let r=e[0].dims,i=e[1].dims,a=r.length,n=O.normalizeAxis(t.gatherAxis,a),s=O.normalizeAxis(t.quantizeAxis,a),o=r.slice(0);o.splice(n,1,...i);let l=O.size(o),d=e[2].dataType,c=e[0].dataType===22,f=[{type:12,data:l},{type:12,data:s},{type:12,data:n},{type:12,data:t.blockSize},...J(...e.map((y,_)=>y.dims),o)],m=y=>{let _=M("data",e[0].dataType,e[0].dims.length),b=M("inputIndices",e[1].dataType,e[1].dims.length),x=M("scales",e[2].dataType,e[2].dims.length),$=e.length>3?M("zeroPoint",e[3].dataType,e[3].dims.length):void 0,w=X("output",d,o.length),T=[_,b,x];$&&T.push($);let C=[{name:"output_size",type:"u32"},{name:"quantize_axis",type:"u32"},{name:"gather_axis",type:"u32"},{name:"block_size",type:"u32"}];return`
        ${y.registerUniforms(C).declareVariables(...T,w)}
        ${y.mainStart()}
        let output_indices = ${w.offsetToIndices("global_idx")};
        var indices_indices = ${b.type.indices}(0);
        ${i.length>1?`
          for (var i: u32 = 0; i < ${i.length}; i++) {
            let index = ${w.indicesGet("output_indices","uniforms.gather_axis + i")};
            ${b.indicesSet("indices_indices","i","index")};
          }`:`indices_indices = ${w.indicesGet("output_indices","uniforms.gather_axis")};`};
        var data_indices = ${_.type.indices}(0);
        for (var i: u32 = 0; i < uniforms.gather_axis; i++) {
          let index = ${w.indicesGet("output_indices","i")};
          ${_.indicesSet("data_indices","i","index")};
        }
        var index_from_indices = ${b.getByIndices("indices_indices")};
        if (index_from_indices < 0) {
          index_from_indices += ${r[n]};
        }
        ${_.indicesSet("data_indices","uniforms.gather_axis","u32(index_from_indices)")};
        for (var i = uniforms.gather_axis + 1; i < ${o.length}; i++) {
          let index = ${w.indicesGet("output_indices",`i + ${i.length} - 1`)};
          ${_.indicesSet("data_indices","i","index")};
        }
        let data_offset = ${_.indicesToOffset("data_indices")};
        let data_index = data_offset % 8;
        // Convert 4-bit packed data to 8-bit packed data.
        let packed_4bit_quantized_data = ${_.getByOffset("data_offset / 8")};
        let packed_8bit_quantized_data = (packed_4bit_quantized_data >> (4 * (data_index % 2))) & 0x0f0f0f0f;
        let quantized_data_vec = ${c?"unpack4xI8":"unpack4xU8"}(u32(packed_8bit_quantized_data));
        let quantized_data = quantized_data_vec[data_index / 2];
        var scale_indices = data_indices;
        let quantize_axis_index = ${x.indicesGet("data_indices","uniforms.quantize_axis")} / uniforms.block_size;
        ${x.indicesSet("scale_indices","uniforms.quantize_axis","quantize_axis_index")};
        var scale = ${x.getByIndices("scale_indices")};
        ${$?`
              let zero_point_indices = scale_indices;
              let zero_point_offset = ${$.indicesToOffset("zero_point_indices")};
              let zero_point_index = zero_point_offset % 8;
              let packed_4bit_zero_points = ${$.getByOffset("zero_point_offset / 8")};
              let packed_8bit_zero_points = (packed_4bit_zero_points >> (4 * (zero_point_index % 2))) & 0x0f0f0f0f;
              let zero_point_vec = ${c?"unpack4xI8":"unpack4xU8"}(u32(packed_8bit_zero_points));
              let zero_point = zero_point_vec[zero_point_index / 2];`:"var zero_point = 0"};
        let dequantized_data = ${ze(d)}(quantized_data - zero_point) * scale;
        ${w.setByOffset("global_idx","dequantized_data")};
    }`};return{name:"GatherBlockQuantized",shaderCache:{hint:`${t.cacheKey};${e.filter((y,_)=>_!==1).map(y=>y.dims.join("_")).join(";")}`,inputDependencies:Array.from({length:e.length},(y,_)=>"rank")},getRunData:()=>({outputs:[{dims:o,dataType:d}],dispatchGroup:{x:Math.ceil(l/64)},programUniforms:f}),getShaderSource:m}},af=(e,t)=>{let r=e.inputs;Nu(r,t),e.compute(Pu(e.inputs,t))},nf=e=>he({blockSize:e.blockSize,gatherAxis:e.gatherAxis,quantizeAxis:e.quantizeAxis})}),Uu,Wu,sf,of,Kg=L(()=>{ie(),se(),xe(),oe(),Uu=e=>{if(!e||e.length!==2)throw new Error("GatherElements requires 2 inputs.");if(e[0].dims.length<1)throw new Error("GatherElements requires that the data input be rank >= 1.");if(e[0].dims.length!==e[1].dims.length)throw new Error(`GatherElements requires that the data input and
                     indices input tensors be of same rank.`)},Wu=(e,t)=>{let r=e[0].dims,i=e[0].dataType,a=r.length,n=e[1].dims,s=e[1].dataType,o=O.normalizeAxis(t.axis,a),l=r[o],d=n.slice(0),c=O.size(d),f=M("input",i,a),m=M("indicesInput",s,n.length),y=X("output",i,d.length),_=[{type:12,data:c},{type:6,data:l},{type:12,data:o}];return _.push(...J(r,n,d)),{name:"GatherElements",shaderCache:{inputDependencies:["rank","rank"]},getRunData:()=>({outputs:[{dims:d,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(c/64)},programUniforms:_}),getShaderSource:b=>`
      ${b.registerUniform("outputSize","u32").registerUniform("axisDimLimit","i32").registerUniform("axis","u32").declareVariables(f,m,y)}
      ${b.mainStart()}
      ${b.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}

      let outputIndices = ${y.offsetToIndices("global_idx")};

      var idx = ${m.getByOffset("global_idx")};
      if (idx < 0) {
        idx = idx + uniforms.axisDimLimit;
      }
      var inputIndices = ${f.type.indices}(outputIndices);
      ${f.indicesSet("inputIndices","uniforms.axis","u32(idx)")};
      let value = ${f.getByIndices("inputIndices")};

      ${y.setByOffset("global_idx","value")};
  }`}},sf=e=>he({axis:e.axis}),of=(e,t)=>{let r=e.inputs;Uu(r),e.compute(Wu(e.inputs,t))}}),Lu,qu,uf,lf,Yg=L(()=>{ie(),se(),oe(),Lu=e=>{if(!e)throw new Error("Input is missing");if(e.length<2||e.length>3)throw new Error("Invaid input number.");if(e.length===3&&e[2].dims.length>2)throw new Error("Invalid input shape of C");if(e[0].dataType!==e[1].dataType||e.length===3&&e[0].dataType!==e[2].dataType)throw new Error("Input types are mismatched")},qu=(e,t)=>{let r=e[0].dims.slice(),i=e[1].dims.slice(),[a,n,s]=np.getShapeOfGemmResult(r,t.transA,i,t.transB,e.length===3?e[2].dims:void 0),o=[a,n];if(!o)throw new Error("Can't use gemm on the given tensors");let l=16,d=Math.ceil(n/l),c=Math.ceil(a/l),f=!0,m=O.size(o),y=[{type:12,data:f?d:m},{type:12,data:a},{type:12,data:n},{type:12,data:s},{type:1,data:t.alpha},{type:1,data:t.beta}],_=["type","type"];e.length===3&&(y.push(...J(e[2].dims)),_.push("rank")),y.push(...J(o));let b=$=>{let w="";t.transA&&t.transB?w="value += a[k * uniforms.M + m] * b[n * uniforms.K + k];":t.transA&&!t.transB?w="value += a[k * uniforms.M + m] * b[k * uniforms.N + n];":!t.transA&&t.transB?w="value += a[m * uniforms.K + k] * b[n * uniforms.K + k];":!t.transA&&!t.transB&&(w="value += a[m * uniforms.K + k] * b[k * uniforms.N + n];");let T=t.alpha===1?"":"value *= uniforms.alpha;",C=M("a",e[0].dataType,e[0].dims),I=M("b",e[1].dataType,e[1].dims),z=C.type.value,k=null,A=[C,I];e.length===3&&(k=M("c",e[2].dataType,e[2].dims.length),A.push(k));let D=X("output",e[0].dataType,o.length);A.push(D);let V=[{name:"output_size",type:"u32"},{name:"M",type:"u32"},{name:"N",type:"u32"},{name:"K",type:"u32"},{name:"alpha",type:"f32"},{name:"beta",type:"f32"}];return`
  ${$.registerUniforms(V).declareVariables(...A)}

  ${$.mainStart()}
    ${$.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}

    let m = global_idx / uniforms.N;
    let n = global_idx % uniforms.N;

    var value = ${z}(0);
    for (var k: u32 = 0u; k < uniforms.K; k++) {
      ${w}
    }

    ${T}
    ${k!=null?`let cOffset = ${k.broadcastedIndicesToOffset("vec2(m, n)",D)}; value += ${z}(uniforms.beta) * ${k.getByOffset("cOffset")};`:""}
    output[global_idx] = value;
  }`},x=$=>{let w=M("a",e[0].dataType,e[0].dims),T=M("b",e[1].dataType,e[1].dims),C=null,I=[w,T];e.length===3&&(C=M("c",e[2].dataType,e[2].dims.length),I.push(C));let z=X("output",e[0].dataType,o.length);I.push(z);let k=[{name:"num_tile_n",type:"u32"},{name:"M",type:"u32"},{name:"N",type:"u32"},{name:"K",type:"u32"},{name:"alpha",type:"f32"},{name:"beta",type:"f32"}],A="",D="";t.transA&&t.transB?(D=`
      var col = tile_row_start + local_id.x;
      var row = k_start + local_id.y;
      if (col < uniforms.M && row < uniforms.K) {
        tile_a[local_id.y][local_id.x] = a[row * uniforms.M + col];
      } else {
        tile_a[local_id.y][local_id.x] = ${w.type.value}(0);
      }

      col = k_start + local_id.x;
      row = tile_col_start + local_id.y;
      if (col < uniforms.K && row < uniforms.N) {
        tile_b[local_id.y][local_id.x] = b[row * uniforms.K + col];
      } else {
        tile_b[local_id.y][local_id.x] = ${T.type.value}(0);
      }
      `,A="value += tile_a[k][local_id.y] * tile_b[local_id.x][k];"):t.transA&&!t.transB?(D=`
      var col = tile_row_start + local_id.x;
      var row = k_start + local_id.y;
      if (col < uniforms.M && row < uniforms.K) {
        tile_a[local_id.y][local_id.x] = a[row * uniforms.M + col];
      } else {
        tile_a[local_id.y][local_id.x] = ${w.type.value}(0);
      }

      col = tile_col_start + local_id.x;
      row = k_start + local_id.y;
      if (col < uniforms.N && row < uniforms.K) {
        tile_b[local_id.y][local_id.x] = b[row * uniforms.N + col];
      } else {
        tile_b[local_id.y][local_id.x] = ${T.type.value}(0);
      }
      `,A="value += tile_a[k][local_id.y] * tile_b[k][local_id.x];"):!t.transA&&t.transB?(D=`
      var col = k_start + local_id.x;
      var row = tile_row_start + local_id.y;
      if (col < uniforms.K && row < uniforms.M) {
        tile_a[local_id.y][local_id.x] = a[row * uniforms.K + col];
      } else {
        tile_a[local_id.y][local_id.x] = ${w.type.value}(0);
      }

      col = k_start + local_id.x;
      row = tile_col_start + local_id.y;
      if (col < uniforms.K && row < uniforms.N) {
        tile_b[local_id.y][local_id.x] = b[row * uniforms.K + col];
      } else {
        tile_b[local_id.y][local_id.x] = ${T.type.value}(0);
      }
      `,A="value += tile_a[local_id.y][k] * tile_b[local_id.x][k];"):!t.transA&&!t.transB&&(D=`
      var col = k_start + local_id.x;
      var row = tile_row_start + local_id.y;
      if (col < uniforms.K && row < uniforms.M) {
        tile_a[local_id.y][local_id.x] = a[row * uniforms.K + col];
      } else {
        tile_a[local_id.y][local_id.x] = ${w.type.value}(0);
      }

      col = tile_col_start + local_id.x;
      row = k_start + local_id.y;
      if (col < uniforms.N && row < uniforms.K) {
        tile_b[local_id.y][local_id.x] = b[row * uniforms.N + col];
      } else {
        tile_b[local_id.y][local_id.x] = ${T.type.value}(0);
      }
      `,A="value += tile_a[local_id.y][k] * tile_b[k][local_id.x];");let V=t.alpha===1?"":"value *= uniforms.alpha;";return`
  ${$.registerUniforms(k).declareVariables(...I)}
  var<workgroup> tile_a: array<array<${w.type.storage}, ${l}>, ${l}>;
  var<workgroup> tile_b: array<array<${T.type.storage}, ${l}>, ${l}>;
  ${$.mainStart([l,l,1])}
    let tile_col_start = (workgroup_index % uniforms.num_tile_n) * ${l};
    let tile_row_start = (workgroup_index / uniforms.num_tile_n) * ${l};
    let num_tiles = (uniforms.K - 1) / ${l} + 1;
    var k_start = 0u;
    var value = ${z.type.value}(0);
    for (var t: u32 = 0u; t < num_tiles; t++) {
      ${D}
      k_start = k_start + ${l};
      workgroupBarrier();

      for (var k: u32 = 0u; k < ${l}; k++) {
        ${A}
      }
      workgroupBarrier();
    }

    ${V}
    let m = tile_row_start + local_id.y;
    let n = tile_col_start + local_id.x;
    ${C!=null?`let cOffset = ${C.broadcastedIndicesToOffset("vec2(m, n)",z)}; value += ${z.type.value}(uniforms.beta) * ${C.getByOffset("cOffset")};`:""}
    if (m < uniforms.M && n < uniforms.N) {
      output[m * uniforms.N + n] = value;
    }
  }`};return f?{name:"GemmShared",shaderCache:{hint:`${t.cacheKey}`,inputDependencies:_},getRunData:()=>({outputs:[{dims:o,dataType:e[0].dataType}],dispatchGroup:{x:d*c},programUniforms:y}),getShaderSource:x}:{name:"Gemm",shaderCache:{hint:`${t.cacheKey}`,inputDependencies:_},getRunData:()=>({outputs:[{dims:o,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(m/64)},programUniforms:y}),getShaderSource:b}},uf=e=>{let t=e.transA,r=e.transB,i=e.alpha,a=e.beta;return{transA:t,transB:r,alpha:i,beta:a,cacheKey:`${e.transA};${e.transB};${e.alpha===1}`}},lf=(e,t)=>{Lu(e.inputs),e.compute(qu(e.inputs,t))}}),Qe,at,$t,xt,Vu,ju,Fu,Gu,Hu,Ku,Yu,Zu,df,pf,Zg=L(()=>{ie(),se(),xe(),oe(),[Qe,at,$t,xt]=[0,1,2,3],Vu=e=>{if(e[0].dims.length!==4)throw new Error("only 4-D tensor is supported.");if(e[0].dims.length!==e[1].dims.length)throw new Error("input dimensions must be equal to grid dimensions");if(e[0].dims.length-2!==e[1].dims[e[1].dims.length-1])throw new Error(`last dimension of grid must be equal to ${e[0].dims.length-2}`);if(e[0].dims[0]!==e[1].dims[0])throw new Error("grid batch size must match input batch size")},ju=`
  fn gs_get_cubic_coeffs(x: f32) -> vec4<f32> {
    let cubic_alpha = -0.75f;
    let x_abs = abs(x);
    var coeffs: vec4<f32>;
    coeffs[0] = (((cubic_alpha * (x_abs + 1) - 5 * cubic_alpha) * (x_abs + 1) + 8 * cubic_alpha) * (x_abs + 1) - 4 * cubic_alpha);
    coeffs[1] = (((cubic_alpha + 2) * x_abs - (cubic_alpha + 3)) * x_abs * x_abs + 1);
    coeffs[2] = (((cubic_alpha + 2) * (1 - x_abs) - (cubic_alpha + 3)) * (1 - x_abs) * (1 - x_abs) + 1);
    coeffs[3] = (((cubic_alpha * (2 - x_abs) - 5 * cubic_alpha) * (2 - x_abs) + 8 * cubic_alpha) * (2 - x_abs) - 4 * cubic_alpha);
    return coeffs;
  }
`,Fu=e=>`
  fn gs_bicubic_interpolate(p: mat4x4<${e}>, x: f32, y: f32) -> ${e} {
    var v: vec4<f32>;
    var coeffs = gs_get_cubic_coeffs(x);
    for (var i = 0; i < 4; i++) {
      v[i] = coeffs[0] * p[i][0] + coeffs[1] * p[i][1] + coeffs[2] * p[i][2] + coeffs[3] * p[i][3];
    }
    coeffs = gs_get_cubic_coeffs(y);
    let pixel = ${e}(coeffs[0] * v[0] + coeffs[1] * v[1] + coeffs[2] * v[2] + coeffs[3] * v[3]);
    return pixel;
  }
`,Gu=e=>`
  fn gs_denormalize(n: f32, length: i32) -> f32 {
    ${e.alignCorners===0?`
    // alignCorners: false => [-1, 1] to [-0.5, length - 0.5]
    return ((n + 1.0) * f32(length) - 1.0) / 2.0;
    `:`
    // alignCorners: true => [-1, 1] to [0, length - 1]
    return (n + 1.0) / 2.0 * (f32(length - 1));
    `}
  }
`,Hu=e=>`
  ${e.paddingMode==="reflection"?`
      fn gs_reflect(x: i32, x_min: f32, x_max: f32) -> u32 {
        var dx = 0.0;
        var fx = f32(x);
        let range = x_max - x_min;
        if (fx < x_min) {
          dx = x_min - fx;
          let n = u32(dx / range);
          let r = dx - f32(n) * range;
          if (n % 2 == 0) {
            fx = x_min + r;
          } else {
            fx = x_max - r;
          }
        } else if (fx > x_max) {
          dx = fx - x_max;
          let n = u32(dx / range);
          let r = dx - f32(n) * range;
          if (n % 2 == 0) {
            fx = x_max - r;
          } else {
            fx = x_min + r;
          }
        }
        return u32(fx);
      }`:""}
`,Ku=(e,t,r)=>`
  fn pixel_at_grid(r: i32, c: i32, H: i32, W: i32, batch: u32, channel: u32, border: vec4<f32>) -> ${t} {
     var pixel = ${t}(0);
     var indices = vec4<u32>(0);
     indices[${Qe}] = batch;
     indices[${at}] = channel;`+(()=>{switch(r.paddingMode){case"zeros":return`
          if (r >= 0 && r < H && c >=0 && c < W) {
            indices[${$t}] = u32(r);
            indices[${xt}] = u32(c);
          } else {
            return ${t}(0);
          }
        `;case"border":return`
          indices[${$t}] = u32(clamp(r, 0, H - 1));
          indices[${xt}] = u32(clamp(c, 0, W - 1));
        `;case"reflection":return`
          indices[${$t}] = gs_reflect(r, border[1], border[3]);
          indices[${xt}] = gs_reflect(c, border[0], border[2]);
        `;default:throw new Error(`padding mode ${r.paddingMode} is not supported`)}})()+`
    return ${e.getByIndices("indices")};
  }
`,Yu=(e,t,r)=>(()=>{switch(r.mode){case"nearest":return`
          let result = pixel_at_grid(i32(round(y)), i32(round(x)), H_in, W_in, indices[${Qe}], indices[${at}], border);
        `;case"bilinear":return`
          let x1 = i32(floor(x));
          let y1 = i32(floor(y));
          let x2 = x1 + 1;
          let y2 = y1 + 1;

          let p11 = pixel_at_grid(y1, x1, H_in, W_in, indices[${Qe}], indices[${at}], border);
          let p12 = pixel_at_grid(y1, x2, H_in, W_in, indices[${Qe}], indices[${at}], border);
          let p21 = pixel_at_grid(y2, x1, H_in, W_in, indices[${Qe}], indices[${at}], border);
          let p22 = pixel_at_grid(y2, x2, H_in, W_in, indices[${Qe}], indices[${at}], border);

          let dx2 = ${t}(f32(x2) - x);
          let dx1 = ${t}(x - f32(x1));
          let dy2 = ${t}(f32(y2) - y);
          let dy1 = ${t}(y - f32(y1));
          let result = dy2 * (dx2 * p11 + dx1 * p12) + dy1 * (dx2 * p21 + dx1 * p22);
        `;case"bicubic":return`
          let x0 = i32(floor(x)) - 1;
          let y0 = i32(floor(y)) - 1;
          var p: mat4x4<${t}>;
          for (var h = 0; h < 4; h++) {
            for (var w = 0; w < 4; w++) {
              p[h][w] = pixel_at_grid(h + y0, w + x0, H_in, W_in, indices[${Qe}], indices[${at}], border);
            }
          }

          let dx = x - f32(x0 + 1);
          let dy = y - f32(y0 + 1);
          let result = gs_bicubic_interpolate(p, dx, dy);
        `;default:throw new Error(`mode ${r.mode} is not supported`)}})()+`${e.setByOffset("global_idx","result")}`,Zu=(e,t)=>{let r=M("x",e[0].dataType,e[0].dims.length),i=[e[1].dims[0],e[1].dims[1],e[1].dims[2]],a=M("grid",e[1].dataType,i.length,2),n=[e[0].dims[0],e[0].dims[1],e[1].dims[1],e[1].dims[2]];t.format==="NHWC"&&(n=[e[0].dims[0],e[1].dims[1],e[1].dims[2],e[0].dims[3]],[Qe,at,$t,xt]=[0,3,1,2]);let s=X("output",e[0].dataType,n.length),o=r.type.value,l=O.size(n),d=[{type:12,data:l},...J(e[0].dims,i,n)],c=f=>`
  ${f.registerUniform("output_size","u32").declareVariables(r,a,s)}
  ${ju}
  ${Fu(o)}
  ${Gu(t)}
  ${Hu(t)}
  ${Ku(r,o,t)}

  ${f.mainStart()}
    ${f.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
      let H_in = i32(uniforms.x_shape[${$t}]);
      let W_in = i32(uniforms.x_shape[${xt}]);

      ${t.alignCorners===0?`
      let x_min = -0.5;
      let x_max = f32(W_in) - 0.5;
      let y_min = -0.5;
      let y_max = f32(H_in) - 0.5;
      `:`
      let x_min = 0.0;
      let x_max = f32(W_in) - 1.0;
      let y_min = 0.0;
      let y_max = f32(H_in) - 1.0;
      `};
      let border = vec4<f32>(x_min, y_min, x_max, y_max);

      let indices = ${s.offsetToIndices("global_idx")};
      var grid_indices = vec3<u32>(indices[${Qe}], indices[${$t}], indices[${xt}]);
      let nxy = ${a.getByIndices("grid_indices")};
      var x = gs_denormalize(f32(nxy[0]), W_in);
      var y = gs_denormalize(f32(nxy[1]), H_in);

      ${Yu(s,o,t)}
  }`;return{name:"GridSample",shaderCache:{hint:`${t.cacheKey}`,inputDependencies:["type","type"]},getRunData:f=>{let m=O.size(n);return{outputs:[{dims:n,dataType:f[0].dataType}],dispatchGroup:{x:Math.ceil(m/64)},programUniforms:d}},getShaderSource:c}},df=(e,t)=>{Vu(e.inputs),e.compute(Zu(e.inputs,t))},pf=e=>he({alignCorners:e.align_corners,mode:e.mode,paddingMode:e.padding_mode,format:e.format})}),Oe,Xu,cf,ta,Qu,di,ff,hf=L(()=>{ie(),se(),xe(),rn(),sn(),oe(),_t(),Oe=(e,t)=>e.length>t&&e[t].dims.length>0?e[t]:void 0,Xu=(e,t)=>{let r=e[0],i=Oe(e,1),a=Oe(e,2),n=Oe(e,3),s=Oe(e,4),o=Oe(e,5),l=Oe(e,6),d=Oe(e,7);if(r.dims.length!==3&&r.dims.length!==5)throw new Error("Input query is expected to have 3 or 5 dimensions");let c=r.dims[0],f=r.dims[1],m=r.dims.length===3?r.dims[2]:t.numHeads*r.dims[4],y=f,_=0,b=0,x=Math.floor(m/t.numHeads);if(l&&d&&O.size(l.dims)&&O.size(d.dims)){if(l.dims.length!==4)throw new Error('Input "past_key" is expected to have 4 dimensions');if(l.dims[0]!==c||l.dims[1]!==t.numHeads||l.dims[3]!==x)throw new Error('Input "past_key" shape (batch_size, num_heads, past_sequence_length, head_size)');if(d.dims[0]!==c||d.dims[1]!==t.numHeads||d.dims[3]!==x)throw new Error('Input "past_value" shape (batch_size, num_heads, past_sequence_length, head_size)');if(l.dims[2]!==d.dims[2])throw new Error('Input "past_key" and "past_value" shall have same dim 2 (past_sequence_length)');if(d.dims.length!==4)throw new Error('Input "past_value" is expected to have 4 dimensions');_=l.dims[2],b=l.dims[2]}else if(l&&O.size(l.dims)||d&&O.size(d.dims))throw new Error('Input "past_key" and "past_value" shall be both present or both absent');let $;if(i&&O.size(i.dims)>0){if(r.dims.length!==3)throw new Error('Input "query" is expected to have 3 dimensions when key is given');if(i.dims.length<3||i.dims.length>5)throw new Error('Input "key" is expected to have 3, 4, or 5 dimensions');if(r.dims[0]!==i.dims[0])throw new Error('Input "query" and "key" shall have same dim 0 (batch size)');if(i.dims.length===3){if(i.dims[2]!==r.dims[2])throw new Error('Input "query" and "key" shall have same dim 2 (hidden_size)');$=2,y=i.dims[1]}else if(i.dims.length===5){if(i.dims[2]!==t.numHeads||i.dims[3]!==2||i.dims[4]!==x)throw new Error('Expect "key" shape (batch_size, kv_sequence_length, num_heads, 2, head_size) for packed kv');if(a)throw new Error('Expect "value" be none when "key" has packed kv format.');$=5,y=i.dims[1]}else{if(i.dims[1]!==t.numHeads||i.dims[3]!==x)throw new Error('Expect "key" shape (batch_size, num_heads, kv_sequence_length, head_size) for past_key');$=0,y=i.dims[2]}}else{if(r.dims.length!==5)throw new Error('Input "query" is expected to have 5 dimensions when key is empty');if(r.dims[2]!==t.numHeads||r.dims[3]!==3)throw new Error('Expect "query" shape (batch_size, kv_sequence_length, num_heads, 3, head_size) for packed kv');$=3}if(n&&O.size(n.dims)>0){if(n.dims.length!==1)throw new Error('Input "bias" is expected to have 1 dimension');if(i&&i.dims.length===5&&i.dims[3]===2)throw new Error("bias is not allowed for packed kv.")}let w=_+y,T=0;if(s&&O.size(s.dims)>0){T=8;let k=s.dims;throw k.length===1?k[0]===c?T=1:k[0]===3*c+2&&(T=3):k.length===2&&k[0]===c&&k[1]===w&&(T=5),T===8?new Error('Input "key_padding_mask" shape shall be (batch_size) or (batch_size, total_sequence_length)'):new Error("Mask not supported")}let C=!1,I=m;if(a&&O.size(a.dims)>0){if(a.dims.length!==3&&a.dims.length!==4)throw new Error('Input "value" is expected to have 3 or 4 dimensions');if(r.dims[0]!==a.dims[0])throw new Error('Input "query" and "value" shall have same dim 0 (batch_size)');if(a.dims.length===3){if(y!==a.dims[1])throw new Error('Input "key" and "value" shall have the same dim 1 (kv_sequence_length)');I=a.dims[2]}else{if(y!==a.dims[2])throw new Error('Input "key" and "value" shall have the same dim 2 (kv_sequence_length)');I=a.dims[1]*a.dims[3],C=!0}}let z=!1;if(s&&O.size(s.dims)>0)throw new Error("Key padding mask is not supported");if(o&&O.size(o.dims)>0){if(o.dims.length!==4)throw new Error('Input "attention_bias" is expected to have 4 dimensions');if(o.dims[0]!==c||o.dims[1]!==t.numHeads||o.dims[2]!==f||o.dims[3]!==w)throw new Error('Expect "attention_bias" shape (batch_size, num_heads, sequence_length, total_sequence_length)')}return{batchSize:c,sequenceLength:f,pastSequenceLength:_,kvSequenceLength:y,totalSequenceLength:w,maxSequenceLength:b,inputHiddenSize:0,hiddenSize:m,vHiddenSize:I,headSize:x,vHeadSize:Math.floor(I/t.numHeads),numHeads:t.numHeads,isUnidirectional:!1,pastPresentShareBuffer:!1,maskFilterValue:t.maskFilterValue,maskType:T,scale:t.scale,broadcastResPosBias:z,passPastInKv:C,qkvFormat:$}},cf=e=>he({...e}),ta=he({perm:[0,2,1,3]}),Qu=(e,t,r,i,a,n,s)=>{let o=[i,a,n],l=O.size(o),d=[{type:12,data:l},{type:12,data:s},{type:12,data:n}],c=f=>{let m=X("qkv_with_bias",t.dataType,o),y=M("qkv",t.dataType,o),_=M("bias",r.dataType,o),b=[{name:"output_size",type:"u32"},{name:"bias_offset",type:"u32"},{name:"hidden_size",type:"u32"}];return`
  ${f.registerUniforms(b).declareVariables(y,_,m)}
  ${f.mainStart()}
    ${f.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
    let bias_offset_idx = (global_idx % uniforms.hidden_size) + uniforms.bias_offset;

    qkv_with_bias[global_idx] = qkv[global_idx] + bias[bias_offset_idx];
  }`};return e.compute({name:"MultiHeadAttentionAddBias",shaderCache:{inputDependencies:["type","type"]},getRunData:()=>({outputs:[{dims:o,dataType:t.dataType,gpuDataType:0}],dispatchGroup:{x:Math.ceil(l/64)},programUniforms:d}),getShaderSource:c},{inputs:[t,r],outputs:[-1]})[0]},di=(e,t,r,i,a,n,s,o)=>{let l=n;if(s&&O.size(s.dims)>0){if(i===1)throw new Error("AddBiasReshape is not implemented. Please export your model with packed QKV or KV");return l=Qu(e,n,s,t,i,r*a,o),l=l.reshape([t,i,r,a]),r===1||i===1?l:e.compute(De(l,ta.perm),{inputs:[l],outputs:[-1]})[0]}else return n.dims.length===3&&(l=n.reshape([t,i,r,a])),r===1||i===1?l:e.compute(De(l,ta.perm),{inputs:[l],outputs:[-1]})[0]},ff=(e,t)=>{let r=Xu(e.inputs,t),i=e.inputs[0],a=Oe(e.inputs,1),n=Oe(e.inputs,2),s=Oe(e.inputs,3),o=Oe(e.inputs,4),l=Oe(e.inputs,5),d=Oe(e.inputs,6),c=Oe(e.inputs,7);if(i.dims.length===5)throw new Error("Packed QKV is not implemented");if(a?.dims.length===5)throw new Error("Packed KV is not implemented");let f=a&&n&&a.dims.length===4&&n.dims.length===4,m=di(e,r.batchSize,r.numHeads,r.sequenceLength,r.headSize,i,s,0);if(f)return hi(e,m,a,n,o,void 0,d,c,l,r);if(!a||!n)throw new Error("key and value must be provided");let y=di(e,r.batchSize,r.numHeads,r.kvSequenceLength,r.headSize,a,s,r.hiddenSize),_=di(e,r.batchSize,r.numHeads,r.kvSequenceLength,r.vHeadSize,n,s,2*r.hiddenSize);hi(e,m,y,_,o,void 0,d,c,l,r)}}),Ju,el,tl,il,Ma,mf,gf,yf=L(()=>{ie(),se(),xe(),oe(),Ju=e=>{if(!e||e.length<1)throw new Error("too few inputs")},el=(e,t)=>{let r=[],i=t.numOutputs;return e[1].dims[0]>0&&(e[1].getBigInt64Array().forEach(a=>r.push(Number(a))),i=r.length),he({numOutputs:i,axis:t.axis,splitSizes:r})},tl=e=>`
fn calculateOutputIndex(index: u32) -> u32 {
    for (var i: u32 = 0u; i < ${e}u; i += 1u ) {
    if (index < ${Q("uniforms.size_in_split_axis","i",e)}) {
        return i;
    }
    }
    return ${e}u;
}`,il=e=>{let t=e.length,r=[];for(let i=0;i<t;++i){let a=e[i].setByIndices("indices","input[global_idx]");t===1?r.push(a):i===0?r.push(`if (output_number == ${i}u) { ${a} }`):i===t-1?r.push(`else { ${a} }`):r.push(`else if (output_number == ${i}) { ${a} }`)}return`
      fn writeBufferData(output_number: u32, indices: ${e[0].type.indices}, global_idx: u32) {
        ${r.join(`
`)}
      }`},Ma=(e,t)=>{let r=e[0].dims,i=O.size(r),a=e[0].dataType,n=O.normalizeAxis(t.axis,r.length),s=new Array(t.numOutputs),o=M("input",a,r.length),l=new Array(t.numOutputs),d=[],c=[],f=0,m=[{type:12,data:i}];for(let _=0;_<t.numOutputs;_++){f+=t.splitSizes[_],l[_]=f;let b=r.slice();b[n]=t.splitSizes[_],c.push(b),s[_]=X(`output${_}`,a,b.length),d.push({dims:c[_],dataType:e[0].dataType})}m.push({type:12,data:l},...J(r,...c));let y=_=>`
  ${_.registerUniform("input_size","u32").registerUniform("size_in_split_axis","u32",l.length).declareVariables(o,...s)}
  ${tl(l.length)}
  ${il(s)}

  ${_.mainStart()}
    ${_.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.input_size")}

    var indices = ${o.offsetToIndices("global_idx")};
    var index = ${o.indicesGet("indices",n)};
    let output_number = calculateOutputIndex(index);
    if (output_number != 0) {
      index -= ${Q("uniforms.size_in_split_axis","output_number - 1u",l.length)};
      ${o.indicesSet("indices",n,"index")};
    }
    writeBufferData(output_number, indices, global_idx);
  }`;return{name:"Split",shaderCache:{hint:t.cacheKey,inputDependencies:["rank"]},getShaderSource:y,getRunData:()=>({outputs:d,dispatchGroup:{x:Math.ceil(i/64)},programUniforms:m})}},mf=(e,t)=>{Ju(e.inputs);let r=e.inputs.length===1?t:el(e.inputs,t);e.compute(Ma(e.inputs,r),{inputs:[0]})},gf=e=>{let t=e.axis,r=e.splitSizes,i=e.numOutputs<0?r.length:e.numOutputs;if(i!==r.length)throw new Error("numOutputs and splitSizes length must be equal");return he({axis:t,numOutputs:i,splitSizes:r})}}),rl,Ki,_f,bf=L(()=>{ie(),se(),xe(),oe(),rl=(e,t)=>{let[r,i,a,n]=e,{numHeads:s,rotaryEmbeddingDim:o}=t;if(r.dims.length!==3&&r.dims.length!==4)throw new Error(`Input 'x' is expected to have 3 or 4 dimensions, got ${r.dims.length}`);if(!O.areEqual(i.dims,[])&&!O.areEqual(i.dims,[1])&&i.dims.length!==2)throw new Error(`Input 'position_ids' is expected to have 0, 1, or 2 dimensions, got ${i.dims.length}`);if(a.dims.length!==2)throw new Error(`Input 'cos_cache' is expected to have 2 dimensions, got ${a.dims.length}`);if(n.dims.length!==2)throw new Error(`Input 'sin_cache' is expected to have 2 dimensions, got ${n.dims.length}`);if(!O.areEqual(a.dims,n.dims))throw new Error("Inputs 'cos_cache' and 'sin_cache' are expected to have the same shape");if(o>0&&s===0)throw new Error("num_heads must be provided if rotary_embedding_dim is specified");let l=r.dims[0],d=r.dims[r.dims.length-2],c=a.dims[0],f=O.sizeFromDimension(r.dims,1)/d,m=o===0?a.dims[1]*2:f/s;if(o>m)throw new Error("rotary_embedding_dim must be less than or equal to head_size");if(i.dims.length===2){if(l!==i.dims[0])throw new Error(`Input 'position_ids' dimension 0 should be of size batch_size, got ${i.dims[0]}`);if(d!==i.dims[1])throw new Error(`Input 'position_ids' dimension 1 should be of size sequence_length, got ${i.dims[1]}`)}if(m/2!==a.dims[1]&&o/2!==a.dims[1])throw new Error(`Input 'cos_cache' dimension 1 should be same as head_size / 2 or rotary_embedding_dim / 2, got ${a.dims[1]}`);if(d>c)throw new Error("Updating cos_cache and sin_cache in RotaryEmbedding is not currently supported")},Ki=(e,t)=>{let{interleaved:r,numHeads:i,rotaryEmbeddingDim:a,scale:n}=t,s=e[0].dims[0],o=O.sizeFromDimension(e[0].dims,1),l=e[0].dims[e[0].dims.length-2],d=o/l,c=e[2].dims[1],f=a===0?c*2:d/i,m=new Array(s,l,d/f,f-c),y=O.computeStrides(m),_=[{type:1,data:n},{type:12,data:m},{type:12,data:y},...e[0].dims.length===3?new Array({type:12,data:[o,d,f,1]}):[],...e[0].dims.length===4?new Array({type:12,data:[o,f,l*f,1]}):[],...J(e[0].dims,e[1].dims,e[2].dims,e[3].dims,e[0].dims)],b=x=>{let $=M("input",e[0].dataType,e[0].dims.length),w=M("position_ids",e[1].dataType,e[1].dims.length),T=M("cos_cache",e[2].dataType,e[2].dims.length),C=M("sin_cache",e[3].dataType,e[3].dims.length),I=X("output",e[0].dataType,e[0].dims.length);return x.registerUniforms([{name:"scale",type:"f32"},{name:"global_shape",type:"u32",length:m.length},{name:"global_strides",type:"u32",length:y.length},{name:"input_output_strides",type:"u32",length:y.length}]),`
        ${x.declareVariables($,w,T,C,I)}

        ${x.mainStart(Lt)}
          let half_rotary_emb_dim = uniforms.${T.name}_shape[1];
          let bsnh = global_idx / uniforms.global_strides % uniforms.global_shape;
          let size = uniforms.global_shape[0] * uniforms.global_strides[0];
          ${x.guardAgainstOutOfBoundsWorkgroupSizes("size")}

          if (bsnh[3] < half_rotary_emb_dim) {
            let position_ids_idx =
                ${w.broadcastedIndicesToOffset("bsnh.xy",X("",w.type.tensor,2))};
            let position_id =
                u32(${w.getByOffset("position_ids_idx")}) + select(0, bsnh[1], position_ids_idx == 0);
            let i = dot(bsnh, uniforms.input_output_strides) + select(0, bsnh[3], ${r});
            let j = i + select(half_rotary_emb_dim, 1, ${r});
            let re = ${$.getByOffset("i")} * ${T.get("position_id","bsnh[3]")} -
                ${$.getByOffset("j")} * ${C.get("position_id","bsnh[3]")};
            ${I.setByOffset("i","re")}
            let im = ${$.getByOffset("i")} * ${C.get("position_id","bsnh[3]")} +
                ${$.getByOffset("j")} * ${T.get("position_id","bsnh[3]")};
            ${I.setByOffset("j","im")}
          } else {
            let k = dot(bsnh, uniforms.input_output_strides) + half_rotary_emb_dim;
            ${I.setByOffset("k",$.getByOffset("k"))}
          }
        }`};return{name:"RotaryEmbedding",shaderCache:{hint:he({interleaved:r}).cacheKey,inputDependencies:["rank","rank","rank","rank"]},getShaderSource:b,getRunData:()=>({outputs:[{dims:e[0].dims,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(O.size(m)/Lt)},programUniforms:_})}},_f=(e,t)=>{rl(e.inputs,t),e.compute(Ki(e.inputs,t))}}),al,nl,ia,sl,wf,Xg=L(()=>{xe(),ie(),sn(),hf(),yf(),_t(),bf(),oe(),al=(e,t)=>{if(t.doRotary&&e.length<=7)throw new Error("cos_cache and sin_cache inputs are required if do_rotary is specified");let r=e[0],i=e[1],a=e[2],n=e[3],s=e[4];if(t.doRotary!==0&&e.length<=7)throw new Error("cos_cast and sin_cache are expected if do_rotary attribute is non-zero");if(t.localWindowSize!==-1)throw new Error("Local attention is not supported");if(t.softcap!==0)throw new Error("Softcap is not supported");if(t.rotaryInterleaved!==0)throw new Error("Rotary interleaved is not supported");if(t.smoothSoftmax)throw new Error("Smooth softmax is not supported");if(r.dims.length!==3&&r.dims.length!==5)throw new Error("Input query is expected to have 3 or 5 dimensions");let o=!1,l=r.dims[0],d=r.dims[1],c=r.dims.length===3?o?r.dims[2]/3:r.dims[2]:t.numHeads*r.dims[4],f=d,m=0,y=!i||i.dims.length===0,_=Math.floor(y?c/(t.numHeads+2*t.kvNumHeads):c/t.numHeads);y&&(c=_*t.numHeads);let b=n&&n.dims.length!==0,x=s&&s.dims.length!==0;if(b&&n.dims.length===4&&n.dims[0]===l&&n.dims[1]!==t.kvNumHeads&&n.dims[2]===t.kvNumHeads&&n.dims[3]===_)throw new Error("BSNH pastKey/pastValue is not supported");if(b&&x){if(n.dims.length!==4)throw new Error('Input "past_key" is expected to have 4 dimensions');if(s.dims.length!==4)throw new Error('Input "past_value" is expected to have 4 dimensions');m=n.dims[2]}else if(b||x)throw new Error('Input "past_key" and "past_value" shall be both present or both absent');let $=1;if(i&&i.dims.length>0){if(r.dims.length!==3)throw new Error('Input "query" is expected to have 3 dimensions when key is given');if(i.dims.length<3||i.dims.length>5)throw new Error('Input "key" is expected to have 3, 4, or 5 dimensions');if(r.dims[0]!==i.dims[0])throw new Error('Input "query" and "key" shall have same dim 0 (batch size)');if(i.dims.length===3){if(r.dims[2]%i.dims[2]!==0)throw new Error('Dimension 2 of "query" should be a multiple of "key"');f=i.dims[1]}else if(i.dims.length===5){if(i.dims[2]!==t.numHeads||i.dims[3]!==2||i.dims[4]!==_)throw new Error('Expect "key" shape (batch_size, kv_sequence_length, num_heads, 2, head_size) for packed kv');if(a)throw new Error('Expect "value" be none when "key" has packed kv format.');f=i.dims[1]}else{if(i.dims[1]!==t.numHeads||i.dims[3]!==_)throw new Error('Expect "key" shape (batch_size, num_heads, kv_sequence_length, head_size) for past_key');f=i.dims[2]}}else{if(r.dims.length!==3&&r.dims.length!==5)throw new Error('Input "query" is expected to have 3 or 5 dimensions when key is empty');if(r.dims.length===5&&(r.dims[2]!==t.numHeads||r.dims[3]!==3))throw new Error('Expect "query" shape (batch_size, kv_sequence_length, num_heads, 3, head_size) for packed kv');$=3}let w=0,T=!1,C=t.kvNumHeads?_*t.kvNumHeads:c;if(a&&a.dims.length>0){if(a.dims.length!==3&&a.dims.length!==4)throw new Error('Input "value" is expected to have 3 or 4 dimensions');if(r.dims[0]!==a.dims[0])throw new Error('Input "query" and "value" shall have same dim 0 (batch_size)');if(a.dims.length===3){if(f!==a.dims[1])throw new Error('Input "key" and "value" shall have the same dim 1 (kv_sequence_length)');C=a.dims[2]}else{if(f!==a.dims[2])throw new Error('Input "past_key" and "past_value" shall have the same dim 2 (kv_sequence_length)');C=a.dims[1]*a.dims[3],T=!0}}let I=e.length>4?e[5]:void 0;if(I&&I.dims.length!==1&&I.dims[0]!==l)throw new Error('Input "seqlens" is expected to have 1 dimension and the same dim 0 as batch_size');return{batchSize:l,sequenceLength:d,pastSequenceLength:m,kvSequenceLength:f,totalSequenceLength:-1,maxSequenceLength:-1,inputHiddenSize:0,hiddenSize:c,vHiddenSize:C,headSize:_,vHeadSize:Math.floor(C/t.kvNumHeads),numHeads:t.numHeads,kvNumHeads:t.kvNumHeads,nReps:t.numHeads/t.kvNumHeads,pastPresentShareBuffer:!1,maskType:w,scale:t.scale,broadcastResPosBias:!1,passPastInKv:T,qkvFormat:$}},nl=he({perm:[0,2,1,3]}),ia=(e,t,r)=>{let i=t,a=r.kvNumHeads;return t.dims.length===3&&r.kvSequenceLength!==0&&(i=t.reshape([r.batchSize,r.kvSequenceLength,a,r.headSize]),i=e.compute(De(i,nl.perm),{inputs:[i],outputs:[-1]})[0]),i},sl=(e,t,r,i)=>{let a=7,n=["type","type"],s=[e*t],o=e*t,l=[{type:12,data:o},{type:12,data:t},{type:12,data:e}],d=c=>{let f=M("seq_lens",r.dataType,r.dims),m=M("total_seq_lens",i.dataType,i.dims),y=X("pos_ids",a,s),_=[{name:"output_size",type:"u32"},{name:"sequence_length",type:"u32"},{name:"batch_size",type:"u32"}];return`
  ${c.registerUniforms(_).declareVariables(f,m,y)}
  ${c.mainStart()}
    ${c.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
    let total_sequence_length = u32(${m.getByOffset("0")});
    let is_subsequent_prompt = uniforms.sequence_length > 1 && uniforms.sequence_length != total_sequence_length;
    let is_first_prompt = !is_subsequent_prompt && uniforms.sequence_length == total_sequence_length;
    let batch_idx = global_idx / uniforms.sequence_length;
    let sequence_idx = i32(global_idx % uniforms.sequence_length);
    var pos_id: i32 = 0;
    let seqlen = ${f.getByOffset("batch_idx")};
    let total_seqlen = seqlen + 1;
    if (is_first_prompt) {
      if (sequence_idx < total_seqlen) {
        pos_id = sequence_idx;
      } else {
        pos_id = 1;
      }
      ${y.setByOffset("global_idx","pos_id")}
    } else if (is_subsequent_prompt) {
      let past_seqlen = total_seqlen - i32(uniforms.sequence_length);
      if (past_seqlen + sequence_idx < total_seqlen) {
        pos_id = past_seqlen + sequence_idx;
      } else {
        pos_id = 1;
      }
      ${y.setByOffset("global_idx","pos_id")}
    } else if (global_idx < uniforms.batch_size) {
      ${y.setByOffset("global_idx","seqlen")}
    };
  }
  `};return{name:"GeneratePositionIds",shaderCache:{hint:`${e};${t}`,inputDependencies:n},getRunData:()=>({outputs:[{dims:s,dataType:a}],dispatchGroup:{x:Math.ceil(o/64)},programUniforms:l}),getShaderSource:d}},wf=(e,t)=>{let r=al(e.inputs,t);if(e.inputs[0].dims.length===5)throw new Error("Packed QKV is not implemented");if(e.inputs[1]?.dims.length===5)throw new Error("Packed KV is not implemented");let i=e.inputs[0],a=e.inputs[1]&&e.inputs[1].dims.length>0?e.inputs[1]:void 0,n=e.inputs[2]&&e.inputs[2].dims.length>0?e.inputs[2]:void 0,s=e.inputs[3]&&e.inputs[3].dims.length!==0?e.inputs[3]:void 0,o=e.inputs[4]&&e.inputs[4].dims.length!==0?e.inputs[4]:void 0,l=e.inputs.length>4?e.inputs[5]:void 0,d=e.inputs.length>5?e.inputs[6]:void 0,c=r.kvNumHeads?r.kvNumHeads:r.numHeads,f=he({axis:2,numOutputs:3,splitSizes:[r.numHeads*r.headSize,c*r.headSize,c*r.headSize]}),[m,y,_]=!a&&!n?e.compute(Ma([i],f),{inputs:[i],outputs:[-1,-1,-1]}):[i,a,n],b,x;if(t.doRotary){let C=e.compute(sl(r.batchSize,r.sequenceLength,l,d),{inputs:[l,d],outputs:[-1]})[0],I=e.inputs[7],z=e.inputs[8],k=he({interleaved:t.rotaryInterleaved!==0,numHeads:r.numHeads,rotaryEmbeddingDim:0,scale:t.scale}),A=[m,C,I,z],D=[-1];b=e.compute(Ki(A,k),{inputs:A,outputs:D})[0],A.splice(0,1,y);let V=he({interleaved:t.rotaryInterleaved!==0,numHeads:r.kvNumHeads,rotaryEmbeddingDim:0,scale:t.scale});x=e.compute(Ki(A,V),{inputs:A,outputs:D})[0]}let $=di(e,r.batchSize,r.numHeads,r.sequenceLength,r.headSize,t.doRotary?b:m,void 0,0),w=ia(e,t.doRotary?x:y,r),T=ia(e,_,r);hi(e,$,w,T,void 0,void 0,s,o,void 0,r,l,d)}}),ra,ol,ul,vf,Qg=L(()=>{ie(),se(),_t(),oe(),ra=(e,t,r,i,a,n,s,o)=>{let l=$e(n),d=l===1?"f32":`vec${l}f`,c=l===1?"vec2f":`mat2x${l}f`,f=a*s,m=64;f===1&&(m=256);let y=[a,s,n/l],_=[a,s,2],b=["rank","type","type"],x=[];x.push(...J(y,_));let $=w=>{let T=M("x",t.dataType,3,l),C=M("scale",r.dataType,r.dims),I=M("bias",i.dataType,i.dims),z=X("output",1,3,2),k=[T,C,I,z];return`
  var<workgroup> workgroup_shared : array<${c}, ${m}>;
  const workgroup_size = ${m}u;
  ${w.declareVariables(...k)}
  ${w.mainStart(m)}
    let batch = workgroup_index / uniforms.x_shape[1];
    let channel = workgroup_index % uniforms.x_shape[1];
    let hight = uniforms.x_shape[2];
    // initialize workgroup memory
    var sum = ${d}(0);
    var squared_sum = ${d}(0);
    for (var h = local_idx; h < hight; h += workgroup_size) {
      let value = ${d}(${T.get("batch","channel","h")});
      sum += value;
      squared_sum += value * value;
    }
    workgroup_shared[local_idx] = ${c}(sum, squared_sum);
    workgroupBarrier();

    for (var currSize = workgroup_size >> 1;  currSize > 0; currSize = currSize >> 1) {
      if (local_idx < currSize) {
        workgroup_shared[local_idx] = workgroup_shared[local_idx] + workgroup_shared[local_idx + currSize];
      }
      workgroupBarrier();
    }
    if (local_idx == 0) {
      let sum_final = ${yt("workgroup_shared[0][0]",l)} / f32(hight * ${l});
      let squared_sum_final = ${yt("workgroup_shared[0][1]",l)} / f32(hight * ${l});

      let inv_std_dev = inverseSqrt(squared_sum_final - sum_final * sum_final + f32(${o}));
      let channel_scale = inv_std_dev * f32(scale[channel]);
      let channel_shift = f32(bias[channel]) - sum_final * channel_scale;
      output[workgroup_index] = vec2f(channel_scale, channel_shift);
    }
  }`};return e.compute({name:"InstanceNormComputeChannelScaleShift",shaderCache:{hint:`${l};${o};${m}`,inputDependencies:b},getRunData:()=>({outputs:[{dims:_,dataType:1}],dispatchGroup:{x:f},programUniforms:x}),getShaderSource:$},{inputs:[t,r,i],outputs:[-1]})[0]},ol=(e,t,r)=>{let i=t[0].dims,a=i,n=2,s=i[0],o=i[1],l=O.sizeFromDimension(i,n),d=$e(l),c=O.size(a)/d,f=ra(e,t[0],t[1],t[2],s,l,o,r.epsilon),m=[s,o,l/d],y=[s,o],_=["type","none"],b=x=>{let $=M("x",t[0].dataType,m.length,d),w=M("scale_shift",1,y.length,2),T=X("output",t[0].dataType,m.length,d),C=[$,w,T];return`
  ${x.registerUniform("output_size","u32").declareVariables(...C)}
  ${x.mainStart()}
  ${x.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
      let outputIndices = ${T.offsetToIndices("global_idx")};
      let batch = outputIndices[0];
      let channel = outputIndices[1];
      let scale_shift = ${w.getByIndices("vec2<u32>(batch, channel)")};
      let value = ${$.getByOffset("global_idx")} * ${T.type.value}(scale_shift.x) + ${T.type.value}(scale_shift.y);
      ${T.setByOffset("global_idx","value")};
  }`};e.compute({name:"InstanceNormalization",shaderCache:{hint:`${d}`,inputDependencies:_},getRunData:()=>({outputs:[{dims:a,dataType:t[0].dataType}],dispatchGroup:{x:Math.ceil(c/64)},programUniforms:[{type:12,data:c},...J(m,y,m)]}),getShaderSource:b},{inputs:[t[0],f]})},ul=(e,t,r)=>{let i=t[0].dims,a=i,n=i[0],s=i[i.length-1],o=O.sizeFromDimension(i,1)/s,l=$e(s),d=O.size(a)/l,c=[{type:12,data:o},{type:12,data:Math.floor(s/l)}],f=["type","type"],m=!1,y=[0,i.length-1];for(let $=0;$<i.length-2;$++)m=m||i[$+1]!==1,y.push($+1);m=m&&i[i.length-1]!==1;let _=m?e.compute(De(e.inputs[0],y),{inputs:[e.inputs[0]],outputs:[-1]})[0]:e.inputs[0].reshape(Array.from({length:i.length},($,w)=>i[y[w]])),b=ra(e,_,t[1],t[2],n,o,s,r.epsilon),x=$=>{let w=Ie(t[0].dataType),T=l===1?"vec2f":`mat${l}x2f`,C=k=>{let A=k===0?"x":"y",D=l===1?"f32":`vec${l}f`;switch(l){case 1:return`${w}(${D}(scale.${A}))`;case 2:return`vec2<${w}>(${D}(scale[0].${A}, scale[1].${A}))`;case 4:return`vec4<${w}>(${D}(scale[0].${A}, scale[1].${A}, scale[2].${A}, scale[3].${A}))`;default:throw new Error(`Not supported compoents ${l}`)}},I=M("input",t[0].dataType,t[0].dims,l),z=X("output",t[0].dataType,a,l);return`
  @group(0) @binding(0) var<storage, read> input : array<${I.type.storage}>;
  @group(0) @binding(1) var<storage, read> scale_input : array<${T}>;
  @group(0) @binding(2) var<storage, read_write> output : array<${z.type.storage}>;
  struct Uniforms {H: u32, C : u32};
  @group(0) @binding(3) var<uniform> uniforms: Uniforms;

  ${$.mainStart()}
    let current_image_number = global_idx / (uniforms.C * uniforms.H);
    let current_channel_number = global_idx % uniforms.C;

    let scale_offset = current_image_number * uniforms.C + current_channel_number;
    let scale = scale_input[scale_offset];
    output[global_idx] = fma(input[global_idx], ${C(0)}, ${C(1)});
  }`};e.compute({name:"InstanceNormalizationNHWC",shaderCache:{hint:`${l}`,inputDependencies:f},getRunData:()=>({outputs:[{dims:a,dataType:t[0].dataType}],dispatchGroup:{x:Math.ceil(d/64)},programUniforms:c}),getShaderSource:x},{inputs:[t[0],b]})},vf=(e,t)=>{t.format==="NHWC"?ul(e,e.inputs,t):ol(e,e.inputs,t)}}),ll,dl,$f,Jg=L(()=>{ie(),se(),oe(),ll=e=>{if(!e||e.length<2)throw new Error("layerNorm requires at least 2 inputs.")},dl=(e,t,r)=>{let i=t.simplified,a=e[0].dims,n=e[1],s=!i&&e[2],o=a,l=O.normalizeAxis(t.axis,a.length),d=O.sizeToDimension(a,l),c=O.sizeFromDimension(a,l),f=O.size(n.dims),m=s?O.size(s.dims):0;if(f!==c||s&&m!==c)throw new Error(`Size of X.shape()[axis:] == ${c}.
       Size of scale and bias (if provided) must match this.
       Got scale size of ${f} and bias size of ${m}`);let y=[];for(let I=0;I<a.length;++I)I<l?y.push(a[I]):y.push(1);let _=$e(c),b=["type","type"],x=[{type:12,data:d},{type:1,data:c},{type:12,data:Math.floor(c/_)},{type:1,data:t.epsilon}];s&&b.push("type");let $=r>1,w=r>2,T=I=>{let z=Ie(e[0].dataType),k=[M("x",e[0].dataType,e[0].dims,_),M("scale",n.dataType,n.dims,_)];s&&k.push(M("bias",s.dataType,s.dims,_)),k.push(X("output",e[0].dataType,o,_)),$&&k.push(X("mean_data_output",1,y)),w&&k.push(X("inv_std_output",1,y));let A=[{name:"norm_count",type:"u32"},{name:"norm_size",type:"f32"},{name:"norm_size_vectorized",type:"u32"},{name:"epsilon",type:"f32"}];return`
  ${I.registerUniforms(A).declareVariables(...k)}
  ${I.mainStart()}
    ${I.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.norm_count")}
    let offset = global_idx * uniforms.norm_size_vectorized;
    var mean_vector = ${Sa("f32",_)};
    var mean_square_vector = ${Sa("f32",_)};

    for (var h: u32 = 0u; h < uniforms.norm_size_vectorized; h++) {
      let value = ${Pt(z,_,"x[h + offset]")};
      mean_vector += value;
      mean_square_vector += value * value;
    }
    let mean = ${yt("mean_vector",_)} / uniforms.norm_size;
    let inv_std_dev = inverseSqrt(${yt("mean_square_vector",_)} / uniforms.norm_size ${i?"":"- mean * mean"} + uniforms.epsilon);

    for (var j: u32 = 0; j < uniforms.norm_size_vectorized; j++) {
      let f32input = ${Pt(z,_,"x[j + offset]")};
      let f32scale = ${Pt(z,_,"scale[j]")};
      output[j + offset] = ${k[0].type.value}((f32input ${i?"":"- mean"}) * inv_std_dev * f32scale
        ${s?`+ ${Pt(z,_,"bias[j]")}`:""}
      );
    }

    ${$?"mean_data_output[global_idx] = mean":""};
    ${w?"inv_std_output[global_idx] = inv_std_dev":""};
  }`},C=[{dims:o,dataType:e[0].dataType}];return $&&C.push({dims:y,dataType:1}),w&&C.push({dims:y,dataType:1}),{name:"LayerNormalization",shaderCache:{hint:`${_};${r};${i}`,inputDependencies:b},getRunData:()=>({outputs:C,dispatchGroup:{x:Math.ceil(d/64)},programUniforms:x}),getShaderSource:T}},$f=(e,t)=>{ll(e.inputs),e.compute(dl(e.inputs,t,e.outputCount))}}),pl,xf,ey=L(()=>{se(),pn(),cn(),pl=e=>{if(!e||e.length!==2)throw new Error("MatMul requires 2 inputs.");if(e[0].dims[e[0].dims.length-1]!==e[1].dims[e[1].dims.length-2])throw new Error("shared dimension does not match.")},xf=e=>{pl(e.inputs);let t=Wt.calcShape(e.inputs[0].dims,e.inputs[1].dims,!0);if(!t)throw new Error("Can't use matmul on the given tensors");let r=t[t.length-1],i=e.inputs[0].dims[e.inputs[0].dims.length-1];if(r<8&&i<8)e.compute(dn(e.inputs,{activation:""},t));else{let a=t[t.length-2],n=O.size(e.inputs[0].dims.slice(0,-2)),s=O.size(e.inputs[1].dims.slice(0,-2));if(n!==1&&a===1&&s===1){let o=e.inputs[0].reshape([1,n,i]),l=e.inputs[1].reshape([1,i,r]),d=[1,n,r],c=[o,l];e.compute(Hi(c,{activation:""},t,d),{inputs:c})}else e.compute(Hi(e.inputs,{activation:""},t))}}}),cl,fl,hl,Cf,Tf,ty=L(()=>{ie(),se(),xe(),oe(),cl=(e,t)=>{if(e.length<3||e.length>4)throw new Error("MatMulNBits requires 3 or 4 inputs");let r=e[0],i=r.dims.length;if(r.dims[i-1]!==t.k)throw new Error("The last dim of input shape does not match the k value");let a=Math.floor((t.k+t.blockSize-1)/t.blockSize),n=t.blockSize/8*t.bits,s=e[1];if(!O.areEqual(s.dims,[t.n,a,n]))throw new Error("The second inputs must be 3D tensor with shape N X nBlocksPerCol X blobSize");let o=e[2].dims;if(O.size(o)!==t.n*a)throw new Error("scales input size error.");if(e.length===4){let l=e[3].dims,d=t.n*(t.bits===8?a:Math.floor((a*t.bits+7)/8));if(O.size(l)!==d)throw new Error("zeroPoints input size error.")}},fl=(e,t)=>{let r=e[0].dims,i=r.length,a=r[i-2],n=t.k,s=t.n,o=r.slice(0,i-2),l=O.size(o),d=e[1].dims[2]/4,c=e[0].dataType,f=$e(t.k),m=$e(d),y=$e(s),_=o.concat([a,s]),b=a>1&&s/y%2===0?2:1,x=O.size(_)/y/b,$=64,w=[],T=[l,a,n/f],C=O.convertShape(e[1].dims).slice();C.splice(-1,1,d/m),w.push(...J(T)),w.push(...J(C)),w.push(...J(e[2].dims)),e.length===4&&w.push(...J(O.convertShape(e[3].dims)));let I=[l,a,s/y];w.push(...J(I));let z=k=>{let A=T.length,D=M("a",e[0].dataType,A,f),V=M("b",12,C.length,m),G=M("scales",e[2].dataType,e[2].dims.length),H=[D,V,G],F=e.length===4?M("zero_points",12,e[3].dims.length):void 0;F&&H.push(F);let W=I.length,re=X("output",e[0].dataType,W,y),ee=Ie(e[0].dataType),K=(()=>{switch(f){case 1:return`array<${ee}, 8>`;case 2:return`mat4x2<${ee}>`;case 4:return`mat2x4<${ee}>`;default:throw new Error(`${f}-component is not supported.`)}})(),ne=()=>{let U=`
          // reuse a data
            var input_offset = ${D.indicesToOffset(`${D.type.indices}(batch, row, word_offset)`)};
            var a_data: ${K};
            for (var j: u32 = 0; j < ${8/f}; j++) {
              a_data[j] = ${D.getByOffset("input_offset")};
              input_offset++;
            }
          `;for(let j=0;j<y*b;j++)U+=`
            b_value = ${m===1?`b${j}_data`:`b${j}_data[i]`};
            b_value_lower = unpack4xU8(b_value & b_mask);
            b_value_upper = unpack4xU8((b_value >> 4) & b_mask);
            b_quantized_values = ${K}(${Array.from({length:4},(ae,pe)=>`${ee}(b_value_lower[${pe}]), ${ee}(b_value_upper[${pe}])`).join(", ")});
            b_dequantized_values = ${f===1?`${K}(${Array.from({length:8},(ae,pe)=>`(b_quantized_values[${pe}] - ${F?`zero_point${j}`:"zero_point"}) * scale${j}`).join(", ")});`:`(b_quantized_values - ${K}(${Array(8).fill(`${F?`zero_point${j}`:"zero_point"}`).join(",")})) * scale${j};`};
            workgroup_shared[local_id.x * ${b} + ${Math.floor(j/y)}]${y>1?`[${j%y}]`:""} += ${Array.from({length:8/f},(ae,pe)=>`${f===1?`a_data[${pe}] * b_dequantized_values[${pe}]`:`dot(a_data[${pe}], b_dequantized_values[${pe}])`}`).join(" + ")};
          `;return U},Y=()=>{let U=`
            var col_index = col * ${y};
            ${F?`
            let zero_point_bytes_per_col = (nBlocksPerCol + 1) / 2;
            var zero_point_byte_count: u32;
            var zero_point_word_index: u32;
            var zero_point_byte_offset: u32;
            let zero_point_nibble_offset: u32 = block & 0x1u;
            var zero_point_bits_offset: u32;
            var zero_point_word: u32;`:`
            // The default zero point is 8 for unsigned 4-bit quantization.
            let zero_point = ${ee}(8);`}
            `;for(let j=0;j<y*b;j++)U+=`
            let scale${j} = ${G.getByOffset("col_index * nBlocksPerCol + block")};
            ${F?`
            zero_point_byte_count = col_index * zero_point_bytes_per_col + (block >> 0x1u);
            zero_point_word_index = zero_point_byte_count >> 0x2u;
            zero_point_byte_offset = zero_point_byte_count & 0x3u;
            zero_point_bits_offset = (zero_point_byte_offset << 3) + (zero_point_nibble_offset << 2);
            zero_point_word = ${F.getByOffset("zero_point_word_index")} >> zero_point_bits_offset;
            let zero_point${j} = ${ee}((zero_point_word) & 0xFu);`:""}
            col_index += 1;`;return U},ye=()=>{let U=`col_index = col * ${y};`;for(let j=0;j<y*b;j++)U+=`
            let b${j}_data = ${V.getByIndices(`${V.type.indices}(col_index, block, word)`)};
            col_index += 1;`;return U+=`
            var b_value: u32;
            let b_mask: u32 = 0x0F0F0F0Fu;
            var b_value_lower: vec4<u32>;
            var b_value_upper: vec4<u32>;
            var b_quantized_values: ${K};
            var b_dequantized_values: ${K};`,U};return`
        var<workgroup> workgroup_shared: array<${re.type.value}, ${b*$}>;
        ${k.declareVariables(...H,re)}
        ${k.mainStart([$,1,1])}
          let output_indices = ${re.offsetToIndices(`(global_idx / ${$}) * ${b}`)};
          let col = output_indices[2];
          let row = output_indices[1];
          let batch = output_indices[0];
          let nBlocksPerCol = uniforms.b_shape[1];

          for (var block = local_id.x; block < nBlocksPerCol; block += ${$}) {
            //process one block
            var word_offset: u32 = block * ${t.blockSize/f};
            ${Y()}
            for (var word: u32 = 0; word < ${d}; word += ${m}) {
              ${ye()}
              for (var i: u32 = 0; i < ${m}; i++) {
                ${ne()}
                word_offset += ${8/f};
              }
            }
          }
          workgroupBarrier();

          if (local_id.x < ${b}) {
            var output_value: ${re.type.value} = ${re.type.value}(0);
            var workgroup_shared_offset: u32 = local_id.x;
            for (var b: u32 = 0u; b < ${$}u; b++) {
              output_value += workgroup_shared[workgroup_shared_offset];
              workgroup_shared_offset += ${b};
            }
            ${re.setByIndices(`${re.type.indices}(batch, row, col + local_id.x)`,"output_value")};
          }
        }`};return{name:"MatMulNBits",shaderCache:{hint:`${t.blockSize};${t.bits};${f};${m};${y};${b};${$}`,inputDependencies:Array(e.length).fill("rank")},getRunData:()=>({outputs:[{dims:_,dataType:c}],dispatchGroup:{x},programUniforms:w}),getShaderSource:z}},hl=(e,t)=>{let r=e[0].dims,i=r.length,a=r[i-2],n=t.k,s=t.n,o=r.slice(0,i-2),l=O.size(o),d=e[1].dims[2]/4,c=e[0].dataType,f=$e(t.k),m=$e(d),y=o.concat([a,s]),_=128,b=s%8===0?8:s%4===0?4:1,x=_/b,$=x*m*8,w=$/f,T=$/t.blockSize,C=O.size(y)/b,I=[],z=[l,a,n/f],k=O.convertShape(e[1].dims).slice();k.splice(-1,1,d/m),I.push(...J(z)),I.push(...J(k)),I.push(...J(e[2].dims)),e.length===4&&I.push(...J(O.convertShape(e[3].dims)));let A=[l,a,s];I.push(...J(A));let D=V=>{let G=z.length,H=M("a",e[0].dataType,G,f),F=M("b",12,k.length,m),W=M("scales",e[2].dataType,e[2].dims.length),re=[H,F,W],ee=e.length===4?M("zero_points",12,e[3].dims.length):void 0;ee&&re.push(ee);let K=A.length,ne=X("output",e[0].dataType,K),Y=Ie(e[0].dataType),ye=()=>{switch(f){case 1:return`
          let a_data0 = vec4<${Y}>(sub_a[word_offset], sub_a[word_offset + 1], sub_a[word_offset + 2], sub_a[word_offset + 3]);
          let a_data1 = vec4<${Y}>(sub_a[word_offset + 4], sub_a[word_offset + 5], sub_a[word_offset + 6], sub_a[word_offset + 7]);`;case 2:return`
          let a_data0 = vec4<${Y}>(sub_a[word_offset], sub_a[word_offset + 1]);
          let a_data1 = vec4<${Y}>(sub_a[word_offset + 2], sub_a[word_offset + 3]);`;case 4:return`
          let a_data0 = sub_a[word_offset];
          let a_data1 = sub_a[word_offset + 1];`;default:throw new Error(`${f}-component is not supported.`)}};return`
        var<workgroup> sub_a: array<${H.type.value}, ${w}>;
        var<workgroup> inter_results: array<array<${ne.type.value}, ${x}>, ${b}>;
        ${V.declareVariables(...re,ne)}
        ${V.mainStart([x,b,1])}
          let output_indices = ${ne.offsetToIndices(`workgroup_index * ${b}`)};
          let col = output_indices[2];
          let row = output_indices[1];
          let batch = output_indices[0];
          let n_blocks_per_col = uniforms.b_shape[1];
          let num_tiles =  (n_blocks_per_col - 1) / ${T} + 1;

          // Loop over shared dimension.
          for (var tile: u32 = 0; tile < num_tiles; tile += 1) {
            let a_col_start = tile * ${w};
            // load one tile A data into shared memory.
            for (var a_offset = local_idx; a_offset < ${w}; a_offset += ${_})
            {
              let a_col = a_col_start + a_offset;
              if (a_col < uniforms.a_shape[2])
              {
                sub_a[a_offset] = ${H.getByIndices(`${H.type.indices}(batch, row, a_col)`)};
              } else {
                sub_a[a_offset] = ${H.type.value}(0);
              }
            }
            workgroupBarrier();

            // each thread process one block
            let b_row = col + local_id.y;
            let block = tile * ${T} + local_id.x;
            ${ee?`
            let zero_point_bytes_per_col = (n_blocks_per_col + 1) / 2;
            let zero_point_byte_count = b_row * zero_point_bytes_per_col + (block >> 0x1u);
            let zero_point_word_index = zero_point_byte_count >> 0x2u;
            let zero_point_byte_offset = zero_point_byte_count & 0x3u;
            let zero_point_nibble_offset: u32 = block & 0x1u;
            let zero_point_bits_offset = (zero_point_byte_offset << 3) + (zero_point_nibble_offset << 2);
            let zero_point_word = ${ee.getByOffset("zero_point_word_index")} >> zero_point_bits_offset;
            let zero_point = ${Y}((zero_point_word) & 0xFu);`:`
            // The default zero point is 8 for unsigned 4-bit quantization.
            let zero_point = ${Y}(8);`}
            let scale = ${W.getByOffset("b_row * n_blocks_per_col + block")};
            let b_data = ${F.getByIndices(`${F.type.indices}(b_row, block, 0)`)};
            var word_offset = local_id.x * ${t.blockSize/f};
            for (var i: u32 = 0; i < ${m}; i++) {
              ${ye()}
              let b_value = ${m===1?"b_data":"b_data[i]"};
              let b_value_lower = unpack4xU8(b_value & 0x0F0F0F0Fu);
              let b_value_upper = unpack4xU8((b_value >> 4) & 0x0F0F0F0Fu);
              let b_quantized_values = mat2x4<${Y}>(${Array.from({length:4},(U,j)=>`${Y}(b_value_lower[${j}]), ${Y}(b_value_upper[${j}])`).join(", ")});
              let b_dequantized_values = (b_quantized_values - mat2x4<${Y}>(${Array(8).fill("zero_point").join(",")})) * scale;
              inter_results[local_id.y][local_id.x] += ${Array.from({length:2},(U,j)=>`${`dot(a_data${j}, b_dequantized_values[${j}])`}`).join(" + ")};
              word_offset += ${8/f};
            }
            workgroupBarrier();
          }

          if (local_idx < ${b}) {
            var output_value: ${ne.type.value} = ${ne.type.value}(0);
            for (var b = 0u; b < ${x}; b++) {
              output_value += inter_results[local_idx][b];
            }
            if (col + local_idx < uniforms.output_shape[2])
            {
              ${ne.setByIndices(`${ne.type.indices}(batch, row, col + local_idx)`,"output_value")}
            }
          }
        }`};return{name:"BlockwiseMatMulNBits32",shaderCache:{hint:`${t.blockSize};${f};${m};${x};${b}`,inputDependencies:Array(e.length).fill("rank")},getRunData:()=>({outputs:[{dims:y,dataType:c}],dispatchGroup:{x:C},programUniforms:I}),getShaderSource:D}},Cf=(e,t)=>{cl(e.inputs,t),t.blockSize===32&&e.adapterInfo.isVendor("intel")&&e.adapterInfo.isArchitecture("gen-12lp")?e.compute(hl(e.inputs,t)):e.compute(fl(e.inputs,t))},Tf=e=>he(e)}),ml,gl,yl,_l,bl,wl,vl,$l,Sf,iy=L(()=>{ie(),se(),oe(),ml=e=>{if(!e||e.length<1)throw new Error("Too few inputs");if(e[0].dataType!==1&&e[0].dataType!==10)throw new Error("Input type must be float or float16.");if(e.length>=2){let t=e[0].dims.length*2===e[1].dims[0];if(e.length===4&&(t=e[3].dims[0]*2===e[1].dims[0]),!t)throw new Error("The pads should be a 1D tensor of shape [2 * input_rank] or [2 * num_axes].")}},gl=(e,t,r)=>{let i="";for(let a=t-1;a>=0;--a)i+=`
            k = i32(${e.indicesGet("indices",a)}) - ${Q("uniforms.pads",a,r)};
            if (k < 0) {
              break;
            }
            if (k >= i32(${Q("uniforms.x_shape",a,t)})) {
              break;
            }
            offset += k * i32(${Q("uniforms.x_strides",a,t)});
        `;return`
          value = ${e.type.value}(uniforms.constant_value);
          for (var i = 0; i < 1; i++) {
            var offset = 0;
            var k = 0;
            ${i}
            value = x[offset];
          }
      `},yl=(e,t,r)=>{let i="";for(let a=t-1;a>=0;--a)i+=`
                k = i32(${e.indicesGet("indices",a)}) - ${Q("uniforms.pads",a,r)};
                if (k < 0) {
                  k = -k;
                }
                {
                  let _2n_1 = 2 * (i32(${Q("uniforms.x_shape",a,t)}) - 1);
                  k = k % _2n_1;
                  if(k >= i32(${Q("uniforms.x_shape",a,t)})) {
                    k = _2n_1 - k;
                  }
                }
                offset += k * i32(${Q("uniforms.x_strides",a,t)});
            `;return`
              var offset = 0;
              var k = 0;
              ${i}
              value = x[offset];
          `},_l=(e,t,r)=>{let i="";for(let a=t-1;a>=0;--a)i+=`
                k = i32(${e.indicesGet("indices",a)}) - ${Q("uniforms.pads",a,r)};
                if (k < 0) {
                  k = 0;
                }
                if (k >= i32(${Q("uniforms.x_shape",a,t)})) {
                  k = i32(${Q("uniforms.x_shape",a,t)}) - 1;
                }
                offset += k * i32(${Q("uniforms.x_strides",a,t)});
            `;return`
              var offset = 0;
              var k = 0;
              ${i}
              value = x[offset];
          `},bl=(e,t,r)=>{let i="";for(let a=t-1;a>=0;--a)i+=`
                k = i32(${e.indicesGet("indices",a)}) - ${Q("uniforms.pads",a,r)};
                if (k < 0)  {
                  k += i32(${Q("uniforms.x_shape",a,t)}]);
                }
                if (k >= i32(${Q("uniforms.x_shape",a,t)})) {
                  k -= i32(${Q("uniforms.x_shape",a,t)});
                }
                offset += k * i32(${Q("uniforms.x_strides",a,t)});
            `;return`
              var offset = 0;
              var k = 0;
              ${i}
              value = x[offset];
          `},wl=(e,t,r)=>{switch(r.mode){case 0:return gl(e,t,r.pads.length);case 1:return yl(e,t,r.pads.length);case 2:return _l(e,t,r.pads.length);case 3:return bl(e,t,r.pads.length);default:throw new Error("Invalid mode")}},vl=(e,t)=>{let r=O.padShape(e[0].dims.slice(),t.pads),i=e[0].dims,a=O.size(r),n=[{type:12,data:a},{type:6,data:t.pads}],s=e.length>=3&&e[2].data;t.mode===0&&n.push({type:s?e[2].dataType:1,data:t.value}),n.push(...J(e[0].dims,r));let o=["rank"],l=d=>{let c=X("output",e[0].dataType,r.length),f=M("x",e[0].dataType,i.length),m=f.type.value,y=wl(c,i.length,t),_=[{name:"output_size",type:"u32"},{name:"pads",type:"i32",length:t.pads.length}];return t.mode===0&&_.push({name:"constant_value",type:s?m:"f32"}),`
            ${d.registerUniforms(_).declareVariables(f,c)}
            ${d.mainStart()}
            ${d.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}

            let indices = ${c.offsetToIndices("global_idx")};

            var value = ${m}(0);
            ${y}
            output[global_idx] = value;
        }`};return{name:"Pad",shaderCache:{hint:`${t.mode}${s}`,inputDependencies:o},getRunData:()=>({outputs:[{dims:r,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(O.size(r)/64)},programUniforms:n}),getShaderSource:l}},$l=(e,t)=>{if(e.length>1){let r=e[1].getBigInt64Array(),i=e.length>=3&&e[2].data?e[2].dataType===10?e[2].getUint16Array()[0]:e[2].getFloat32Array()[0]:0,a=e[0].dims.length,n=new Int32Array(2*a).fill(0);if(e.length>=4){let o=e[3].getBigInt64Array();for(let l=0;l<o.length;l++)n[Number(o[l])]=Number(r[l]),n[Number(o[l])+a]=Number(r[l+o.length])}else r.forEach((o,l)=>n[Number(l)]=Number(o));let s=[];return n.forEach(o=>s.push(o)),{mode:t.mode,value:i,pads:s}}else return t},Sf=(e,t)=>{ml(e.inputs);let r=$l(e.inputs,t);e.compute(vl(e.inputs,r),{inputs:[0]})}}),ri,aa,na,sa,oa,xl,Cl,ua,la,If,kf,da,Ef,zf,pa,Af,Of,Rf,Bf,ry=L(()=>{We(),ie(),se(),oe(),ri=e=>{if(ge.webgpu.validateInputContent&&(!e||e.length!==1))throw new Error("Pool ops requires 1 input.")},aa=(e,t,r)=>{let i=t.format==="NHWC",a=e.dims.slice();i&&a.splice(1,0,a.pop());let n=Object.hasOwnProperty.call(t,"dilations"),s=t.kernelShape.slice(),o=t.strides.slice(),l=n?t.dilations.slice():[],d=t.pads.slice();Fi.adjustPoolAttributes(r,a,s,o,l,d);let c=Fi.computePoolOutputShape(r,a,o,l,s,d,t.autoPad),f=Object.assign({},t);n?Object.assign(f,{kernelShape:s,strides:o,pads:d,dilations:l,cacheKey:t.cacheKey}):Object.assign(f,{kernelShape:s,strides:o,pads:d,cacheKey:t.cacheKey});let m=c.slice();return m.push(m.splice(1,1)[0]),[f,i?m:c]},na=(e,t)=>{let r=t.format==="NHWC",i=O.size(e),a=O.size(t.kernelShape),n=[{type:12,data:i},{type:12,data:a}],s=[{name:"outputSize",type:"u32"},{name:"kernelSize",type:"u32"}];if(t.kernelShape.length<=2){let o=t.kernelShape[t.kernelShape.length-1],l=t.strides[t.strides.length-1],d=t.pads[t.pads.length/2-1],c=t.pads[t.pads.length-1],f=!!(d+c);n.push({type:12,data:o},{type:12,data:l},{type:12,data:d},{type:12,data:c}),s.push({name:"kw",type:"u32"},{name:"sw",type:"u32"},{name:"pwStart",type:"u32"},{name:"pwEnd",type:"u32"});let m=!1;if(t.kernelShape.length===2){let y=t.kernelShape[t.kernelShape.length-2],_=t.strides[t.strides.length-2],b=t.pads[t.pads.length/2-2],x=t.pads[t.pads.length-2];m=!!(b+x),n.push({type:12,data:y},{type:12,data:_},{type:12,data:b},{type:12,data:x}),s.push({name:"kh",type:"u32"},{name:"sh",type:"u32"},{name:"phStart",type:"u32"},{name:"phEnd",type:"u32"})}return[n,s,!0,f,m]}else{if(r)throw new Error("Pooling with kernelShape.length > 2 is not supported for NHWC format.");let o=O.computeStrides(t.kernelShape);n.push({type:12,data:o},{type:12,data:t.pads},{type:12,data:t.strides}),s.push({name:"kernelStrides",type:"u32",length:o.length},{name:"pads",type:"u32",length:t.pads.length},{name:"strides",type:"u32",length:t.strides.length});let l=t.pads.reduce((d,c)=>d+c);return[n,s,!!l,!1,!1]}},sa=(e,t,r,i,a,n,s,o,l,d,c,f)=>{let m=a.format==="NHWC",y=t.type.value,_=X("output",t.type.tensor,i);if(a.kernelShape.length<=2){let b="",x="",$="",w=r-(m?2:1);if(c?b=`
                for (var i: u32 = 0u; i < uniforms.kw; i++) {
                  xIndices[${w}] = indices[${w}] * uniforms.sw - uniforms.pwStart + i;
                  if (xIndices[${w}] < 0 || xIndices[${w}]
                      >= uniforms.x_shape[${w}]) {
                    pad++;
                    continue;
                  }
                  let x_val = x[${t.indicesToOffset("xIndices")}];
                  ${n}
                }`:b=`
                for (var i: u32 = 0u; i < uniforms.kw; i++) {
                  xIndices[${w}] = indices[${w}] * uniforms.sw - uniforms.pwStart + i;
                  let x_val = x[${t.indicesToOffset("xIndices")}];
                  ${n}
                }`,a.kernelShape.length===2){let T=r-(m?3:2);f?x=`
                for (var j: u32 = 0u; j < uniforms.kh; j++) {
                  xIndices[${T}] = indices[${T}] * uniforms.sh - uniforms.phStart + j;
                  if (xIndices[${T}] < 0 || xIndices[${T}] >= uniforms.x_shape[${T}]) {
                    pad += i32(uniforms.kw);
                    continue;
                  }
              `:x=`
                for (var j: u32 = 0u; j < uniforms.kh; j++) {
                  xIndices[${T}] = indices[${T}] * uniforms.sh - uniforms.phStart + j;
                `,$=`
              }
            `}return`
            ${e.registerUniforms(l).declareVariables(t,_)}

            ${e.mainStart()}
              ${e.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}

              let indices = ${_.offsetToIndices("global_idx")};
              var xIndices = ${_.offsetToIndices("global_idx")};

              var value = ${y}(${o});
              var pad = 0;
              ${x}
              ${b}
              ${$}
              ${s}

              output[global_idx] = value;
            }`}else{if(m)throw new Error("Pooling with kernelShape.length > 2 is not supported for NHWC format.");let b=a.kernelShape.length,x=a.pads.length,$="";return d?$=`
                if (xIndices[j] >= uniforms.x_shape[j]) {
                  pad++;
                  isPad = true;
                  break;
                }
              }
              if (!isPad) {
                let x_val = x[${t.indicesToOffset("xIndices")}];
                ${n}
              }`:$=`
              }
              let x_val = x[${t.indicesToOffset("xIndices")}];
              ${n}
            `,`
            ${e.registerUniforms(l).declareVariables(t,_)}

            ${e.mainStart()}
              ${e.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
              let indices = ${_.offsetToIndices("global_idx")};
              var xIndices = ${_.offsetToIndices("global_idx")};

              var offsets: array<u32, ${b}>;

              var value = ${y}(${o});
              var pad = 0;
              var isPad = false;

              for (var i: u32 = 0u; i < uniforms.kernelSize; i++) {
                var offset = i;
                for (var j = 0u; j < ${b-1}u; j++) {
                  offsets[j] = offset / ${Q("uniforms.kernelStrides","j",b)};
                  offset -= offsets[j] * ${Q("uniforms.kernelStrides","j",b)};
                }
                offsets[${b-1}] = offset;

                isPad = false;
                for (var j = ${r-b}u; j < ${r}u; j++) {
                  xIndices[j] = indices[j] * ${Q("uniforms.strides",`j - ${r-b}u`,b)}
                    + offsets[j - ${r-b}u] - ${Q("uniforms.pads","j - 2u",x)};
                  ${$}
              }
              ${s}

              output[global_idx] = value;
            }`}},oa=e=>`${e.format};${e.ceilMode};${e.autoPad};${e.kernelShape.length}`,xl=e=>`${oa(e)};${e.countIncludePad}`,Cl=e=>`${oa(e)};${e.storageOrder};${e.dilations}`,ua=e=>({format:e.format,autoPad:["NOTSET","VALID","SAME_UPPER","SAME_LOWER"][e.auto_pad],ceilMode:e.ceil_mode,kernelShape:e.kernel_shape,strides:e.strides,pads:e.pads}),la=(e,t,r,i)=>{let[a,n]=aa(t,i,r),s=M("x",t.dataType,t.dims.length),o=s.type.value,l="value += x_val;",d="";a.countIncludePad?d+=`value /= ${o}(uniforms.kernelSize);`:d+=`value /= ${o}(i32(uniforms.kernelSize) - pad);`;let[c,f,m,y,_]=na(n,a);c.push(...J(t.dims,n));let b=["rank"];return{name:e,shaderCache:{hint:`${i.cacheKey};${m};${y};${_}`,inputDependencies:b},getRunData:()=>({outputs:[{dims:n,dataType:t.dataType}],dispatchGroup:{x:Math.ceil(O.size(n)/64)},programUniforms:c}),getShaderSource:x=>sa(x,s,t.dims.length,n.length,a,l,d,0,f,m,y,_)}},If=e=>{let t=e.count_include_pad!==0,r=ua(e);if(r.ceilMode!==0)throw new Error("using ceil() in shape computation is not yet supported for AveragePool");let i={countIncludePad:t,...r,cacheKey:""};return{...i,cacheKey:xl(i)}},kf=(e,t)=>{ri(e.inputs),e.compute(la("AveragePool",e.inputs[0],!1,t))},da={autoPad:"",ceilMode:0,countIncludePad:!1,kernelShape:[],strides:[],pads:[],storageOrder:0,dilations:[]},Ef=e=>{let t=e.format;return{format:t,...da,cacheKey:t}},zf=(e,t)=>{ri(e.inputs),e.compute(la("GlobalAveragePool",e.inputs[0],!0,t))},pa=(e,t,r,i)=>{let[a,n]=aa(t,i,r),s=`
      value = max(x_val, value);
    `,o="",l=M("x",t.dataType,t.dims.length),d=["rank"],[c,f,m,y,_]=na(n,a);return c.push(...J(t.dims,n)),{name:e,shaderCache:{hint:`${i.cacheKey};${m};${y};${_}`,inputDependencies:d},getRunData:()=>({outputs:[{dims:n,dataType:t.dataType}],dispatchGroup:{x:Math.ceil(O.size(n)/64)},programUniforms:c}),getShaderSource:b=>sa(b,l,t.dims.length,n.length,a,s,o,t.dataType===10?-65504:-1e5,f,m,y,_)}},Af=(e,t)=>{ri(e.inputs),e.compute(pa("MaxPool",e.inputs[0],!1,t))},Of=e=>{let t=e.storage_order,r=e.dilations,i=ua(e);if(t!==0)throw new Error("column major storage order is not yet supported for MaxPool");if(i.ceilMode!==0)throw new Error("using ceil() in shape computation is not yet supported for MaxPool");let a={storageOrder:t,dilations:r,...i,cacheKey:""};return{...a,cacheKey:Cl(a)}},Rf=e=>{let t=e.format;return{format:t,...da,cacheKey:t}},Bf=(e,t)=>{ri(e.inputs),e.compute(pa("GlobalMaxPool",e.inputs[0],!0,t))}}),Tl,Sl,Mf,Df,ay=L(()=>{ie(),se(),xe(),oe(),Tl=(e,t)=>{if(e.length<2||e.length>3)throw new Error("DequantizeLinear requires 2 or 3 inputs.");if(e.length===3&&e[1].dims===e[2].dims)throw new Error("x-scale and x-zero-point must have the same shape.");if(e.length===3&&e[0].dataType!==e[2].dataType)throw new Error("x and x-zero-point must have the same data type.");if(e[0].dataType===6&&e.length>2)throw new Error("In the case of dequantizing int32 there is no zero point.");if(e[1].dims.length!==0&&e[1].dims.length!==1&&e[1].dims.length!==e[0].dims.length)throw new Error("scale input must be a scalar, a 1D tensor, or have the same rank as the input tensor.");if(e.length>2){if(e[0].dataType!==e[2].dataType)throw new Error("x and x-zero-point must have the same data type.");if(e[1].dims.length!==e[2].dims.length)throw new Error("scale and zero-point inputs must have the same rank.");if(!e[1].dims.map((r,i)=>r===e[2].dims[i]).reduce((r,i)=>r&&i,!0))throw new Error("scale and zero-point inputs must have the same shape.")}if(t.blockSize>0){if(e[1].dims.length===0||e[1].dims.length===1&&e[1].dims[0]===1)throw new Error("blockSize must be set only for block quantization.");if(!e[1].dims.map((a,n)=>n===t.axis||a===e[0].dims[n]).reduce((a,n)=>a&&n,!0))throw new Error("For block qunatization, scale input shape to match the input shape except for the axis");if(e[1].dims.length!==e[0].dims.length)throw new Error("For block qunatization the scale input rank must be the same as the x rank.");let r=e[0].dims[t.axis],i=e[1].dims[t.axis];if(t.blockSize<Math.ceil(r/i)||t.blockSize>Math.ceil(r/(i-1)-1))throw new Error("blockSize must be with in the range [ceil(dI / Si), ceil(dI / (Si - 1) - 1)].")}},Sl=(e,t)=>{let r=O.normalizeAxis(t.axis,e[0].dims.length),i=e[0].dataType,a=i===3,n=e[0].dims,s=e[1].dataType,o=O.size(n),l=i===3||i===2,d=l?[Math.ceil(O.size(e[0].dims)/4)]:e[0].dims,c=e[1].dims,f=e.length>2?e[2]:void 0,m=f?l?[Math.ceil(O.size(f.dims)/4)]:f.dims:void 0,y=c.length===0||c.length===1&&c[0]===1,_=y===!1&&c.length===1,b=$e(o),x=y&&(!l||b===4),$=x?b:1,w=x&&!l?b:1,T=M("input",l?12:i,d.length,w),C=M("scale",s,c.length),I=f?M("zero_point",l?12:i,m.length):void 0,z=X("output",s,n.length,$),k=[T,C];I&&k.push(I);let A=[d,c];f&&A.push(m);let D=[{type:12,data:o/$},{type:12,data:r},{type:12,data:t.blockSize},...J(...A,n)],V=G=>{let H=[{name:"output_size",type:"u32"},{name:"axis",type:"u32"},{name:"block_size",type:"u32"}];return`
      ${G.registerUniforms(H).declareVariables(...k,z)}
      ${G.mainStart()}
          ${G.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
          let output_indices = ${z.offsetToIndices("global_idx")};

          // Set input x
          ${l?`
            let input = ${T.getByOffset("global_idx / 4")};
            let x_vec = ${a?"unpack4xI8(input)":"unpack4xU8(input)"};
            let x_value = ${$===1?"x_vec[global_idx % 4]":"x_vec"};`:`let x_value = ${T.getByOffset("global_idx")};`};

          // Set scale input
          ${y?`let scale_value= ${C.getByOffset("0")}`:_?`
            let scale_index = ${z.indicesGet("output_indices","uniforms.axis")};
            let scale_value= ${C.getByOffset("scale_index")};`:`
            var scale_indices: ${C.type.indices} = output_indices;
            let index = ${C.indicesGet("scale_indices","uniforms.axis")} / uniforms.block_size;
            ${C.indicesSet("scale_indices","uniforms.axis","index")};
            let scale_value= ${C.getByIndices("scale_indices")};`};

          // Set zero-point input
          ${I?y?l?`
                let zero_point_input = ${I.getByOffset("0")};
                let zero_point_vec =  ${a?"unpack4xI8(zero_point_input)":"unpack4xU8(zero_point_input)"};
                let zero_point_value= zero_point_vec[0]`:`let zero_point_value = ${I.getByOffset("0")}`:_?l?`
                let zero_point_index = ${z.indicesGet("output_indices","uniforms.axis")};
                let zero_point_input = ${I.getByOffset("zero_point_index / 4")};
                let zero_point_vec =  ${a?"unpack4xI8(zero_point_input)":"unpack4xU8(zero_point_input)"};
                let zero_point_value = zero_point_vec[zero_point_index % 4]`:`
                let zero_point_index = ${z.indicesGet("output_indices","uniforms.axis")};
                let zero_point_value = ${I.getByOffset("zero_point_index")};`:l?`
                let zero_point_offset = ${C.indicesToOffset("scale_indices")};
                let zero_point_input = ${I.getByOffset("zero_point_offset / 4")};
                let zero_point_vec = ${a?"unpack4xI8(zero_point_input)":"unpack4xU8(zero_point_input)"};
                let zero_point_value = zero_point_vec[zero_point_offset % 4];`:`let zero_point_value = ${I.getByIndices("scale_indices")};`:`let zero_point_value = ${l?a?"i32":"u32":T.type.value}(0);`};
      // Compute and write output
      ${z.setByOffset("global_idx",`${z.type.value}(x_value - zero_point_value) * scale_value`)};
      }`};return{name:"DequantizeLinear",shaderCache:{hint:t.cacheKey,inputDependencies:I?["rank","rank","rank"]:["rank","rank"]},getShaderSource:V,getRunData:()=>({outputs:[{dims:n,dataType:s}],dispatchGroup:{x:Math.ceil(o/$/64),y:1,z:1},programUniforms:D})}},Mf=(e,t)=>{Tl(e.inputs,t),e.compute(Sl(e.inputs,t))},Df=e=>he({axis:e.axis,blockSize:e.blockSize})}),Il,kl,Nf,ny=L(()=>{We(),ie(),oe(),Il=(e,t,r)=>{let i=e===t,a=e<t&&r<0,n=e>t&&r>0;if(i||a||n)throw new Error("Range these inputs' contents are invalid.")},kl=(e,t,r,i)=>{let a=Math.abs(Math.ceil((t-e)/r)),n=[a],s=a,o=[{type:12,data:s},{type:i,data:e},{type:i,data:r},...J(n)],l=d=>{let c=X("output",i,n.length),f=c.type.value,m=[{name:"outputSize",type:"u32"},{name:"start",type:f},{name:"delta",type:f}];return`
        ${d.registerUniforms(m).declareVariables(c)}
        ${d.mainStart()}
        ${d.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
        output[global_idx] = uniforms.start + ${f}(global_idx) * uniforms.delta;
      }`};return{name:"Range",shaderCache:{hint:`${i}`},getShaderSource:l,getRunData:()=>({outputs:[{dims:n,dataType:i}],dispatchGroup:{x:Math.ceil(s/64)},programUniforms:o})}},Nf=e=>{let t=0,r=0,i=0;e.inputs[0].dataType===6?(t=e.inputs[0].getInt32Array()[0],r=e.inputs[1].getInt32Array()[0],i=e.inputs[2].getInt32Array()[0]):e.inputs[0].dataType===1&&(t=e.inputs[0].getFloat32Array()[0],r=e.inputs[1].getFloat32Array()[0],i=e.inputs[2].getFloat32Array()[0]),ge.webgpu.validateInputContent&&Il(t,r,i),e.compute(kl(t,r,i,e.inputs[0].dataType),{inputs:[]})}}),El,zl,Pf,Uf,sy=L(()=>{ie(),se(),xe(),oe(),El=(e,t,r,i)=>{if(e!=="none"&&i!=="i32"&&i!=="u32"&&i!=="f32")throw new Error(`Input ${i} is not supported with reduction ${e}.`);let a=`{
                var oldValue = 0;
                loop {
                  let newValueF32 =`,n=`;
                  let newValue = bitcast<i32>(newValueF32);
                  let res = atomicCompareExchangeWeak(&${t}, oldValue, newValue);
                  if res.exchanged {
                    break;
                  }
                  oldValue = res.old_value;
                }
              }`;switch(e){case"none":return`${t}=${r};`;case"add":return i==="i32"||i==="u32"?`atomicAdd(&${t}, bitcast<${i}>(${r}));`:`
              ${a}bitcast<${i}>(oldValue) + (${r})${n}`;case"max":return i==="i32"||i==="u32"?`atomicMax(&${t}, bitcast<${i}>(${r}));`:`
                ${a}max(bitcast<f32>(oldValue), (${r}))${n}`;case"min":return i==="i32"||i==="u32"?`atomicMin(&${t}, bitcast<${i}>(${r}));`:`${a}min(bitcast<${i}>(oldValue), (${r}))${n}`;case"mul":return`${a}(bitcast<${i}>(oldValue) * (${r}))${n}`;default:throw new Error(`Reduction ${e} is not supported.`)}},zl=(e,t)=>{let r=e[0].dims,i=e[1].dims,a=r,n=1,s=Math.ceil(O.sizeToDimension(i,i.length-1)/n),o=i[i.length-1],l=O.sizeFromDimension(r,o),d=[{type:12,data:s},{type:12,data:o},{type:12,data:l},...J(e[1].dims,e[2].dims,a)],c=f=>{let m=M("indices",e[1].dataType,e[1].dims.length),y=M("updates",e[2].dataType,e[2].dims.length,n),_=t.reduction!=="none"&&t.reduction!==""?cp("output",e[0].dataType,a.length):X("output",e[0].dataType,a.length,n);return`
      ${f.registerUniform("output_size","u32").registerUniform("last_index_dimension","u32").registerUniform("num_updates_elements","u32").declareVariables(m,y,_)}
      ${f.mainStart()}
        ${f.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
  var data_offset = 0u;
  let indices_start = uniforms.last_index_dimension * global_idx;
  let indices_end = indices_start + uniforms.last_index_dimension;
  for (var i = indices_start; i < indices_end; i++) {
    var index = i32(indices[i].x);
    ${e[0].dims.length===1?`
    let element_count_dim = uniforms.output_strides;
    let dim_value = uniforms.output_shape;`:`
    let element_count_dim = uniforms.output_strides[i - indices_start];
    let dim_value = uniforms.output_shape[i - indices_start];`}
    if (index >= 0) {
      if (index >= i32(dim_value)) {
        index = i32(dim_value - 1);
      }
    } else {
      if (index < -i32(dim_value)) {
        index = 0;
      } else {
        index += i32(dim_value);
      }
    }
    data_offset += u32((u32(index) * element_count_dim));
  }

  for (var i = 0u; i < uniforms.num_updates_elements; i++) {
    let value = updates[uniforms.num_updates_elements * global_idx + i];
    ${El(t.reduction,"output[data_offset + i]","value",_.type.value)}
  }

      }`};return{name:"ScatterND",shaderCache:{hint:`${t.cacheKey}_${t.reduction}`,inputDependencies:["rank","rank"]},getRunData:()=>({outputs:[{dims:a,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(s/64)},programUniforms:d}),getShaderSource:c}},Pf=e=>he({reduction:e.reduction}),Uf=(e,t)=>{e.compute(zl(e.inputs,t),{inputs:[e.inputs[1],e.inputs[2]],outputs:[]})}}),Al,Ol,Rl,ca,Bl,Ml,Dl,Nl,Pl,Ul,Wl,Ll,fa,ql,Vl,jl,Fl,Gl,Wf,Lf,oy=L(()=>{ie(),se(),xe(),oe(),Al=(e,t)=>{if(e.every(r=>r>0||(()=>{throw new Error("Resize requires scales input values to be positive")})),e.length>0){if(t.mode==="linear"){if(!(e.length===2||e.length===3||e.length===4&&e[0]===1&&e[1]===1||e.length===4&&e[0]===1&&e[3]===1||e.length===5&&e[0]===1&&e[1]===1))throw new Error(`For linear mode, Resize requires scales to be 2D, 3D, 4D with either two outermost or one innermost and
            one outermost scale values equal to 1, or 5D with two outermost scale values equal to 1`)}else if(t.mode==="cubic"&&!(e.length===2||e.length===4&&e[0]===1&&e[1]===1||e.length===4&&e[0]===1&&e[3]===1))throw new Error("Resize requires scales input size to be 2 or 4 for cubic mode")}},Ol=(e,t,r)=>{t.every(a=>a>=0&&a<r||(()=>{throw new Error("Resize requires axes input values to be positive and less than rank")}));let i=new Array(r).fill(1);return t.forEach((a,n)=>i[a]=e[n]),i},Rl=(e,t,r,i,a,n)=>{let[s,o,l]=r>10?[1,2,3]:[-1,e.length>1?1:-1,-1],d=e[0].dims.length;if(s>0&&e.length>s&&e[s].dims.length>0)e[s].getFloat32Array().forEach(c=>n.push(c));else if(t.coordinateTransformMode==="tf_crop_and_resize")throw new Error("Resize requires RoI input to be specified when coordinateTransformMode is tfCropAndResize");if(o>0&&e.length>o&&e[o].dims.length===1&&e[o].dims[0]>0){if(e[o].getFloat32Array().forEach(c=>i.push(c)),i.length!==0&&i.length!==d&&r>=18&&i.length!==t.axes.length)throw new Error("Resize requires scales input size to be same as input rank or axes size for opset 18 and up");Al(i,t),t.axes.length>0&&Ol(i,t.axes,d).forEach((c,f)=>i[f]=c)}if(l>0&&e.length>l&&e[l].dims.length===1&&e[l].dims[0]>0&&(e[l].getBigInt64Array().forEach(c=>a.push(Number(c))),a.length!==0&&a.length!==d&&r>=18&&a.length!==t.axes.length))throw new Error("Resize requires sizes input size to be same as input rank or axes size for opset 18 and up");if(t.axes.length>0){if(i.length!==0&&i.length!==t.axes.length)throw new Error('Resize requires "scales" input size to be of axes rank when axes attributes is specified');if(a.length!==0&&a.length!==t.axes.length)throw new Error('Resize requires "sizes" input size to be of rank axes rank when axes attributes is specified')}if(typeof i<"u"&&typeof a<"u"&&i.length>0&&a.length>d)throw new Error("Resize requires only of scales or sizes to be specified")},ca=(e,t,r,i)=>`
  // The whole part and the fractional part are calculated separately due to inaccuracy of floating
  // point division. As an example, f32(21) / f32(7) may evaluate to 2.99... instead of 3, causing an
  // offset-by-one error later in floor().
  let big = (${e}) * (${t});
  let whole = ${i}(big / (${r}));
  let fract = ${i}(big % (${r})) / ${i}(${r});
  return whole + fract;
`,Bl=(e,t)=>`fn getOriginalCoordinateFromResizedCoordinate(xResized: u32, xScale: f32, lengthResized: u32,
     lengthOriginal: u32, roiStart: f32, roiEnd: f32) -> ${t} { `+(()=>{switch(e){case"asymmetric":return`
          if (xScale < 1.0 || floor(xScale) != xScale) {
            return ${t}(xResized) / ${t}(xScale);
          } else {
            ${ca("xResized","lengthOriginal","lengthResized",t)}
          }
        `;case"pytorch_half_pixel":return`if (lengthResized > 1) {
                    return (${t}(xResized) + 0.5) / ${t}(xScale) - 0.5;
                  } else {
                    return 0.0;
                  }`;case"tf_half_pixel_for_nn":return`return (${t}(xResized) + 0.5) / ${t}(xScale);`;case"align_corners":return`if (lengthResized == 1) {
                    return 0.0;
                  } else {
                    ${ca("xResized","lengthOriginal - 1","lengthResized - 1",t)}
                  }`;case"tf_crop_and_resize":return`if (lengthResized > 1) {
                    return ${t}(roiStart) * ${t}(lengthOriginal - 1) +
                        (${t}(xResized) * ${t}(roiEnd - roiStart) * ${t}(lengthOriginal - 1)) /
                        ${t}(lengthResized - 1);
                  } else {
                    return 0.5 * ${t}(roiStart + roiEnd) * ${t}(lengthOriginal - 1);
                  }`;case"half_pixel_symmetric":return`const outputWidth = ${t}xScale * ${t}(lengthResized);
                  const adjustment = ${t}(lengthResized) / outputWidth;
                  const center = ${t}(lengthOriginal) / 2;
                  const offset = center * (1 - adjustment);
                  return offset + ((${t}(xResized) + 0.5) / ${t}(xScale)) - 0.5;`;case"half_pixel":return`return ((${t}(xResized) + 0.5) / ${t}(xScale)) - 0.5;`;default:throw new Error(`Coordinate transform mode ${e} is not supported`)}})()+"}",Ml=(e,t,r)=>`fn getNearestPixelFromOriginal(xOriginal: ${r}, isDownSample: bool) -> ${r} {`+(()=>{switch(e){case"round_prefer_ceil":return"if (fract(xOriginal) == 0.5) {             return ceil(xOriginal);           } else {             return round(xOriginal);           }";case"floor":return"return floor(xOriginal);";case"ceil":return"return ceil(xOriginal);";case"round_prefer_floor":return"if (fract(xOriginal) == 0.5) {                     return floor(xOriginal);                   } else {                     return round(xOriginal);                   }";case"simple":default:if(t<11)return"if (isDownSample)                     {                       return ceil(xOriginal);                     } else {                       return xOriginal;                     }";throw new Error(`Nearest mode ${e} is not supported`)}})()+"}",Dl=(e,t,r)=>{let i=new Array(r).fill(0).concat(new Array(r).fill(1)),a=e.length===0?i:e.slice();return t.length>0?(t.forEach((n,s)=>{i[n]=a[s],i[s+r]=a[t.length+s]}),i):a},Nl=(e,t,r,i)=>{let a=[];if(r.length>0)if(i.length>0){if(e.forEach(n=>a.push(n)),Math.max(...i)>e.length)throw new Error("axes is out of bound");i.forEach((n,s)=>a[n]=r[s])}else r.forEach(n=>a.push(n));else{if(t.length===0)throw new Error("Resize requires either scales or sizes.");a=e.map((n,s)=>Math.round(n*t[s]))}return a},Pl=(e,t,r)=>{let i=(()=>{switch(r.keepAspectRatioPolicy){case"not_larger":return r.axes.length>0?Math.min(...r.axes.map(n=>t[n]),Number.MAX_VALUE):Math.min(...t,Number.MAX_VALUE);case"not_smaller":return r.axes.length>0?Math.max(...r.axes.map(n=>t[n]),Number.MIN_VALUE):Math.max(...t,Number.MIN_VALUE);default:throw new Error(`Keep aspect ratio policy ${r.keepAspectRatioPolicy} is not supported`)}})();t.fill(1,0,t.length);let a=e.slice();return r.axes.length>0?(r.axes.forEach(n=>t[n]=i),r.axes.forEach(n=>a[n]=Math.round(e[n]*t[n]))):(t.fill(i,0,t.length),a.forEach((n,s)=>a[s]=Math.round(n*t[s]))),a},Ul=(e,t,r,i,a)=>`
    fn calculateOriginalIndicesFromOutputIndices(output_indices: ${e.type.indices}) -> array<${e.type.value}, ${r.length}> {
      var original_indices: array<${e.type.value}, ${r.length}>;
      for (var i:u32 = 0; i < ${r.length}; i++) {
        var output_index = ${e.indicesGet("output_indices","i")};
        var scale = ${Q("uniforms.scales","i",i)};
        var roi_low = ${Q("uniforms.roi","i",a)};
        var roi_hi = ${Q("uniforms.roi",`i + ${t.length}`,a)};
        if (scale == 1.0) {
          original_indices[i] = ${e.type.value}(output_index);
        } else {
          var input_shape_i = ${Q("uniforms.input_shape","i",t.length)};
          var output_shape_i = ${Q("uniforms.output_shape","i",r.length)};
          original_indices[i] = getOriginalCoordinateFromResizedCoordinate(output_index, scale, output_shape_i,
                                                                           input_shape_i, roi_low, roi_hi);
        }
      }
      return original_indices;
    }`,Wl=(e,t,r,i,a,n,s)=>`
    fn calculateInputIndicesFromOutputIndices(output_indices: ${t.type.indices}) -> ${e.type.indices} {
      var input_indices: ${e.type.indices};
      for (var i:u32 = 0; i < ${i.length}; i++) {
        var output_index = ${t.indicesGet("output_indices","i")};
        var input_index: u32;
        var scale = ${Q("uniforms.scales","i",a)};
        if (scale == 1.0) {
          input_index = output_index;
        } else {
          var roi_low = ${Q("uniforms.roi","i",n)};
          var roi_hi = ${Q("uniforms.roi",`i + ${r.length}`,n)};
          var input_shape_i = ${Q("uniforms.input_shape","i",r.length)};
          var output_shape_i = ${Q("uniforms.output_shape","i",i.length)};
          var original_idx = getOriginalCoordinateFromResizedCoordinate(output_index, scale, output_shape_i,
                                                                        input_shape_i, roi_low, roi_hi);
          if (!${s} || (original_idx >= 0 && original_idx < ${t.type.value}(input_shape_i))) {
            if (original_idx < 0) {
              input_index = 0;
            } else if (original_idx > ${t.type.value}(input_shape_i - 1)) {
              input_index = input_shape_i - 1;
            } else {
              input_index = u32(getNearestPixelFromOriginal(original_idx, scale < 1));
            }
          } else {
            input_index = u32(original_idx);
          }
        }
        ${e.indicesSet("input_indices","i","input_index")}
      }
      return input_indices;
    }`,Ll=(e,t)=>`
    fn checkInputIndices(input_indices: ${e.type.indices}) -> bool {
      for (var i:u32 = 0; i < ${t.length}; i++) {
        var input_index = ${e.indicesGet("input_indices","i")};
        if (input_index < 0 || input_index >= ${Q("uniforms.input_shape","i",t.length)}) {
          return false;
        }
      }
      return true;
    }`,fa=(e,t,r,i)=>e.rank>i?`
    ${e.indicesSet("input_indices",t,"channel")};
    ${e.indicesSet("input_indices",r,"batch")};
`:"",ql=(e,t,r,i,a)=>{let[n,s,o,l]=r.length===2?[-1,0,1,-1]:[0,2,3,1],d=e.type.value;return`
    fn getInputValue(batch: u32, channel: u32, row: u32, col: u32) -> ${d} {
      var input_indices: ${e.type.indices};
      ${e.indicesSet("input_indices",s,`max(0, min(row, ${r[s]} - 1))`)};
      ${e.indicesSet("input_indices",o,`max(0, min(col, ${r[o]} - 1))`)};
      ${fa(e,l,n,2)}
      return ${e.getByIndices("input_indices")};
    }

    fn bilinearInterpolation(output_indices: ${t.type.indices}) -> ${d} {
      var originalIndices = calculateOriginalIndicesFromOutputIndices(output_indices);
      var row:${d} = originalIndices[${s}];
      var col:${d} = originalIndices[${o}];
      ${i?`if (row < 0 || row > (${r[s]} - 1) || col < 0 || col > (${r[o]} - 1)) {
        return ${a};
      }`:""};
      row = max(0, min(row, ${r[s]} - 1));
      col = max(0, min(col, ${r[o]} - 1));
      var row1: u32 = u32(row);
      var col1: u32 = u32(col);
      var row2: u32 = u32(row + 1);
      var col2: u32 = u32(col + 1);
      var channel: u32 = ${r.length>2?`u32(originalIndices[${l}])`:"0"};
      var batch: u32 =  ${r.length>2?`u32(originalIndices[${n}])`:"0"};
      var x11: ${d} = getInputValue(batch, channel, row1, col1);
      var x12: ${d} = getInputValue(batch, channel, row1, col2);
      var x21: ${d} = getInputValue(batch, channel, row2, col1);
      var x22: ${d} = getInputValue(batch, channel, row2, col2);
      var dx1: ${d} = abs(row - ${d}(row1));
      var dx2: ${d} = abs(${d}(row2) - row);
      var dy1: ${d} = abs(col - ${d}(col1));
      var dy2: ${d} = abs(${d}(col2) - col);
      if (row1 == row2) {
        dx1 = 0.5;
        dx2 = 0.5;
      }
      if (col1 == col2) {
        dy1 = 0.5;
        dy2 = 0.5;
      }
      return (x11 * dx2 * dy2 + x12 * dx2 * dy1 + x21 * dx1 * dy2 + x22 * dx1 * dy1);
    }`},Vl=(e,t,r,i,a,n,s,o,l,d)=>{let c=r.length===2,[f,m]=c?[0,1]:[2,3],y=e.type.value,_=b=>{let x=b===f?"row":"col";return`
      fn ${x}CubicInterpolation(input_indices: ${e.type.indices}, output_indices: ${t.type.indices}) -> ${y} {
        var output_index = ${t.indicesGet("output_indices",b)};
        var originalIdx: ${y} = getOriginalCoordinateFromResizedCoordinate(output_index, ${a[b]},
        ${i[b]}, ${r[b]}, ${n[b]}, ${n[b]} + ${r.length});
        var fractOriginalIdx: ${y} = originalIdx - floor(originalIdx);
        var coefs = getCubicInterpolationCoefs(fractOriginalIdx);

        if (${o} && (originalIdx < 0 || originalIdx > (${r[b]} - 1))) {
          return ${l};
        }
        var data: array<${y}, 4> = array<${y}, 4>(0.0, 0.0, 0.0, 0.0);
        for (var i: i32 = -1; i < 3; i++) {
          var ${x}: ${y} = originalIdx + ${y}(i);
          if (${x} < 0 || ${x} >= ${r[b]}) {
            ${d?`coefs[i + 1] = 0.0;
                        continue;`:o?`return ${l};`:`${x} = max(0, min(${x}, ${r[b]} - 1));`};
          }
        var input_indices_copy: ${e.type.indices} = input_indices;
          ${e.indicesSet("input_indices_copy",b,`u32(${x})`)};
          data[i + 1] = ${b===f?e.getByIndices("input_indices_copy"):"rowCubicInterpolation(input_indices_copy, output_indices)"};
        }
        return cubicInterpolation1D(data, coefs);
      }`};return`
    ${_(f)};
    ${_(m)};
  fn getCubicInterpolationCoefs(s: ${y}) -> array<${y}, 4> {
    var absS = abs(s);
    var coeffs: array<${y}, 4> = array<${y}, 4>(0.0, 0.0, 0.0, 0.0);
    var oneMinusAbsS: ${y} = 1.0 - absS;
    var twoMinusAbsS: ${y} = 2.0 - absS;
    var onePlusAbsS: ${y} = 1.0 + absS;
    coeffs[0] = ((${s} * onePlusAbsS - 5 * ${s}) * onePlusAbsS + 8 * ${s}) * onePlusAbsS - 4 * ${s};
    coeffs[1] = ((${s} + 2) * absS - (${s} + 3)) * absS * absS + 1;
    coeffs[2] = ((${s} + 2) * oneMinusAbsS - (${s} + 3)) * oneMinusAbsS * oneMinusAbsS + 1;
    coeffs[3] = ((${s} * twoMinusAbsS - 5 * ${s}) * twoMinusAbsS + 8 * ${s}) * twoMinusAbsS - 4 * ${s};
    return coeffs;
  }

  fn cubicInterpolation1D(x: array<${y}, 4>, coefs: array<${y}, 4>) -> ${y} {
    var coefsSum: ${y} = coefs[0] + coefs[1] + coefs[2] + coefs[3];
    return (x[0] * coefs[0] + x[1] * coefs[1]+ x[2] * coefs[2]+ x[3] * coefs[3]) / coefsSum;
  }

  fn bicubicInterpolation(output_indices: ${t.type.indices}) -> ${y} {
    var input_indices: ${e.type.indices} = output_indices;
    return colCubicInterpolation(input_indices, output_indices);
  }
    `},jl=(e,t,r,i,a)=>{let[n,s,o,l,d]=r.length===3?[-1,0,1,2,-1]:[0,2,3,4,1],c=e.type.value;return`
    fn getInputValue(batch: u32, channel: u32, depth:u32, height: u32, width: u32) -> ${c} {
      var input_indices: ${e.type.indices};
      ${e.indicesSet("input_indices",s,`max(0, min(depth, ${r[s]} - 1))`)};
      ${e.indicesSet("input_indices",o,`max(0, min(height, ${r[o]} - 1))`)};
      ${e.indicesSet("input_indices",l,`max(0, min(width, ${r[l]} - 1))`)};
      ${fa(e,d,n,3)}
      return ${e.getByIndices("input_indices")};
    }

    fn trilinearInterpolation(output_indices: ${t.type.indices}) -> ${c} {
      var originalIndices = calculateOriginalIndicesFromOutputIndices(output_indices);
      var depth:${c} = originalIndices[${s}];
      var height:${c} = originalIndices[${o}];
      var width:${c} = originalIndices[${l}];
      ${i?`if (depth < 0 || depth > (${r[s]} - 1) || height < 0 || height > (${r[o]} - 1) || width < 0 || (width > ${r[l]} - 1)) {
      return ${a};
        }`:""};

    depth = max(0, min(depth, ${r[s]} - 1));
      height = max(0, min(height, ${r[o]} - 1));
      width = max(0, min(width, ${r[l]} - 1));
      var depth1: u32 = u32(depth);
      var height1: u32 = u32(height);
      var width1: u32 = u32(width);
      var depth2: u32 = u32(depth + 1);
      var height2: u32 = u32(height + 1);
      var width2: u32 = u32(width + 1);
      var channel: u32 = ${r.length>3?`u32(originalIndices[${d}])`:"0"};
      var batch: u32 =  ${r.length>3?`u32(originalIndices[${n}])`:"0"};

      var x111: ${c} = getInputValue(batch, channel, depth1, height1, width1);
      var x112: ${c} = getInputValue(batch, channel, depth1, height1, width2);
      var x121: ${c} = getInputValue(batch, channel, depth1, height2, width1);
      var x122: ${c} = getInputValue(batch, channel, depth1, height2, width2);
      var x211: ${c} = getInputValue(batch, channel, depth2, height1, width1);
      var x212: ${c} = getInputValue(batch, channel, depth2, height1, width2);
      var x221: ${c} = getInputValue(batch, channel, depth2, height2, width1);
      var x222: ${c} = getInputValue(batch, channel, depth2, height2, width2);
      var dx1: ${c} = abs(depth - ${c}(depth1));
      var dx2: ${c} = abs(${c}(depth2) - depth);
      var dy1: ${c} = abs(height - ${c}(height1));
      var dy2: ${c} = abs(${c}(height2) - height);
      var dz1: ${c} = abs(width - ${c}(width1));
      var dz2: ${c} = abs(${c}(width2) - width);
      if (depth1 == depth2) {
        dx1 = 0.5;
        dx2 = 0.5;
      }
      if (height1 == height2) {
        dy1 = 0.5;
        dy2 = 0.5;
      }
      if (width1 == width2) {
        dz1 = 0.5;
        dz2 = 0.5;
      }
      return (x111 * dx2 * dy2 * dz2 + x112 * dx2 * dy2 * dz1 + x121 * dx2 * dy1 *dz2 + x122 * dx2 * dy1 * dz1 +
              x211 * dx1 * dy2 * dz2 + x212 * dx1 * dy2 * dz1 + x221 * dx1 * dy1 *dz2 + x222 * dx1 * dy1 * dz1);
    }`},Fl=(e,t,r,i,a,n)=>{let s=e.dims,o=Dl(n,t.axes,s.length),l=Nl(s,i,a,t.axes),d=i.slice();i.length===0&&(d=s.map((w,T)=>w===0?1:l[T]/w),t.keepAspectRatioPolicy!=="stretch"&&(l=Pl(s,d,t)));let c=X("output",e.dataType,l.length),f=M("input",e.dataType,s.length),m=O.size(l),y=s.length===l.length&&s.every((w,T)=>w===l[T]),_=t.coordinateTransformMode==="tf_crop_and_resize",b=t.extrapolationValue,x=f.type.value,$=w=>`
      ${y?"":`
      ${Bl(t.coordinateTransformMode,x)};
      ${(()=>{switch(t.mode){case"nearest":return`
              ${Ll(f,s)};
              ${Ml(t.nearestMode,r,x)};
              ${Wl(f,c,s,l,d.length,o.length,_)};
              `;case"linear":return`
              ${Ul(c,s,l,d.length,o.length)};
              ${(()=>{if(s.length===2||s.length===4)return`${ql(f,c,s,_,b)}`;if(s.length===3||s.length===5)return`${jl(f,c,s,_,b)}`;throw Error("Linear mode only supports input dims 2, 3, 4 and 5 are supported in linear mode.")})()};
            `;case"cubic":return`
            ${(()=>{if(s.length===2||s.length===4)return`${Vl(f,c,s,l,d,o,t.cubicCoeffA,_,t.extrapolationValue,t.excludeOutside)}`;throw Error("Cubic mode only supports input dims 2 and 4 are supported in linear mode.")})()};
            `;default:throw Error("Invalid resize mode")}})()};
      `}
      ${w.registerUniform("output_size","u32").registerUniform("scales","f32",d.length).registerUniform("roi","f32",o.length).declareVariables(f,c)}
      ${w.mainStart()}
        ${w.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
        ${y?"output[global_idx] = input[global_idx];":`
        let output_indices = ${c.offsetToIndices("global_idx")};
        var input_indices: ${f.type.indices};
        ${(()=>{switch(t.mode){case"nearest":return`input_indices = calculateInputIndicesFromOutputIndices(output_indices);
                if (checkInputIndices(input_indices)) {
                  output[global_idx] = ${f.getByIndices("input_indices")};
                } else {
                  output[global_idx] = ${t.extrapolationValue};
                }`;case"linear":return`output[global_idx] = ${s.length===2||s.length===4?"bilinearInterpolation":"trilinearInterpolation"}(output_indices);`;case"cubic":return"output[global_idx] = bicubicInterpolation(output_indices);";default:throw Error(`Unsupported resize mode: ${t.mode}`)}})()};
`}
      }`;return{name:"Resize",shaderCache:{hint:`${t.cacheKey}|${r}|${d.length>0?t.mode==="cubic"?d:d.length:""}|${a.length>0?a:""}|${o.length>0?o:""}|${y}|${t.mode==="nearest"?s.length:s}`,inputDependencies:["rank"]},getShaderSource:$,getRunData:()=>({outputs:[{dims:l,dataType:e.dataType}],dispatchGroup:{x:Math.ceil(m/64)},programUniforms:[{type:12,data:m},{type:1,data:d},{type:1,data:o},...J(s,l)]})}},Gl=e=>{let t=e.customDataBuffer;return new Uint32Array(t,t.byteOffset,1)[0]},Wf=(e,t)=>{let r=[],i=[],a=[],n=Gl(e);if(t.antialias!==0)throw Error("Only default value (0) for Antialias attribute is supported");Rl(e.inputs,t,n,r,i,a),e.compute(Fl(e.inputs[0],t,n,r,i,a),{inputs:[0]})},Lf=e=>{let t=e.antialias,r=e.axes,i=e.coordinateTransformMode,a=e.cubicCoeffA,n=e.excludeOutside!==0,s=e.extrapolationValue,o=e.keepAspectRatioPolicy,l=e.mode,d=e.nearestMode===""?"simple":e.nearestMode;return he({antialias:t,axes:r,coordinateTransformMode:i,cubicCoeffA:a,excludeOutside:n,extrapolationValue:s,keepAspectRatioPolicy:o,mode:l,nearestMode:d})}}),Hl,Kl,qf,uy=L(()=>{ie(),se(),oe(),Hl=e=>{if(!e||e.length<3)throw new Error("layerNorm requires at least 3 inputs.");let t=e[0],r=e[1],i=e[2];if(t.dataType!==r.dataType||t.dataType!==i.dataType)throw new Error("All inputs must have the same data type");if(t.dims.length!==3&&t.dims.length!==2)throw new Error("Input must be 2D or 3D");if(r.dims.length!==3&&r.dims.length!==2)throw new Error("Skip must be 2D or 3D");let a=t.dims[t.dims.length-1],n=t.dims[t.dims.length-2];if(r.dims[r.dims.length-1]!==a)throw new Error("Skip must have the same hidden size as input");if(r.dims[r.dims.length-2]!==n)throw new Error("Skip must have the same sequence length as input");if(i.dims.length!==1)throw new Error("Gamma must be 1D");if(i.dims[i.dims.length-1]!==a)throw new Error("Gamma must have the same hidden size as input");if(e.length>3){let s=e[3];if(s.dims.length!==1)throw new Error("Beta must be 1D");if(s.dims[s.dims.length-1]!==a)throw new Error("Beta must have the same hidden size as input")}if(e.length>4){let s=e[4];if(s.dims.length!==1)throw new Error("Bias must be 1D");if(s.dims[s.dims.length-1]!==a)throw new Error("Bias must have the same hidden size as input")}},Kl=(e,t,r,i)=>{let a=t.simplified,n=e[0].dims,s=O.size(n),o=n,l=s,d=n.slice(-1)[0],c=i?n.slice(0,-1).concat(1):[],f=!a&&e.length>3,m=e.length>4,y=i&&r>1,_=i&&r>2,b=r>3,x=64,$=$e(d),w=[{type:12,data:l},{type:12,data:$},{type:12,data:d},{type:1,data:t.epsilon}],T=I=>{let z=[{name:"output_size",type:"u32"},{name:"components",type:"u32"},{name:"hidden_size",type:"u32"},{name:"epsilon",type:"f32"}],k=[M("x",e[0].dataType,e[0].dims,$),M("skip",e[1].dataType,e[1].dims,$),M("gamma",e[2].dataType,e[2].dims,$)];f&&k.push(M("beta",e[3].dataType,e[3].dims,$)),m&&k.push(M("bias",e[4].dataType,e[4].dims,$)),k.push(X("output",e[0].dataType,o,$)),y&&k.push(X("mean_output",1,c)),_&&k.push(X("inv_std_output",1,c)),b&&k.push(X("input_skip_bias_sum",e[0].dataType,o,$));let A=Ie(e[0].dataType),D=Ie(1,$);return`

      ${I.registerUniforms(z).declareVariables(...k)}
      var<workgroup> sum_shared : array<${D}, ${x}>;
      var<workgroup> sum_squared_shared : array<${D}, ${x}>;

      ${I.mainStart([x,1,1])}
        let ix = local_id.x;
        let iy = global_id.x / ${x};

        let hidden_size_vectorized: u32 = uniforms.hidden_size / uniforms.components;
        var stride = hidden_size_vectorized / ${x};
        let offset = ix * stride + iy * hidden_size_vectorized;
        let offset1d = stride * ix;
        if (ix == ${x-1}) {
          stride = hidden_size_vectorized - stride * ix;
        }
        for (var i: u32 = 0; i < stride; i++) {
          let skip_value = skip[offset + i];
          let bias_value = ${m?"bias[offset1d + i]":A+"(0.0)"};
          let input_value = x[offset + i];
          let value = input_value + skip_value + bias_value;
          ${b?"input_skip_bias_sum[offset + i] = value;":""}
          output[offset + i] = value;
          let f32_value = ${Pt(A,$,"value")};
          sum_shared[ix] += f32_value;
          sum_squared_shared[ix] += f32_value * f32_value;
        }
        workgroupBarrier();

        var reduce_size : u32 = ${x};
        for (var curr_size = reduce_size >> 1;  curr_size > 0; curr_size = reduce_size >> 1) {
          reduce_size = curr_size + (reduce_size & 1);
          if (ix < curr_size) {
            sum_shared[ix] += sum_shared[ix + reduce_size];
            sum_squared_shared[ix] += sum_squared_shared[ix + reduce_size];
          }
          workgroupBarrier();
        }

        let sum = sum_shared[0];
        let square_sum = sum_squared_shared[0];
        let mean = ${yt("sum",$)} / f32(uniforms.hidden_size);
        let inv_std_dev = inverseSqrt(${yt("square_sum",$)} / f32(uniforms.hidden_size) ${a?"":"- mean * mean"} + uniforms.epsilon);
        ${y?"mean_output[global_idx] = mean;":""}
        ${_?"inv_std_output[global_idx] = inv_std_dev;":""}

        for (var i: u32 = 0; i < stride; i++) {
          output[offset + i] = (output[offset + i] ${a?"":`- ${A}(mean)`}) *
            ${A}(inv_std_dev) * gamma[offset1d + i]
            ${f?"+ beta[offset1d + i]":""};
        }
      }`},C=[{dims:o,dataType:e[0].dataType}];return r>1&&C.push({dims:c,dataType:1}),r>2&&C.push({dims:c,dataType:1}),r>3&&C.push({dims:n,dataType:e[0].dataType}),{name:"SkipLayerNormalization",shaderCache:{hint:`${$};${y};${_};${b}`,inputDependencies:e.map((I,z)=>"type")},getShaderSource:T,getRunData:()=>({outputs:C,dispatchGroup:{x:Math.ceil(l/d)},programUniforms:w})}},qf=(e,t)=>{Hl(e.inputs);let r=[0];e.outputCount>1&&r.push(-3),e.outputCount>2&&r.push(-3),e.outputCount>3&&r.push(3),e.compute(Kl(e.inputs,t,e.outputCount,!1),{outputs:r})}}),Yl,ai,Zl,ha,Xl,Ql,Vf,jf,ly=L(()=>{ie(),se(),xe(),oe(),Yl=(e,t)=>{if(!e||e.length<1)throw new Error("too few inputs");if(t.axes.length!==0){if(t.axes.length!==t.starts.length||t.axes.length!==t.ends.length)throw new Error("axes, starts and ends must have the same length")}else if(t.starts.length!==t.ends.length)throw new Error("starts and ends must have the same length");e.slice(1).forEach((r,i)=>{if(e[i+1].dataType!==6&&e[i+1].dataType!==7)throw new Error(`Input ${i} must be an array of int32 or int64`)})},ai=(e,t)=>{let r=[];if(e.length>t)if(e[t].dataType===7)e[t].getBigInt64Array().forEach(i=>r.push(Number(i)));else if(e[t].dataType===6)e[t].getInt32Array().forEach(i=>r.push(Number(i)));else throw new Error(`Input ${t} must be an array of int32 or int64`);return r},Zl=(e,t)=>{if(e.length>1){let r=ai(e,1),i=ai(e,2),a=ai(e,3);return a.length===0&&(a=[...Array(e[0].dims.length).keys()]),he({starts:r,ends:i,axes:a})}else return t},ha=(e,t,r,i,a)=>{let n=e;return e<0&&(n+=r[i[t]]),a[t]<0?Math.max(0,Math.min(n,r[i[t]]-1)):Math.max(0,Math.min(n,r[i[t]]))},Xl=(e,t,r)=>`fn calculateInputIndices(output_indices: ${t.type.indices}) -> ${e.type.indices} {
          var input_indices: ${e.type.indices};
          var carry = 0u;
          for (var i = ${r.length-1}; i >= 0; i--) {
            let input_shape_i = ${Q("uniforms.input_shape","i",r.length)};
            let steps_i = ${Q("uniforms.steps","i",r.length)};
            let signs_i = ${Q("uniforms.signs","i",r.length)};
            let starts_i = ${Q("uniforms.starts","i",r.length)};
            var output_index = ${t.indicesGet("output_indices","i")};
            var input_index = output_index * steps_i + starts_i + carry;
            carry = input_index / input_shape_i;
            input_index = input_index % input_shape_i;
            if (signs_i < 0) {
              input_index = input_shape_i - input_index - 1u + starts_i;
            }
            ${e.indicesSet("input_indices","i","input_index")};
          }
          return input_indices;
      }`,Ql=(e,t)=>{let r=e[0].dims,i=O.size(r),a=t.axes.length>0?O.normalizeAxes(t.axes,r.length):[...Array(r.length).keys()],n=ai(e,4);n.forEach($=>$!==0||(()=>{throw new Error("step cannot be 0")})),n.length===0&&(n=Array(a.length).fill(1));let s=t.starts.map(($,w)=>ha($,w,r,a,n)),o=t.ends.map(($,w)=>ha($,w,r,a,n));if(a.length!==s.length||a.length!==o.length)throw new Error("start, ends and axes should have the same number of elements");if(a.length!==r.length)for(let $=0;$<r.length;++$)a.includes($)||(s.splice($,0,0),o.splice($,0,r[$]),n.splice($,0,1));let l=n.map($=>Math.sign($));n.forEach(($,w,T)=>{if($<0){let C=(o[w]-s[w])/$,I=s[w],z=I+C*n[w];s[w]=z,o[w]=I,T[w]=-$}});let d=r.slice(0);a.forEach(($,w)=>{d[$]=Math.ceil((o[$]-s[$])/n[$])});let c={dims:d,dataType:e[0].dataType},f=X("output",e[0].dataType,d.length),m=M("input",e[0].dataType,e[0].dims.length),y=O.size(d),_=[{name:"outputSize",type:"u32"},{name:"starts",type:"u32",length:s.length},{name:"signs",type:"i32",length:l.length},{name:"steps",type:"u32",length:n.length}],b=[{type:12,data:y},{type:12,data:s},{type:6,data:l},{type:12,data:n},...J(e[0].dims,d)],x=$=>`
      ${$.registerUniforms(_).declareVariables(m,f)}
        ${Xl(m,f,r)}
        ${$.mainStart()}
          ${$.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.outputSize")}
          let output_indices = ${f.offsetToIndices("global_idx")};
          let input_indices = calculateInputIndices(output_indices);
          ${f.setByOffset("global_idx",m.getByIndices("input_indices"))}
      }`;return{name:"Slice",shaderCache:{hint:`${l.length}_${s.length}_${n.length}`,inputDependencies:["rank"]},getShaderSource:x,getRunData:()=>({outputs:[c],dispatchGroup:{x:Math.ceil(i/64)},programUniforms:b})}},Vf=(e,t)=>{Yl(e.inputs,t);let r=Zl(e.inputs,t);e.compute(Ql(e.inputs,r),{inputs:[0]})},jf=e=>{let t=e.starts,r=e.ends,i=e.axes;return he({starts:t,ends:r,axes:i})}}),Jl,ed,Ff,Gf,dy=L(()=>{ie(),se(),xe(),_t(),oe(),Jl=e=>{if(!e||e.length!==1)throw new Error("Softmax op requires 1 input.")},ed=(e,t)=>{let r=e.inputs[0],i=r.dims,a=O.size(i),n=i.length,s=O.normalizeAxis(t.axis,n),o=s<i.length-1,l,d=[];o?(d=Array.from({length:n},(k,A)=>A),d[s]=n-1,d[n-1]=s,l=e.compute(De(r,d),{inputs:[r],outputs:[-1]})[0]):l=r;let c=l.dims,f=c[n-1],m=a/f,y=$e(f),_=f/y,b=64;m===1&&(b=256);let x=(k,A)=>A===4?`max(max(${k}.x, ${k}.y), max(${k}.z, ${k}.w))`:A===2?`max(${k}.x, ${k}.y)`:A===3?`max(max(${k}.x, ${k}.y), ${k}.z)`:k,$=M("x",l.dataType,l.dims,y),w=X("result",l.dataType,l.dims,y),T=$.type.value,C=Ie(l.dataType)==="f32"?`var threadMax = ${T}(-3.402823e+38f);`:`var threadMax = ${T}(-65504.0h);`,I=k=>`
      var<workgroup> rowMaxShared : ${T};
      var<workgroup> rowSumShared : ${T};
      var<workgroup> threadShared : array<${T}, ${b}>;

      fn getValue(row: i32, col: i32, row_stride: i32) -> ${T} {
        let index = row * row_stride + col;
        return x[index];
      }

      fn setValue(row: i32, col: i32, row_stride: i32, value: ${T}) {
        let index = row * row_stride + col;
        result[index] = value;
      }
      ${k.registerUniform("packedCols","i32").declareVariables($,w)}
      ${k.mainStart(b)}
        let gindex = i32(global_idx);
        let lindex = i32(local_idx);
        const wg = ${b};
        let row = gindex / wg;
        let cols = uniforms.packedCols;
        let row_stride : i32 = uniforms.packedCols;

        // find the rows max
        ${C}
        for (var col = lindex; col < cols; col += wg) {
          let value = getValue(row, col, row_stride);
          threadMax = max(threadMax, value);
        }
        if (lindex < cols) {
          threadShared[lindex] = threadMax;
        }
        workgroupBarrier();

        var reduceSize = min(cols, wg);
        for (var currSize = reduceSize >> 1;  currSize > 0; currSize = reduceSize >> 1) {
          reduceSize = currSize + (reduceSize & 1);
          if (lindex < currSize) {
            threadShared[lindex] = max(threadShared[lindex], threadShared[lindex + reduceSize]);
          }
          workgroupBarrier();
        }
        if (lindex == 0) {
          rowMaxShared = ${T}(${x("threadShared[0]",y)});
        }
        workgroupBarrier();

        // find the rows sum
        var threadSum = ${T}(0.0);
        for (var col = lindex; col < cols; col += wg) {
          let subExp = exp(getValue(row, col, row_stride) - rowMaxShared);
          threadSum += subExp;
        }
        threadShared[lindex] = threadSum;
        workgroupBarrier();

        for (var currSize = wg >> 1;  currSize > 0; currSize = currSize >> 1) {
          if (lindex < currSize) {
            threadShared[lindex] = threadShared[lindex] + threadShared[lindex + currSize];
          }
          workgroupBarrier();
        }
        if (lindex == 0) {
          rowSumShared = ${T}(${yt("threadShared[0]",y)});
        }
        workgroupBarrier();

        // calculate final value for each element in the row
        for (var col = lindex; col < cols; col += wg) {
          var value = exp(getValue(row, col, row_stride) - rowMaxShared) / rowSumShared;
          // max operation protects against NaN since all values should be >=0
          value = max(value, ${T}(0.0));
          setValue(row, col, row_stride, value);
        }
      }`,z=e.compute({name:"Softmax",shaderCache:{hint:`${y};${b}`,inputDependencies:["type"]},getRunData:()=>({outputs:[{dims:c,dataType:l.dataType}],dispatchGroup:{x:m},programUniforms:[{type:6,data:_}]}),getShaderSource:I},{inputs:[l],outputs:[o?-1:0]})[0];o&&e.compute(De(z,d),{inputs:[z]})},Ff=(e,t)=>{Jl(e.inputs),ed(e,t)},Gf=e=>he({axis:e.axis})}),ma,td,id,rd,Hf,py=L(()=>{ie(),se(),oe(),ma=e=>Array.from(e.getBigInt64Array(),Number),td=e=>{if(!e||e.length!==2)throw new Error("Tile requires 2 inputs.");if(e[0].dataType!==1&&e[0].dataType!==10&&e[0].dataType!==6&&e[0].dataType!==12)throw new Error("Tile only support float, float16, int32, and uint32 data types");if(e[1].dataType!==7)throw new Error("Tile `repeats` input should be of int64 data type");if(e[1].dims.length!==1)throw new Error("Tile `repeats` input should be 1-D");if(ma(e[1]).length!==e[0].dims.length)throw new Error("Tile `repeats` input should have same number of elements as rank of input data tensor")},id=(e,t)=>{let r=[];for(let i=0;i<e.length;++i)r.push(e[i]*t[i]);return r},rd=(e,t)=>{let r=e[0].dims,i=t??ma(e[1]),a=id(r,i),n=O.size(a),s=e[0].dataType,o=M("input",s,r.length),l=X("output",s,a.length),d=c=>`
      const inputShape = ${o.indices(...r)};
      ${c.registerUniform("output_size","u32").declareVariables(o,l)}
      ${c.mainStart()}
      ${c.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.output_size")}
      let output_indices = ${l.offsetToIndices("global_idx")};
      var input_indices: ${o.type.indices};
      for (var i = 0; i < ${r.length}; i++) {
        let input_dim_i = ${o.indicesGet("uniforms.input_shape","i")};
        let input_dim_value = ${l.indicesGet("output_indices","i")}  % input_dim_i;

        ${o.indicesSet("input_indices","i","input_dim_value")}
      }
      ${l.setByOffset("global_idx",o.getByIndices("input_indices"))}
    }`;return{name:"Tile",shaderCache:{hint:`${i}`,inputDependencies:["rank"]},getRunData:()=>({outputs:[{dims:a,dataType:e[0].dataType}],dispatchGroup:{x:Math.ceil(n/64)},programUniforms:[{type:12,data:n},...J(e[0].dims,a)]}),getShaderSource:d}},Hf=e=>{td(e.inputs),e.compute(rd(e.inputs),{inputs:[0]})}}),ad,nd,Kf,cy=L(()=>{ie(),se(),oe(),ad=(e,t,r,i,a)=>{let n=X("output_data",a,r.length,4),s=M("a_data",t[1].dataType,t[1].dims.length,4),o=M("b_data",t[2].dataType,t[2].dims.length,4),l=M("c_data",t[0].dataType,t[0].dims.length,4),d,c=(f,m,y)=>`select(${m}, ${f}, ${y})`;if(!i)d=n.setByOffset("global_idx",c(s.getByOffset("global_idx"),o.getByOffset("global_idx"),l.getByOffset("global_idx")));else{let f=(m,y,_="")=>{let b=`a_data[index_a${y}][component_a${y}]`,x=`b_data[index_b${y}][component_b${y}]`,$=`bool(c_data[index_c${y}] & (0xffu << (component_c${y} * 8)))`;return`
            let output_indices${y} = ${n.offsetToIndices(`global_idx * 4u + ${y}u`)};
            let offset_a${y} = ${s.broadcastedIndicesToOffset(`output_indices${y}`,n)};
            let offset_b${y} = ${o.broadcastedIndicesToOffset(`output_indices${y}`,n)};
            let offset_c${y} = ${l.broadcastedIndicesToOffset(`output_indices${y}`,n)};
            let index_a${y} = offset_a${y} / 4u;
            let index_b${y} = offset_b${y} / 4u;
            let index_c${y} = offset_c${y} / 4u;
            let component_a${y} = offset_a${y} % 4u;
            let component_b${y} = offset_b${y} % 4u;
            let component_c${y} = offset_c${y} % 4u;
            ${m}[${y}] = ${_}(${c(b,x,$)});
          `};a===9?d=`
            var data = vec4<u32>(0);
            ${f("data",0,"u32")}
            ${f("data",1,"u32")}
            ${f("data",2,"u32")}
            ${f("data",3,"u32")}
            output_data[global_idx] = dot(vec4<u32>(0x1, 0x100, 0x10000, 0x1000000), vec4<u32>(data));`:d=`
            ${f("output_data[global_idx]",0)}
            ${f("output_data[global_idx]",1)}
            ${f("output_data[global_idx]",2)}
            ${f("output_data[global_idx]",3)}
          `}return`
        ${e.registerUniform("vec_size","u32").declareVariables(l,s,o,n)}
        ${e.mainStart()}
        ${e.guardAgainstOutOfBoundsWorkgroupSizes("uniforms.vec_size")}
        ${d}
      }`},nd=e=>{let t=e[1].dims,r=e[2].dims,i=e[0].dims,a=e[1].dataType,n=!(O.areEqual(t,r)&&O.areEqual(r,i)),s=t,o=O.size(t);if(n){let d=Wt.calcShape(Wt.calcShape(t,r,!1),i,!1);if(!d)throw new Error("Can't perform where op on the given tensors");s=d,o=O.size(s)}let l=Math.ceil(o/4);return{name:"Where",shaderCache:{inputDependencies:["rank","rank","rank"]},getShaderSource:d=>ad(d,e,s,n,a),getRunData:()=>({outputs:[{dims:s,dataType:a}],dispatchGroup:{x:Math.ceil(o/64/4)},programUniforms:[{type:12,data:l},...J(i,t,r,s)]})}},Kf=e=>{e.compute(nd(e.inputs))}}),Yf,fy=L(()=>{Ig(),sn(),kg(),Eg(),zg(),Ag(),Og(),Ng(),Ug(),Wg(),Lg(),qg(),Vg(),jg(),Fg(),Gg(),Hg(),Kg(),Yg(),Zg(),Xg(),Qg(),Jg(),ey(),ty(),hf(),iy(),ry(),ay(),ny(),sy(),nn(),oy(),bf(),uy(),ly(),dy(),yf(),py(),_t(),on(),cy(),Yf=new Map([["Abs",[Lp]],["Acos",[qp]],["Acosh",[Vp]],["Add",[xc]],["ArgMax",[Np,ka]],["ArgMin",[Dp,ka]],["Asin",[jp]],["Asinh",[Fp]],["Atan",[Gp]],["Atanh",[Hp]],["Attention",[Pp]],["AveragePool",[kf,If]],["BatchNormalization",[Up]],["BiasAdd",[Wp]],["BiasSplitGelu",[$c]],["Cast",[Yp,Kp]],["Ceil",[Xp]],["Clip",[Zp]],["Concat",[Rc,Bc]],["Conv",[Ba,Ra]],["ConvTranspose",[jc,Vc]],["Cos",[Qp]],["Cosh",[Jp]],["CumSum",[Fc,Gc]],["DepthToSpace",[Hc,Kc]],["DequantizeLinear",[Mf,Df]],["Div",[Cc]],["Einsum",[Yc,Zc]],["Elu",[ec,li]],["Equal",[Tc]],["Erf",[tc]],["Exp",[ic]],["Expand",[Xc]],["FastGelu",[Qc]],["Floor",[rc]],["FusedConv",[Ba,Ra]],["Gather",[ef,Jc]],["GatherElements",[of,sf]],["GatherBlockQuantized",[af,nf]],["GatherND",[tf,rf]],["Gelu",[ac]],["Gemm",[lf,uf]],["GlobalAveragePool",[zf,Ef]],["GlobalMaxPool",[Bf,Rf]],["Greater",[Ec]],["GreaterOrEqual",[Ac]],["GridSample",[df,pf]],["GroupQueryAttention",[wf]],["HardSigmoid",[cc,pc]],["InstanceNormalization",[vf]],["LayerNormalization",[$f]],["LeakyRelu",[nc,li]],["Less",[zc]],["LessOrEqual",[Oc]],["Log",[wc]],["MatMul",[xf]],["MatMulNBits",[Cf,Tf]],["MaxPool",[Af,Of]],["Mul",[Sc]],["MultiHeadAttention",[ff,cf]],["Neg",[oc]],["Not",[sc]],["Pad",[Sf]],["Pow",[Ic]],["QuickGelu",[vc,li]],["Range",[Nf]],["Reciprocal",[uc]],["ReduceMin",[Ap]],["ReduceMean",[Sp]],["ReduceMax",[zp]],["ReduceSum",[Rp]],["ReduceProd",[Op]],["ReduceL1",[Ip]],["ReduceL2",[kp]],["ReduceLogSum",[Mp]],["ReduceLogSumExp",[Ep]],["ReduceSumSquare",[Bp]],["Relu",[lc]],["Resize",[Wf,Lf]],["RotaryEmbedding",[_f]],["ScatterND",[Uf,Pf]],["Sigmoid",[dc]],["Sin",[fc]],["Sinh",[hc]],["Slice",[Vf,jf]],["SkipLayerNormalization",[qf]],["Split",[mf,gf]],["Sqrt",[mc]],["Softmax",[Ff,Gf]],["Sub",[kc]],["Tan",[gc]],["Tanh",[yc]],["ThresholdedRelu",[bc,li]],["Tile",[Hf]],["Transpose",[hp,mp]],["Where",[Kf]]])}),Zf,hy=L(()=>{We(),st(),oe(),Zf=class{constructor(e){this.backend=e,this.repo=new Map,this.attributesBound=!1}getArtifact(e){return this.repo.get(e)}setArtifact(e,t){this.repo.set(e,t)}run(e,t,r,i,a){Ke(e.programInfo.name);let n=this.backend.device,s=this.backend.getComputePassEncoder();this.backend.writeTimestamp(this.backend.pendingDispatchNumber*2);let o=[];for(let d of t)o.push({binding:o.length,resource:{buffer:d.buffer}});for(let d of r)o.push({binding:o.length,resource:{buffer:d.buffer}});a&&o.push({binding:o.length,resource:a});let l=n.createBindGroup({layout:e.computePipeline.getBindGroupLayout(0),entries:o,label:e.programInfo.name});if(this.backend.sessionStatus==="capturing"){let d={kernelId:this.backend.currentKernelId,computePipeline:e.computePipeline,bindGroup:l,dispatchGroup:i};this.backend.capturedCommandList.get(this.backend.currentSessionId).push(d)}s.setPipeline(e.computePipeline),s.setBindGroup(0,l),s.dispatchWorkgroups(...i),this.backend.writeTimestamp(this.backend.pendingDispatchNumber*2+1),this.backend.pendingDispatchNumber++,(this.backend.pendingDispatchNumber>=this.backend.maxDispatchNumber||this.backend.queryType==="at-passes")&&this.backend.endComputePass(),this.backend.pendingDispatchNumber>=this.backend.maxDispatchNumber&&this.backend.flush(),Ue(e.programInfo.name)}dispose(){}build(e,t){Ke(e.name);let r=this.backend.device,i=[];[{feature:"shader-f16",extension:"f16"},{feature:"subgroups",extension:"subgroups"}].forEach(d=>{r.features.has(d.feature)&&i.push(`enable ${d.extension};`)});let a=fp(t,this.backend.device.limits),n=e.getShaderSource(a),s=`${i.join(`
`)}
${a.additionalImplementations}
${n}`,o=r.createShaderModule({code:s,label:e.name});de("verbose",()=>`[WebGPU] ${e.name} shader code: ${s}`);let l=r.createComputePipeline({compute:{module:o,entryPoint:"main"},layout:"auto",label:e.name});return Ue(e.name),{programInfo:e,computePipeline:l,uniformVariablesInfo:a.variablesInfo}}normalizeDispatchGroupSize(e){let t=typeof e=="number"?e:e.x,r=typeof e=="number"?1:e.y||1,i=typeof e=="number"?1:e.z||1,a=this.backend.device.limits.maxComputeWorkgroupsPerDimension;if(t<=a&&r<=a&&i<=a)return[t,r,i];let n=t*r*i,s=Math.ceil(Math.sqrt(n));if(s>a){if(s=Math.ceil(Math.cbrt(n)),s>a)throw new Error("Total dispatch size exceeds WebGPU maximum.");return[s,s,s]}else return[s,s,1]}}}),Xf={};qt(Xf,{WebGpuBackend:()=>Qf});var sd,od,ud,Qf,my=L(()=>{We(),ie(),st(),up(),Tg(),fy(),hy(),sd=(e,t)=>{if(t.length!==e.length)throw new Error(`inputDependencies length ${t.length} is not equal to inputTensors length ${e.length}.`);let r=[];for(let i=0;i<e.length;++i){let a=e[i].dataType;switch(t[i]){case"none":{r.push("");break}case"type":{r.push(`${a}`);break}case"rank":{let n=e[i].dims.length;r.push(`${a};${n}`);break}case"dims":{let n=e[i].dims.join(",");r.push(`${a};${n}`);break}default:throw new Error(`unsupported input dependency: ${t[i]}`)}}return r.join("|")},od=(e,t,r)=>{let i=e.name;return e.shaderCache?.hint&&(i+="["+e.shaderCache.hint+"]"),i+=":"+r+`:${sd(t,e.shaderCache?.inputDependencies??new Array(t.length).fill("dims"))}`,i},ud=class{constructor(e){e&&(this.architecture=e.architecture,this.vendor=e.vendor)}isArchitecture(e){return this.architecture===e}isVendor(e){return this.vendor===e}},Qf=class{constructor(){this.currentSessionId=null,this.currentKernelId=null,this.commandEncoder=null,this.computePassEncoder=null,this.maxDispatchNumber=16,this.pendingDispatchNumber=0,this.pendingKernels=[],this.pendingQueries=new Map,this.sessionStatus="default",this.capturedCommandList=new Map,this.capturedPendingKernels=new Map,this.sessionExternalDataMapping=new Map}get currentKernelCustomData(){if(this.currentKernelId===null)throw new Error("currentKernelCustomData(): currentKernelId is null. (should not happen)");let e=this.kernelCustomData.get(this.currentKernelId);return e||(e={},this.kernelCustomData.set(this.currentKernelId,e)),e}async initialize(e,t){this.env=e;let r=[],i={requiredLimits:{maxComputeWorkgroupStorageSize:t.limits.maxComputeWorkgroupStorageSize,maxComputeWorkgroupsPerDimension:t.limits.maxComputeWorkgroupsPerDimension,maxStorageBufferBindingSize:t.limits.maxStorageBufferBindingSize,maxBufferSize:t.limits.maxBufferSize,maxComputeInvocationsPerWorkgroup:t.limits.maxComputeInvocationsPerWorkgroup,maxComputeWorkgroupSizeX:t.limits.maxComputeWorkgroupSizeX,maxComputeWorkgroupSizeY:t.limits.maxComputeWorkgroupSizeY,maxComputeWorkgroupSizeZ:t.limits.maxComputeWorkgroupSizeZ},requiredFeatures:r},a=n=>t.features.has(n)&&r.push(n)&&!0;a("chromium-experimental-timestamp-query-inside-passes")||a("timestamp-query"),a("shader-f16"),a("subgroups"),this.device=await t.requestDevice(i),this.adapterInfo=new ud(t.info||await t.requestAdapterInfo()),this.gpuDataManager=pp(this),this.programManager=new Zf(this),this.kernels=new Map,this.kernelPersistentData=new Map,this.kernelCustomData=new Map,en(e.logLevel,!!e.debug),this.device.onuncapturederror=n=>{n.error instanceof GPUValidationError&&console.error(`An uncaught WebGPU validation error was raised: ${n.error.message}`)},Object.defineProperty(this.env.webgpu,"device",{value:this.device,writable:!1,enumerable:!0,configurable:!1}),Object.defineProperty(this.env.webgpu,"adapter",{value:t,writable:!1,enumerable:!0,configurable:!1}),this.setQueryType()}dispose(){typeof this.querySet<"u"&&this.querySet.destroy(),this.gpuDataManager.dispose()}getCommandEncoder(){return this.commandEncoder||(this.commandEncoder=this.device.createCommandEncoder()),this.commandEncoder}getComputePassEncoder(){if(!this.computePassEncoder){let e=this.getCommandEncoder(),t={};this.queryType==="at-passes"&&(t.timestampWrites={querySet:this.querySet,beginningOfPassWriteIndex:this.pendingDispatchNumber*2,endOfPassWriteIndex:this.pendingDispatchNumber*2+1}),this.computePassEncoder=e.beginComputePass(t)}return this.computePassEncoder}endComputePass(){this.computePassEncoder&&(this.computePassEncoder.end(),this.computePassEncoder=null)}flush(){if(!this.commandEncoder)return;Ke(),this.endComputePass();let e;this.queryType!=="none"&&(this.commandEncoder.resolveQuerySet(this.querySet,0,this.pendingDispatchNumber*2,this.queryResolveBuffer,0),e=this.device.createBuffer({size:this.pendingDispatchNumber*2*8,usage:GPUBufferUsage.MAP_READ|GPUBufferUsage.COPY_DST}),this.pendingQueries.set(e,this.pendingKernels),this.pendingKernels=[],this.commandEncoder.copyBufferToBuffer(this.queryResolveBuffer,0,e,0,this.pendingDispatchNumber*2*8)),this.device.queue.submit([this.commandEncoder.finish()]),this.gpuDataManager.refreshPendingBuffers(),this.commandEncoder=null,this.pendingDispatchNumber=0,this.queryType!=="none"&&e.mapAsync(GPUMapMode.READ).then(()=>{let t=new BigUint64Array(e.getMappedRange()),r=this.pendingQueries.get(e);for(let i=0;i<t.length/2;i++){let a=r[i],n=a.kernelId,s=this.kernels.get(n),o=s.kernelType,l=s.kernelName,d=a.programName,c=a.inputTensorViews,f=a.outputTensorViews,m=t[i*2],y=t[i*2+1];typeof this.queryTimeBase>"u"&&(this.queryTimeBase=m);let _=Number(m-this.queryTimeBase),b=Number(y-this.queryTimeBase);if(!Number.isSafeInteger(_)||!Number.isSafeInteger(b))throw new RangeError("incorrect timestamp range");if(this.env.webgpu.profiling?.ondata)this.env.webgpu.profiling.ondata({version:1,inputsMetadata:c.map(x=>({dims:x.dims,dataType:nt(x.dataType)})),outputsMetadata:f.map(x=>({dims:x.dims,dataType:nt(x.dataType)})),kernelId:n,kernelType:o,kernelName:l,programName:d,startTime:_,endTime:b});else{let x="";c.forEach((w,T)=>{x+=`input[${T}]: [${w.dims}] | ${nt(w.dataType)}, `});let $="";f.forEach((w,T)=>{$+=`output[${T}]: [${w.dims}] | ${nt(w.dataType)}, `}),console.log(`[profiling] kernel "${n}|${o}|${l}|${d}" ${x}${$}start time: ${_} ns, execution time: ${b-_} ns`)}fi("GPU",`${d}::${m}::${y}`)}e.unmap(),this.pendingQueries.delete(e)}),Ue()}run(e,t,r,i,a,n){Ke(e.name);let s=[];for(let w=0;w<t.length;++w){let T=t[w].data;if(T===0)continue;let C=this.gpuDataManager.get(T);if(!C)throw new Error(`no GPU data for input: ${T}`);s.push(C)}let{outputs:o,dispatchGroup:l,programUniforms:d}=e.getRunData(t),c=r.length===0?o.map((w,T)=>T):r;if(c.length!==o.length)throw new Error(`Output size ${c.length} must be equal to ${o.length}.`);let f=[],m=[];for(let w=0;w<o.length;++w){if(!Number.isInteger(c[w])||c[w]<-3||c[w]>=n)throw new Error(`Invalid output index: ${c[w]}`);if(c[w]===-3)continue;let T=c[w]===-1,C=c[w]===-2,I=T||C?a(o[w].dataType,o[w].dims):i(c[w],o[w].dataType,o[w].dims);if(f.push(I),I.data===0)continue;let z=this.gpuDataManager.get(I.data);if(!z)throw new Error(`no GPU data for output: ${I.data}`);if(T&&this.temporaryData.push(z),C){let k=this.kernelPersistentData.get(this.currentKernelId);k||(k=[],this.kernelPersistentData.set(this.currentKernelId,k)),k.push(z)}m.push(z)}if(s.length!==t.length||m.length!==f.length){if(m.length===0)return Ue(e.name),f;throw new Error(`Program ${e.name} has zero-sized tensor(s) in inputs or outputs. This is not supported now.`)}let y;if(d){let w=0,T=[];d.forEach(k=>{let A=typeof k.data=="number"?[k.data]:k.data;if(A.length===0)return;let D=k.type===10?2:4,V,G;k.type===10?(G=A.length>4?16:A.length>2?8:A.length*D,V=A.length>4?16:D*A.length):(G=A.length<=2?A.length*D:16,V=16),w=Math.ceil(w/G)*G,T.push(w);let H=k.type===10?8:4;w+=A.length>4?Math.ceil(A.length/H)*V:A.length*D});let C=16;w=Math.ceil(w/C)*C;let I=new ArrayBuffer(w);d.forEach((k,A)=>{let D=T[A],V=typeof k.data=="number"?[k.data]:k.data;if(k.type===6)new Int32Array(I,D,V.length).set(V);else if(k.type===12)new Uint32Array(I,D,V.length).set(V);else if(k.type===10)new Uint16Array(I,D,V.length).set(V);else if(k.type===1)new Float32Array(I,D,V.length).set(V);else throw new Error(`Unsupported uniform type: ${nt(k.type)}`)});let z=this.gpuDataManager.create(w,GPUBufferUsage.COPY_DST|GPUBufferUsage.UNIFORM);this.device.queue.writeBuffer(z.buffer,0,I,0,w),this.gpuDataManager.release(z.id),y={offset:0,size:w,buffer:z.buffer}}let _=this.programManager.normalizeDispatchGroupSize(l),b=_[1]===1&&_[2]===1,x=od(e,t,b),$=this.programManager.getArtifact(x);if($||($=this.programManager.build(e,_),this.programManager.setArtifact(x,$),de("info",()=>`[artifact] key: ${x}, programName: ${e.name}`)),d&&$.uniformVariablesInfo){if(d.length!==$.uniformVariablesInfo.length)throw new Error(`Uniform variables count mismatch: expect ${$.uniformVariablesInfo.length}, got ${d.length} in program "${$.programInfo.name}".`);for(let w=0;w<d.length;w++){let T=d[w],C=T.type,I=typeof T.data=="number"?1:T.data.length,[z,k]=$.uniformVariablesInfo[w];if(C!==z||I!==k)throw new Error(`Uniform variable ${w} mismatch: expect type ${z} with size ${k}, got type ${C} with size ${I} in program "${$.programInfo.name}".`)}}if(de("info",()=>`[ProgramManager] run "${e.name}" (key=${x}) with ${_[0]}x${_[1]}x${_[2]}`),this.queryType!=="none"||this.sessionStatus==="capturing"){let w={kernelId:this.currentKernelId,programName:$.programInfo.name,inputTensorViews:t,outputTensorViews:f};this.pendingKernels.push(w),this.sessionStatus==="capturing"&&this.capturedPendingKernels.get(this.currentSessionId).push(w)}return this.programManager.run($,s,m,_,y),Ue(e.name),f}upload(e,t){this.gpuDataManager.upload(e,t)}memcpy(e,t){this.gpuDataManager.memcpy(e,t)}async download(e,t){await this.gpuDataManager.download(e,t)}alloc(e){return this.gpuDataManager.create(e).id}free(e){return this.gpuDataManager.release(e)}createKernel(e,t,r,i){let a=Yf.get(e);if(!a)throw new Error(`kernel not implemented: ${e}`);let n={kernelType:e,kernelName:i,kernelEntry:a[0],attributes:[a[1],r]};this.kernels.set(t,n)}releaseKernel(e){let t=this.kernelPersistentData.get(e);if(t){for(let r of t)this.gpuDataManager.release(r.id);this.kernelPersistentData.delete(e)}this.kernelCustomData.delete(e),this.kernels.delete(e)}computeKernel(e,t,r){let i=this.kernels.get(e);if(!i)throw new Error(`kernel not created: ${e}`);let a=i.kernelType,n=i.kernelName,s=i.kernelEntry,o=i.attributes;if(this.currentKernelId!==null)throw new Error(`kernel "[${a}] ${n}" is not allowed to be called recursively`);this.currentKernelId=e,o[0]&&(o[1]=o[0](o[1]),o[0]=void 0),de("info",()=>`[WebGPU] Start to run kernel "[${a}] ${n}"...`);let l=this.env.debug;this.temporaryData=[];try{return l&&this.device.pushErrorScope("validation"),s(t,o[1]),0}catch(d){return r.push(Promise.resolve(`[WebGPU] Kernel "[${a}] ${n}" failed. ${d}`)),1}finally{l&&r.push(this.device.popErrorScope().then(d=>d?`GPU validation error for kernel "[${a}] ${n}": ${d.message}`:null));for(let d of this.temporaryData)this.gpuDataManager.release(d.id);this.temporaryData=[],this.currentKernelId=null}}registerBuffer(e,t,r,i){let a=this.sessionExternalDataMapping.get(e);a||(a=new Map,this.sessionExternalDataMapping.set(e,a));let n=a.get(t),s=this.gpuDataManager.registerExternalBuffer(r,i,n);return a.set(t,[s,r]),s}unregisterBuffers(e){let t=this.sessionExternalDataMapping.get(e);t&&(t.forEach(r=>this.gpuDataManager.unregisterExternalBuffer(r[0])),this.sessionExternalDataMapping.delete(e))}getBuffer(e){let t=this.gpuDataManager.get(e);if(!t)throw new Error(`no GPU data for buffer: ${e}`);return t.buffer}createDownloader(e,t,r){return async()=>{let i=await Ta(this,e,t);return tn(i.buffer,r)}}writeTimestamp(e){this.queryType==="inside-passes"&&this.computePassEncoder.writeTimestamp(this.querySet,e)}setQueryType(){this.queryType="none",(this.env.webgpu.profiling?.mode==="default"||(typeof this.env.trace>"u"?this.env.wasm.trace:this.env.trace))&&(this.device.features.has("chromium-experimental-timestamp-query-inside-passes")?this.queryType="inside-passes":this.device.features.has("timestamp-query")&&(this.queryType="at-passes"),this.queryType!=="none"&&typeof this.querySet>"u"&&(this.querySet=this.device.createQuerySet({type:"timestamp",count:this.maxDispatchNumber*2}),this.queryResolveBuffer=this.device.createBuffer({size:this.maxDispatchNumber*2*8,usage:GPUBufferUsage.COPY_SRC|GPUBufferUsage.QUERY_RESOLVE})))}captureBegin(){de("info","captureBegin"),this.capturedCommandList.get(this.currentSessionId)||this.capturedCommandList.set(this.currentSessionId,[]),this.capturedPendingKernels.get(this.currentSessionId)||this.capturedPendingKernels.set(this.currentSessionId,[]),this.flush(),this.sessionStatus="capturing"}captureEnd(){de("info","captureEnd"),this.flush(),this.sessionStatus="default"}replay(){de("info","replay"),this.sessionStatus="replaying";let e=this.capturedCommandList.get(this.currentSessionId),t=this.capturedPendingKernels.get(this.currentSessionId),r=e.length;this.pendingKernels=[];for(let i=0;i<r;i++){let a=this.getComputePassEncoder(),n=e[i];this.writeTimestamp(this.pendingDispatchNumber*2),a.setPipeline(n.computePipeline),a.setBindGroup(0,n.bindGroup),a.dispatchWorkgroups(...n.dispatchGroup),this.writeTimestamp(this.pendingDispatchNumber*2+1),this.pendingDispatchNumber++,this.queryType!=="none"&&this.pendingKernels.push(t[i]),(this.pendingDispatchNumber>=this.maxDispatchNumber||this.queryType==="at-passes")&&this.endComputePass(),this.pendingDispatchNumber>=this.maxDispatchNumber&&this.flush()}this.flush(),this.sessionStatus="default"}onCreateSession(){this.gpuDataManager.onCreateSession()}onReleaseSession(e){this.unregisterBuffers(e),this.capturedCommandList.has(e)&&this.capturedCommandList.delete(e),this.capturedPendingKernels.has(e)&&this.capturedPendingKernels.delete(e),this.gpuDataManager.onReleaseSession(e)}onRunStart(e){this.currentSessionId=e,this.setQueryType()}}}),Jf={};qt(Jf,{init:()=>eh});var Di,ld,eh,gy=L(()=>{ie(),st(),se(),Cg(),Di=class th{constructor(t,r,i,a){this.module=t,this.dataType=r,this.data=i,this.dims=a}getFloat32Array(){if(this.dataType!==1)throw new Error("Invalid data type");let t=O.size(this.dims);return t===0?new Float32Array:new Float32Array(this.module.HEAP8.buffer,this.data,t)}getBigInt64Array(){if(this.dataType!==7)throw new Error("Invalid data type");let t=O.size(this.dims);return t===0?new BigInt64Array:new BigInt64Array(this.module.HEAP8.buffer,this.data,t)}getInt32Array(){if(this.dataType!==6)throw new Error("Invalid data type");let t=O.size(this.dims);return t===0?new Int32Array:new Int32Array(this.module.HEAP8.buffer,this.data,t)}getUint16Array(){if(this.dataType!==10&&this.dataType!==4)throw new Error("Invalid data type");let t=O.size(this.dims);return t===0?new Uint16Array:new Uint16Array(this.module.HEAP8.buffer,this.data,t)}reshape(t){if(O.size(t)!==O.size(this.dims))throw new Error("Invalid new shape");return new th(this.module,this.dataType,this.data,t)}},ld=class{constructor(e,t,r){this.module=e,this.backend=t,this.customDataOffset=0,this.customDataSize=0,this.adapterInfo=t.adapterInfo;let i=e.PTR_SIZE,a=r/e.PTR_SIZE,n=i===4?"i32":"i64";this.opKernelContext=Number(e.getValue(i*a++,n));let s=Number(e.getValue(i*a++,n));this.outputCount=Number(e.getValue(i*a++,n)),this.customDataOffset=Number(e.getValue(i*a++,"*")),this.customDataSize=Number(e.getValue(i*a++,n));let o=[];for(let l=0;l<s;l++){let d=Number(e.getValue(i*a++,n)),c=Number(e.getValue(i*a++,"*")),f=Number(e.getValue(i*a++,n)),m=[];for(let y=0;y<f;y++)m.push(Number(e.getValue(i*a++,n)));o.push(new Di(e,d,c,m))}this.inputs=o}get kernelCustomData(){return this.backend.currentKernelCustomData}get customDataBuffer(){return this.module.HEAPU8.subarray(this.customDataOffset,this.customDataOffset+this.customDataSize)}compute(e,t){let r=t?.inputs?.map(s=>typeof s=="number"?this.inputs[s]:s)??this.inputs,i=t?.outputs??[],a=(s,o,l)=>new Di(this.module,o,this.output(s,l),l),n=(s,o)=>{let l=kt(s,o);if(!l)throw new Error(`Unsupported data type: ${s}`);let d=l>0?this.backend.gpuDataManager.create(l).id:0;return new Di(this.module,s,d,o)};return this.backend.run(e,r,i,a,n,this.outputCount)}output(e,t){let r=this.module.stackSave();try{let i=this.module.PTR_SIZE,a=i===4?"i32":"i64",n=this.module.stackAlloc((1+t.length)*i);this.module.setValue(n,t.length,a);for(let s=0;s<t.length;s++)this.module.setValue(n+i*(s+1),t[s],a);return this.module._JsepOutput(this.opKernelContext,e,n)}catch(i){throw new Error(`Failed to generate kernel's output[${e}] with dims [${t}]. If you are running with pre-allocated output, please make sure the output type/dims are correct. Error: ${i}`)}finally{this.module.stackRestore(r)}}},eh=async(e,t,r,i)=>{let a=t.jsepInit;if(!a)throw new Error("Failed to initialize JSEP. The WebAssembly module is not built with JSEP support.");if(e==="webgpu"){let n=(my(),ci(Xf)).WebGpuBackend,s=new n;await s.initialize(r,i),a("webgpu",[s,o=>s.alloc(Number(o)),o=>s.free(o),(o,l,d,c=!1)=>{if(c)de("verbose",()=>`[WebGPU] jsepCopyGpuToGpu: src=${Number(o)}, dst=${Number(l)}, size=${Number(d)}`),s.memcpy(Number(o),Number(l));else{de("verbose",()=>`[WebGPU] jsepCopyCpuToGpu: dataOffset=${Number(o)}, gpuDataId=${Number(l)}, size=${Number(d)}`);let f=t.HEAPU8.subarray(Number(o>>>0),Number(o>>>0)+Number(d));s.upload(Number(l),f)}},async(o,l,d)=>{de("verbose",()=>`[WebGPU] jsepCopyGpuToCpu: gpuDataId=${o}, dataOffset=${l}, size=${d}`),await s.download(Number(o),()=>t.HEAPU8.subarray(Number(l)>>>0,Number(l+d)>>>0))},(o,l,d)=>s.createKernel(o,Number(l),d,t.UTF8ToString(t._JsepGetNodeName(Number(l)))),o=>s.releaseKernel(o),(o,l,d,c)=>{de("verbose",()=>`[WebGPU] jsepRun: sessionHandle=${d}, kernel=${o}, contextDataOffset=${l}`);let f=new ld(t,s,Number(l));return s.computeKernel(Number(o),f,c)},()=>s.captureBegin(),()=>s.captureEnd(),()=>s.replay()])}else{let n=new dp(r);a("webnn",[n,()=>n.reserveTensorId(),s=>n.releaseTensorId(s),async(s,o,l,d,c)=>n.ensureTensor(s,o,l,d,c),(s,o)=>{n.uploadTensor(s,o)},async(s,o)=>n.downloadTensor(s,o),(s,o)=>n.registerMLContext(s,o),!!r.trace])}}}),dd,fn,hn,ft,pd,ga,Yi,mn,gn,ya,yn,_n,bn,ih=L(()=>{We(),vg(),$g(),ie(),Rt(),Za(),ap(),dd=(e,t)=>{_e()._OrtInit(e,t)!==0&&me("Can't initialize onnxruntime.")},fn=async e=>{dd(e.wasm.numThreads,ji(e.logLevel))},hn=async(e,t)=>{_e().asyncInit?.();let r=e.webgpu.adapter;if(t==="webgpu"){if(typeof navigator>"u"||!navigator.gpu)throw new Error("WebGPU is not supported in current environment");if(r){if(typeof r.limits!="object"||typeof r.features!="object"||typeof r.requestDevice!="function")throw new Error("Invalid GPU adapter set in `env.webgpu.adapter`. It must be a GPUAdapter object.")}else{let i=e.webgpu.powerPreference;if(i!==void 0&&i!=="low-power"&&i!=="high-performance")throw new Error(`Invalid powerPreference setting: "${i}"`);let a=e.webgpu.forceFallbackAdapter;if(a!==void 0&&typeof a!="boolean")throw new Error(`Invalid forceFallbackAdapter setting: "${a}"`);if(r=await navigator.gpu.requestAdapter({powerPreference:i,forceFallbackAdapter:a}),!r)throw new Error('Failed to get GPU adapter. You may need to enable flag "--enable-unsafe-webgpu" if you are using Chrome.')}}if(t==="webnn"&&(typeof navigator>"u"||!navigator.ml))throw new Error("WebNN is not supported in current environment");{let i=(gy(),ci(Jf)).init;t==="webgpu"&&await i("webgpu",_e(),e,r),t==="webnn"&&await i("webnn",_e(),e)}},ft=new Map,pd=e=>{let t=_e(),r=t.stackSave();try{let i=t.PTR_SIZE,a=t.stackAlloc(2*i);t._OrtGetInputOutputCount(e,a,a+i)!==0&&me("Can't get session input/output count.");let n=i===4?"i32":"i64";return[Number(t.getValue(a,n)),Number(t.getValue(a+i,n))]}finally{t.stackRestore(r)}},ga=(e,t)=>{let r=_e(),i=r.stackSave(),a=0;try{let n=r.PTR_SIZE,s=r.stackAlloc(2*n);r._OrtGetInputOutputMetadata(e,t,s,s+n)!==0&&me("Can't get session input/output metadata.");let o=Number(r.getValue(s,"*"));a=Number(r.getValue(s+n,"*"));let l=r.HEAP32[a/4];if(l===0)return[o,0];let d=r.HEAPU32[a/4+1],c=[];for(let f=0;f<d;f++){let m=Number(r.getValue(a+8+f*n,"*"));c.push(m!==0?r.UTF8ToString(m):Number(r.getValue(a+8+(f+d)*n,"*")))}return[o,l,c]}finally{r.stackRestore(i),a!==0&&r._OrtFree(a)}},Yi=e=>{let t=_e(),r=t._malloc(e.byteLength);if(r===0)throw new Error(`Can't create a session. failed to allocate a buffer of size ${e.byteLength}.`);return t.HEAPU8.set(e,r),[r,e.byteLength]},mn=async(e,t)=>{let r,i,a=_e();Array.isArray(e)?[r,i]=e:e.buffer===a.HEAPU8.buffer?[r,i]=[e.byteOffset,e.byteLength]:[r,i]=Yi(e);let n=0,s=0,o=0,l=[],d=[],c=[];try{if([s,l]=await rp(t),t?.externalData&&a.mountExternalData){let C=[];for(let I of t.externalData){let z=typeof I=="string"?I:I.path;C.push(Ja(typeof I=="string"?I:I.data).then(k=>{a.mountExternalData(z,k)}))}await Promise.all(C)}for(let C of t?.executionProviders??[])if((typeof C=="string"?C:C.name)==="webnn"){if(a.shouldTransferToMLTensor=!1,typeof C!="string"){let I=C,z=I?.context,k=I?.gpuDevice,A=I?.deviceType,D=I?.powerPreference;z?a.currentContext=z:k?a.currentContext=await a.webnnCreateMLContext(k):a.currentContext=await a.webnnCreateMLContext({deviceType:A,powerPreference:D})}else a.currentContext=await a.webnnCreateMLContext();break}n=await a._OrtCreateSession(r,i,s),a.webgpuOnCreateSession?.(n),n===0&&me("Can't create a session."),a.jsepOnCreateSession?.(),a.currentContext&&(a.webnnRegisterMLContext(n,a.currentContext),a.currentContext=void 0,a.shouldTransferToMLTensor=!0);let[f,m]=pd(n),y=!!t?.enableGraphCapture,_=[],b=[],x=[],$=[],w=[];for(let C=0;C<f;C++){let[I,z,k]=ga(n,C);I===0&&me("Can't get an input name."),d.push(I);let A=a.UTF8ToString(I);_.push(A),x.push(z===0?{name:A,isTensor:!1}:{name:A,isTensor:!0,type:nt(z),shape:k})}for(let C=0;C<m;C++){let[I,z,k]=ga(n,C+f);I===0&&me("Can't get an output name."),c.push(I);let A=a.UTF8ToString(I);b.push(A),$.push(z===0?{name:A,isTensor:!1}:{name:A,isTensor:!0,type:nt(z),shape:k});{if(y&&t?.preferredOutputLocation===void 0){w.push("gpu-buffer");continue}let D=typeof t?.preferredOutputLocation=="string"?t.preferredOutputLocation:t?.preferredOutputLocation?.[A]??"cpu",V=a.webnnIsGraphOutput;if(D==="cpu"&&V&&V(n,A)){w.push("ml-tensor-cpu-output");continue}if(D!=="cpu"&&D!=="cpu-pinned"&&D!=="gpu-buffer"&&D!=="ml-tensor")throw new Error(`Not supported preferred output location: ${D}.`);if(y&&D!=="gpu-buffer")throw new Error(`Not supported preferred output location: ${D}. Only 'gpu-buffer' location is supported when enableGraphCapture is true.`);w.push(D)}}let T=null;return w.some(C=>C==="gpu-buffer"||C==="ml-tensor"||C==="ml-tensor-cpu-output")&&(o=a._OrtCreateBinding(n),o===0&&me("Can't create IO binding."),T={handle:o,outputPreferredLocations:w,outputPreferredLocationsEncoded:w.map(C=>C==="ml-tensor-cpu-output"?"ml-tensor":C).map(C=>xa(C))}),ft.set(n,[n,d,c,T,y,!1]),[n,_,b,x,$]}catch(f){throw d.forEach(m=>a._OrtFree(m)),c.forEach(m=>a._OrtFree(m)),o!==0&&a._OrtReleaseBinding(o)!==0&&me("Can't release IO binding."),n!==0&&a._OrtReleaseSession(n)!==0&&me("Can't release session."),f}finally{a._free(r),s!==0&&a._OrtReleaseSessionOptions(s)!==0&&me("Can't release session options."),l.forEach(f=>a._free(f)),a.unmountExternalData?.()}},gn=e=>{let t=_e(),r=ft.get(e);if(!r)throw new Error(`cannot release session. invalid session id: ${e}`);let[i,a,n,s,o]=r;s&&(o&&t._OrtClearBoundOutputs(s.handle)!==0&&me("Can't clear bound outputs."),t._OrtReleaseBinding(s.handle)!==0&&me("Can't release IO binding.")),t.jsepOnReleaseSession?.(e),t.webnnOnReleaseSession?.(e),t.webgpuOnReleaseSession?.(e),a.forEach(l=>t._OrtFree(l)),n.forEach(l=>t._OrtFree(l)),t._OrtReleaseSession(i)!==0&&me("Can't release session."),ft.delete(e)},ya=async(e,t,r,i,a,n,s=!1)=>{if(!e){t.push(0);return}let o=_e(),l=o.PTR_SIZE,d=e[0],c=e[1],f=e[3],m=f,y,_;if(d==="string"&&(f==="gpu-buffer"||f==="ml-tensor"))throw new Error("String tensor is not supported on GPU.");if(s&&f!=="gpu-buffer")throw new Error(`External buffer must be provided for input/output index ${n} when enableGraphCapture is true.`);if(f==="gpu-buffer"){let $=e[2].gpuBuffer;_=kt(It(d),c);{let w=o.jsepRegisterBuffer;if(!w)throw new Error('Tensor location "gpu-buffer" is not supported without using WebGPU.');y=w(i,n,$,_)}}else if(f==="ml-tensor"){let $=e[2].mlTensor;_=kt(It(d),c);let w=o.webnnRegisterMLTensor;if(!w)throw new Error('Tensor location "ml-tensor" is not supported without using WebNN.');y=w(i,$,It(d),c)}else{let $=e[2];if(Array.isArray($)){_=l*$.length,y=o._malloc(_),r.push(y);for(let w=0;w<$.length;w++){if(typeof $[w]!="string")throw new TypeError(`tensor data at index ${w} is not a string`);o.setValue(y+w*l,Ge($[w],r),"*")}}else{let w=o.webnnIsGraphInput,T=o.webnnIsGraphOutput;if(d!=="string"&&w&&T){let C=o.UTF8ToString(a);if(w(i,C)||T(i,C)){let I=It(d);_=kt(I,c),m="ml-tensor";let z=o.webnnCreateTemporaryTensor,k=o.webnnUploadTensor;if(!z||!k)throw new Error('Tensor location "ml-tensor" is not supported without using WebNN.');let A=await z(i,I,c);k(A,new Uint8Array($.buffer,$.byteOffset,$.byteLength)),y=A}else _=$.byteLength,y=o._malloc(_),r.push(y),o.HEAPU8.set(new Uint8Array($.buffer,$.byteOffset,_),y)}else _=$.byteLength,y=o._malloc(_),r.push(y),o.HEAPU8.set(new Uint8Array($.buffer,$.byteOffset,_),y)}}let b=o.stackSave(),x=o.stackAlloc(4*c.length);try{c.forEach((w,T)=>o.setValue(x+T*l,w,l===4?"i32":"i64"));let $=o._OrtCreateTensor(It(d),y,_,x,c.length,xa(m));$===0&&me(`Can't create tensor for input/output. session=${i}, index=${n}.`),t.push($)}finally{o.stackRestore(b)}},yn=async(e,t,r,i,a,n)=>{let s=_e(),o=s.PTR_SIZE,l=ft.get(e);if(!l)throw new Error(`cannot run inference. invalid session id: ${e}`);let d=l[0],c=l[1],f=l[2],m=l[3],y=l[4],_=l[5],b=t.length,x=i.length,$=0,w=[],T=[],C=[],I=[],z=s.stackSave(),k=s.stackAlloc(b*o),A=s.stackAlloc(b*o),D=s.stackAlloc(x*o),V=s.stackAlloc(x*o);try{[$,w]=ip(n),mt("wasm prepareInputOutputTensor");for(let W=0;W<b;W++)await ya(r[W],T,I,e,c[t[W]],t[W],y);for(let W=0;W<x;W++)await ya(a[W],C,I,e,f[i[W]],b+i[W],y);gt("wasm prepareInputOutputTensor");for(let W=0;W<b;W++)s.setValue(k+W*o,T[W],"*"),s.setValue(A+W*o,c[t[W]],"*");for(let W=0;W<x;W++)s.setValue(D+W*o,C[W],"*"),s.setValue(V+W*o,f[i[W]],"*");if(m&&!_){let{handle:W,outputPreferredLocations:re,outputPreferredLocationsEncoded:ee}=m;if(c.length!==b)throw new Error(`input count from feeds (${b}) is expected to be always equal to model's input count (${c.length}).`);mt("wasm bindInputsOutputs");for(let K=0;K<b;K++){let ne=t[K];await s._OrtBindInput(W,c[ne],T[K])!==0&&me(`Can't bind input[${K}] for session=${e}.`)}for(let K=0;K<x;K++){let ne=i[K];a[K]?.[3]?s._OrtBindOutput(W,f[ne],C[K],0)!==0&&me(`Can't bind pre-allocated output[${K}] for session=${e}.`):s._OrtBindOutput(W,f[ne],0,ee[ne])!==0&&me(`Can't bind output[${K}] to ${re[K]} for session=${e}.`)}gt("wasm bindInputsOutputs"),ft.set(e,[d,c,f,m,y,!0])}s.jsepOnRunStart?.(d),s.webnnOnRunStart?.(d);let G;m?G=await s._OrtRunWithBinding(d,m.handle,x,D,$):G=await s._OrtRun(d,A,k,b,V,x,D,$),G!==0&&me("failed to call OrtRun().");let H=[],F=[];mt("wasm ProcessOutputTensor");for(let W=0;W<x;W++){let re=Number(s.getValue(D+W*o,"*"));if(re===C[W]){H.push(a[W]);continue}let ee=s.stackSave(),K=s.stackAlloc(4*o),ne=!1,Y,ye=0;try{s._OrtGetTensorData(re,K,K+o,K+2*o,K+3*o)!==0&&me(`Can't access output tensor data on index ${W}.`);let U=o===4?"i32":"i64",j=Number(s.getValue(K,U));ye=s.getValue(K+o,"*");let ae=s.getValue(K+o*2,"*"),pe=Number(s.getValue(K+o*3,U)),N=[];for(let be=0;be<pe;be++)N.push(Number(s.getValue(ae+be*o,U)));s._OrtFree(ae)!==0&&me("Can't free memory for tensor dims.");let le=N.reduce((be,we)=>be*we,1);Y=nt(j);let Ye=m?.outputPreferredLocations[i[W]];if(Y==="string"){if(Ye==="gpu-buffer"||Ye==="ml-tensor")throw new Error("String tensor is not supported on GPU.");let be=[];for(let we=0;we<le;we++){let Ce=s.getValue(ye+we*o,"*"),mi=s.getValue(ye+(we+1)*o,"*"),Vt=we===le-1?void 0:mi-Ce;be.push(s.UTF8ToString(Ce,Vt))}H.push([Y,N,be,"cpu"])}else if(Ye==="gpu-buffer"&&le>0){let be=s.jsepGetBuffer;if(!be)throw new Error('preferredLocation "gpu-buffer" is not supported without using WebGPU.');let we=be(ye),Ce=kt(j,le);if(Ce===void 0||!Xa(Y))throw new Error(`Unsupported data type: ${Y}`);ne=!0,H.push([Y,N,{gpuBuffer:we,download:s.jsepCreateDownloader(we,Ce,Y),dispose:()=>{s._OrtReleaseTensor(re)!==0&&me("Can't release tensor.")}},"gpu-buffer"])}else if(Ye==="ml-tensor"&&le>0){let be=s.webnnEnsureTensor,we=s.webnnIsGraphInputOutputTypeSupported;if(!be||!we)throw new Error('preferredLocation "ml-tensor" is not supported without using WebNN.');if(kt(j,le)===void 0||!Qa(Y))throw new Error(`Unsupported data type: ${Y}`);if(!we(e,Y,!1))throw new Error(`preferredLocation "ml-tensor" for ${Y} output is not supported by current WebNN Context.`);let Ce=await be(e,ye,j,N,!1);ne=!0,H.push([Y,N,{mlTensor:Ce,download:s.webnnCreateMLTensorDownloader(ye,Y),dispose:()=>{s.webnnReleaseTensorId(ye),s._OrtReleaseTensor(re)}},"ml-tensor"])}else if(Ye==="ml-tensor-cpu-output"&&le>0){let be=s.webnnCreateMLTensorDownloader(ye,Y)(),we=H.length;ne=!0,F.push((async()=>{let Ce=[we,await be];return s.webnnReleaseTensorId(ye),s._OrtReleaseTensor(re),Ce})()),H.push([Y,N,[],"cpu"])}else{let be=Xi(Y),we=new be(le);new Uint8Array(we.buffer,we.byteOffset,we.byteLength).set(s.HEAPU8.subarray(ye,ye+we.byteLength)),H.push([Y,N,we,"cpu"])}}finally{s.stackRestore(ee),Y==="string"&&ye&&s._free(ye),ne||s._OrtReleaseTensor(re)}}m&&!y&&(s._OrtClearBoundOutputs(m.handle)!==0&&me("Can't clear bound outputs."),ft.set(e,[d,c,f,m,y,!1]));for(let[W,re]of await Promise.all(F))H[W][2]=re;return gt("wasm ProcessOutputTensor"),H}finally{s.webnnOnRunEnd?.(d),s.stackRestore(z),T.forEach(G=>s._OrtReleaseTensor(G)),C.forEach(G=>s._OrtReleaseTensor(G)),I.forEach(G=>s._free(G)),$!==0&&s._OrtReleaseRunOptions($),w.forEach(G=>s._free(G))}},_n=e=>{let t=_e(),r=ft.get(e);if(!r)throw new Error("invalid session id");let i=r[0],a=t._OrtEndProfiling(i);a===0&&me("Can't get an profile file name."),t._OrtFree(a)},bn=e=>{let t=[];for(let r of e){let i=r[2];!Array.isArray(i)&&"buffer"in i&&t.push(i.buffer)}return t}}),ht,Pe,Nt,ni,si,Ni,_a,Pi,Ct,Tt,cd,rh,ah,nh,sh,oh,uh,lh,dh=L(()=>{We(),ih(),Rt(),Ka(),ht=()=>!!ge.wasm.proxy&&typeof document<"u",Nt=!1,ni=!1,si=!1,Pi=new Map,Ct=(e,t)=>{let r=Pi.get(e);r?r.push(t):Pi.set(e,[t])},Tt=()=>{if(Nt||!ni||si||!Pe)throw new Error("worker not ready")},cd=e=>{switch(e.data.type){case"init-wasm":Nt=!1,e.data.err?(si=!0,_a[1](e.data.err)):(ni=!0,_a[0]()),Ni&&(URL.revokeObjectURL(Ni),Ni=void 0);break;case"init-ep":case"copy-from":case"create":case"release":case"run":case"end-profiling":{let t=Pi.get(e.data.type);e.data.err?t.shift()[1](e.data.err):t.shift()[0](e.data.out);break}}},rh=async()=>{if(!ni){if(Nt)throw new Error("multiple calls to 'initWasm()' detected.");if(si)throw new Error("previous call to 'initWasm()' failed.");if(Nt=!0,ht())return new Promise((e,t)=>{Pe?.terminate(),ep().then(([r,i])=>{try{Pe=i,Pe.onerror=n=>t(n),Pe.onmessage=cd,_a=[e,t];let a={type:"init-wasm",in:ge};!a.in.wasm.wasmPaths&&(r||$a)&&(a.in.wasm.wasmPaths={wasm:new URL("/build/assets/ort-wasm-simd-threaded.jsep-BGTZ4Y7F.wasm",import.meta.url).href}),Pe.postMessage(a),Ni=r}catch(a){t(a)}},t)});try{await Ya(ge.wasm),await fn(ge),ni=!0}catch(e){throw si=!0,e}finally{Nt=!1}}},ah=async e=>{if(ht())return Tt(),new Promise((t,r)=>{Ct("init-ep",[t,r]);let i={type:"init-ep",in:{epName:e,env:ge}};Pe.postMessage(i)});await hn(ge,e)},nh=async e=>ht()?(Tt(),new Promise((t,r)=>{Ct("copy-from",[t,r]);let i={type:"copy-from",in:{buffer:e}};Pe.postMessage(i,[e.buffer])})):Yi(e),sh=async(e,t)=>{if(ht()){if(t?.preferredOutputLocation)throw new Error('session option "preferredOutputLocation" is not supported for proxy.');return Tt(),new Promise((r,i)=>{Ct("create",[r,i]);let a={type:"create",in:{model:e,options:{...t}}},n=[];e instanceof Uint8Array&&n.push(e.buffer),Pe.postMessage(a,n)})}else return mn(e,t)},oh=async e=>{if(ht())return Tt(),new Promise((t,r)=>{Ct("release",[t,r]);let i={type:"release",in:e};Pe.postMessage(i)});gn(e)},uh=async(e,t,r,i,a,n)=>{if(ht()){if(r.some(s=>s[3]!=="cpu"))throw new Error("input tensor on GPU is not supported for proxy.");if(a.some(s=>s))throw new Error("pre-allocated output tensor is not supported for proxy.");return Tt(),new Promise((s,o)=>{Ct("run",[s,o]);let l=r,d={type:"run",in:{sessionId:e,inputIndices:t,inputs:l,outputIndices:i,options:n}};Pe.postMessage(d,bn(l))})}else return yn(e,t,r,i,a,n)},lh=async e=>{if(ht())return Tt(),new Promise((t,r)=>{Ct("end-profiling",[t,r]);let i={type:"end-profiling",in:e};Pe.postMessage(i)});_n(e)}}),ba,fd,ph,yy=L(()=>{We(),dh(),ie(),Ha(),ap(),ba=(e,t)=>{switch(e.location){case"cpu":return[e.type,e.dims,e.data,"cpu"];case"gpu-buffer":return[e.type,e.dims,{gpuBuffer:e.gpuBuffer},"gpu-buffer"];case"ml-tensor":return[e.type,e.dims,{mlTensor:e.mlTensor},"ml-tensor"];default:throw new Error(`invalid data location: ${e.location} for ${t()}`)}},fd=e=>{switch(e[3]){case"cpu":return new He(e[0],e[2],e[1]);case"gpu-buffer":{let t=e[0];if(!Xa(t))throw new Error(`not supported data type: ${t} for deserializing GPU tensor`);let{gpuBuffer:r,download:i,dispose:a}=e[2];return He.fromGpuBuffer(r,{dataType:t,dims:e[1],download:i,dispose:a})}case"ml-tensor":{let t=e[0];if(!Qa(t))throw new Error(`not supported data type: ${t} for deserializing MLTensor tensor`);let{mlTensor:r,download:i,dispose:a}=e[2];return He.fromMLTensor(r,{dataType:t,dims:e[1],download:i,dispose:a})}default:throw new Error(`invalid data location: ${e[3]}`)}},ph=class{async fetchModelAndCopyToWasmMemory(e){return nh(await Ja(e))}async loadModel(e,t){Ke();let r;typeof e=="string"?r=await this.fetchModelAndCopyToWasmMemory(e):r=e,[this.sessionId,this.inputNames,this.outputNames,this.inputMetadata,this.outputMetadata]=await sh(r,t),Ue()}async dispose(){return oh(this.sessionId)}async run(e,t,r){Ke();let i=[],a=[];Object.entries(e).forEach(f=>{let m=f[0],y=f[1],_=this.inputNames.indexOf(m);if(_===-1)throw new Error(`invalid input '${m}'`);i.push(y),a.push(_)});let n=[],s=[];Object.entries(t).forEach(f=>{let m=f[0],y=f[1],_=this.outputNames.indexOf(m);if(_===-1)throw new Error(`invalid output '${m}'`);n.push(y),s.push(_)});let o=i.map((f,m)=>ba(f,()=>`input "${this.inputNames[a[m]]}"`)),l=n.map((f,m)=>f?ba(f,()=>`output "${this.outputNames[s[m]]}"`):null),d=await uh(this.sessionId,a,o,s,l,r),c={};for(let f=0;f<d.length;f++)c[this.outputNames[s[f]]]=n[f]??fd(d[f]);return Ue(),c}startProfiling(){}endProfiling(){lh(this.sessionId)}}}),ch={};qt(ch,{OnnxruntimeWebAssemblyBackend:()=>Na,initializeFlags:()=>Da,wasmBackend:()=>fh});var Da,Na,fh,_y=L(()=>{We(),dh(),yy(),Da=()=>{(typeof ge.wasm.initTimeout!="number"||ge.wasm.initTimeout<0)&&(ge.wasm.initTimeout=0);let e=ge.wasm.simd;if(typeof e!="boolean"&&e!==void 0&&e!=="fixed"&&e!=="relaxed"&&(console.warn(`Property "env.wasm.simd" is set to unknown value "${e}". Reset it to \`false\` and ignore SIMD feature checking.`),ge.wasm.simd=!1),typeof ge.wasm.proxy!="boolean"&&(ge.wasm.proxy=!1),typeof ge.wasm.trace!="boolean"&&(ge.wasm.trace=!1),typeof ge.wasm.numThreads!="number"||!Number.isInteger(ge.wasm.numThreads)||ge.wasm.numThreads<=0)if(typeof self<"u"&&!self.crossOriginIsolated)ge.wasm.numThreads=1;else{let t=typeof navigator>"u"?ag("node:os").cpus().length:navigator.hardwareConcurrency;ge.wasm.numThreads=Math.min(4,Math.ceil((t||1)/2))}},Na=class{async init(e){Da(),await rh(),await ah(e)}async createInferenceSessionHandler(e,t){let r=new ph;return await r.loadModel(e,t),r}},fh=new Na});We();We();We();var by="1.23.2",wy=Kd;{let e=(_y(),ci(ch)).wasmBackend;Et("webgpu",e,5),Et("webnn",e,5),Et("cpu",e,10),Et("wasm",e,10)}Object.defineProperty(ge.versions,"web",{value:by,enumerable:!0});/**
* @license
* Copyright 2021 Google LLC. All Rights Reserved.
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
* =============================================================================
*//**
 * @license
 * Copyright 2020 Google LLC. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =============================================================================
 *//**
 * @license
 * Copyright 2019 Google LLC. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =============================================================================
 */var hh=Object.freeze({__proto__:null,get InferenceSession(){return Ga},get TRACE(){return fi},get TRACE_EVENT_BEGIN(){return mt},get TRACE_EVENT_END(){return gt},get TRACE_FUNC_BEGIN(){return Ke},get TRACE_FUNC_END(){return Ue},get Tensor(){return He},default:wy,get env(){return ge},get registerBackend(){return Et}});let wn={verbose:!1,debug:!1,debugFolder:"out"},mh={mean:[.485,.456,.406],stdDeviation:[.229,.224,.225],maxSideLength:"auto",minimumAreaThreshold:20,paddingVertical:.4,paddingHorizontal:.6},Pa={imageHeight:48,strategy:"per-line",crossLineWidthFactor:1,minimumConfidence:.5,charactersDictionary:[],maxCropSourceSideLength:2e3,mainThreadYieldMs:0,recBatchSize:6,rotateVerticalCrops:!0,spaceRecovery:!1},vy=10,$y={executionProviders:["cpu"],graphOptimizationLevel:"all",enableCpuMemArena:!0,enableMemPattern:!0,executionMode:"sequential",interOpNumThreads:0,intraOpNumThreads:0},gh="opencv",xy={engine:gh},wa={model:{},detection:mh,recognition:Pa,debugging:wn,session:$y,processing:xy};function Ua(e,...t){if(!t.length)return e;let r=t.shift();if(Ui(e)&&Ui(r)){for(let i in r)if(Object.prototype.hasOwnProperty.call(r,i)){if(i==="__proto__"||i==="constructor"||i==="prototype")continue;let a=r[i],n=e[i];Ui(a)?((!n||!Ui(n))&&(e[i]={}),Ua(e[i],a)):a!==void 0&&(e[i]=a)}}return Ua(e,...t)}let hd=6e4;function Cy(e,t){if(!e)return null;let r=e.trim();if(r==="")return null;let i=Number(r);if(Number.isFinite(i))return i<=0?0:Math.min(i*1e3,hd);let a=Date.parse(r);return Number.isNaN(a)?null:Math.min(Math.max(a-t,0),hd)}function Ty(e){let t=500*2**e;return t+Math.random()*t}async function yh(e,t={}){const{timeoutMs:r=3e5,retries:i=2,fallbackUrl:a=Jm(e)}=t;let n;for(let o=0;o<=i;o++){let l=null;try{let d=await fetch(e,{signal:AbortSignal.timeout(r),referrerPolicy:"no-referrer"});if(!d.ok)throw l=Cy(d.headers.get("retry-after"),Date.now()),new Error(`HTTP ${d.status} ${d.statusText}`);return await d.arrayBuffer()}catch(d){n=d,o<i&&await new Promise(c=>setTimeout(c,l??Ty(o)))}}let s=new Error(`Failed to fetch ${e} after ${i+1} attempt(s): ${String(n)}`);if(!a)throw s;console.warn(`[ppu-paddle-ocr] ${s.message}
  retrying on ${a}`);try{return await yh(a,{timeoutMs:r,retries:i,fallbackUrl:null})}catch(o){throw new Error(`${s.message}; mirror also failed: ${String(o)}`,{cause:s})}}function Wa(e){return(typeof e=="string"?e:new TextDecoder("utf-8").decode(e)).split(/\r?\n/)}function Ui(e){return e!==null&&typeof e=="object"&&!Array.isArray(e)&&!(e instanceof Date)&&!(e instanceof RegExp)&&!(e instanceof ArrayBuffer)&&!ArrayBuffer.isView(e)}function Sy(e,t){return e!=="auto"?e:Math.min(1920,Math.max(960,Math.round(t*.75/32)*32))}function _h(e,t,r){let i=e,a=t,n=1;return Math.max(a,i)>r&&(n=r/(a>i?a:i),i=Math.round(i*n),a=Math.round(a*n)),{width:i,height:a,ratio:n}}function Iy(e,t,r,i,a){let n=Math.round(e.height*i),s=Math.round(e.height*a),o=e.x-s,l=e.y-n;o=Math.max(0,o),l=Math.max(0,l);let d=Math.min(t,e.x+e.width+s),c=Math.min(r,e.y+e.height+n),f=d-o,m=c-l;return{x:o,y:l,width:f,height:m}}function ky(e,t,r,i){let a=e.x/t,n=e.y/t,s=e.width/t,o=e.height/t,l=Math.max(0,Math.round(a)),d=Math.max(0,Math.round(n)),c=Math.min(r-l,Math.round(s)),f=Math.min(i-d,Math.round(o));return{x:l,y:d,width:c,height:f}}function Ey(e,t,r,i,a,n,s,o,l){let d=[];return e.iterate(c=>{let f=e.getRect(c);if(f.width*f.height<=s)return;let m=Iy(f,t,r,o,l),y=ky(m,i,a,n);y.width>5&&y.height>5&&d.push(y)}),d}function zy(e,t,r){let i=[];for(let a of e){const{bbox:n}=a;let s={x:Math.max(0,n.x0),y:Math.max(0,n.y0),width:n.x1-n.x0,height:n.y1-n.y0};s.x+s.width>t&&(s.width=t-s.x),s.y+s.height>r&&(s.height=r-s.y),s.width>5&&s.height>5&&i.push(s)}return i}let Ay=3;function Oy(e,t,r,i,a){let o=e.getContext("2d").getImageData(0,0,t,r).data,l=r*t,d=new Float32Array(Ay*l),c=i[0]??.485,f=i[1]??.456,m=i[2]??.406,y=a[0]??.229,_=a[1]??.224,b=a[2]??.225,x=1/(255*y),$=1/(255*_),w=1/(255*b),T=c/y,C=f/_,I=m/b,z=l,k=l*2;for(let A=0,D=0;A<l;A++,D+=4){let V=o[D],G=o[D+1],H=o[D+2];d[A]=V*x-T,d[z+A]=G*$-C,d[k+A]=H*w-I}return d}function md(e,t,r,i){let a=i(t,r),n=a.getContext("2d"),s=n.createImageData(t,r),o=s.data,l=t*r;for(let d=0;d<l;d++){let c=e[d]||0,f=Math.round(c*255),m=d*4;o[m]=f,o[m+1]=f,o[m+2]=f,o[m+3]=255}return n.putImageData(s,0,0),a}class bh{options;debugging;session;platform;engine;lastDetectionCanvas=null;constructor(t,r,i={},a={},n="opencv"){this.platform=t,this.session=r,this.options={...mh,...i},this.debugging={...wn,...a},n==="opencv"&&!this.platform.imageProcessor?this.engine="canvas-native":this.engine=n}log(t){this.debugging.verbose&&console.log(`[DetectionService] ${t}`)}async run(t){this.log("Starting text detection process");try{let r;this.platform.isCanvas(t)?r=t:this.engine==="opencv"&&this.platform.imageProcessor?r=await this.platform.imageProcessor.prepareCanvas(t):r=await this.platform.canvas.prepareCanvas(t);let i=await this.preprocessDetection(r),a=await this.runInference(i.tensor,i.width,i.height);if(!a)return console.error("Text detection failed (output tensor is null)"),[];let n=this.postprocessDetection(a,i);if(this.debugging.debug&&this.debugging.debugFolder&&this.lastDetectionCanvas)try{await this.debugDetectionCanvas(this.lastDetectionCanvas,i.width,i.height),await this.debugDetectedBoxes(r,n)}catch(s){this.log(`Debug dump failed: ${s instanceof Error?s.message:String(s)}`)}return this.log(`Detected ${n.length} text boxes in image`),n}catch(r){return console.error("Error during text detection:",r instanceof Error?r.message:String(r)),[]}}async preprocessDetection(t){const{width:r,height:i}=t;let a=Sy(this.options.maxSideLength??"auto",Math.max(r,i));const{width:n,height:s,ratio:o}=_h(r,i,a);let l=Math.ceil(n/32)*32,d=Math.ceil(s/32)*32,c=this.platform.createCanvas(l,d);c.getContext("2d").drawImage(t,0,0,r,i,0,0,n,s);let m=this.options.mean??[.485,.456,.406],y=this.options.stdDeviation??[.229,.224,.225],_=Oy(c,l,d,m,y);return this.log(`Detection preprocessed: original(${r}x${i}), model_input(${l}x${d}), resize_ratio: ${o.toFixed(4)}, engine: ${this.engine}`),{tensor:_,width:l,height:d,resizeRatio:o,originalWidth:r,originalHeight:i}}async runInference(t,r,i){let a;try{this.log("Running detection inference..."),a=new this.platform.ort.Tensor("float32",t,[1,3,i,r]);let n={x:a},o=(await this.session.run(n))[this.session.outputNames[0]||"sigmoid_0.tmp_0"];return this.log("Detection inference complete!"),o?o.data:(console.error(`Output tensor ${this.session.outputNames[0]} not found in detection results`),null)}catch(n){throw console.error("Error during model inference:",n instanceof Error?n.message:String(n)),n}finally{a?.dispose()}}postprocessDetection(t,r,i=this.options.minimumAreaThreshold??50,a=this.options.paddingVertical||.4,n=this.options.paddingHorizontal||.6){this.log("Post-processing detection results...");const{width:s,height:o,resizeRatio:l,originalWidth:d,originalHeight:c}=r;if(this.engine==="opencv"&&this.platform.imageProcessor)return this.lastDetectionCanvas=this.debugging.debug&&this.debugging.debugFolder?md(t,s,o,this.platform.createCanvas.bind(this.platform)):null,this.postprocessWithOpenCV(t,s,o,l,d,c,i,a,n);let f=md(t,s,o,this.platform.createCanvas.bind(this.platform));return this.lastDetectionCanvas=f,this.postprocessWithCanvasNative(f,l,d,c,i,a,n)}postprocessWithOpenCV(t,r,i,a,n,s,o,l,d){let c=this.platform.imageProcessor,f=new c.cv.Mat(i,r,c.cv.CV_8UC1),m=f.data,y=r*i;for(let b=0;b<y;b++){let x=t[b]||0;m[b]=Math.round(Math.min(Math.max(x,0),1)*255)}let _=new c.ImageProcessor(f);try{let b=new c.Contours(_.toMat(),{mode:c.cv.RETR_LIST,method:c.cv.CHAIN_APPROX_SIMPLE}),x=Ey(b,r,i,a,n,s,o,l,d);return b.destroy(),this.log(`Found ${x.length} potential text boxes (opencv)`),x}finally{_.destroy()}}postprocessWithCanvasNative(t,r,i,a,n,s,o){let d=this.platform.canvas.createProcessor(t).grayscale().threshold({thresh:0}).findRegions({foreground:"light",minArea:n,thresh:0,padding:{vertical:s,horizontal:o},scale:1/r}),c=zy(d,i,a);return this.log(`Found ${c.length} potential text boxes (canvas-native)`),c}async debugDetectionCanvas(t,r,i){let a=this.debugging.debugFolder??"";await this.platform.saveDebugImage(t,"detection-debug",a),this.log(`Probability map visualized and saved to: ${a}`)}async debugDetectedBoxes(t,r){let i=this.platform.isCanvas(t)?t:await this.platform.canvas.prepareCanvas(t),a=this.platform.createCanvas(i.width,i.height),n=a.getContext("2d");n.drawImage(i,0,0);for(let o of r){const{x:l,y:d,width:c,height:f}=o;this.platform.canvas.getToolkit().drawLine({ctx:n,x:l,y:d,width:c,height:f})}let s=this.debugging.debugFolder??"";await this.platform.saveDebugImage(a,"boxes-debug",s),this.log(`Boxes visualized and saved to: ${s}`)}}function gd(e){return e.reason instanceof Error?e.reason:new DOMException("The batch operation was aborted.","AbortError")}function Ry(e){if(Symbol.asyncIterator in e)return e[Symbol.asyncIterator]();let t=e[Symbol.iterator]();return{next:()=>Promise.resolve(t.next()),return:r=>Promise.resolve(t.return?.(r)??{done:!0,value:void 0})}}async function yd(e,t,r,i){const{settle:a,signal:n}=t;let s=Math.max(1,Math.floor(t.concurrency));if(n?.aborted)throw gd(n);let o=0,l=0,d=!1,c=!1,f,m=Array.isArray(e)?e:null,y=m?null:Ry(e),_=Promise.resolve(),b=async()=>{let w=_,T;_=new Promise(C=>{T=C}),await w;try{return await y.next()}finally{T()}},x=()=>{d=!0};n?.addEventListener("abort",x,{once:!0});let $=async()=>{for(;!d;){let w,T;if(m){if(o>=m.length)return;T=o++,w=m[T]}else{let C=await b();if(C.done||d)return;T=o++,w=C.value}try{let C=await r(w,T);if(d)return;i({index:T,status:"fulfilled",value:C})}catch(C){if(a)i({index:T,status:"rejected",reason:C});else{d=!0,c=!0,f=C;return}}finally{l++,t.onProgress?.(l,t.total)}}};try{await Promise.all(Array.from({length:s},()=>$()))}finally{n?.removeEventListener("abort",x),await y?.return?.()}if(n?.aborted)throw gd(n);if(c)throw f}function By(){let e=[],t=null,r=!1,i=null,a=()=>{let n=t;t=null,n?.()};return{push(n){e.push(n),a()},close(){r=!0,a()},fail(n){i={error:n},r=!0,a()},async*drain(){for(;;){for(;e.length>0;)yield e.shift();if(i)throw i.error;if(r)return;await new Promise(n=>{t=n})}}}}async function My(e,t,r,i){let a=e.canvas.getToolkit(),n=[];for(const[s,o]of r.entries()){let l=a.crop({bbox:{x0:o.x,y0:o.y,x1:o.x+o.width,y1:o.y+o.height},canvas:t});if(i.saveCropsTo&&e.saveImage){let d=`crop_${String(s).padStart(3,"0")}.png`;await e.saveImage(l,[i.saveCropsTo,d].join(e.pathSeparator))}i.crop&&n.push(await Dy(l))}return n}async function Dy(e){let t=e;if(typeof t.toBuffer=="function"){let r=t.toBuffer("image/png");return r.buffer.slice(r.byteOffset,r.byteOffset+r.byteLength)}if(typeof t.convertToBlob=="function")return(await t.convertToBlob({type:"image/png"})).arrayBuffer();if(typeof t.toBlob=="function"){let r=t.toBlob.bind(t);return(await new Promise((a,n)=>r(s=>s?a(s):n(new Error("Canvas toBlob() returned null")),"image/png"))).arrayBuffer()}throw new Error("Canvas cannot be encoded to a PNG buffer on this platform")}function Ny(e){if(e.length===0)return{text:"",results:[],confidence:0};let t=e.map(i=>i.text).join(" "),r=e.reduce((i,a)=>i+a.confidence,0)/e.length;return{text:t,results:e,confidence:r}}function Py(e){if(e.length===0)return{text:"",lines:[],confidence:0};let t=[],r=[],i=e[0];if(!i)return{text:"",lines:[],confidence:0};let a=i.box.y,n=i.box.height;for(let d of e){const{box:c}=d;Math.abs(c.y-a)<n/2?(r.push(d),n=(n*(r.length-1)+c.height)/r.length):(r.sort((f,m)=>f.box.x-m.box.x),t.push(r),r=[d],a=c.y,n=c.height)}r.length>0&&(r.sort((d,c)=>d.box.x-c.box.x),t.push(r));let s=t.map(d=>d.map(c=>c.text).join(" ")).join(`
`),o=t.reduce((d,c)=>d+c.reduce((f,m)=>f+m.confidence,0),0),l=t.reduce((d,c)=>d+c.length,0);return{text:s,lines:t,confidence:l>0?o/l:0}}function wh(e){if(e.length===0)return[];let t=[...e].sort((o,l)=>o.box.y-l.box.y||o.box.x-l.box.x),r=[],i=t[0];if(!i)return[];let a=[i],n=i.box.height,s=i.box.height;for(let o=1;o<t.length;o++){let l=t[o],d=t[o-1];if(!l||!d)continue;let c=Math.abs(l.box.y-d.box.y),f=s*.5;c<=f?(a.push(l),n+=l.box.height,s=n/a.length):(a.sort((m,y)=>m.box.x-y.box.x),r.push(a),a=[l],n=l.box.height,s=l.box.height)}return a.length>0&&(a.sort((o,l)=>o.box.x-l.box.x),r.push(a)),r}let Uy=4,_d=16384;function vh(e,t,r,i){let a=Math.min(...t.map(w=>w.box.x)),n=Math.min(...t.map(w=>w.box.y)),s=Math.max(...t.map(w=>w.box.x+w.box.width)),o=Math.max(...t.map(w=>w.box.y+w.box.height)),l={x:a,y:n,width:s-a,height:o-n},d=o-n,c=Math.max(1,Math.round(d*.4)),f=t.map(({box:w})=>Math.max(1,Math.round(w.width*Math.min(d/w.height,Uy)))),m=f.reduce((w,T)=>w+T,0)+c*(t.length-1);if(m>_d){let w=_d/m;f=f.map(T=>Math.max(1,Math.round(T*w))),c=Math.max(1,Math.floor(c*w))}let y=f.reduce((w,T)=>w+T,0)+c*(t.length-1),_=r(y,d),b=_.getContext("2d");b.fillStyle="white",b.fillRect(0,0,y,d);let x=0,$=[];for(let w=0;w<t.length;w++){let T=t[w],C=f[w];if(!T||C===void 0)continue;const{box:I}=T;let z=i.getToolkit().crop({bbox:{x0:I.x,y0:I.y,x1:I.x+I.width,y1:I.y+I.height},canvas:e});b.drawImage(z,0,0,I.width,I.height,x,0,C,d);let k=w<t.length-1?c:0;$.push(C+k),x+=C+k}return{mergedCanvas:_,mergedBox:l,cropWidths:$}}function $h(e,t,r){let i=[...e];if(t.length!==i.length||r.length===0)return Ly(e,r);let a=r.reduce((l,d)=>l+d,0),n=r.map(()=>""),s=0,o=(r[0]??0)/a;for(let l=0;l<i.length;l++){let d=t[l]??0;for(;d>=o&&s<r.length-1;)s++,o+=(r[s]??0)/a;n[s]+=i[l]??""}return n}let Wy=4;function Ly(e,t){if(t.length===1)return[e];let r=t.reduce((o,l)=>o+l,0),i=[...e],a=i.length>0?r/i.length:0,n=[],s=0;for(let o=0;o<t.length;o++){if(o===t.length-1){n.push(i.slice(s).join(""));break}let l=Math.min(s+Math.round((t[o]??0)/a),i.length),d=l,c=!1;for(let f=0;f<=Wy&&!c;f++)for(let m of[l-f,l+f]){let y=i[m];if(m>s&&m<i.length&&y!==void 0&&/\s/.test(y)){d=m,c=!0;break}}n.push(i.slice(s,d).join("")),s=c?d+1:d}return n}function qy(e,t,r,i){let a=[...e].sort((o,l)=>t(l)-t(o)),n=[],s=[];for(let o of a){let l=!1;for(let d=0;d<n.length;d++){let c=n[d],f=s[d];if(c===void 0||f===void 0)continue;let m=i*c.length;if(f+m+t(o)<=r){c.push(o),s[d]=f+t(o),l=!0;break}}l||(n.push([o]),s.push(t(o)))}return n}class xh{cache=new Map;maxSize;constructor(t=10){this.maxSize=t}get(t){let r=this.cache.get(t);if(r!==void 0)return this.cache.delete(t),this.cache.set(t,r),r}set(t,r){if(this.cache.has(t))this.cache.delete(t);else if(this.cache.size>=this.maxSize){let i=this.cache.keys().next().value;i!==void 0&&this.cache.delete(i)}this.cache.set(t,r)}clear(){this.cache.clear()}static generateKey(t){let r=new Uint8Array(t);if(r.length===0)return"0_0";let i=Math.min(r.length,Vy),a=r.length-1,n=0;for(let s=0;s<i;s++){let o=i===1?0:Math.round(s*a/(i-1));n=(n<<5)-n+(r[o]??0),n=n&n}return`${n}_${r.length}`}}let Vy=4096,bd=new xh;function wd(e){return!!(e?.noCache||e?.dictionary||e?.strategy!==void 0||e?.minimumConfidence!==void 0||e?.spaceRecovery!==void 0||e?.rotateVerticalCrops!==void 0||e?.recBatchSize!==void 0)}class jy{options=wa;detectionSession=null;recognitionSession=null;detector=null;recognitor=null;platform;constructor(t,r){this.platform=t,this.options=Ua({},wa,r),this.options.session=this.options.session||wa.session}log(t){this.options.debugging?.verbose&&console.log(`[PaddleOcrService:Base] ${t}`)}isInitialized(){return this.detectionSession!==null&&this.recognitionSession!==null}async destroy(){await this.detectionSession?.release(),await this.recognitionSession?.release(),this.detectionSession=null,this.recognitionSession=null,this.detector=null,this.recognitor=null}async recognize(t,r){(!this.detector||!this.recognitor)&&await this.initSessions();try{let i;if(typeof t=="string"){if(!t.startsWith("http")&&!t.startsWith("/"))throw new Error("Invalid image string format. Must be an HTTP URL, an absolute path, ArrayBuffer, or Canvas");i=await this.platform.loadResource(t,t)}else if(t instanceof ArrayBuffer)i=t;else if(typeof t.toBuffer=="function"){let y=t.toBuffer("image/png");i=y.buffer.slice(y.byteOffset,y.byteOffset+y.byteLength)}else{let m=t,b=m.getContext("2d",{willReadFrequently:!0}).getImageData(0,0,m.width,m.height).data;i=b.buffer.slice(b.byteOffset,b.byteOffset+b.byteLength)}let a=xh.generateKey(i);if(!wd(r)){let m=bd.get(a);if(m)return this.log("Using cached OCR result"),r?.flatten?{text:m.text,results:m.lines?m.lines.flat():m.results??[],confidence:m.confidence}:m}let n=[],s=typeof t=="string"||t instanceof ArrayBuffer?await this.platform.canvas.prepareCanvas(i):t;if(n=await this.detector.run(s),n.length===0)return r?.flatten?{text:"",results:[],confidence:0}:{text:"",lines:[],confidence:0};let o=this.options.recognition?.charactersDictionary;if(r?.dictionary){let m="";if(typeof r.dictionary=="string"){let y=await this.platform.loadResource(r.dictionary,r.dictionary);m=new TextDecoder("utf-8").decode(y)}else m=new TextDecoder("utf-8").decode(r.dictionary);if(o=Wa(m),o.length===0)throw new Error("Custom character dictionary is empty or could not be loaded.")}let l=r?.strategy??this.options.recognition?.strategy??"per-line",d=await this.recognitor.run(s,n,o,l,r),c=Py(d),f=r?.flatten?Ny(d):c;return wd(r)||bd.set(a,f),f}catch(i){if(this.options.debugging?.verbose){let a=i instanceof Error?i:new Error(String(i));console.error("recognize: error",a.message,a.stack)}throw i}}async detect(t,r){this.detector||await this.initSessions();const{crop:i,saveCropsTo:a,...n}=r??{};let s=Object.keys(n).length>0?new bh(this.platform,this.detectionSession,{...this.options.detection,...n},this.options.debugging,this.options.processing?.engine??gh):this.detector,o;if(typeof t=="string"){if(!t.startsWith("http")&&!t.startsWith("/"))throw new Error("Invalid image string format. Must be an HTTP URL, an absolute path, ArrayBuffer, or Canvas");o=await this.platform.canvas.prepareCanvas(await this.platform.loadResource(t,t))}else t instanceof ArrayBuffer?o=await this.platform.canvas.prepareCanvas(t):o=t;let l=(await s.run(o)).filter(c=>c.width>0&&c.height>0);if(!i&&!a)return{boxes:l};let d=await My(this.platform,o,l,{crop:i,saveCropsTo:a});return i?{boxes:l,crops:d}:{boxes:l}}async batchRecognize(t,r){let i=r?.settle??!1,a=[];return await yd(t,{concurrency:this.resolveConcurrency(r?.concurrency),settle:i,signal:r?.signal,onProgress:r?.onProgress,total:Array.isArray(t)?t.length:void 0},n=>this.recognize(n,r),n=>{a[n.index]=n}),i?a:a.map(n=>n.status==="fulfilled"?n.value:void 0)}async*batchRecognizeStream(t,r){let i=By(),a=(async()=>{try{await yd(t,{concurrency:this.resolveConcurrency(r?.concurrency),settle:r?.settle??!1,signal:r?.signal,onProgress:r?.onProgress,total:Array.isArray(t)?t.length:void 0},n=>this.recognize(n,r),n=>i.push(n)),i.close()}catch(n){i.fail(n)}})();yield*i.drain(),await a}resolveConcurrency(t){return typeof t=="number"&&t>0?Math.floor(t):(this.options.session?.executionProviders??[]).some(a=>{let n=(typeof a=="string"?a:a.name).toLowerCase();return n!=="cpu"&&n!=="wasm"})?1:4}}let vd=new Set(["cpu","wasm"]);function Fy(e){return typeof e=="string"?e:e.name}async function Gy(e,t,r,i,a){const{onSessionFallback:n,...s}=r??{};try{return await e.InferenceSession.create(t,s)}catch(o){let d=(s.executionProviders??[]).map(Fy);if(d.every(x=>vd.has(x))||d.length===0)throw o;let m=d.find(x=>vd.has(x))??(d.includes("wasm")?"wasm":"cpu"),y=o instanceof Error?o.message:String(o);i(`executionProviders=${JSON.stringify(d)} failed (${y}); falling back to ["${m}"].`);let _={...s,executionProviders:[m]};a?.({..._,onSessionFallback:n});let b=await e.InferenceSession.create(t,_);return n?.(o),b}}let La=null;function Hy(e){La=e}function Je(){if(!La)throw new Error('No canvas platform registered. Import "ppu-ocv" (Node), "ppu-ocv/web" (browser), "ppu-ocv/canvas" (Node canvas-only), "ppu-ocv/canvas-web" (browser canvas-only), or "ppu-ocv/canvas-mobile" (React Native / Skia) to auto-register.');return La}function Ky(e){return typeof e=="object"&&e!==null&&typeof e.getContext=="function"&&typeof e.width=="number"&&typeof e.height=="number"}let Ch={createCanvas(e,t){if(typeof OffscreenCanvas<"u")return new OffscreenCanvas(e,t);if(typeof document<"u"){let r=document.createElement("canvas");return r.width=e,r.height=t,r}throw new Error("No canvas implementation available in this environment.")},async loadImage(e){let t;if(e instanceof ArrayBuffer)t=new Blob([e]);else if(typeof e=="string")t=await(await fetch(e)).blob();else throw new Error("loadImage: unsupported source type");let r=await createImageBitmap(t),i=Ch.createCanvas(r.width,r.height);return i.getContext("2d").drawImage(r,0,0),r.close(),i},isCanvas(e){return typeof HTMLCanvasElement<"u"&&e instanceof HTMLCanvasElement||typeof OffscreenCanvas<"u"&&e instanceof OffscreenCanvas}};class Ut{static _baseInstance=null;step=0;constructor(){}static getInstance(){return Ut._baseInstance||(Ut._baseInstance=new Ut),Ut._baseInstance}crop(t){const{bbox:r,canvas:i}=t;let a=Je().createCanvas(r.x1-r.x0,r.y1-r.y0);return a.getContext("2d").drawImage(i,r.x0,r.y0,r.x1-r.x0,r.y1-r.y0,0,0,a.width,a.height),a}isDirty(t){const{canvas:r,threshold:i=127.5,majorColorThreshold:a=.97}=t;let n=0,s=0,o=this.crop({bbox:{x0:r.width*.1,y0:r.height*.1,x1:r.width*.9,y1:r.height*.9},canvas:r}),d=o.getContext("2d").getImageData(0,0,o.width,o.height).data;for(let f=0;f<d.length;f+=4){let m=d[f],y=d[f+1],_=d[f+2];m>=i&&y>=i&&_>=i?n++:s++}return Math.max(n,s)/(s+n)<a}drawLine(t){const{ctx:r,x:i,y:a,width:n,height:s,lineWidth:o=2,color:l="blue"}=t;r.beginPath(),r.strokeStyle=l,r.lineWidth=o,r.strokeRect(i,a,n,s),r.closePath()}drawContour(t){const{ctx:r,contour:i,strokeStyle:a="red",lineWidth:n=2}=t;let s=i.data32S;if(!(s.length<4)){r.strokeStyle=a,r.lineWidth=n,r.beginPath(),r.moveTo(s[0]??0,s[1]??0);for(let o=2;o<s.length;o+=2)r.lineTo(s[o]??0,s[o+1]??0);r.closePath(),r.stroke()}}}async function Yy(e){return Ky(e)?e:Je().loadImage(e)}async function Zy(e){if(e instanceof ArrayBuffer)return e;if(typeof e.toBuffer=="function"){let n=e.toBuffer("image/png"),s=new ArrayBuffer(n.byteLength);return new Uint8Array(s).set(new Uint8Array(n)),s}let t=e.toBlob;if(typeof t=="function")return(await new Promise((s,o)=>{t.call(e,l=>l?s(l):o(new Error("toBlob returned null")),"image/png")})).arrayBuffer();if(typeof e.convertToBlob=="function")return(await e.convertToBlob({type:"image/png"})).arrayBuffer();if(typeof e.toDataURL=="function"){let s=e.toDataURL("image/png").replace(/^data:image\/png;base64,/,""),o=atob(s),l=new ArrayBuffer(o.length),d=new Uint8Array(l);for(let c=0;c<o.length;c++)d[c]=o.charCodeAt(c);return l}let i=e.getContext("2d").getImageData(0,0,e.width,e.height),a=new ArrayBuffer(i.data.byteLength);return new Uint8Array(a).set(new Uint8Array(i.data.buffer,i.data.byteOffset,i.data.byteLength)),a}function Xy(e,t,r,i={}){const{foreground:a="light",thresh:n=127,minArea:s=1,maxArea:o=1/0,padding:l,scale:d=1}=i;let c=new Uint8Array(t*r),f=[],m=[[-1,-1],[0,-1],[1,-1],[-1,0],[1,0],[-1,1],[0,1],[1,1]],y=_=>{let b=e[_]??0;return a==="light"?b>n:b<=n};for(let _=0;_<r;_++)for(let b=0;b<t;b++){let x=_*t+b;if(c[x]||(c[x]=1,!y(x*4)))continue;let $=[x],w=b,T=b,C=_,I=_,z=0;for(;$.length>0;){let k=$.pop();if(k===void 0)break;z++;let A=k%t,D=(k-A)/t;A<w?w=A:A>T&&(T=A),D<C?C=D:D>I&&(I=D);for(const[V,G]of m){let H=A+V,F=D+G;if(H<0||H>=t||F<0||F>=r)continue;let W=F*t+H;c[W]||(c[W]=1,y(W*4)&&$.push(W))}}if(z>=s&&z<=o){let k=w,A=C,D=T+1,V=I+1;if(l){let G=V-A,H=Math.round(G*(l.vertical??0)),F=Math.round(G*(l.horizontal??0));k=Math.max(0,k-F),A=Math.max(0,A-H),D=Math.min(t,D+F),V=Math.min(r,V+H)}d!==1&&(k=Math.max(0,Math.round(k*d)),A=Math.max(0,Math.round(A*d)),D=Math.round(D*d),V=Math.round(V*d)),f.push({bbox:{x0:k,y0:A,x1:D,y1:V},area:z})}}return f}class $d{_canvas;constructor(t){this._canvas=t}get width(){return this._canvas.width}get height(){return this._canvas.height}resize(t){const{width:r,height:i}=t;let a=Je().createCanvas(r,i);return a.getContext("2d").drawImage(this._canvas,0,0,r,i),this._canvas=a,this}grayscale(){const{width:t,height:r}=this._canvas;let i=this._canvas.getContext("2d").getImageData(0,0,t,r),a=i.data;for(let s=0;s<a.length;s+=4){let o=Math.round(.299*(a[s]??0)+.587*(a[s+1]??0)+.114*(a[s+2]??0));a[s]=o,a[s+1]=o,a[s+2]=o}let n=Je().createCanvas(t,r);return n.getContext("2d").putImageData(i,0,0),this._canvas=n,this}convert(t={}){const{alpha:r=1,beta:i=0}=t;if(r===1&&i===0)return this;const{width:a,height:n}=this._canvas;let s=this._canvas.getContext("2d").getImageData(0,0,a,n),o=s.data;for(let d=0;d<o.length;d+=4)o[d]=Math.round((o[d]??0)*r+i),o[d+1]=Math.round((o[d+1]??0)*r+i),o[d+2]=Math.round((o[d+2]??0)*r+i);let l=Je().createCanvas(a,n);return l.getContext("2d").putImageData(s,0,0),this._canvas=l,this}invert(){const{width:t,height:r}=this._canvas;let i=this._canvas.getContext("2d").getImageData(0,0,t,r),a=i.data;for(let s=0;s<a.length;s+=4)a[s]=255-(a[s]??0),a[s+1]=255-(a[s+1]??0),a[s+2]=255-(a[s+2]??0);let n=Je().createCanvas(t,r);return n.getContext("2d").putImageData(i,0,0),this._canvas=n,this}threshold(t={}){const{thresh:r=127,maxValue:i=255}=t,{width:a,height:n}=this._canvas;let s=this._canvas.getContext("2d").getImageData(0,0,a,n),o=s.data;for(let d=0;d<o.length;d+=4){let f=(o[d]===o[d+1]&&o[d+1]===o[d+2]?o[d]??0:Math.round(.299*(o[d]??0)+.587*(o[d+1]??0)+.114*(o[d+2]??0)))>r?i:0;o[d]=f,o[d+1]=f,o[d+2]=f}let l=Je().createCanvas(a,n);return l.getContext("2d").putImageData(s,0,0),this._canvas=l,this}border(t={}){const{size:r=10,color:i="white"}=t,{width:a,height:n}=this._canvas;let s=Je().createCanvas(a+r*2,n+r*2),o=s.getContext("2d");return o.fillStyle=i,o.fillRect(0,0,s.width,s.height),o.drawImage(this._canvas,r,r),this._canvas=s,this}rotate(t){const{angle:r,cx:i=this._canvas.width/2,cy:a=this._canvas.height/2}=t;if(r===0)return this;const{width:n,height:s}=this._canvas;let o=Je().createCanvas(n,s),l=o.getContext("2d");return l.save(),l.translate(i,a),l.rotate(-r*Math.PI/180),l.drawImage(this._canvas,-i,-a),l.restore(),this._canvas=o,this}findRegions(t={}){const{width:r,height:i}=this._canvas;let a=this._canvas.getContext("2d").getImageData(0,0,r,i).data;return Xy(a,r,i,t)}toCanvas(){return this._canvas}static async prepareCanvas(t){return Yy(t)}static async prepareBuffer(t){return Zy(t)}}Hy(Ch);class vn{pathSeparator="/";ort=hh;createCanvas(t,r){let i=Je().createCanvas(t,r);return i.getContext.bind(i)("2d",{willReadFrequently:!0}),i}isCanvas(t){return!!t&&typeof t.getContext=="function"}async loadResource(t,r){if(t instanceof ArrayBuffer)return t;let i=typeof t=="string"?t:r,a=await fetch(i,{referrerPolicy:"no-referrer"});if(!a.ok)throw new Error(`Failed to fetch resource from ${i}`);return a.arrayBuffer()}async saveDebugImage(t,r,i){return Promise.resolve()}canvas={prepareCanvas:t=>$d.prepareCanvas(t),createProcessor:t=>new $d(t),getToolkit:()=>Ut.getInstance()}}function Qy(){return`https://cdn.jsdelivr.net/npm/onnxruntime-web@${ge.versions.web??ge.versions.common}/dist/`}function Th(){return typeof globalThis.WorkerGlobalScope=="function"}function Jy(){!(typeof window<"u"||Th())||ge.wasm.wasmPaths||(ge.wasm.wasmPaths=Qy())}Jy();async function e_(){if(typeof navigator>"u")return!1;let e=navigator;if(!e.gpu||typeof e.gpu.requestAdapter!="function")return!1;try{let t=await e.gpu.requestAdapter();return t!=null}catch{return!1}}async function t_(){return await e_()?["webgpu","wasm"]:["wasm"]}class xd extends bh{constructor(t,r={},i={}){super(new vn,t,r,i,"canvas-native")}}let i_=0,r_="<unk>",qa=8,a_=1.5,n_=2.5;function Cd(e){return new RegExp("\\p{L}","u").test(e)?0:new RegExp("\\p{N}","u").test(e)?1:2}function s_(e,t){if(e.length<4)return;let r=[];for(let s=1;s<t.length;s++)r.push((t[s]??0)-(t[s-1]??0));let i=[...r].sort((s,o)=>s-o),a=i[Math.floor(i.length/2)]??0;if(a<=0)return;let n=i.find(s=>s>0)??0;if(!(n<=0))for(let s=e.length-1;s>=1;s--){let o=t[s-1]??0,l=t[s]??0,d=Cd(e[s]??"")===Cd(e[s-1]??"")?n_:a_;l-o>a+d*n&&e[s]!==" "&&e[s-1]!==" "&&e[s]!==e[s-1]&&(e.splice(s,0," "),t.splice(s,0,(o+l)/2))}}let o_=65248,u_=/[\u2E80-\u9FFF\uAC00-\uD7AF\uF900-\uFAFF]/;function l_(e,t){for(let r=e.length-1;r>=1;r--)e[r]===" "&&e[r-1]===" "&&(e.splice(r,1),t.splice(r,1));if(!u_.test(e.join("")))for(let r=0;r<e.length;r++){let i=e[r]?.codePointAt(0)??0;i>=65281&&i<=65374?e[r]=String.fromCodePoint(i-o_):i===12288&&(e[r]=" ")}}function Sh(e,t,r,i,a=!1){let n=i.length,s=n-1,o=[],l=-1,d=0,c=0,f=[];for(let y=0;y<t;y++){let _=y*r,b=e[_],x=0;for(let $=1;$<r;$++){let w=e[_+$];w>b&&(b=w,x=$)}if(x===i_||x===l){l=x;continue}if(x>=0&&x<n){a&&x!==s&&(e[_+s]??0)>.001&&o[o.length-1]!==" "&&(o.push(" "),f.push((y+.5)/t));let $=i[x]??"";x===s?$!==r_&&(o.push(" "),d+=b,c++,f.push((y+.5)/t)):(o.push($),d+=b,c++,f.push((y+.5)/t))}l=x}s_(o,f),l_(o,f);let m=c>0?d/c:0;return{text:o.join(""),confidence:m,positions:f}}function d_(e){let t=e.length;for(;t>0&&e[t-1]==="";)t--;return e.slice(0,t)}function Ih(e,t){if(e.length===t)return e;let r=d_(e);if(r.length===t)return r;let i=["",...r[0]===""?r.slice(1):r];return i.length===t-1&&i.push(""),i}function p_(e,t,r,i=!1,a=!1){let n=e.data,s=e.dims,o=s[1],l=s[2]??r;if(!t)return{text:"",confidence:0,positions:[]};let d=Ih(t,l);return d.length!==l&&i&&console.warn(`Warning: Model output classes (${l}) does not match dictionary length (${t.length}).
 Consider using our model & dictionary catalogue at https://github.com/PT-Perkasa-Pilar-Utama/ppu-paddle-ocr-models.`),Sh(n,o,l,d,a)}function c_(e,t,r,i,a=!1){let n=Ih(i,r);return Sh(e,t,r,n,a)}async function kh(e,t,r,i){let a=e.width,n=e.height;if(n===0||a===0)throw new Error(`Crop dimensions are zero: ${a}x${n}`);let s=a/n,o=Math.max(qa,Math.round(t*s));if(r){let c=new r.ImageProcessor(e);try{c.resize({width:o,height:t});let f=c.toMat();return f.isContinuous()&&(f.channels()===4||f.channels()===1)?{imageTensor:h_(f,o,t),tensorWidth:o,tensorHeight:t}:{imageTensor:Eh(c.toCanvas(),o,t),tensorWidth:o,tensorHeight:t}}finally{c.destroy()}}let l=i(e).resize({width:o,height:t});return{imageTensor:f_(l,o,t),tensorWidth:o,tensorHeight:t}}function f_(e,t,r){let i=e.toCanvas();return Eh(i,t,r)}function Eh(e,t,r){let n=e.getContext("2d").getImageData(0,0,t,r).data,s=r*t,o=new Float32Array(3*s),l=1/127.5;for(let d=0,c=0;d<s;d++,c+=4)o[d]=(n[c]??0)*l-1;return o.copyWithin(s,0,s),o.copyWithin(s*2,0,s),o}function h_(e,t,r){let i=e.channels(),a=e.data,n=r*t,s=new Float32Array(3*n),o=1/127.5;for(let l=0,d=0;l<n;l++,d+=i)s[l]=a[d]*o-1;return s.copyWithin(n,0,n),s.copyWithin(n*2,0,n),s}async function $n(e,t,r){let i=t.options.imageHeight??48,a=Math.max(1,t.options.recBatchSize??6),n=r??t.options.charactersDictionary??[],s=t.options.spaceRecovery??!1,o=t.engine==="opencv"?t.platform.imageProcessor:void 0,l=await Promise.all(e.map(f=>kh(f,i,o,t.platform.canvas.createProcessor.bind(t.platform.canvas)))),d=l.map((f,m)=>m).sort((f,m)=>{let y=l[f]?.tensorWidth??0,_=l[m]?.tensorWidth??0;return y-_}),c=Array.from({length:e.length});for(let f=0;f<d.length;f+=a){let m=d.slice(f,f+a),y=Math.max(...m.map($=>l[$]?.tensorWidth??1)),_=i*y,b=new Float32Array(m.length*3*_);m.forEach(($,w)=>{let T=l[$];if(!T)return;let C=w*3*_;for(let I=0;I<3;I++)for(let z=0;z<i;z++){let k=(I*i+z)*T.tensorWidth,A=C+(I*i+z)*y;b.set(T.imageTensor.subarray(k,k+T.tensorWidth),A);let D=T.imageTensor[k+T.tensorWidth-1]??0;b.fill(D,A+T.tensorWidth,A+y)}});let x;try{x=new t.platform.ort.Tensor("float32",b,[m.length,3,i,y]);let $=await t.runInference(x);const[,w,T]=$.dims;let C=$.data,I=(w??0)*(T??0);m.forEach((z,k)=>{let A=(l[z]?.tensorWidth??y)/y,D=Math.max(1,Math.min(w??0,Math.ceil((w??0)*A)));c[z]=c_(C.subarray(k*I,k*I+D*(T??0)),D,T??0,n,s)})}finally{x?.dispose()}}return c}function m_(e){let r=e.inputMetadata?.[0]?.shape?.[0];return typeof r!="number"||r<0}function xn(e,t){if(!(t.options.rotateVerticalCrops??!0)||e.height/e.width<1.5)return e;let r=t.platform.createCanvas(e.height,e.width),i=r.getContext("2d");return i.translate(0,e.width),i.rotate(-Math.PI/2),i.drawImage(e,0,0),r}function Qi(e,t,r){return r.getToolkit().crop({bbox:{x0:t.x,y0:t.y,x1:t.x+t.width,y1:t.y+t.height},canvas:e})}async function g_(e,t,r){let i=t.options.imageHeight??48,a=t.engine==="opencv"?t.platform.imageProcessor:void 0;const{imageTensor:n,tensorWidth:s,tensorHeight:o}=await kh(e,i,a,t.platform.canvas.createProcessor.bind(t.platform.canvas));let l;try{l=new t.platform.ort.Tensor("float32",n,[1,3,o,s]);let d=await t.runInference(l),c=r??t.options.charactersDictionary??[];return p_(d,c,s,t.debugging.verbose)}finally{l?.dispose()}}function Zi(e){return[...e].sort((t,r)=>Math.abs(t.box.y-r.box.y)<(t.box.height+r.box.height)/4?t.box.x-r.box.x:t.box.y-r.box.y)}async function y_(e,t,r,i,a){let n=r.debugging.debugFolder?`${r.debugging.debugFolder}${r.platform.pathSeparator}crops`:"";if(r.debugging.debug&&n){let o=r.platform.canvas.getToolkit();"clearOutput"in o&&typeof o.clearOutput=="function"&&o.clearOutput(n)}if(!r.debugging.debug){let o=t.map(({box:c})=>xn(Qi(e,c,r.platform.canvas),r)),l=await $n(o,r,a),d=t.map(({box:c},f)=>({text:l[f]?.text??"",box:c,confidence:l[f]?.confidence??0}));return Zi(d)}let s=[];for(const{box:o,index:l}of t){let d=await i(e,o,l,t.length,n,a);d!==null&&s.push(d)}return Zi(s)}async function __(e,t,r,i){let a=wh(t),n=[],s=[];for(let d of a){let c=d[0];if(c)if(d.length===1)s.push(xn(Qi(e,c.box,r.platform.canvas),r)),n.push({lineBoxes:d,cropWidths:null});else{const{mergedCanvas:f,cropWidths:m}=vh(e,d,r.platform.createCanvas.bind(r.platform),r.platform.canvas);s.push(f),n.push({lineBoxes:d,cropWidths:m})}}let o=await $n(s,r,i),l=[];return n.forEach((d,c)=>{let f=o[c];if(f)if(d.cropWidths===null){let m=d.lineBoxes[0];m&&l.push({text:f.text,box:m.box,confidence:f.confidence})}else{let m=$h(f.text,f.positions,d.cropWidths);for(let y=0;y<d.lineBoxes.length;y++){let _=d.lineBoxes[y];_&&l.push({text:(m[y]??"").trim(),box:_.box,confidence:f.confidence})}}}),Zi(l)}async function b_(e,t,r,i){let a=wh(t),n=r.options.imageHeight??48,s=20,o=[];for(let _ of a)if(_.length===1){let b=_[0];if(!b)continue;let x=Qi(e,b.box,r.platform.canvas);o.push({canvas:x,boxes:_,cropWidths:[x.width]})}else{const{mergedCanvas:b,cropWidths:x}=vh(e,_,r.platform.createCanvas.bind(r.platform),r.platform.canvas);o.push({canvas:b,boxes:_,cropWidths:x})}let l=o.map(({canvas:_,boxes:b,cropWidths:x},$)=>{let w=_.width/_.height,T=Math.max(qa,Math.round(n*w));return{canvas:_,boxes:b,cropWidths:x,resizedWidth:T,originalHeight:_.height,index:$}}),d=Math.max(...l.map(_=>_.resizedWidth)),c=r.options.crossLineWidthFactor??1.5,f=Math.round(d*c),m=qy(l,_=>_.resizedWidth,f,s),y=[];for(let _ of m){let b=[..._].sort((F,W)=>F.index-W.index),x=Math.max(...b.map(F=>F.originalHeight)),$=b.map(F=>{if(F.originalHeight>=x)return F.resizedWidth;let W=x/F.originalHeight;return Math.max(qa,Math.round(F.resizedWidth*W))}),T=$.reduce((F,W)=>F+W,0)+s*(b.length-1),C=r.platform.createCanvas(T,n),I=C.getContext("2d");I.fillStyle="white",I.fillRect(0,0,T,n);let z=0;for(let F=0;F<b.length;F++){let W=b[F],re=$[F];W===void 0||re===void 0||(I.drawImage(W.canvas,0,0,W.canvas.width,W.canvas.height,z,0,re,n),z+=re,F<b.length-1&&(z+=s))}const{text:k,confidence:A,positions:D}=await g_(C,r,i);let V=[],G=[];for(let F=0;F<b.length;F++){let W=b[F],re=$[F];if(!W||re===void 0)continue;let ee=re/W.canvas.width;for(let K=0;K<W.boxes.length;K++){let ne=W.boxes[K];if(!ne)continue;let Y=(W.cropWidths[K]??0)*ee;K===W.boxes.length-1&&F<b.length-1&&(Y+=s),V.push(Y),G.push(ne)}}let H=$h(k,D,V);for(let F=0;F<G.length;F++){let W=G[F];W&&y.push({text:(H[F]??"").trim(),box:W.box,confidence:A})}}return Zi(y)}class w_{options;debugging;session;platform;engine;constructor(t,r,i={},a={},n="opencv"){this.platform=t,this.session=r,this.options={...Pa,...i},this.debugging={...wn,...a},n==="opencv"&&!this.platform.imageProcessor?this.engine="canvas-native":this.engine=n}log(t){this.debugging.verbose&&console.log(`[RecognitionService] ${t}`)}async run(t,r,i,a="per-line",n){this.log("Starting text recognition process");try{let s;this.platform.isCanvas(t)?s=t:this.engine==="opencv"&&this.platform.imageProcessor?s=await this.platform.imageProcessor.prepareCanvas(t):s=await this.platform.canvas.prepareCanvas(t);let o=this.filterValidBoxes(r);if(o.length===0)return[];const{canvas:l,ratio:d}=this.buildCropCanvas(s);let c=d===1?o:o.map(_=>({..._,box:Sd(_.box,d)})),f=this.buildContext(n),m;switch(a){case"cross-line":m=await b_(l,c,f,i);break;case"per-line":m=await __(l,c,f,i);break;case"per-box":default:m=await y_(l,c,f,(_,b,x,$,w,T)=>this.processBox(_,b,x,$,w,f,T),i)}d!==1&&(m=m.map(_=>({..._,box:Sd(_.box,1/d)})));let y=n?.minimumConfidence??this.options.minimumConfidence??Pa.minimumConfidence??Td;return y>Td?m.filter(_=>{let b=/[\p{L}\p{N}]/u.test(_.text)?y:Math.min(v_,y+x_);return _.confidence>=b}):m}catch(s){return console.error("Error during text recognition:",s instanceof Error?s.message:String(s)),[]}}buildContext(t){let r={...this.options,...t?.spaceRecovery!==void 0?{spaceRecovery:t.spaceRecovery}:{},...t?.rotateVerticalCrops!==void 0?{rotateVerticalCrops:t.rotateVerticalCrops}:{},...t?.recBatchSize!==void 0?{recBatchSize:t.recBatchSize}:{}};return{platform:this.platform,options:m_(this.session)?r:{...r,recBatchSize:$_},debugging:this.debugging,engine:this.engine,runInference:i=>this.runInference(i)}}filterValidBoxes(t){return t.map((r,i)=>({box:r,index:i})).filter(({box:r,index:i})=>this.isValidBox(r,i))}buildCropCanvas(t){const{width:r,height:i}=t;let a=this.options.maxCropSourceSideLength??2e3;const{width:n,height:s,ratio:o}=_h(r,i,a);if(o===1)return{canvas:t,ratio:1};let l=this.platform.createCanvas(n,s);return l.getContext("2d").drawImage(t,0,0,r,i,0,0,n,s),{canvas:l,ratio:o}}async processBox(t,r,i,a,n,s,o){let l=Date.now();try{let d=xn(Qi(t,r,this.platform.canvas),s);const[c]=await $n([d],s,o);let f=c?.text??"",m=c?.confidence??0;if(this.debugging.debug&&n){await this.platform.saveDebugImage(d,`crop_${String(i).padStart(3,"0")}.png`,n);let y=Date.now()-l;this.log(`Box ${i+1}/${a}: [x:${r.x}, y:${r.y}, w:${r.width}, h:${r.height}]
	 -> "${f}" (processed in ${y}ms)
`)}return{text:f,box:r,confidence:m}}catch(d){let c=d instanceof Error?d:new Error(String(d));return console.error(`Error processing box ${i+1}: ${c.message}`,c.stack),null}}isValidBox(t,r){return t.width<=0||t.height<=0?(console.warn(`Skipping invalid box ${r+1}: w=${t.width}, h=${t.height}`),!1):!0}async runInference(t){let r=this.options.mainThreadYieldMs??0;r>0&&await new Promise(o=>setTimeout(o,r));let i={x:t},a=await this.session.run(i),n=Object.keys(a)[0],s=n?a[n]:void 0;if(!s)throw new Error(`Recognition output tensor '${n}' not found. Available keys: ${Object.keys(a)}`);return s}}let Td=0,v_=1,$_=1,x_=.3;function Sd(e,t){return{x:Math.round(e.x*t),y:Math.round(e.y*t),width:Math.max(1,Math.round(e.width*t)),height:Math.max(1,Math.round(e.height*t))}}function C_(e,t=typeof window<"u"&&!Th()){return t?{mainThreadYieldMs:vy,...e}:e}class Id extends w_{constructor(t,r={},i={}){super(new vn,t,C_(r),i,"canvas-native")}}let T_={graphOptimizationLevel:"all"};class S_ extends jy{constructor(t){super(new vn,t),(this.options.session===void 0||Object.keys(this.options.session).length===0)&&(this.options.session=T_)}async initSessions(){throw new Error("Initialization is handled proactively in PaddleOcrService. Call initialize() instead.")}async _loadResource(t,r){if(t instanceof ArrayBuffer)return this.log("Loading resource from ArrayBuffer"),t;let i=typeof t=="string"?t:r;return this.log(`Fetching resource from URL: ${i}`),yh(i)}async _resolveSessionExecutionProviders(){let t=this.options.session??{};if(t.executionProviders&&t.executionProviders.length>0){this.log(`Using user-provided executionProviders: ${JSON.stringify(t.executionProviders)}`);return}let r=await t_();this.options.session={...t,executionProviders:r},this.log(`Resolved executionProviders: ${JSON.stringify(r)}`)}async _createSession(t){return Gy(hh,t,this.options.session,r=>console.warn(`[PaddleOcrService] ${r}`),r=>this.options.session=r)}async initialize(){try{this.log("Initializing PaddleOcrService (Web)..."),await this._resolveSessionExecutionProviders();const[t,r,i]=await Promise.all([this._loadResource(this.options.model?.detection,Dt.detection),this._loadResource(this.options.model?.recognition,Dt.recognition),this._loadResource(this.options.model?.charactersDictionary,Dt.charactersDictionary)]),[a,n]=await Promise.all([this._createSession(new Uint8Array(t)),this._createSession(new Uint8Array(r))]);this.detectionSession=a,this.recognitionSession=n,this.options.model&&(this.options.model.detection=t),this.options.model&&(this.options.model.recognition=r),this.log(`Detection ONNX model loaded successfully
	input: ${a.inputNames}
	output: ${a.outputNames}`),this.log(`Recognition ONNX model loaded successfully
	input: ${n.inputNames}
	output: ${n.outputNames}`);let s=Wa(i);if(s.length===0)throw new Error("Character dictionary is empty or could not be loaded.");this.options.model&&(this.options.model.charactersDictionary=i),this.options.recognition&&(this.options.recognition.charactersDictionary=s),this.log(`Character dictionary loaded with ${s.length} entries.`),this.detector=new xd(a,this.options.detection,this.options.debugging),this.recognitor=new Id(n,this.options.recognition,this.options.debugging),this.options.model&&(this.options.model.detection=void 0),this.options.model&&(this.options.model.recognition=void 0)}catch(t){throw console.error("Failed to initialize PaddleOcrService Web:",t),t}}async changeDetectionModel(t){this.log("Changing detection model...");let r=await this._loadResource(t,Dt.detection);await this.detectionSession?.release(),this.detectionSession=await this._createSession(new Uint8Array(r)),this.detector=new xd(this.detectionSession,this.options.detection,this.options.debugging),this.options.model&&(this.options.model.detection=r),this.log("Detection model changed successfully.")}async changeRecognitionModel(t){this.log("Changing recognition model...");let r=await this._loadResource(t,Dt.recognition);await this.recognitionSession?.release(),this.recognitionSession=await this._createSession(new Uint8Array(r)),this.recognitor=new Id(this.recognitionSession,this.options.recognition,this.options.debugging),this.options.model&&(this.options.model.recognition=r),this.log("Recognition model changed successfully.")}async changeTextDictionary(t){this.log("Changing text dictionary...");let r=await this._loadResource(t,Dt.charactersDictionary),i=Wa(r);if(i.length===0)throw new Error("Character dictionary is empty or could not be loaded.");this.options.model&&(this.options.model.charactersDictionary=r),this.options.recognition&&(this.options.recognition.charactersDictionary=i),this.log(`Character dictionary changed successfully with ${i.length} entries.`)}async recognize(t,r){return super.recognize(t,r)}}let Va=null,va=null;async function kd(){va||(va=(async()=>{Va=new S_({model:Ed,recognition:{mainThreadYieldMs:0}}),await Va.initialize((e,t)=>{self.postMessage({type:"progress",done:e,total:t})})})()),await va}self.onmessage=async e=>{const{type:t}=e.data;try{if(t==="init"){await kd(),self.postMessage({type:"ready"});return}if(t==="recognize"){await kd();const{id:r,width:i,height:a,buffer:n}=e.data,s=new OffscreenCanvas(i,a),o=s.getContext("2d"),l=new ImageData(new Uint8ClampedArray(n),i,a);o.putImageData(l,0,0);const d=await Va.recognize(s),c=(d.lines||[]).map(y=>y.text).filter(Boolean),f=(d.text||c.join(`
`)).trim(),m=typeof d.confidence=="number"?d.confidence:c.length?c.reduce((y,_)=>y+(_.confidence||0),0)/c.length:null;self.postMessage({type:"result",id:r,text:f,confidence:m})}}catch(r){self.postMessage({type:"error",message:r?.message||"Falha no OCR em tempo real."})}};
