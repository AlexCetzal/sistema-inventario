# Vale de Suministros (versión PHP + MySQL)

Sistema de inventario y solicitudes de material de oficina y limpieza para
DESUR. Hecho en HTML + PHP puro (sin frameworks), pensado para correr con
**Laragon** (o cualquier XAMPP/WAMP/MAMP) usando su MySQL de siempre.

- **Empleados**: entran a `solicitar.php`, un formulario abierto (sin
  contraseña) para pedir materiales de oficina (lapiceros, plumas,
  libretas, etc.) -- con un buscador que permite elegir varios materiales
  de un jalón, cada uno con su cantidad.
- **Encargado**: entra a `panel.php` con contraseña. Ahí ve el inventario
  completo (oficina y limpieza), con niveles de stock, y aprueba o rechaza
  las solicitudes pendientes. Al aprobar, el stock se descuenta solo.
- Si se configura un webhook de Microsoft Teams, cada solicitud nueva manda
  UN solo aviso al canal del encargado con todos los materiales pedidos en
  esa solicitud (aunque hayan sido varios); el kit de bienvenida manda el
  suyo aparte.
- **Kit de bienvenida**: desde el panel, un botón para entregarle de golpe
  a alguien nuevo la lista estándar de materiales de oficina -- descuenta
  todo el inventario junto, sin dar de alta una solicitud por artículo.
- El historial de solicitudes se puede descargar a Excel (por semana, por
  mes, o completo) desde el panel.
- Diseño con la marca DESUR: logo real y color naranja de marca (`#F39200`).

## 1. Requisitos

- PHP 8.0 o superior, con las extensiones `pdo_mysql` y `curl` habilitadas
  (vienen activadas por defecto en Laragon/XAMPP/WAMP/MAMP).
- MySQL o MariaDB corriendo (en Laragon, se prende solo con "Start All").

## 2. Instalación (primera vez)

Copia toda la carpeta `inventario-desur-php` a `www` de Laragon (o
`htdocs` si usas XAMPP), abre una terminal ahí dentro y ejecuta:

```bash
# 1. Crear la base de datos y las tablas
php init_db.php

# 2. Cargar el catálogo inicial de materiales
php seed_db.php

# 3. Definir la contraseña del panel del encargado
php set_admin_password.php
```

En el paso 1, el script crea solo la base de datos `inventario_desur` en tu
MySQL local (usuario `root`, sin contraseña -- los valores por defecto de
Laragon) y las tablas dentro de ella. Si usas otro usuario/contraseña de
MySQL, copia `config.local.php.example` a `config.local.php` y ajusta los
valores de `$DB_HOST`, `$DB_USER`, `$DB_PASS`, etc. antes de correr el
paso 1.

En el paso 3 te pide escribir una contraseña dos veces (se ve en pantalla
mientras la escribes, así que hazlo donde nadie más esté viendo). Esa es
la que tu compañero usará para entrar al panel -- nunca se guarda en texto
plano, solo su hash.

## 3. Ver o editar la base de datos a mano

Con Laragon, entra a **phpMyAdmin** desde el menú del programa (o
`http://localhost/phpmyadmin`), usuario `root`, sin contraseña. Ahí puedes
ver las tablas `materiales` y `solicitudes`, corregir un stock a mano, o
revisar el historial completo.

## 4. Arrancar la aplicación

Con Apache/Laragon prendido, abre en el navegador:
```
http://localhost/inventario-desur-php/solicitar.php
```
(o el dominio bonito que te arme Laragon automáticamente, por ejemplo
`http://inventario-desur-php.test/solicitar.php`).

## 5. Que otras computadoras de la oficina puedan entrar

1. En la computadora donde corre Laragon, busca su IP local (`ipconfig`
   en Windows). Algo como `192.168.1.50`.
2. Desde cualquier otra computadora conectada a la misma red, abre en el
   navegador: `http://192.168.1.50/inventario-desur-php/solicitar.php`
   (con la IP real de tu caso). El dominio `.test` de Laragon solo
   funciona en tu propia máquina, así que para otros equipos siempre usa
   la IP.
3. Si no conecta, revisa que el firewall de esa computadora no esté
   bloqueando el puerto 80.

El `.htaccess` incluido bloquea el acceso directo por navegador a
`config.local.php` (ahí viven las credenciales de la base de datos y la
contraseña del panel), para que nadie pueda leerlo adivinando la URL.

## 6. Configurar el aviso a Microsoft Teams (opcional)

1. En Teams, entra al canal donde quieres recibir los avisos.
2. Menú "..." del canal → **Conectores** (o **Flujos de trabajo** en
   versiones nuevas de Teams) → busca **Webhook entrante** → **Configurar**.
3. Ponle un nombre (ej. "Vale de Suministros") y copia la URL que te da.
4. Ábrela en un editor de texto: `config.local.php` (lo crea
   `set_admin_password.php` en el paso 3) y pega la URL en la línea de
   `$TEAMS_WEBHOOK_URL`.
5. Guarda el archivo -- no hace falta reiniciar nada.

Si prefieres no configurarlo por ahora, déjalo vacío -- el sistema sigue
funcionando normal, solo no manda el aviso a Teams.

## 7. Estructura del proyecto

