document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initDonationSystem();
    initForms();
    initTestimonialSlider();
    initRippleEffects();
    setupSmoothScrolling();
});

// Mobile Menu Toggle
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            mobileMenu.classList.toggle('block');
        });
    }
}

// Donation System
function initDonationSystem() {
    const donationOptions = document.querySelectorAll('.donation-option');
    const customAmount = document.getElementById('customAmount');
    const generateQRBtn = document.getElementById('generateQR');
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const qrCodeElement = document.getElementById('qrCode');
    const saveQRBtn = document.getElementById('saveQR');
    const upiLinkEl = document.getElementById('upiLink');
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    let qrCode = null;

    // Handle donation option selection
    donationOptions.forEach(option => {
        option.addEventListener('click', () => {
            donationOptions.forEach(opt => opt.classList.remove('border-primary'));
            option.classList.add('border-primary');
            customAmount.value = option.dataset.amount;
            customAmount.dispatchEvent(new Event('input'));
        });
    });

    // Handle custom amount input
    customAmount?.addEventListener('input', (e) => {
        let value = e.target.value.replace(/[^0-9]/g, "");

        if (value) {
            const numericValue = parseInt(value);

            if (numericValue < 100) {
                showToast("Minimum donation amount is ₹100", "error");
                generateQRBtn.disabled = true;
                generateQRBtn.classList.add('opacity-50');
            } else {
                generateQRBtn.disabled = false;
                generateQRBtn.classList.remove('opacity-50');
                e.target.value = "₹" + numericValue.toLocaleString('en-IN');
            }
        } else {
            generateQRBtn.disabled = true;
            generateQRBtn.classList.add('opacity-50');
            e.target.value = "";
        }
    });

    // Generate QR Code
    generateQRBtn?.addEventListener('click', () => {
        const rawValue = customAmount.value.replace(/[^0-9]/g, "");
        const amount = parseInt(rawValue);

        if (isNaN(amount) || amount < 100) {
            showToast("Minimum donation amount is ₹100", "error");
            return;
        }

        const upiLink = `upi://pay?pa=helpinghands8785@sbi&pn=Helping Hands&am=${amount}&cu=INR`;

        qrCodeElement.innerHTML = "";
        qrCodeContainer.classList.remove('hidden');

        qrCode = new QRCode(qrCodeElement, {
            text: upiLink,
            width: 192,
            height: 192,
            correctLevel: QRCode.CorrectLevel.H
        });

        upiLinkEl.textContent = "Make Payment";

        upiLinkEl.href = upiLink;

        copyLinkBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(upiLink).then(() => {
                showToast("UPI link copied to clipboard", "success");
            }).catch(() => {
                showToast("Failed to copy the link", "error");
            });
        });
    });

    // Save QR Code
    saveQRBtn?.addEventListener('click', () => {
        if (!qrCode) return;
        const canvas = qrCodeElement.querySelector('canvas');
        const image = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = image;
        link.download = 'donation-qr.png';
        link.click();
    });
}

// Toast Notification System
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 
        ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white 
        transform transition-all duration-300 translate-y-[-200%]`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateY(0)';
    }, 50);

    setTimeout(() => {
        toast.style.transform = 'translateY(-200%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Form Handling
function initForms() {
    document.getElementById('contactForm')?.addEventListener('submit', async (e) => {
        await submitForm(e.target, 'processes/contact-process.php');
    });

    document.getElementById('newsletterForm')?.addEventListener('submit', async (e) => {
        await submitForm(e.target, 'processes/subscribe-process.php');
    });
}

// Form Submission Handler
async function submitForm(form, actionUrl) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i> Processing...';

        const formData = new FormData(form);
        const response = await fetch(actionUrl, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Network error');
        const result = await response.json();

        showToast(result.message, result.status);
        if (result.status === 'success') form.reset();
    } catch (error) {
        showToast('Failed to submit form', 'error');
        console.error('Error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Testimonial Slider
function initTestimonialSlider() {
    let currentSlide = 0;
    const totalSlides = 3;
    const prevBtn = document.getElementById('prevSlide');
    const nextBtn = document.getElementById('nextSlide');

    if (!prevBtn || !nextBtn) return;

    const updateSlider = () => {
        const offset = -(currentSlide * 100);
        document.querySelector('.testimonial-slider').style.transform = `translateX(${offset}%)`;
    };

    prevBtn.addEventListener('click', () => {
        currentSlide = Math.max(currentSlide - 1, 0);
        updateSlider();
    });

    nextBtn.addEventListener('click', () => {
        currentSlide = Math.min(currentSlide + 1, totalSlides - 1);
        updateSlider();
    });
}

// Ripple Effects
function initRippleEffects() {
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('div');

            ripple.style.left = (e.clientX - rect.left) + 'px';
            ripple.style.top = (e.clientY - rect.top) + 'px';
            ripple.className = 'ripple';

            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// Dummy function if smooth scroll required
function setupSmoothScrolling() {
    // Optional setup code
}
