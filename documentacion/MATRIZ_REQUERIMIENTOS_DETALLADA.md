# 📋 MATRIZ COMPLETA DE REQUERIMIENTOS CON REGLAS DE NEGOCIO

## Sistema E-Commerce "D'gala"
**Versión:** 2.0 | **Fecha:** 24 de Febrero de 2026  
**Estado:** 95% Implementado

---

## 🎯 REQUERIMIENTOS FUNCIONALES (RF)

### 3.1 AUTENTICACIÓN Y USUARIOS

#### RF-001: Registro de Clientes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-001 |
| **Tabla de Matriz** | 3.1 Autenticación y Gestión de Usuarios |
| **Tabla BD** | `clientes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Autenticación |
| **Reglas de Negocio** | • El email debe ser único en todo el sistema<br>• La contraseña mínima es 8 caracteres (mayúsculas, minúsculas, números)<br>• El documento de identidad debe ser validado para evitar duplicados |

#### RF-002: Login de Clientes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-002 |
| **Tabla de Matriz** | 3.1 Autenticación y Gestión de Usuarios |
| **Tabla BD** | `clientes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Autenticación |
| **Reglas de Negocio** | • El cliente solo puede iniciar sesión si su estado es 'activo'<br>• Se bloquea acceso temporal tras 3 intentos fallidos de login<br>• La sesión expira después de 30 minutos de inactividad |

#### RF-003: Recuperación de Contraseña
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-003 |
| **Tabla de Matriz** | 3.1 Autenticación y Gestión de Usuarios |
| **Tabla BD** | `clientes`, `auditoria` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Autenticación |
| **Reglas de Negocio** | • El token de recuperación es válido solo por 24 horas<br>• Se permite máximo 3 solicitudes de recuperación al día por email<br>• Cada cambio de contraseña se registra en tabla de auditoría |

#### RF-004: Logout
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-004 |
| **Tabla de Matriz** | 3.1 Autenticación y Gestión de Usuarios |
| **Tabla BD** | `sesiones` (implícita) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Autenticación |
| **Reglas de Negocio** | • Se destruyen todas las variables de sesión del usuario<br>• Se borra la cookie de sesión del navegador<br>• Se registra el logout en tabla de auditoría para control de acceso |

#### RF-005: Autenticación Admin
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-005 |
| **Tabla de Matriz** | 3.1 Autenticación y Gestión de Usuarios |
| **Tabla BD** | `admin`, `permisos` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Autenticación |
| **Reglas de Negocio** | • Existen 2 roles: Administrador (acceso total) y Operario (acceso limitado)<br>• Solo Administrador puede crear otros usuarios admin<br>• Cada acción de admin registra usuario, fecha, hora, IP |

#### RF-006: Bloqueo por Intentos Fallidos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-006 |
| **Tabla de Matriz** | 3.1 Autenticación y Gestión de Usuarios |
| **Tabla BD** | `auditoria`, `intentos_login` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | 🟨 PARCIALMENTE IMPLEMENTADO |
| **Módulo** | Autenticación |
| **Reglas de Negocio** | • Se registra cada intento fallido con timestamp e IP<br>• Bloqueo temporal de 15 minutos después de 3 intentos<br>• El admin puede desbloquear cuenta manualmente |

---

### 3.2 CATÁLOGO Y PRODUCTOS

#### RF-007: Catálogo de Productos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-007 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `productos`, `categorias` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Catálogo |
| **Reglas de Negocio** | • Solo se muestran productos con estado 'activo'<br>• El catálogo se pagina en grupos de 20 productos máximo<br>• El descuento mostrado es porcentaje del precio original |

#### RF-008: Detalles de Producto
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-008 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `productos` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Catálogo |
| **Reglas de Negocio** | • La página muestra solo productos existentes, 404 si no existe<br>• Se muestran todas las variantes disponibles del producto<br>• Se calcula automáticamente el precio con descuento |

#### RF-009: Filtrado por Categoría
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-009 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `productos`, `categorias` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Catálogo |
| **Reglas de Negocio** | • Solo se muestran productos activos de la categoría seleccionada<br>• Si categoría no existe, se muestra página 404<br>• Se respeta el ordenamiento por popularidad o precio |

#### RF-010: Búsqueda de Productos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-010 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `productos` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Catálogo |
| **Reglas de Negocio** | • La búsqueda es insensible a mayúsculas/minúsculas<br>• Máximo 50 resultados por búsqueda<br>• Se busca en nombre y descripción del producto |

