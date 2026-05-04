<?php
// Generar URL del API de forma segura
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_path = dirname(dirname(dirname(__FILE__)));
$api_url = $protocol . $host . '/e-comerse2/Admin/include/api_dashboard.php';
?>

<div class="title-wrapper pt-30">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon purple">
                  <i class="lni lni-cart-full"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Órdenes Totales</h6>
                  <h3 class="text-bold mb-10" id="totalOrders">0</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-arrow-up"></i> <span id="ordersTrend">0%</span>
                    <span class="text-gray">(Este mes)</span>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon success">
                  <i class="lni lni-dollar"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Ingresos Totales</h6>
                  <h3 class="text-bold mb-10" id="totalRevenue">$0.00</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-arrow-up"></i> <span id="revenueTrend">0%</span>
                    <span class="text-gray">Incremento</span>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon primary">
                  <i class="lni lni-user"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Usuarios Registrados</h6>
                  <h3 class="text-bold mb-10" id="totalUsers">0</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-arrow-up"></i> <span id="usersTrend">0%</span>
                    <span class="text-gray">Nuevos</span>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon orange">
                  <i class="lni lni-package"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Productos Activos</h6>
                  <h3 class="text-bold mb-10" id="totalProducts">0</h3>
                  <p class="text-sm text-success">
                    <i class="lni lni-arrow-up"></i> <span id="productsTrend">0%</span>
                    <span class="text-gray">En stock</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-8">
              <div class="card-style mb-30">
                <div class="title d-flex flex-wrap justify-content-between">
                  <div class="left">
                    <h6 class="text-medium mb-10">Compras Mensuales</h6>
                    <h3 class="text-bold">Estadísticas</h3>
                  </div>
                </div>
                <div class="chart">
                  <canvas id="salesChart" style="width: 100%; height: 350px;"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-30">Distribución de Categorías</h6>
                </div>
                <div class="chart">
                  <canvas id="categoriesChart" style="width: 100%; height: 350px;"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-30">Productos Más Vendidos</h6>
                </div>
                <div class="table-responsive">
                  <table class="table top-selling-table">
                    <thead>
                      <tr>
                        <th><h6 class="text-sm text-medium">Producto</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Categoría</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Vendidos</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Ingresos</h6></th>
                      </tr>
                    </thead>
                    <tbody id="topProductsTable">
                      <tr><td colspan="4" class="text-center text-gray">Cargando...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-30">Crecimiento de Usuarios</h6>
                </div>
                <div class="chart">
                  <canvas id="usersChart" style="width: 100%; height: 300px;"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-5">
              <div class="card-style calendar-card mb-30">
                <h6 class="text-medium mb-20">Calendario de Eventos</h6>
                <div id="calendar"></div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card-style mb-30">
                <div class="title">
                  <h6 class="text-medium mb-30">Últimas Órdenes</h6>
                </div>
                <div class="table-responsive">
                  <table class="table top-selling-table">
                    <thead>
                      <tr>
                        <th><h6 class="text-sm text-medium">Pedido #</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Cliente</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Monto</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Fecha</h6></th>
                        <th class="min-width"><h6 class="text-sm text-medium">Estado</h6></th>
                      </tr>
                    </thead>
                    <tbody id="recentOrdersTable">
                      <tr><td colspan="5" class="text-center text-gray">Cargando...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- end container -->
      </section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<script>
// URL del API inyectada desde PHP
const dashboardAPI = '<?php echo $api_url; ?>';
console.log('API URL configurada:', dashboardAPI);
let charts = {};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard inicializando...');
    loadDashboardData();
});

