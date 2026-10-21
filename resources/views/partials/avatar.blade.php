{{-- ইউজারের ছবি (আপলোড/গুগল) অথবা নামের প্রথম অক্ষরের গ্রেডিয়েন্ট বৃত্ত। ব্যবহার: @include('partials.avatar', ['user' => $u, 'size' => 'w-10 h-10', 'text' => 'text-base']) --}}
@php $avatarUrl = $user->avatarUrl(); @endphp
@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" referrerpolicy="no-referrer" loading="lazy"
         class="{{ $size ?? 'w-10 h-10' }} rounded-full object-cover shrink-0 ring-2 ring-white shadow">
@else
    <div class="{{ $size ?? 'w-10 h-10' }} {{ $text ?? 'text-base' }} rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 text-white grid place-items-center font-bold shrink-0 ring-2 ring-white shadow">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
@endif
