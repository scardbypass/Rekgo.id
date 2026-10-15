<?php
namespace App\Http\Controllers;

use App\Services\StaticQris;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

final class GoPayLiveTestController
{
    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->user()?->role === 'super_admin', 403);
    }

    public function show(Request $request, StaticQris $qris)
    {
        $this->authorizeOwner($request);
        $ready = true;
        try { $qris->payload(); } catch (\Throwable $e) { $ready = false; }
        return view('admin-gopay-live-test', ['ready' => $ready]);
    }

    public function image(Request $request, StaticQris $qris)
    {
        $this->authorizeOwner($request);
        try { $payload = $qris->payload(); }
        catch (\Throwable $e) { abort(404, 'QRIS merchant belum tersedia.'); }
        $svg = (new SvgWriter())->write(new QrCode(data: $payload, size: 360, margin: 16))->getString();
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
