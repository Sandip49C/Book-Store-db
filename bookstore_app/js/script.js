document.addEventListener('DOMContentLoaded', function() {
    // Form validation for order form
    const orderForm = document.querySelector('.order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            const quantity = document.getElementById('quantity');
            if (quantity && (quantity.value <= 0 || quantity.value > parseInt(quantity.max))) {
                alert('Please enter a valid quantity between 1 and ' + quantity.max);
                e.preventDefault();
            }
        });
    }

    // Form validation for customer form
    const customerForm = document.querySelector('.customer-form');
    if (customerForm) {
        customerForm.addEventListener('submit', function(e) {
            const customerId = document.getElementById('customer_id');
            if (customerId && customerId.value <= 0) {
                alert('Customer ID must be a positive number');
                e.preventDefault();
            }
        });
    }

    // Fade out notifications after 5 seconds
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    });
});