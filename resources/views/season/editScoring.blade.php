<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Form</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

  @section('scripts')
  <script src="{{ asset('js/Scoring.js') }}" >
  @stop
</head>

    <x-app-layout class="flex flex-col min-h-screen">
        @if ($errors->any())
                        <div class="flex p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                            <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Error</span>
                            <div>
                                <span class="font-medium">There was a problem creating your season</span>
                                <ul class="mt-1.5 ml-4 text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{!! $error !!}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('success'))
                    <div class="flex p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-red-800 transition-opacity duration-500 hidden" role="alert">
                        <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Error</span>
                        <div>
                            <span class="font-medium">Season has been created!</span>
                            <ul class="mt-1.5 ml-4 text-green-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">
          </main>

          <div class="flex-1 p-4 sm:w-64">
            <div class="p-5 my-10  flex justify-center items-center">
                <div class="bg-gray-300 py-4 px-8 rounded-3xl">
                    <form method="POST" action="">
                            {{ csrf_field() }}
                        <br>
                        <div class="flex flex-row p-4 rounded-xl justify-center items-center">
                            <h1 class="text-4xl mx-auto">{{ $season[0]->season_name }} scoring</h1>
                        </div>
                        <div class="flex justify-center pb-8">
                        <div class="text-sm font-medium text-center">
                            <ul class="flex flex-wrap -mb-px">
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="qualifying">Qualifying</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500" aria-current="page" data-tab="tabs" id="heats">Heats</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="consolation">Consolation</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button" class="text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700" data-tab="tabs" id="feature">Feature (Main)</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                        <div class="grid grid-cols-4 grid-flow-row gap-2 hidden" id="qualifyingBody">
                            @for ($i = 1; $i <= 60; $i++)
                                <div class="grid grid-cols-4 items-center">
                                    <p class="rounded-lg w-14">#{{$i}}</p>
                                    <input type="text" name="scoring_column[{{$i}}]" class="rounded-lg w-14" value={{ $qualifying[$i] }}></input>
                                </div>
                            @endfor
                        </div>
                        <div class="grid grid-cols-4 grid-flow-row gap-2" id="heatBody">
                            @for ($i = 1; $i <= 60; $i++)
                                <div class="grid grid-cols-4 items-center">
                                    <p class="rounded-lg w-14">#{{$i}}</p>
                                    <input type="text" name="scoring_column[{{$i}}]" class="rounded-lg w-14" value={{ $heat[$i] }}></input>
                                </div>
                            @endfor
                        </div>
                        <div>
                            <div class="grid grid-cols-6 gap-2 items-center hidden" id="consolationBody">
                                @for ($i = 1; $i <= 60; $i++)
                                    <div class="grid grid-cols-4 items-center">
                                        <p class="rounded-lg w-14">#{{$i}}</p>
                                        <input type="text" name="scoring_column[{{$i}}]" class="rounded-lg w-14" value={{ $consolation[$i] }}></input>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div class="grid grid-cols-6 gap-2 items-center hidden" id="featureBody">
                            @for ($i = 1; $i <= 60; $i++)
                                <div class="grid grid-cols-4 items-center">
                                    <p class="rounded-lg w-14">#{{$i}}</p>
                                    <input type="text" name="scoring_column[{{$i}}]" class="rounded-lg w-14" value={{ $feature[$i] }}></input>
                                </div>
                            @endfor
                        </div>
                        <br>
                        <div class="flex justify-center items-center pt-4">
                            <button class="bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 mb-10">Save</button>
                            <div class="px-10 pb-10">
                                <a class="bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700" href="/season/{{ $season[0]->id }}">Cancel</a>
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
            // Get the value of the 'data-tab' attribute
            const tab = item.getAttribute('id');
            // Update the content based on the clicked tab
            const allTabs = document.querySelectorAll('[data-tab="tabs"]');
            const qualifyingBody = document.getElementById('qualifyingBody');
            const heatBody = document.getElementById('heatBody');
            const consolationBody = document.getElementById('consolationBody');
            const featureBody = document.getElementById('featureBody');
            const bodys = [qualifyingBody, heatBody, consolationBody, featureBody];
            bodys.forEach(bod => {
                bod.classList.add('hidden');
            })
            allTabs.forEach(tab => {
                tab.className = "text-black tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-700";
            })
            if (tab === 'qualifying') {
                qualifyingBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                const qualifying = document.getElementById('qualifying');
                qualifying.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500";
            } else if (tab === 'heats') {
                heatBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                const heat = document.getElementById('heats');
                heat.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500";
            } else if (tab === 'consolation') {
                consolationBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                consolationBody.style.display = 'visible';
                const consolation = document.getElementById('consolation');
                consolation.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500";
            } else if (tab === 'feature') {
                featureBody.className = "grid grid-cols-4 grid-flow-row gap-2";
                const feature = document.getElementById('feature');
                feature.className = "tab-link inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500";
            }
        });
    });
    </script>


