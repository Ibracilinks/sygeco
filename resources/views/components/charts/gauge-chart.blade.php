@props([
    'id' => 'gauge-chart-' . uniqid(),
    'value' => 0,
    'min' => 0,
    'max' => 100,
    'title' => null,
    'unit' => '%',
    'size' => 200,
])

@php
    $percentage = (($value - $min) / ($max - $min)) * 100;
    $percentage = max(0, min(100, $percentage));
    $angle = ($percentage / 100) * 180 - 90;
    $color = $percentage >= 80 ? '#10b981' : ($percentage >= 50 ? '#f59e0b' : '#ef4444');
@endphp

<div class="flex flex-col items-center">
    @if ($title)
        <h4 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">{{ $title }}</h4>
    @endif

    <div class="relative" style="width: {{ $size }}px; height: {{ $size / 2 }}px;">
        <canvas id="{{ $id }}" width="{{ $size }}" height="{{ $size / 2 }}"
            style="width: {{ $size }}px; height: {{ $size / 2 }}px;"></canvas>
        <div class="absolute inset-0 flex items-center justify-center" style="top: -{{ $size / 4 }}px;">
            <div class="text-center">
                <span class="text-3xl font-bold dark:text-white">{{ number_format($value, 1) }}</span>
                <span class="text-sm text-zinc-500">{{ $unit }}</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const canvas = document.getElementById('{{ $id }}');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const percentage = {{ $percentage }};
            const angle = (percentage / 100) * Math.PI;
            const color = '{{ $color }}';

            const centerX = canvas.width / 2;
            const centerY = canvas.height;
            const radius = canvas.width / 2 - 10;

            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Background arc
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, -Math.PI / 2, Math.PI / 2);
            ctx.strokeStyle = '#e5e7eb';
            ctx.lineWidth = 20;
            ctx.stroke();

            // Value arc
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, -Math.PI / 2, -Math.PI / 2 + angle);
            ctx.strokeStyle = color;
            ctx.lineWidth = 20;
            ctx.stroke();

            // Center circle
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius - 15, 0, 2 * Math.PI);
            ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#27272a' : '#ffffff';
            ctx.fill();
        })();
    </script>
@endpush
