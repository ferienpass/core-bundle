import 'alpinejs'
import Gantt from 'frappe-gantt';
import { Chart } from 'frappe-charts/dist/frappe-charts.esm.js'

window.Gantt = Gantt;
window.Chart = Chart;

import dragParticipants from './_drag-participants';

dragParticipants();
