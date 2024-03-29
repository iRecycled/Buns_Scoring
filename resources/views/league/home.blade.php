<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
      $league = DB::table('leagues')->where('leagueId',$leagueId)->first();
  @endphp
  <title> {{ $league->name }}</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>

    <x-app-layout class="flex flex-col min-h-screen">
        @if ($errors->any())
        <div class="flex p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
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
        <div class="flex p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            <span class="sr-only">Error</span>
            <div>
                <span class="font-medium">{{session('success') }}</span>
                <ul class="mt-1.5 ml-4 text-green-700 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2"></main>
          <div class="flex-1 p-4 sm:w-64" id="container">
            <div class="relative">
                <div class="p-4 bg-white rounded-xl items-center justify-content-between">
                    <div class="flex flex-1 justify-center">
                        <h1 class="text-4xl font-bold text-center">{{ $league->name }}</h1>
                    </div>

                    @if(Auth::check() && Auth::id() == $league->league_owner_id)

                    <form method="POST" action="{{$leagueId}}/delete">
                        {{ csrf_field() }}
                        <input class="hidden" name="userId" value="{{Auth::id()}}">
                        <input class="hidden" name="leagueId" value={{$league->leagueId}}>
                        <div class="pr-6 absolute top-4 right-48">
                            <button type="submit" class="text-lg p-2 float-right bg-red-500 hover:bg-red-600 rounded-xl text-gray-100">Delete League</button>
                        </div>
                    </form>

                    <div class="pr-6 absolute top-4 right-2">
                        <a href="{{route('create_season', ['leagueId' => $league->leagueId])}}" id="modal-button" class="text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Create Season</a>
                    </div>
                    @endif
                    <p class="text-lg my-3 mx-auto text-center">{{ $league->description }}</p>
                </div>
            </div>

            <div class="p-5 my-10  flex justify-center items-center">
                    <div class="bg-gray-300 py-4 px-8 rounded-3xl">
                        <div class="p-4 rounded-xl">
                            <table class="table-auto border border-black">
                                <thead>
                                  <tr>
                                    <th class="border-b border-black pr-2 pl-2">Season Name</th>
                                    <th class="border-b border-l border-black pl-2 pr-2">Seasons</th>
                                    @if (Auth::check() && Auth::id() == $league->league_owner_id)
                                        <th class="border-b border-l"></th>
                                    @endif
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach ($seasons as $leagues_season)
                                    <tr>
                                        <td class="border-r border-black">
                                            <a href="{{ route('season.showSeason', ['id' => $leagues_season->id]) }} " class="text-blue-500 underline p-2"> {{  $leagues_season->season_name }} </a>
                                        </td>
                                        <td class="pr-1 pl-2">Season {{ $leagues_season->season_count }}</td>
                                        @if (Auth::check() && Auth::id() == $league->league_owner_id)
                                        <td>
                                            <form method="POST" action="{{$league->leagueId}}/{{$leagues_season->id}}/delete" enctype="multipart/form-data">
                                                @csrf
                                                <input class="hidden" name="userId" value="{{Auth::id()}}">
                                                <input class="hidden" name="leagueId" value="{{$league->leagueId}}">
                                                <input class="hidden" name="seasonId" value="{{$leagues_season->id}}">
                                                <button id="delete-button" class="text-red-500 px-2">Delete</button>
                                            </form>
                                        </td>
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
        {{-- <footer class="h-48 bg-gray-100">Footer</footer> --}}
      </div>
      <script>

      </script>
</x-app-layout>
</html>
