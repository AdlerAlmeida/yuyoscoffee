/* ----------------- Datos e inventario por defecto ----------------- */
const STORAGE_KEY = 'yuyos_inventory_v3'; // Solo se usa para las funciones de reseteo/renderizado local

let inventario = {}; 

/* (Mantener 'productos', 'precios', 'recetas', 'sizeMul' sin cambios) */

const productos = [
    { nombre:'Café', sabores:['Americano' ,'Latte','Capuchino'], img:'cafe.jpg'},
    { nombre:'Frappe', sabores:['Moka','Oreo','Caramelo'], img:'frappe.jpg' },
    { nombre:'Galleta', sabores:['Chocolate','Avena','Vainilla'], img:'galleta.jpg' }
];

const precios = {
    'Café': {Chico:45, Mediano:60, Jumbo:75},
    'Frappe': {Chico:80, Mediano:120, Jumbo:160},
    'Galleta': 20
};

const recetas = {
    'Café': {
        'Americano': {cafe:1, agua:250,},
        'Latte': {cafe:1, leche:200},
        'Capuchino': {cafe:1, leche:150}
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

const sizeMul = {Chico:1, Mediano:1.5, Jumbo:2};

/* Estado de venta */
let seleccion = { producto:null, sabor:null, tamano:null };
let orden = [];
let ingredientes_a_descontar = {}; // Nuevo objeto para acumular los ingredientes de toda la orden

/* ----------------- Funciones de Ayuda ----------------- */

/**
 * Genera el objeto de ingredientes y cantidades totales necesarias para la orden.
 * @returns {object} Un objeto con {ingrediente_clave: cantidad_total_necesaria}
 */
function generarListaDescuento(currentOrden) {
    const needed = {};
    currentOrden.forEach(line => {
        const receta = (recetas[line.producto] && recetas[line.producto][line.sabor]) ? recetas[line.producto][line.sabor] : null;
        if (!receta) return; // No hay receta, no hay descuento

        const mul = sizeMul[line.tamano] || 1;
        
        for (const ingr in receta) {
            const cantidad = receta[ingr] * mul * line.unidades;
            needed[ingr] = (needed[ingr] || 0) + cantidad;
        }
    });
    return needed;
}

/* ----------------- UI: mostrar panel ----------------- */
function show(id){
    document.getElementById('inv').classList.add('hidden');
    document.getElementById('ventas').classList.add('hidden');
    document.getElementById(id).classList.remove('hidden');
    
    // Si mostramos el inventario, intentamos cargarlo del servidor
    if(id === 'inv'){
        loadInventoryFromDB();
    }
}

/* ----------------- INVENTARIO (Ahora desde el backend) ----------------- */

/**
 * NUEVA FUNCIÓN: Carga el inventario usando obtener_inventario.php (necesitas crear este archivo PHP)
 */
function loadInventoryFromDB(){
    fetch("obtener.php")
    .then(r => {
        if (!r.ok) throw new Error("Error al obtener inventario: " + r.status);
        return r.json();
    })
    .then(data => {
        // Asume que data es un array o un objeto, y lo convierte al formato 'inventario' si es necesario
        // Aquí asumimos que PHP devuelve {clave: {nombre, cantidad, min, cat}, ...}
        inventario = data; 
        renderInventory();
    })
    .catch(error => {
        console.error("Error al cargar inventario:", error);
        alert("No se pudo cargar el inventario desde el servidor.");
        // Si falla, al menos muestra la UI con datos vacíos o por defecto
        inventario = JSON.parse(JSON.stringify(defaultInventory)); 
        renderInventory();
    });
}

// Las funciones saveInventory y resetInventory ya no son relevantes para el inventario real. 
// Mantengo solo la función de renderizado para mostrar lo que hay en la DB (o local si falló)

function renderInventory(){
    const cafeEl = document.getElementById('cafeSection');
    const frappeEl = document.getElementById('frappeSection');
    const galletaEl = document.getElementById('galletaSection');
    cafeEl.innerHTML = frappeEl.innerHTML = galletaEl.innerHTML = '';
    
    // Se asume que 'inventario' es un objeto {clave: {nombre, cantidad, min, cat}}
    for(const key in inventario){
        const it = inventario[key];
        // Aquí necesitas la propiedad 'min' si tu PHP no la devuelve, debes cargarla.
        const minVal = it.min || 0; 
        const estado = it.cantidad <= minVal ? '🔴 Reabastecer' : '🟢 OK';
        const row = `<tr><td>${it.nombre}</td><td>${it.cantidad}</td><td>${minVal}</td><td>${estado}</td></tr>`;
        
        if(it.cat === 'cafe') cafeEl.innerHTML += row;
        if(it.cat === 'frappe') frappeEl.innerHTML += row;
        if(it.cat === 'galleta') galletaEl.innerHTML += row;
    }
}
// Las funciones saveInventory y resetInventory ya no son relevantes para el inventario real en DB.
// Se dejan como stub por si la UI las necesita, pero no realizan guardado real.
function saveInventory(){ alert('La función de guardar ha sido deshabilitada. El inventario se actualiza automáticamente al servir las líneas.'); }
function resetInventory(){ alert('La función de restablecer ha sido deshabilitada.'); }


/* ----------------- Productos UI (ventas) - Sin cambios ----------------- */
function renderProducts(){
    // ... (Mantener la función renderProducts sin cambios)
    const grid = document.getElementById('productGrid'); grid.innerHTML = '';
    productos.forEach(p=>{
        const d = document.createElement('div'); d.className = 'cardItem';
        d.innerHTML = `<img src='${p.img}' alt='${p.nombre}'/><p><strong>${p.nombre}</strong></p>`;
        d.onclick = () => seleccionarProducto(p.nombre);
        grid.appendChild(d);
    });
}

function seleccionarProducto(prod){
    // ... (Mantener la función seleccionarProducto sin cambios)
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
    // ... (Mantener la función seleccionarSabor sin cambios)
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
    // ... (Mantener la función seleccionarTamano sin cambios)
    seleccion.tamano = t;
    document.getElementById('unidadBox').style.display = '';
}


/* ----------------- Orden: agregar / render / acciones ----------------- */
function agregarOrden(){
    // ... (Mantener la función agregarOrden sin cambios)
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
    // ... (Mantener la función renderOrden sin cambios)
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
                <button class="btn success btn small" onclick="guardarOrdenYProcesar()">Pagar / Servir</button>
                <button class="btn danger btn small" onclick="eliminarLinea(${i})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    const total = orden.reduce((s, l) => s + Number(l.linePrice), 0);
    document.getElementById('totalOrder').textContent = total.toFixed(2);
}

function eliminarLinea(i){
    // ... (Mantener la función eliminarLinea sin cambios)
    if(!confirm('Eliminar esta línea?')) return;
    orden.splice(i,1);
    renderOrden();
}

// -------------------------------------------------------------
// NUEVA LÓGICA DE PROCESAMIENTO
// -------------------------------------------------------------

/**
 * Función que reemplaza a marcarListo y al botón de registro de venta.
 * 1. Registra la venta en registrar_venta.php.
 * 2. Descuenta el inventario en actualizar.php.
 */
async function guardarOrdenYProcesar() {
    if (orden.length === 0) {
        alert("La orden está vacía.");
        return;
    }

    // 1. Generar la lista total de ingredientes a descontar
    ingredientes_a_descontar = generarListaDescuento(orden);

    // 2. Ejecutar la Venta (registrar_venta.php)
    try {
        const total = Number(document.getElementById("totalOrder").textContent.trim());
        
        let resVenta = await fetch("registrar_venta.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ total: total, items: orden }) // Envía toda la orden
        });
        let resTextVenta = await resVenta.text();
        
        if (resTextVenta.trim() !== 'VENTA OK') {
            throw new Error('Error al registrar venta: ' + resTextVenta);
        }
        
        alert(`✅ Venta registrada (ID: ${resTextVenta.match(/\d+/) || 'N/A'}).`);

        // 3. Ejecutar el Descuento de Inventario (actualizar.php)
        // Solo si la venta fue exitosa
        let resDesc = await fetch("actualizar.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ingredientes: ingredientes_a_descontar }) // Envía solo el resumen de ingredientes
        });
        let resTextDesc = await resDesc.text();
        
        if (resTextDesc.trim() !== 'OK') {
            alert('⚠️ Advertencia: Venta registrada, pero falló el descuento de inventario: ' + resTextDesc);
        } else {
            alert('Inventario descontado correctamente.');
        }

        // 4. Limpiar la orden y actualizar UI
        orden = [];
        ingredientes_a_descontar = {};
        renderOrden();
        loadInventoryFromDB(); // Recarga el inventario actualizado desde el servidor

    } catch (error) {
        console.error("Error crítico en el proceso de venta:", error);
        alert(`Error crítico: No se pudo completar la transacción. Revise la consola. Mensaje: ${error.message}`);
    }
}

