import Sortable, {MultiDrag} from 'sortablejs';

Sortable.mount(new MultiDrag());

export default () => {

    window.Sortable = Sortable;

    window.assignAttendanceOnAdd = function () {
        return (evt, reload) => {
            fetch(`/admin/api/attendance/${evt.item.dataset.attendanceId}/sort`, {
                method: 'POST',
                body: new URLSearchParams(`newStatus=${evt.to.dataset.attendanceStatus}&newIndex=${evt.newIndex}`)
            }).then(response => {
                if (!response.ok) alert('Ein Fehler ist aufgetreten! Bitte Seite neu laden.');
            });

            if (reload) {
                window.location.reload();
            }
        }
    };

    window.assignAttendanceOnUpdate = function () {
        return (evt, reload) => {
            fetch(`/admin/api/attendance/${evt.item.dataset.attendanceId}/sort`, {
                method: 'POST',
                body: new URLSearchParams(`newIndex=${evt.newIndex}`)
            }).then(response => {
                if (!response.ok) alert('Ein Fehler ist aufgetreten! Bitte Seite neu laden.');
            });


            if (reload) {
                window.location.reload();
            }
        }
    };
}
