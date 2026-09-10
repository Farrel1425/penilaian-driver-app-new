<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnershipInquiryRequest;
use App\Models\PartnershipInquiry;
use Illuminate\Http\RedirectResponse;

class PartnershipInquiryController extends Controller
{
    public function store(StorePartnershipInquiryRequest $request): RedirectResponse
    {
        PartnershipInquiry::query()->create($request->validated());

        return redirect()->to(route('home').'#hubungi')
            ->with('inquiry_status', 'Permintaan berhasil dikirim. Tim PT. BDS akan segera menghubungi Anda.');
    }
}
