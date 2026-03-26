<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class RecaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Mengirim request ke server Google untuk verifikasi
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $value,
            'remoteip' => request()->ip()
        ]);

        // Jika balasan dari Google adalah false/gagal
        if (! $response->json('success')) {
            $fail('Verifikasi Captcha gagal. Silakan centang ulang "I\'m not a robot".');
        }
    }
}