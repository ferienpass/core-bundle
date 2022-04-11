import { Chart } from 'frappe-charts/dist/frappe-charts.esm.js'

export default (values, start) => {
    return {
        chart: null,
        init() {
            this.chart = new Chart(this.$refs.chart, {
                type: "heatmap", colors: ["#ebedf0", "#c0ddf9", "#73b3f3", "#3886e1", "#17459e"],
                data: {
                    dataPoints: values,
                    start: start,
                    end: new Date()
                },
            })
        }
    }
}
