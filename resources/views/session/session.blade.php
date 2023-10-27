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

                    <div class="flex flex-1 items-center justify-center flex-col text-center">
                            <h1 class="text-4xl font-bold">{{ $league->name }}</h1>
                            <p class="text-center pt-2">{{ $sessions[0]->track_name }}
                                @if ($sessions[0]->config_name) - {{ $sessions[0]->config_name }}
                                @endif
                            </p>
                            <p class="text-center pt-2">
                                @php
                                    $formattedDate = \Carbon\Carbon::parse($sessions[0]->race_date)->format('l, F jS, Y');
                                @endphp
                                {{ $formattedDate }}
                            </p>
                            <p class="text-center pt-2"> Temp: {{$sessions[0]->temp_value}}
                                @if ($sessions[0]->temp_units == 0)
                                    F
                                @if ($sessions[0]->temp_units == 1)
                                    C
                                @endif
                                @endif
                            <p class="text-center pt-2"> Humidity: {{ $sessions[0]->rel_humidity }}

                    </div>

                </div>
            </div>
            <div class="py-4 text-center flex justify-between items-center">
                <div class="flex-1">
                    <select class="simsession_name_selector" name="simsession_name_selector">
                    </select>
                </div>
                @if (Auth::check() && Auth::id() == $league->league_owner_id)
                    <div class="absolute">
                        <a class=" add-penalties text-lg p-2 bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Add Penalties</a>
                    </div>
                @endif

            </div>

            <div class="flex justify-center items-center">
                    <div class="flex flex-row p-2 rounded-xl justify-center items-center">
                            <tr class="m-2">
                                <table class="pl-2 text-center" id="racers_table">
                                <thead>
                                    <tr>
                                    <th class="pl-2">Finish</th>
                                    <th class="p-0">Start</th>
                                    <th class="pl-2">Race Points</th>
                                    <th class="p-0">Driver</th>
                                    <th class="px-4">Laps Lead</th>
                                    <th class="p-0">Laps Completed</th>
                                    <th class="p-4">Interval</th>
                                    <th class="p-3">Best Lap Time</th>
                                    <th class="p-0">Inc</th>
                                    <th class="p-0">Club Name</th>
                                    <th class="p-2">Pen Points</th>
                                    <th class="p-2">Time Pen</th>
                                    </tr>
                                </thead>
                                <tbody class="display">
                                    {{-- Display's the table --}}
                                </tbody>
                            </table>
                    </div>
            </div>
            <div>
                <img class="rounded-3xl py-5" src="{{ asset('f3.png') }}">
            </div>
        </div>
            <div class="flex-2 w-64">

            </div>
        </div>

        <!-- Penalty Modal -->
        <div class="hidden penalty-modal fixed top-0 left-0 w-full h-full flex items-center justify-center backdrop-filter-blur">
            <div class="modal-dialog w-1/2 h-1/2">
                <div class="modal-content p-6 bg-gray-200 border border-black rounded-lg">
                <div class="text-header pb-2">
                    <p>Penalties to add to drivers. Each driver specified will be applied the time penalty to the selected race. Any positions on the lead lap will be adjusted based on the time penalty applied. </p>
                </div>
                <div class="pb-6 flex justify-between items-center">
                    <div class="flex-grow text-center">
                        <a id="addDriverButton" class="p-2 bg-blue-400 hover:bg-blue-500 rounded-xl">Add Driver</a>
                    </div>
                </div>
                <form id="driverForm" method="POST" action="{{ url("/session/" . $sessions[0]->subsession_id . "/add-penalty")}}">
                    @csrf
                    <div class="modal-body">
                        <ul id="driverList" name="penalty-data">
                            @if ($currentData)
                                @foreach ($currentData as $pen)
                                <li data-index="{{ $loop->index }}">
                                    <select name="driver[]" class="driver-dropdown">
                                        <option value="{{$pen->display_name}}" selected>{{$pen->display_name}}
                                    </select>
                                    <input name="penaltyPoints[]" type="number" class="penaltyPoints" placeholder="Penalty Points" value="{{$pen->penalty_points}}">
                                    <input name="penaltyTime[]" type="number" class="penaltyTime" placeholder="Penalty Time (seconds)"
                                    value="{{$pen->penalty_seconds}}">
                                    <select name="penalty-session[]">
                                        @foreach ($types as $type)
                                            <option {{$type==$pen->simsession_name ? 'selected' : ''}} value="{{$type}}">{{$type}}</option>
                                        @endforeach
                                    </select>
                                    <a type="button" class="removeRowButton p-2 rounded text-center bg-red-500 hover:bg-red-600">X</a>
                                </li>
                                @endforeach
                            @else
                                <li id="defaultElement">
                                    <select name="driver[]" class="driver-dropdown">
                                        <option value="" disabled>Select a driver</option>
                                    </select>
                                    <input name="penaltyPoints[]" type="number" class="penaltyPoints" placeholder="Penalty Points">
                                    <input name="penaltyTime[]" type="number" class="penaltyTime" placeholder="Penalty Time (seconds)">
                                    <select name="penalty-session[]" class="race-type-dropdown"></select>
                                </select>
                                    <a type="button" class="removeRowButton p-2 rounded text-center bg-red-500 hover:bg-red-600" hidden>X</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="modal-footer flex justify-center items-center">
                        <a class="cancel-penalties text-lg p-2 float-right bg-gray-400 hover:bg-gray-500 rounded-xl text-gray-100 m-6">Cancel</a>

                        <button class="add-penalties text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Apply Penalties</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
</html>

<script>
    function populateDropdownOptions(array, selector) {
        let elements = document.querySelectorAll(selector);
        elements.forEach(function (dropdown) {
            array.forEach(function (opt) {
                let e = document.createElement("option");
                e.value = opt;
                e.text = opt;
                dropdown.appendChild(e);
            });
        });
    }

    function timeToSeconds(timeString) {
        const [minutes, seconds] = timeString.split(':');
        return parseFloat(minutes) * 60 + parseFloat(seconds);
    }

    let raceTypeArray = @json($types);
    populateDropdownOptions(raceTypeArray, '.simsession_name_selector');

    let driversArray = @json($drivers);

    var sessions1 = <?php echo json_encode($calculatedResults); ?>;
    var selected = document.querySelector("select[name='simsession_name_selector']");

    function createTable(selectedValue, type) {
        var sessionByName = sessions1.filter(element => {
            return(element.simsession_name == selectedValue);
        });
        let fastest = -1;
        let fastest_time;
        sessionByName.forEach(element => {
            if (element.best_lap_time !== '-') {
                let time = timeToSeconds(element.best_lap_time);
                if (fastest == -1 || time < fastest) {
                    fastest = time;
                    fastest_time = element.best_lap_time;
                }
            }
        });
        var table = document.querySelector(".display");
        table.innerHTML = "";
        sessionByName.forEach(function(element) {
            if (element.simsession_name == selectedValue) {
                var row = document.createElement("tr");
                var fields = [
                    "finish_position",
                    "starting_pos",
                    "race_points",
                    "display_name",
                    "laps_lead",
                    "laps_completed",
                    "interval",
                    "best_lap_time",
                    "incidents",
                    "club_name",
                    "penalty_points",
                    "penalty_seconds"
                ];
                if(type == "display"){
                    fields.forEach(function(fieldName) {
                    var cell = document.createElement("td");
                    if(element[fieldName] == fastest_time) {
                        cell.className = "fastest-lap";
                    }
                    cell.textContent = element[fieldName];
                    row.appendChild(cell);
                    });
                }
                table.appendChild(row);
            }
        });
    }

    function handleSelectedValueChange() {
        var selectedValue = selected.value;
        createTable(selectedValue, "display");
    }
    selected.addEventListener("change", handleSelectedValueChange);

    const penModal = document.querySelector(".penalty-modal");
    function togglePenaltyModal() {
        penModal.classList.toggle('hidden');
    }

    let penaltiesBtn = document.querySelector(".add-penalties");
    penaltiesBtn.addEventListener('click', togglePenaltyModal);
    const cancelPenBtn = document.querySelector(".cancel-penalties");
    cancelPenBtn.addEventListener('click', togglePenaltyModal);

    window.addEventListener("load", handleSelectedValueChange);

    populateDropdownOptions(driversArray, '.driver-dropdown');
    document.getElementById('addDriverButton').addEventListener('click', function () {
        const driverList = document.getElementById('driverList');
        let driverDropdown = document.querySelector('.driver-dropdown');

        if(!driverDropdown){
            driverDropdown = document.createElement('select');
            driverDropdown.name = 'driver[]';
            driverDropdown.className = 'driver-dropdown-add';
        } else {
            driverDropdown = driverDropdown.cloneNode(true);
        }
        let raceTypeDropdown = selected.cloneNode(true);
        raceTypeDropdown.name = 'penalty-session[]';
        raceTypeDropdown.className = 'race-type-dropdown';

        const penaltyPointsInput = `<input name="penaltyPoints[]" type="number" class="penaltyPoints" placeholder="Penalty Points"></input>`;
        const penaltyTimeInput = `<input name="penaltyTime[]" type="number" class="penaltyTime" placeholder="Penalty Time (seconds)"></input>`;
        const removeButton = `<a type="button" class="removeRowButton p-2 rounded text-center bg-red-500 hover:bg-red-600">X</a>`;

        const newRow = document.createElement('li');
        newRow.innerHTML = driverDropdown.outerHTML + ' ' +
        penaltyPointsInput + ' ' +
        penaltyTimeInput + ' ' +
        raceTypeDropdown.outerHTML + ' ' +
        removeButton;
        driverList.appendChild(newRow);
        populateDropdownOptions(driversArray, '.driver-dropdown-add');
    });

    document.getElementById('driverList').addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('removeRowButton')) {
            const row = event.target.parentNode;
            const driverList = document.getElementById('driverList');
            row.remove();
        }
    });
</script>
