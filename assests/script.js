  // Back to Top Button Functionality
        const backToTopBtn = document.getElementById('backToTopBtn');

        // Show/hide back to top button
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
                backToTopBtn.classList.remove('hide');
            } else {
                backToTopBtn.classList.remove('show');
                backToTopBtn.classList.add('hide');
            }
        });

        // Smooth scroll to top
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });