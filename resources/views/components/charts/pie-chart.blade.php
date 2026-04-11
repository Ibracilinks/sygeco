@props([
    'id' => 'pie-chart-' . uniqid(),
    'labels' => [],
    'data' => [],
    'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
    'type' => 'pie', // pie or donut
    'height' => 300,
])

@php
    $chartId = 'pie-chart-' . uniqid();
@endphp

<div>
    <canvas id="{{ $chartId }}" style="height: {{ $height }}px; width: 100%;"></canvas>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('{{ $chartId }}').getContext('2d');

            new Chart(ctx, {
                type: '{{ $type === 'donut' ? 'doughnut' : 'pie' }}',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        data: @json($data),
                        backgroundColor: @json($colors),
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' :
                                    '#1f2937',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + new Intl.NumberFormat('fr-FR').format(value) +
                                        ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '{{ $type === 'donut' ? '60%' : '0%' }}'
                }
            });
        });
    </script>
@endpush
