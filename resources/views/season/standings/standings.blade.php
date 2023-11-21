<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
  <title>Season Standings</title>
</head>

    <x-app-layout class="flex flex-col min-h-screen">

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">

          </main>
          <div class="flex-1 p-4 sm:w-64">
            <div class="flex items-center">
                <a href="/league/{{ $league->leagueId }}" class="text-blue-500 underline p-2"> League </a>
                <p> > </p>
                <a href="/season/{{ $seasonId }}" class="text-blue-500 underline p-2"> Season </a>
            </div>
            <div class="p-4 bg-white rounded-xl items-center justify-content-between">
                <div class="flex flex-row items-center">
                    <div class="flex flex-1 items-center justify-center">
                        <h1 class="text-4xl font-bold text-center"> Standings </h1>
                    </div>
                </div>
            </div>
            <div class="p-4 text-center">

            </div>

            <div class="p-5 flex justify-center items-center">
                    <div class="bg-gray-300 py-4 px-8 rounded-3xl">
                        <div class="flex flex-row p-4 rounded-xl justify-center items-center">
                                <tr class="m-2">
                                  <table class="pl-2 text-center" id="racers_table">
                                    <thead>
                                      <tr>
                                        <th class="px-4">Pos</th>
                                        <th class="px-6">Season Points</th>
                                        <th class="px-10">Driver</th>
                                        <th class="px-10">Laps</th>
                                        <th class="px-4">Laps lead</th>
                                        <th class="px-4">Incidents</th>
                                        <th class="px-2">Races</th>
                                        <th class="px-4">Wins</th>
                                        @if ($dropWeeksEnabled)
                                            <th class="px-4">Dropped Races</th>
                                            <th class="px-4">Points Dropped</th>
                                        @endif

                                      </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Javascript creates table here --}}
                                        @foreach ($standings as $user)
                                        <tr>
                                            {{-- @dd($user) --}}
                                            <td>{{ $loop->index + 1 }}</td>
                                          <td>{{ $user['total_points'] }}</td>
                                          <td>{{ $user['display_name'] }}</td>
                                          <td>{{ $user['total_laps'] }}</td>
                                          <td>{{ $user['total_lead'] }}</td>
                                          <td>{{ $user['total_incidents'] }}</td>
                                          <td>{{ $user['total_races'] }}</td>
                                          <td>{{ $user['total_wins'] }}</td>
                                          @if ($dropWeeksEnabled)
                                            <td>{{ $user['races_dropped'] }}</td>
                                            <td>{{ $user['points_dropped'] }}</td>
                                          @endif

                                        </tr>
                                      @endforeach
                                    </tbody>
                                  </table>
                        </div>
                    </div>
            </div>
            <div>
                <img class="rounded-3xl" src="{{ asset('f3.png') }}">
            </div>
        </div>
            <div class="flex-2 w-64">

            </div>
        </div>
    </div>

</x-app-layout>
</html>
