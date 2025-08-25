@extends(auth()->check() ? 'dashboard' : 'layouts.app')

@section('title', 'Article Detail')

@section(auth()->check() ? 'dashboard-content' : 'content')
<div class="container mt-5 d-flex justify-content-center">
    <div style="max-width: 800px; width: 100%;" id="articleContainer">
        <p class="text-center">Loading ....</p>
    </div>
</div>

<div class="container mt-4 d-flex justify-content-center">
    <div style="max-width: 800px; width: 100%;" id="commentsContainer">
        <h4>Komentar (<span id="commentsCount">0</span>)</h4>
        @if (auth()->check())
            <div class="mt-3" id="commentFormWrapper" style="display:none;">
                <textarea id="newComment" class="form-control" rows="3" placeholder="Tulis komentar..."></textarea>
                <button id="btnPostComment" class="btn btn-primary mt-2">Post Comment</button>
            </div>
        @endif
        <div id="commentsList" class="mt-3"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById('articleContainer');
    const commentsList = document.getElementById('commentsList');
    const commentFormWrapper = document.getElementById('commentFormWrapper');
    const newComment = document.getElementById('newComment');
    const btnPostComment = document.getElementById('btnPostComment');
    const commentsCount = document.getElementById('commentsCount');

    const urlParts = window.location.pathname.split('/');
    const articleId = urlParts[2];

    if (!articleId) {
        container.innerHTML = '<p class="text-danger text-center">Article ID missing</p>';
        return;
    }

    const postWithCredentials = (url, data = {}) => axios.post(url, data, {
        headers: { 'Accept': 'application/json' },
        withCredentials: true
    });

    const toggleLike = (btn, type, id) => {
    const isLiked = btn.dataset.liked === 'true'; // apakah sudah like
    const method = isLiked ? 'delete' : 'post';  // delete = unlike, post = like
    const url = type === 'article'
        ? `/api/articles/${id}/like`
        : `/api/comments/${id}/like`;

    btn.disabled = true;

    // Optimistic update UI
    let currentCount = parseInt(btn.innerText.match(/\d+/)[0] || 0);
    btn.dataset.liked = (!isLiked).toString();
    btn.innerText = `${!isLiked ? 'Liked' : 'Like'} (${!isLiked ? currentCount + 1 : currentCount - 1})`;
    btn.classList.toggle('btn-secondary', !isLiked);
    btn.classList.toggle('btn-outline-primary', isLiked);

    const csrfToken = document.querySelector('input[name=_token]').value;

    axios({
        method,
        url,
        withCredentials: true,
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => {
        // update count sesuai response server
        const count = res.data.data.liked_users_count || 0;
        btn.innerText = `${!isLiked ? 'Liked' : 'Like'} (${count})`;
    })
    .catch(err => {
        if (err.response && err.response.status === 409) {
            // Conflict → rollback dan ubah method ke delete otomatis
            if (!isLiked) { // sebelumnya kita kirim post, server bilang 409 → berarti harus unlike
                axios.delete(url, {
                    withCredentials: true,
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(res => {
                    const count = res.data.data.liked_users_count || 0;
                    btn.dataset.liked = 'false';
                    btn.innerText = `Like (${count})`;
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-outline-primary');
                })
                .catch(err2 => {
                    console.error('Auto-unlike failed', err2);
                    alert('Gagal update like.');
                });
            }
        } else {
            console.error(err);
            // rollback UI
            btn.dataset.liked = isLiked.toString();
            btn.innerText = `${isLiked ? 'Liked' : 'Like'} (${currentCount})`;
            btn.classList.toggle('btn-secondary', isLiked);
            btn.classList.toggle('btn-outline-primary', !isLiked);
            alert('Gagal update like.');
        }
    })
    .finally(() => {
        btn.disabled = false;
    });
};


    const renderArticle = (article) => {
        const isLiked = article.is_liked_by_user || false;

        container.innerHTML = `
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title text-center">${article.title}</h2>
                    <p class="text-center text-muted">
                        <small>by ${article.user.name} | ${new Date(article.published_at).toLocaleDateString()}</small>
                    </p>
                    <div class="card-text mt-3">${article.content.replace(/\n/g, '<br>')}</div>
                    <div class="mt-2">
                        <button id="btnLikeArticle" 
                                class="btn btn-sm ${isLiked ? 'btn-secondary' : 'btn-outline-primary'}" 
                                data-liked="${isLiked}" 
                                data-id="${article.id}">
                            ${isLiked ? 'Liked' : 'Like'} (${article.liked_users_count || 0})
                        </button>
                    </div>
                </div>
            </div>
        `;

        const btnLikeArticle = document.getElementById('btnLikeArticle');
        if (btnLikeArticle) {
            btnLikeArticle.addEventListener('click', () => toggleLike(btnLikeArticle, 'article', article.id));
        }
    };

    const attachCommentLikeToggle = (btn) => {
        btn.addEventListener('click', () => {
            const commentId = btn.dataset.commentId;
            toggleLike(btn, 'comment', commentId);
        });
    };

    const renderComments = (comments) => {
        commentsCount.innerText = comments.length;
        if (!comments.length) {
            commentsList.innerHTML = '<p class="text-center">Belum ada komentar.</p>';
            return;
        }

        let html = '';
        comments.forEach(c => {
            const isLiked = c.is_liked_by_user || false;
            html += `
                <div class="card mb-2">
                    <div class="card-body">
                        <p class="mb-1">${c.content}</p>
                        <small class="text-muted">${new Date(c.created_at).toLocaleString()}</small>
                        <div class="mt-2">
                            <button class="btn btn-sm ${isLiked ? 'btn-secondary' : 'btn-outline-primary'} btn-like-comment"
                                    data-comment-id="${c.id}"
                                    data-liked="${isLiked}">
                                ${isLiked ? 'Liked' : 'Like'} (${c.liked_users_count || 0})
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        commentsList.innerHTML = html;

        document.querySelectorAll('.btn-like-comment').forEach(attachCommentLikeToggle);
    };

    const loadArticle = () => {
        axios.get(`/api/articles/${articleId}`, { withCredentials: true })
            .then(res => {
                const article = res.data.data;
                renderArticle(article);
                renderComments(article.comments || []);
                if (commentFormWrapper) commentFormWrapper.style.display = 'block';
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<p class="text-danger text-center">Failed to load article</p>';
            });
    };

    if (btnPostComment) {
        btnPostComment.addEventListener('click', () => {
            const content = newComment.value.trim();
            if (!content) return alert('Komentar tidak boleh kosong!');

            btnPostComment.disabled = true;
            btnPostComment.innerText = 'Posting...';

            postWithCredentials(`/api/comments/articles/${articleId}`, { content })
                .then(res => {
                    newComment.value = '';
                    const c = res.data.data;
                    const commentHtml = `
                        <div class="card mb-2">
                            <div class="card-body">
                                <p class="mb-1">${c.content}</p>
                                <small class="text-muted">${new Date(c.created_at).toLocaleString()}</small>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary btn-like-comment"
                                            data-comment-id="${c.id}" data-liked="false">
                                        Like (0)
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                    commentsCount.innerText = parseInt(commentsCount.innerText) + 1;

                    const newBtn = commentsList.querySelector(`.btn-like-comment[data-comment-id="${c.id}"]`);
                    if (newBtn) attachCommentLikeToggle(newBtn);

                    btnPostComment.disabled = false;
                    btnPostComment.innerText = 'Post Comment';
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal mengirim komentar. Silakan coba lagi.');
                    btnPostComment.disabled = false;
                    btnPostComment.innerText = 'Post Comment';
                });
        });
    }

    loadArticle();
});
</script>

@endsection