<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create a Season</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>

    <x-app-layout class="flex flex-col min-h-screen">
        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">
          </main>

          <div class="flex-1 p-4 sm:w-64">
            <div class="p-5 my-10  flex justify-center items-center">
                <div class="bg-gray-300 py-4 px-8 rounded-3xl" style="width: 800px">
                        <div class="flex flex justify-center p-4">
                            <h1 class="text-4xl">Create a Season</h1>
                        </div>
                    <form id="scoring_form" method="POST" action="{{route('createSeason', ['leagueId' => $league->first()->leagueId])}}">
                            {{ csrf_field() }}
                        <div class="flex flex justify-center">
                            <p><span class="text-red-600">* </span> Season Name</p>
                        </div>
                        <div class="flex flex justify-center">
                            <input type="text" name="season_name" class="text-xl" required>
                        </div>
                        <input type="text" class="hidden" name="season_count" value="{{ ++$count }}">
                        <br>
                        <div class="flex flex-row p-4 rounded-xl justify-center items-center">
                            <h1 class="text-4xl mx-auto">Season Scoring</h1>
                        </div>
                        <div class="flex justify-center pb-8">
                        <div class="text-sm font-medium text-center">
                            <ul class="flex flex-wrap -mb-px">
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="qualifying">Qualifying</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active" aria-current="page" data-tab="tabs" id="heats">Heats</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="consolation">Consolation</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="feature">Feature (Main)</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="extra">Extra Points</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 grid-flow-row gap-2 hidden" id="qualifyingBody">
                        @for ($i = 1; $i <= 60; $i++)
                            <div class="grid grid-cols-4 items-center">
                                <p class="rounded-lg w-14">#{{$i}}</p>
                                <input type="text" name="qualifying_data[{{$i}}]" class="rounded-lg w-14" value="0">
                            </div>
                        @endfor
                    </div>
                    <div class="grid grid-cols-4 grid-flow-row gap-2" id="heatBody">
                        @for ($i = 1; $i <= 60; $i++)
                            <div class="grid grid-cols-4 items-center">
                                <p class="rounded-lg w-14">#{{$i}}</p>
                                <input type="text" name="heat_data[{{$i}}]" class="rounded-lg w-14" value="0">
                            </div>
                        @endfor
                    </div>
                    <div>
                        <div class="grid grid-cols-6 gap-2 items-center hidden" id="consolationBody">
                            @for ($i = 1; $i <= 60; $i++)
                                <div class="grid grid-cols-4 items-center">
                                    <p class="rounded-lg w-14">#{{$i}}</p>
                                    <input type="text" name="consolation_data[{{$i}}]" class="rounded-lg w-14" value="0">
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div class="grid grid-cols-6 gap-2 items-center hidden" id="featureBody">
                        @for ($i = 1; $i <= 60; $i++)
                            <div class="grid grid-cols-4 items-center">
                                <p class="rounded-lg w-14">#{{$i}}</p>
                                <input type="text" name="feature_data[{{$i}}]" class="rounded-lg w-14" value="0">
                            </div>
                        @endfor
                    </div>
                        <div class="hidden" id="extraBody">
                            <p class="pl-2 italic underline"> Extra points will apply to all race types except for Qualifying </p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center p-2">
                                    <label for="fastest_lap">Fastest Lap</label>
                                </div>
                                <div class="p-2">
                                    <input type="text" id="fastest_lap" name="fastest_lap" class="rounded-lg p-2 w-10" value="0">
                                </div>

                                <div class="flex items-center inline-flex">
                                    <p class="p-2"> Drop weeks enabled: </p>
                                    <input type="checkbox" name="enabled_drop_weeks" class="form-checkbox h-5 w-5 text-blue-600 ml-10 rounded-sm enabled_drop_weeks" value="true">
                                </div>
                                <div class="flex"></div>
                                <div class="flex items-center p-2 showDropWeekOptions">
                                    <label>Number of races before drop weeks start</label>
                                </div>
                                <div class="p-2 showDropWeekOptions">
                                    <input type="text" id="raceCountBeforeDropWeeks" name="start_of_drop_score" class="rounded-lg p-2 w-10" value="8">
                                </div>
                                <div class="flex items-center p-2 showDropWeekOptions">
                                    <label>Number of lowest score races to drop</label>
                                </div>
                                <div class="p-2 showDropWeekOptions">
                                    <input type="text" id="countOfDroppedRaces" name="races_to_drop" class="rounded-lg p-2 w-10" value="4">
                                </div>
                                <div class="flex items-center inline-flex">
                                    <p class="p-2"> Scored by % of laps completed: </p>
                                    <input type="checkbox" name="enabled_percentage_laps" class="form-checkbox h-5 w-5 text-blue-600 ml-10 rounded-sm percentageCheckbox" value="true">
                                </div>
                                <div class="flex"></div>
                                <div class="flex items-center p-2 showPercentageOptions">
                                    <label class="">Percentage of race completed before scoring:</label>
                                </div>
                                <div class="p-2 showPercentageOptions">
                                    <input type="text" id="lap_percentage_to_complete" name="lap_percentage_to_complete" class="rounded-lg p-2 w-10" min="0" max="100" value="25" style="margin-right: 5px;">
                                    <span>%</span>
                                </div>

                            </div>
                        </div>
                        <br>
                        <div class="flex justify-center items-center pt-4">
                            <button class="bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 mb-10">Save</button>
                            <div class="px-10 pb-10">
                                <a class="bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700" href="/league/{{ $league->first()->leagueId }}">Cancel</a>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            <div class="my-10">
                <img class="rounded-3xl" src="{{ asset('f3.png') }}">
            </div>
        </div>
        <div class="flex-2 w-64">

        </div>
        </div>
        {{-- <footer class="h-48 bg-gray-100">Footer</footer> --}}
      </div>
