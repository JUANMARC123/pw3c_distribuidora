# Arquitectura del Frontend — Pw3c Distribuidora

## 1. Visión General

Sistema de gestión de distribución **Single Page Application (SPA)** construido con JavaScript vainilla (vanilla JS), sin bundlers (Webpack, Vite), sin frameworks (React, Vue, Angular). El backend es una **API REST Laravel** en `http://127.0.0.1:8000/api`.

Cada página HTML funciona como un "shell" que carga su módulo JS correspondiente, el cual renderiza el contenido dinámicamente mediante `innerHTML`. La navegación lateral (sidebar) es persistente y se comparte entre todas las páginas.

| Aspecto | Detalle |
|---------|---------|
| **Lenguaje** | JavaScript ES6+, HTML5, CSS3 |
| **Backend** | Laravel API (PHP) en `http://127.0.0.1:8000` |
| **Autenticación** | JWT Bearer Token via Laravel Sanctum |
| **Tiempo real** | Firebase Realtime Database v10.12.2 |
| **Mapas** | Mapbox GL JS v3.2.0 + Leaflet 1.9.4 |
| **Estado** | localStorage (token, usuario, permisos) |
| **Estilos** | CSS Custom Properties + clases utilitarias |

---

## 2. Estructura de Directorios

```
frontenda_distribuidora/
├── index.html                    # Login / Registro
├── dashboard.html                # Panel principal
├── css/
│   ├── style.css                 # Estilos globales (~800 líneas)
│   ├── login.css                 # Estilos específicos de login
│   └── logistica.css             # Estilos de logística y mapas
├── js/
│   ├── error-handler.js          # Captura global de errores (IIFE)
│   ├── config.js                 # Configuración y utilidades globales
│   ├── auth.js                   # Autenticación (login/register/logout)
│   ├── layout.js                 # Sidebar dinámico con permisos
│   └── modules/
│       ├── dashboard.js          # Panel de inicio
│       ├── logistica.js          # Enrutador de tabs de logística
│       ├── pedidos.js            # CRUD de pedidos
│       ├── planificador.js       # Planificador de rutas (Mapbox)
│       ├── tracking.js           # Seguimiento GPS (Mapbox + Firebase)
│       ├── historial.js          # Historial de rutas (Leaflet)
│       ├── farmacias.js          # CRUD de farmacias (Leaflet)
│       ├── repartidores.js       # CRUD de repartidores (Firebase)
│       ├── vehiculos.js          # CRUD de vehículos
│       ├── rutas.js              # CRUD de rutas
│       ├── productos.js          # CRUD de productos
│       ├── usuarios.js           # CRUD de usuarios
│       ├── roles.js              # CRUD de roles y permisos
│       ├── caja.js               # Caja diaria
│       ├── despachos.js          # Despachos
│       ├── controles-ruta.js     # Controles de ruta
│       └── documentos.js         # Visualización de documentos
└── pages/
    ├── logistica.html            # Centro logístico (multitab)
    ├── farmacias.html            # Gestión de farmacias
    ├── repartidores.html         # Gestión de repartidores
    ├── vehiculos.html            # Gestión de vehículos
    ├── rutas.html                # Gestión de rutas
    ├── productos.html            # Gestión de productos
    ├── pedidos.html              # Gestión de pedidos
    ├── usuarios.html             # Gestión de usuarios
    ├── roles.html                # Gestión de roles
    ├── caja.html                 # Caja diaria
    ├── despachos.html            # Despachos
    ├── controles-ruta.html       # Controles de ruta
    ├── historial.html            # Historial (independiente)
    └── documentos.html           # Documentos
```

---

## 3. Librerías Externas

