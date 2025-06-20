<x-app-layout>
    <div x-data="{ currentIndex: 0, slides: ['{{ asset('favicons/images/1.jpg') }}', '{{ asset('favicons/images/2.jpg') }}', '{{ asset('favicons/images/3.jpg') }}'] }" class="py-8 px-4">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Carrusel con altura ajustada -->
            <div class="relative w-full h-96">
                <div class="relative w-full h-full overflow-hidden rounded-lg">
                    <!-- Slides -->
                    <template x-for="(slide, index) in slides" :key="index">
                        <div :class="{ 'hidden': currentIndex !== index }" class="absolute inset-0 transition-opacity duration-700 ease-in-out">
                            <img :src="slide" class="w-full h-full object-cover" :alt="'Slide ' + (index + 1)">
                        </div>
                    </template>
                </div>
                <!-- Controles -->
                <button @click="currentIndex = (currentIndex > 0) ? currentIndex - 1 : slides.length - 1"
                        class="absolute top-1/2 left-0 z-30 flex items-center justify-center w-10 h-10 -translate-y-1/2 bg-gray-800 text-white cursor-pointer rounded-full">
                    <span class="sr-only">Previous</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button @click="currentIndex = (currentIndex < slides.length - 1) ? currentIndex + 1 : 0"
                        class="absolute top-1/2 right-0 z-30 flex items-center justify-center w-10 h-10 -translate-y-1/2 bg-gray-800 text-white cursor-pointer rounded-full">
                    <span class="sr-only">Next</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <x-footer />
</x-app-layout>

<!-- Tailwind CSS and Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
