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
</section>
