<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> {{ $league->name }}</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
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
            <span class="sr-only">Error</span>
            <div>
                <span class="font-medium">{{ session('success') }}</span>
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
            <div class="flex items-center">
                <a href="/league/{{ $league->leagueId }}" class="text-blue-500 underline p-2"> League </a> >
            </div>
            <div class="relative">
                <div class="p-4 bg-white rounded-xl items-center justify-content-between">
                    <div class="flex flex-row items-center">
                        <div class="flex flex-1 justify-center">
                            <h1 class="text-4xl font-bold text-center">{{ $league->name }}</h1>
                        </div>
                        @if (Auth::check() && Auth::id() === $league->league_owner_id)
                            <div class="pr-6 absolute top-4 right-36">
                                <a href="/season/{{$seasonId}}/scoring", class="text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Edit Scoring</a>
                            </div>
                            <div>
                                <button href="" id="modal-button" class="text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100 absolute top-4 right-4">Import session</button>
                            </div>
                        @endif
                    </div>
                    <p class="text-lg my-5 mx-auto text-center">{{ $league->description }}</p>
                </div>
            </div>

            <div class="p-5 my-10  flex justify-center items-center">
                <!-- Modal container -->
                <div id="modal" class="hidden fixed top-0 left-0 w-full h-full flex items-center justify-center">
                    <!-- Modal content -->
                    <div id="main-modal" class="bg-gray-400 rounded-xl p-3 mx-auto shadow-xl overflow-y-auto">
                        <form method="POST" action={{ url("/league/" . $league->leagueId . "/" . $seasonId) }} enctype="multipart/form-data">
                            @csrf
                        <button type="button" class="float-right pr-2 close" data-dismiss="main-modal" id="close">&times;</button>
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
                                    @if (Auth::check() && Auth::id() === $league->league_owner_id)
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
                                        @if (Auth::check() && Auth::id() === $league->league_owner_id)
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
    // Get the modal, button, and input elements
    const modal = document.getElementById('modal');
    const popup = document.getElementById('main-modal');
    const button = document.getElementById('modal-button');
    const submit = document.getElementById('modal-submit');

    // When the button is clicked, toggle the modal's visibility
    button.addEventListener('click', () => {
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        modal.classList.add('modal-open');
    }
    });

    modal.addEventListener('click', (event) => {
        if(!popup.contains(event.target)){
            modal.classList.add('hidden');
            modal.classList.remove('modal-open');
        }
    });

    document.getElementById('delete-button').addEventListener('submit', function (event) {
        event.preventDefault();
        this.submit();
    });

  </script>
</html>
