document.addEventListener('DOMContentLoaded', () => {
    const paymentSelect = document.getElementById('payment-method');
    const forms = {
        'Credit Card': 'form-credit',
        'ATM Card': 'form-atm',
        'Bank Transfer': 'form-bank'
    };

    if (!paymentSelect) return;

    paymentSelect.addEventListener('change', () => {
        // Hide all
        Object.values(forms).forEach(id => {
            document.getElementById(id).style.display = 'none';
        });
        // Show selected
        const formId = forms[paymentSelect.value];
        if (formId) {
            document.getElementById(formId).style.display = 'block';
        }
    });

    // Optional: Format credit card input
    window.formatCard = function(input) {
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += value[i];
        }
        input.value = formatted;
    };

    window.formatExpiry = function(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 3) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        input.value = value;
    };
});