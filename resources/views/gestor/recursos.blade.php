@extends('layouts.gestor')

@section('content')
    <div class="p-4 md:p-8 bg-slate-50 min-h-screen">
        <div class="flex justify-between items-center mb-6 md:mb-10 border-b border-slate-200 pb-4 md:pb-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-secondary mb-2 flex items-center gap-2 md:gap-3">
                    <span class="material-symbols-outlined text-3xl md:text-4xl text-primary">library_books</span>
                    Recursos
                </h2>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl md:rounded-3xl shadow-xl border border-slate-200 overflow-hidden w-full h-[450px] sm:h-[600px] lg:h-[800px] relative">
            <iframe src="https://anyflip.com/bookcase/ghert" class="absolute top-0 left-0 w-full h-full border-0"
                allowfullscreen="true" scrolling="no">
            </iframe>
        </div>

    </div>
@endsection

