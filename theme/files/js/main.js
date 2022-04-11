import Alpine from 'alpinejs'
import Gantt from 'frappe-gantt';
import { Chart } from 'frappe-charts/dist/frappe-charts.esm.js'
import dragParticipants from './_drag-participants';

window.Alpine = Alpine
window.Gantt = Gantt;
window.Chart = Chart;

Alpine.start()

dragParticipants();