#### RF-011: Stock en Tiempo Real
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-011 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `productos`, `productos_variantes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Catálogo |
| **Reglas de Negocio** | • El stock se actualiza en tiempo real después de cada compra<br>• Si stock = 0, el producto muestra estado 'Agotado'<br>• El stock no puede ser negativo (validación antes de guardar) |

#### RF-012: CRUD de Productos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-012 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `productos` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Administración |
| **Reglas de Negocio** | • Solo admin puede crear/editar/eliminar productos<br>• Las imágenes se validan por tipo (JPG, PNG, WebP) y se redimensionan<br>• El precio debe ser mayor a 0 y menor a 9,999,999.99 |

#### RF-013: CRUD de Categorías
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-013 |
| **Tabla de Matriz** | 3.2 Catálogo y Gestión de Productos |
| **Tabla BD** | `categorias` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Administración |
| **Reglas de Negocio** | • No puede existir categoría con nombre duplicado<br>• No se puede eliminar categoría con productos activos<br>• Cada categoría puede tener máximo 1000 productos |

---

### 3.3 SISTEMA DE VARIANTES

#### RF-014: Catálogo de Tallas
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-014 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `c_tallas` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Las tallas predefinidas son reutilizables en múltiples productos<br>• No se puede eliminar talla si está asignada a variante activa<br>• Máximo 20 tallas diferentes en el sistema |

#### RF-015: Catálogo de Colores
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-015 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `c_colores` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Código hexadecimal debe tener formato válido (#RRGGBB)<br>• No puede existir color con nombre duplicado<br>• Máximo 30 colores diferentes en el sistema |

#### RF-016: Combinaciones de Variantes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-016 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `productos_variantes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Cada combinación Talla+Color es única por producto<br>• No se pueden crear dos variantes idénticas del mismo producto<br>• Cada variante requiere ambos valores (talla + color) |

#### RF-017: Precio y Stock por Variante
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-017 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `productos_variantes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • El precio de variante puede diferir del precio base del producto<br>• Stock mínimo por variante es 0, máximo 99,999 unidades<br>• El precio debe validarse antes de guardar (> 0) |

#### RF-018: Interfaz Visual de Variantes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-018 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `c_tallas`, `c_colores`, `productos_variantes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Tallas se muestran como botones cuadrados<br>• Colores se muestran como círculos con código hex<br>• Las opciones sin stock se deshabilitan visualmente |

#### RF-019: Filtrado Dinámico Bidireccional
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-019 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `productos_variantes`, `c_tallas`, `c_colores` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Al seleccionar talla, se muestran solo colores disponibles para esa talla<br>• Al seleccionar color, se muestran solo tallas disponibles para ese color<br>• No se permite seleccionar combinación sin stock disponible |

