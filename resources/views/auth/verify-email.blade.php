<x-guest-layout>

<div class="form-card rounded-2xl p-8 text-center">

    <!-- Icon -->
    <div class="w-16 h-16 rounded-2xl hero-gradient flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary-600/25">
        <i data-lucide="mail-check" class="w-8 h-8 text-white"></i>
    </div>

    <!-- Header -->
    <h1 class="text-2xl font-bold text-gray-900 mb-2">E-postanı Doğrula</h1>
    <p class="text-sm text-gray-500 leading-relaxed mb-6">
        Hesabını aktifleştirmek için e-posta adresine gönderilen doğrulama linkine tıkla.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-3 rounded-xl text-sm font-medium bg-green-50 border border-green-200 text-green-700">
            Yeni doğrulama linki e-posta adresine gönderildi.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
        @csrf
        <button type="submit"
                class="btn-primary w-full py-3 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-4 h-4"></i>
            Doğrulama Linkini Tekrar Gönder
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
            Çıkış Yap
        </button>
    </form>

</div>

</x-guest-layout>
