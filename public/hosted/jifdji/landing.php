<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Inkwell Blog — Discover insightful articles on Technology, Lifestyle, Travel, and more. Read, explore, and stay inspired.">
  <title>Inkwell Blog — Discover Stories That Matter</title>

  <!-- Bootstrap 5.3.8 CSS (already in project) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Landing custom styles -->
  <link rel="stylesheet" href="landing.css">
</head>

<body>

<!-- ================================================
     1. NAVBAR
     ================================================ -->
<nav class="navbar navbar-expand-lg landing-nav" id="mainNavbar" aria-label="Main navigation">
  <div class="container">

    <!-- Brand / Logo -->
    <a class="navbar-brand" href="#" id="navBrand">
      <span>Ink</span>well
    </a>

    <!-- Mobile Toggler -->
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMenu"
            aria-controls="navMenu" aria-expanded="false"
            aria-label="Toggle navigation"
            id="navToggler">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav Links -->
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-1">
        <li class="nav-item">
          <a class="nav-link active" href="#hero" id="navHome">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#categories" id="navCategories">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#blogs" id="navBlogs">Blogs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#contact" id="navContact">Contact</a>
        </li>
      </ul>

      <!-- Auth Buttons -->
      <div class="nav-auth-btns d-flex align-items-center gap-2 ms-3" id="navAuthBtns">
        <a href="admin/login.php" class="btn-nav-login" id="navLoginBtn">Login</a>
        <a href="admin/register.php" class="btn-nav-register" id="navRegisterBtn">Create Account</a>
      </div>
    </div>

  </div>
</nav><!-- /navbar -->


<!-- ================================================
     2. HERO / HEADER SECTION
     ================================================ -->
<header class="hero-section" id="hero" aria-label="Hero section">
  <div class="container position-relative" style="z-index:1">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8">

        <span class="hero-badge" id="heroBadge">&#128221; Your Daily Dose of Inspiration</span>

        <h1 class="hero-title" id="heroTitle">
          Explore Ideas That<br>
          <span class="highlight">Shape the World</span>
        </h1>

        <p class="hero-desc" id="heroDesc">
          Dive into handpicked articles on technology, lifestyle, travel, and beyond.
          Written by passionate voices — crafted for curious minds like yours.
        </p>

        <a href="#blogs" class="hero-btn" id="heroCTA">
          Start Reading &nbsp;&#8594;
        </a>

      </div>
    </div>
  </div>

  <!-- Scroll hint -->
  <div class="hero-scroll-hint" aria-hidden="true">Scroll</div>
</header><!-- /hero -->


<!-- ================================================
     3. CATEGORIES SECTION
     ================================================ -->
<section class="categories-section" id="categories" aria-label="Blog categories">
  <div class="container">

    <!-- Section header -->
    <div class="text-center">
      <h2 class="section-title" id="categoriesTitle">Browse Categories</h2>
      <p class="section-subtitle" id="categoriesSubtitle">Find stories in the topics you love most</p>
      <div class="section-divider" aria-hidden="true"></div>
    </div>

    <!-- 4 Category Cards -->
    <div class="row g-4 justify-content-center" id="categoriesGrid">

      <!-- Category 1: Technology -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="category-card cat-1" id="catTech" role="button" tabindex="0"
             onclick="window.location.href='#blogs'" aria-label="Technology category">
          <span class="cat-icon" aria-hidden="true">&#128187;</span>
          <h5>Technology</h5>
          <p>AI, gadgets, software trends &amp; future-forward ideas for the digital age.</p>
          <span class="cat-count">24 articles</span>
        </div>
      </div>

      <!-- Category 2: Lifestyle -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="category-card cat-2" id="catLifestyle" role="button" tabindex="0"
             onclick="window.location.href='#blogs'" aria-label="Lifestyle category">
          <span class="cat-icon" aria-hidden="true">&#127774;</span>
          <h5>Lifestyle</h5>
          <p>Wellness, productivity habits, and the art of living a balanced, fulfilling life.</p>
          <span class="cat-count">18 articles</span>
        </div>
      </div>

      <!-- Category 3: Travel -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="category-card cat-3" id="catTravel" role="button" tabindex="0"
             onclick="window.location.href='#blogs'" aria-label="Travel category">
          <span class="cat-icon" aria-hidden="true">&#9992;&#65039;</span>
          <h5>Travel</h5>
          <p>Hidden gems, travel guides &amp; cultural experiences from every corner of the globe.</p>
          <span class="cat-count">31 articles</span>
        </div>
      </div>

      <!-- Category 4: Business -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="category-card cat-4" id="catBusiness" role="button" tabindex="0"
             onclick="window.location.href='#blogs'" aria-label="Business category">
          <span class="cat-icon" aria-hidden="true">&#128200;</span>
          <h5>Business</h5>
          <p>Entrepreneurship, market insights, startup stories &amp; growth strategies.</p>
          <span class="cat-count">15 articles</span>
        </div>
      </div>

    </div><!-- /row -->
  </div>