| Librería | Versión | CDN | USO |
|----------|---------|-----|-----|
| **Font Awesome** | 6.5.1 | `cdnjs.cloudflare.com` | Iconos en toda la UI |
| **jQuery** | 3.7.1 | `cdnjs.cloudflare.com` | DataTables y selectores |
| **DataTables** | 1.13.6 | `cdn.datatables.net` | Tablas con búsqueda, orden, paginación |
| **SweetAlert2** | 11 | `cdn.jsdelivr.net` | Diálogos modales, confirmaciones |
| **SortableJS** | 1.15.0 | `cdn.jsdelivr.net` | Drag & drop de pedidos en planificador |
| **Mapbox GL JS** | 3.2.0 | `api.mapbox.com` | Mapas en planificador y tracking |
| **Leaflet** | 1.9.4 | `unpkg.com` | Mapas en farmacias e historial |
| **Firebase (compat)** | 10.12.2 | `www.gstatic.com` | App + Realtime Database para GPS |

---

## 4. Autenticación y Permisos

### 4.1 Flujo de autenticación

```
Usuario → index.html → login(email, pass) → POST /auth/login → Laravel
  → Responde: { token, user, permisos }
  → localStorage: token, user, permisos
  → Redirecciona a dashboard.html
```

- **`login(email, password)`** (`auth.js:22`): Envía credenciales, extrae token (formato flexible), guarda en localStorage.
- **`register(nombre, apellido, email, password, ...)`** (`auth.js:75`): Registra nuevo usuario.
- **`logout()`** (`auth.js:104`): Elimina token y redirige al login.
- **`checkAuth()`** (`auth.js:116`): Verifica que exista un token válido en localStorage.
- **`extractToken(data)`** (`auth.js:12`): Busca token en `data.token`, `data.access_token`, `data.data.token`, etc.

### 4.2 Manejo de permisos

Los permisos se cargan desde la API en `loadUserPermissions()` y se cachean en `localStorage('permisos')`. Cada módulo filtra:

- **Sidebar** (`layout.js:44`): Solo muestra enlaces a páginas donde el usuario tiene `acceder`.
- **Páginas individuales** (ej. `dashboard.js:4`): Verifican `hasPermission('Dashboard', 'acceder')` al cargar.

```js
// config.js:186
function hasPermission(modulo, accion) {
    return userPermissions.some(p => p.modulo === modulo && p.accion === accion);
}
```

---

## 5. API Communication

### 5.1 apiFetch() — Cliente HTTP universal

**Archivo:** `js/config.js:8`

```js
async function apiFetch(endpoint, options = {})
```

Características:
- Concatena `API_URL + endpoint`
- Inyecta automáticamente `Authorization: Bearer {token}` desde localStorage
- Detecta `401` → limpia sesión y redirige al login
- Maneja errores de red (servidor caído, CORS)
- Serializa body a JSON automáticamente
- Soporta FormData (detecta `instanceof FormData`)

### 5.2 Endpoints principales

| Módulo | Endpoints |
|--------|-----------|
| Auth | `POST /auth/login`, `POST /auth/register`, `POST /auth/logout`, `GET /auth/user` |
| Dashboard | `GET /dashboard/metricas`, `GET /dashboard/alertas` |
| Pedidos | `GET /pedidos`, `POST /pedidos`, `PUT /pedidos/{id}`, `DELETE /pedidos/{id}` |
| Planificador | `GET /logistica/repartidores-disponibles`, `POST /logistica/planificacion/confirmar` |
| Seguimiento | `GET /logistica/rutas-activas` |
| Farmacias | CRUD completo `/farmacias` |
| Repartidores | CRUD completo `/repartidores` |
| Vehículos | CRUD completo `/vehiculos` |
| Rutas | CRUD completo `/rutas` |
| Productos | CRUD completo `/productos` |
| Usuarios | CRUD completo `/usuarios` |
| Roles | CRUD completo `/roles` |
| Caja | CRUD `/caja` |
| Despachos | CRUD `/despachos` |
| Controles Ruta | CRUD `/controles-ruta` |

---

## 6. Sistema de Páginas

### 6.1 index.html — Login / Registro

| Archivo | Ruta |
|---------|------|
| **HTML** | `index.html` |
| **CSS** | `css/login.css` |
| **JS** | `error-handler.js`, `config.js`, `auth.js` + inline |

