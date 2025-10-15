<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Contact Us | SparkLab</title>
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
                  url('https://images.unsplash.com/photo-1521791055366-0d553872125f?auto=format&fit=crop&w=1400&q=80');
      background-size: cover;
      background-position: center;
      color: white;
      text-align: center;
      padding: 100px 20px;
    }
    .contact-info i {
      font-size: 1.5rem;
      color: #0d6efd;
      margin-right: 10px;
    }
    .form-control, .btn {
      border-radius: 0.6rem;
    }
    .map-container {
      border-radius: 15px;
      overflow: hidden;
      height: 400px;
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
  <h1 class="display-4 fw-bold">Contact Us</h1>
  <p class="lead">We’d love to hear from you. Let’s connect!</p>
</div>

<!-- Contact Section -->
<div class="container py-5">
  <div class="row g-5">
    
    <!-- Contact Info -->
    <div class="col-lg-5">
      <h2 class="fw-bold mb-4">Get in Touch</h2>
      <p class="mb-4">Have a project in mind or need support? Reach out to us using the form or via the contact details below.</p>
      
      <div class="contact-info mb-3 d-flex align-items-center">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Nairobi, Kenya</span>
      </div>
      <div class="contact-info mb-3 d-flex align-items-center">
        <i class="bi bi-envelope-fill"></i>
        <span>info@sparklab.com</span>
      </div>
      <div class="contact-info mb-3 d-flex align-items-center">
        <i class="bi bi-telephone-fill"></i>
        <span>+254 700 123 456</span>
      </div>
      <div class="contact-info mb-3 d-flex align-items-center">
        <i class="bi bi-clock-fill"></i>
        <span>Mon – Fri: 9:00 AM – 6:00 PM</span>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="col-lg-7">
      <div class="card shadow border-0 p-4">
        <h3 class="fw-bold mb-3">Send Us a Message</h3>
        <form action="contact_process.php" method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <input type="text" class="form-control" name="name" placeholder="Your Name" required>
            </div>
            <div class="col-md-6">
              <input type="email" class="form-control" name="email" placeholder="Your Email" required>
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control" name="phone" placeholder="Your Phone">
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control" name="subject" placeholder="Subject" required>
            </div>
            <div class="col-12">
              <textarea class="form-control" name="message" rows="5" placeholder="Your Message" required></textarea>
            </div>
            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary btn-lg px-4">Send Message</button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<!-- Google Map -->
<div class="container pb-5">
  <h2 class="fw-bold mb-4 text-center">Find Us on the Map</h2>
  <div class="map-container">
    <iframe 
      src="https://www.google.com/maps?q=THfvDWhEmf6QCba19&output=embed" 
      width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
    </iframe>
  </div>
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
