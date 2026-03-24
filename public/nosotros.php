<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';

// Iniciar sesión si no está iniciada
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre Nosotros | Uniformes Mayoristas</title>
    <link rel="stylesheet" href="tt.css">
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#000000">
</head>

<body>
    <?php include '../include/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container text-center">
            <h1 class="fadeInUp">Sobre <span>Nosotros</span></h1>
            <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
            <p class="fadeInUp" style="animation-delay: 0.4s;">
                Somos una empresa líder en la venta de uniformes mayoristas, comprometidos con la calidad y la satisfacción de nuestros clientes
            </p>
        </div>
    </section>

    <!-- Presentación -->
    <section class="section-padding">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="../assets/img/uniformes-empresa.jpg" alt="Nuestra empresa" class="img-fluid rounded-4 shadow-lg">
                </div>
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4" style="color: var(--primary-black);">Más de 10 años vistiendo a <span style="color: var(--primary-gold);">profesionales</span></h2>
                    <p class="lead mb-4" style="color: #555;">
                        En <strong>Uniformes Mayoristas</strong> nos especializamos en la fabricación y distribución de uniformes de alta calidad para empresas, instituciones y profesionales.
                    </p>
                    <p class="mb-4" style="color: #666;">
                        Nuestra misión es proporcionar uniformes que combinen estilo, comodidad y durabilidad, adaptándonos a las necesidades específicas de cada sector. Trabajamos con los mejores materiales y procesos de fabricación para garantizar la satisfacción de nuestros clientes.
                    </p>
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 0.5rem;"></i>
                                <span>Calidad garantizada</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-truck" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 0.5rem;"></i>
                                <span>Envíos a todo el país</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 0.5rem;"></i>
                                <span>Equipo profesional</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-award" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 0.5rem;"></i>
                                <span>Certificaciones ISO</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Valores -->
    <section class="section-padding" style="background: var(--gray-light);">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Valores</h2>
                <p>Los principios que guían nuestro trabajo diario</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <h3>Calidad</h3>
                        <p>Utilizamos los mejores materiales y procesos de fabricación</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-hand-thumbs-up"></i>
                        </div>
                        <h3>Compromiso</h3>
                        <p>Cumplimos con los plazos y expectativas de nuestros clientes</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h3>Innovación</h3>
                        <p>Constantemente mejoramos nuestros diseños y procesos</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h3>Pasión</h3>
                        <p>Amamos lo que hacemos y se refleja en cada uniforme</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Estadísticas -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Años de experiencia</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Empresas clientes</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">50k+</div>
                        <div class="stat-label">Uniformes vendidos</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Clientes satisfechos</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Historia -->
    <section class="section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Nuestra Historia</h2>
                <p>Cómo llegamos a ser líderes en el sector</p>
            </div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>Fundación</h4>
                        <p>Iniciamos como un pequeño taller familiar con visión de crecimiento.</p>
                    </div>
                    <div class="timeline-year">2014</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>Expansión</h4>
                        <p>Abrimos nuestra primera fábrica y comenzamos a distribuir a nivel nacional.</p>
                    </div>
                    <div class="timeline-year">2017</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>Certificación ISO</h4>
                        <p>Obtenemos la certificación ISO 9001 de calidad.</p>
                    </div>
                    <div class="timeline-year">2019</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>Líderes del mercado</h4>
                        <p>Nos convertimos en proveedores oficiales de importantes empresas.</p>
                    </div>
                    <div class="timeline-year">2024</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Equipo -->
    <section class="section-padding" style="background: var(--gray-light);">
        <div class="container">
            <div class="section-title">
                <h2>Nuestro Equipo</h2>
                <p>Profesionales apasionados por lo que hacen</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="../assets/img/team1.jpg" alt="CEO">
                        </div>
                        <div class="team-info">
                            <h4>Carlos Rodríguez</h4>
                            <p>CEO & Fundador</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="../assets/img/team2.jpg" alt="Diseñadora">
                        </div>
                        <div class="team-info">
                            <h4>María González</h4>
                            <p>Directora de Diseño</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-instagram"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="../assets/img/team3.jpg" alt="Producción">
                        </div>
                        <div class="team-info">
                            <h4>Juan Pérez</h4>
                            <p>Gerente de Producción</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="../assets/img/team4.jpg" alt="Ventas">
                        </div>
                        <div class="team-info">
                            <h4>Ana Martínez</h4>
                            <p>Directora de Ventas</p>
                            <div class="team-social">
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-whatsapp"></i></a>
                                <a href="#"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonios -->
    <section class="section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Lo que dicen nuestros clientes</h2>
                <p>Empresas que confían en nosotros</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">"</div>
                        <p class="testimonial-text">Excelente calidad en los uniformes. Han superado nuestras expectativas y el servicio es excepcional.</p>
                        <div class="testimonial-author">
                            <img src="../assets/img/client1.jpg" alt="Cliente">
                            <div>
                                <h5>Empresa Textil SA</h5>
                                <p>Industria manufacturera</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">"</div>
                        <p class="testimonial-text">Los mejores uniformes que hemos tenido. Duraderos, cómodos y con un diseño moderno.</p>
                        <div class="testimonial-author">
                            <img src="../assets/img/client2.jpg" alt="Cliente">
                            <div>
                                <h5>Hotel Paraíso</h5>
                                <p>Sector hotelero</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">"</div>
                        <p class="testimonial-text">Profesionalismo y calidad. Siempre cumplen con los plazos y el producto es de primera.</p>
                        <div class="testimonial-author">
                            <img src="../assets/img/client3.jpg" alt="Cliente">
                            <div>
                                <h5>Clínica Salud</h5>
                                <p>Sector salud</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 style="color: white;">¿Listo para <span>uniformar</span> tu empresa?</h2>
            <p>Contáctanos hoy mismo y descubre por qué somos la mejor opción para uniformes mayoristas</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="contacto.php" style="background-color: #ffd700; border-color: #ffd700; color: #000;" class="btn btn-gold">Solicitar cotización</a>
                <a href="catalogo.php" style="background-color: #ffd700; border-color: #ffd700; color: #000;" class="btn btn-gold">Ver catálogo</a>
            </div>
        </div>
    </section>

    <?php include '../include/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animaciones al hacer scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.value-card, .team-card, .testimonial-card, .timeline-item').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
                observer.observe(el);
            });
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>