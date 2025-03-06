@extends('layouts.layout')
@section('header')
    <img src="/image/tour-guide.png" alt="district image" class="object-cover w-full h-80 district-header">
    <div class="absolute w-full text-center text-white z-1 top-60">
        <div class="text-4xl font-kushan">
            Tour Guides
        </div>
        <div class="text-lg">Plan Your Trip</div>
    </div>
@endsection
@section('contents')
    <div class="container mx-auto mb-10">
        <div class="mt-10 mb-10">
            <div class="p-4 text-4xl text-center font-kushan">Certified tour guide list</div>
            <div class="text-sm text-center">{{ $totalGuides }}as Guides</div>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($guides as $guide)
                <div
                    class="flex flex-row p-2 pt-4 transition duration-300 ease-in-out border border-card-border hover:shadow-xl hover:cursor-pointer hover:scale-105">
                    <div class="flex-col p-2"><img src="/image/guides.png" alt="guide" class="w-10 h-10"></div>
                    <div class="flex-col">
                        <div class="text-xl font-bold">{{ $guide->name }}</div>
                        <div class="text-colorx text-[10px] bg-reg-background rounded-full pl-1 pr-1">{{ $guide->license }}
                        </div>
                        <div class="mt-4">
                            <ul>
                                <li class="location">{{ $guide->address }}</li>

                                <li class="phone hover:underline">
                                    <a href="tel:{{ $guide->phone_one }}">{{ $guide->phone_one }} /
                                        {{ $guide->phone_two }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $guides->links() }}
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .location::before {
            content: '\00a0\00a0\00a0\00a0\00a0\00a0\00a0\00a0';
            background-image: url('/image/location.png');
            background-size: contain;
            background-repeat: no-repeat;
        }

        .phone::before {
            content: '\00a0\00a0\00a0\00a0\00a0\00a0\00a0\00a0';
            background-image: url('/image/phone.png');
            background-size: contain;
            background-repeat: no-repeat;
        }
    </style>
@endsection
