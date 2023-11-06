<nav class="bg-emerald-800 px-6 py-2">
    <div class="flex items-center justify-between flex-wrap">
        <div class="flex items-center flex-shrink-0 mr-6 text-lg text-white hover:text-amber-200">
            <i class="fa fa-users mr-6" aria-hidden="true"></i>
            <a href="{{ route('home') }}"
               class="font-semibold">{{ config('app.name', 'Vehikl Growth Sessions') }}</a>
        </div>
        <div class="block lg:hidden">
            <button
                class="flex items-center px-3 py-2 border rounded text-white border-white hover:text-amber-200 hover:border-amber-200"
                onclick="document.getElementById('nav-links').classList.toggle('hidden')"
                @click="isExpanded = !isExpanded">
                <svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <title>
                        Menu
                    </title>
                    <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
                </svg>
            </button>
        </div>
        <div
            class="w-full hidden text-xl uppercase lg:flex justify-end lg:items-center lg:w-auto text-center"
            id="nav-links">
            <div class="text-xl justify-center items-center flex flex-col lg:flex-row">
                @guest
                    <a href="{{route('oauth.login.redirect', ['driver' => 'github'])}}"
                       class="flex items-center mt-4 lg:mt-0 mr-6 text-white hover:text-amber-200 ">
                        <i class="fa fa-github text-3xl mr-4" aria-hidden="true"></i> Login with Github
                    </a>
                    @if(config('services.google.client_id'))
                        <a href="{{route('oauth.login.redirect', ['driver' => 'google'])}}"
                           class="flex items-center mt-4 lg:mt-0 mr-6 text-white hover:text-amber-200 ">
                            <i class="fa fa-google text-3xl mr-4" aria-hidden="true"></i> Login with Google
                        </a>
                    @endif
                @endguest
                @auth
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="mt-4 flex items-center lg:mt-0 text-white hover:text-amber-200 tracking-wider">
                        <v-avatar class="mr-4"
                                  src="{{ auth()->user()->avatar }}"
                                  alt="Your Avatar"
                                  :size="6"></v-avatar>
                        Logout
                    </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @endauth
                <a href="{{route('about')}}"
                   class="mt-4 lg:mt-0 ml-6 text-white hover:text-amber-200 tracking-wider">About
                </a>
            </div>
        </div>
    </div>
</nav>
