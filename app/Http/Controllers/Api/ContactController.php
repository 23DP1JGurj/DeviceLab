<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?\d+$/'],
            'message' => ['required', 'string', 'max:3000'],
        ], [
            'phone.regex' => 'Tālruņa numurā drīkst būt tikai cipari un sākumā simbols +.',
        ]);

        $message = ContactMessage::create($data);

        try {
            Mail::to(config('mail.contact_to'))->send(new ContactMessageMail($message));
        } catch (Throwable $exception) {
            Log::warning('Contact message email failed.', [
                'contact_message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Ziņa nosūtīta. Mēs ar jums sazināsimies.',
        ], 201);
    }
}
