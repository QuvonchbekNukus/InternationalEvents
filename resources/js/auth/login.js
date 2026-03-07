const loginPage = document.querySelector('.login-page');
const passwordInput = document.getElementById('password');
const toggleButton = document.getElementById('togglePassword');
const eyeOpen = document.getElementById('eyeOpen');
const eyeClosed = document.getElementById('eyeClosed');

if (loginPage?.dataset.loginBackground) {
    loginPage.style.backgroundImage = `url("${loginPage.dataset.loginBackground}")`;
}

if (passwordInput && toggleButton && eyeOpen && eyeClosed) {
    toggleButton.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';

        passwordInput.type = isHidden ? 'text' : 'password';
        toggleButton.setAttribute('aria-pressed', String(isHidden));
        toggleButton.setAttribute('aria-label', isHidden ? "Parolni yashirish" : "Parolni ko'rsatish");
        eyeOpen.classList.toggle('is-hidden', isHidden);
        eyeClosed.classList.toggle('is-hidden', !isHidden);
    });
}
