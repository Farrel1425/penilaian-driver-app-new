<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22mm; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, Arial, sans-serif; }
        .print-sheet { width: 100%; padding-top: 18mm; text-align: center; }
        .qr-print-card { width: 420px; margin: 0 auto; padding: 26px; border: 1px solid #dbe7f3; border-radius: 8px; background: #ffffff; text-align: center; }
        .brand { color: #00853f; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        h1 { margin: 8px 0 4px; font-size: 26px; }
        p { margin: 0 0 18px; color: #697891; font-size: 14px; }
        img { width: 320px; height: 320px; }
    </style>
</head>
<body>
    <main class="print-sheet">
        <section class="qr-print-card">
            <div class="brand">Penilaian Driver &amp; Kendaraan</div>
            <h1>{{ $vehicle->police_number }}</h1>
            <p>{{ $vehicle->brand }} {{ $vehicle->model }} - {{ $vehicle->branch?->name }}</p>
            <img src="{{ $qrDataUri }}" alt="QR {{ $vehicle->police_number }}">
        </section>
    </main>
</body>
</html>
