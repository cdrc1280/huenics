@php
    $logoPath = public_path('images/huenics-logo.png');
    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : asset('images/huenics-logo.png');
@endphp
<img src="{{ $logoSrc }}" alt="Huenics Industrial Sales, Inc." style="{{ $style ?? 'max-height: 38px; width: auto; display: block;' }}">