#### RF-020: Mostrar Variante Exacta
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-020 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `productos_variantes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Se muestra precio exacto de la variante seleccionada<br>• Se valida stock antes de permitir agregar al carrito<br>• Cambios se reflejan sin recargar la página |

#### RF-021: Agregar Múltiples Variantes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-021 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `sesiones` (carrito temporal) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Se permite agregar diferentes variantes del mismo producto<br>• Cada variante se trata como item separado en carrito<br>• Se validar stock total disponible de la variante |

#### RF-022: Compatibilidad Sistema Antiguo
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-022 |
| **Tabla de Matriz** | 3.3 Sistema de Variantes (Tallas y Colores) |
| **Tabla BD** | `productos`, `productos_variantes` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Variantes |
| **Reglas de Negocio** | • Productos sin variantes siguen funcionando normalmente<br>• El sistema detecta automáticamente si producto tiene variantes<br>• Ambos tipos de productos se pueden vender sin problemas |

---

### 3.4 CARRITO DE COMPRAS

#### RF-023: Agregar al Carrito
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-023 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `sesiones` (carrito temporal) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • La cantidad debe estar entre 1 y 12 unidades por item<br>• No se puede agregar más cantidad que el stock disponible<br>• Si el producto ya existe, se suma la cantidad al existente |

#### RF-024: Ver Contenido Carrito
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-024 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `productos`, `sesiones` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • Se muestra el listado de items con precio unit, cantidad, subtotal<br>• Se calcula automáticamente el total del carrito<br>• Se muestran descuentos aplicables por producto |

#### RF-025: Actualizar Cantidad (AJAX)
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-025 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `sesiones` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • Se valida stock disponible antes de permitir aumento<br>• Cambios se reflejan en tiempo real sin recargar<br>• Se bloquea cantidad > 12 unidades |

#### RF-026: Eliminar del Carrito
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-026 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `sesiones` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • Se elimina el item del carrito inmediatamente<br>• Se puede revertir dentro de la misma sesión<br>• Se actualiza el total del carrito automáticamente |

#### RF-027: Calcular Total
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-027 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `productos`, `sesiones` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • Total = Σ(precio_unit × cantidad) - descuentos<br>• Se aplican descuentos por producto (no globales aún)<br>• Se muestra desglose de subtotal, descuentos e impuestos |

#### RF-028: Vaciar Carrito
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-028 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `sesiones` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • Requiere confirmación antes de vaciar<br>• Se destruye completamente la sesión del carrito<br>• Se registra la acción en logs |

#### RF-029: Carrito Vacío
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-029 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `sesiones` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • Se muestra mensaje amigable 'Tu carrito está vacío'<br>• Se ofrece link al catálogo para continuar comprando<br>• Se limpia cualquier cookie de carrito anterior |

#### RF-030: Cupones de Descuento
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-030 |
| **Tabla de Matriz** | 3.4 Carrito de Compras |
| **Tabla BD** | `cupones` |
| **Prioridad** | 🟢 BAJA |
| **Estado** | 📋 PLANIFICADO |
| **Módulo** | Carrito |
| **Reglas de Negocio** | • El code de cupón debe ser válido y estar vigente<br>• Se valida el número máximo de usos del cupón<br>• El descuento se aplica al monto total sin incluir envío |

---

### 3.5 COMPRA Y PAGO

#### RF-031: Página de Checkout
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-031 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra`, `sesiones` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Compra |
| **Reglas de Negocio** | • Solo clientes logueados pueden acceder a checkout<br>• Se valida que carrito no esté vacío<br>• Se muestra resumen final antes de pagar |

#### RF-032: Integración PayPal
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-032 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Pago |
| **Reglas de Negocio** | • Solo se acepta el pago si el monto coincide con total carrito<br>• La compra se confirma solo cuando PayPal retorna 'COMPLETED'<br>• Se valida que email del comprador coincida con PayPal |

#### RF-033: Registrar Transacción
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-033 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Pago |
| **Reglas de Negocio** | • Se almacena ID transacción PayPal (id_transaccion)<br>• Se registra estado actual (COMPLETED, CANCELED, etc.)<br>• Se guarda fecha/hora y monto total |

#### RF-034: Detalles de Items
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-034 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `detalle_compra` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Pago |
| **Reglas de Negocio** | • Se guarda snapshot del producto al momento de compra<br>• Cada item registra id_producto, precio_vendido, cantidad<br>• No se modifican estos datos si el producto se actualiza después |

#### RF-035: Actualizar Stock
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-035 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `productos`, `productos_variantes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Pago |
| **Reglas de Negocio** | • Se decrementa stock después de pago confirmado<br>• La resta es automática y no puede quedar negativa<br>• Se registra quién hizo la resta (sistema automático) |

#### RF-036: Email de Confirmación
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-036 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra`, `clientes` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Pago |
| **Reglas de Negocio** | • Se envía al email del cliente registrado<br>• Incluye número de orden, productos, total y fecha<br>• Se incluye link para ver detalles de la compra |

#### RF-037: Página de Confirmación
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-037 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra`, `detalle_compra` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Pago |
| **Reglas de Negocio** | • Se muestra número de orden único<br>• Se valida que el usuario sea propietario de la compra<br>• Se ofrece opción de descargar PDF de la orden |

#### RF-038: Historial de Compras
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-038 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra`, `detalle_compra` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Compra |
| **Reglas de Negocio** | • El cliente solo ve sus propias compras<br>• Se pagina máximo 10 compras por página<br>• Se muestra ordenado por fecha decreciente (más recientes arriba) |

#### RF-039: Cambio de Estado de Pedidos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-039 |
| **Tabla de Matriz** | 3.5 Proceso de Compra y Pago |
| **Tabla BD** | `compra` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | 🟨 PARCIALMENTE IMPLEMENTADO |
| **Módulo** | Administración |
| **Reglas de Negocio** | • Estados posibles: PENDING, PROCESSING, SHIPPED, DELIVERED, CANCELED<br>• Solo admin puede cambiar estado<br>• Se registra quién cambió el estado y cuándo |

---

### 3.6 PANEL ADMINISTRATIVO

