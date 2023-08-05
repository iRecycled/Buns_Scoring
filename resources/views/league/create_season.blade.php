<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Form</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
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
                        <div class="flex flex-row p-4 rounded-xl justify-center items-center">
                            <h1 class="text-4xl mx-auto">Create a Season</h1>
                        </div>
                    <form method="POST" action="{{route('createSeason', ['leagueId' => $league->first()->leagueId])}}">
                            {{ csrf_field() }}
                        <p class="mt-5"><span class="text-red-600">* </span> Season Name</p>
                        <input type="text" name="season_name" class="text-xl" required>
                        <input type="text" class="hidden" name="season_count" value="{{ ++$count }}">
                        <br>
                        <div class="flex flex-row p-4 rounded-xl justify-center items-center">
                            <h1 class="text-4xl mx-auto">Season Scoring</h1>
                        </div>
                        <div class="grid grid-cols-4 grid-flow-row gap-2">
                            @for ($i = 1; $i < 64; $i++)
                            <div class="flex items-center w-auto">
                                <p class="p-2">#{{$i}}</p>
                                <input type="text" name="column{{$i}}[]" class="bg-black rounded-lg w-14" value="0">
                            </div>
                            @endfor
                        </div>
                        {{-- @for($i = 1; $i < 64; $i++)
                                <tr class="text-center p-2">
                                  <td class="border px-4 py-2">{{$i}}</td>
                                  <td class="border px-4 py-2">
                                    <input type="text" name="column{{$i}}[]" class="bg-black rounded-lg p-2">
                                  </td>
                                </tr>
                        @endfor --}}
                        <br>
                        <button class="mt-5 bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 mb-10">Create</button>
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

