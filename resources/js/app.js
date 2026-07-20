import './bootstrap';

// 1. Importar jQuery
import jQuery from 'jquery';

// 2. Hacerlo global de forma súper compatible
window.$ = jQuery;
window.jQuery = jQuery;
globalThis.$ = jQuery;
globalThis.jQuery = jQuery;

import Swal from 'sweetalert2';
window.Swal = Swal;

function mostrarFlashMessages() {
    const flash = window.__flashMessages || {};
    const validationTitle = flash.validationTitle || 'No se pudo actualizar';
    const errorTitle = flash.errorTitle || 'Operación denegada';
    const successTitle = flash.successTitle || '¡Acción Exitosa!';

    if (flash.validationErrors && flash.validationErrors.length) {
        Swal.fire({
            title: validationTitle,
            html: flash.validationErrors.join('<br>'),
            icon: 'error',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Corregir',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs'
            }
        });
        return;
    }

    if (flash.error) {
        Swal.fire({
            title: errorTitle,
            html: flash.error,
            icon: 'error',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs'
            }
        });
        return;
    }

    if (flash.success) {
        const popup = Swal.fire({
            title: successTitle,
            text: flash.success,
            icon: 'success',
            iconColor: '#84cc16',
            timer: 3000,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-3xl'
            }
        });

        if (flash.redirectTo) {
            popup.then(() => {
                window.location.href = flash.redirectTo;
            });
        }
    }
}

window.mostrarFlashMessages = mostrarFlashMessages;

document.addEventListener('DOMContentLoaded', () => {
    window.setTimeout(() => {
        if (window.Swal) {
            mostrarFlashMessages();
        }
    }, 150);
});

import DataTable from 'datatables.net';
window.DataTable = DataTable;

DataTable.use(jQuery);

import './echo';