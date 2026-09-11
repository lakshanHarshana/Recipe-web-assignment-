/**
 * js/validation.js - Client-Side Form Validation Engine
 * Handles real-time validation on Blur & Submit for Register, Login, Recipe Add, and Contact forms.
 */

document.addEventListener('DOMContentLoaded', () => {
    initFormValidation();
});

function initFormValidation() {
    // 1. User Registration Form Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        setupFormValidation(registerForm, {
            username: (val) => val.trim().length >= 3 || 'Username must be at least 3 characters.',
            email: (val) => isValidEmail(val) || 'Please enter a valid email address.',
            password: (val) => val.length >= 6 || 'Password must be at least 6 characters.',
            confirm_password: (val, form) => {
                const pass = form.querySelector('[name="password"]')?.value || '';
                return val === pass || 'Passwords do not match.';
            }
        });
    }

    // 2. Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        setupFormValidation(loginForm, {
            email_username: (val) => val.trim().length > 0 || 'Email or username is required.',
            password: (val) => val.length > 0 || 'Password is required.'
        });
    }

    // 3. Add Recipe Form Validation
    const addRecipeForm = document.getElementById('addRecipeForm');
    if (addRecipeForm) {
        setupFormValidation(addRecipeForm, {
            title: (val) => val.trim().length >= 3 || 'Recipe title must be at least 3 characters.',
            category: (val) => val !== '' && val !== 'Select Category' || 'Please select a valid category.',
            prep_time: (val) => (!isNaN(val) && parseInt(val) > 0) || 'Prep time must be a positive number.',
            cook_time: (val) => (!isNaN(val) && parseInt(val) >= 0) || 'Cook time must be 0 or greater.',
            servings: (val) => (!isNaN(val) && parseInt(val) > 0) || 'Servings must be at least 1.',
            ingredients: (val) => val.trim().length >= 10 || 'Ingredients list must be at least 10 characters long.',
            instructions: (val) => val.trim().length >= 15 || 'Step-by-step instructions must be at least 15 characters long.'
        });
    }

    // 4. Contact Form Validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        setupFormValidation(contactForm, {
            name: (val) => val.trim().length >= 2 || 'Full name is required.',
            email: (val) => isValidEmail(val) || 'Please enter a valid email address.',
            subject: (val) => val.trim().length >= 3 || 'Subject is required.',
            message: (val) => val.trim().length >= 10 || 'Message must be at least 10 characters long.'
        });
    }
}

/**
 * Generic helper to bind input event listeners and intercept form submission
 */
function setupFormValidation(form, rules) {
    const inputs = form.querySelectorAll('input, select, textarea');

    // Attach real-time validation on Blur & Input
    inputs.forEach(input => {
        const name = input.name;
        if (!rules[name]) return;

        input.addEventListener('blur', () => validateField(input, rules[name], form));
        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid-custom')) {
                validateField(input, rules[name], form);
            }
        });
    });

    // Form Submit Interception
    form.addEventListener('submit', (e) => {
        let isFormValid = true;

        Object.keys(rules).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                const isValid = validateField(field, rules[fieldName], form);
                if (!isValid) isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            e.stopPropagation();
            
            // Focus on first invalid field
            const firstInvalid = form.querySelector('.is-invalid-custom');
            if (firstInvalid) firstInvalid.focus();
        }
    });
}

/**
 * Validate an individual field against its rule function
 */
function validateField(field, ruleFunc, form) {
    const val = field.value;
    const result = ruleFunc(val, form);
    let feedbackEl = field.nextElementSibling;

    // Check if next element is custom feedback div
    if (!feedbackEl || !feedbackEl.classList.contains('invalid-feedback-custom')) {
        feedbackEl = document.createElement('div');
        feedbackEl.className = 'invalid-feedback-custom';
        field.parentNode.insertBefore(feedbackEl, field.nextSibling);
    }

    if (result === true) {
        field.classList.remove('is-invalid-custom');
        field.classList.add('is-valid-custom');
        feedbackEl.style.display = 'none';
        feedbackEl.textContent = '';
        return true;
    } else {
        field.classList.remove('is-valid-custom');
        field.classList.add('is-invalid-custom');
        feedbackEl.style.display = 'block';
        feedbackEl.textContent = result;
        return false;
    }
}

/**
 * Validate email address syntax using Standard RFC Regex
 */
function isValidEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(String(email).toLowerCase().trim());
}
