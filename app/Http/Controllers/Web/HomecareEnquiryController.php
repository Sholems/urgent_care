<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreHomecareEnquiryRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;

class HomecareEnquiryController extends Controller
{
    public function store(StoreHomecareEnquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $service = config('homecare.services')[$data['service']];

        Contact::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'description' => implode(PHP_EOL, [
                'Website homecare enquiry',
                'Service: ' . $service,
                'Care location: ' . $data['town'],
                'Preferred start: ' . ($data['preferred_start'] ?? 'Flexible'),
                'Privacy notice acknowledged',
            ]),
        ]);

        return redirect()
            ->route('homecare.enquiry')
            ->with('homecare_received', true);
    }
}
