<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> {{ $league->name }}</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
  <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
</head>

    <x-app-layout class="flex flex-col min-h-screen">
    @if ($errors->any())
        <div class="flex p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <span class="sr-only">Error</span>
            <div>
                <span class="font-medium">{{ $errors->first()}}</span>
                <ul class="mt-1.5 ml-4 text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="flex p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd"></path></svg>
            <div>
                <span class="font-medium">{{ session('success')}}</span>
            </div>
        </div>
    @endif

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">
          </main>
          <div class="flex-1 p-4 sm:w-64">
            <div class="flex items-center">
                <a href="/league/{{ $league->leagueId }}" class="text-blue-500 underline p-2"> League </a> >
            </div>
            <div class="relative">
                <div class="p-4 bg-white rounded-xl items-center justify-content-between">
                    <div class="flex flex-row items-center">
                        <div class="flex flex-1 justify-center">
                            <h1 class="text-4xl font-bold text-center">{{ $league->name }}</h1>
                        </div>
                        @if (Auth::check() && Auth::id() == $league->league_owner_id)
                        <div class="absolute top-4 left-4">
                            <button class="show-api-modal text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">API Import</button>
                        </div>
                            <div class="pr-6 absolute top-4 right-36">
                                <a href="/season/{{$seasonId}}/scoring", class="text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Edit Scoring</a>
                            </div>
                            <div>
                                <button href="" class="import-session-btn text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100 absolute top-4 right-4">Import session
                                </button>
                            </div>
                        @endif
                    </div>
                    <p class="text-lg my-5 mx-auto text-center">{{ $league->description }}</p>
                </div>
            </div>

            <div class="p-5 my-10  flex justify-center items-center">
                <!-- Import Session Modal -->
                <div id="modal" class="hidden import-session-modal fixed top-0 left-0 w-full h-full flex items-center justify-center">
                    <div id="main-modal" class="bg-gray-400 rounded-xl p-3 mx-auto shadow-xl overflow-y-auto">
                        <form method="POST" action={{ url("/league/" . $league->leagueId . "/" . $seasonId) }} enctype="multipart/form-data">
                            @csrf
                        <button type="button" class="float-right pr-2 close close-session-btn" id="close">&times;</button>
                        <h2 class="text-2xl font-bold py-4 ml-5">Import Session JSON file</h2>
                        <input type="file" class="ml-5" name="json_file" accept=".json">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-5 rounded focus:outline-none
                         focus:shadow-outline items-center mr-5" id="modal-submit">Submit</button>
                        </form>
                    </div>
                </div>

                    <div class="bg-gray-300 py-4 px-8 rounded-3xl">
                        <div class="p-4 rounded-xl">
                            <table class="table-auto border border-black">
                                <thead>
                                  <tr>
                                    <th class="border-b border-black pr-2 pl-2">Race #</th>
                                    <th class="border-b border-l pr-2 pl-2 border-black">Track</th>
                                    @if (Auth::check() && Auth::id() == $league->league_owner_id)
                                        <th class="border-b border-l"></th>
                                    @endif
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unique_leagues_sessions as $sessions)
                                    <tr>
                                        <td class="pr-1 pl-2">Race {{ $loop->index+ 1 }} </td>
                                        <td class="border-l border-black">
                                            <a href="/session/{{ $sessions->subsession_id }}" class="text-blue-500 underline p-2"> {{  $sessions->track_name }} </a>
                                        </td>
                                        @if (Auth::check() && Auth::id() == $league->league_owner_id)
                                        <td>
                                            <form method="POST" action="{{$sessions->season_id}}/delete/{{$sessions->subsession_id }}" enctype="multipart/form-data">
                                                @csrf
                                                <input class="hidden" name="userId" value="{{Auth::id()}}">
                                                <input class="hidden" name="sessionId" value={{$sessions->subsession_id}}>
                                                <input class="hidden" name="seasonId" value={{$sessions->season_id}}>
                                                <button id="delete-button" class="text-red-500 px-2">Delete</button>
                                            </form>
                                        </td>
                                        @endif
                                      </tr>
                                    @endforeach
                                </tbody>
                              </table>
                        </div>
                        <div class="pt-2 text-center">
                                <a href="{{$seasonId}}/standings" class="text-black" > Season Standings </a>
                        </div>

        <!-- iRacing API Modal -->
        <div class="hidden api-modal fixed top-0 left-0 w-full h-full flex items-center justify-center backdrop-filter-blur">
            <div class="modal-dialog w-1/2 h-1/2">
                <div class="modal-content p-6 bg-gray-200 border border-black rounded-lg">
                <div class="text-header pb-2">
                    <p>Put in your iRacing league Id and select the League Season that you want synced. Will then import any sessions for your league that are new. </p>
                </div>
                <form method="POST" action="{{$seasonId}}/update">
                    <div class="flex flex-row">
                            <div>
                                <input type="text" class="ajaxUrl hidden" value="{{$seasonId}}/get/">
                                <label for="iRacingLeagueId" class="block text-gray-700 text-sm font-bold mb-2">iRacing League Id:</label>
                                <input type="text" name="iRacingLeagueId" class="shadow appearance-none border rounded py-2 px-3">
                            </div>
                            <div>
                                <button type="button" onclick="getSeasonFromLeagueId()" class="p-2 bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100 mt-7 ml-4 mb-10">Get Seasons</button>
                            </div>
                    </div>
                    @csrf
                    <div class="loader hidden"></div>

                    <div id="seasonList">
                    </div>

                    <div class="modal-footer flex justify-center items-center">
                        <button type="button" class="cancel-api-modal text-lg p-2 float-right bg-gray-400 hover:bg-gray-500 rounded-xl text-gray-100 m-6">Cancel</button>

                        <button class="add-penalties text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Save</button>
                    </div>
                </form>
                </div>
            </div>
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
        {{-- <footer class="h-48 bg-gray-100">Footer</footer> --}}
      </div>
