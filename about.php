<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>About | SparkLab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  .hero {
    background: 
      linear-gradient(rgba(13,110,253,0.7), rgba(13,110,253,0.7)), 
      url('images/about-banner.jpg') center/cover no-repeat;
    color: white;
    height: 60vh;
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
  .team img {
    border: 4px solid #eee;
    transition: transform 0.3s;
  }
  .team img:hover {
    transform: scale(1.05);
  }
  .timeline {
    border-left: 3px solid #0d6efd;
    padding-left: 20px;
  }
  .timeline h5 {
    color: #0d6efd;
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
    <h1>About SparkLab</h1>
    <p class="lead">Innovating the Future with Technology & Creativity</p>
  </div>
</section>

<div class="container py-5 flex-grow-1">

  <!-- Company Overview -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Who We Are</h2>
    <p class="lead text-center">
      SparkLab is a forward-thinking IT solutions company that designs, develops, and deploys 
      innovative digital systems tailored for businesses of all sizes. Our expertise covers 
      <strong>web development, system automation, software engineering, and digital consultancy</strong>.
    </p>
    <p class="text-center">
      Since our founding, we’ve been driven by a passion for solving problems using technology. 
      We partner with businesses to transform their ideas into powerful, efficient, and 
      user-friendly applications.
    </p>
  </section>

  <!-- Mission, Vision, Values -->
  <section class="row text-center mb-5">
    <div class="col-md-4">
      <div class="card shadow-sm p-4">
        <h4>Our Mission</h4>
        <p>To empower businesses through cutting-edge IT solutions that drive growth and efficiency.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm p-4">
        <h4>Our Vision</h4>
        <p>To become Africa’s most trusted IT innovation hub, shaping the digital future for generations.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm p-4">
        <h4>Our Values</h4>
        <p>Innovation • Integrity • Collaboration • Excellence • Customer First</p>
      </div>
    </div>
  </section>

  <!-- Timeline / Our Story -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Our Story</h2>
    <div class="timeline">
      <h5>2022 - Founded</h5>
      <p>SparkLab was born with the goal of helping small businesses access affordable IT solutions.</p>
      <h5>2023 - First Milestone</h5>
      <p>Launched our first SaaS product, helping local companies automate operations.</p>
      <h5>2024 - Expansion</h5>
      <p>Opened new offices and expanded our services to cover system integration and AI-based tools.</p>
      <h5>2025 - Present</h5>
      <p>Continuing to grow with clients across multiple industries, driving digital transformation worldwide.</p>
    </div>
  </section>

  <!-- Team Members -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Meet Our Team</h2>
    <div class="row text-center team">
      <div class="col-md-3">
        <img src="images/ceo.jpg" class="rounded-circle mb-3" width="150" height="150" alt="CEO">
        <h5>Andrew Saitabau</h5>
        <p class="text-muted">Chief Executive Officer</p>
      </div>
      <div class="col-md-3">
        <img src="images/assistant_ceo.jpg" class="rounded-circle mb-3" width="150" height="150" alt="Assistant CEO">
        <h5>Jane Smith</h5>
        <p class="text-muted">Assistant CEO</p>
      </div>
      <div class="col-md-3">
        <img src="images/dev.jpg" class="rounded-circle mb-3" width="150" height="150" alt="Lead Developer">
        <h5>Emily NJOROGE</h5>
        <p class="text-muted">Lead Developer</p>
      </div>
      <div class="col-md-3">
        <img src="images/designer.jpg" class="rounded-circle mb-3" width="150" height="150" alt="UI Designer">
        <h5>Johnson Tirkolo</h5>
        <p class="text-muted">UI/UX Designer</p>
      </div>
    </div>
  </section>

  <!-- Why Choose Us -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Why Choose SparkLab?</h2>
    <div class="row text-center">
      <div class="col-md-3">
        <h5>✔ Innovative Solutions</h5>
        <p>We bring fresh, creative ideas to every project.</p>
      </div>
      <div class="col-md-3">
        <h5>✔ Experienced Team</h5>
        <p>Our experts have years of combined IT knowledge.</p>
      </div>
      <div class="col-md-3">
        <h5>✔ Client Focused</h5>
        <p>We listen to your needs and customize solutions accordingly.</p>
      </div>
      <div class="col-md-3">
        <h5>✔ Proven Results</h5>
        <p>Our portfolio speaks for itself with successful deployments.</p>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="mb-5">
    <h2 class="text-center mb-4">What Our Clients Say</h2>
    <div class="row">
      <div class="col-md-4">
        <div class="testimonial shadow-sm">
          <p>“SparkLab transformed our business operations with their system. Professional and reliable!”</p>
          <h6>- TechStart Ltd</h6>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial shadow-sm">
          <p>“We loved working with their team! Their creativity and problem-solving skills are unmatched.”</p>
          <h6>- CreativeHub</h6>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial shadow-sm">
          <p>“The SparkLab team delivered beyond expectations. Highly recommended for IT solutions.”</p>
          <h6>- InnovateX</h6>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="mb-5">
    <h2 class="text-center mb-4">Get in Touch</h2>
    <div class="row">
      <div class="col-md-6">
        <h5>Contact Information</h5>
        <p><strong>Email:</strong> info@sparklab.com</p>
        <p><strong>Phone:</strong> +254 700 123 456</p>
        <p><strong>Address:</strong> Nairobi, Kenya</p>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!..." 
                width="100%" height="250" style="border:0;" 
                allowfullscreen="" loading="lazy"></iframe>
      </div>
      <div class="col-md-6">
        <h5>Send Us a Message</h5>
        <form action="contact_process.php" method="POST">
          <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
          </div>
          <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
          </div>
          <div class="mb-3">
            <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
      </div>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