#### RF-040: Dashboard Admin
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-040 |
| **Tabla de Matriz** | 3.6 Panel Administrativo |
| **Tabla BD** | `admin` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Administración |
| **Reglas de Negocio** | • Solo accesible si usuario está logueado como admin<br>• Se valida token CSRF en cada acción<br>• Sesión expira después de 1 hora de inactividad |

#### RF-041: Gestión de Categorías
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-041 |
| **Tabla de Matriz** | 3.6 Panel Administrativo |
| **Tabla BD** | `categorias` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Administración |
| **Reglas de Negocio** | • Se puede crear, editar, eliminar categorías<br>• No puede existir categoría duplicada por nombre<br>• Se valida que no tenga productos activos antes de eliminar |

#### RF-042: Gestión de Productos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RF-042 |
| **Tabla de Matriz** | 3.6 Panel Administrativo |
| **Tabla BD** | `productos` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Administración |
| **Reglas de Negocio** | • Se validan datos antes de guardar (precio > 0)<br>• Las imágenes se redimensionan automáticamente<br>• Se permite cambiar estado sin afectar otros datos |

---

## 🔧 REQUERIMIENTOS NO FUNCIONALES (RNF)

### 4.1 SEGURIDAD

#### RNF-001: Cifrado de Contraseñas
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-001 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | `clientes`, `admin` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • Costo de bcrypt mínimo es 10 iteraciones<br>• Las contraseñas antiguas no se pueden recuperar (one-way)<br>• Se regeneran hashes cada 90 días en auditoría |

#### RNF-002: Protección SQL Injection
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-002 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | Todas las tablas |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • 100% de consultas usan prepared statements (PDO)<br>• No se permite concatenación de variables en queries<br>• Toda entrada se valida contra patrón esperado |

#### RNF-003: Protección XSS
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-003 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | N/A (Frontend) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • Toda salida HTML usa htmlspecialchars()<br>• Atributos HTML usan htmlentities()<br>• Input se sanitiza al recibir en servidor |

#### RNF-004: Protección CSRF
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-004 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | `sesiones` |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • Cada formulario incluye token CSRF único<br>• Token se regenera cada vez que se valida<br>• Token expira si sesión expira |

#### RNF-005: HTTPS/SSL
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-005 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | N/A (Infraestructura) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | 🟨 PARCIALMENTE IMPLEMENTADO |
| **Módulo** | Infraestructura |
| **Reglas de Negocio** | • Desarrollo local sin SSL<br>• Producción **REQUIERE** certificado SSL válido<br>• Todo tráfico debe ser HTTPS, redirect automático |

#### RNF-006: Validación Servidor
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-006 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | N/A (Lógica) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • Validación OBLIGATORIA en servidor (no solo cliente)<br>• Se valida tipo, rango y formato de datos<br>• Mensajes de error no revelan estructura BD |

#### RNF-007: Rate Limiting
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-007 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | `intentos_login` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ❌ NO INICIADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • Máximo 5 intentos fallidos por IP en 15 minutos<br>• Bloqueo temporal de 30 minutos después de exceder<br>• Se registra cada intento fallido con IP y timestamp |

#### RNF-008: Auditoría de Cambios
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-008 |
| **Tabla de Matriz** | 4.1 Seguridad |
| **Tabla BD** | `auditoria` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ❌ NO INICIADO |
| **Módulo** | Seguridad |
| **Reglas de Negocio** | • Se registra cada acción de admin (CREATE, UPDATE, DELETE)<br>• Se guarda usuario, tabla, datos anteriores, nuevos datos, fecha, IP<br>• Logs de auditoría son inmutables |

---

### 4.2 DESEMPEÑO

#### RNF-009: Tiempo Carga < 2 segundos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-009 |
| **Tabla de Matriz** | 4.2 Desempeño y Escalabilidad |
| **Tabla BD** | `productos`, `categorias` |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Desempeño |
| **Reglas de Negocio** | • Se implementa paginación máximo 20 productos/página<br>• Se activa lazy loading de imágenes<br>• Se cachea listado de categorías |

#### RNF-010: Índices en BD
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-010 |
| **Tabla de Matriz** | 4.2 Desempeño y Escalabilidad |
| **Tabla BD** | Todas las tablas |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Desempeño |
| **Reglas de Negocio** | • Índices en campos de búsqueda y filtrado frecuentes<br>• EXPLAIN PLAN verificado para queries lentas<br>• Se revisan índices trimestralmente |

