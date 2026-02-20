@php
    $days = $seminar->days()->orderBy('day_number')->get();
@endphp

<div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700" role="region" aria-label="Attendance by day table">
    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm" style="table-layout: fixed;">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 18%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 53%;">
        </colgroup>
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Day</th>
                <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Date</th>
                <th scope="col" class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300">Checked In</th>
                <th scope="col" class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300">Checked Out</th>
                <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Venue</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
            @foreach($days as $day)
                @php
                    $checkedInCount = $day->checkIns()->whereNotNull('checked_in_at')->count();
                    $checkedOutCount = $day->checkIns()->whereNotNull('checked_out_at')->count();
                @endphp
                <tr>
                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100 font-medium">Day {{ $day->day_number }}</td>
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $day->formatted_date }}</td>
                    <td class="px-3 py-2 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            {{ $checkedInCount }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                            {{ $checkedOutCount }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400 break-words" style="word-wrap: break-word;">{{ $day->venue ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
