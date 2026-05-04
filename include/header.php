<style>
    /* ======================================================================
       HEADER BLACK & GOLD — v2: LAYOUT + SCROLL + RESPONSIVE + CART
       ====================================================================== */

    :root {
        --header-bg: #000000;
        --header-bg-blur: rgba(0, 0, 0, 0.96);
        --header-gold: #ffd700;
        --header-gold-dark: #e6b800;
        --header-text: #ffffff;
        --header-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --header-height: 72px;
    }

    body {
        padding-top: var(--header-height);
        margin: 0;
    }

    #mainHeader.header-premium {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: var(--header-height);
        background: var(--header-bg);
        border-bottom: 2px solid var(--header-gold);
        z-index: 9999;
        transition: background 0.3s ease,
                    backdrop-filter 0.3s ease,
                    box-shadow 0.3s ease;
        will-change: transform;
    }

    #mainHeader.header-premium.header-scrolled {
        background: var(--header-bg-blur);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 4px 28px rgba(0,0,0,0.45);
    }

    #mainHeader.header-premium.header-sticky-scroll {
        position: sticky;
        top: 0;
    }

    #mainHeader.header-premium .navbar-premium {
        height: var(--header-height);
        display: flex;
        align-items: center;
        padding: 0 2rem;
    }

    #mainHeader.header-premium .navbar-inner {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
    }

    #mainHeader.header-premium .navbar-brand-premium {
        flex-shrink: 0;
    }
    #mainHeader.header-premium .navbar-brand-premium img {
        max-height: 100px;
        width: auto;
        filter: brightness(0) invert(1);
        transition: transform 0.3s ease, filter 0.3s ease;
        display: block;
    }
    #mainHeader.header-premium .navbar-brand-premium:hover img {
        transform: scale(1.05);
        filter: brightness(0) invert(1) drop-shadow(0 0 6px rgba(255,215,0,0.5));
    }

    #mainHeader.header-premium .navbar-nav-premium {
        display: flex;
        flex-direction: row;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 0.5rem;
        align-items: center;
    }

    #mainHeader.header-premium .nav-link-premium {
        color: var(--header-text);
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        text-decoration: none;
        padding: 0.5rem 1.25rem;
        border-radius: 40px;
        position: relative;
        transition: var(--header-transition);
        white-space: nowrap;
    }
    #mainHeader.header-premium .nav-link-premium::after {
        content: '';
        position: absolute;
        bottom: 5px; left: 50%;
        transform: translateX(-50%) scaleX(0);
        width: 20px; height: 2px;
        background: var(--header-gold);
        border-radius: 2px;
        transition: transform 0.3s ease;
    }
    #mainHeader.header-premium .nav-link-premium:hover,
    #mainHeader.header-premium .nav-link-premium.active {
        color: var(--header-gold);
    }
    #mainHeader.header-premium .nav-link-premium:hover::after,
    #mainHeader.header-premium .nav-link-premium.active::after {
        transform: translateX(-50%) scaleX(1);
    }

    #mainHeader.header-premium .header-buttons-container {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-shrink: 0;
    }

    #mainHeader.header-premium .btn-cart-premium {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.35rem;
        border: 2px solid var(--header-gold);
        border-radius: 40px;
        color: var(--header-gold);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        background: transparent;
        transition: var(--header-transition);
        white-space: nowrap;
    }
    #mainHeader.header-premium .btn-cart-premium:hover {
        background: var(--header-gold);
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255,215,0,0.3);
    }

    #mainHeader.header-premium .badge-premium {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--header-gold);
        color: #000;
        font-size: 0.68rem;
        font-weight: 800;
        border-radius: 20px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        line-height: 1;
        transition: background 0.2s, color 0.2s, transform 0.2s;
    }
    #mainHeader.header-premium .btn-cart-premium:hover .badge-premium {
        background: #000;
        color: var(--header-gold);
    }

    @keyframes badgePop {
        0%   { transform: scale(1); }
        30%  { transform: scale(1.55); background: #fff; color: #000; }
        60%  { transform: scale(0.9); }
        100% { transform: scale(1); }
    }
    #mainHeader.header-premium .badge-premium.pop {
        animation: badgePop 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }
    #mainHeader.header-premium .badge-premium.empty {
        opacity: 0.4;
        transform: scale(0.85);
    }

    #mainHeader.header-premium .btn-user-premium,
    #mainHeader.header-premium .btn-login-premium {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.35rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        transition: var(--header-transition);
        white-space: nowrap;
        background: transparent;
        border: 2px solid rgba(255,255,255,0.5);
        color: white;
    }
    #mainHeader.header-premium .btn-login-premium {
        border-color: var(--header-gold);
        color: var(--header-gold);
    }
    #mainHeader.header-premium .btn-user-premium:hover,
    #mainHeader.header-premium .btn-login-premium:hover {
        background: var(--header-gold);
        border-color: var(--header-gold);
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255,215,0,0.25);
    }

    #mainHeader.header-premium .dropdown {
        position: relative;
    }
    #mainHeader.header-premium .dropdown-menu-premium {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        min-width: 210px;
        background: #111;
        border: 1px solid rgba(255,215,0,0.4);
        border-radius: 14px;
        box-shadow: 0 12px 36px rgba(0,0,0,0.5);
        padding: 0.4rem 0;
        z-index: 10001;
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
        transition: opacity 0.22s ease, transform 0.22s ease;
    }
    #mainHeader.header-premium .dropdown-menu-premium.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: all;
    }
    #mainHeader.header-premium .dropdown-item-premium {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.2rem;
        color: #ddd;
        text-decoration: none;
        font-size: 0.9rem;
        transition: background 0.2s, color 0.2s, padding-left 0.2s;
    }
    #mainHeader.header-premium .dropdown-item-premium i {
        color: var(--header-gold);
        width: 1.1rem;
        flex-shrink: 0;
    }
    #mainHeader.header-premium .dropdown-item-premium:hover {
        background: rgba(255,215,0,0.1);
        color: var(--header-gold);
        padding-left: 1.6rem;
    }
    #mainHeader.header-premium .dropdown-divider-premium {
        height: 1px;
        margin: 0.3rem 0;
        background: rgba(255,215,0,0.18);
    }

    /* ════════════════════════════════════════════
       HAMBURGER
       ════════════════════════════════════════════ */
    #mainHeader.header-premium .btn-hamburger {
        display: none;
        flex-direction: column;
        justify-content: space-between;
        width: 28px;
        height: 20px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        flex-shrink: 0;
    }
    #mainHeader.header-premium .btn-hamburger span {
        display: block;
        width: 100%;
        height: 2px;
        background: var(--header-gold);
        border-radius: 2px;
        transition: transform 0.3s ease, opacity 0.3s ease;
        transform-origin: center;
    }
    #mainHeader.header-premium .btn-hamburger.open span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
    }
    #mainHeader.header-premium .btn-hamburger.open span:nth-child(2) {
        opacity: 0; transform: scaleX(0);
    }
    #mainHeader.header-premium .btn-hamburger.open span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
    }

    /* ════════════════════════════════════════════
       RESPONSIVE ≤ 900px
       ════════════════════════════════════════════ */
    @media (max-width: 900px) {
        #mainHeader.header-premium .navbar-premium {
            padding: 0 1.25rem;
        }

        #mainHeader.header-premium .btn-hamburger {
            display: flex;
        }

        #mainHeader.header-premium .navbar-collapse-premium {
            position: absolute;
            top: var(--header-height);
            left: 0; right: 0;
            background: rgba(0,0,0,0.97);
            border-top: 1px solid rgba(255,215,0,0.15);
            border-bottom: 2px solid var(--header-gold);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0 1.5rem;
            gap: 0;
            max-height: 0;
            overflow: hidden;      /* necesario para la animación */
            opacity: 0;
            pointer-events: none;
            transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1),
                        opacity 0.3s ease,
                        padding 0.35s ease;
        }
        #mainHeader.header-premium .navbar-collapse-premium.open {
            max-height: 520px;
            opacity: 1;
            pointer-events: all;
            padding: 1rem 1.5rem 1.5rem;
            overflow: visible;
            /* ✅ centrar todo el contenido horizontalmente */
            align-items: center;
        }

        #mainHeader.header-premium .navbar-nav-premium {
            flex-direction: column;
            align-items: center;   /* ✅ centrado */
            width: 100%;
            gap: 0;
        }
        #mainHeader.header-premium .nav-link-premium {
            padding: 0.75rem 0.25rem;
            width: 100%;
            border-radius: 0;
            border-bottom: 1px solid rgba(255,215,0,0.1);
            font-size: 0.9rem;
            text-align: center;    /* ✅ texto centrado */
        }
        #mainHeader.header-premium .nav-link-premium::after { display: none; }

        #mainHeader.header-premium .header-buttons-container {
            flex-wrap: wrap;
            width: 100%;
            padding-top: 0.75rem;
            gap: 0.5rem;
            justify-content: center; /* ✅ botones centrados */
        }

        #mainHeader.header-premium .navbar-inner {
            flex-wrap: wrap;
            position: relative;
            justify-content: space-between;
        }
        #mainHeader.header-premium .navbar-collapse-premium {
            flex: unset;
            width: 100%;
        }

        /* ✅ FIX: el dropdown en mobile ocupa todo el ancho y se abre hacia arriba */
        #mainHeader.header-premium .navbar-collapse-premium .dropdown {
            width: 100%;
        }
        #mainHeader.header-premium .navbar-collapse-premium .btn-user-premium {
            width: 100%;
            justify-content: center;
        }
        #mainHeader.header-premium .navbar-collapse-premium .dropdown-menu-premium {
            top: auto;
            bottom: calc(100% + 8px);
            left: 0;
            right: 0;
            min-width: unset;
            width: 100%;
            text-align: center;
        }
        #mainHeader.header-premium .navbar-collapse-premium .dropdown-item-premium {
            justify-content: center;
        }
    }

    /* ════════════════════════════════════════════
       MOBILE ≤ 500px
       ════════════════════════════════════════════ */
    @media (max-width: 500px) {
        #mainHeader.header-premium .btn-cart-premium,
        #mainHeader.header-premium .btn-user-premium,
        #mainHeader.header-premium .btn-login-premium {
            font-size: 0.82rem;
            padding: 0.42rem 1rem;
        }

        /* ✅ FIX: en pantallas muy pequeñas, el dropdown ocupa todo el ancho y se centra */
        #mainHeader.header-premium .navbar-collapse-premium .dropdown {
            width: 100%;
        }
        #mainHeader.header-premium .navbar-collapse-premium .dropdown-menu-premium {
            left: 0;
            right: 0;
            bottom: calc(100% + 8px);
            top: auto;
            min-width: unset;
            width: 100%;
            text-align: center;
        }
        #mainHeader.header-premium .navbar-collapse-premium .dropdown-item-premium {
            justify-content: center;
        }
        #mainHeader.header-premium .navbar-collapse-premium .btn-user-premium {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['carrito']['productos'])) {
        $total += count($_SESSION['carrito']['productos']);
    }
    if (isset($_SESSION['carrito']['variantes'])) {
        $total += count($_SESSION['carrito']['variantes']);
    }
    return $total;
}

