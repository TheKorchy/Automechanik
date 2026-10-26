<style>
    .footer-custom {
        background-color: #020617; /* Tmavé pozadie ladiace s webom */
        padding: 80px 0 40px;
        color: white;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .footer-custom h2 {
        font-family: 'Bricolage Grotesque', sans-serif;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .footer-accent {
        color: #3b82f6;
    }

    .social-links {
        margin: 25px 0;
    }

    .social-links a {
        color: white;
        font-size: 1.4rem;
        margin: 0 15px;
        transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: inline-block;
        text-decoration: none;
    }

    .social-links a:hover {
        color: #3b82f6;
        transform: translateY(-5px);
    }

    .btn-term-footer {
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 50px;
        padding: 10px 25px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-term-footer:hover {
        background: white;
        color: #020617;
        border-color: white;
    }

    .copy-text {
        opacity: 0.4;
        font-size: 0.85rem;
        margin-top: 40px;
    }

    .footer-line {
        height: 1px;
        width: 100%;
        max-width: 400px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        margin: 30px auto;
    }
</style>

<footer class="footer-custom text-center">
    <div class="container">
        <h2>Autoservis <span class="footer-accent">Torysa</span></h2>
        <p class="opacity-75">Rýchlo a spoľahlivo • Inžinierstvo stretáva umenie</p>

        <div class="social-links">
            <a href="https://www.facebook.com/profile.php?id=61564723647680" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.tiktok.com/@korchnak.j" target="_blank"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.instagram.com/peter.angelovic183/" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>

        <div>
            <a href="kontakt.php" class="btn-term-footer">
                <i class="fa-regular fa-calendar-check" style="color: #3b82f6;"></i> Rezervovať termín
            </a>
        </div>

        <div class="footer-line"></div>

        <p class="copy-text">
            © <?= date("2026"); ?> Autoservis Torysa. Všetky práva vyhradené.
        </p>
    </div>
</footer>