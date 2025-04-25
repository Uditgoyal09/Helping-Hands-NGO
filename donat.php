<?php
// Start session and include database configuration
session_start();
require_once 'db.php';
require_once 'includes/config.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process donation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    // Sanitize inputs
    $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    try {
        // Validate amount
        if ($amount < 100) {
            throw new Exception("Minimum donation amount is ₹100");
        }

        // Save to database
        $stmt = $pdo->prepare("INSERT INTO donations (amount, donor_email) VALUES (?, ?)");
        $stmt->execute([$amount, $email]);

        // Set success message
        $_SESSION['donation_message'] = "Thank you for your donation of ₹{$amount}!";
        header("Location: donation.php");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hope Foundation - Make a Difference Today</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: "#4F46E5", secondary: "#10B981" },
                    borderRadius: {
                        none: "0px",
                        sm: "4px",
                        DEFAULT: "4px",
                        md: "12px",
                        lg: "16px",
                        xl: "20px",
                        "2xl": "24px",
                        "3xl": "32px",
                        full: "9999px",
                        button: "4px",
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .qr-code-container { transition: all 0.3s ease; }
        .testimonial-slider { transition: transform 0.5s ease; }
        button {
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:active { transform: scale(0.95); }
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple {
            to { transform: scale(4); opacity: 0; }
        }
        button.active { opacity: 0.8; }
        button:hover { box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-white min-h-screen">
    <header class="fixed w-full bg-white shadow-sm z-50">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="#" class="text-2xl font-['Pacifico'] text-primary"><img src="logo.jpeg" alt="NGO Logo"></a>
                    <div class="hidden md:flex items-center ml-16 space-x-8">
                        <a href="#" class="text-gray-700 hover:text-primary">Home</a>
                        <a href="#about" class="text-gray-700 hover:text-primary">About Us</a>
                        <a href="#donate" class="text-gray-700 hover:text-primary">Donate</a>
                        <a href="#photos" class="text-gray-700 hover:text-primary">Gallery</a>
                        <a href="#impact" class="text-gray-700 hover:text-primary">Impact Stories</a>
                        <a href="#contact" class="text-gray-700 hover:text-primary">Contact</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Social media links remain same -->
                </div>
            </div>
        </nav>
    </header>

    <main class="pt-16">
        <!-- Donation Section -->
        <section id="donate" class="py-20">
            <div class="container mx-auto px-6">
                <?php if(isset($_SESSION['donation_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
                        <?= htmlspecialchars($_SESSION['donation_message']); ?>
                        <?php unset($_SESSION['donation_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <h2 class="text-3xl font-bold text-center mb-12">Make a Donation</h2>
                <div class="max-w-2xl mx-auto">
                    <form id="donationForm" method="POST" class="bg-white rounded-lg shadow-lg p-8">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="donate" value="1">
                        
                        <div class="space-y-8">
                            <!-- Donation options remain same -->
                            
                            <div class="max-w-md mx-auto">
                                <div class="mb-6">
                                    <label class="block text-gray-700 mb-2">Custom Amount (₹)</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                                        <input
                                            type="text"
                                            id="customAmount"
                                            name="amount"
                                            class="w-full pl-8 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none text-lg"
                                            placeholder="Enter your amount"
                                            required
                                        />
                                    </div>
                                    <div class="text-sm text-gray-500 mt-2">* Minimum donation amount is ₹100</div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-gray-700 mb-2">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none"
                                        required
                                    />
                                </div>

                                <button
                                    type="submit"
                                    class="w-full bg-primary text-white py-4 !rounded-button font-semibold hover:bg-opacity-90 transition-all mb-2 whitespace-nowrap flex items-center justify-center"
                                >
                                    <i class="ri-heart-line mr-2"></i>
                                    Complete Donation
                                </button>
                                <p class="text-center text-sm text-gray-500">
                                    Secure payment processing
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Rest of the sections remain similar to original HTML -->
        
    </main>

    <footer class="bg-gray-900 text-white py-12">
        <!-- Footer content remains same -->
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
      // Function to show notification
    // Add this function to your existing script
function submitForm(form, actionUrl) {
    // Prevent default form submission
    event.preventDefault();
    
    // Create FormData object from the form
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Processing...';
    submitBtn.disabled = true;

    // Send AJAX request
    fetch(actionUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if(data.status === 'success') {
            showToast(data.message, 'success');
            form.reset();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
    
    return false;
}

// Make sure you have this toast notification function (add if missing)
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded shadow-lg z-50 ${
        type === "success" ? "bg-green-500" : "bg-red-500"
    } text-white transform transition-all duration-300 translate-y-[-100%]`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = "translateY(0)";
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = "translateY(-100%)";
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
      document.addEventListener("DOMContentLoaded", function () {
        const mobileMenuBtn = document.getElementById("mobileMenuBtn");
        const mobileMenu = document.getElementById("mobileMenu");
        const amountBtns = document.querySelectorAll(".amount-btn");
        const customAmount = document.getElementById("customAmount");
        const generateQRBtn = document.getElementById("generateQR");
        const qrCodeContainer = document.getElementById("qrCodeContainer");
        const qrCodeElement = document.getElementById("qrCode");
        const saveQRBtn = document.getElementById("saveQR");
        const contactForm = document.getElementById("contactForm");
        const newsletterForm = document.getElementById("newsletterForm");
        const testimonialSlider = document.getElementById("testimonialSlider");
        const prevSlideBtn = document.getElementById("prevSlide");
        const nextSlideBtn = document.getElementById("nextSlide");
        let currentSlide = 0;
        const totalSlides = 3;
        let qrCode = null;
        // Add click event listeners to all buttons
        document.querySelectorAll("button").forEach((button) => {
          button.addEventListener("click", function (e) {
            // Add ripple effect
            const ripple = document.createElement("div");
            ripple.classList.add("ripple");
            this.appendChild(ripple);
            // Get button position
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ripple.style.left = x + "px";
            ripple.style.top = y + "px";
            // Remove ripple after animation
            setTimeout(() => ripple.remove(), 600);
            // Add active state
            this.classList.add("active");
            setTimeout(() => this.classList.remove("active"), 200);
          });
        });
        // Add hover effect to all buttons
        document.querySelectorAll("button").forEach((button) => {
          button.addEventListener("mouseenter", function () {
            this.style.transform = "scale(1.05)";
            this.style.transition = "transform 0.2s ease";
          });
          button.addEventListener("mouseleave", function () {
            this.style.transform = "scale(1)";
          });
        });
        mobileMenuBtn.addEventListener("click", () => {
          mobileMenu.classList.toggle("hidden");
        });
        // Handle donation option selection
        document.querySelectorAll(".donation-option").forEach((option) => {
          option.addEventListener("click", () => {
            // Remove selected state from all options
            document.querySelectorAll(".donation-option").forEach((opt) => {
              opt.classList.remove("border-primary");
            });
            // Add selected state to clicked option
            option.classList.add("border-primary");
            // Update custom amount input
            customAmount.value = option.dataset.amount;
            // Smooth scroll to custom amount section
            customAmount.scrollIntoView({ behavior: "smooth", block: "center" });
          });
        });
        // Handle frequency selection
        document.querySelectorAll(".frequency-btn").forEach((btn) => {
          btn.addEventListener("click", () => {
            // Remove selected state from all frequency buttons
            document.querySelectorAll(".frequency-btn").forEach((b) => {
              b.classList.remove("border-primary", "text-primary");
            });
            // Add selected state to clicked button
            btn.classList.add("border-primary", "text-primary");
          });
        });
        // Validate custom amount input
        customAmount.addEventListener("input", (e) => {
  // Get raw numeric value
  let value = e.target.value.replace(/[^0-9]/g, "");
  
  if (value) {
    const numericValue = parseInt(value);
    
    // Update validation
    if (numericValue < 100) {
      showToast("Minimum donation amount is ₹100", "error");
      generateQRBtn.disabled = true;
      generateQRBtn.classList.add("opacity-50");
    } else {
      generateQRBtn.disabled = false;
      generateQRBtn.classList.remove("opacity-50");
    }
    
    // Format display value
    e.target.value = "₹" + numericValue.toLocaleString("en-IN");
  } else {
    e.target.value = "";
  }
});
        generateQRBtn.addEventListener("click", () => {
  // Get raw number value without currency symbol
  const rawValue = customAmount.value.replace(/[^0-9]/g, "");
  const amount = parseInt(rawValue);

  if (!amount || amount < 100) {
    showToast("Minimum donation amount is ₹100", "error");
    return;
  }

  qrCodeContainer.classList.remove("hidden");
  qrCodeElement.innerHTML = "";
  qrCode = new QRCode(qrCodeElement, {
    text: `upi://pay?pa=uditgoyal90532@okhdfcbank&pn=Udit Goyal&am=${amount}&cu=INR`,
    width: 192,
    height: 192,
  });
});
        saveQRBtn.addEventListener("click", () => {
          if (!qrCode) return;
          const canvas = qrCodeElement.querySelector("canvas");
          const image = canvas.toDataURL("image/png");
          const link = document.createElement("a");
          link.href = image;
          link.download = "donation-qr.png";
          link.click();
        });
        function showNotification(message, type = "success") {
          showToast(message, type);
        }
        contactForm.addEventListener("submit", (e) => {
          e.preventDefault();
          showNotification("Message sent successfully!");
          contactForm.reset();
        });
        newsletterForm.addEventListener("submit", (e) => {
          e.preventDefault();
          showNotification("Successfully subscribed to newsletter!");
          newsletterForm.reset();
        });
        function updateSlider() {
          const offset = -currentSlide * 100;
          testimonialSlider.querySelector(".testimonial-slider").style.transform =
            `translateX(${offset}%)`;
        }
        prevSlideBtn.addEventListener("click", () => {
          currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
          updateSlider();
        });
        nextSlideBtn.addEventListener("click", () => {
          currentSlide = (currentSlide + 1) % totalSlides;
          updateSlider();
        });
        // Remove auto slide
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
          anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
              window.scrollTo({
                top: target.offsetTop - 64,
                behavior: "smooth",
              });
              if (mobileMenu.classList.contains("block")) {
                mobileMenu.classList.remove("block");
              }
            }
          });
        });
      });
    </script>
</body>
</html>
