@extends('layouts.layout')
@section('header')
  <div class="relative w-full h-[20rem] overflow-hidden">
    <img src="/image/tour-guide.png" alt="Avatar" class="object-cover w-full h-full" />
    <div class="absolute top-0 w-full h-full text-6xl text-center text-white z-1 font-kushan bg-gradient-to-t from-black">
      <div class="absolute w-full text-center top-32">
        <div class="text-4xl font-kushan">
          Tour Guides
        </div>
        <div class="text-lg">Plan Your Trip</div>
      </div>
    </div>
  </div>
@endsection
@section('contents')
  <div class="container mx-auto mb-10">
    <div class="mt-10 mb-10">
      <div class="p-4 text-4xl text-center font-kushan">Certified tour guide list</div>
      <div class="text-sm text-center">{{ $totalGuides }} Guides</div>
    </div>
    @livewire('guide-rating')
  </div>
@endsection

@section('styles')
  <style>
    .location::before{
        content: '\00a0\00a0\00a0\00a0\00a0\00a0\00a0\00a0';
        background-image: url('/image/location.png');
        background-size: contain;
        background-repeat: no-repeat;
      }

      .phone::before{
        content: '\00a0\00a0\00a0\00a0\00a0\00a0\00a0\00a0';
        background-image: url('/image/phone.png');
        background-size: contain;
        background-repeat: no-repeat;
      }
  </style>
@endsection