</section><!-- /categories -->


<!-- ================================================
     4. BLOGS SECTION
     ================================================ -->
<section class="blogs-section" id="blogs" aria-label="Blog posts">
  <div class="container">

    <!-- Section header -->
    <div class="text-center">
      <h2 class="section-title" id="blogsTitle">Latest Blog Posts</h2>
      <p class="section-subtitle" id="blogsSubtitle">Handpicked reads to keep you informed &amp; inspired</p>
      <div class="section-divider" aria-hidden="true"></div>
    </div>

    <!-- 8 Blog Cards (3 cols on lg, 2 on md, 1 on sm) -->
    <div class="row g-4" id="blogsGrid">

      <!-- Blog 1 — WITH IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog1" aria-label="Blog post: The Future of AI">
          <div class="blog-img-wrap">
            <img src="https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=600&q=80"
                 alt="Abstract AI neural network visualisation" loading="lazy">
          </div>
          <div class="card-body">
            <span class="blog-tag">Technology</span>
            <h3 class="card-title">The Future of Artificial Intelligence in Everyday Life</h3>
            <p class="card-text">From smart assistants to self-driving cars, AI is weaving itself into the fabric of our daily routines faster than we ever imagined.</p>
            <a href="#" class="blog-read-more" id="blog1Btn" aria-label="Read more about AI">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 2 — NO IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog2" aria-label="Blog post: Morning Routines">
          <div class="blog-no-img" role="img" aria-label="No image available">
            <span aria-hidden="true">&#128444;&#65039;</span>
            No Image
          </div>
          <div class="card-body">
            <span class="blog-tag">Lifestyle</span>
            <h3 class="card-title">5 Morning Routines That Will Transform Your Day</h3>
            <p class="card-text">Small intentional habits in the first hour of your day can dramatically shift your focus, energy, and overall sense of well-being.</p>
            <a href="#" class="blog-read-more" id="blog2Btn" aria-label="Read more about morning routines">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 3 — WITH IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog3" aria-label="Blog post: Hidden gems of Southeast Asia">
          <div class="blog-img-wrap">
            <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=600&q=80"
                 alt="Tropical temple surrounded by jungle" loading="lazy">
          </div>
          <div class="card-body">
            <span class="blog-tag">Travel</span>
            <h3 class="card-title">Hidden Gems of Southeast Asia You Must Visit</h3>
            <p class="card-text">Beyond the tourist trails lie ancient temples, pristine beaches, and vibrant markets waiting for the truly curious traveller.</p>
            <a href="#" class="blog-read-more" id="blog3Btn" aria-label="Read more about Southeast Asia travel">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 4 — WITH IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog4" aria-label="Blog post: Startup fundraising">
          <div class="blog-img-wrap">
            <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=600&q=80"
                 alt="Entrepreneurs discussing business strategy around a table" loading="lazy">
          </div>
          <div class="card-body">
            <span class="blog-tag">Business</span>
            <h3 class="card-title">How to Raise Your First Round of Startup Funding</h3>
            <p class="card-text">Navigating venture capital can feel overwhelming. Here is a practical roadmap to pitch investors and secure your seed round.</p>
            <a href="#" class="blog-read-more" id="blog4Btn" aria-label="Read more about startup funding">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 5 — NO IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog5" aria-label="Blog post: Deep focus techniques">
          <div class="blog-no-img" role="img" aria-label="No image available">
            <span aria-hidden="true">&#128444;&#65039;</span>
            No Image
          </div>
          <div class="card-body">
            <span class="blog-tag">Lifestyle</span>
            <h3 class="card-title">Deep Focus: How to Get Into the Zone and Stay There</h3>
            <p class="card-text">Distraction is the enemy of great work. Learn evidence-backed techniques to reclaim your attention and produce your best output.</p>
            <a href="#" class="blog-read-more" id="blog5Btn" aria-label="Read more about deep focus">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 6 — WITH IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog6" aria-label="Blog post: Web development trends 2025">
          <div class="blog-img-wrap">
            <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=600&q=80"
                 alt="Code editor on a laptop screen" loading="lazy">
          </div>
          <div class="card-body">
            <span class="blog-tag">Technology</span>
            <h3 class="card-title">Top Web Development Trends to Watch in 2025</h3>
            <p class="card-text">From edge computing to AI-generated UI, the web development landscape is evolving at lightning speed. Stay ahead of the curve.</p>
            <a href="#" class="blog-read-more" id="blog6Btn" aria-label="Read more about web dev trends">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 7 — NO IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog7" aria-label="Blog post: Solo travel guide">
          <div class="blog-no-img" role="img" aria-label="No image available">
            <span aria-hidden="true">&#128444;&#65039;</span>
            No Image
          </div>
          <div class="card-body">
            <span class="blog-tag">Travel</span>
            <h3 class="card-title">The Ultimate Beginner's Guide to Solo Travel</h3>
            <p class="card-text">Travelling alone for the first time? Discover how to stay safe, meet amazing people, and turn solitude into the adventure of a lifetime.</p>
            <a href="#" class="blog-read-more" id="blog7Btn" aria-label="Read more about solo travel">Read More &#8594;</a>
          </div>
        </article>
      </div>

      <!-- Blog 8 — WITH IMAGE -->
      <div class="col-12 col-md-6 col-lg-4">
        <article class="blog-card card" id="blog8" aria-label="Blog post: Personal finance basics">
          <div class="blog-img-wrap">
            <img src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?w=600&q=80"
                 alt="Coins and a calculator representing personal finance" loading="lazy">
          </div>
          <div class="card-body">
            <span class="blog-tag">Business</span>
            <h3 class="card-title">Personal Finance 101: Build Wealth on Any Salary</h3>
            <p class="card-text">You don't need to earn a fortune to build one. Master budgeting, saving, and investing with practical steps anyone can start today.</p>
            <a href="#" class="blog-read-more" id="blog8Btn" aria-label="Read more about personal finance">Read More &#8594;</a>
          </div>
        </article>
      </div>

    </div><!-- /blogsGrid -->

    <!-- Load More Button -->
    <div class="text-center mt-5" id="loadMoreWrap">
      <button class="load-more-btn" id="loadMoreBtn" type="button"
              aria-label="Load more blog posts">
        Load More Posts
      </button>
    </div>

  </div>
