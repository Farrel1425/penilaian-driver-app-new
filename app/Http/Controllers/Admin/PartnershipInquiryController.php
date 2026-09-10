<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnershipInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnershipInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', Rule::when($request->filled('start_date'), 'after_or_equal:start_date')],
            'status' => ['nullable', Rule::in(array_keys(PartnershipInquiry::STATUSES))],
        ]);

        $inquiries = PartnershipInquiry::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('service', 'like', "%{$search}%");
                });
            })
            ->when($filters['start_date'] ?? null, fn ($query, string $startDate) => $query->whereDate('created_at', '>=', $startDate))
            ->when($filters['end_date'] ?? null, fn ($query, string $endDate) => $query->whereDate('created_at', '<=', $endDate))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.partnership-inquiries.index', compact('inquiries'));
    }

    public function show(PartnershipInquiry $partnershipInquiry): View
    {
        return view('admin.partnership-inquiries.show', ['inquiry' => $partnershipInquiry]);
    }

    public function updateStatus(Request $request, PartnershipInquiry $partnershipInquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(PartnershipInquiry::STATUSES))],
        ]);

        $partnershipInquiry->update($validated);

        return back()->with('status', 'Status permintaan kerjasama berhasil diperbarui.');
    }
}
