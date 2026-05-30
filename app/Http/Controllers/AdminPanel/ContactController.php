<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        Contact::create([
            'user_id' => $request->user()?->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Your message has been sent successfully.');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $contacts = Contact::query()
            ->with('user:id,name,email')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (Contact $contact) {
                return [
                    'id' => $contact->id,
                    'user_id' => $contact->user_id,
                    'full_name' => $contact->full_name,
                    'email' => $contact->email,
                    'subject' => $contact->subject,
                    'message' => $contact->message,
                    'created_at' => optional($contact->created_at)->format('M d, Y h:i A'),
                    'user_name' => $contact->user?->name,
                ];
            });

        return Inertia::render('Admin/Contact/Index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
