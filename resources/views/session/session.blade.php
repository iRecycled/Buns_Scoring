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
                <a class="add-penalties text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Add Penalties</a>
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

            <div class="flex justify-center items-center">
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
                                    <th class="px-4">Interval</th>
                                    <th class="px-6">Avg. Lap Time</th>
                                    <th class="px-4">Best Lap Time</th>
                                    <th class="p-2">Incidents</th>
                                    <th class="p-2">Club Name</th>
                                    </tr>
                                </thead>
                                <tbody class="display">
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
                        <a id="addRowButton" class="p-2 bg-blue-400 hover:bg-blue-500 rounded-xl">Add Driver</a>
                    </div>
                </div>
                <form id="driverForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <ul id="driverList" name="penalty-data">
                            <li id="defaultElement">
                                <select name="driver[]">
                                    <option value="">Select a driver</option>
                                @foreach ($drivers as $session_drivers)
                                    <option value="{{ $session_drivers }}">{{ $session_drivers }}</option>
                                @endforeach
                            </select>
                                <input name="penaltyPoints[]" type="number" class="penaltyPoints" placeholder="Penalty Points">
                                <input name="penaltyTime[]" type="number" class="penaltyTime" placeholder="Penalty Time (seconds)">
                                <select name="penalty-session[]">
                                    @foreach ($types as $session_type)
                                        <option value="{{ $session_type }}">{{ $session_type }}</option>
                                    @endforeach
                                </select>
                                <a type="button" class="removeRowButton p-2 rounded text-center bg-red-500 hover:bg-red-600" hidden>X</a>
                            </li>
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
    var sessions1 = <?php echo json_encode($sessions); ?>;
    var selected = document.querySelector("select[name='simsession_name_selector']");
    let penaltiesBtn = document.querySelector(".add-penalties");

    function createTable(selectedValue, type) {
        var sessionByName = sessions1.filter(element => {
            return(element.simsession_name == selectedValue);
        });
        var table = document.querySelector(".display");
        table.innerHTML = "";
        sessionByName.forEach(function(element) {
            if (element.simsession_name === selectedValue) {
                var row = document.createElement("tr");
                var fields = [
                    "finish_position",
                    "starting_pos",
                    "race_points",
                    "display_name",
                    "laps_lead",
                    "laps_completed",
                    "interval",
                    "average_lap_time",
                    "best_lap_time",
                    "incidents",
                    "club_name",
                ];
                if(type == "display"){
                    fields.forEach(function(fieldName) {
                    var cell = document.createElement("td");
                    cell.textContent = element[fieldName];
                    row.appendChild(cell);
                    });
                }
                table.appendChild(row);
            }
        });
    }
    selected.addEventListener("change", function() {
        var selectedValue = selected.value;
        createTable(selectedValue, "display");
    });

    window.addEventListener("load", function() {
        var selectedValue = selected.value;
        createTable(selectedValue, "display");

    });
    const penModal = document.querySelector(".penalty-modal");
    const cancelPenBtn = document.querySelector(".cancel-penalties");

    penaltiesBtn.addEventListener('click', () => {
        penModal.classList.remove('hidden');
    });

    cancelPenBtn.addEventListener('click', () => {
        penModal.classList.add('hidden');
    });


    document.getElementById('addRowButton').addEventListener('click', function () {
        const driverList = document.getElementById('driverList');
        const newRow = driverList.querySelector('li').cloneNode(true);

        newRow.querySelector('select').value = "";
        newRow.querySelectorAll('input').forEach(function(input) {
            input.value = "";
        });

        driverList.appendChild(newRow);

        if (driverList.children.length > 1) {
            newRow.querySelector('.removeRowButton').style.display = 'inline-block';
        }
    });

    window.addEventListener('DOMContentLoaded', function () {
        const driverList = document.getElementById('driverList');
        if (driverList.children.length === 1) {
            driverList.querySelector('.removeRowButton').style.display = 'none';
        }
    });

    document.getElementById('driverList').addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('removeRowButton')) {
            const row = event.target.parentNode;
            const driverList = document.getElementById('driverList');
            row.remove();

            if (driverList.children.length === 1) {
                driverList.querySelector('.removeRowButton').style.display = 'none';
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
    // Assuming you have already retrieved the form and driverList elements
    const form = document.getElementById('driverForm');
    const driverList = document.getElementById('driverList');

    const sessionData = @json($currentData);
    if(sessionData.length > 0){
        document.getElementById('defaultElement').remove();
    }
    sessionData.forEach(function (record, index) {
        const newRow = document.createElement('li');
        newRow.innerHTML = `
            <select name="driver[]">
                <option value="">${record.display_name}</option>
                @foreach ($drivers as $session_drivers)
                    <option value="">{{ $session_drivers }}</option>
                @endforeach
            </select>
            <input name="penaltyPoints[]" type="number" class="penaltyPoints" placeholder="Penalty Points" value="${record.penalty_points}">
            <input name="penaltyTime[]" type="number" class="penaltyTime" placeholder="Penalty Time (seconds)" value="${record.penalty_seconds}">
            <select name="penalty-session[]">
                <option value="">${record.simsession_name}</option>
                @foreach ($types as $session_type)
                    <option value="{{ $session_type }}">{{ $session_type }}</option>
                @endforeach
            </select>
            <a type="button" class="removeRowButton p-2 rounded text-center bg-red-500 hover:bg-red-600">X</a>`;

        // Append the new list item to the driverList
        driverList.appendChild(newRow);

        if (index === 0) {
            const defaultFormFields = form.querySelectorAll('input[name="penaltyPoints[]"], input[name="penaltyTime[]"]');
            defaultFormFields.forEach(function (field) {
                field.value = record.penalty_points; // Set the default value
            });
        }
        });
    });


    //sessionData[0].display_name;
</script>
