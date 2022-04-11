import Alpine from 'alpinejs'
import DashboardGantt from './components/dashboard/gantt'
import DashboardHeatmap from './components/dashboard/heatmap'
import DragParticipants from './components/drag-participants';

window.Alpine = Alpine

Alpine.data('DashboardGantt', DashboardGantt)
Alpine.data('DashboardHeatmap', DashboardHeatmap)
Alpine.data('DragParticipants', DragParticipants)
Alpine.start()
