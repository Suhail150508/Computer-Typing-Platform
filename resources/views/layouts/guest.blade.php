@extends('layouts.base')

@section('body')
    @include('partials.flash')
    <main class="min-h-screen grid place-items-center px-4 py-10">
        <div class="w-full max-w-md anim-fade-up">
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-2 bg-white rounded-full px-5 py-2 shadow-md border border-blue-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                    <span class="font-bold tracking-wide text-brand-700" style="font-family:'Poppins',sans-serif">TYPING MOJA</span>
                </div>
                <p class="mt-3 text-slate-500 text-sm">বাংলা ও ইংরেজি টাইপিং প্র্যাকটিস ও পরীক্ষা প্ল্যাটফর্ম</p>
            </div>
            <div class="bg-white/95 backdrop-blur rounded-3xl border border-blue-200/70 shadow-xl shadow-blue-200/40 p-6 sm:p-8">
                @yield('content')
            </div>
        </div>
    </main>
@endsection
