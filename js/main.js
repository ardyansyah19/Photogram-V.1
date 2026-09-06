document.addEventListener('DOMContentLoaded', function () {

  // ===== LIKE / UNLIKE =====
  document.querySelectorAll('.js-like-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const photoId = this.dataset.photoId;
      const icon = this.querySelector('i');

      fetch('ajax/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'photo_id=' + encodeURIComponent(photoId)
      })
      .then(res => res.json())
      .then(data => {
        if (data.error) return alert(data.error);

        if (data.liked) {
          icon.classList.remove('fa-regular');
          icon.classList.add('fa-solid');
          this.classList.remove('text-gray-700', 'hover:text-gray-400');
          this.classList.add('text-red-500');
        } else {
          icon.classList.remove('fa-solid');
          icon.classList.add('fa-regular');
          this.classList.remove('text-red-500');
          this.classList.add('text-gray-700', 'hover:text-gray-400');
        }

        const countEl = document.querySelector(`.js-like-count[data-photo-id="${photoId}"]`);
        if (countEl) countEl.textContent = data.count + ' suka';
      })
      .catch(() => alert('Terjadi kesalahan, coba lagi.'));
    });
  });

  // ===== KOMENTAR =====
  document.querySelectorAll('.js-comment-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const photoId = this.dataset.photoId;
      const input = this.querySelector('input[name="comment"]');
      const comment = input.value.trim();
      if (!comment) return;

      fetch('ajax/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'photo_id=' + encodeURIComponent(photoId) + '&comment=' + encodeURIComponent(comment)
      })
      .then(res => res.json())
      .then(data => {
        if (data.error) return alert(data.error);

        const list = document.getElementById('comment-list-' + photoId);
        const p = document.createElement('p');
        p.className = 'text-sm';
        p.innerHTML = `<span class="font-semibold">${escapeHtml(data.username)}</span> ${escapeHtml(data.comment)}`;
        list.appendChild(p);
        input.value = '';
      })
      .catch(() => alert('Terjadi kesalahan, coba lagi.'));
    });
  });

  // ===== TRACK VIEW (Intersection Observer) =====
  const viewedPhotos = new Set();
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const article = entry.target.closest('article');
        const photoId = article?.dataset.photoId;
        if (photoId && !viewedPhotos.has(photoId)) {
          viewedPhotos.add(photoId);
          fetch('ajax/track_view.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'photo_id=' + encodeURIComponent(photoId)
          });
        }
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.js-photo-view').forEach(img => observer.observe(img));

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
});
