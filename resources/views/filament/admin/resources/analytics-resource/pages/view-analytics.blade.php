<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="fi-section-bg rounded-xl shadow-sm border">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $record->title }}</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Analytics Dashboard</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Seminar Date</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->date->format('F j, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Registrations</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getAnalyticsData()['total_registrations'] }}</div>
                    </div>
                </div>
            </div>

            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Checked In</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getAnalyticsData()['total_checked_in'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $this->getAnalyticsData()['check_in_rate'] }}% rate</div>
                    </div>
                </div>
            </div>

            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Checked Out</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getAnalyticsData()['total_checked_out'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $this->getAnalyticsData()['check_out_rate'] }}% rate</div>
                    </div>
                </div>
            </div>

            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacity</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $record->is_open ? 'Unlimited' : $record->capacity }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $record->is_open ? 'Open Seminar' : ($record->isFull() ? 'Full' : 'Available') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Personnel Type Breakdown -->
            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personnel Type Breakdown</h3>
                @if(!empty($this->getAnalyticsData()['personnel_breakdown']))
                    <div class="space-y-3">
                        @foreach($this->getAnalyticsData()['personnel_breakdown'] as $type => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($type) }}</span>
                                <div class="flex items-center">
                                    @php($total = $this->getAnalyticsData()['total_registrations'])
                                    <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                        <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($count / $total) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-white font-semibold">{{ $count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No personnel type data available.</p>
                @endif
            </div>

            <!-- Gender Breakdown -->
            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Gender Distribution</h3>
                @if(!empty($this->getAnalyticsData()['gender_breakdown']))
                    <div class="space-y-3">
                        @foreach($this->getAnalyticsData()['gender_breakdown'] as $gender => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($gender) }}</span>
                                <div class="flex items-center">
                                    @php($total = $this->getAnalyticsData()['total_registrations'])
                                    <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                        <div class="bg-green-600 dark:bg-green-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($count / $total) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-white font-semibold">{{ $count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No gender data available.</p>
                @endif
            </div>

            <!-- Top Schools -->
            <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Participating Schools</h3>
                @if(!empty($this->getAnalyticsData()['top_schools']))
                    <div class="space-y-3">
                        @foreach($this->getAnalyticsData()['top_schools'] as $school => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $school }}</span>
                                <span class="text-sm text-gray-900 dark:text-white font-semibold">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No school/office data available for this seminar.</p>
                @endif
            </div>

            <!-- Daily Attendance (Multi-Day) -->
            @if($this->getAnalyticsData()['is_multi_day'])
                <div class="fi-section-bg rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Attendance</h3>
                    @if(!empty($this->getAnalyticsData()['daily_attendance']))
                        <div class="space-y-3">
                            @foreach($this->getAnalyticsData()['daily_attendance'] as $day)
                                <div class="border-l-4 border-blue-500 dark:border-blue-400 pl-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Day {{ $day['day'] }} - {{ $day['date'] }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Checked In:</span>
                                            <span class="ml-2 font-semibold text-green-600 dark:text-green-400">{{ $day['checked_in'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Checked Out:</span>
                                            <span class="ml-2 font-semibold text-purple-600 dark:text-purple-400">{{ $day['checked_out'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No daily attendance data available.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
