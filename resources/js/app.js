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

import DataTable from 'datatables.net';
window.DataTable = DataTable;

DataTable.use(jQuery);