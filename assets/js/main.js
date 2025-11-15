/**
 * CRM-система - Основной JavaScript файл
 */

document.addEventListener('DOMContentLoaded', function() {

    // Автоматическое скрытие уведомлений
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Валидация форм
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Валидация телефона
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            // Разрешаем только цифры, пробелы, тире, скобки и знак +
            let value = e.target.value;
            value = value.replace(/[^\d\s\-\(\)\+]/g, '');
            e.target.value = value;
        });

        input.addEventListener('blur', function(e) {
            const value = e.target.value;
            const phoneRegex = /^\+?[\d\s\-\(\)]{10,20}$/;

            if (value && !phoneRegex.test(value)) {
                e.target.setCustomValidity('Введите корректный номер телефона');
                e.target.classList.add('is-invalid');
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('is-invalid');
            }
        });
    });

    // Валидация email
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function(e) {
            const value = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (value && !emailRegex.test(value)) {
                e.target.setCustomValidity('Введите корректный email адрес');
                e.target.classList.add('is-invalid');
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('is-invalid');
            }
        });
    });

    // Валидация суммы
    const amountInputs = document.querySelectorAll('input[name="amount"]');
    amountInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value;
            // Разрешаем только цифры и точку
            value = value.replace(/[^\d\.]/g, '');
            // Разрешаем только одну точку
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            e.target.value = value;
        });

        input.addEventListener('blur', function(e) {
            const value = parseFloat(e.target.value);
            if (value <= 0 || isNaN(value)) {
                e.target.setCustomValidity('Сумма должна быть больше нуля');
                e.target.classList.add('is-invalid');
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('is-invalid');
            }
        });
    });

    // Подтверждение паролей
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
    confirmPasswordInputs.forEach(function(confirmInput) {
        const newPasswordInput = document.querySelector('input[name="new_password"]');

        if (newPasswordInput) {
            confirmInput.addEventListener('input', function(e) {
                if (e.target.value !== newPasswordInput.value) {
                    e.target.setCustomValidity('Пароли не совпадают');
                    e.target.classList.add('is-invalid');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('is-invalid');
                }
            });
        }
    });

    // Подтверждение удаления
    const deleteLinks = document.querySelectorAll('a[href*="delete.php"]');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этот элемент?')) {
                e.preventDefault();
            }
        });
    });

    // Форматирование чисел с разделителями тысяч
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Применение форматирования к элементам с классом format-number
    const numberElements = document.querySelectorAll('.format-number');
    numberElements.forEach(function(element) {
        const value = parseFloat(element.textContent);
        if (!isNaN(value)) {
            element.textContent = formatNumber(value);
        }
    });

    // Tooltip для элементов с data-bs-toggle="tooltip"
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Поддержка мобильного меню
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Закрытие сайдбара при клике вне его на мобильных устройствах
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');

            if (sidebar && sidebar.classList.contains('show')) {
                if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
                    sidebar.classList.remove('show');
                }
            }
        }
    });

    // Автоматическое обновление года в footer
    const yearElement = document.getElementById('currentYear');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    // Добавление анимации fade-in к карточкам
    const cards = document.querySelectorAll('.card');
    cards.forEach(function(card, index) {
        setTimeout(function() {
            card.classList.add('fade-in');
        }, index * 50);
    });

    // Защита от двойной отправки формы
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обработка...';

                // Повторное включение кнопки через 3 секунды (на случай ошибки)
                setTimeout(function() {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'Отправить';
                }, 3000);
            }
        });

        // Сохранение оригинального текста кнопки
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.setAttribute('data-original-text', submitButton.innerHTML);
        }
    });

    console.log('CRM-система загружена успешно');
});