#### RNF-011: Respuesta AJAX < 500ms
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-011 |
| **Tabla de Matriz** | 4.2 Desempeño y Escalabilidad |
| **Tabla BD** | Variantes según AJAX |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Desempeño |
| **Reglas de Negocio** | • Llamadas AJAX retornan en < 500ms<br>• Se envía solo datos necesarios (sin datos redundantes)<br>• Se activa compresión gzip en servidor |

#### RNF-012: Usuarios Concurrentes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-012 |
| **Tabla de Matriz** | 4.2 Desempeño y Escalabilidad |
| **Tabla BD** | N/A (Infraestructura) |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | 🟨 PARCIALMENTE IMPLEMENTADO |
| **Módulo** | Infraestructura |
| **Reglas de Negocio** | • Servidor debe soportar 500 usuarios concurrentes<br>• Escalabilidad requiere load balancing y BD replicada<br>• Implementar cache distribuida (Redis) |

#### RNF-013: Optimización de Imágenes
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-013 |
| **Tabla de Matriz** | 4.2 Desempeño y Escalabilidad |
| **Tabla BD** | `productos` (almacenamiento) |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Desempeño |
| **Reglas de Negocio** | • Redimensionamiento automático a máximo 800px ancho<br>• Compresión JPG a 80% calidad<br>• Soporte para WebP como alternativa |

---

### 4.3 DISPONIBILIDAD

#### RNF-014: Disponibilidad 99.5%
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-014 |
| **Tabla de Matriz** | 4.3 Disponibilidad y Confiabilidad |
| **Tabla BD** | N/A (Infraestructura) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | 🟨 PARCIALMENTE IMPLEMENTADO |
| **Módulo** | Infraestructura |
| **Reglas de Negocio** | • Máximo 3.6 horas downtime permitidas por mes<br>• Se requiere servidor backup para failover automático<br>• Monitoreo debe alertar si disponibilidad < 99.5% |

#### RNF-015: Backups Automáticos
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-015 |
| **Tabla de Matriz** | 4.3 Disponibilidad y Confiabilidad |
| **Tabla BD** | Todas las tablas |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ❌ NO INICIADO |
| **Módulo** | Infraestructura |
| **Reglas de Negocio** | • Backups automáticos cada 24 horas<br>• Retención mínima de 30 días de backups<br>• Se verifica restore mensualmente |

#### RNF-016: Manejo de Errores
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-016 |
| **Tabla de Matriz** | 4.3 Disponibilidad y Confiabilidad |
| **Tabla BD** | N/A (Lógica) |
| **Prioridad** | 🔴 ALTA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Confiabilidad |
| **Reglas de Negocio** | • Errores de BD no exponen detalles técnicos<br>• Usuario ve mensajes genéricos amigables<br>• Se registran errores en log de servidor |

#### RNF-017: Integridad Referencial
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-017 |
| **Tabla de Matriz** | 4.3 Disponibilidad y Confiabilidad |
| **Tabla BD** | Todas las tablas |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Confiabilidad |
| **Reglas de Negocio** | • FOREIGN KEY constraints activos en todas las relaciones<br>• ON DELETE CASCADE/RESTRICT según política de negocio<br>• Validación también en aplicación |

---

### 4.4 COMPATIBILIDAD

#### RNF-018: Compatibilidad Navegadores
| Aspecto | Descripción |
|---------|------------|
| **ID** | RNF-018 |
| **Tabla de Matriz** | 4.4 Compatibilidad y Estándares |
| **Tabla BD** | N/A (Frontend) |
| **Prioridad** | 🟠 MEDIA |
| **Estado** | ✅ IMPLEMENTADO |
| **Módulo** | Frontend |
| **Reglas de Negocio** | • Compatible con Chrome, Firefox, Safari, Edge v80+<br>• HTML5/CSS3 estándares correctos<br>• Responsive design mobile-first |

---

## 📊 RESUMEN FINAL

| Métrica | Cantidad | Porcentaje |
|---------|----------|-----------|
| **Total Requerimientos** | 60 | 100% |
| **Implementados** | 51 | 85% |
| **Parcialmente Implementados** | 6 | 10% |
| **Planificados** | 2 | 3.3% |
| **No Iniciados** | 3 | 5% |

**Cada requerimiento incluye:**
- ✅ Tabla de Matriz (sección a la que pertenece)
- ✅ Tabla de Base de Datos (tablas involucradas)
- ✅ 3 Reglas de Negocio específicas

---

**Documento:** Matriz Completa de Requerimientos con Reglas de Negocio  
**Versión:** 2.0 | **Fecha:** 24 de Febrero de 2026  
**Estado:** Aprobado para Revisión
