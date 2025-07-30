// Dev Network Solutions - Validation Utilities
class FormValidator {
    constructor() {
        this.errors = {};
    }

    // Validar que el monto sea mayor que 0
    validateAmount(value, fieldName = 'amount') {
        const amount = parseFloat(value);
        if (isNaN(amount) || amount <= 0) {
            this.errors[fieldName] = 'El monto debe ser mayor que 0';
            return false;
        }
        if (amount > 999999999.99) {
            this.errors[fieldName] = 'El monto es demasiado grande';
            return false;
        }
        return true;
    }

    // Validar fecha
    validateDate(value, fieldName = 'date') {
        if (!value) {
            this.errors[fieldName] = 'La fecha es requerida';
            return false;
        }
        
        const date = new Date(value);
        const today = new Date();
        const maxDate = new Date();
        maxDate.setFullYear(today.getFullYear() + 1);
        
        if (date > maxDate) {
            this.errors[fieldName] = 'La fecha no puede ser mayor a un año en el futuro';
            return false;
        }
        
        return true;
    }

    // Validar texto requerido
    validateRequired(value, fieldName, minLength = 1) {
        if (!value || value.trim().length < minLength) {
            this.errors[fieldName] = `Este campo es requerido${minLength > 1 ? ` (mínimo ${minLength} caracteres)` : ''}`;
            return false;
        }
        return true;
    }

    // Validar email
    validateEmail(value, fieldName = 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            this.errors[fieldName] = 'El email no es válido';
            return false;
        }
        return true;
    }

    // Validar número de tarjeta (básico)
    validateCardNumber(value, fieldName = 'card_number') {
        const cardNumber = value.replace(/\s/g, '');
        if (cardNumber.length < 13 || cardNumber.length > 19) {
            this.errors[fieldName] = 'El número de tarjeta debe tener entre 13 y 19 dígitos';
            return false;
        }
        if (!/^\d+$/.test(cardNumber)) {
            this.errors[fieldName] = 'El número de tarjeta solo puede contener dígitos';
            return false;
        }
        return true;
    }

    // Validar porcentaje
    validatePercentage(value, fieldName = 'percentage') {
        const percentage = parseFloat(value);
        if (isNaN(percentage) || percentage < 0 || percentage > 100) {
            this.errors[fieldName] = 'El porcentaje debe estar entre 0 y 100';
            return false;
        }
        return true;
    }

    // Validar día del mes
    validateDayOfMonth(value, fieldName = 'day') {
        const day = parseInt(value);
        if (isNaN(day) || day < 1 || day > 31) {
            this.errors[fieldName] = 'El día debe estar entre 1 y 31';
            return false;
        }
        return true;
    }

    // Mostrar errores en el formulario
    showErrors(formId) {
        // Limpiar errores anteriores
        const form = document.getElementById(formId);
        if (!form) return;

        const errorElements = form.querySelectorAll('.invalid-feedback');
        errorElements.forEach(el => el.remove());

        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => input.classList.remove('is-invalid'));

        // Mostrar nuevos errores
        Object.keys(this.errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('is-invalid');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = this.errors[fieldName];
                
                field.parentNode.appendChild(errorDiv);
            }
        });
    }

    // Limpiar errores
    clearErrors() {
        this.errors = {};
    }

    // Verificar si hay errores
    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }

    // Obtener errores
    getErrors() {
        return this.errors;
    }
}

// Función para validar formulario de transacción
function validateTransactionForm(formId) {
    const validator = new FormValidator();
    const form = document.getElementById(formId);
    
    if (!form) return false;

    const formData = new FormData(form);
    
    // Validar monto
    validator.validateAmount(formData.get('amount'));
    
    // Validar fecha
    validator.validateDate(formData.get('transaction_date'), 'transaction_date');
    
    // Validar descripción
    validator.validateRequired(formData.get('description'), 'description', 3);
    
    // Validar tipo
    validator.validateRequired(formData.get('type'), 'type');
    
    // Validar categoría
    validator.validateRequired(formData.get('category_id'), 'category_id');
    
    // Validar método de pago
    validator.validateRequired(formData.get('payment_method'), 'payment_method');
    
    if (validator.hasErrors()) {
        validator.showErrors(formId);
        return false;
    }
    
    return true;
}

// Función para validar formulario de cuenta por pagar
function validateAccountPayableForm(formId) {
    const validator = new FormValidator();
    const form = document.getElementById(formId);
    
    if (!form) return false;

    const formData = new FormData(form);
    
    // Validar nombre del acreedor
    validator.validateRequired(formData.get('creditor_name'), 'creditor_name', 2);
    
    // Validar descripción
    validator.validateRequired(formData.get('description'), 'description', 3);
    
    // Validar monto
    validator.validateAmount(formData.get('total_amount'), 'total_amount');
    
    // Validar fecha de vencimiento
    validator.validateDate(formData.get('due_date'), 'due_date');
    
    if (validator.hasErrors()) {
        validator.showErrors(formId);
        return false;
    }
    
    return true;
}