Funcionalidad:
- Formulario de inicio de sesión con email + contraseña
- Modo registro toggle (nombre, apellido, teléfono, email auto-generado)
- Botón de diagnóstico de conexión a Laravel
- Validación de campos en cliente

### 6.2 dashboard.html — Panel Principal

| Archivo | Ruta |
|---------|------|
| **HTML** | `dashboard.html` |
| **CSS** | `css/style.css` |
| **JS** | `error-handler.js`, `config.js`, `auth.js`, `layout.js`, `modules/dashboard.js` |

Funcionalidad:
- Sidebar persistente con navegación a todas las páginas
- Hero section con acciones rápidas (Crear pedido, Crear entrega, Revisar caja)
- Métricas operativas (pedidos, entregas, farmacias, repartidores, vehículos, problemas)
- Alertas de atención rápida
- Rutas en curso (enlace a seguimiento)
- Resumen de caja del día

### 6.3 pages/logistica.html — Torre Logística

| Archivo | Ruta |
|---------|------|
| **HTML** | `pages/logistica.html` |
| **CSS** | `css/style.css`, `css/logistica.css` |
| **JS src** | `error-handler.js`, `config.js`, `auth.js`, `layout.js`, `modules/logistica.js` |
| **JS libs** | jQuery, DataTables, SweetAlert2, SortableJS, Mapbox GL v3.2.0, Firebase compat, Leaflet |
| **Módulos** | `pedidos.js`, `planificador.js`, `tracking.js`, `historial.js` |

**Pestañas internas** (controladas por `logistica.js`):

#### Pestaña: Pedidos
- **Módulo:** `pedidos.js` (puede cargarse como módulo independiente en `pages/pedidos.html`)
- Listado de pedidos con DataTables (búsqueda, orden, paginación)
- CRUD completo (crear, editar, eliminar)
- Detalles del pedido con productos, farmacia, totales

#### Pestaña: Despachar (Planificador)
- **Módulo:** `planificador.js` (681 líneas)
- **Mapa:** Mapbox GL v3.2.0 (light-v11)
- Panel izquierdo: lista de pedidos pendientes (arrastrables)
- Panel central: mapa con marcadores y ruta
- Panel derecho: repartidores con dropzones para asignar pedidos
- **SortableJS:** drag & drop de pedidos a repartidores
- **Firebase:** estado de conexión de repartidores en tiempo real
- Resumen de asignaciones (cantidad de pedidos, total Bs)
- Botón "Iniciar ruta" → confirma asignación via API
- Vista previa de ruta en el mapa con marcadores numerados y línea

#### Pestaña: Seguimiento
- **Módulo:** `tracking.js` (457 líneas)
- **Mapa:** Mapbox GL v3.2.0 (light-v11)
- **Tiempo real:** Firebase Realtime Database
- Panel izquierdo: lista de rutas activas con progreso
- Panel central: mapa con GPS markers (camión Font Awesome), trail de posiciones y paradas
- Panel derecho: detalle de ruta seleccionada (paradas, estados)
- Marcadores GPS con actualización en vivo via `setLngLat()`
- Trail de últimas 5 posiciones con puntos semitransparentes
- Botón "Re-centrar" para volver a la vista completa
- Control de interacción del usuario (evita `fitBounds` forzado)

#### Pestaña: Historial
- **Módulo:** `historial.js` (desde `logistica.js:renderHistorialTab`)
- **Mapa:** Leaflet + OpenStreetMap
- Historial de rutas completadas
- Visualización de ruta histórica con marcadores

### 6.4 pages/farmacias.html — Farmacias

| Archivo | Ruta |
|---------|------|
| **HTML** | `pages/farmacias.html` |
| **CSS** | `css/style.css` |
| **JS** | `error-handler.js`, `config.js`, `auth.js`, `layout.js`, `modules/farmacias.js` |
| **Mapa** | Leaflet 1.9.4 + OpenStreetMap |

Funcionalidad:
- CRUD completo de farmacias
- Mapa global con todas las farmacias (Leaflet)
- Mapa modal para seleccionar ubicación (marcador arrastrable)
- Búsqueda y filtros
- DataTables para listado

