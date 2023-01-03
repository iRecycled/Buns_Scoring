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
                                <span class="font-medium">There was a problem creating your league</span>
                                <ul class="mt-1.5 ml-4 text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{!! $error !!}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('success'))
                    <div class="flex p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-red-800" role="alert">
                        <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Error</span>
                        <div>
                            <span class="font-medium">League has been created!</span>
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
                            <h1 class="text-4xl mx-auto">Create a League</h1>
                        </div>
                    <form method="POST" action="{{ route('create') }}">
                            {{ csrf_field() }}
                        <p class="mt-5"><span class="text-red-600">* </span> League Name</p>
                        <input type="text" name="name" class="text-xl" required>

                        <p class="mt-5">League Description</p>
                        <textarea name="description" class="text-xl" rows="4" cols="50"></textarea>

                        <p class="mt-5"><span class="text-red-600">* </span>iRacing League ID</p>
                        <input type="text" name="leagueId" class="text-xl" required rows="4" cols="50"></textarea>

                        {{-- <p class="mt-5"><span class="text-red-600">* </span>iRacing Email</p>
                        <input type="email" name="email" class="text-xl" required rows="4" cols="50"></textarea>

                        <p class="mt-5"><span class="text-red-600">* </span>iRacing Password</p>
                        <input type="password" name="password" class="text-xl" required rows="4" cols="50"></textarea> --}}
                        <br>
                        <button class="mt-5 bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 mb-10">Create</button>
                    </form>
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
