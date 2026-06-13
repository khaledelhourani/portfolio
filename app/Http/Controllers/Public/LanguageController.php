<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): \Symfony\Component\HttpFoundation\Response
    {
        if (in_array($locale, ['ar', 'en'], true)) {
            $request->session()->put('locale', $locale);
        }

        // AJAX (live toggle) just needs the session updated.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->noContent();
        }

        return back();
    }
}
