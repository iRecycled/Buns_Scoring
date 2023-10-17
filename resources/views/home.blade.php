<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buns Scoring</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
  @php
  $leagues = DB::table('leagues')->get();
@endphp
</head>
    <x-app-layout class="flex flex-col min-h-screen">

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">

          </main>
          <div class="flex-1 p-4 sm:w-64">
            <div class="flex flex-row p-4 bg-white rounded-xl justify-between items-center">
                <h1 class="text-4xl mx-auto">Welcome to Buns Scoring!</h1>
                @if (Auth::check())
                <a href="league/create-league" class="text-lg p-2 float-right bg-blue-400 hover:bg-blue-500 rounded-xl text-gray-100">Create a League</a>
                @endif
            </div>
                <div class="p-5 my-10 bg-gray-300 rounded-3xl">
                    <h4 class="text-xl">Current Leagues: </h4>
                    @foreach ($leagues as $league)
                    <table>
                        <tr>
                            <a href="{{ route('league.showLeague', ['leagueId' => $league->leagueId]) }} " class="text-blue-500 underline py-6"> {{  $league->name }} </a>
                        </tr>
                    </table>
                    @endforeach

                </div>
            <div>
                <img src="{{ asset('f3.png') }}">
            </div>
        </div>
        <div class="flex-2 w-64">

        </div>
        </div>

        <footer class="h-18 bg-gray-100"></footer>
      </div>

</x-app-layout>
</html>
