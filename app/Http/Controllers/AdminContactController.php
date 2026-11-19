<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Support\ContactPhoneFormatter;
use Illuminate\Http\JsonResponse;

class AdminContactController extends Controller
{
    public function show(): JsonResponse
    {
        $siteSetting = SiteSetting::first();

        $contactEmail = $siteSetting?->contact_email
            ?? $siteSetting?->footer_email
            ?? 'info@lizelsundria.com';

        return response()->json([
            'contact_phone' => ContactPhoneFormatter::resolve($siteSetting),
            'contact_email' => $contactEmail,
        ]);
    }
}
