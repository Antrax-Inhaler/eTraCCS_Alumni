<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OfflineAlert from '@/Components/OfflineAlert.vue';
defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>
<style scoped>
:root {
    --primary: #ff8c00;
    --primary-light: rgba(255, 140, 0, 0.1);
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    --bg-dark: #1a1a1a;
    --bg-darker: #121212;
    --card-bg: rgba(40, 40, 40, 0.7);
    --card-border: rgba(255, 255, 255, 0.1);
    --success: #4CAF50;
    --warning: #FFC107;
    --danger: #F44336;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg-darker);
    color: var(--text-primary);
    background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');
    background-size: cover;
    background-attachment: fixed;
    min-height: 100vh;
    overflow-x: hidden;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--bg-dark);
}

::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 4px;
}

/* Navigation */
header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card-bg);
    border-bottom: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
}

header.scrolled {
    padding: 15px 40px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: var(--primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

nav ul {
    display: flex;
    gap: 30px;
    list-style: none;
}

nav a {
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    position: relative;
    transition: all 0.3s ease;
}

nav a:hover {
    color: var(--primary);
}

nav a::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary);
    transition: width 0.3s ease;
}

nav a:hover::after {
    width: 100%;
}

.cta-button {
    background: var(--primary);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.cta-button:hover {
    background: #e67e00;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 140, 0, 0.3);
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: var(--text-primary);
    font-size: 24px;
    cursor: pointer;
}

/* Hero Section */
.hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 120px 40px 80px;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,140,0,0.1) 0%, rgba(0,0,0,0) 70%);
    animation: rotate 20s linear infinite;
    z-index: -1;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.hero-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 50px;
}

.hero-text {
    flex: 1;
    animation: fadeInUp 1s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
    color: #cccccc;
}

.hero p {
    font-size: 18px;
    color: var(--text-secondary);
    margin-bottom: 30px;
    max-width: 600px;
}

.hero-buttons {
    display: flex;
    gap: 20px;
}

.secondary-button {
    background: transparent;
    color: var(--text-primary);
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    border: 2px solid var(--primary);
    transition: all 0.3s ease;
}

.secondary-button:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
}

.hero-image {
    flex: 1;
    position: relative;
    animation: fadeIn 1.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.dashboard-mockup {
    width: 100%;
    max-width: 600px;
    border-radius: 20px;
    border: 1px solid var(--card-border);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    background: var(--card-bg);
    overflow: hidden;
    transform: perspective(1000px) rotateY(10deg);
    transition: all 0.5s ease;
}

.dashboard-mockup:hover {
    transform: perspective(1000px) rotateY(0deg);
}

/* Features Section */
.features {
    padding: 100px 40px;
    background: rgba(0, 0, 0, 0.5);
    position: relative;
}

.section-title {
    text-align: center;
    font-size: 36px;
    margin-bottom: 60px;
    position: relative;
    display: inline-block;
    left: 50%;
    transform: translateX(-50%);
    color: var(--text-secondary);
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--primary);
    border-radius: 3px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 15px;
    padding: 30px;
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
    transform: translateY(0);
    opacity: 0;
    animation: fadeInUp 0.5s ease forwards;
}

.feature-card:nth-child(1) { animation-delay: 0.2s; }
.feature-card:nth-child(2) { animation-delay: 0.4s; }
.feature-card:nth-child(3) { animation-delay: 0.6s; }
.feature-card:nth-child(4) { animation-delay: 0.8s; }

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    border-color: var(--primary);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-light);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: var(--primary);
    font-size: 24px;
}

.feature-card h3 {
    font-size: 22px;
    margin-bottom: 15px;
}

.feature-card p {
    color: var(--text-secondary);
    line-height: 1.6;
}

/* Testimonials */
.testimonials {
    padding: 100px 40px;
    position: relative;
}

.testimonials::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent);
    z-index: -1;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.testimonial-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 15px;
    padding: 30px;
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.testimonial-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.testimonial-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
}

.testimonial-author {
    font-weight: 600;
}

.testimonial-role {
    color: var(--text-secondary);
    font-size: 14px;
}

.testimonial-text {
    font-style: italic;
    line-height: 1.6;
    position: relative;
}

.testimonial-text::before,
.testimonial-text::after {
    content: '"';
    font-size: 24px;
    color: var(--primary);
    opacity: 0.5;
}

/* CTA Section */
.cta-section {
    padding: 100px 40px;
    text-align: center;
    background: linear-gradient(to right, rgba(255,140,0,0.1), rgba(255,140,0,0.05));
    position: relative;
    overflow: hidden;
}

.cta-container {
    max-width: 800px;
    margin: 0 auto;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 60px 40px;
    backdrop-filter: blur(12px);
    position: relative;
    z-index: 1;
}

.cta-container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,140,0,0.1) 0%, rgba(0,0,0,0) 70%);
    animation: rotate 20s linear infinite reverse;
    z-index: -1;
}

.cta-section h2 {
    font-size: 36px;
    margin-bottom: 20px;
}

.cta-section p {
    color: var(--text-secondary);
    margin-bottom: 30px;
    font-size: 18px;
}

/* Footer */
footer {
    background: var(--bg-dark);
    padding: 60px 40px 30px;
    border-top: 1px solid var(--card-border);
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto 40px;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 20px;
}

