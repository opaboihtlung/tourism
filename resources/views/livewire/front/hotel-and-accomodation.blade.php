<div class="mt-10 mb-10">
    <div class="flex flex-row justify-center gap-10">
        <div wire:click="changeType('Hotel')"
            class="{{ $type == 'Hotel' ? 'border-b-2 text-black' : 'border-none text-whitish-gray' }} font-bold flex-cols-1 border-colorx text-right hover:cursor-pointer">
            Hotel
        </div>
        <div wire:click="changeType('Homestay')"
            class="{{ $type == 'Homestay' ? 'border-b-2 text-black' : 'border-none text-whitish-gray' }} font-bold flex-cols-1 border-colorx hover:cursor-pointer">
            Homestay
        </div>
    </div>
    <div class="mt-10 mb-10">
        <div class="text-4xl text-center font-kushan">List of {{ $type }}</div>
        <div class="text-sm text-center">{{ count($accomodations) }} {{ $type }}</div>
    </div>
    @if ($type == 'Hotel')
        <div class="m-4">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-12">
                @foreach ($districts as $district)
                    <div wire:click="getData({{ $district->id }})"
                        class="{{ $this->districtsId == $district->id ? 'border-colorx' : 'border-district-outline' }} border pl-4 pr-4 p-2 rounded-lg text-center hover:cursor-pointer hover:shadow-lg font-bold hover:scale-105 transition duration-300 ease-in-out">
                        {{ $district->district_name }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-2 md:grid-cols-3">
        @if (count($accomodations) == 0)
            <div class="text-xs italic">
                No operators found
            </div>
        @endif
        @foreach ($accomodations as $accomodation)
            <div
                class="flex flex-row p-2 pt-4 transition duration-300 ease-in-out border border-card-border hover:shadow-xl hover:cursor-pointer hover:scale-105">
                <div class="flex-col p-2"><img src="/image/icons/hotel.png" alt="guide" class="w-10 h-10"></div>
                <div class="flex-col">
                    <div class="text-xl font-bold">{{ $accomodation->name }}</div>
                    <div class="mt-4">
                        <ul>
                            <li class="location hover:text-colorx">
                                <a href="https://maps.google.com/maps?q={{ $accomodation->address }};" target="_blank">
                                    {{ $accomodation->address }}</a>
                            </li>
                            <li class="phone">
                                <a class="hover:underline"
                                    href="tel:{{ $type == 'Hotel' ? $accomodation->phone_one : $accomodation->phone }}">
                                    {{ $type == 'Hotel' ? $accomodation->phone_one : $accomodation->phone }}
                                </a>
                            </li>
                            </li>
                            <li class="mail">{{ $accomodation->email }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