// Cargar todos los datos del dashboard
async function loadDashboardData() {
    try {
        console.log('Iniciando carga de datos desde:', dashboardAPI);
        
        // Cargar estadísticas
        console.log('Cargando stats...');
        const statsRes = await fetch(dashboardAPI + '?action=stats');
        if(!statsRes.ok) throw new Error('Error en stats: ' + statsRes.status + ' - ' + statsRes.statusText);
        const stats = await statsRes.json();
        console.log('Stats recibidos:', stats);
        updateStats(stats);
        
        // Cargar productos top
        console.log('Cargando products...');
        const productsRes = await fetch(dashboardAPI + '?action=products');
        if(!productsRes.ok) throw new Error('Error en products: ' + productsRes.status);
        const products = await productsRes.json();
        console.log('Productos recibidos:', products);
        updateTopProducts(products);
        
        // Cargar órdenes recientes
        console.log('Cargando orders...');
        const ordersRes = await fetch(dashboardAPI + '?action=orders');
        if(!ordersRes.ok) throw new Error('Error en orders: ' + ordersRes.status);
        const orders = await ordersRes.json();
        console.log('Órdenes recibidas:', orders);
        updateRecentOrders(orders);
        if(Array.isArray(orders)) {
            initCalendar(orders);
        }
        
        // Cargar datos para gráficos
        console.log('Cargando sales...');
        const salesRes = await fetch(dashboardAPI + '?action=sales');
        if(!salesRes.ok) throw new Error('Error en sales: ' + salesRes.status);
        const sales = await salesRes.json();
        console.log('Ventas recibidas:', sales);
        setTimeout(() => initSalesChart(sales), 100);
        
        console.log('Cargando categories...');
        const categoriesRes = await fetch(dashboardAPI + '?action=categories');
        if(!categoriesRes.ok) throw new Error('Error en categories: ' + categoriesRes.status);
        const categories = await categoriesRes.json();
        console.log('Categorías recibidas:', categories);
        setTimeout(() => initCategoriesChart(categories), 100);
        
        console.log('Cargando users...');
        const usersRes = await fetch(dashboardAPI + '?action=users');
        if(!usersRes.ok) throw new Error('Error en users: ' + usersRes.status);
        const users = await usersRes.json();
        console.log('Usuarios recibidos:', users);
        setTimeout(() => initUsersChart(users), 100);
        
    } catch(error) {
        console.error('Error cargando datos:', error);
        alert('Error cargando el dashboard: ' + error.message);
    }
}

// Actualizar estadísticas
function updateStats(stats) {
    try {
        document.getElementById('totalOrders').textContent = (stats.total_orders || 0).toString();
        document.getElementById('totalRevenue').textContent = '$' + (stats.total_revenue || 0).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('totalUsers').textContent = (stats.total_users || 0).toString();
        document.getElementById('totalProducts').textContent = (stats.total_products || 0).toString();
        
        document.getElementById('ordersTrend').textContent = (stats.orders_trend >= 0 ? '+' : '') + (stats.orders_trend || 0).toFixed(2) + '%';
        document.getElementById('revenueTrend').textContent = (stats.revenue_trend >= 0 ? '+' : '') + (stats.revenue_trend || 0).toFixed(2) + '%';
        document.getElementById('usersTrend').textContent = '+' + (stats.total_users || 0);
        
        console.log('✓ Estadísticas actualizadas');
    } catch(e) {
        console.error('Error en updateStats:', e);
    }
}

// Actualizar tabla de productos
function updateTopProducts(products) {
    try {
        const tbody = document.getElementById('topProductsTable');
        if(!tbody) {
            console.error('Elemento topProductsTable no encontrado');
            return;
        }
        
        if(!Array.isArray(products) || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-gray">Sin datos disponibles</td></tr>';
            return;
        }
        
        tbody.innerHTML = products.map(p => `
            <tr>
                <td><p class="text-sm"><strong>${p.nombre || 'Sin nombre'}</strong></p></td>
                <td><p class="text-sm">-</p></td>
                <td><p class="text-sm font-weight-bold">${p.vendidos || 0}</p></td>
                <td><p class="text-sm text-success">$${(p.ingresos || 0).toFixed(2)}</p></td>
            </tr>
        `).join('');
        
        console.log('✓ Tabla de productos actualizada');
    } catch(e) {
        console.error('Error en updateTopProducts:', e);
    }
}