</section><!-- /blogs -->


<!-- ================================================
     5. FOOTER
     ================================================ -->
<footer class="landing-footer" id="contact" aria-label="Site footer">
  <div class="container">
    <div class="row g-4">

      <!-- Brand col -->
      <div class="col-12 col-md-4 col-lg-4">
        <div class="footer-brand" id="footerBrand">
          <span>Ink</span>well
        </div>
        <p class="footer-tagline">Stories that educate, inspire, and ignite curiosity.</p>
        <!-- Social Icons -->
        <div class="social-icons mt-3" id="socialIcons">
          <a href="#" class="social-icon" id="socialTwitter" aria-label="Twitter">&#120143;</a>
          <a href="#" class="social-icon" id="socialFacebook" aria-label="Facebook">&#102;</a>
          <a href="#" class="social-icon" id="socialInstagram" aria-label="Instagram">&#128247;</a>
          <a href="#" class="social-icon" id="socialLinkedIn" aria-label="LinkedIn">&#105;&#110;</a>
          <a href="#" class="social-icon" id="socialYoutube" aria-label="YouTube">&#9654;</a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-6 col-md-2 col-lg-2">
        <h6 class="footer-heading">Quick Links</h6>
        <ul class="footer-links" id="footerLinks1">
          <li><a href="#hero" id="fHome">Home</a></li>
          <li><a href="#categories" id="fCategories">Categories</a></li>
          <li><a href="#blogs" id="fBlogs">Blogs</a></li>
          <li><a href="#contact" id="fContact">Contact</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div class="col-6 col-md-2 col-lg-2">
        <h6 class="footer-heading">Categories</h6>
        <ul class="footer-links" id="footerLinks2">
          <li><a href="#blogs" id="fTech">Technology</a></li>
          <li><a href="#blogs" id="fLifestyle">Lifestyle</a></li>
          <li><a href="#blogs" id="fTravel">Travel</a></li>
          <li><a href="#blogs" id="fBusiness">Business</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="col-12 col-md-4 col-lg-4">
        <h6 class="footer-heading">Stay in the Loop</h6>
        <p style="font-size:.85rem;color:rgba(255,255,255,.45);margin-bottom:.9rem;">
          Subscribe for weekly hand-picked stories straight to your inbox.
        </p>
        <form class="d-flex gap-2" id="newsletterForm" onsubmit="handleSubscribe(event)" novalidate>
          <input type="email" class="form-control form-control-sm"
                 id="newsletterEmail"
                 placeholder="your@email.com"
                 aria-label="Email address"
                 style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:8px;">
          <button type="submit" id="subscribeBtn"
                  class="btn btn-sm text-nowrap"
                  style="background:var(--primary);color:#fff;border-radius:8px;font-weight:600;padding:.42rem 1rem;">
            Subscribe
          </button>
        </form>
        <p id="subscribeMsg" style="font-size:.78rem;margin-top:.5rem;color:var(--accent);display:none;">
          &#10003; Thanks for subscribing!
        </p>
      </div>

    </div><!-- /row -->

    <!-- Footer bottom bar -->
    <div class="footer-bottom d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2" id="footerBottom">
      <span id="copyright">&copy; <span id="currentYear"></span> Inkwell Blog. All rights reserved.</span>
      <span>Made with &#10084;&#65039; and curiosity</span>
    </div>

  </div>
</footer><!-- /footer -->


<!-- Bootstrap 5.3.8 JS Bundle (already in project) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
  // ── Dynamic copyright year
  document.getElementById('currentYear').textContent = new Date().getFullYear();

  // ── Newsletter subscribe handler
  function handleSubscribe(e) {
    e.preventDefault();
    const email = document.getElementById('newsletterEmail').value.trim();
    const msg   = document.getElementById('subscribeMsg');
    if (email) {
      msg.style.display = 'block';
      document.getElementById('newsletterEmail').value = '';
    }
  }

  // ── Load More button (simple demo: shows alert)
  document.getElementById('loadMoreBtn').addEventListener('click', function () {
    this.textContent = 'Loading…';
    this.disabled = true;
    setTimeout(() => {
      this.textContent = 'No More Posts';
    }, 1200);
  });

  // ── Active nav link on scroll
  const sections = document.querySelectorAll('section[id], header[id]');
  const navLinks = document.querySelectorAll('.landing-nav .nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(sec => {
      if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
    });
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === '#' + current) link.classList.add('active');
    });
  }, { passive: true });

  // ── Smooth scroll for all anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
</script>

</body>
</html>
