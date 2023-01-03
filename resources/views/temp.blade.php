<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Form</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>

    <x-app-layout class="flex flex-col min-h-screen">

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">
            <div class="bg-blue-100 p-5 rounded-xl">
            <div class="flex flex-wrap justify-center p-5">
                <h1 class="text-2xl py-4">Add a League!</h1>
                <button class="p-2 px-5 bg-blue-400 rounded-lg"> Create </button>
            </div>

            <div class="flex flex-col justify-center p-2 items-center">
                <h1 class=" text-2xl py-4">Add Race</h1>
                <a class=" bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg" id="modal-button">Add</a>
            </div>

                <!-- Modal container -->
                <div id="modal" class="hidden fixed top-0 left-0 w-full h-full flex items-center justify-center">
                    <form method="POST" action="{{ route('formSubmit') }}">
                        {{ csrf_field() }}
                    <!-- Modal content -->
                    <div id="main-modal" class="bg-gray-400 rounded-lg p-3 max-w-xl mx-auto shadow-xl overflow-y-auto">
                        <a class="float-right pr-2" href="" id="close">X</a>
                        <h2 class="text-2xl font-bold py-4">iRacing Race Id</h2>
                        <input type="text" name="Racing_Id" class="border rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="modal-input">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-5 rounded focus:outline-none focus:shadow-outline items-center" id="modal-submit">Submit</button>
                    </div>
                </form>
                </div>

            </div>
    </main>
            <script>
                // Get the modal, button, and input elements
                const modal = document.getElementById('modal');
                const popup = document.getElementById('main-modal');
                const button = document.getElementById('modal-button');
                const input = document.getElementById('modal-input');
                const submit = document.getElementById('modal-submit');

                // // When the button is clicked, toggle the modal's visibility
                // button.addEventListener('click', () => {
                // if (modal.classList.contains('hidden')) {
                //     modal.classList.remove('hidden');
                //     modal.classList.add('modal-open');
                // }
                // });

                // close.addEventListener('click', () => {
                //     modal.classList.add('hidden');
                //     modal.classList.remove('modal-open');
                // })

                // modal.addEventListener('click', (event) => {
                //     if(!popup.contains(event.target)){
                //         modal.classList.add('hidden');
                //         modal.classList.remove('modal-open');
                //     }
                // });

              </script>

          <div class="flex-1 p-4 sm:w-64">
            <div class="flex flex-row p-4 bg-white rounded-xl justify-between items-center">
                <h1 class="text-4xl mx-auto">Welcome to Buns Scoring!</h1>
                <a href="{{ url('league/create') }}" class="p-2 rounded-xl bg-blue-200">Create a League</a>
            </div>
            <div class="p-5 my-10 bg-red-200 rounded-3xl">
                <h4 class="text-xl">Created specifically for League Zero scoring</h4>
            </div>
            <div>
                <img src="{{ asset('f3.png') }}">
            </div>
        </div>
        </div>
        <footer class="h-48 bg-gray-100">Footer</footer>
      </div>
</x-app-layout>
</html>
