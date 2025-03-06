<div>
    <div class="mt-10 mb-10">
        <div class="text-4xl text-center font-kushan">List of Tour Operators</div>
        <div class="text-sm text-center">{{ count($operators) }} Operator{{ count($operators) == 1 ? '' : 's' }}</div>
    </div>
    <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-4">
        @foreach ($operators as $operator)
            <div wire:click="onGuideClick({{ $operator }})"
                class="flex flex-row justify-between p-2 pt-4 transition duration-300 ease-in-out border border-card-border hover:shadow-xl hover:cursor-pointer hover:scale-105">
                <div class="flex-col">
                    <div class="flex flex-row">
                        <div class="flex-col p-2"><img src="/image/tour_operators.png" alt="guide" class="w-10 h-10">
                        </div>
                        <div class="flex-col">
                            <div class="text-xl font-bold">{{ $operator->name }} <span
                                    class="text-orange rounded-full bg-[#fff8ed] text-sm pl-2 pr-2">{{ $operator->average_ratings }}
                                    &#9733;</span></div>
                            <div class="text-colorx text-[10px] bg-reg-background rounded-full pl-1 pr-1 text-center">
                                {{ $operator->license }}</div>
                            <div class="mt-4">
                                <ul>
                                    <li class="location hover:text-colorx">
                                        <a href="https://maps.google.com/maps?q={{ $operator->address }};"
                                            target="_blank">
                                            {{ $operator->address }}</a>
                                    </li>
                                    <li class="phone">
                                        <a class="hover:underline" href="tel:{{ $operator->phone_one }}">
                                            {{ $operator->phone_one }}
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
        {{ $operators->links() }}
    </div>

    <x-jet-dialog-modal wire:model="ratingDialog">
        <x-slot name="title">
            <div class="mb-4 text-xl font-bold text-black">
                Tour Operator
            </div>
        </x-slot>
        <x-slot name="content">
            <div class="grid grid-cols-2">
                <div class="">
                    <div class="flex flex-row">
                        <div class="flex-col p-2">
                            <img src="/image/tour_operators.png" alt="guide" class="w-10 h-10">
                        </div>
                        <div class="flex-col">
                            <div class="text-xl font-bold">
                                {{ $selectedOperator != null ? $selectedOperator['name'] : $operator->name }}</div>
                            <div class="text-colorx text-[10px] bg-reg-background rounded-full pl-1 pr-1">
                                {{ $selectedOperator != null ? $selectedOperator['license'] : $operator->license }}
                            </div>
                            <div class="mt-4">
                                <ul>
                                    <li class="location hover:text-colorx">
                                        <a href="https://maps.google.com/maps?q={{ $selectedOperator != null ? $selectedOperator['address'] : $operator->address }};"
                                            target="_blank">
                                            {{ $selectedOperator != null ? $selectedOperator['address'] : $operator->address }}</a>
                                    </li>
                                    <li class="phone">
                                        <a class="hover:underline"
                                            href="tel: {{ $selectedOperator != null ? $selectedOperator['phone_one'] : $operator->phone_one }}">
                                            {{ $selectedOperator != null ? $selectedOperator['phone_one'] : $operator->phone_one }}
                                        </a>/
                                        <a class="hover:underline"
                                            href="tel: {{ $selectedOperator != null ? $selectedOperator['phone_one'] : $operator->phone_one }}">
                                            {{ $selectedOperator != null ? $selectedOperator['phone_two'] : $operator->phone_two }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-gray-400">OVERALL RATING</div>
                    <div class="text-4xl font-bold text-center text-orange">
                        {{ $selectedOperator != null ? $selectedOperator['average_ratings'] : $operator->average_ratings }}
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
