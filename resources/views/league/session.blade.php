<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
      $league = DB::table('leagues')->where('id',$leagueId)->first();
      $users = DB::table('sessions')->where('subsession_id', '=', $sessionId)->get();
  @endphp
  <title> {{ $league->name }}</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>

    <x-app-layout class="flex flex-col min-h-screen">

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">

          </main>
          <div class="flex-1 p-4 sm:w-64">
            <div class="p-4 bg-white rounded-xl items-center justify-content-between">
                <div class="flex flex-row items-center">
                    <div class="flex flex-1 items-center justify-center">
                        <h1 class="text-4xl font-bold text-center">{{ $league->name }}</h1>
                    </div>
                </div>
            </div>
            <div class="p-5 my-10  flex justify-center items-center">
                    <div class="bg-gray-300 py-4 px-8 rounded-3xl">
                        <div class="flex flex-row p-4 rounded-xl justify-center items-center">
                                <tr class="m-2">
                                  <table class="pl-2">
                                    <thead>
                                      <tr>
                                        <th class="pr-10">Pos</th>
                                        <th class="pr-10 ">Driver</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      @foreach ($users as $user)
                                        <tr>
                                          <td>{{ $user->finish_position }}</td>
                                          <td>{{ $user->display_name }}</td>
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
</x-app-layout>
</html>
