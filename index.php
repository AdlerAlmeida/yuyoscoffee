<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Yuyos Coffee — Inventario y Ventas</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body{font-family:Arial,system-ui;background:#f3f3f3;margin:0;padding:0}
    header{background:#8B4513;color:white;padding:12px 18px;display:flex;gap:18px;align-items:center}
    header a{color:white;text-decoration:none;margin-right:14px;font-weight:700;cursor:pointer}
    .brand{font-weight:800}
    .wrap{max-width:1100px;margin:18px auto;padding:16px}
    .panel{background:#fff;padding:16px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
    .hidden{display:none}
    table{width:100%;border-collapse:collapse;margin-top:14px}
    th,td{border:1px solid #ddd;padding:8px}
    th{background:#8B4513;color:white}
    .cardGrid{display:flex;flex-wrap:wrap;gap:18px;margin-top:12px}
    .cardItem{width:130px;text-align:center;cursor:pointer}
    .cardItem img{width:100%;height:100px;border-radius:8px;object-fit:cover}
    .btn{background:#8B4513;color:white;padding:7px 12px;border:none;border-radius:6px;cursor:pointer}
    .btn.small{padding:4px 8px;font-size:13px}
    .danger{background:#c0392b}
    .success{background:#27ae60}
    .muted{color:#666;font-size:14px}
    .totalBox{margin-top:12px;text-align:right;font-weight:700}
    @media (max-width:700px){ .cardItem{width:120px} table, th, td{font-size:13px} }
  </style>

</head>
<body>

<header>
  <div class="brand">Yuyo's Coffee</div>
  <a onclick="show('inv')" class="menu"> Inventario  </a>
  <a onclick="show('ventas')" class="menu">  Atender</a>
</header>

<div class="wrap">

  <!-- INVENTARIO -->
  <div id="inv" class="panel hidden">
    <h2>Inventario</h2>

    <h3> Café</h3>
    <table>
      <thead><tr><th>Ingrediente</th><th>Cantidad</th><th>Mínimo</th><th>Estado</th></tr></thead>
      <tbody id="cafeSection"></tbody>
    </table>

    <h3> Frappe / Esencias / Hielo</h3>
    <table>
      <thead><tr><th>Ingrediente</th><th>Cantidad</th><th>Mínimo</th><th>Estado</th></tr></thead>
      <tbody id="frappeSection"></tbody>
    </table>

    <h3> Galletas</h3>
    <table>
      <thead><tr><th>Ingrediente</th><th>Cantidad</th><th>Mínimo</th><th>Estado</th></tr></thead>
      <tbody id="galletaSection"></tbody>
    </table>

    <div style="margin-top:12px">
      <button class="btn" onclick="saveInventory()">Guardar inventario</button>
      <button class="btn" onclick="resetInventory()">Restablecer inicial</button>
      <h3 style="margin-top:20px">Agregar producto al inventario</h3>
<div class="panel" style="padding:15px; margin-top:10px;">
  <input id="newClave" placeholder="Clave (ej: cafe_arabica)" style="padding:6px; width:180px;">
  <input id="newNombre" placeholder="Nombre" style="padding:6px; width:180px;">
  <input id="newCantidad" type="number" placeholder="Cantidad inicial" style="padding:6px; width:150px;">
  <input id="newMin" type="number" placeholder="Mínimo" style="padding:6px; width:150px;">

  <select id="newCategoria" style="padding:6px; width:160px;">
    <option value="">Categoría</option>
    <option value="cafe">Café</option>
    <option value="frappe">Frappe / Esencias / Hielo</option>
    <option value="galleta">Galletas</option>
  </select>

  <button class="btn success" onclick="agregarProducto()">Guardar producto</button>
</div>

      <span class="muted" style="margin-left:12px">Los cambios se guardan en tu navegador (localStorage).</span>
    </div>
  </div>

  <!-- VENTAS -->
  <div id="ventas" class="panel" style="margin-top:16px">
    <h2 class="titulo">Atender Cliente</h2>

    <h3 class="subtitulo">Selecciona producto</h3>
    <div id="productGrid" class="cardGrid"></div>

    <div id="saborBox" style="display:none; margin-top:16px;">
      <h3>Selecciona sabor</h3>
      <div id="saborGrid" class="cardGrid"></div>
    </div>

    <div id="tamanoBox" style="display:none; margin-top:16px;">
      <h3>Selecciona tamaño (Café / Frappe)</h3>
      <div id="tamanoGrid" class="cardGrid"></div>
    </div>

    <div id="unidadBox" style="display:none; margin-top:16px;">
      <h3>Unidades</h3>
      <input id="unidades" type="number" min="1" placeholder="Ej. 2" style="padding:10px; width:120px;">
      <button class="btn" onclick="agregarOrden()">Agregar</button>
    </div>

    <h3 style="margin-top:20px">Orden actual</h3>
    <table>
      <thead>
        <tr>
          <th>Producto</th>
          <th>Sabor</th>
          <th>Tamaño</th>
          <th>Unidades</th>
          <th>Precio unidad</th>
          <th>Precio línea</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="ordenTable"></tbody>
    </table>

    <div class="totalBox">Total orden: $<span id="totalOrder">0.00</span></div>
  </div>
</div>

<script class="punche">

/* Productos y sabores */
const productos = [
  { nombre:'Café', sabores:['Americano' ,'Latte','Capuchino'], img:'cafe.jpg'},
  { nombre:'Frappe', sabores:['Moka','Oreo','Caramelo'], img:'frappe.jpg' },
  { nombre:'Galleta', sabores:['Chocolate','Avena','Vainilla'], img:'galleta.jpg' }
];

/* Precios por tamaño / producto */
const precios = {
  'Café': {Chico:45, Mediano:60, Jumbo:75},
  'Frappe': {Chico:80, Mediano:120, Jumbo:160},
  'Galleta': 20
};

/* Recetas base (multiplicar por sizeMul y por unidades) */
const recetas = {
  'Café': {
    'Americano': {cafe:1, agua:1,},
    'Latte': {cafe:1, leche:1},
    'Capuchino': {cafe:1, leche:1}
  },
  'Frappe': {
    'Moka': {cafe:1, leche:150, moka:50, hielo:5},
    'Oreo': {cafe:1, leche:120, galleta_oreo:1, hielo:5},
    'Caramelo': {cafe:1, leche:150, caramelo:40, hielo:5}
  },
  'Galleta': {
    'Chocolate': {galleta_choc:1},
    'Avena': {galleta_avena:1},
    'Vainilla': {galleta_vainilla:1}
  }
};

/* Multiplicadores por tamaño */
const sizeMul = {Chico:1, Mediano:1.5, Jumbo:2};

/* Estado de venta */
let seleccion = { producto:null, sabor:null, tamano:null };
let orden = [];

/* ----------------- UI: mostrar panel ----------------- */
function show(id){
  document.getElementById('inv').classList.add('hidden');
  document.getElementById('ventas').classList.add('hidden');
  document.getElementById(id).classList.remove('hidden');
}

/* ----------------- Inventario DESDE LA BASE DE DATOS ----------------- */
function loadInventory(){
  fetch("obtener.php")
    .then(res => res.json())
    .then(data => {
      inventario = data;
      renderInventory();
    })
    .catch(err => {
      alert("Error cargando inventario desde la base de datos");
      console.error(err);
    });
}

function saveInventory(){
  localStorage.setItem(STORAGE_KEY, JSON.stringify(inventario));
  alert('Inventario guardado localmente.');
}

function resetInventory(){
  if(!confirm('Restablecer inventario a valores iniciales?')) return;
  inventario = JSON.parse(JSON.stringify(defaultInventory));
  renderInventory();
  localStorage.setItem(STORAGE_KEY, JSON.stringify(inventario));
  alert('Inventario restablecido.');
}

function renderInventory(){
  const cafeEl = document.getElementById('cafeSection');
  const frappeEl = document.getElementById('frappeSection');
  const galletaEl = document.getElementById('galletaSection');
  cafeEl.innerHTML = frappeEl.innerHTML = galletaEl.innerHTML = '';
  for(const key in inventario){
    const it = inventario[key];
    const estado = it.cantidad <= it.min ? 'Reabastecer' : 'OK';
    const row = `<tr><td>${it.nombre}</td><td>${it.cantidad}</td><td>${it.min}</td><td>${estado}</td></tr>`;
    if(it.cat === 'cafe') cafeEl.innerHTML += row;
    if(it.cat === 'frappe') frappeEl.innerHTML += row;
    if(it.cat === 'galleta') galletaEl.innerHTML += row;
  }
}


/* ----------------- Productos UI (ventas) ----------------- */
function renderProducts(){
  const grid = document.getElementById('productGrid'); grid.innerHTML = '';
  productos.forEach(p=>{
    const d = document.createElement('div'); d.className = 'cardItem';
    d.innerHTML = `<img src='${p.img}' alt='${p.nombre}'/><p><strong>${p.nombre}</strong></p>`;
    d.onclick = () => seleccionarProducto(p.nombre);
    grid.appendChild(d);
  });
}

function seleccionarProducto(prod){
  seleccion.producto = prod;
  seleccion.sabor = null;
  seleccion.tamano = null;

  document.getElementById('saborBox').style.display = '';
  document.getElementById('tamanoBox').style.display = 'none';
  document.getElementById('unidadBox').style.display = 'none';

  const sabores = productos.find(x=>x.nombre===prod).sabores;
  const grid = document.getElementById('saborGrid'); grid.innerHTML = '';
  sabores.forEach(s=>{
    const d = document.createElement('div'); d.className = 'cardItem';
    d.innerHTML = 
    ` <img src='img/sabores/${s.toLowerCase()}.jpg 'alt='${s}'/><p>${s}</p>`;
    d.onclick = () => seleccionarSabor(s);
    grid.appendChild(d);
  });
}

function seleccionarSabor(s){
  seleccion.sabor = s;
  if(seleccion.producto === 'Frappe' || seleccion.producto === 'Café'){
    document.getElementById('tamanoBox').style.display = '';
    const grid = document.getElementById('tamanoGrid'); grid.innerHTML = '';
    ['Chico','Mediano','Jumbo'].forEach(t=>{
      const d = document.createElement('div'); d.className = 'cardItem';
      d.innerHTML = `
      <img src='img/tamanos/${t.toLowerCase()}.jpg' alt='${t}'/><p>${t}</p>`;
      d.onclick = () => seleccionarTamano(t);
      grid.appendChild(d);
    });
    document.getElementById('unidadBox').style.display = 'none';
  } else {
    seleccion.tamano = null;
    document.getElementById('tamanoBox').style.display = 'none';
    document.getElementById('unidadBox').style.display = '';
  }
}

function seleccionarTamano(t){
  seleccion.tamano = t;
  document.getElementById('unidadBox').style.display = '';
}

/* ----------------- Orden: agregar / render / acciones ----------------- */
function agregarOrden(){
  const u = Number(document.getElementById('unidades').value);
  if(!u || !seleccion.producto || !seleccion.sabor){
    alert('Selecciona producto, sabor y unidades (y tamaño si aplica).');
    return;
  }
  if((seleccion.producto === 'Frappe' || seleccion.producto === 'Café') && !seleccion.tamano){
    alert('Selecciona el tamaño antes de agregar.');
    return;
  }

  let unitPrice = 0;
  if(seleccion.producto === 'Frappe'){
    unitPrice = precios['Frappe'][seleccion.tamano] || 0;
  } else if (seleccion.producto === 'Café'){
    unitPrice = precios['Café'][seleccion.tamano] || 0;
  } else if (seleccion.producto === 'Galleta'){
    unitPrice = precios['Galleta'];
  }

  const linePrice = unitPrice * u;

  orden.push({
    producto: seleccion.producto,
    sabor: seleccion.sabor,
    tamano: (seleccion.tamano || '-'),
    unidades: u,
    unitPrice: unitPrice,
    linePrice: linePrice
  });

  document.getElementById('saborBox').style.display = 'none';
  document.getElementById('tamanoBox').style.display = 'none';
  document.getElementById('unidadBox').style.display = 'none';
  document.getElementById('unidades').value = '';

  renderOrden();
}

function renderOrden(){
  const tbody = document.getElementById('ordenTable'); tbody.innerHTML = '';
  orden.forEach((line, i)=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${line.producto}</td>
      <td>${line.sabor}</td>
      <td>${line.tamano}</td>
      <td>${line.unidades}</td>
      <td>$${Number(line.unitPrice).toFixed(2)}</td>
      <td>$${Number(line.linePrice).toFixed(2)}</td>
      <td>
        <button class="btn success btn small" onclick="marcarListo(${i})">Listo</button>
        <button class="btn danger btn small" onclick="eliminarLinea(${i})">Eliminar</button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const total = orden.reduce((s, l) => s + Number(l.linePrice), 0);
  document.getElementById('totalOrder').textContent = total.toFixed(2);
}

function eliminarLinea(i){
  if(!confirm('Eliminar esta línea?')) return;
  orden.splice(i,1);
  renderOrden();
}

function marcarListo(i){
  const line = orden[i];
  const receta = (recetas[line.producto] && recetas[line.producto][line.sabor]) ? recetas[line.producto][line.sabor] : null;
  if(!receta){
    alert('Línea marcada como lista (no hay receta configurada).');
    orden.splice(i,1);
    renderOrden();
    return;
  }

  const mul = sizeMul[line.tamano] || 1;
  const needed = {};
  for(const ingr in receta){
    needed[ingr] = receta[ingr] * mul * line.unidades;
  }

  const faltantes = [];
  for(const ingr in needed){
    if(!inventario[ingr] || inventario[ingr].cantidad < needed[ingr]){
      const have = inventario[ingr] ? inventario[ingr].cantidad : 0;
      faltantes.push({ingr, need: needed[ingr], have});
    }
  }
  if(faltantes.length){
    let msg = 'No hay suficiente inventario para completar esta línea:\n';
    faltantes.forEach(f => {
      msg += `- ${inventario[f.ingr] ? inventario[f.ingr].nombre : f.ingr}: hace falta ${f.need} (tienes ${f.have})\n`;
    });
    alert(msg);
    return;
  }

 fetch("actualizar.php", {
  method: "POST",
  headers: {"Content-Type": "application/json"},
  body: JSON.stringify({ ingredientes: needed })
})
.then(res => res.text())
.then(txt => {
  if(txt.trim() === "OK"){
    loadInventory(); // recarga inventario desde MySQL
    alert("✔️ Línea servida y base de datos actualizada.");
  } else {
    alert("Error al actualizar inventario");
    console.error(txt);
  }
});

  orden.splice(i,1);
  renderOrden();

  alert('✅ Línea servida y inventario actualizado.');
}
/* ----------------- AGREGAR PRODUCTO AL INVENTARIO ----------------- */
function agregarProducto(){
  const clave = document.getElementById("newClave").value.trim();
  const nombre = document.getElementById("newNombre").value.trim();
  const cantidad = Number(document.getElementById("newCantidad").value);
  const min = Number(document.getElementById("newMin").value);
  const cat = document.getElementById("newCategoria").value;

  if(!clave || !nombre || !cantidad || !min || !cat){
    alert("Completa todos los campos.");
    return;
  }

fetch("insertar_producto.php", {
  method: "POST",
  headers: {"Content-Type":"application/json"},
  body: JSON.stringify({
    clave,
    nombre,
    cantidad,
    min,
    cat
  })
})
.then(res => res.json())
.then(r => {
  if(r.status === "ok"){
    alert("Producto agregado correctamente.");

    // limpiar inputs
    document.getElementById("newClave").value = "";
    document.getElementById("newNombre").value = "";
    document.getElementById("newCantidad").value = "";
    document.getElementById("newMin").value = "";
    document.getElementById("newCategoria").value = "";

    loadInventory();
  } else {
    alert("Error agregando producto: " + r.msg);
  }
});
}

/* ----------------- Inicialización ----------------- */
function init(){
  loadInventory();
  renderProducts();
  show('ventas');
}
init();
</script>

</body>
</html>
