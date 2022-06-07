import Sortable, {MultiDrag} from 'sortablejs';

Sortable.mount(new MultiDrag());

export default (count, autoAssign) => {
    return {
        count: count,
        autoAssign: autoAssign,
        init() {
            Sortable.create(this.$refs.list, {
                group: 'assign',
                ghostClass: 'bg-yellow-50',
                selectedClass: 'bg-blue-50',
                multiDrag: true,

                onAdd: this.assignAttendanceOnAdd(event, autoAssign),
                onUpdate: this.assignAttendanceOnUpdate(event, autoAssign),
                onSort: () => this.count = this.$root.querySelectorAll('ul > li').length
            })
        },
        assignAttendanceOnAdd() {
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
        },
        assignAttendanceOnUpdate() {
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
        }
    }
}