.footer-logo-icon {
    width: 30px;
    height: 30px;
    background: var(--primary);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.footer-about p {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 20px;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-3px);
}

.footer-links h3 {
    font-size: 18px;
    margin-bottom: 20px;
    color: var(--primary);
}

.footer-links ul {
    list-style: none;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: var(--primary);
    padding-left: 5px;
}

.footer-contact p {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-secondary);
    margin-bottom: 15px;
}

.footer-contact i {
    color: var(--primary);
}

.copyright {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid var(--card-border);
    color: var(--text-secondary);
    font-size: 14px;
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.floating {
    animation: float 6s ease-in-out infinite;
}

/* Mobile Responsiveness */
@media (max-width: 992px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
    }

    .hero-text {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .hero-buttons {
        justify-content: center;
    }

    .dashboard-mockup {
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    header {
        padding: 15px 20px;
    }

    nav ul {
        display: none;
    }

    .mobile-menu-btn {
        display: block;
    }

    .hero h1 {
        font-size: 36px;
    }

    .hero p {
        font-size: 16px;
    }

    .section-title {
        font-size: 28px;
    }

    .features, .testimonials, .cta-section {
        padding: 60px 20px;
    }
}

@media (max-width: 480px) {
    .hero-buttons {
        flex-direction: column;
        gap: 10px;
    }

    .hero h1 {
        font-size: 28px;
    }

    .feature-card, .testimonial-card {
        padding: 20px;
    }

    .cta-container {
        padding: 40px 20px;
    }

    .cta-section h2 {
        font-size: 28px;
    }
}
</style>
<template>
      <OfflineAlert />
  <router-view />

    <header id="header">
        <a href="#" class="logo">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span>eTraCCS</span>
        </a>
        
        <nav>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
        
        <nav v-if="canLogin" class="hero-buttons">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class=""
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class=""
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class=""
                    >
                        Register
                    </Link>
                </template>
            </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Connecting Alumni, Building Futures</h1>
                <p>The eTraCCS Alumni Portal helps graduates of the College of Computer Studies stay connected, track career progress, and access exclusive opportunities.</p>
                <div class="hero-buttons">
                    <a href="/register" class="cta-button">Join Now</a>
                    <a href="#features" class="secondary-button">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="/storage/designer/eTraCCS-logo.png" alt="eTraCCS Dashboard" class="dashboard-mockup floating">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">Key Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h3>Alumni Network</h3>
                <p>Connect with fellow graduates, share experiences, and expand your professional network across different batches and industries.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Career Tracking</h3>
                <p>Track employment history, additional education, and professional development in one centralized platform.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Employability Analytics</h3>
                <p>Access detailed reports on graduate employability and see how your career progress compares to peers.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Events & Opportunities</h3>
                <p>Stay updated on alumni events, job postings, and exclusive opportunities tailored for graduates.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <h2 class="section-title">Success Stories</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Jane Cooper" class="testimonial-avatar">
                    <div>
                        <div class="testimonial-author">Jane Cooper</div>
                        <div class="testimonial-role">Senior Developer, Google</div>
                    </div>
                </div>
                <div class="testimonial-text">
                    The eTraCCS portal helped me reconnect with classmates and discover amazing career opportunities. I landed my dream job through an alumni referral!
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Devon Lane" class="testimonial-avatar">
                    <div>
                        <div class="testimonial-author">Devon Lane</div>
                        <div class="testimonial-role">CEO, TechStart Inc.</div>
                    </div>
                </div>
                <div class="testimonial-text">
                    As an entrepreneur, the alumni network has been invaluable for finding talent and business partnerships. The GIS mapping feature helped me identify potential collaborators in my area.
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Arlene McCoy" class="testimonial-avatar">
                    <div>
                        <div class="testimonial-author">Arlene McCoy</div>
                        <div class="testimonial-role">Data Scientist, Tesla</div>
                    </div>
                </div>
                <div class="testimonial-text">
                    The competency tracking feature helped me identify skills gaps and find relevant training programs. I've advanced my career significantly since joining the portal.
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="contact">
        <div class="cta-container">
            <h2>Ready to Join Our Growing Network?</h2>
            <p>Over 5,000 alumni are already connected and benefiting from the eTraCCS portal.</p>
            <div class="hero-buttons">
                <a href="/register" class="cta-button">Register Now</a>
                <a href="/login" class="secondary-button">Alumni Login</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-about">
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span>eTraCCS</span>
                </div>
                <p>The official alumni portal of the College of Computer Studies, connecting graduates and fostering professional growth since 2025.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Success Stories</a></li>
                    <li><a href="/events">Upcoming Events</a></li>
                    <li><a href="/jobs">Job Board</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h3>Resources</h3>
                <ul>
                    <li><a href="/help">Help Center</a></li>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms of Service</a></li>
                    <li><a href="/faq">FAQs</a></li>
                </ul>
            </div>
            
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> College of Computer Studies, University Campus</p>
                <p><i class="fas fa-phone"></i> +1 (234) 567-8900</p>
                <p><i class="fas fa-envelope"></i> alumni@etraccs.edu</p>
            </div>
        </div>
        
        <div class="copyright">
            &copy; 2023 eTraCCS. All rights reserved.
        </div>
    </footer>

        <!-- Footer -->
        <footer class="w-full mx-auto px-6 py-10 text-center text-sm text-gray-600 dark:text-gray-400">
        </footer>
</template>
