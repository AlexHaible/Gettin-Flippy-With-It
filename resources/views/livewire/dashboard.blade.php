<div>
    @if(isset($heroBackdrop) && $heroBackdrop)
    <style>
        body::before {
            background-image: url('{{ $heroBackdrop }}') !important;
            background-size: cover !important;
            background-position: center 20% !important;
            background-attachment: fixed !important;
            background-repeat: no-repeat !important;
            filter: brightness(0.2) !important;
        }
    </style>
    @endif

    <div class="container mx-auto px-4 py-8">
        <div class="relative z-10 mb-8 text-center">
            <div class="deco-divider w-56 mx-auto mb-4"></div>
            <h1 class="text-4xl font-display font-bold text-gold-500 text-shadow-gold uppercase tracking-[0.2em] mb-2">
                Dashboard</h1>
            <a href="{{ route('wrapped', ['year' => now()->year]) }}" class="inline-block text-gold-400 hover:text-gold-200 text-sm tracking-widest uppercase border border-gold-600/50 hover:border-gold-400 px-4 py-1 rounded-full transition-all bg-noir-900/50 backdrop-blur-sm">
                🍿 View {{ now()->year }} Wrapped
            </a>
            <div class="deco-divider w-56 mx-auto mt-4"></div>
        </div>

        @if(isset($upcomingShowings) && $upcomingShowings->isNotEmpty())
        <!-- Upcoming Movie Night -->
        <div class="mb-8">
            <div class="bg-noir-900/90 deco-border-metallic border-2 border-gold-400 rounded-lg p-6 shadow-[0_0_20px_var(--color-gold-500)] relative overflow-hidden">
                <!-- Shimmer effect -->
                <div class="absolute top-0 -inset-full h-full w-1/2 z-5 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-10 animate-shimmer"></div>

                <h2 class="text-2xl font-bold font-display text-gold-500 mb-4 tracking-wider flex items-center">
                    <svg class="w-6 h-6 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Upcoming Movie Night
                </h2>

                @php $nextShowing = $upcomingShowings->first(); @endphp
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left space-y-4 sm:space-y-0 sm:space-x-6 mb-4 md:mb-0">
                        @if($nextShowing->movie->poster_path)
                        <img src="https://image.tmdb.org/t/p/w200{{ $nextShowing->movie->poster_path }}" alt="Poster" class="w-24 sm:w-20 h-36 sm:h-32 object-cover rounded shadow-md border border-gold-800">
                        @endif
                        <div>
                            <h3 class="text-2xl sm:text-3xl font-bold font-display text-gold-100 leading-tight">{{ $nextShowing->movie->title }}</h3>
                            <p class="text-gold-500 text-base sm:text-lg mt-1">{{ $nextShowing->cinema->name }} • {{ $nextShowing->hall_name ?? 'TBA' }}</p>
                            <p class="text-gold-400 font-mono mt-2 text-sm">{{ $nextShowing->start_time->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="bg-noir-950 p-4 rounded border border-gold-900/50 text-center flex-shrink-0">
                        <p class="text-[10px] text-gold-600 uppercase tracking-widest mb-1">Snack Payer</p>
                        <p class="text-lg font-bold text-gold-400">{{ $currentPayer->username ?? 'No One Set' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Movies -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">Total Movies</h3>
                <p class="text-3xl font-bold font-display text-gold-500 text-shadow-gold">{{ $totalShowings }}</p>
                <p class="text-gold-700 text-xs mt-1">{{ $totalMovies }} Unique Titles</p>
            </div>

            <!-- Total Spend -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">Total Spend</h3>
                <p class="text-3xl font-bold font-display text-gold-400">{{ number_format($totalSpent, 0, ',', '.') }}
                    kr.</p>
                <p class="text-gold-700 text-xs mt-1">Tickets & Snacks</p>
            </div>

            <!-- Total Time -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">Total Time</h3>
                <p class="text-3xl font-bold font-display text-gold-400">{{ number_format($totalHours, 1, ',', '.') }}
                    hrs</p>
                <p class="text-gold-700 text-xs mt-1">In Theater</p>
            </div>

            <!-- Avg Cost / Movie -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">Avg. Cost / Movie</h3>
                <p class="text-3xl font-bold font-display text-gold-400">{{ number_format($averageCost, 0, ',', '.') }}
                    kr.</p>
                <p class="text-gold-700 text-xs mt-1">Per showing</p>
            </div>

            <!-- Cost / Hour -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">Cost / Hour</h3>
                <p class="text-3xl font-bold font-display text-gold-400">{{ number_format($costPerHour, 0, ',', '.') }}
                    kr.</p>
                <p class="text-gold-700 text-xs mt-1">Price of entertainment</p>
            </div>

            <!-- Top Cinema -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">Top Cinema</h3>
                @if($cinemaDistribution->isNotEmpty())
                <p class="text-xl font-bold font-display text-gold-500 truncate"
                    title="{{ $cinemaDistribution->first()->cinema->name }}">{{
                    $cinemaDistribution->first()->cinema->name }}</p>
                <p class="text-gold-700 text-xs mt-1">{{ $cinemaDistribution->first()->total }} visits</p>
                @else
                <p class="text-xl text-gold-800">No data</p>
                @endif
            </div>

            <!-- Top Genre -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <a href="{{ route('genre', ['name' => $topGenre]) }}" class="block hover:opacity-80 transition">
                    <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2 hover:text-gold-400">Top Genre</h3>
                    <p class="text-xl font-bold font-display text-gold-500 truncate" title="{{ $topGenre }}">{{ $topGenre }}</p>
                    <p class="text-gold-700 text-xs mt-1">{{ $topGenreCount }} movies</p>
                </a>
            </div>

            <!-- Top Actor -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <a href="{{ route('actor', ['name' => $topActor]) }}" class="block hover:opacity-80 transition">
                    <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2 hover:text-gold-400">Top Actor</h3>
                    <p class="text-xl font-bold font-display text-gold-500 truncate" title="{{ $topActor }}">{{ $topActor }}</p>
                    <p class="text-gold-700 text-xs mt-1">{{ $topActorCount }} movies</p>
                </a>
            </div>

            <!-- The Pace -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h3 class="text-gold-600 text-sm font-uppercase tracking-widest mb-2">The Pace (Projection)</h3>
                <p class="text-xl font-bold font-display text-gold-500 truncate">{{ $projectedMovies }} movies</p>
                <p class="text-gold-700 text-xs mt-1">{{ number_format($projectedSpend, 0, ',', '.') }} kr. by EOY</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Payer Breakdown -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h2 class="text-xl font-bold font-display text-gold-500 mb-4 tracking-wider">Payer Breakdown</h2>
                <div class="space-y-4">
                    @foreach($payerStats as $payer)
                    <div class="flex items-center justify-between border-b border-gold-900/30 pb-2 last:border-0">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 rounded-full bg-gold-600 flex items-center justify-center text-noir-900 font-bold text-sm shadow-md shadow-gold-500/50">
                                {{ substr($payer->user->username ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-gold-100 font-medium">{{ $payer->user->username ?? 'Unknown' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gold-400 font-bold">{{ number_format($payer->total_spent, 0, ',', '.') }}
                                kr.</p>
                            <div class="w-24 bg-noir-800 rounded-full h-1.5 mt-1 ml-auto border border-gold-900">
                                <div class="bg-gold-500 h-1.5 rounded-full shadow-[0_0_5px_var(--color-gold-500)]"
                                    style="width: {{ ($totalSpent > 0) ? ($payer->total_spent / $totalSpent) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Weekly Habits -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h2 class="text-xl font-bold font-display text-gold-500 mb-4 tracking-wider">Weekly Habits</h2>
                <div class="h-48 flex items-end space-x-2">
                    @php $maxDay = $dayOfWeekStats->max() ?: 1; @endphp
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <div class="flex-1 flex flex-col items-center justify-end h-full group">
                        <div class="w-full bg-gold-900/50 rounded-t-sm relative transition-all duration-300 hover:bg-gold-600 border border-gold-800"
                            style="height: {{ isset($dayOfWeekStats[$day]) ? ($dayOfWeekStats[$day] / $maxDay) * 100 : 0 }}%">
                            <span
                                class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs text-gold-100 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ $dayOfWeekStats[$day] ?? 0 }}
                            </span>
                        </div>
                        <span class="text-xs text-gold-700 mt-2 font-display">{{ substr($day, 0, 3) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Showings -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <a href="{{ route('showings') }}" class="group flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold font-display text-gold-500 tracking-wider group-hover:text-gold-300 transition-colors">Recent Showings</h2>
                    <span class="text-gold-600 group-hover:text-gold-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>
                <div class="space-y-4">
                    @forelse($recentShowings as $showing)
                    <div
                        class="flex justify-between items-center border-b border-gold-900/30 pb-4 last:border-0 last:pb-0">
                        <div>
                            <h4 class="text-lg font-semibold text-gold-100 font-display">{{ $showing->movie->title }}
                            </h4>
                            <p class="text-gold-600 text-sm">{{ $showing->cinema->name }} • {{ $showing->hall_name ??
                                'N/A' }}</p>
                            <p class="text-gold-700 text-xs">{{ $showing->start_time->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-gold-400 font-bold">{{ number_format($showing->price_total, 0, ',',
                                '.') }} kr.</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-gold-700 italic">No showings found.</p>
                    @endforelse
                </div>
            </div>

            <!-- Cinema Distribution -->
            <div class="bg-noir-900/90 deco-border-metallic border-2 rounded-lg p-6 shadow-lg shadow-gold-900/20">
                <h2 class="text-xl font-bold font-display text-gold-500 mb-4 tracking-wider">Cinema Distribution</h2>
                <div class="space-y-3">
                    @foreach($cinemaDistribution as $dist)
                    <div class="flex items-center">
                        <div class="w-full">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gold-100">{{ $dist->cinema->name }}</span>
                                <span class="text-sm font-medium text-gold-400">{{ $dist->total }}</span>
                            </div>
                            <div class="w-full bg-noir-800 rounded-full h-2.5 border border-gold-900">
                                <div class="bg-gold-600 h-2.5 rounded-full shadow-[0_0_5px_var(--color-gold-600)]"
                                    style="width: {{ ($dist->total / $totalShowings) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>


    </div>
</div>