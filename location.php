<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Location | SparkLab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .location-header {
      background: linear-gradient(to right, #0d6efd, #6610f2);
      color: white;
      padding: 60px 0;
      text-align: center;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    iframe {
      border-radius: 15px;
    }
    footer {
      background: #0d6efd;
      color: white;
      padding: 20px 0;
      margin-top: 50px;
      text-align: center;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Header Section -->
<div class="location-header">
  <h1 class="fw-bold">Our Locations</h1>
  <p>Visit any of our branches near you</p>
</div>

<div class="container py-5">

  <!-- HQ Section -->
  <div class="row mb-5">
    <div class="col-lg-6">
      <div class="card p-4">
        <h2 class="mb-3 text-primary">Headquarters - Nairobi</h2>
        <p><strong>SparkLab HQ</strong><br>
           Nairobi, Kenya<br>
           Phone: +254 700 000 000<br>
           Email: info@sparklab.com</p>
        <p>Our HQ is the main hub where all major operations take place. Feel free to stop by for official inquiries, project discussions, or general support.</p>
      </div>
    </div>
    <div class="col-lg-6">
      <!-- Nairobi Google Maps Embed -->
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8590789730495!2d36.81722397496516!3d-1.2863899987139384!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f173487a1b7ff%3A0xe84d4d9b98cb0f49!2sNairobi!5e0!3m2!1sen!2ske!4v1727346000000!5m2!1sen!2ske" 
        width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </div>

  <!-- Thika Branch Section -->
  <div class="row mb-5">
    <div class="col-lg-6 order-lg-2">
      <div class="card p-4">
        <h2 class="mb-3 text-primary">Thika Branch - Engen</h2>
        <p><strong>SparkLab Thika (Engen)</strong><br>
           Thika, Kenya<br>
           Phone: +254 711 111 111<br>
           Email: thika@sparklab.com</p>
        <p>This branch is strategically located at Thika Engen to serve clients in Kiambu County and surrounding areas. Drop by for consultations, tech solutions, or quick assistance.</p>
      </div>
    </div>
    <div class="col-lg-6 order-lg-1">
      <!-- Thika Engen Google Maps Embed -->
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.7854490832497!2d37.06947447496543!3d-1.0332398989125078!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f3b6e6c37dbf7%3A0xfda57b8aa1a931d1!2sEngen%20Petrol%20Station%20-%20Thika!5e0!3m2!1sen!2ske!4v1727345600000!5m2!1sen!2ske" 
        width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </div>

  <!-- Contact Section -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card p-4">
        <h2 class="mb-3 text-primary">Get in Touch</h2>
        <p>If you have any questions, fill out the form below and we’ll get back to you as soon as possible.</p>
        <form method="post" action="contact_process.php">
  <div class="row mb-3">
    <div class="col-md-6">
      <input type="text" name="name" class="form-control" placeholder="Your Name" required>
    </div>
    <div class="col-md-6">
      <input type="email" name="email" class="form-control" placeholder="Your Email" required>
    </div>
  </div>
  <div class="mb-3">
    <input type="text" name="phone" class="form-control" placeholder="Your Phone">
  </div>
  <div class="mb-3">
    <input type="text" name="subject" class="form-control" placeholder="Subject">
  </div>
  <div class="mb-3">
    <textarea name="message" class="form-control" rows="5" placeholder="Message" required></textarea>
  </div>
  <button class="btn btn-primary">Send Message</button>
</form>

      </div>
    </div>
  </div>

</div>

<!-- Footer -->
<footer>
  <p>&copy; <?php echo date("Y"); ?> SparkLab. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
