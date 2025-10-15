<?php
// Check if session is not already started before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $posted_token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $posted_token)) {
        $errors[] = 'Invalid CSRF token. Please refresh the page and try again.';
    }
    
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Prevent timing attacks
        if (!$user) {
            // Use password_verify with a dummy hash
            password_verify($pass, '$2y$10$dummyhashdummyhashdummyhashdu');
            $errors[] = 'Invalid email or password';
        } else if (!password_verify($pass, $user['password_hash'])) {
            $errors[] = 'Invalid email or password';
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            // Regenerate session ID after login for security
            session_regenerate_id(true);
            header('Location: ' . ($user['role']==='admin' ? 'admin_dashboard.php' : 'client_dashboard.php'));
            exit;
        }
    }
}

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SparkLab | Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    animation: slideShow 20s infinite;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: relative;
  }

  body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
  }

  @keyframes slideShow {
    0%   { background-image: url('https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'); }
    25%  { background-image: url('https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'); }
    50%  { background-image: url('https://images.unsplash.com/photo-1464983953574-0892a716854b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'); }
    75%  { background-image: url('https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'); }
    100% { background-image: url('https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'); }
  }

  .login-card {
    max-width: 450px;
    width: 100%;
    padding: 2.5rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    position: relative;
    z-index: 2;
    backdrop-filter: blur(10px);
  }

  .navbar {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1030;
    width: 100%;
  }

  .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  .btn-primary {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    border: none;
    padding: 10px;
    font-weight: 600;
    transition: all 0.3s;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
  }

  .sparklab-logo {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    text-align: center;
    color: #0d6efd;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .sparklab-logo img {
    max-width: 120px;
    margin-bottom: 10px;
  }

  .login-footer {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.9rem;
    color: #6c757d;
  }

  /* Chatbot styles */
  #chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 350px;
    z-index: 1000;
  }
  #chatbot {
    display: flex;
    flex-direction: column;
    height: 400px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    overflow: hidden;
    transition: all 0.3s ease;
  }
  #chatbot-header {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    color: #fff;
    padding: 12px;
    text-align: center;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
  }
  #chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background-color: #f8f9fa;
  }
  .message {
    margin: 10px 0;
    padding: 10px 15px;
    border-radius: 18px;
    max-width: 80%;
    word-wrap: break-word;
  }
  .user-message {
    background: #d1e7dd;
    margin-left: auto;
    border-bottom-right-radius: 5px;
  }
  .bot-message {
    background: #e9ecef;
    margin-right: auto;
    border-bottom-left-radius: 5px;
  }
  #chatbot-input-container {
    display: flex;
    border-top: 1px solid #ccc;
    background: #fff;
    padding: 10px;
    gap: 8px;
    flex-shrink: 0;
  }
  #chatbot-input {
    flex: 1;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 20px;
    outline: none;
    font-size: 14px;
  }
  #chatbot-send {
    border: none;
    background: #0d6efd;
    color: #fff;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 20px;
    font-size: 14px;
    transition: all 0.2s;
  }
  #chatbot-send:hover {
    background: #0b5ed7;
  }
  #chatbot-send:disabled {
    background: #6c757d;
    cursor: not-allowed;
  }
  .chatbot-minimized #chatbot-messages,
  .chatbot-minimized #chatbot-input-container {
    display: none;
  }
  .chatbot-minimized { 
    height: 44px; 
  }
  #chatbot-toggle {
    position: absolute;
    bottom: -40px;
    right: 0;
    background: #0d6efd;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    font-size: 12px;
    cursor: pointer;
  }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="login.php">
      <i class="fas fa-bolt me-2"></i>SparkLab
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-bars me-1"></i>Menu
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="about.php"><i class="fas fa-info-circle me-2"></i>About</a></li>
            <li><a class="dropdown-item" href="services.php"><i class="fas fa-cogs me-2"></i>Services</a></li>
            <li><a class="dropdown-item" href="projects.php"><i class="fas fa-project-diagram me-2"></i>Projects</a></li>
            <li><a class="dropdown-item" href="portfolio.php"><i class="fas fa-briefcase me-2"></i>Portfolio</a></li>
            <li><a class="dropdown-item" href="location.php"><i class="fas fa-map-marker-alt me-2"></i>Location</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Login Card -->
