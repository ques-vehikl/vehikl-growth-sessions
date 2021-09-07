@extends('layouts.app')

@section('content')
    @guest
        <p role="alert" class="block text-center text-orange-600 text-2xl my-10">To join/create growth session or see
            their location, you must
            <strong><a href="{{route('oauth.login.redirect', ['driver' => 'github'])}}"
                       class="underline hover:text-orange-400">log in with Github</a></strong>
            @if(config('services.google.client_id'))
                or
                <strong><a href="{{route('oauth.login.redirect', ['driver' => 'google'])}}"
                           class="underline hover:text-orange-400">log in with Google</a></strong>
            @endif
            !
        </p>
    @endguest
    <growth-session-card-new></growth-session-card-new>
    <week-view class="mt-6" :user="{{ json_encode(auth()->user()) }}"></week-view>

    <p class="text-lg mx-2 text-blue-700 font-bold mt-4">Have suggestions for this app? </p>
    <a class="inline-block bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white font-bold py-2 px-4 rounded mx-2 mt-2"
       target="_blank"
       href="https://github.com/FRFlor/social-mob/issues">
        Please, share them here!
    </a>

    @include('partials.about')
@endsection