// Función para validar formulario de cuenta por cobrar
function validateAccountReceivableForm(formId) {
    const validator = new FormValidator();
    const form = document.getElementById(formId);
    
    if (!form) return false;

    const formData = new FormData(form);
    
    // Validar nombre del deudor
    validator.validateRequired(formData.get('debtor_name'), 'debtor_name', 2);
    
    // Validar descripción
    validator.validateRequired(formData.get('description'), 'description', 3);
    
    // Validar monto
    validator.validateAmount(formData.get('total_amount'), 'total_amount');
    
    // Validar fecha de vencimiento
    validator.validateDate(formData.get('due_date'), 'due_date');
    
    if (validator.hasErrors()) {
        validator.showErrors(formId);
        return false;
    }
    
    return true;
}

// Función para validar formulario de tarjeta de crédito
function validateCreditCardForm(formId) {
    const validator = new FormValidator();
    const form = document.getElementById(formId);
    
    if (!form) return false;

    const formData = new FormData(form);
    
    // Validar nombre de la tarjeta
    validator.validateRequired(formData.get('card_name'), 'card_name', 2);
    
    // Validar número de tarjeta
    validator.validateCardNumber(formData.get('card_number'), 'card_number');
    
    // Validar límite de crédito
    validator.validateAmount(formData.get('credit_limit'), 'credit_limit');
    
    // Validar días
    validator.validateDayOfMonth(formData.get('cut_off_date'), 'cut_off_date');
    validator.validateDayOfMonth(formData.get('payment_due_date'), 'payment_due_date');
    
    // Validar porcentaje de pago mínimo
    validator.validatePercentage(formData.get('minimum_payment_percentage'), 'minimum_payment_percentage');
    
    if (validator.hasErrors()) {
        validator.showErrors(formId);
        return false;
    }
    
    return true;
}

// Función para validar formulario de pago
function validatePaymentForm(formId) {
    const validator = new FormValidator();
    const form = document.getElementById(formId);
    
    if (!form) return false;

    const formData = new FormData(form);
    
    // Validar monto
    validator.validateAmount(formData.get('amount'));
    
    // Validar fecha
    validator.validateDate(formData.get('payment_date'), 'payment_date');
    
    // Validar método de pago
    validator.validateRequired(formData.get('payment_method'), 'payment_method');
    
    if (validator.hasErrors()) {
        validator.showErrors(formId);
        return false;
    }
    
    return true;
}

// Función para validar formulario de transacción de tarjeta
function validateCreditCardTransactionForm(formId) {
    const validator = new FormValidator();
    const form = document.getElementById(formId);
    
    if (!form) return false;

    const formData = new FormData(form);
    
    // Validar tipo
    validator.validateRequired(formData.get('type'), 'type');
    
    // Validar monto
    validator.validateAmount(formData.get('amount'));
    
    // Validar descripción
    validator.validateRequired(formData.get('description'), 'description', 3);
    
    // Validar fecha
    validator.validateDate(formData.get('transaction_date'), 'transaction_date');
    
    if (validator.hasErrors()) {
        validator.showErrors(formId);
        return false;
    }
    
    return true;
}

// Función para formatear número de tarjeta mientras se escribe
function formatCardNumber(input) {
    let value = input.value.replace(/\s/g, '');
    let formattedValue = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    input.value = formattedValue;
}

// Función para permitir solo números en un input
function allowOnlyNumbers(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

// Función para permitir solo números decimales
function allowOnlyDecimals(input) {
    input.value = input.value.replace(/[^0-9.]/g, '');
    
    // Permitir solo un punto decimal
    const parts = input.value.split('.');
    if (parts.length > 2) {
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }
}

// Inicializar validaciones cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Agregar validación en tiempo real para campos de monto
    const amountInputs = document.querySelectorAll('input[type="number"][step="0.01"]');
    amountInputs.forEach(input => {
        input.addEventListener('input', function() {
            allowOnlyDecimals(this);
        });
    });

    // Agregar formateo para campos de número de tarjeta
    const cardNumberInputs = document.querySelectorAll('input[name="card_number"]');
    cardNumberInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatCardNumber(this);
        });
    });

    // Agregar validación para campos de día del mes
    const dayInputs = document.querySelectorAll('input[name="cut_off_date"], input[name="payment_due_date"]');
    dayInputs.forEach(input => {
        input.addEventListener('input', function() {
            allowOnlyNumbers(this);
            const value = parseInt(this.value);
            if (value > 31) {
                this.value = '31';
            }
        });
    });
});
