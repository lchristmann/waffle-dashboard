<x-filament-panels::page>
    @php
        // Group images by year
        $imagesByYear = $this->getImages()->groupBy(fn ($image) => $image->date->year);
        // Sort by year descending (latest year first)
        $imagesByYear = $imagesByYear->sortKeysDesc();
    @endphp

    @foreach ($imagesByYear as $year => $images)
        <h2 class="fi-header-subheading">{{ $year }}</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($images as $image)
                <div class="rounded-md overflow-hidden shadow">
                    <img alt="Gallery Image"
                        src="{{ route('gallery.image', $image) }}"
                        class="w-full h-40 object-cover"
                    />

                    <div class="p-2 text-xs text-gray-500">
                        {{ $image->date->toFormattedDateString() }}
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

</x-filament-panels::page>
