const passwordInput = document.querySelector('[data-password-input]');
const toggleButton = document.querySelector('[data-password-toggle]');
const eyeOpen = document.querySelector('[data-eye-open]');
const eyeClosed = document.querySelector('[data-eye-closed]');
const loginForm = document.querySelector('[data-login-form]');
const submitButton = document.querySelector('[data-submit-button]');

if (passwordInput && toggleButton && eyeOpen && eyeClosed) {
    toggleButton.addEventListener('click', () => {
        const shouldReveal = passwordInput.type === 'password';

        passwordInput.type = shouldReveal ? 'text' : 'password';
        toggleButton.setAttribute('aria-pressed', String(shouldReveal));
        eyeOpen.classList.toggle('auth-login-password-toggle__icon--hidden', shouldReveal);
        eyeClosed.classList.toggle('auth-login-password-toggle__icon--hidden', !shouldReveal);
    });
}

if (loginForm && submitButton) {
    loginForm.addEventListener('submit', () => {
        if (!loginForm.checkValidity()) {
            return;
        }

        submitButton.disabled = true;
        submitButton.classList.add('is-loading');
        submitButton.setAttribute('aria-busy', 'true');
    });
}