// Marcado como listo (ya no descuenta inventario, solo es un stub)
function marcarListo(i){
    alert('Esta función ya no está disponible. Usa el botón "Pagar / Servir" para registrar la venta y descontar todo el inventario de la orden.');
}
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

  const payload = { clave, nombre, cantidad, min, cat };
  console.log("Enviando payload:", payload);

  fetch("agregar.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(payload)
  })
  .then(async res => {
    const text = await res.text();
    // Intentar parsear JSON si devuelve JSON
    try {
      const json = JSON.parse(text);
      console.log("Respuesta servidor:", json);
      if(json.status === "ok"){
        alert("Producto agregado correctamente.");
        // limpiar campos
        document.getElementById("newClave").value = "";
        document.getElementById("newNombre").value = "";
        document.getElementById("newCantidad").value = "";
        document.getElementById("newMin").value = "";
        document.getElementById("newCategoria").value = "";
         loadInventoryFromDB();; // recargar inventario
      } else {
        alert("Error al guardar: " + (json.msg || JSON.stringify(json)));
      }
    } catch(e){
      console.error("Respuesta no-JSON del servidor:", text);
      alert("Respuesta inesperada del servidor. Revisa la consola (Network/Response).");
    }
  })
  .catch(err => {
    console.error("Fetch error:", err);
    alert("Error de red al intentar guardar (ver consola).");
  });
}


/* ----------------- Inicialización ----------------- */
function init(){
    // loadInventory(); // Reemplazado por loadInventoryFromDB()
    renderProducts();
    show('ventas'); // Muestra la sección de ventas por defecto
    loadInventoryFromDB(); // Carga el inventario al iniciar
}
init();