// Actualizar tabla de órdenes recientes
function updateRecentOrders(orders) {
    try {
        const tbody = document.getElementById('recentOrdersTable');
        if(!tbody) {
            console.error('Elemento recentOrdersTable no encontrado');
            return;
        }
        
        if(!Array.isArray(orders) || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray">Sin órdenes</td></tr>';
            return;
        }
        
        tbody.innerHTML = orders.map(o => {
            const fecha = new Date(o.fecha).toLocaleDateString('es-ES');
            return `
                <tr>
                    <td><p class="text-sm"><strong>#${o.numero || o.id}</strong></p></td>
                    <td><p class="text-sm">${o.cliente || '-'}</p></td>
                    <td><p class="text-sm text-success">$${(o.total || 0).toFixed(2)}</p></td>
                    <td><p class="text-sm">${fecha}</p></td>
                    <td><span class="status-btn ${o.estado_class || 'secondary-btn'}">${o.estado_label || 'Desconocido'}</span></td>
                </tr>
            `;
        }).join('');
        
        console.log('✓ Tabla de órdenes actualizada');
    } catch(e) {
        console.error('Error en updateRecentOrders:', e);
    }
}

// Gráfico de compras mensuales
function initSalesChart(sales) {
    try {
        if(!Array.isArray(sales) || sales.length === 0) {
            console.log('Sin datos de ventas');
            return;
        }
        
        const ctx = document.getElementById('salesChart');
        if(!ctx) {
            console.error('Canvas salesChart no encontrado');
            return;
        }
        
        const months = sales.map(s => {
            const [year, month] = s.mes.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('es-ES', {month: 'short', year: '2-digit'});
        });
        const revenues = sales.map(s => parseFloat(s.total) || 0);
        
        if(charts.sales) charts.sales.destroy();
        
        charts.sales = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: revenues,
                    borderColor: '#6c5ce7',
                    backgroundColor: 'rgba(108, 92, 231, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#6c5ce7',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-ES');
                            }
                        }
                    }
                }
            }
        });
        
        console.log('✓ Gráfico de ventas creado');
    } catch(e) {
        console.error('Error en initSalesChart:', e);
    }
}

// Gráfico de categorías
function initCategoriesChart(categories) {
    try {
        if(!Array.isArray(categories) || categories.length === 0) {
            console.log('Sin datos de categorías');
            return;
        }
        
        const ctx = document.getElementById('categoriesChart');
        if(!ctx) {
            console.error('Canvas categoriesChart no encontrado');
            return;
        }
        
        const labels = categories.map(c => c.label || 'Sin nombre');
        const data = categories.map(c => parseInt(c.value) || 0);
        const colors = ['#6c5ce7', '#00b894', '#fdcb6e', '#d63031', '#00cec9', '#a29bfe', '#74b9ff', '#ff7675'];
        
        if(charts.categories) charts.categories.destroy();
        
        charts.categories = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        console.log('✓ Gráfico de categorías creado');
    } catch(e) {
        console.error('Error en initCategoriesChart:', e);
    }
}

// Gráfico de usuarios
function initUsersChart(users) {
    try {
        if(!Array.isArray(users) || users.length === 0) {
            console.log('Sin datos de usuarios');
            return;
        }
        
        const ctx = document.getElementById('usersChart');
        if(!ctx) {
            console.error('Canvas usersChart no encontrado');
            return;
        }
        
        const months = users.map(u => {
            const [year, month] = u.mes.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('es-ES', {month: 'short', year: '2-digit'});
        });
        const counts = users.map(u => parseInt(u.usuarios) || 0);
        
        if(charts.users) charts.users.destroy();
        
        charts.users = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Usuarios Nuevos',
                    data: counts,
                    backgroundColor: '#00b894',
                    borderColor: '#00a871',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        console.log('✓ Gráfico de usuarios creado');
    } catch(e) {
        console.error('Error en initUsersChart:', e);
    }
}

// Inicializar calendario
function initCalendar(orders) {
    try {
        const calendarEl = document.getElementById('calendar');
        if(!calendarEl) {
            console.log('Elemento calendar no encontrado, omitiendo');
            return;
        }
        
        const eventos = Array.isArray(orders) ? orders.map(o => ({
            title: 'Pedido #' + (o.numero || o.id),
            start: o.fecha,
            backgroundColor: o.estado === 'COMPLETED' ? '#00b894' : '#fdcb6e',
            borderColor: o.estado === 'COMPLETED' ? '#00a871' : '#f0b000'
        })) : [];
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            locale: 'es',
            height: 'auto',
            events: eventos
        });
        
        calendar.render();
        console.log('✓ Calendario creado');
    } catch(e) {
        console.error('Error en initCalendar:', e);
    }
}
</script>