$num_cart = getCartTotal();
?>

<header id="mainHeader" class="header-premium">
    <nav class="navbar-premium">
        <div class="navbar-inner">

            <!-- LOGO (izquierda) -->
            <a href="<?php echo SITE_URL; ?>/public/index.php" class="navbar-brand-premium">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo-1.png" alt="Logo">
            </a>

            <!-- NAV LINKS (centro) — solo visible en desktop -->
            <ul class="navbar-nav-premium desktop-only">
                <li class="nav-item-premium">
                    <a href="<?php echo SITE_URL; ?>/public/nosotros.php" class="nav-link-premium">Sobre Nosotros</a>
                </li>
                <li class="nav-item-premium">
                    <a href="<?php echo SITE_URL; ?>/public/index.php" class="nav-link-premium">Catálogo</a>
                </li>
            </ul>

            <!-- BOTONES (derecha) — solo visible en desktop -->
            <div class="header-buttons-container desktop-only">
                <a href="<?php echo SITE_URL; ?>/public/checkout.php" class="btn-cart-premium" id="cartButton">
                    <i class="bi bi-cart3"></i>
                    Carrito
                    <span id="num_cart_premium"
                          class="badge-premium <?php echo $num_cart === 0 ? 'empty' : ''; ?>">
                        <?php echo $num_cart; ?>
                    </span>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn-user-premium" type="button" id="userMenuButton"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]); ?>
                        </button>
                        <div class="dropdown-menu-premium" id="userDropdown" role="menu">
                            <a class="dropdown-item-premium" href="<?php echo SITE_URL; ?>/public/mi_cuenta.php">
                                <i class="bi bi-person"></i> Mi Cuenta
                            </a>
                            <a class="dropdown-item-premium" href="<?php echo SITE_URL; ?>/public/compras.php">
                                <i class="bi bi-bag-check"></i> Mis Compras
                            </a>
                            <div class="dropdown-divider-premium"></div>
                            <a class="dropdown-item-premium" href="<?php echo SITE_URL; ?>/include/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/public/login.php" class="btn-login-premium">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                    </a>
                <?php endif; ?>
            </div>

            <!-- HAMBURGER (solo visible en mobile) -->
            <button class="btn-hamburger" id="btnHamburger"
                    aria-label="Menú" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div><!-- /.navbar-inner -->

        <!-- MENÚ COLAPSABLE MOBILE -->
        <div class="navbar-collapse-premium" id="navCollapse">
            <ul class="navbar-nav-premium">
                <li class="nav-item-premium">
                    <a href="<?php echo SITE_URL; ?>/public/nosotros.php" class="nav-link-premium">Sobre Nosotros</a>
                </li>
                <li class="nav-item-premium">
                    <a href="<?php echo SITE_URL; ?>/public/index.php" class="nav-link-premium">Catálogo</a>
                </li>
            </ul>

            <div class="header-buttons-container">
                <a href="<?php echo SITE_URL; ?>/public/checkout.php" class="btn-cart-premium" id="cartButtonMobile">
                    <i class="bi bi-cart3"></i>
                    Carrito
                    <span id="num_cart_premium_mobile"
                          class="badge-premium <?php echo $num_cart === 0 ? 'empty' : ''; ?>">
                        <?php echo $num_cart; ?>
                    </span>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn-user-premium" type="button" id="userMenuButtonMobile"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]); ?>
                        </button>
                        <div class="dropdown-menu-premium" id="userDropdownMobile" role="menu">
                            <a class="dropdown-item-premium" href="<?php echo SITE_URL; ?>/public/mi_cuenta.php">
                                <i class="bi bi-person"></i> Mi Cuenta
                            </a>
                            <a class="dropdown-item-premium" href="<?php echo SITE_URL; ?>/public/compras.php">
                                <i class="bi bi-bag-check"></i> Mis Compras
                            </a>
                            <div class="dropdown-divider-premium"></div>
                            <a class="dropdown-item-premium" href="<?php echo SITE_URL; ?>/include/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/public/login.php" class="btn-login-premium">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                    </a>
                <?php endif; ?>
            </div>
        </div><!-- /.navbar-collapse-premium -->

    </nav>
