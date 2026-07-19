@if ($categories->count() > 0)
<div class="category-pills-bar">
    <div class="category-pills-list">
        <a class="category-pill active" href="{{ route("frontend.home") }}">
            All Topics
        </a>
        @foreach ($categories as $category)
        <a class="category-pill" href="{{ route("frontend.category", $category->slug) }}">
            @if($category->icon)
                <i class="{{ $category->icon }} mr-1"></i>
            @endif
            {{ $category->title }}
            @if(count($category->posts->where("status", true)) > 0)
                <span>{{ count($category->posts->where("status", true)) }}</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif
