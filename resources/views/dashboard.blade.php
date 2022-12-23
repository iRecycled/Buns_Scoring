<x-app-layout>
    <!-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot> -->
<script src="https://unpkg.com/tailwindcss-jit-cdn"></script>

<!-- Snippet min-h-screen -->
<section class="flex flex-col justify-center antialiased bg-gray-100 text-gray-600  p-4">
    <div class="h-full">
        <!-- Pricing -->
        <div class="max-w-5xl mx-auto" x-data="{ annual: true }">
            <h2 class="text-3xl text-gray-800 font-bold text-center mb-4">Plans</h2>
            <!-- Toggle switch -->
            <!-- <div class="flex justify-center">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="text-sm text-gray-500 font-medium min-w-[120px] text-right">Monthly</div>
                    <div class="relative select-none w-[44px]">
                        <input type="checkbox" id="toggle" class="peer sr-only" x-model="annual" />
                        <label class="block overflow-hidden cursor-pointer h-6 rounded-full bg-gray-400 peer-checked:bg-indigo-500" for="toggle">
                            <span class="absolute block rounded-full w-5 h-5 top-0.5 left-0.5 right-1/2 bg-white shadow-sm transition-all duration-150 ease-out" aria-hidden="true"></span>
                            <span class="sr-only">Pay annually</span>
                        </label>
                    </div>
                    <div class="text-sm text-gray-500 font-medium min-w-[120px]">Annually <span class="text-green-500">(-20%)</span></div>
                </div>
            </div> -->
            <!-- Pricing tabs -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Tab 1 -->
                <div class="relative col-span-full md:col-span-4 bg-white shadow-md rounded-sm border border-gray-200">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-green-500" aria-hidden="true"></div>
                    <div class="px-5 pt-5 pb-6 border-b border-gray-200">
                        <header class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full flex-shrink-0 bg-gradient-to-tr from-green-500 to-green-300 mr-3">
                                <svg class="w-6 h-6 fill-current text-white" viewBox="0 0 24 24">
                                    <path d="M12 17a.833.833 0 01-.833-.833 3.333 3.333 0 00-3.334-3.334.833.833 0 110-1.666 3.333 3.333 0 003.334-3.334.833.833 0 111.666 0 3.333 3.333 0 003.334 3.334.833.833 0 110 1.666 3.333 3.333 0 00-3.334 3.334c0 .46-.373.833-.833.833z" />
                                </svg>
                            </div>
                            <h3 class="text-lg text-gray-800 font-semibold">Basic</h3>
                        </header>
                        <div class="text-sm mb-2">Ideal for individuals that need a custom solution with custom tools.</div>
                        <!-- Price -->
                        <div class="text-gray-800 font-bold mb-4">
                            <span class="text-2xl">$</span><span class="text-3xl" x-text="annual ? '14' : '19'">14</span><span class="text-gray-500 font-medium text-sm">/mo</span>
                        </div>
                        <!-- CTA -->
                        <button class="font-medium text-sm inline-flex items-center justify-center px-3 py-2 border border-gray-200 rounded leading-5 shadow-sm transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 hover:border-gray-300 text-gray-600 w-full">Downgrade</button> <!--    -->
                    </div>
                    <div class="px-5 pt-4 pb-5">
                        <div class="text-xs text-gray-800 font-semibold uppercase mb-4">What's included</div>
                        <!-- List -->
                        <ul>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Tab 2 -->
                <div class="relative col-span-full md:col-span-4 bg-white shadow-md rounded-sm border border-gray-200">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-500" aria-hidden="true"></div>
                    <div class="px-5 pt-5 pb-6 border-b border-gray-200">
                        <header class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full flex-shrink-0 bg-gradient-to-tr from-blue-500 to-blue-300 mr-3">
                                <svg class="w-6 h-6 fill-current text-white" viewBox="0 0 24 24">
                                    <path d="M12 17a.833.833 0 01-.833-.833 3.333 3.333 0 00-3.334-3.334.833.833 0 110-1.666 3.333 3.333 0 003.334-3.334.833.833 0 111.666 0 3.333 3.333 0 003.334 3.334.833.833 0 110 1.666 3.333 3.333 0 00-3.334 3.334c0 .46-.373.833-.833.833z" />
                                </svg>
                            </div>
                            <h3 class="text-lg text-gray-800 font-semibold">Standard</h3>
                        </header>
                        <div class="text-sm mb-2">Ideal for individuals that need a custom solution with custom tools.</div>
                        <!-- Price -->
                        <div class="text-gray-800 font-bold mb-4">
                            <span class="text-2xl">$</span><span class="text-3xl" x-text="annual ? '34' : '39'">34</span><span class="text-gray-500 font-medium text-sm">/mo</span>
                        </div>
                        <!-- CTA -->
                        <button class="font-medium text-sm inline-flex items-center justify-center px-3 py-2 border border-transparent rounded leading-5 shadow-sm transition duration-150 ease-in-out border-gray-200 focus:outline-none focus-visible:ring-2 bg-gray-100 text-gray-400 w-full cursor-not-allowed flex items-center" disabled>
                            <svg class="w-3 h-3 flex-shrink-0 fill-current mr-2" viewBox="0 0 12 12">
                                <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                            </svg>
                            <span>Current Plan</span>
                        </button>
                    </div>
                    <div class="px-5 pt-4 pb-5">
                        <div class="text-xs text-gray-800 font-semibold uppercase mb-4">What's included</div>
                        <!-- List -->
                        <ul>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Tab 3 -->
                <div class="relative col-span-full md:col-span-4 bg-white shadow-md rounded-sm border border-gray-200">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-indigo-500" aria-hidden="true"></div>
                    <div class="px-5 pt-5 pb-6 border-b border-gray-200">
                        <header class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full flex-shrink-0 bg-gradient-to-tr from-indigo-500 to-indigo-300 mr-3">
                                <svg class="w-6 h-6 fill-current text-white" viewBox="0 0 24 24">
                                    <path d="M12 17a.833.833 0 01-.833-.833 3.333 3.333 0 00-3.334-3.334.833.833 0 110-1.666 3.333 3.333 0 003.334-3.334.833.833 0 111.666 0 3.333 3.333 0 003.334 3.334.833.833 0 110 1.666 3.333 3.333 0 00-3.334 3.334c0 .46-.373.833-.833.833z" />
                                </svg>
                            </div>
                            <h3 class="text-lg text-gray-800 font-semibold">Plus</h3>
                        </header>
                        <div class="text-sm mb-2">Ideal for individuals that need a custom solution with custom tools.</div>
                        <!-- Price -->
                        <div class="text-gray-800 font-bold mb-4">
                            <span class="text-2xl">$</span><span class="text-3xl" x-text="annual ? '74' : '79'">74</span><span class="text-gray-500 font-medium text-sm">/mo</span>
                        </div>
                        <!-- CTA -->
                        <button class="font-medium text-sm inline-flex items-center justify-center px-3 py-2 border border-transparent rounded leading-5 shadow-sm transition duration-150 ease-in-out bg-indigo-500 focus:outline-none focus-visible:ring-2 hover:bg-indigo-600 text-white w-full">Upgrade</button>
                    </div>
                    <div class="px-5 pt-4 pb-5">
                        <div class="text-xs text-gray-800 font-semibold uppercase mb-4">What's included</div>
                        <!-- List -->
                        <ul>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Lorem ipsum dolor sit amet</div>
                            </li>
                            <li class="flex items-center py-1">
                                <svg class="w-3 h-3 flex-shrink-0 fill-current text-green-500 mr-2" viewBox="0 0 12 12">
                                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                                </svg>
                                <div class="text-sm">Quis nostrud exercitation</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</x-app-layout>