<div class="login-card">
  <div class="sparklab-logo">
    <img src="images/logo.jpg" alt="Company Logo">
    <i class="fas fa-bolt"></i>
  </div>
  <h3 class="text-center mb-4">SparkLab Login</h3>

  <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle me-2"></i>Registration successful. Please login.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['logout'])): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle me-2"></i>You have been successfully logged out.
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <?php echo implode('<br>', array_map('e', $errors)); ?>
    </div>
  <?php endif; ?>

  <form method="post" id="loginForm">
    <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>">

    <div class="mb-3">
      <label class="form-label">Email</label>
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        <input name="email" type="email" class="form-control" required value="<?php echo e($_POST['email'] ?? ''); ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
        <input name="password" type="password" class="form-control" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">
      <i class="fas fa-sign-in-alt me-2"></i>Login
    </button>

    <div class="text-center mt-3">
      <a href="register.php">Create client account</a> | 
      <a href="forgot_password.php">Forgot password?</a>
    </div>
  </form>

  <div class="login-footer">
    <p>&copy; 2025 SparkLab. All rights reserved.</p>
  </div>
</div>

<!-- Chatbot -->
<div id="chatbot-container">
  <div id="chatbot">
    <div id="chatbot-header">
      <span><i class="fas fa-robot me-2"></i>SparkLab Assistant</span>
      <button id="chatbot-minimize" class="btn btn-sm btn-light">−</button>
    </div>
    <div id="chatbot-messages">
      <div class="message bot-message">
        Hello! I'm SparkLab's AI assistant. Ask me anything about our company or services.
      </div>
    </div>
    <div id="chatbot-input-container">
      <input type="text" id="chatbot-input" placeholder="Ask about SparkLab...">
      <button id="chatbot-send">Send</button>
    </div>
  </div>
  <button id="chatbot-toggle" class="d-none">Chat</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const messagesContainer = document.getElementById('chatbot-messages');
const chatInput = document.getElementById('chatbot-input');
const sendButton = document.getElementById('chatbot-send');
const minimizeButton = document.getElementById('chatbot-minimize');
const toggleButton = document.getElementById('chatbot-toggle');
const chatbot = document.getElementById('chatbot');

function addMessage(text, isUser=false){
    const msg = document.createElement('div');
    msg.className = 'message ' + (isUser?'user-message':'bot-message');
    msg.textContent = text;
    messagesContainer.appendChild(msg);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

sendButton.addEventListener('click', () => {
    const msg = chatInput.value.trim();
    if(!msg) return;
    addMessage(msg,true);
    chatInput.value='';
    sendButton.disabled=true;

    setTimeout(()=>{
        let answer = "SparkLab specializes in AI solutions, web & mobile development, cloud services, and digital marketing.";
        if(msg.toLowerCase().includes("service")) answer="We offer AI, web & mobile app development, cloud computing, data analytics, and digital marketing services.";
        else if(msg.toLowerCase().includes("contact") || msg.toLowerCase().includes("where")) answer="You can reach us at contact@sparklab.com or visit our office at 123 Innovation Drive.";
        else if(msg.toLowerCase().includes("hour") || msg.toLowerCase().includes("time")) answer="Our support team is available Monday to Friday, 9 AM to 6 PM.";
        else if(msg.toLowerCase().includes("login") || msg.toLowerCase().includes("account")) answer="You can login with your email and password. If you don't have an account, please register first.";
        addMessage(answer,false);
        sendButton.disabled=false;
        chatInput.focus();
    },500);
});

chatInput.addEventListener('keypress', (e)=>{
    if(e.key==='Enter' && !sendButton.disabled) sendButton.click();
});

minimizeButton.addEventListener('click', ()=>{
    chatbot.classList.add('chatbot-minimized');
    toggleButton.classList.remove('d-none');
});
toggleButton.addEventListener('click', ()=>{
    chatbot.classList.remove('chatbot-minimized');
    toggleButton.classList.add('d-none');
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = this.querySelector('input[name="email"]');
    const password = this.querySelector('input[name="password"]');
    
    if (!email.value || !password.value) {
        e.preventDefault();
        alert('Please fill in all required fields');
    }
});

// Slideshow background
const images = [
  "https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80",
  "https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80",
  "https://images.unsplash.com/photo-1464983953574-0892a716854b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80",
  "https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80"
];
let index = 0;
function changeBackground() {
    document.body.style.backgroundImage = `url('${images[index]}')`;
    index = (index + 1) % images.length;
}
changeBackground();
setInterval(changeBackground, 5000);
</script>

</body>
</html>
