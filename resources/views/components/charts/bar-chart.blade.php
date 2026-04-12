@props([
    'id' => 'bar-chart-' . uniqid(),
    'labels' => [],
    'datasets' => [],
    'title' => null,
    'height' => 300,
    'stacked' => false,
])

@php
    $chartId = 'bar-chart-' . uniqid();
@endphp

<div>
    <canvas id="{{ $chartId }}" style="height: {{ $height }}px; width: 100%;"></canvas>
</div>

@push('scripts')
    <script>
        (function() {
            function initChart() {
                const ctx = document.getElementById('{{ $chartId }}')?.getContext('2d');
                if (!ctx) {
                    return;
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($labels),
                        datasets: @json($datasets)
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: document.documentElement.classList.contains('dark') ? '#e5e7eb' :
                                        '#1f2937'
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        let value = context.raw;
                                        if (typeof value === 'number') {
                                            value = new Intl.NumberFormat('fr-FR').format(value);
                                        }
                                        return label + ': ' + value;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' :
                                        '#e5e7eb'
                                },
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' :
                                        '#6b7280'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' :
                                        '#6b7280'
                                }
                            }
                        }
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChart);
            } else {
                initChart();
            }
        })();
    </script>
@endpush