```
inventario-desur-php/
├── index.php                    # redirige a solicitar.php
├── solicitar.php                 # formulario de empleados
├── login.php                     # acceso del encargado
├── logout.php
├── panel.php                     # panel del encargado (protegido)
├── actions.php                   # aprobar / rechazar una solicitud
├── materiales.php                 # agregar / editar materiales del catálogo (protegido)
├── restock.php                    # acción rápida: sumar existencia a un material (protegido)
├── bienvenida.php                  # kit de bienvenida para alguien nuevo (protegido)
├── exportar_historial.php          # descarga el historial en Excel (protegido)
├── bootstrap.php                 # arranque común de cada página
├── config.php                    # configuración (lee config.local.php)
├── config.local.php.example       # plantilla -- config.local.php es privado
├── db.php                        # conexión PDO a MySQL
├── schema.sql                     # estructura de las tablas (MySQL)
├── init_db.php                    # crea la base y las tablas
├── seed_db.php                    # carga el catálogo de ejemplo (demo)
├── seed_papeleria.php              # carga el catálogo real de oficina (desde tu Excel)
├── reset_db.php                   # limpia las solicitudes de prueba antes de usarlo en serio
├── set_admin_password.php         # define la contraseña del panel
├── includes/
│   ├── header.php / footer.php    # plantilla compartida (con el logo DESUR)
│   ├── flash.php                  # mensajes de confirmación/error
│   ├── auth.php                   # protección del panel
│   └── teams.php                  # aviso a Microsoft Teams
├── img/                            # logo DESUR (versión clara y oscura)
├── css/style.css                  # estilos con la paleta DESUR
└── .htaccess                       # bloquea config.local.php
```

## 8. Agregar, editar o reabastecer materiales

Desde el panel (**Agregar / editar materiales**, o dando clic al nombre de
cualquier material en el inventario) el encargado puede, sin tocar la base
de datos a mano:

- **Agregar** un material nuevo al catálogo (nombre, categoría, unidad,
  existencia inicial, mínimo y máximo).
- **Editar** uno que ya existe -- corregir nombre, categoría, unidad,
  mínimo/máximo, o la existencia actual directamente.
- **Agregar existencia** con un solo campo: cuando llega producto nuevo,
  se escribe la cantidad que llegó y se suma al stock actual (sin tener
  que calcular ni volver a escribir el total).

También se puede seguir editando la tabla `materiales` directo desde
phpMyAdmin o HeidiSQL si lo prefieres.

## 9. Kit de bienvenida

Desde el panel, el botón **🎉 Kit de bienvenida** abre una pantalla para
dar de alta a alguien nuevo: se le pone su nombre (y opcionalmente su
puesto/área), y se le entrega de golpe el kit estándar de materiales de
oficina -- descontando el stock de todos a la vez y quedando registrado
en el historial como si cada artículo se hubiera aprobado por separado.

El kit estándar viene precargado (1 libreta media carta, 1 pluma azul, 1
pluma negra, 1 pluma roja, 1 paquete de post-it, 1 marcatexto naranja, 1
marcatexto verde, 1 lápiz, 1 corrector), pero antes de confirmar se puede
desmarcar lo que no aplique, cambiar cantidades, o agregar cualquier otro
material de oficina desde el buscador de abajo -- nada se descuenta hasta
que se manda el formulario.

La lista estándar está definida en la función `kit_base()` dentro de
`bienvenida.php` (busca ese nombre en el archivo); si más adelante quieren
cambiar qué trae el kit por default, se edita ahí. Si algún artículo del
kit todavía no existe en tu catálogo (por ejemplo, la libreta media carta
si no vino en el Excel de papelería), la pantalla lo avisa arriba con un
link directo para agregarlo.

## 10. Exportar el historial a Excel

Desde el panel, junto a "Historial reciente", hay tres botones --
**Exportar semana**, **Exportar mes** y **Exportar todo** -- que descargan
el historial de solicitudes (aprobadas y rechazadas) como un archivo
`.xls` listo para abrir en Excel, con folio, nombre, material, cantidad,
área, urgencia, estado y fechas de cada movimiento.

## 11. Cargar tu catálogo real de oficina (desde tu Excel)

`seed_papeleria.php` carga los 139 artículos de tu Excel de papelería
("Inv Papeleria RM 2.xlsx") directo a la base de datos, reemplazando los
materiales de categoría "oficina" que hubiera (los de "limpieza" no se
tocan). También borra las solicitudes de prueba de una vez, porque
apuntaban a materiales que van a desaparecer.

```bash
php seed_papeleria.php
```

El Excel no traía columnas de "unidad" ni de niveles mínimo/máximo, así
que se rellenaron con un supuesto razonable (todo en "pza", y
mínimo/máximo calculados a partir de tu existencia actual). Son solo un
punto de partida -- después de cargarlo, entra a **Agregar / editar
materiales** en el panel y corrige unidad, mínimo o máximo donde tengas
mejor información (por ejemplo, si algo se maneja por rollo, por litro o
por paquete en vez de por pieza).

## 12. Dejar la base de datos lista para usarse en serio

Cuando terminen las pruebas y quieran empezar a usar el sistema con
solicitudes reales, corran en la terminal (dentro de la carpeta del
proyecto):

```bash
php reset_db.php
```

Esto borra únicamente las solicitudes de prueba (los vales que se
generaron mientras probaban) y reinicia los folios para que el primer
vale real sea el folio 1. El catálogo de materiales y las existencias que
ya hayan corregido con "Editar" o "Agregar existencia" **no se tocan**.

Si además quieren que las existencias de todos los materiales empiecen en
0 (para capturar el conteo real desde cero usando "Agregar existencia"):

```bash
php reset_db.php --materiales-cero
```

Y si prefieren borrar todo -- solicitudes y catálogo -- y volver a cargar
el catálogo de ejemplo original (como si el sistema fuera recién
instalado):

```bash
php reset_db.php --full
```

El script pide confirmación (escribir `si`) antes de borrar cualquier
cosa.

## 13. Siguientes pasos sugeridos

- Aplicar las fuentes oficiales de DESUR (Effra) si nos comparten los
  archivos `.ttf` directo en el chat -- por ahora se usa Poppins como
  tipografía cercana, vía Google Fonts.
