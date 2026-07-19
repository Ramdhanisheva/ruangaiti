@if ($post->enable_comment)
<div class="comments-section-container">

    {{-- Comments List --}}
    @if ($post->comments_count > 0)
    <h3 class="comments-title">{{ $post->comments_count }} {{ Str::plural('Comment', $post->comments_count) }}</h3>
    <div class="comments-list">
        @foreach ($post->comments as $comment)
        <div class="comment-card">
            <img
                class="comment-avatar"
                src="{{ asset('uploads/author/' . ($comment->user && $comment->user->profile ? $comment->user->profile : 'default.webp')) }}"
                alt="{{ $comment->user->name ?? $comment->name }}"
            >
            <div style="flex: 1;">
                <div class="comment-content">
                    <div class="comment-meta">
                        <span class="comment-author-name">
                            @if ($comment->user)
                                <a href="{{ route('frontend.user', $comment->user->username) }}">{{ $comment->user->name }}</a>
                                @if ($comment->user && $comment->user->id === $post->user->id)
                                    <span class="badge" style="margin-left: 8px; font-size: 11px; vertical-align: middle;">Author</span>
                                @endif
                            @else
                                {{ $comment->name }}
                            @endif
                        </span>
                        <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="comment-text">{{ $comment->message }}</p>
                    <a href="#" data-comment-id="{{ $comment->id }}" class="comment-reply-btn btn-reply">
                        <x-icon name="reply" width="14" height="14" />
                        Reply
                    </a>
                </div>

                {{-- Nested Replies --}}
                @if (count($comment->replies) > 0)
                <div class="replies-wrapper">
                    @foreach ($comment->replies as $reply)
                    <div class="comment-card">
                        <img
                            class="comment-avatar"
                            style="width: 36px; height: 36px;"
                            src="{{ asset('uploads/author/' . ($reply->user && $reply->user->profile ? $reply->user->profile : 'default.webp')) }}"
                            alt="{{ $reply->user->name ?? $reply->name }}"
                        >
                        <div class="comment-content" style="flex: 1;">
                            <div class="comment-meta">
                                <span class="comment-author-name">
                                    @if ($reply->user)
                                        <a href="{{ route('frontend.user', $reply->user->username) }}">{{ $reply->user->name }}</a>
                                        @if ($reply->user && $reply->user->id === $post->user->id)
                                            <span class="badge" style="margin-left: 8px; font-size: 11px; vertical-align: middle;">Author</span>
                                        @endif
                                    @else
                                        {{ $reply->name }}
                                    @endif
                                </span>
                                <span class="comment-date">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="comment-text">{{ $reply->message }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Comment Form --}}
    <div id="comment-form-location">
        <div class="comment-form-container" id="comment-form">
            <h3>Leave a Reply</h3>

            @if (session('success'))
            <div style="background-color: rgba(34, 197, 94, 0.1); border: 1px solid var(--color-success); color: var(--color-success); padding: 12px 16px; border-radius: var(--radius-small); margin-bottom: 16px; font-size: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--color-danger); color: var(--color-danger); padding: 12px 16px; border-radius: var(--radius-small); margin-bottom: 16px; font-size: 15px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('frontend.comment', $post->id) }}" method="POST" id="main_contact_form">
                @csrf
                @guest
                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 16px;">Your email address will not be published. Required fields are marked *.</p>
                <div class="comment-form-grid">
                    <div class="form-group">
                        <input type="text" name="name" id="name" class="form-control" placeholder="Name *" required value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email *" required value="{{ old('email') }}">
                    </div>
                </div>
                @endguest
                <div class="form-group">
                    <textarea name="message" id="message" rows="5" class="form-control" placeholder="Write your comment..." required>{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn-primary">Post Comment</button>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
    var replyUrl = '{{ route('frontend.comment.reply') }}';
</script>
@endsection
@endif
