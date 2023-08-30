<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> {{ $league->name }}</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>

    <x-app-layout class="flex flex-col min-h-screen">

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">
          </main>
          <div class="flex-1 p-4 sm:w-64">
            <div class="flex items-center">
                <a href="/league/{{ $league->leagueId }}" class="text-blue-500 underline p-2"> League </a>
                <p> > </p>
                <a href="/season/{{$season_id}}" class="text-blue-500 underline p-2"> Season </a>
            </div>
            <div class="p-4 bg-white rounded-xl items-center justify-content-between">
                <div class="flex flex-row items-center">
                    <div class="flex flex-1 items-center justify-center">
                        <h1 class="text-4xl font-bold text-center">{{ $league->name }}</h1>
                        <p class=""> {{$sessions[0]->track_name}}
                        </p>
                        @php
                        @endphp
                    </div>
                </div>
            </div>
            <div class="p-4 text-center">
                <select name="simsession_name_selector">
                    @foreach ($types as $session_type)
                        <option value="{{ $session_type }}">{{ $session_type }}</option>
                    @endforeach
                </select>
            </div>

            <script>
                    var sessions1 = <?php echo json_encode($sessions); ?>;
                    var selected = document.querySelector("select[name='simsession_name_selector']");

                    function updateTable(selectedValue) {
                        var filteredVal = sessions1.filter(element => {
                            return(element.simsession_name == selectedValue);
                        });
                        var table = document.querySelector("table tbody");
                        table.innerHTML = "";
                        filteredVal.forEach(function(element) {
                            var row = document.createElement("tr");
                            var finish_position = document.createElement("td");
                            finish_position.innerHTML = element.finish_position;

                            var race_points = document.createElement("td");
                            race_points.innerHTML = element.race_points;

                            var display_name = document.createElement("td");
                            display_name.innerHTML = element.display_name;

                            var laps_lead = document.createElement("td");
                            laps_lead.innerHTML = element.laps_lead;

                            var laps_completed = document.createElement("td");
                            laps_completed.innerHTML = element.laps_completed;

                            var interval = document.createElement("td");
                            interval.innerHTML = element.interval;

                            var average_lap_time = document.createElement("td");
                            average_lap_time.innerHTML = element.average_lap_time;

                            var best_lap_time = document.createElement("td");
                            best_lap_time.innerHTML = element.best_lap_time;

                            var incidents = document.createElement("td");
                            incidents.innerHTML = element.incidents;

                            var starting_pos = document.createElement("td");
                            starting_pos.innerHTML = element.starting_pos;

                            var club_name = document.createElement("td");
                            club_name.innerHTML = element.club_name;

                            row.appendChild(finish_position);
                            row.appendChild(starting_pos);
                            row.appendChild(race_points);
                            row.appendChild(display_name);
                            row.appendChild(laps_lead);
                            row.appendChild(laps_completed);
                            row.appendChild(interval);
                            row.appendChild(average_lap_time);
                            row.appendChild(best_lap_time);
                            row.appendChild(incidents);
                            row.appendChild(club_name);

                            table.appendChild(row);
                        });
                    }

                    selected.addEventListener("change", function(){
                        var selectedValue = selected.value;
                        updateTable(selectedValue);
                    });

                    window.addEventListener("load", function(){
                        var selectedValue = selected.value;
                        updateTable(selectedValue);
                    });
            </script>

            <div class="flex justify-center items-center">
                    <div class="bg-gray-300 py-4 px-12 rounded-3xl">
                        <div class="flex flex-row p-2 rounded-xl justify-center items-center">
                                <tr class="m-2">
                                  <table class="pl-2 text-center" id="racers_table">
                                    <thead>
                                      <tr>
                                        <th class="px-4">Pos</th>
                                        <th class="p-2">Starting Pos.</th>
                                        <th class="px-6">Race Points</th>
                                        <th class="px-10">Driver</th>
                                        <th class="px-5">Laps Lead</th>
                                        <th>Laps Completed</th>
                                        <th>Interval</th>
                                        <th class="px-6">Avg. Lap Time</th>
                                        <th class="px-4">Best Lap Time</th>
                                        <th class="p-2">Incidents</th>
                                        <th class="p-2">Club Name</th>

                                      </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sessions as $user)
                                        <tr>
                                          <td>{{ $user->finish_position }}</td>
                                          <td>{{ $user->starting_pos }}</td>
                                          <td>{{ $user->race_points }}</td>
                                          <td>{{ $user->display_name }}</td>
                                          <td>{{ $user->laps_lead }}</td>
                                          <td>{{ $user->laps_completed }}</td>
                                          <td>{{ $user->interval }}</td>
                                          <td>{{ $user->average_lap_time }}</td>
                                          <td>{{ $user->best_lap_time }}</td>
                                          <td>{{ $user->incidents }}</td>
                                          <td>{{ $user->club_name }}</td>

                                        </tr>
                                      @endforeach
                                    </tbody>
                                  </table>

                        </div>

                    </div>
            </div>
            <div>
                <img class="rounded-3xl py-5" src="{{ asset('f3.png') }}">
            </div>
        </div>
            <div class="flex-2 w-64">

            </div>
        </div>
    </div>
</x-app-layout>
</html>