</x-app-layout>
</html>

<script>
    const listItems = document.querySelectorAll('.tab-link');

    listItems.forEach(item => {
        item.addEventListener('click', () => {
            const tab = item.getAttribute('id');
            const allTabs = document.querySelectorAll('[data-tab="tabs"]');
            const qualifyingBody = document.getElementById('qualifyingBody');
            const heatBody = document.getElementById('heatBody');
            const consolationBody = document.getElementById('consolationBody');
            const featureBody = document.getElementById('featureBody');
            const bodys = [qualifyingBody, heatBody, consolationBody, featureBody, extraBody];
            bodys.forEach(bod => {
                bod.classList.add('hidden');
            })
            allTabs.forEach(tab => {
                tab.className = "text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700";
            })
            if (tab == 'qualifying') {
                qualifyingBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                const qualifying = document.getElementById('qualifying');
                qualifying.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active";
            } else if (tab == 'heats') {
                heatBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                const heat = document.getElementById('heats');
                heat.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active";
            } else if (tab == 'consolation') {
                consolationBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                consolationBody.style.display = 'visible';
                const consolation = document.getElementById('consolation');
                consolation.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active";
            } else if (tab == 'feature') {
                featureBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                const feature = document.getElementById('feature');
                feature.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active";
            } else if(tab == 'extra') {
                const allowDropWeeksCheckbox = document.querySelector('.enabled_drop_weeks');
                const showDropWeekOptions = document.querySelectorAll('.showDropWeekOptions');

                allowDropWeeksCheckbox.addEventListener('change', function () {
                    showDropWeekOptions.forEach((element) => {
                        if (!this.checked) {
                            element.classList.add('hidden');
                        } else {
                            element.classList.remove('hidden');
                        }
                    })
                });

                const areDropWeeksEnabled = allowDropWeeksCheckbox.checked;
                showDropWeekOptions.forEach((element) => {
                        if (areDropWeeksEnabled) {
                            allowDropWeeksCheckbox.checked = true;
                            element.classList.remove('hidden');
                        } else {
                            allowDropWeeksCheckbox.checked = false;
                            element.classList.add('hidden');
                        }
                    });

                const lapPercentageCheckbox = document.querySelector('.percentageCheckbox');
                const showPercentageOptions = document.querySelectorAll('.showPercentageOptions');

                lapPercentageCheckbox.addEventListener('change', function () {
                    showPercentageOptions.forEach((element) => {
                        if (lapPercentageCheckbox.checked) {
                            element.classList.remove('hidden');
                        } else {
                            element.classList.add('hidden');
                        }
                    })
                });

                const isScoringByPercentEnabled = lapPercentageCheckbox.checked;
                showPercentageOptions.forEach((element) => {
                        if (isScoringByPercentEnabled) {
                            lapPercentageCheckbox.checked = true;
                            element.classList.add('visible');
                        } else {
                            lapPercentageCheckbox.checked = false;
                            element.classList.remove('visible');
                        }
                    });


                extraBody.className = "";
                const extra = document.getElementById('extra');
                extra.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active";
            }
        });
    });
</script>
