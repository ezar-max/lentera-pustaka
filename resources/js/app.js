import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { LenteraPopup, initGlobalPopupListeners } from './popup';

window.Alpine = Alpine;
window.Chart = Chart;
window.LenteraPopup = LenteraPopup;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initGlobalPopupListeners();

    const flashEl = document.getElementById('lentera-flash-data');
    if (flashEl) {
        const success = flashEl.getAttribute('data-success');
        const error = flashEl.getAttribute('data-error');
        const warning = flashEl.getAttribute('data-warning');
        const validationError = flashEl.getAttribute('data-validation');

        if (success) {
            LenteraPopup.toast('success', success);
        } else if (error) {
            LenteraPopup.alert({
                judul: 'Pemberitahuan',
                pesan: error,
                tipe: 'error',
            });
        } else if (warning) {
            LenteraPopup.alert({
                judul: 'Perhatian',
                pesan: warning,
                tipe: 'warning',
            });
        } else if (validationError) {
            LenteraPopup.toast('error', validationError);
        }
    }
});
