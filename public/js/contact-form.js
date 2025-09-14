document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission started');
            
            const formData = new FormData(contactForm);
            
            fetch(contactForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Response received', response);
                if (response.redirected) {
                    window.location.href = response.url;
                }
                return response.json();
            })
            .then(data => {
                console.log('Success:', data);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        });
    }
});