</header>

<style>
    @media (min-width: 901px) {
        #mainHeader.header-premium .navbar-collapse-premium {
            display: none !important;
        }
        #mainHeader.header-premium .desktop-only {
            display: flex;
        }
        #mainHeader.header-premium .navbar-premium {
            flex-direction: column;
            align-items: stretch;
            height: auto;
            padding: 0;
        }
        #mainHeader.header-premium .navbar-inner {
            height: var(--header-height);
            padding: 0 2rem;
        }
    }
    @media (max-width: 900px) {
        #mainHeader.header-premium .desktop-only {
            display: none !important;
        }
        #mainHeader.header-premium .navbar-premium {
            flex-direction: column;
            height: auto;
            padding: 0;
        }
        #mainHeader.header-premium .navbar-inner {
            height: var(--header-height);
            padding: 0 1.25rem;
        }
    }
</style>

<script>
(function () {
    'use strict';

    const header      = document.getElementById('mainHeader');
    const hamburger   = document.getElementById('btnHamburger');
    const navCollapse = document.getElementById('navCollapse');
    const badge       = document.getElementById('num_cart_premium');
    const badgeMobile = document.getElementById('num_cart_premium_mobile');

    if (!header) return;

    /* ════════════════════════════════════════════
       1. SCROLL
       ════════════════════════════════════════════ */
    let ticking = false;

    function onScroll() {
        const y = window.scrollY;
        header.classList.toggle('header-scrolled', y > 10);
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            requestAnimationFrame(onScroll);
            ticking = true;
        }
    }, { passive: true });

    /* ════════════════════════════════════════════
       2. BADGE
       ════════════════════════════════════════════ */
    function setBadge(total) {
        [badge, badgeMobile].forEach(function(b) {
            if (!b) return;
            const prev = parseInt(b.textContent) || 0;
            b.textContent = total;
            b.classList.toggle('empty', total === 0);
            if (total !== prev) {
                b.classList.remove('pop');
                void b.offsetWidth;
                b.classList.add('pop');
                b.addEventListener('animationend', () => b.classList.remove('pop'), { once: true });
            }
        });
    }

    /* ════════════════════════════════════════════
       3. CONTADOR TIEMPO REAL
       ════════════════════════════════════════════ */
    window.actualizarBadgeCarrito = function(total) {
        setBadge(parseInt(total) || 0);
    };

    (function() {
        var _real = null;

        Object.defineProperty(window, 'addProducto', {
            configurable: true,
            enumerable: true,
            set: function(fn) {
                _real = function() {
                    var result = fn.apply(this, arguments);
                    if (result && typeof result.then === 'function') {
                        result.then(function(data) {
                            if (data && data.numero !== undefined) {
                                setBadge(parseInt(data.numero));
                            } else {
                                fetch('<?php echo SITE_URL; ?>/include/obtener_carrito.php')
                                    .then(function(r) { return r.json(); })
                                    .then(function(d) { setBadge(d.total || 0); })
                                    .catch(function() {});
                            }
                        }).catch(function() {});
                    } else {
                        setTimeout(function() {
                            fetch('<?php echo SITE_URL; ?>/include/obtener_carrito.php')
                                .then(function(r) { return r.json(); })
                                .then(function(d) { setBadge(d.total || 0); })
                                .catch(function() {});
                        }, 400);
                    }
                    return result;
                };
                _real._hooked = true;
            },
            get: function() {
                return _real;
            }
        });
    })();

    document.addEventListener('carrito:actualizado', function(e) {
        if (e.detail && e.detail.total !== undefined) {
            setBadge(parseInt(e.detail.total));
        } else {
            fetch('<?php echo SITE_URL; ?>/include/obtener_carrito.php')
                .then(function(r) { return r.json(); })
                .then(function(d) { setBadge(d.total || 0); })
                .catch(function() {});
        }
    });

    window.addEventListener('storage', function (e) {
        if (e.key === 'carrito_actualizado') {
            fetch('<?php echo SITE_URL; ?>/include/obtener_carrito.php')
                .then(function(r) { return r.json(); })
                .then(function(d) { setBadge(d.total || 0); })
                .catch(function() {});
        }
    });

    setInterval(function() {
        fetch('<?php echo SITE_URL; ?>/include/obtener_carrito.php')
            .then(function(r) { return r.json(); })
            .then(function(d) { setBadge(d.total || 0); })
            .catch(function() {});
    }, 30000);

    /* ════════════════════════════════════════════
       4. HAMBURGER MENU
       ════════════════════════════════════════════ */
    if (hamburger && navCollapse) {
        hamburger.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = navCollapse.classList.toggle('open');
            hamburger.classList.toggle('open', open);
            hamburger.setAttribute('aria-expanded', open);

            // ✅ FIX: al cerrar el menú, cerrar también cualquier dropdown abierto
            if (!open) {
                navCollapse.querySelectorAll('.dropdown-menu-premium.show').forEach(function(m) {
                    m.classList.remove('show');
                });
                navCollapse.querySelectorAll('[aria-expanded="true"]').forEach(function(b) {
                    b.setAttribute('aria-expanded', 'false');
                });
            }
        });

        navCollapse.querySelectorAll('.nav-link-premium').forEach(function (link) {
            link.addEventListener('click', function () {
                navCollapse.classList.remove('open');
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', function (e) {
            if (navCollapse.classList.contains('open') && !header.contains(e.target)) {
                navCollapse.classList.remove('open');
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ════════════════════════════════════════════
       5. DROPDOWN USUARIO (desktop + mobile)
          ✅ FIX: al abrir el dropdown mobile, el collapse
          ya tiene overflow:visible así que no se corta.
          Solo cerramos el otro dropdown si estuviera abierto.
       ════════════════════════════════════════════ */
    ['', 'Mobile'].forEach(function(suffix) {
        const btn  = document.getElementById('userMenuButton' + suffix);
        const menu = document.getElementById('userDropdown' + suffix);
        if (!btn || !menu) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();

            // Cerrar el dropdown del otro contexto (desktop/mobile) si está abierto
            const otherSuffix = suffix === '' ? 'Mobile' : '';
            const otherMenu = document.getElementById('userDropdown' + otherSuffix);
            if (otherMenu && otherMenu.classList.contains('show')) {
                otherMenu.classList.remove('show');
                const otherBtn = document.getElementById('userMenuButton' + otherSuffix);
                if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
            }

            const open = menu.classList.toggle('show');
            btn.setAttribute('aria-expanded', open);
        });

        document.addEventListener('click', function (e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    });

})();
</script>