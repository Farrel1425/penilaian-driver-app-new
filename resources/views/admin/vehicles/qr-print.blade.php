<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print QR {{ $vehicle->police_number }}</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #172033; }
        body { margin: 0; background: #f4f8fc; }
        .print-sheet { display: grid; min-height: 100vh; place-items: center; padding: 24px; }
        .qr-print-card { width: min(100%, 420px); border: 1px solid #dbe7f3; border-radius: 8px; background: #fff; padding: 26px; text-align: center; }
        .qr-print-card.is-label { width: min(100%, 260px); padding: 18px; }
        .qr-print-card.is-label h1 { font-size: 20px; }
        .qr-print-card.is-label p { font-size: 13px; }
        .brand { color: #0668d8; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        h1 { margin: 8px 0 4px; font-size: 26px; }
        p { margin: 0 0 18px; color: #697891; }
        img { width: 320px; max-width: 100%; height: auto; }
        code { display: block; margin-top: 14px; overflow-wrap: anywhere; color: #697891; }
        .actions { margin-top: 20px; }
        button { border: 0; border-radius: 8px; background: #0668d8; color: #fff; font-weight: 800; padding: 12px 18px; }
        @media print { body { background: #fff; } .actions { display: none; } .print-sheet { min-height: auto; } }
    </style>
</head>
<body>
    <main class="print-sheet">
        <section @class(['qr-print-card', 'is-label' => $printFormat === 'label'])>
            <div class="brand">Penilaian Driver & Kendaraan</div>
            <h1>{{ $vehicle->police_number }}</h1>
            <p>{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->branch?->name }}</p>
            <img src="{{ $qrDataUri }}" alt="QR {{ $vehicle->police_number }}">
            <div class="actions"><button type="button" onclick="window.print()">Print QR</button></div>
        </section>
    </main>
</body>
</html>
