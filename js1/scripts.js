// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initTooltips();
    addFormValidations();
});

// Password Toggle
function initPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const isPassword = input.type === 'password';
            
            input.type = isPassword ? 'text' : 'password';
            this.classList.toggle('fa-eye-slash', isPassword);
            this.classList.toggle('fa-eye', !isPassword);
        });
    });
}

// Tooltips
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', showTooltip);
        el.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = e.target.dataset.tooltip;
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = `${rect.left + window.scrollX}px`;
    tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
    
    document.body.appendChild(tooltip);
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) tooltip.remove();
}

// Form Validation
function addFormValidations() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            // Email validation
            const email = this.querySelector('input[type="email"]');
            if (email && !validateEmail(email.value)) {
                showError('Please enter a valid email address', email);
                valid = false;
            }

            // Password validation
            const password = this.querySelector('input[type="password"]');
            if (password && password.value.length < 8) {
                showError('Password must be at least 8 characters', password);
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    });
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(message, element) {
    const error = document.createElement('div');
    error.className = 'inline-error';
    error.textContent = message;
    
    element.parentNode.insertBefore(error, element.nextSibling);
    setTimeout(() => error.remove(), 5000);
}

// AJAX Helper
function fetchJSON(url, options) {
    return fetch(url, options)
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.json();
        });
}