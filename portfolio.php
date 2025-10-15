<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Portfolio | SparkLab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .hero {
      background: url('images/portfolio-banner.jpg') center/cover no-repeat;
      color: white;
      height: 50vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.6);
    }
    .portfolio-item {
      position: relative;
      overflow: hidden;
    }
    .portfolio-item img {
      transition: transform 0.4s;
    }
    .portfolio-item:hover img {
      transform: scale(1.1);
    }
    .overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.4s;
    }
    .portfolio-item:hover .overlay {
      opacity: 1;
    }
    .testimonial {
      background: #f8f9fa;
      padding: 2rem;
      border-radius: 10px;
      margin: 1rem;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'navbar.php'; ?>

<!-- Hero Section -->
<section class="hero">
  <div>
    <h1>Our Portfolio</h1>
    <p class="lead">Showcasing our creativity, innovation, and successful projects</p>
  </div>
</section>

<div class="container py-5 flex-grow-1">

  <!-- Portfolio Grid -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Recent Projects</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="portfolio-item">
          <img src="g1.jpeg" class="img-fluid rounded shadow" alt="Project 1">
          <div class="overlay">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#project1">View Details</button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="portfolio-item">
          <img src="g2.jpeg" class="img-fluid rounded shadow" alt="Project 2">
          <div class="overlay">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#project2">View Details</button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="portfolio-item">
          <img src="g3.jpeg" class="img-fluid rounded shadow" alt="Project 3">
          <div class="overlay">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#project3">View Details</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Project Modals -->
  <div class="modal fade" id="project1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content p-4">
        <h4>Project 1: Business Website</h4>
        <p>We developed a responsive and modern website for a local business, boosting their online presence.</p>
        <img src="x10.jpeg" class="img-fluid rounded" alt="Project 1">
      </div>
    </div>
  </div>

  <div class="modal fade" id="project2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content p-4">
        <h4>Project 2: E-commerce System</h4>
        <p>A fully functional online store with integrated payment solutions and inventory management.</p>
        <img src="x1.jpg" class="img-fluid rounded" alt="Project 2">
      </div>
    </div>
  </div>

  <div class="modal fade" id="project3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content p-4">
        <h4>Project 3: Tourism Booking Platform</h4>
        <p>A system that allows clients to book tours, hotels, and services online with M-Pesa integration.</p>
        <img src="x2.jpg" class="img-fluid rounded" alt="Project 3">
      </div>
    </div>
  </div>

  <!-- Services Section -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Our Expertise</h2>
    <div class="row text-center">
      <div class="col-md-3">
        <h5>🌐 Web Development</h5>
        <p>Modern, responsive websites tailored to your brand.</p>
      </div>
      <div class="col-md-3">
        <h5>🛒 E-Commerce</h5>
        <p>Robust online stores with secure payments.</p>
      </div>
      <div class="col-md-3">
        <h5>⚙️ System Automation</h5>
        <p>Custom systems to streamline business operations.</p>
      </div>
      <div class="col-md-3">
        <h5>📊 Data & Analytics</h5>
        <p>Transforming data into insights for better decisions.</p>
      </div>
    </div>
  </section>

  <!-- Client Logos -->
  <section class="mb-5 text-center">
    <h2 class="mb-4">Trusted by Brands</h2>
    <div class="row g-4 align-items-center">
      <div class="col-md-3"><img src="images/x1.jpg" class="img-fluid" alt="Client 1"></div>
      <div class="col-md-3"><img src="images/x2.jpg" class="img-fluid" alt="Client 2"></div>
      <div class="col-md-3"><img src="images/x3.jpg" class="img-fluid" alt="Client 3"></div>
      <div class="col-md-3"><img src="images/x4.jpg" class="img-fluid" alt="Client 4"></div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="mb-5">
    <h2 class="text-center mb-4">What Clients Say</h2>
    <div class="row">
      <div class="col-md-4">
        <div class="testimonial shadow-sm">
          <p>“SparkLab built our e-commerce store and sales doubled in 3 months. Amazing team!”</p>
          <h6>- Client A</h6>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial shadow-sm">
          <p>“We loved their professionalism and creativity. Highly recommend SparkLab.”</p>
          <h6>- Client B</h6>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial shadow-sm">
          <p>“Their tourism booking system transformed how we operate. Excellent work!”</p>
          <h6>- Client C</h6>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="text-center py-5 bg-light rounded shadow">
    <h2>Have a Project in Mind?</h2>
    <p class="lead">Let’s work together to turn your idea into reality.</p>
    <a href="contact.php" class="btn btn-primary btn-lg">Contact Us</a>
  </section>

</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
