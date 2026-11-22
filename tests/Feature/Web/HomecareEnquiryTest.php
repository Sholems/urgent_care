<?php

namespace Tests\Feature\Web;

use App\Models\Contact;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomecareEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_homecare_pages_link_to_the_dedicated_website_form(): void
    {
        $this->get(route('homecare.index'))
            ->assertOk()
            ->assertSee(route('homecare.enquiry'));

        $this->get(route('homecare.enquiry'))
            ->assertOk()
            ->assertSee(route('homecare.enquiry.store'))
            ->assertSee('website enquiry privacy notice');
    }

    public function test_valid_enquiry_is_saved_as_a_website_contact(): void
    {
        $this->followingRedirects()
            ->post(route('homecare.enquiry.store'), [
                'name' => 'Website Test',
                'email' => 'homecare@example.test',
                'phone' => '+232 72 555 010',
                'service' => 'nursing',
                'town' => 'Freetown',
                'privacy_acknowledged' => '1',
            ])
            ->assertOk()
            ->assertSee('Your enquiry was received.');

        $contact = Contact::where('email', 'homecare@example.test')->sole();
        $this->assertStringContainsString('Website homecare enquiry', $contact->description);
        $this->assertStringContainsString('Service: Nursing at home', $contact->description);
        $this->assertStringContainsString('Care location: Freetown', $contact->description);
    }

    public function test_doctor_with_incomplete_profile_keeps_website_booking_controls(): void
    {
        $doctor = Doctor::create([
            'name' => 'Dr Preview',
            'photo' => 'photo.jpg',
            'categories' => [],
            'specialities' => [],
            'biography' => 'Lorem ipsum placeholder biography',
            'price' => 100,
        ]);

        $this->get(route('doctor', $doctor))
            ->assertOk()
            ->assertSee(route('appointments.book'))
            ->assertSee('/api/appointments/booked-slots')
            ->assertDontSee('Lorem ipsum placeholder biography');
    }

    public function test_invalid_enquiry_is_not_saved(): void
    {
        $this->from(route('homecare.enquiry'))
            ->post(route('homecare.enquiry.store'), [
                'email' => 'invalid-enquiry@example.test',
                'service' => 'unlisted-service',
            ])
            ->assertRedirect(route('homecare.enquiry'))
            ->assertSessionHasErrors(['name', 'service', 'town', 'privacy_acknowledged']);

        $this->assertDatabaseMissing('contacts', ['email' => 'invalid-enquiry@example.test']);
    }
}