### 6.5 Otras páginas (patrón estándar)

Todas las siguientes páginas siguen el mismo patrón: sidebar, header, content dinámico, DataTables para listados, CRUD via API.

| Página | Archivo HTML | Módulo JS | Funcionalidad principal |
|--------|-------------|-----------|------------------------|
| Repartidores | `pages/repartidores.html` | `repartidores.js` | CRUD, Firebase uid, asignación de vehículos |
| Vehículos | `pages/vehiculos.html` | `vehiculos.js` | CRUD, marcas/modelos, estado operativo |
| Rutas | `pages/rutas.html` | `rutas.js` | CRUD, asignación de repartidores |
| Productos | `pages/productos.html` | `productos.js` | CRUD, categorías, precios |
| Usuarios | `pages/usuarios.html` | `usuarios.js` | CRUD, roles, estado |
| Roles | `pages/roles.html` | `roles.js` | CRUD, permisos por módulo |
| Caja | `pages/caja.html` | `caja.js` | Apertura/cierre, ingresos/egresos |
| Despachos | `pages/despachos.html` | `despachos.js` | Gestión de despachos |
| Controles Ruta | `pages/controles-ruta.html` | `controles-ruta.js` | Control y seguimiento de rutas |
| Documentos | `pages/documentos.html` | `documentos.js` | Visualización de PDFs (config.js) |
| Historial | `pages/historial.html` | `historial.js` | Mapa Leaflet con ruta histórica |

---

## 7. Sistema de Mapas

### 7.1 Mapbox GL JS v3.2.0

**Usado en:** `planificador.js` y `tracking.js`

```js
// Configuración base
const GPS_MAPBOX_TOKEN = "<TU_MAPBOX_TOKEN>";  // Definir en public/js/config.local.js

new mapboxgl.Map({
    container: 'planificador-map',  // o 'tracking-map'
    style: 'mapbox://styles/mapbox/light-v11',
    center: [-67.8217, -16.4897],  // La Paz, Bolivia
    zoom: 12,
    fadeDuration: 0,
});
```

**Características compartidas:**
- NavigationControl (zoom +/-, brújula) en top-right
- `fitBounds()` con `animate: false` y `maxZoom: 14`
- Bandera `userInteracted` para evitar fitBounds forzado cuando el usuario interactúa
- `fadeDuration: 0` para tiles sin transición

**Características específicas:**

