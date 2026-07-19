@extends("frontend.master", ['isBlankTemplate' => ($page->template === 'blank')])

@section("title", $page->title." - ".config('app.sitesettings')::first()->site_title)
@section("meta_description", \Illuminate\Support\Str::limit(strip_tags($page->content), 150))
@push('head')
<meta name="page-entity-type" content="App\Models\Page">
<meta name="page-entity-id" content="{{ $page->id }}">
@endpush
@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('frontend.home') }}" },
        { "@type": "ListItem", "position": 2, "name": "{{ $page->title }}", "item": "{{ request()->url() }}" }
      ]
    },
    {
      "@type": "WebPage",
      "@id": "{{ request()->url() }}#webpage",
      "name": "{{ $page->title }}",
      "url": "{{ request()->url() }}",
      "description": "{{ \Illuminate\Support\Str::limit(strip_tags($page->content), 150) }}",
      "dateModified": "{{ $page->updated_at->toIso8601String() }}"
    }
  ]
}
</script>
@endsection

@section("content")
@if($page->template !== 'landing' && $page->template !== 'blank')
<div class="section-heading">
    <div class="container-fluid">
         <div class="section-heading-2">
             <div class="row">
                 <div class="col-lg-12">
                     <div class="section-heading-2-title">
                         <h1>{{ $page->title }}</h1>
                         <p class="links"><a href="{{ route("frontend.home") }}">Home <x-icon name="chevron-right" width="12" height="12" /></a> {{ $page->title }}</p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
</div>
@endif

@if($page->sections && $page->sections->count() > 0)
    <div class="page-sections-container">
        @foreach($page->sections as $section)
            @if($section->status === 'Published')
                <div class="page-section section-type-{{ $section->type }} style-{{ $section->layout_style }} py-5" id="section-{{ $section->id }}">
                    <div class="{{ $section->layout_style === 'full-width' ? 'container-fluid' : 'container' }}">
                        @if($section->type === 'html')
                            {!! $section->items->first()?->content !!}
                        @elseif($section->type === 'css')
                            <style>{!! $section->items->first()?->content !!}</style>
                        @elseif($section->type === 'markdown')
                            <div class="markdown-body">
                                {!! class_exists('Parsedown') ? (new \Parsedown())->text($section->items->first()?->content ?? '') : nl2br(e($section->items->first()?->content ?? '')) !!}
                            </div>
                        @elseif($section->type === 'hero')
                            @foreach($section->items as $item)
                                <div class="text-center py-5">
                                    @if($item->title)<h1 class="display-4 font-weight-bold mb-3">{{ $item->title }}</h1>@endif
                                    @if($item->subtitle)<p class="lead text-muted mb-4">{{ $item->subtitle }}</p>@endif
                                    @if($item->content)<div class="mb-4">{!! $item->content !!}</div>@endif
                                    @if($item->link)<a href="{{ $item->link }}" class="btn btn-primary btn-lg">Learn More</a>@endif
                                </div>
                            @endforeach
                        @elseif($section->type === 'features')
                            <div class="row">
                                @foreach($section->items as $item)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 shadow-sm border-0" style="background:var(--card-background,#fff); border-radius:8px;">
                                            @if($item->image)<img src="{{ $item->image }}" class="card-img-top" alt="{{ $item->title }}">@endif
                                            <div class="card-body">
                                                @if($item->title)<h5 class="font-weight-bold">{{ $item->title }}</h5>@endif
                                                @if($item->subtitle)<h6 class="text-muted mb-2">{{ $item->subtitle }}</h6>@endif
                                                @if($item->content)<div>{!! $item->content !!}</div>@endif
                                                @if($item->link)<a href="{{ $item->link }}" class="btn btn-sm btn-link px-0 mt-2">Explore <i class="fas fa-arrow-right ml-1"></i></a>@endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @foreach($section->items as $item)
                                <div class="section-generic-item mb-4">
                                    @if($item->title)<h3 class="font-weight-bold">{{ $item->title }}</h3>@endif
                                    @if($item->subtitle)<h5 class="text-muted">{{ $item->subtitle }}</h5>@endif
                                    @if($item->content)<div>{!! $item->content !!}</div>@endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
    <section class="post-single mt-1">
        <div class="container-fluid">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="description">
                        {!! preg_replace('/<h1\b([^>]*)>(.*?)<\/h1>/i', '<h2$1>$2</h2>', $page->content) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
@endsection
