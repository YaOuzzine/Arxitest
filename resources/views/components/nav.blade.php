{{-- resources/views/components/nav.blade.php --}}
<nav class="bg-white border rounded-full shadow-sm mx-4 mt-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo.svg') }}" alt="Arxitest" class="h-8 w-auto">
                    </a>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <a href="{{ route('overview') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 {{ request()->routeIs('overview') ? 'text-gray-900' : '' }}">
                    Overview
                </a>
                <a href="{{ route('pricing') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 {{ request()->routeIs('pricing') ? 'text-gray-900' : '' }}">
                    Pricing
                </a>
                <a href="{{ route('privacy') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 {{ request()->routeIs('privacy') ? 'text-gray-900' : '' }}">
                    Privacy and terms
                </a>
                <a href="{{ route('faq') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 {{ request()->routeIs('faq') ? 'text-gray-900' : '' }}">
                    FAQ
                </a>

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                            Sign out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 {{ request()->routeIs('login') ? 'text-gray-900' : '' }}">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Get started
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