| Aspecto | Planificador (`planificador.js`) | Seguimiento (`tracking.js`) |
|---------|--------------------------------|----------------------------|
| **Marcador GPS** | Círculo azul numerado + texto "GPS" | Camión Font Awesome (`.gps-marker`) |
| **Marcadores parada** | Círculos numerados azules | Círculos coloreados por estado |
| **Ruta** | Línea azul sólida (#2563eb, width 4) | Línea azul discontinua (width 3, dashed) |
| **Popups** | No usa | Sí, con info de parada/repartidor |
| **Trail posiciones** | No | Sí, últimas 5 posiciones como puntos |
| **Actualización** | Manual (arrastrar pedidos) | Firebase tiempo real (automático) |
| **Interactividad** | Drag & drop sortable | Re-center button + interacción usuario |

### 7.2 Leaflet 1.9.4 + OpenStreetMap

**Usado en:** `farmacias.js` y `historial.js`

```js
const map = L.map('map-container').setView([-16.4897, -67.8217], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
```

**Características:**
- Marcadores circulares (`L.circleMarker`) y regulares (`L.marker`)
- Polilíneas para rutas
- `fitBounds()` con animación
- Marcadores arrastrables en farmacias (selección de ubicación)

---

## 8. Firebase Realtime Database

### 8.1 Configuración

```js
const GPS_FIREBASE_CONFIG = {
    apiKey: "AIzaSyDLpt_G8VOvmePuAYEQyffhU1Qb9braKSw",
    authDomain: "mapboxfirebase-a79b5.firebaseapp.com",
    databaseURL: "https://mapboxfirebase-a79b5-default-rtdb.firebaseio.com",
    projectId: "mapboxfirebase-a79b5",
    ...
};
```

### 8.2 Datos en tiempo real

| Referencia Firebase | Uso | Módulo |
|-------------------|-----|--------|
| `rutas_activas` | Ubicación GPS de rutas activas | `tracking.js` |
| `usuarios` | Ubicación y estado de repartidores | `tracking.js`, `planificador.js` |

### 8.3 Flujo de datos GPS

```
Repartidor (App Móvil)
  → Firebase: /rutas_activas/{id}/ubicacion { lat, lng, hora }
  → Firebase: /usuarios/{firebase_uid}/ubicacion { lat, lng, hora }
    → tracking.js: listener 'value' → trackingMarkers[key].setLngLat(coords)
      → updateGPSTrail(key, coords) → drawGPSTrail(key)
```

---

## 9. Helpers y Utilidades Compartidas

**Archivo:** `js/config.js`

| Función | Línea | Propósito |
|---------|-------|-----------|
| `apiFetch(endpoint, options)` | 8 | Cliente HTTP universal |
| `showLoading(container)` | 95 | Spinner de carga |
| `getStatusBadge(status)` | 99 | Badge coloreado según estado |
| `formatDate(dateStr)` | 126 | Fecha formateada local |
| `formatDateShort(dateStr)` | 137 | Fecha corta |
| `reloadDataTable(table, endpoint)` | 145 | Recarga DataTable |
| `loadUserPermissions()` | 171 | Carga permisos desde API |
| `hasPermission(modulo, accion)` | 186 | Verifica permiso |
| `normalizePermissionKey(value)` | 199 | Normaliza nombre de módulo |
| `escapeHtml(value)` | 253 | Sanitiza HTML |
| `normalize(value)` | 257 | Normaliza texto (sin acentos) |
| `parseMoney(value)` | 261 | Parsea valor monetario |
| `money(value)` | 266 | Formatea Bs |
| `getRepartidorId(r)` | 270 | Extrae ID de repartidor |
| `getRepartidorName(r)` | 274 | Extrae nombre de repartidor |

---

## 10. Sistema de Estilos (CSS)

### 10.1 style.css (~800 líneas)

**Variables CSS personalizadas:**
```css
:root {
    --primary: #3b82f6;
    --primary-dark: #2563eb;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #6366f1;
    --sidebar-bg: #1e293b;
    --sidebar-width: 250px;
    --card-bg: #ffffff;
    --border: #e2e8f0;
    --text: #1e293b;
    --text-muted: #64748b;
    --bg: #f1f5f9;
    --radius: 10px;
    --shadow: 0 1px 3px rgba(0,0,0,0.08);
    --transition: none;
}
```

**Secciones principales:**
- Sidebar animado (transform, open/close)
- Layout responsive (sidebar + header + content)
- Cards, tablas, formularios, badges, botones
- Modal overlay y contenido
- Loading spinner
- Responsive (media queries para tablet/mobile)
- Utilidades (flex, gap, text, colors)

### 10.2 login.css

- Login centrado (flexbox)
- Card de autenticación con sombra
- Input wrapper con iconos
- Toggle password
- Botón de submit con gradiente
- Animaciones de error/success

### 10.3 logistica.css

- Tabs de logística (`.logistica-tabs`, `.logistica-tab`)
- Shell de rutas (`.route-shell`, `.route-panel`, `.route-map-panel`)
- Grid de tracking (`.tracking-grid`)
- Marcadores Mapbox (`.gps-marker`, `.gps-trail-dot`)
- Kanban board para seguimiento visual
- Responsive (breakpoints a 1100px, 900px, 768px)

---

## 11. Patrones de Diseño

### 11.1 Carga de módulos

Cada página HTML carga su módulo JS específico al final del `<body>`. Los módulos se registran con eventos `DOMContentLoaded` o se ejecutan inmediatamente.

```html
<!-- dashboard.html -->
<script src="js/modules/dashboard.js"></script>

<!-- El módulo se auto-ejecuta -->
<script>
document.addEventListener('DOMContentLoaded', async function () {
    if (!checkAuth()) return;
    renderInicioSkeleton();
    await loadInicioOperativo();
});
</script>
```

### 11.2 Renderizado dinámico

Todo el contenido de las páginas se genera mediante `innerHTML` desde JavaScript. No hay plantillas HTML del lado del servidor.

```js
function renderPedidos() {
    container.innerHTML = pedidos.map(p => `
        <div class="pedido-card">
            <div class="pedido-id">#${p.id}</div>
            ...
        </div>
    `).join('');
}
```

### 11.3 CRUD genérico

La mayoría de los módulos siguen este patrón:
1. `init()` → carga datos via API, renderiza UI
2. Tabla DataTables con datos
3. Botón "Nuevo" → modal con formulario
4. Botón "Editar" → modal precargado
5. Botón "Eliminar" → confirmación SweetAlert2
6. `render()` → refresca UI tras cambios

### 11.4 Manejo de errores

- **Global:** `error-handler.js` captura `window.onerror` y `unhandledrejection`
- **API:** `apiFetch()` maneja errores HTTP (401 → logout, otros → throw)
- **UX:** SweetAlert2 para errores de usuario, console.error para debugging

### 11.5 Estado de la aplicación

| Almacenamiento | Datos |
|---------------|-------|
| `localStorage('token')` | JWT Bearer token |
| `localStorage('user')` | Objeto usuario (nombre, email, roles) |
| `localStorage('permisos')` | Array de permisos |
| Variables globales | `firebaseUsuariosData`, `rutasActivas`, `pedidosPendientes` |

---

## 12. Diagrama de Flujo de Datos

```
                    ┌─────────────────────────────────┐
                    │        Laravel API               │
                    │    http://127.0.0.1:8000/api     │
                    └──────────┬──────────────────────┘
                               │ http (JSON)
                               ▼
┌──────────────────────────────────────────────────────────┐
│                   Frontend (Vanilla JS)                   │
│                                                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │  Auth    │  │  Layout  │  │  Config  │  │  Error   │ │
│  │ (JWT)    │  │ (Sidebar)│  │ (apiFetch)│  │ Handler  │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘ │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │              Módulos de Página                   │   │
│  │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐  │   │
│  │  │Dashboard│Pedidos│Planif │Tracking│Farmacias│...│   │
│  │  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘  │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │              Mapas                               │   │
│  │  ┌─────────────────┐  ┌──────────────────────┐  │   │
│  │  │  Mapbox GL v3.2 │  │  Leaflet 1.9.4       │  │   │
│  │  │  (planif+track) │  │  (farmacias+historial)│  │   │
│  │  └─────────────────┘  └──────────────────────┘  │   │
│  └──────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
           │                                    ▲
           │ http (JSON)                        │ WebSocket (Firebase)
           ▼                                    │
┌──────────────────────┐          ┌──────────────────────────┐
│   Laravel API        │          │  Firebase RTDB           │
│   - Auth             │          │  - /rutas_activas        │
│   - CRUDs            │          │  - /usuarios             │
│   - Reportes         │          └──────────────────────────┘
└──────────────────────┘                       ▲
                                               │ GPS data
                                        ┌──────┴──────┐
                                        │ App Móvil   │
                                        │ Repartidor  │
                                        └─────────────┘
```

---

## 13. Resumen de Versiones

| Componente | Versión |
|------------|---------|
| Mapbox GL JS | 3.2.0 (julio 2024) |
| Leaflet | 1.9.4 |
| Firebase compat | 10.12.2 |
| Font Awesome | 6.5.1 |
| jQuery | 3.7.1 |
| DataTables | 1.13.6 |
| SweetAlert2 | 11 |
| SortableJS | 1.15.0 |
| PHP (Backend) | Laravel (API) |
| Servidor | `http://127.0.0.1:8000` |

---

*Documento generado el 7 de julio de 2026.*