</x-app-layout>
<script>

    const apiModal = document.querySelector(".api-modal");
    function toggleAPIModal() {
        apiModal.classList.toggle('hidden');
    }

    let apiBtn = document.querySelector(".show-api-modal");
    apiBtn.addEventListener('click', toggleAPIModal);
    const cancelApiBtn = document.querySelector(".cancel-api-modal");
    cancelApiBtn.addEventListener('click', toggleAPIModal);

    const sessionModal = document.querySelector(".import-session-modal");
    function toggleImportSessionModal() {
        sessionModal.classList.toggle('hidden');
    }

    let sessionBtn = document.querySelector(".import-session-btn");
    sessionBtn.addEventListener('click', toggleImportSessionModal);
    let closeSessionBtn = document.querySelector(".close-session-btn");
    closeSessionBtn.addEventListener('click', toggleImportSessionModal);

    document.getElementById('delete-button')?.addEventListener('submit', function (event) {
        event.preventDefault();
        this.submit();
    });

    const loader = document.querySelector('.loader');
    function toggleLoader() {
        loader.classList.toggle('hidden');
    }

    function clearSeasonList() {
        const seasonList = document.getElementById("seasonList");
        while (seasonList.firstChild) {
            seasonList.removeChild(seasonList.firstChild);
        }
    }


    function getSeasonFromLeagueId() {
        const ajaxURL = document.querySelector(".ajaxUrl").value;
        var dataToSend = $('input[name="iRacingLeagueId"]').val();
        this.clearSeasonList();
        this.toggleLoader();
        $.ajax({
            data: {
                "_token": "{{ csrf_token() }}",
                iRacingLeagueId: dataToSend,
                },
            url: ajaxURL,
            method: 'POST',
            success: function (data) {
                toggleLoader();
                var seasonList = $("#seasonList");
                seasonList.empty();
                if(data.length == 0) {
                    var seasonList = $("#seasonList");
                    var par = $('<p class="pb-4"> No Seasons exist for League Id ' + dataToSend + '. </p>');
                    seasonList.append(par);
                } else {
                    var par = $('<p class="pb-4"> Please select one season to sync. </p>');
                    $.each(data, function (seasonId, seasonName) {
                    var radioLabel = $('<label><input type="radio" name="iRacingSeasonId" value="' + seasonId + '" /> ' + seasonName + '</label><br>');
                    seasonList.append(radioLabel);
                });
                }

            },
            error: function (xhr, status, error) {
                toggleLoader();
            }
        });
}

  </script>
</html>
