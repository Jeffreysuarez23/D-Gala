# 📊 Guía de Reportes Profesionales de Compras

## Descripción General
Se han agregado dos nuevas funcionalidades de reportes profesionales en el sistema de gestión de compras:

### 1. **Reporte Individual de Pedidos**
Genera un reporte profesional completo de un pedido específico.

**Ubicación:** En la tabla de pedidos de la sección "Gestionar Pedidos", en la columna de acciones
**Icono:** 📄 (PDF)
**Archivo:** `/Admin/include/generar_reporte_pedido.php`

#### Características:
- ✅ Datos completos del cliente (nombre, email, teléfono, dirección, documento)
- ✅ Información de la transacción (ID, fecha, estado, método de pago)
- ✅ Listado detallado de productos comprados
- ✅ Cálculo automático de totales y subtotales
- ✅ Diseño profesional y responsivo
- ✅ Optimizado para impresión y descarga como PDF
- ✅ Información de contacto de la tienda

#### Cómo usar:
1. Ve a **Admin → Gestionar Pedidos**
2. Busca el pedido que deseas reportar
3. Haz clic en el botón con icono **PDF** (color celeste)
4. Se abrirá en una nueva ventana con el reporte profesional
5. Puedes imprimir (Ctrl+P) o guardar como PDF desde tu navegador

#### Parámetros URL:
```
include/generar_reporte_pedido.php?id=123&download=true
```
- `id`: ID del pedido (requerido)
- `download`: true para iniciar descarga, false para visualizar

---

### 2. **Reporte Consolidado de Compras**
Genera un reporte completo de múltiples pedidos con filtros avanzados.

**Ubicación:** En la sección de filtros de "Gestionar Pedidos", botón verde
**Icono:** 📊 (Spreadsheet)
**Archivo:** `/Admin/include/generar_reporte_consolidado.php`

#### Características:
- ✅ Resumen ejecutivo con totales de ventas
- ✅ Tabla completa de todas las compras filtradas
- ✅ Resumen agrupado por estado de pedido
- ✅ Detalles de método de pago y estado
- ✅ Porcentajes de distribución
- ✅ Rango de fechas personalizado
- ✅ Filtrado por estado, cliente, o fecha
- ✅ Diseño profesional para presentaciones

#### Cómo usar:
1. Ve a **Admin → Gestionar Pedidos**
2. (Opcional) Aplica filtros: Estado, Cliente, Fecha
3. Haz clic en el botón **Descargar Reporte** (verde)
4. Se generará un reporte con los datos filtrados
5. Imprime o guarda como PDF desde tu navegador

#### Parámetros URL:
```
include/generar_reporte_consolidado.php?estado=entregado&cliente=Juan&fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```
- `estado`: Filtrar por estado (pendiente, procesando, enviado, entregado, cancelado)
- `cliente`: Buscar por nombre/email del cliente
- `fecha_inicio`: Fecha inicial del rango (YYYY-MM-DD)
- `fecha_fin`: Fecha final del rango (YYYY-MM-DD)

---

## Descarga como PDF

### Opción 1: Usar el navegador (Recomendado)
1. Abre el reporte en tu navegador
2. Presiona **Ctrl+P** (Windows) o **Cmd+P** (Mac)
3. Selecciona "Guardar como PDF"
4. Elige la ubicación y nombre del archivo
5. Haz clic en "Guardar"

### Opción 2: Instalación de TCPDF (Opcional)
Si deseas descargas automáticas sin pasar por el navegador:

1. Instala TCPDF en tu servidor:
```bash
composer require tecnickcom/tcpdf
```

2. El sistema detectará automáticamente TCPDF y proporcionará descargas directas en PDF

---

## Características de los Reportes

### Diseño
- **Gradientes profesionales:** Colores consistentes con la marca (azul/púrpura)
- **Tipografía clara:** Segoe UI, fuentes web estándar
- **Responsive:** Se adapta a diferentes tamaños de pantalla
- **Optimizado para impresión:** Colores y espacios ajustados para papel

### Datos Incluidos

**Reporte Individual:**
- Encabezado con nombre de la tienda
- ID de transacción y número de orden
- Estado actual del pedido
- Información completa del cliente
- Lista de productos con cantidades y precios
- Total final con desglose de impuestos

**Reporte Consolidado:**
- Resumen ejecutivo (total de pedidos, ventas totales)
- Tabla completa de transacciones
- Resumen por estado de entrega
- Análisis porcentual
- Información de contacto

---

## Ejemplos de Uso

### Caso 1: Generar reporte de un pedido específico
```
Pasos:
1. Gestionar Pedidos → Buscar pedido
2. Hacer clic en PDF
3. Imprimir con Ctrl+P → Guardar como PDF
```

### Caso 2: Crear reporte de ventas mensuales entregadas
```
Pasos:
1. Gestionar Pedidos → Seleccionar estado "Entregado"
2. Seleccionar rango de fechas
3. Click "Descargar Reporte"
4. Imprimir como PDF
```

### Caso 3: Analizar ventas de un cliente específico
```
Pasos:
1. Gestionar Pedidos → Buscar por cliente
2. Click "Descargar Reporte"
3. Obtener análisis completo de ese cliente
```

---

## Notas Técnicas

- **Seguridad:** Se valida el ID de pedido antes de mostrar datos
- **Rendimiento:** Consultas optimizadas con prepared statements
- **Compatibilidad:** Funciona en todos los navegadores modernos
- **Codificación:** UTF-8 para caracteres especiales
- **Precisión:** Cálculos con format number_format() para 2 decimales

---

## Solución de Problemas

### Los reportes se ven vacíos
- Verifica que exista el ID de pedido
- Asegúrate de que el cliente tenga datos completos en la BD

### No se descarga como PDF
- Usa Ctrl+P desde el navegador para guardar como PDF
- Si tienes TCPDF instalado, la descarga ser directa

### Estilos no se ven bien
- Limpia el caché del navegador (Ctrl+Shift+Delete)
- Intenta con otro navegador (Chrome, Firefox, Edge)

---

## Versión
**v1.0** - Marzo 2026

## Autor
Sistema de Gestión de Compras

---
