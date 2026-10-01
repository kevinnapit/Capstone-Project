import './bootstrap';
import 'flowbite';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const readChartData = (id) => {
    const element = document.getElementById(id);
    return element ? JSON.parse(element.textContent) : null;
};

const rupiah = (value) => `Rp ${Number(value).toLocaleString('id-ID')}`;

const initDashboardCharts = async () => {
    if (!document.getElementById('order-status-chart')) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');
    const salesElement = document.getElementById('sales-trend-chart');
    const salesData = readChartData('sales-trend-data');
    if (salesElement && salesData) {
        new ApexCharts(salesElement, {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            series: [{ name: 'Penjualan', data: salesData.series }],
            xaxis: { categories: salesData.labels, labels: { style: { colors: '#6b7280' } } },
            yaxis: { labels: { formatter: rupiah, style: { colors: '#6b7280' } } },
            colors: ['#1d4ed8'],
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            tooltip: { y: { formatter: rupiah } },
        }).render();
    }

    const statusElement = document.getElementById('order-status-chart');
    const statusData = readChartData('order-status-data');
    if (statusElement && statusData) {
        new ApexCharts(statusElement, {
            chart: { type: 'donut', height: 300, fontFamily: 'Figtree, sans-serif' },
            series: statusData.series,
            labels: statusData.labels,
            colors: ['#9ca3af', '#2563eb', '#f59e0b', '#16a34a', '#dc2626'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
        }).render();
    }

    const compositionElement = document.getElementById('sales-composition-chart');
    const compositionData = readChartData('sales-composition-data');
    if (compositionElement && compositionData) {
        new ApexCharts(compositionElement, {
            chart: { type: 'bar', height: 285, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            series: [{ name: 'Nilai penjualan', data: compositionData.series }],
            xaxis: { categories: compositionData.labels },
            yaxis: { labels: { formatter: rupiah } },
            colors: ['#0891b2'],
            plotOptions: { bar: { borderRadius: 7, columnWidth: '45%', distributed: true } },
            dataLabels: { enabled: false },
            legend: { show: false },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            tooltip: { y: { formatter: rupiah } },
        }).render();
    }
};

document.addEventListener('DOMContentLoaded', initDashboardCharts);
