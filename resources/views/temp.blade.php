<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Form</title>
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>
    <div class="flex flex-col min-h-screen">
        <header class="bg-white-50 ">
            <div class="flex flex-wrap justify-center p-4">
                <ul class="mx-10"><a href="#">Home</a></ul>
                <ul class="mx-10"><a href="#">League</a></ul>
            </div>
        </header>

        <div class="flex flex-row flex-1">
          <main class="w-64 bg-white-100 flex-0 sm:flex-2">
            <div class="bg-blue-100 p-5 rounded-xl">
            <div class="flex flex-wrap justify-center p-5">
                <h1 class="text-2xl py-4">Add a League!</h1>
                <button class="p-2 px-5 bg-blue-400 rounded-lg"> Create </button>
            </div>

        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="flex flex-wrap justify-center p-2">
                <H1 class="text-2xl py-4">Import Json file</H1>
                <button class="p-2 px-5 bg-blue-400 rounded-lg" type="submit" value="Upload"> Upload </button>
            </div>
        </form>
            </div>
          </main>

          <nav class="flex-1 p-4 sm:w-64">
            <div class="flex flex-row justify-center p-4 bg-white rounded-xl">
                <h1 class="text-4xl">Welcome to Buns Scoring!</h1>
            </div>
            <div class="p-5 my-10 bg-red-200 rounded-3xl">
                <h4 class="text-xl">Created specifically for League Zero scoring</h4>
            </div>
            <div>
                <img src="{{ asset('f3.png') }}">
            </div>
          </nav>
        </div>
        <footer class="h-48 bg-gray-100">Footer</footer>
      </div>
</html>
