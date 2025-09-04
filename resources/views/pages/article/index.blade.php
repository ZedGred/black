@extends('layouts.app')

@section('title', 'Article Detail')

@section('dashboard-content')
    <div class="container mt-5 d-flex justify-content-center">
        <div style="max-width: 800px; width: 100%;" id="articleContainer">
            <p class="text-center">Loading...</p>
        </div>
    </div>

    <div class="container mt-4 d-flex justify-content-center">
        <div style="max-width: 800px; width: 100%;" id="commentsContainer">
            <h4>Komentar (<span id="commentsCount">0</span>)</h4>

            @if (auth()->check())
                <div id="commentFormWrapper" style="display: none;" class="mt-3">
                    <textarea id="newComment" class="form-control" rows="3" placeholder="Tulis komentar..."></textarea>
                    <button id="btnPostComment" class="btn btn-primary mt-2">Post Comment</button>
                </div>
            @endif

            <div id="commentsList" class="mt-3"></div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const isAuth = {{ auth()->check() ? 1 : 0 }};
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

            // Helper: POST request dengan CSRF & credentials
            const postWithCredentials = (url, data = {}) => {
                return axios.post(url, data, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    withCredentials: true
                });
            };

            // Toggle Like (Article or Comment)
            const toggleLike = (btn, type, id) => {
                const isLiked = btn.dataset.liked === 'true';
                const method = isLiked ? 'DELETE' : 'POST';
                const url = type === 'article' ?
                    `/api/articles/${id}/like` :
                    `/api/comments/${id}/like`;

                btn.disabled = true;

                // Optimistic UI update
                const likeTextEl = btn.querySelector('span');
                let currentCount = parseInt(likeTextEl.textContent) || 0;
                const newCount = isLiked ? currentCount - 1 : currentCount + 1;

                // Store original state for potential rollback
                const originalLiked = isLiked;
                const originalCount = currentCount;

                // Update UI optimistically
                btn.dataset.liked = (!isLiked).toString();
                likeTextEl.textContent = newCount;

                // Update button styling
                if (isLiked) {
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-outline-primary');
                } else {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-secondary');
                }

                // Make API request
                axios({
                        method,
                        url,
                        withCredentials: true,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        // Update with server count
                        const serverCount = res.data.data?.liked_users_count || newCount;
                        likeTextEl.textContent = serverCount;
                    })
                    .catch(err => {
                        console.error('Like error:', err);

                        if (err.response?.status === 409) {
                            // Conflict - handle auto-unlike
                            axios.delete(url, {
                                    withCredentials: true,
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    }
                                })
                                .then(res => {
                                    const count = res.data.data?.liked_users_count || 0;
                                    btn.dataset.liked = 'false';
                                    likeTextEl.textContent = count;
                                    btn.classList.remove('btn-secondary');
                                    btn.classList.add('btn-outline-primary');
                                })
                                .catch(retryErr => {
                                    console.error('Auto-unlike failed:', retryErr);
                                    // Rollback to original state
                                    rollbackUI(btn, likeTextEl, originalLiked, originalCount);
                                    alert('Gagal membatalkan like. Silakan coba lagi.');
                                });
                        } else {
                            // Rollback UI to original state
                            rollbackUI(btn, likeTextEl, originalLiked, originalCount);

                            // Show appropriate error message
                            const serverMessage = err.response?.data?.message;
                            alert(id);
                        }
                    })
                    .finally(() => {
                        btn.disabled = false;
                    });
            };

            // Helper function to rollback UI state
            const rollbackUI = (btn, likeTextEl, originalLiked, originalCount) => {
                btn.dataset.liked = originalLiked.toString();
                likeTextEl.textContent = originalCount;

                if (originalLiked) {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-secondary');
                } else {
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-outline-primary');
                }
            };

            // Render Article
            const renderArticle = (article) => {
                const isLiked = article.liked_by_user || false;
                const likeButtonHtml = isAuth ? `
                    <button class="btn btn-sm d-flex align-items-center ${isLiked ? 'btn-secondary' : 'btn-outline-primary'} btn-like" 
                            data-type="article" 
                            data-id="${article.id}" 
                            data-liked="${isLiked}">
                        <i class="bi bi-hand-thumbs-up me-1 fs-5"></i>
                        <span class="fw-semibold fs-6">${article.liked_users_count || 0}</span>
                    </button>
                ` : '';

                const commentButtonHtml = isAuth ? `
                    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center btn-reply"
                            data-id="${article.id}">
                        <i class="bi bi-chat-left-text me-1 fs-5"></i>
                        <span class="fw-semibold fs-6">${article.comments_count || 0}</span>
                    </button>
                ` : '';

                // Create clickable author profile with photo
                const authorProfileHtml = `
                    <div class="d-flex align-items-center mb-3">
                        <div class="symbol symbol-35px me-2">
                            <img src="{{ asset('assets/media/avatars/blank.png') }}" alt="${article.user.name}" class="rounded-circle" />
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <a href="http://localhost:8000/${article.user.name}" class="text-decoration-none text-primary fw-semibold">
                                ${article.user.name}
                            </a>
                            <small class="text-muted">${new Date(article.published_at).toLocaleDateString()}</small>
                        </div>
                    </div>
                `;

                container.innerHTML = `
                    <div class="card mb-4">
                        <div class="card-body">
                            ${authorProfileHtml}
                            <h2 class="card-title mb-4">${article.title}</h2>
                            <div class="card-text mt-3">${article.content.replace(/\n/g, '<br>')}</div>
                            <div class="mt-3 d-flex align-items-center justify-content-start gap-2">
                                ${likeButtonHtml}
                                ${commentButtonHtml}
                            </div>
                        </div>
                    </div>
                `;

                // Attach like event to article button
                const likeBtn = container.querySelector('.btn-like[data-type="article"]');
                if (likeBtn) {
                    likeBtn.addEventListener('click', () => toggleLike(likeBtn, 'article', article.id));
                }
            };

            // Render Comments
            const renderComments = (comments) => {
                commentsCount.innerText = comments.length;

                if (comments.length === 0) {
                    commentsList.innerHTML = '<p class="text-center text-muted">Belum ada komentar.</p>';
                    return;
                }

                const fragment = document.createDocumentFragment();

                comments.forEach(c => {
                    const isLiked = c.liked_by_user || false;
                    const likeButtonHtml = isAuth ? `
                        <button class="btn btn-sm d-flex align-items-center ${isLiked ? 'btn-secondary' : 'btn-outline-primary'} btn-like" 
                                data-type="comment" 
                                data-id="${c.id}" 
                                data-liked="${isLiked}">
                            <i class="bi bi-hand-thumbs-up me-1 fs-5"></i>
                            <span class="fw-semibold fs-6">${c.liked_users_count || 0}</span>
                        </button>
                    ` : '';

                    const commentButtonHtml = isAuth ? `
                        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center btn-reply"
                                data-id="${c.id}">
                            <i class="bi bi-chat-left-text me-1 fs-5"></i>
                            <span class="fw-semibold fs-6">${c.comments_count || 0}</span>
                        </button>
                    ` : '';

                    // Create comment author profile with photo
                    const commentAuthorHtml = `
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-30px me-2">
                                <img src="{{ asset('assets/media/avatars/blank.png') }}" alt="${c.user.name}" class="rounded-circle" />
                            </div>
                            <div class="d-flex flex-column">
                                <a href="http://localhost:8000/${c.user.name}" class="text-decoration-none text-primary fw-semibold fs-7">
                                    ${c.user.name}
                                </a>
                                <small class="text-muted">${new Date(c.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                    `;

                    const commentEl = document.createElement('div');
                    commentEl.className = 'card mb-2';
                    commentEl.innerHTML = `
                        <div class="card-body">
                            ${commentAuthorHtml}
                            <p class="mb-2">${c.content}</p>
                            <div class="mt-2 d-flex align-items-center justify-content-start gap-2">
                                ${likeButtonHtml}
                                ${commentButtonHtml}
                            </div>
                        </div>
                    `;
                    fragment.appendChild(commentEl);
                });

                commentsList.innerHTML = '';
                commentsList.appendChild(fragment);

                // Attach like events to all comment like buttons
                document.querySelectorAll('.btn-like[data-type="comment"]').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const id = e.target.closest('.btn-like').dataset.id;
                        toggleLike(e.target.closest('.btn-like'), 'comment', id);
                    });
                });
            };

            // Load Article & Comments
            const loadArticle = () => {
                axios.get(`/api/articles/${articleId}`, {
                        withCredentials: true
                    })
                    .then(res => {
                        const article = res.data.data;
                        renderArticle(article);
                        renderComments(article.comments || []);
                        if (commentFormWrapper) commentFormWrapper.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Failed to load article:', err);
                        container.innerHTML =
                            '<p class="text-danger text-center">Gagal memuat artikel.</p>';
                    });
            };

            // Post Comment
            if (btnPostComment && newComment) {
                btnPostComment.addEventListener('click', () => {
                    const content = newComment.value.trim();
                    if (!content) return alert('Komentar tidak boleh kosong!');

                    btnPostComment.disabled = true;
                    btnPostComment.textContent = 'Posting...';

                    postWithCredentials(`/api/comments/articles/${articleId}`, {
                            content
                        })
                        .then(res => {
                            const c = res.data.data;
                            const isLiked = c.liked_by_user || false;

                            const likeButtonHtml = isAuth ? `
                                <button class="btn btn-sm d-flex align-items-center ${isLiked ? 'btn-secondary' : 'btn-outline-primary'} btn-like" 
                                        data-type="comment" 
                                        data-id="${c.id}" 
                                        data-liked="${isLiked}">
                                    <i class="bi bi-hand-thumbs-up me-1 fs-5"></i>
                                    <span class="fw-semibold fs-6">${c.liked_users_count || 0}</span>
                                </button>
                            ` : '';

                            const commentButtonHtml = isAuth ? `
                                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center btn-reply"
                                        data-id="${c.id}">
                                    <i class="bi bi-chat-left-text me-1 fs-5"></i>
                                    <span class="fw-semibold fs-6">${c.comments_count || 0}</span>
                                </button>
                            ` : '';

                            // Create new comment author profile with photo
                            const newCommentProfilePicture = c.user.profile_picture || "{{ asset('assets/media/avatars/blank.png') }}";
                            const newCommentAuthorHtml = `
                                <div class="d-flex align-items-center mb-2">
                                    <div class="symbol symbol-30px me-2">
                                        <img src="${newCommentProfilePicture}" alt="${c.user.name}" class="rounded-circle" />
                                    </div>
                                    <a href="http://localhost:8000/${c.user.name}" class="text-decoration-none text-primary fw-semibold fs-7 me-2">
                                        ${c.user.name}
                                    </a>
                                    <small class="text-muted">${new Date(c.created_at).toLocaleDateString()}</small>
                                </div>
                            `;

                            const newCommentEl = document.createElement('div');
                            newCommentEl.className = 'card mb-2';
                            newCommentEl.innerHTML = `
                                <div class="card-body">
                                    ${newCommentAuthorHtml}
                                    <p class="mb-2">${c.content}</p>
                                    <div class="mt-2 d-flex align-items-center justify-content-start gap-2">
                                        ${likeButtonHtml}
                                        ${commentButtonHtml}
                                    </div>
                                </div>
                            `;

                            commentsList.prepend(newCommentEl);
                            commentsCount.innerText = parseInt(commentsCount.innerText) + 1;

                            // Attach event listener to new like button
                            const likeBtn = newCommentEl.querySelector('.btn-like');
                            if (likeBtn) {
                                likeBtn.addEventListener('click', () => toggleLike(likeBtn, 'comment', c.id));
                            }

                            // Reset form
                            newComment.value = '';
                            btnPostComment.disabled = false;
                            btnPostComment.textContent = 'Post Comment';
                        })
                        .catch(err => {
                            console.error('Failed to post comment:', err);
                            alert('Gagal mengirim komentar. Silakan coba lagi.');
                            btnPostComment.disabled = false;
                            btnPostComment.textContent = 'Post Comment';
                        });
                });
            }

            // Load on start
            loadArticle();
        });
    </script>
@endsection