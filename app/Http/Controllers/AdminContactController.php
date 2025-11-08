<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class AdminContactController extends Controller
{
    public function show(): JsonResponse
    {
        $siteSetting = SiteSetting::first();

        $contactPhone = $siteSetting?->contact_phone
            ?? $siteSetting?->footer_phone
            ?? '+1 (240) 476-9840';

        $contactEmail = $siteSetting?->contact_email
            ?? $siteSetting?->footer_email
            ?? 'info@lizelsundria.com';

        return response()->json([
            'contact_phone' => $contactPhone,
            'contact_email' => $contactEmail,
        ]);
    }
}
