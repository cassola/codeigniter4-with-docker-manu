<?php

namespace App\Controllers;

class Language extends BaseController
{
    public function set(string $locale)
    {
        $supported = ['es', 'pt', 'en', 'it'];
        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        session()->set('locale', $locale);

        $redirect = (string) $this->request->getGet('redirect');
        if ($redirect !== '') {
            return redirect()->to($redirect);
        }

        return redirect()->back();
    }
}
