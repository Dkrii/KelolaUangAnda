import Alpine from 'alpinejs';
import {
    ArcElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement,
    CategoryScale,
    DoughnutController,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

window.Alpine = Alpine;

Alpine.start();

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

function readChartData(elementId) {
    const element = document.getElementById(elementId);

    if (!element) {
        return null;
    }

    try {
        return JSON.parse(element.textContent);
    } catch {
        return null;
    }
}

function initCharts() {
    const cashFlowCanvas = document.getElementById('cashFlowChart');
    const cashFlowData = readChartData('cashFlowChartData');

    if (cashFlowCanvas && cashFlowData) {
        new Chart(cashFlowCanvas, {
            type: 'line',
            data: {
                labels: cashFlowData.labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: cashFlowData.income,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.12)',
                        pointBackgroundColor: '#059669',
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Pengeluaran',
                        data: cashFlowData.expense,
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225, 29, 72, 0.12)',
                        pointBackgroundColor: '#e11d48',
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => rupiah.format(value),
                        },
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${rupiah.format(context.parsed.y)}`,
                        },
                    },
                },
            },
        });
    }

    const categoryCanvas = document.getElementById('expenseCategoryChart');
    const categoryData = readChartData('expenseCategoryChartData');

    if (categoryCanvas && categoryData) {
        new Chart(categoryCanvas, {
            type: 'doughnut',
            data: {
                labels: categoryData.labels,
                datasets: [
                    {
                        data: categoryData.amounts,
                        backgroundColor: categoryData.colors,
                        borderWidth: 0,
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.label}: ${rupiah.format(context.parsed)}`,
                        },
                    },
                },
            },
        });
    }

    const reportCanvas = document.getElementById('reportTrendChart');
    const reportData = readChartData('reportTrendChartData');

    if (reportCanvas && reportData) {
        new Chart(reportCanvas, {
            type: 'line',
            data: {
                labels: reportData.labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: reportData.income,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.12)',
                        pointBackgroundColor: '#059669',
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Pengeluaran',
                        data: reportData.expense,
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225, 29, 72, 0.12)',
                        pointBackgroundColor: '#e11d48',
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => rupiah.format(value),
                        },
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${rupiah.format(context.parsed.y)}`,
                        },
                    },
                },
            },
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
