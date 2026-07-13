<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationTemplateController extends Controller
{
    public function index(): View
    {
        $templates = NotificationTemplate::query()
            ->orderBy('channel')
            ->orderBy('type')
            ->get()
            ->groupBy('channel');

        return view('templates.index', compact('templates'));
    }

    public function update(Request $request, NotificationTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $template->update($data);

        return redirect()->route('templates.index')
            ->with('success', 'Şablon güncellendi.');
    }
}
