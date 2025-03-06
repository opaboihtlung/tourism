<a href="{{ Route('newsDetails', $newsEvent) }}">
  <div class="flex-col m-2 transition duration-300 ease-in-out border hover:shadow-xl border-card-border hover:cursor-pointer hover:scale-105">
    @if (count($newsEvent->images) != 0)
    @foreach ($newsEvent->images as $image)
      <img src="/post_images/{{ $image->name }}" alt="vantawng" class="object-cover w-full h-72">
      @break
    @endforeach
    @else
    <img src="/image/tourism_cheraw.png" class="object-cover w-full h-72" alt="...">
    @endif
    <div class="p-4">
      <div class="w-16 text-center rounded-full text-tourism-green bg-light-green">{{ $newsEvent->category->name }}</div>
      <div class="text-lg font-bold">{{ $newsEvent->title }}</div>
    </div>
  </div>
</a>