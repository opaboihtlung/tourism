<div>
    <div class="grid grid-cols-2 p-4 sm:grid-cols-2 md:grid-cols-3 gap-7">
        @foreach ($guides as $guide)
            <div wire:click="onGuideClick({{ $guide }})"
                class="flex flex-row justify-between p-2 pt-4 transition duration-300 ease-in-out border border-card-border hover:shadow-xl pb-7 hover:cursor-pointer hover:scale-105">
                <div class="flex-col">
                    <div class="flex flex-row">
                        <div class="flex-col p-2"><img src="/image/guides.png" alt="guide" class="w-10 h-10"></div>
                        <div class="flex-col">
                            <div class="text-xl font-bold">{{ $guide->name }} <span
                                    class="text-orange rounded-full bg-[#fff8ed] text-sm pl-2 pr-2">{{ $guide->average_ratings }}
                                    &#9733;</span></div>
                            <div class="text-colorx text-[10px] bg-reg-background rounded-full pl-1 pr-1 w-1/2">
                                {{ $guide->license }}
                            </div>
                            <div class="mt-4">
                                <ul>
                                    <li class="location hover:text-colorx">
                                        <a href="https://maps.google.com/maps?q={{ $guide->address }};" target="_blank">
                                            {{ $guide->address }}</a>
                                    </li>
                                    <li class="phone">
                                        <a class="hover:underline" href="tel:{{ $guide->phone_one }}">
                                            {{ $guide->phone_one }}
                                        </a> /
                                        <a class="hover:underline" href="tel:{{ $guide->phone_two }}">
                                            {{ $guide->phone_two }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $guides->links() }}
    </div>
    <x-jet-dialog-modal wire:model="ratingDialog">
        <x-slot name="title">
            <div class="mb-4 text-xl font-bold text-black">
                Tour Guide
            </div>
        </x-slot>
        <x-slot name="content">
            <div class="grid grid-cols-2">
                <div class="">
                    <div class="flex flex-row">
                        <div class="flex-col p-2">
                            <img src="/image/guides.png" alt="guide" class="w-10 h-10">
                        </div>
                        <div class="flex-col">
                            <div class="text-xl font-bold">
                                {{ $selectedGuide != null ? $selectedGuide['name'] : $guide->name }}</div>
                            <div class="text-colorx text-[10px] bg-reg-background rounded-full pl-1 pr-1">
                                {{ $selectedGuide != null ? $selectedGuide['license'] : $guide->license }}</div>
                            <div class="mt-4">
                                <ul>
                                    <li class="location">
                                        {{ $selectedGuide != null ? $selectedGuide['address'] : $guide->address }}</li>
                                    <li class="phone">
                                        {{ $selectedGuide != null ? $selectedGuide['phone_one'] : $guide->phone_one }}
                                        /
                                        {{ $selectedGuide != null ? $selectedGuide['phone_two'] : $guide->phone_two }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-gray-400">OVERALL RATING</div>
                    <div class="text-4xl font-bold text-center text-orange">
                        {{ $selectedGuide != null ? $selectedGuide['average_ratings'] : $guide->average_ratings }}
                        <span class="text-xl">&#9733;</span>
                    </div>
                </div>
            </div>
            <div class="p-4 text-center bg-gray-200">
                Give Rating
                <div class="">
                    <div class="text-orange">
                        @for ($i = 0; $i < $userRating; $i++)
                            <span class="text-2xl hover:cursor-pointer"
                                wire:click="onRatingClicked({{ $i + 1 }})">&#9733;</span>
                        @endfor
                        @for ($i = 0; $i < 5 - $userRating; $i++)
                            <span class="text-2xl hover:cursor-pointer"
                                wire:click="onRatingClicked({{ $i + $userRating + 1 }})">&#9734;</span>
                        @endfor
                    </div>
                    <textarea wire:model="review" name="" id="" cols="30" rows="4" class="bg-gray-200"
                        placeholder="Review"></textarea>
                </div>
                <button class="p-2 pl-4 pr-4 text-white bg-colorx" wire:click="giveRating">Rate Now</button>
            </div>
        </x-slot>
    </x-jet-dialog-modal>
</div>
