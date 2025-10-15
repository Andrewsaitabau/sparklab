<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Our Services | SparkLab</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f9f9f9;
    }
    .hero {
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                  url('https://images.unsplash.com/photo-1531297484001-80022131f5a1?auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      color: white;
      text-align: center;
      padding: 100px 20px;
    }
    .service-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .service-card:hover {
      transform: translateY(-5px);
      box-shadow: 0px 8px 20px blue(0,0,0,0.15);
    }
    footer {
      background-color: #111;
      color: #ccc;
      padding: 40px 0;
    }
    footer a {
      color: #ccc;
      text-decoration: none;
    }
    footer a:hover {
      color: #fff;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Hero Section -->
<div class="hero">
  <h1 class="display-4 fw-bold">Our Services</h1>
  <p class="lead">Empowering your business with cutting-edge technology solutions</p>
</div>

<!-- Services Section -->
<div class="container py-5">
  <div class="row g-4">
    
    <div class="col-md-4">
      <div class="card service-card h-100 text-center p-4">
        <i class="bi bi-globe fs-1 text-primary"></i>
        <h4 class="mt-3">Website Development</h4>
        <p>Responsive, SEO-optimized, and user-friendly websites tailored for your brand and business goals.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card service-card h-100 text-center p-4">
        <i class="bi bi-phone fs-1 text-success"></i>
        <h4 class="mt-3">Mobile App Development</h4>
        <p>Custom Android and iOS applications designed for performance, security, and seamless user experience.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card service-card h-100 text-center p-4">
        <i class="bi bi-laptop fs-1 text-danger"></i>
        <h4 class="mt-3">Custom Systems & Software</h4>
        <p>End-to-end development of tailor-made software systems to streamline operations and boost productivity.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card service-card h-100 text-center p-4">
        <i class="bi bi-cloud fs-1 text-info"></i>
        <h4 class="mt-3">Cloud & Hosting Solutions</h4>
        <p>Reliable cloud hosting, data storage, and deployment services ensuring scalability and high availability.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card service-card h-100 text-center p-4">
        <i class="bi bi-shield-lock fs-1 text-warning"></i>
        <h4 class="mt-3">IT Security & Support</h4>
        <p>Comprehensive cybersecurity solutions and 24/7 IT support to safeguard your business operations.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card service-card h-100 text-center p-4">
        <i class="bi bi-people fs-1 text-secondary"></i>
        <h4 class="mt-3">IT Consultancy & Training</h4>
        <p>Expert guidance, workshops, and staff training to help your team maximize technology adoption.</p>
      </div>
    </div>

  </div>
</div>

<!-- Call to Action -->
<div class="bg-primary text-white text-center py-5">
  <h2 class="fw-bold">Ready to work with us?</h2>
  <p class="lead">Let’s build something amazing together.</p>
  <a href="contact.php" class="btn btn-light btn-lg mt-3">Contact Us</a>
</div>

<!-- Footer -->
<footer class="text-center">
  <div class="container">
    <p class="mb-1">&copy; <?php echo date("Y"); ?> SparkLab. All Rights Reserved.</p>
    <p>
      <a href="about.php">About Us</a> | 
      <a href="services.php">Services</a> | 
      <a href="contact.php">Contact</a>
    </p>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
