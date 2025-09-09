// signup.js
document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");
    const toast = document.getElementById('toast');
    const toastMessage = toast.querySelector('.toast-message');
    const toastCloseBtn = toast.querySelector('.toast-close');

    // Make hideToast global so onclick works
    window.hideToast = function() {
        toast.classList.remove('show', 'success', 'error');
        toastMessage.textContent = '';
    }

    if (toastCloseBtn) {
        toastCloseBtn.addEventListener('click', window.hideToast);
    }

    function showToast(message, type = 'success') {
        toastMessage.textContent = message;
        toast.classList.add('show', type);
        setTimeout(window.hideToast, 3000);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = signupForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        const fullname = signupForm.querySelector('input[name="fullname"]').value.trim();
        const email = signupForm.querySelector('input[name="email"]').value.trim();
        const password = signupForm.querySelector('input[name="password"]').value;

        if (!fullname || !email || !password) {
            showToast('Please fill in all fields', 'error');
            return;
        }
        if (!isValidEmail(email)) {
            showToast('Invalid email address', 'error');
            return;
        }

        submitBtn.textContent = 'Creating Account...';
        submitBtn.disabled = true;

        const formData = new FormData(signupForm);

        try {
            const res = await fetch('signup.php', { method: 'POST', body: formData });
            const text = await res.text();

            showToast(text || 'Account created successfully', 'success');

            signupForm.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;

            setTimeout(() => {
                if (typeof toggleModal === 'function') toggleModal('signup-modal');
            }, 1500);

        } catch (err) {
            showToast('Something went wrong', 'error');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            console.error(err);
        }
    });
});
