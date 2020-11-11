<section class="comment col-12">
    <form action="{{url('comment')}}" method="POST">
        @csrf
        <div class="comment_form">
            <div class="">
                <h3>Bình luận</h3>
            </div>
            <div class="comment_content d-flex">
                <div class="comment_ava">
                    <div class="comment_img">
                        <img src="/images/comments/user.png" alt="">
                    </div>
                </div>
                <div class="comment_textarea">
                    <input type="text-area" value="{{ old('content') }}" class="textarea"
                    placeholder="Nhập bình luận ..." name="content">
                    <input name="commentable_id" value="{{ $commentable_id }}" hidden>
                    <input name="commentable_type" value="{{ $commentable_type }}" hidden>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i><input type="submit" value="Add reply">
            </div>
        </div>
    </form>
    @if(!count($comments))
    <div class="comment_alert">
        <div class="alert alert-danger">Chưa có bình luận nào !</div>
    </div>
    @else
    @foreach ($comments as $comment)
    <div class="comment_show">
        <div class="d-flex">
            <div class="comment_ava">
                <div class="comment_img">
                    <img src="/images/comments/user.png" alt="">
                </div>
            </div>
            <div class="comment_textarea">
                <div class="d-flex align-items-center">
                    <div class="comment_user">{{ $comment->name }}</div>
                    <div class="comment_date">{{ $comment->datetimes() }}</div>
                </div>
                <div class="textarea" id="ShowInfo">{{ $comment->content }}</div>
            </div>
        </div>
    </div>
    @endforeach
    @if(method_exists($comments,'links'))
    <div class="d-flex justify-content-end"> {{ $comments->links() }}</div>
    @endif
    @endif
</section>
