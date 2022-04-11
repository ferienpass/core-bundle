import Gantt from 'frappe-gantt';

export default (tasks) => {
    return {
        gantt: null,
        viewMode: 'Month',
        init() {
            this.gantt = new Gantt(this.$refs.gantt, tasks, {
                view_mode: this.viewMode, custom_popup_html: function (task) {
                    return `
<div class="px-4 py-2">
<h5 class="text-white text-sm font-medium whitespace-nowrap">${task.name}
<a class="text-white font-normal text-xs underline" href="${task.editLink.href}" title="${task.editLink.title}">${task.editLink.link}</a>
</h5>
<p class="mt-1 text-gray-100 leading-4 text-sm">${task.description}</p>
</div>
`;
                }
            });

            this.$watch('viewMode', value => gantt.change_view_mode(value))
        }
    }
}
